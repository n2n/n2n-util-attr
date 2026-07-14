<?php
namespace n2n\util\attr;

use PHPUnit\Framework\TestCase;
use n2n\util\attr\mock\StringBackedEnumMock;
use n2n\util\attr\mock\PureEnumMock;
use n2n\spec\valobj\err\IllegalValueException;
use n2n\util\attr\mock\StringValueObjectMock;
use n2n\util\attr\mock\IntValueObjectMock;
use n2n\util\attr\mock\FloatValueObjectMock;
use n2n\util\attr\mock\BoolValueObjectMock;
use n2n\test\case\N2nTestCaseTrait;
use n2n\util\attr\mock\SubStringValueObjectMock;
use n2n\util\attr\mock\SubIntValueObjectMock;
use n2n\spec\valobj\scalar\IntValueObject;

class DataSetTest extends TestCase {
	use N2nTestCaseTrait;

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testEnum() {
		$dataSet = new DataSet(['key1' => 'value-1', 'key2' => 'CASE2']);

		$this->assertEquals(StringBackedEnumMock::VALUE1,
				$dataSet->reqEnum('key1', StringBackedEnumMock::cases()));

		$this->assertEquals(PureEnumMock::CASE2,
				$dataSet->reqEnum('key2', PureEnumMock::cases()));
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReadAttributeEmpty(): void {
		$dataSet = new DataSet(['key1' => 'value-1']);

		$this->assertSame(['key1' => 'value-1'], $dataSet->readAttribute(new AttributePath([])));
	}

	private function createStringable(string $value): \Stringable {
		return new class($value) implements \Stringable{

			function __construct(private string $value) {
			}

			public function __toString(): string {
				return $this->value;
			}
		};
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqString(): void {
		$dataSet = new DataSet(['key1' => 'value-1', 'key2' => $this->createStringable('value-2'), 'key3' => null]);
		$this->assertSame('value-1', $dataSet->reqString('key1'));
		$this->assertSame('value-2', $dataSet->reqString('key2'));
		$this->assertNull($dataSet->reqString('key3', nullAllowed: true));
	}

	/**
	 * @throws MissingAttributeFieldException
	 */
	function testReqStringNotLenientStringable(): void {
		$dataSet = new DataSet(['key2' => $this->createStringable('value-2')]);
		$this->expectException(InvalidAttributeException::class);
		$dataSet->reqString('key2', lenient: false);
	}

	/**
	 * @throws MissingAttributeFieldException
	 */
	function testReqStringNullNotAllowedStringable(): void {
		$dataSet = new DataSet(['key3' => null]);
		$this->expectException(InvalidAttributeException::class);
		$dataSet->reqString('key3');
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptString(): void {
		$dataSet = new DataSet(['key1' => 'value-1', 'key2' => $this->createStringable('value-2'), 'key3' => null]);
		$this->assertSame('value-1', $dataSet->optString('key1'));
		$this->assertSame('value-2', $dataSet->optString('key2'));
		$this->assertNull($dataSet->optString('key3'));
		$this->assertNull($dataSet->optString('key4'));
		$this->assertSame('holeradio', $dataSet->optString('key4', defaultValue: 'holeradio'));
	}

	function testOptStringNotLenientStringable(): void {
		$dataSet = new DataSet(['key2' => $this->createStringable('value-2')]);
		$this->expectException(InvalidAttributeException::class);
		$dataSet->optString('key2', lenient: false);
	}


	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqStringStringBackedEnum(): void {
		$dataSet = new DataSet(['key1' => StringBackedEnumMock::VALUE1, 'key2' => $this->createStringable('value-2'), 'key3' => null]);
		$this->assertSame('value-1', $dataSet->reqString('key1'));
		$this->assertSame(StringBackedEnumMock::VALUE1->value, $dataSet->reqString('key1'));
		$this->assertSame('value-2', $dataSet->reqString('key2'));
		$this->assertNull($dataSet->reqString('key3', nullAllowed: true));
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqEnum() {
		$dataSet = new DataSet(['key1' => 'value-1', 'key2' => 'CASE2', 'key3' => StringBackedEnumMock::VALUE1]);

		$this->assertEquals(StringBackedEnumMock::VALUE1,
				$dataSet->reqEnum('key1', StringBackedEnumMock::cases()));

		$this->assertEquals(PureEnumMock::CASE2,
				$dataSet->reqEnum('key2', PureEnumMock::cases()));

		$this->expectException(InvalidAttributeException::class);
		$dataSet->reqEnum('key3', PureEnumMock::cases());
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqEnumMissingAttributes() {
		$dataSet = new DataSet(['key1' => 'value-1', 'key2' => 'CASE2']);

		$this->assertEquals(StringBackedEnumMock::VALUE1,
				$dataSet->reqEnum('key1', StringBackedEnumMock::cases()));

		$this->assertEquals(PureEnumMock::CASE2,
				$dataSet->reqEnum('key2', PureEnumMock::cases()));

		$this->expectException(MissingAttributeFieldException::class);
		$dataSet->reqEnum('key3', PureEnumMock::cases());
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testOptEnum() {
		$dataSet = new DataSet(['key1' => 'value-1', 'key2' => 'CASE2']);

		$this->assertEquals(StringBackedEnumMock::VALUE1,
				$dataSet->optEnum('key1', StringBackedEnumMock::cases()));

		$this->assertEquals(PureEnumMock::CASE2,
				$dataSet->optEnum('key2', PureEnumMock::cases()));

		$dataSet->optEnum('key3', PureEnumMock::cases());
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqScalar() {
		$dataSet = new DataSet(['key1' => 'value-1', 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertEquals('value-1',
				$dataSet->reqScalar('key1', false));

		$this->expectException(InvalidAttributeException::class);
		$dataSet->reqScalar('key2', false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqScalarMissingAttributes() {
		$dataSet = new DataSet(['key1' => 'value-1']);
		$this->assertEquals('value-1',
				$dataSet->reqScalar('key1', false));

		$this->expectException(MissingAttributeFieldException::class);
		$dataSet->reqScalar('key3', false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptScalar() {
		$dataSet = new DataSet(['key1' => 'value-1', 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertEquals('value-1',
				$dataSet->optScalar('key1', false));

		$dataSet->optScalar('key3', false);

		$this->expectException(InvalidAttributeException::class);
		$dataSet->optScalar('key2', false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqBool() {
		$dataSet = new DataSet(['key1' => true, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(true,
				$dataSet->reqBool('key1', false, false));

		$this->expectException(InvalidAttributeException::class);
		$dataSet->reqBool('key2', false, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqBoolMissingAttributes() {
		$dataSet = new DataSet(['key1' => true]);
		$this->assertSame(true,
				$dataSet->reqBool('key1', false, false));

		$this->expectException(MissingAttributeFieldException::class);
		$dataSet->reqBool('key3', false, false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptBool() {
		$dataSet = new DataSet(['key1' => false, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(false,
				$dataSet->optBool('key1', false, true, false));

		$dataSet->optBool('key3', false, true, false);

		$this->expectException(InvalidAttributeException::class);
		$dataSet->optBool('key2', false, true, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqNumeric() {
		$dataSet = new DataSet(['key1' => 10, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10,
				$dataSet->reqNumeric('key1', false));

		$this->expectException(InvalidAttributeException::class);
		$dataSet->reqNumeric('key2', false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqNumericMissingAttributes() {
		$dataSet = new DataSet(['key1' => '10']);
		$this->assertSame('10',
				$dataSet->reqNumeric('key1', false));

		$this->expectException(MissingAttributeFieldException::class);
		$dataSet->reqNumeric('key3', false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptNumeric() {
		$dataSet = new DataSet(['key1' => 10.01, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10.01,
				$dataSet->optNumeric('key1', false, true));

		$dataSet->optNumeric('key3', false, true);

		$this->expectException(InvalidAttributeException::class);
		$dataSet->optNumeric('key2', false, true);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqInt() {
		$dataSet = new DataSet(['key1' => 10, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10,
				$dataSet->reqInt('key1', false));

		$this->expectException(InvalidAttributeException::class);
		$dataSet->reqInt('key2', false, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqIntMissingAttributes() {
		$dataSet = new DataSet(['key1' => 10]);
		$this->assertSame(10,
				$dataSet->reqInt('key1', false));

		$this->expectException(MissingAttributeFieldException::class);
		$dataSet->reqInt('key3', false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptInt() {
		$dataSet = new DataSet(['key1' => 10.01, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10,
				$dataSet->optInt('key1', false, true));

		$dataSet->optInt('key3', false, true);

		$this->expectException(InvalidAttributeException::class);
		$dataSet->optInt('key2', false, true);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqArray() {
		$dataSet = new DataSet(['key1' => ['val1' => 10], 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(['val1' => 10],
				$dataSet->reqArray('key1', 'int'));

		$this->expectException(InvalidAttributeException::class);
		$dataSet->reqArray('key2', 'int', false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqArrayMissingAttributes() {
		$dataSet = new DataSet(['key1' => [10]]);
		$this->assertSame([10],
				$dataSet->reqArray('key1', 'int'));

		$this->expectException(MissingAttributeFieldException::class);
		$dataSet->reqArray('key3', 'int');
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptArray() {
		$dataSet = new DataSet(['key1' => [10], 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame([10], $dataSet->optArray('key1', 'int', [11]));

		$this->assertSame([13], $dataSet->optArray('key3', 'int', [13]));

		$this->expectException(InvalidAttributeException::class);
		$dataSet->optArray('key2', 'int');
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqScalarArray() {
		$dataSet = new DataSet(['key1' => [10], 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame([10],
				$dataSet->reqScalarArray('key1', false));

		$this->expectException(InvalidAttributeException::class);
		$dataSet->reqScalarArray('key2', false, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqScalarArrayMissingAttributes() {
		$dataSet = new DataSet(['key1' => [10]]);
		$this->assertSame([10],
				$dataSet->reqScalarArray('key1', false));

		$this->expectException(MissingAttributeFieldException::class);
		$dataSet->reqScalarArray('key3', false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptScalarArray() {
		$dataSet = new DataSet(['key1' => [10], 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame([10], $dataSet->optScalarArray('key1', [11]));

		$this->assertSame([12], $dataSet->optScalarArray('key3', [12]));

		$this->expectException(InvalidAttributeException::class);
		$dataSet->optScalarArray('key2');
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqStringValueObject() {
		$dataSet = new DataSet(['key1' => 'a10' ,'key2' => new IntValueObjectMock('10')]);
		$this->assertTypeSafeEquals(new StringValueObjectMock('a10'),
				$dataSet->reqStringValueObject('key1', StringValueObjectMock::class));

		$this->expectException(InvalidAttributeException::class);
		$dataSet->reqStringValueObject('key2', StringValueObjectMock::class, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqStringValueObjectMissingAttributes() {
		$dataSet = new DataSet(['key1' => new StringValueObjectMock('10')]);
		$this->assertSame('10',
				$dataSet->reqStringValueObject('key1', SubStringValueObjectMock::class)->toScalar());

		$this->expectException(MissingAttributeFieldException::class);
		$dataSet->reqStringValueObject('key3', StringValueObjectMock::class);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqStringValueObjectNotNullable() {
		$dataSet = new DataSet(['key1' => new SubStringValueObjectMock('10'), 'key2' => null]);
		$this->assertSame('10',
				$dataSet->reqStringValueObject('key1', SubStringValueObjectMock::class)->toScalar());

		$this->expectException(InvalidAttributeException::class);
		$dataSet->reqStringValueObject('key2', StringValueObjectMock::class, false);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptStringValueObject() {
		$dataSet = new DataSet([
				'key1' => 10.01,
				'key2' => StringBackedEnumMock::VALUE1,
				'key3' => new StringValueObjectMock('11')]);
		$this->assertSame('10.01',
				$dataSet->optStringValueObject('key1', StringValueObjectMock::class, null)->toScalar());
		$this->assertSame('value-1',
				$dataSet->optStringValueObject('key2', StringValueObjectMock::class, null)->toScalar());
		$this->assertSame('11',
				$dataSet->optStringValueObject('key3', StringValueObjectMock::class, null)->toScalar());

	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptStringValueObjectIllegalValue() {
		$dataSet = new DataSet(['invalid' => '12345678910']);

		try {
			$dataSet->optStringValueObject('invalid', StringValueObjectMock::class, null);
			$this->fail();
		} catch (InvalidAttributeException $e) {
			$this->assertInstanceOf(IllegalValueException::class, $e->getPrevious());
		}

	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptStringValueObjectInvalidValObject() {
		$dataSet = new DataSet(['invalid' => new IntValueObjectMock('10')]);

		$this->expectException(InvalidAttributeException::class);
		$dataSet->optStringValueObject('invalid', StringValueObjectMock::class, null);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptStringValueObjectDefaultValue() {
		$dataSet = new DataSet(['key1' => null]);
		//key with value null will not be changed to default value
		$this->assertTypeSafeEquals(null,
				$dataSet->optStringValueObject('key1', StringValueObjectMock::class, new StringValueObjectMock('holeradio')));

		//missing keys will be added with default value
		$this->assertTypeSafeEquals(new StringValueObjectMock('holeradio'),
				$dataSet->optStringValueObject('missing', StringValueObjectMock::class, new StringValueObjectMock('holeradio')));

		//default value need to be the same Type as the given typeName or null
		$this->assertTypeSafeEquals(null,
				$dataSet->optStringValueObject('missingNullDefault', StringValueObjectMock::class, null));

		//default value need to be the same Type as the given typeName
		$this->expectException(\InvalidArgumentException::class);
		$dataSet->optStringValueObject('missingInvalidDefault', StringValueObjectMock::class, 12);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptStringValueObjectNotNullable() {
		$dataSet = new DataSet(['key1' => new SubStringValueObjectMock('10'), 'key2' => null]);
		$this->assertSame('10',
				$dataSet->optStringValueObject('key1', SubStringValueObjectMock::class)->toScalar());

		$this->expectException(InvalidAttributeException::class);
		$dataSet->optStringValueObject('key2', StringValueObjectMock::class, null,false);
	}


	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @throws IllegalValueException
	 */
	function testReqIntValueObject() {
		$dataSet = new DataSet(['key1' => 10 ,'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertTypeSafeEquals(new IntValueObjectMock(10),
				$dataSet->reqIntValueObject('key1', IntValueObjectMock::class));

		$this->expectException(InvalidAttributeException::class);
		$dataSet->reqIntValueObject('key2', IntValueObjectMock::class, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqIntValueObjectMissingAttributes() {
		$dataSet = new DataSet(['key1' => new SubIntValueObjectMock('10')]);
		$this->assertSame(10,
				$dataSet->reqIntValueObject('key1', SubIntValueObjectMock::class)->toScalar());

		$this->expectException(MissingAttributeFieldException::class);
		$dataSet->reqIntValueObject('key3', IntValueObjectMock::class);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptIntValueObject() {
		$dataSet = new DataSet(['key1' => 10, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10,
				$dataSet->optIntValueObject('key1', IntValueObjectMock::class, null)->toScalar());

		$dataSet->optIntValueObject('key3', IntValueObjectMock::class, null );

		$this->expectException(InvalidAttributeException::class);
		$dataSet->optIntValueObject('key2', IntValueObjectMock::class, null);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @throws IllegalValueException
	 */
	function testReqFloatValueObject() {
		$dataSet = new DataSet(['key1' => 10.01 ,'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertTypeSafeEquals(new FloatValueObjectMock(10.01),
				$dataSet->reqFloatValueObject('key1', FloatValueObjectMock::class));

		$this->expectException(InvalidAttributeException::class);
		$dataSet->reqFloatValueObject('key2', FloatValueObjectMock::class, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @throws IllegalValueException
	 */
	function testReqFloatValueObjectMissingAttributes() {
		$dataSet = new DataSet(['key1' => new FloatValueObjectMock(10.01)]);
		$this->assertSame(10.01,
				$dataSet->reqFloatValueObject('key1', FloatValueObjectMock::class)->toScalar());

		$this->expectException(MissingAttributeFieldException::class);
		$dataSet->reqFloatValueObject('key3', FloatValueObjectMock::class);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptFloatValueObject() {
		$dataSet = new DataSet(['key1' => 10.01, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(10.01,
				$dataSet->optFloatValueObject('key1', FloatValueObjectMock::class, null)->toScalar());

		$dataSet->optFloatValueObject('key3', FloatValueObjectMock::class, null );

		$this->expectException(InvalidAttributeException::class);
		$dataSet->optFloatValueObject('key2', FloatValueObjectMock::class, null);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqBoolValueObject() {
		$dataSet = new DataSet(['key1' => false ,'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertTypeSafeEquals(new BoolValueObjectMock(false),
				$dataSet->reqBoolValueObject('key1', BoolValueObjectMock::class));

		$this->expectException(InvalidAttributeException::class);
		$dataSet->reqBoolValueObject('key2', BoolValueObjectMock::class, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqBoolValueObjectMissingAttributes() {
		$dataSet = new DataSet(['key1' => new BoolValueObjectMock(true)]);
		$this->assertSame(true,
				$dataSet->reqBoolValueObject('key1', BoolValueObjectMock::class)->toScalar());

		$this->expectException(MissingAttributeFieldException::class);
		$dataSet->reqBoolValueObject('key3', BoolValueObjectMock::class);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptBoolValueObject() {
		$dataSet = new DataSet(['key1' => true, 'key2' => StringBackedEnumMock::VALUE1]);
		$this->assertSame(true,
				$dataSet->optBoolValueObject('key1', BoolValueObjectMock::class, null)->toScalar());

		$dataSet->optBoolValueObject('key3', BoolValueObjectMock::class, null );

		$this->expectException(InvalidAttributeException::class);
		$dataSet->optBoolValueObject('key2', BoolValueObjectMock::class, null);
	}
}