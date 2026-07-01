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

class DataMapTest extends TestCase {

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
		$dataMap->reqEnum('key3', PureEnumMock::cases());
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testOptEnum() {
		$dataMap = new DataMap(['key1' => 'value-1', 'key2' => 'CASE2']);

		$this->assertEquals(StringBackedEnumMock::VALUE1,
				$dataMap->optEnum('key1', StringBackedEnumMock::cases()));

		$this->assertEquals(PureEnumMock::CASE2,
				$dataMap->optEnum('key2', PureEnumMock::cases()));

		$dataMap->optEnum('key3', PureEnumMock::cases());
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqScalar() {
		$dataMap = new DataMap(['key1' => 'value-1', 'key2' => StringBackedEnumMock::VALUE1]);
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
		$dataMap->reqScalar('key3', false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptScalar() {
		$dataMap = new DataMap(['key1' => 'value-1', 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertEquals('value-1',
				$dataMap->optScalar('key1', false));

		$dataMap->optScalar('key3', false);

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optScalar('key2', false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqBool() {
		$dataMap = new DataMap(['key1' => true, 'key2' => StringBackedEnumMock::VALUE1]);
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
		$dataMap->reqBool('key3', false, false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptBool() {
		$dataMap = new DataMap(['key1' => false, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(false,
				$dataMap->optBool('key1', false, true, false));

		$dataMap->optBool('key3', false, true, false);

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optBool('key2', false, true, false);
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
		$dataMap->reqNumeric('key3', false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptNumeric() {
		$dataMap = new DataMap(['key1' => 10.01, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10.01,
				$dataMap->optNumeric('key1', false, true));

		$dataMap->optNumeric('key3', false, true);

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optNumeric('key2', false, true);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqInt() {
		$dataMap = new DataMap(['key1' => 10, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10,
				$dataMap->reqInt('key1', false));

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
		$dataMap->reqInt('key3', false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptInt() {
		$dataMap = new DataMap(['key1' => 10.01, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10,
				$dataMap->optInt('key1', false, true));

		$dataMap->optInt('key3', false, true);

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optInt('key2', false, true);
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
		$dataMap->reqArray('key3', 'int');
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptArray() {
		$dataMap = new DataMap(['key1' => [10], 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame([10],
				$dataMap->optArray('key1', 'int', true));

		$dataMap->optArray('key3', 'int', true);

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
		$dataMap->reqScalarArray('key3', false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptScalarArray() {
		$dataMap = new DataMap(['key1' => [10], 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame([10],
				$dataMap->optScalarArray('key1', false, true));

		$dataMap->optScalarArray('key3', false, true);

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optScalarArray('key2', false, true);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @throws IllegalValueException
	 */
	function testReqStringValueObject() {
		$dataMap = new DataMap(['key1' => new StringValueObjectMock(10) ,'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame('10',
				$dataMap->reqStringValueObject('key1', StringValueObjectMock::class)->toScalar());

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqStringValueObject('key2', false, false);
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
		$dataMap->reqStringValueObject('key3', StringValueObjectMock::class);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws IllegalValueException
	 */
	function testOptStringValueObject() {
		$dataMap = new DataMap(['key1' => new StringValueObjectMock(10.01), 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame('10.01',
				$dataMap->optStringValueObject('key1', StringValueObjectMock::class, null)->toScalar());

		$dataMap->optStringValueObject('key3', StringValueObjectMock::class, null );

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optStringValueObject('key2', StringValueObjectMock::class, null);
	}


	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @throws IllegalValueException
	 */
	function testReqIntValueObject() {
		$dataMap = new DataMap(['key1' => new IntValueObjectMock(10) ,'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10,
				$dataMap->reqIntValueObject('key1', IntValueObjectMock::class)->toScalar());

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqIntValueObject('key2', false, false);
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
		$dataMap->reqIntValueObject('key3', IntValueObjectMock::class);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws IllegalValueException
	 */
	function testOptIntValueObject() {
		$dataMap = new DataMap(['key1' => new IntValueObjectMock(10), 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10,
				$dataMap->optIntValueObject('key1', IntValueObjectMock::class, null)->toScalar());

		$dataMap->optIntValueObject('key3', IntValueObjectMock::class, null );

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optIntValueObject('key2', IntValueObjectMock::class, null);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws IllegalValueException
	 * @throws MissingAttributeFieldException
	 */
	function testReqFloatValueObject() {
		$dataMap = new DataMap(['key1' => new FloatValueObjectMock(10.01) ,'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10.01,
				$dataMap->reqFloatValueObject('key1', FloatValueObjectMock::class)->toScalar());

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqFloatValueObject('key2', false, false);
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
		$dataMap->reqFloatValueObject('key3', FloatValueObjectMock::class);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws IllegalValueException
	 */
	function testOptFloatValueObject() {
		$dataMap = new DataMap(['key1' => new FloatValueObjectMock(10.01), 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10.01,
				$dataMap->optFloatValueObject('key1', FloatValueObjectMock::class, null)->toScalar());

		$dataMap->optFloatValueObject('key3', FloatValueObjectMock::class, null );

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optFloatValueObject('key2', FloatValueObjectMock::class, null);
	}
	
	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @throws IllegalValueException
	 */
	function testReqBoolValueObject() {
		$dataMap = new DataMap(['key1' => new BoolValueObjectMock(false) ,'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(false,
				$dataMap->reqBoolValueObject('key1', BoolValueObjectMock::class)->toScalar());

		$this->expectException(InvalidAttributeException::class);
		$dataMap->reqBoolValueObject('key2', false, false);
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
		$dataMap->reqBoolValueObject('key3', BoolValueObjectMock::class);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws IllegalValueException
	 */
	function testOptBoolValueObject() {
		$dataMap = new DataMap(['key1' => new BoolValueObjectMock(false), 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(false,
				$dataMap->optBoolValueObject('key1', BoolValueObjectMock::class, null)->toScalar());

		$dataMap->optBoolValueObject('key3', BoolValueObjectMock::class, null );

		$this->expectException(InvalidAttributeException::class);
		$dataMap->optBoolValueObject('key2', BoolValueObjectMock::class, null);
	}

}