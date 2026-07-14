<?php

namespace n2n\util\attr;

use PHPUnit\Framework\TestCase;
use n2n\util\attr\mock\StringBackedEnumMock;
use n2n\util\attr\mock\PureEnumMock;
use n2n\util\attr\mock\StringValueObjectMock;
use n2n\spec\valobj\err\IllegalValueException;
use n2n\util\attr\mock\IntValueObjectMock;
use n2n\util\attr\mock\FloatValueObjectMock;
use n2n\util\attr\mock\BoolValueObjectMock;
use n2n\test\case\N2nTestCaseTrait;
use InvalidArgumentException;

class DataMapTest extends TestCase {
	use N2nTestCaseTrait;

	function testSet() {
		$dataMap = new DataMap(['key1' => ['skey1' => 'string']]);

		$dataMap->set('key1/skey1/sskey1', 'huii');

		$array = $dataMap->toArray();

		$this->assertTrue($array['key1']['skey1']['sskey1'] === 'huii');
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testClean() {
		$dataMap = new DataMap(['key1' => ['skey1' => " \t st\x00ri\r\nng \r\n \n"]]);

		$dataMap->cleanStrings(['key1/skey1']);

		$this->assertEquals("stri ng", $dataMap->reqString('key1/skey1'));
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testClean2() {
		$dataMap = new DataMap(['key1' => ['skey1' => " \t st\x00ri\r\nng \r\n \n"]]);

		$dataMap->cleanStrings(['key1/skey1'], false);

		$this->assertEquals(" \t stri\r\nng \r\n \n", $dataMap->reqString('key1/skey1'));
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqEnum() {
		$dataMap = new DataMap(['key1' => 'value-1', 'key2' => 'CASE2', 'key3' => StringBackedEnumMock::VALUE1]);

		$this->assertEquals(StringBackedEnumMock::VALUE1,
				$dataMap->reqEnum('key1', StringBackedEnumMock::cases()));

		$this->assertEquals(PureEnumMock::CASE2,
				$dataMap->reqEnum('key2', PureEnumMock::cases()));

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqEnum('key3', PureEnumMock::cases());
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqEnumMissingAttributes() {
		$dataMap = new DataMap(['key1' => 'value-1', 'key2' => 'CASE2']);

		$this->assertEquals(StringBackedEnumMock::VALUE1,
				$dataMap->reqEnum('key1', StringBackedEnumMock::cases()));

		$this->assertEquals(PureEnumMock::CASE2,
				$dataMap->reqEnum('key2', PureEnumMock::cases()));

		$this->expectException(MissingAttributeFieldException::class);
		$dataMap->reqEnum('missing', PureEnumMock::cases());
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testOptEnum() {
		$dataMap = new DataMap(['key1' => 'value-1', 'key2' => 'CASE2', 'key3' => null]);

		$this->assertEquals(StringBackedEnumMock::VALUE1,
				$dataMap->optEnum('key1', StringBackedEnumMock::cases()));

		$this->assertEquals(PureEnumMock::CASE2,
				$dataMap->optEnum('key2', PureEnumMock::cases()));

		$dataMap->optEnum('key3', PureEnumMock::cases(), StringBackedEnumMock::VALUE1);

		$dataMap->optEnum('missing', PureEnumMock::cases(), null, true);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqScalar() {
		$dataMap = new DataMap(['key1' => 'value-1', 'key2' => StringBackedEnumMock::VALUE1, 'key3' => null]);
		$this->assertEquals('value-1',
				$dataMap->reqScalar('key1', false));

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqScalar('key2', false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqScalarMissingAttributes() {
		$dataMap = new DataMap(['key1' => 'value-1']);
		$this->assertEquals('value-1',
				$dataMap->reqScalar('key1', false));

		$this->expectException(MissingAttributeFieldException::class);
		$dataMap->reqScalar('missing', false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptScalar() {
		$dataMap = new DataMap(['key1' => 'value-1', 'key2' => StringBackedEnumMock::VALUE1, 'key3' => null]);
		$this->assertEquals('value-1',
				$dataMap->optScalar('key1', false));

		$dataMap->optScalar('missing', false);

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optScalar('key2', false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqBool() {
		$dataMap = new DataMap(['key1' => true, 'key2' => StringBackedEnumMock::VALUE1, 'key3' => null]);
		$this->assertSame(true,
				$dataMap->reqBool('key1', false, false));

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqBool('key2', false, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqBoolMissingAttributes() {
		$dataMap = new DataMap(['key1' => true]);
		$this->assertSame(true,
				$dataMap->reqBool('key1', false, false));

		$this->expectException(MissingAttributeFieldException::class);
		$dataMap->reqBool('missing', false, false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptBool() {
		$dataMap = new DataMap(['key1' => false, 'key2' => StringBackedEnumMock::VALUE1, 'key3' => null]);
		$this->assertSame(false,
				$dataMap->optBool('key1', false, true, false));

		$dataMap->optBool('missing', false, true, false);

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optBool('key2', false, true, false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptBoolLenientFalse() {
		$dataMap = new DataMap(['key1' => null, 'key2' => 1]);
		$this->assertSame(null,
				$dataMap->optBool('key1', true, true, false));
		$this->assertSame(false,
				$dataMap->optBool('key4', false, false, true));
		$this->assertSame(null,
				$dataMap->optBool('key4', null, false, true));

		$dataMap->optBool('key2', false, true, true);
		$this->expectException(InvalidAttributeException::class);
		$dataMap->optBool('key2', false, true, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqBoolLenientFalse() {
		$dataMap = new DataMap(['key1' => null, 'key2' => 1]);
		$this->assertSame(null,
				$dataMap->reqBool('key1', true, true));

		$dataMap->reqBool('key2', false, true);
		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqBool('key2', false, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqNumeric() {
		$dataMap = new DataMap(['key1' => 10, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10,
				$dataMap->reqNumeric('key1', false));

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqNumeric('key2', false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqNumericMissingAttributes() {
		$dataMap = new DataMap(['key1' => '10']);
		$this->assertSame('10',
				$dataMap->reqNumeric('key1', false));

		$this->expectException(MissingAttributeFieldException::class);
		$dataMap->reqNumeric('missing', false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptNumeric() {
		$dataMap = new DataMap(['key1' => 10.01, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10.01,
				$dataMap->optNumeric('key1', false, true));

		$dataMap->optNumeric('missing', false, true);

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optNumeric('key2', false, true);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqInt() {
		$dataMap = new DataMap(['key1' => 10, 'key2' => StringBackedEnumMock::VALUE1, 'key3' => null]);
		$this->assertSame(10,
				$dataMap->reqInt('key1', false));

		$this->assertSame(null,
				$dataMap->reqInt('key3', true));

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqInt('key2', false, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqIntMissingAttributes() {
		$dataMap = new DataMap(['key1' => 10]);
		$this->assertSame(10,
				$dataMap->reqInt('key1', false));

		$this->expectException(MissingAttributeFieldException::class);
		$dataMap->reqInt('missing', false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptInt() {
		$dataMap = new DataMap(['key1' => 10.01, 'key2' => StringBackedEnumMock::VALUE1, 'key3' => null]);
		$this->assertSame(10,
				$dataMap->optInt('key1', false, true));

		$this->assertSame(null,
				$dataMap->optInt('key3', null));

		$dataMap->optInt('missing', false, true);

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optInt('key2', false, true);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptIntLenientFalse() {
		$dataMap = new DataMap(['key1' => null, 'key2' => StringBackedEnumMock::VALUE1, 'key3' => 10.1]);
		$dataMap->optInt('key1', null, true, false);
		$dataMap->optInt('key3', null, false, true);
		$this->expectException(InvalidAttributeException::class);
		$dataMap->optInt('key3', null, false, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqFloat() {
		$dataMap = new DataMap(['key1' => 10, 'key2' => StringBackedEnumMock::VALUE1, 'key3' => null]);
		$this->assertSame(10.0,
				$dataMap->reqFloat('key1', false));

		$this->assertSame(null,
				$dataMap->reqFloat('key3', true));

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqFloat('key2', false, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqFloatMissingAttributes() {
		$dataMap = new DataMap(['key1' => 10]);
		$this->assertSame(10.0,
				$dataMap->reqFloat('key1', false));

		$this->expectException(MissingAttributeFieldException::class);
		$dataMap->reqFloat('missing', false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptFloat() {
		$dataMap = new DataMap(['key1' => 10.01, 'key2' => StringBackedEnumMock::VALUE1, 'key3' => null]);
		$this->assertSame(10.01,
				$dataMap->optFloat('key1', false, true));

		$this->assertSame(null,
				$dataMap->optFloat('key3', null));

		$dataMap->optFloat('missing', false, true);

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optFloat('key2', false, true);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptFloatLenientFalse() {
		$dataMap = new DataMap(['key1' => null, 'key2' => StringBackedEnumMock::VALUE1, 'key3' => '10.1']);
		$dataMap->optFloat('key1', null, true, false);
		$dataMap->optFloat('key3', null, false, true);
		$this->expectException(InvalidAttributeException::class);
		$dataMap->optFloat('key3', null, false, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqArray() {
		$dataMap = new DataMap(['key1' => ['val1' => 10], 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(['val1' => 10],
				$dataMap->reqArray('key1', 'int'));

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqArray('key2', 'int', false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqArrayMissingAttributes() {
		$dataMap = new DataMap(['key1' => [10]]);
		$this->assertSame([10],
				$dataMap->reqArray('key1', 'int'));

		$this->expectException(MissingAttributeFieldException::class);
		$dataMap->reqArray('missing', 'int');
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptArray() {
		$dataMap = new DataMap(['key1' => [10], 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame([10],
				$dataMap->optArray('key1', 'int', true));

		$dataMap->optArray('missing', 'int', true);

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optArray('key2', 'int', true);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqScalarArray() {
		$dataMap = new DataMap(['key1' => [10], 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame([10],
				$dataMap->reqScalarArray('key1', false));

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqScalarArray('key2', false, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqScalarArrayMissingAttributes() {
		$dataMap = new DataMap(['key1' => [10]]);
		$this->assertSame([10],
				$dataMap->reqScalarArray('key1', false));

		$this->expectException(MissingAttributeFieldException::class);
		$dataMap->reqScalarArray('missing', false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptScalarArray() {
		$dataMap = new DataMap(['key1' => [10], 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame([10],
				$dataMap->optScalarArray('key1', false, true));

		$dataMap->optScalarArray('missing', false, true);

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optScalarArray('key2', false, true);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqStringValueObject() {
		$dataMap = new DataMap(['key1' => 'a10' ,'key2' => new IntValueObjectMock('10')]);
		$this->assertTypeSafeEquals(new StringValueObjectMock('a10'),
				$dataMap->reqStringValueObject('key1', StringValueObjectMock::class));

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqStringValueObject('key2', StringValueObjectMock::class, false);
	}


	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqStringValueObjectMissingAttributes() {
		$dataMap = new DataMap(['key1' => new StringValueObjectMock('10')]);
		$this->assertSame('10',
				$dataMap->reqStringValueObject('key1', StringValueObjectMock::class)->toScalar());

		$this->expectException(MissingAttributeFieldException::class);
		$dataMap->reqStringValueObject('missing', StringValueObjectMock::class);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptStringValueObject() {
		$dataMap = new DataMap(['key1' => 10.01, 'key2' => new IntValueObjectMock('10')]);
		$this->assertSame('10.01',
				$dataMap->optStringValueObject('key1', StringValueObjectMock::class, null)->toScalar());

		$this->assertTypeSafeEquals(new StringValueObjectMock('holeradio'),
				$dataMap->optStringValueObject('missing', StringValueObjectMock::class, new StringValueObjectMock('holeradio')));

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optStringValueObject('key2', StringValueObjectMock::class, null);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptStringValueObjectExpectExceptionBecauseWrongMock() {
		$dataMap = new DataMap(['key1' => 10.01, 'key2' => new IntValueObjectMock('10')]);

		$this->expectException(InvalidArgumentException::class);
		$dataMap->optStringValueObject('key1', IntValueObjectMock::class, null);
	}


	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @throws IllegalValueException
	 */
	function testReqIntValueObject() {
		$dataMap = new DataMap(['key1' => 10 ,'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertTypeSafeEquals(new IntValueObjectMock(10),
				$dataMap->reqIntValueObject('key1', IntValueObjectMock::class));

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqIntValueObject('key2', IntValueObjectMock::class, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqIntValueObjectMissingAttributes() {
		$dataMap = new DataMap(['key1' => new IntValueObjectMock('10')]);
		$this->assertSame(10,
				$dataMap->reqIntValueObject('key1', IntValueObjectMock::class)->toScalar());

		$this->expectException(MissingAttributeFieldException::class);
		$dataMap->reqIntValueObject('missing', IntValueObjectMock::class);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptIntValueObject() {
		$dataMap = new DataMap(['key1' => 10, 'invalid' => StringBackedEnumMock::VALUE1, 'key3' => new IntValueObjectMock('10')]);
		$this->assertSame(10,
				$dataMap->optIntValueObject('key1', IntValueObjectMock::class, null)->toScalar());
		$this->assertSame(10,
				$dataMap->optIntValueObject('key3', IntValueObjectMock::class, null)->toScalar());

		$dataMap->optIntValueObject('missing', IntValueObjectMock::class, null );

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optIntValueObject('invalid', IntValueObjectMock::class, null);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @throws IllegalValueException
	 */
	function testReqFloatValueObject() {
		$dataMap = new DataMap(['key1' => 10.01 ,'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertTypeSafeEquals(new FloatValueObjectMock(10.01),
				$dataMap->reqFloatValueObject('key1', FloatValueObjectMock::class));

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqFloatValueObject('key2', FloatValueObjectMock::class, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @throws IllegalValueException
	 */
	function testReqFloatValueObjectMissingAttributes() {
		$dataMap = new DataMap(['key1' => new FloatValueObjectMock(10.01)]);
		$this->assertSame(10.01,
				$dataMap->reqFloatValueObject('key1', FloatValueObjectMock::class)->toScalar());

		$this->expectException(MissingAttributeFieldException::class);
		$dataMap->reqFloatValueObject('missing', FloatValueObjectMock::class);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws IllegalValueException
	 */
	function testOptFloatValueObject() {
		$dataMap = new DataMap(['key1' => 10.01, 'key2' => StringBackedEnumMock::VALUE1, 'key3' => new FloatValueObjectMock(1.1)]);
		$this->assertSame(10.01,
				$dataMap->optFloatValueObject('key1', FloatValueObjectMock::class, null)->toScalar());

		$this->assertTypeSafeEquals(new FloatValueObjectMock(10.01),
				$dataMap->optFloatValueObject('key1', FloatValueObjectMock::class, null));

		$this->assertTypeSafeEquals(new FloatValueObjectMock(1.1),
				$dataMap->optFloatValueObject('key3', FloatValueObjectMock::class, null));

		$dataMap->optFloatValueObject('missing', FloatValueObjectMock::class, null );

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optFloatValueObject('key2', FloatValueObjectMock::class, null);
	}
	
	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqBoolValueObject() {
		$dataMap = new DataMap(['key1' => false ,'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertTypeSafeEquals(new BoolValueObjectMock(false),
				$dataMap->reqBoolValueObject('key1', BoolValueObjectMock::class));

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqBoolValueObject('key2', BoolValueObjectMock::class, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqBoolValueObjectMissingAttributes() {
		$dataMap = new DataMap(['key1' => new BoolValueObjectMock(true)]);
		$this->assertSame(true,
				$dataMap->reqBoolValueObject('key1', BoolValueObjectMock::class)->toScalar());

		$this->expectException(MissingAttributeFieldException::class);
		$dataMap->reqBoolValueObject('missing', BoolValueObjectMock::class);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptBoolValueObject() {
		$dataMap = new DataMap(['key1' => true, 'key2' => StringBackedEnumMock::VALUE1, 'key3' => new BoolValueObjectMock(true)]);
		$this->assertSame(true,
				$dataMap->optBoolValueObject('key1', BoolValueObjectMock::class, null)->toScalar());

		$this->assertSame(true,
				$dataMap->optBoolValueObject('key3', BoolValueObjectMock::class, null)->toScalar());

		$dataMap->optBoolValueObject('missing', BoolValueObjectMock::class, null );

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optBoolValueObject('key2', BoolValueObjectMock::class, null);
	}

}