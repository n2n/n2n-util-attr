<?php
namespace n2n\util\attr;
use PHPUnit\Framework\TestCase;
use n2n\util\type\TypeConstraints;
use n2n\spec\valobj\err\IllegalValueException;

class DataMapValueObjectsTest extends TestCase {
	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReq() {
		$emailStr = 'hole@radio.ch';
		$dataMap = new DataMap(['email' => $emailStr]);
		$email = $dataMap->req('email',
				TypeConstraints::namedType(Email::class, false, true));
		$this->assertInstanceOf(Email::class, $email);
		$this->assertEquals($emailStr, $email);
	}

	/**
	 * @throws MissingAttributeFieldException
	 */
	function testReqEmptyValue() {
		$this->expectException(InvalidAttributeException::class);
		$emailStr = '';
		$dataMap = new DataMap(['email' => $emailStr]);
		$dataMap->req('email',
				TypeConstraints::namedType(Email::class, false, true));
	}

	/**
	 * @throws MissingAttributeFieldException|InvalidAttributeException
	 */
	function testReqInvalidValue() {
		$this->expectException(InvalidAttributeException::class);
		$emailStr = 'abcd';
		$dataMap = new DataMap(['email' => $emailStr]);
		$dataMap->req('email',
				TypeConstraints::namedType(Email::class, false, true));
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqValueObject() {
		$emailStr = 'hole@radio.ch';
		$dataMap = new DataMap(['email' => $emailStr]);
		$email = $dataMap->reqValueObject('email',Email::class);
		$this->assertInstanceOf(Email::class, $email);
		$this->assertEquals($emailStr, $email);
	}
	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqValueObjectInvalidType() {
		$this->expectException(InvalidAttributeException::class);
		$dataMap = new DataMap(['email' => 'hole@radio.ch']);
		$dataMap->reqValueObject('email',DataMap::class);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqValueObjectNull() {
		$emailStr = null;
		$dataMap = new DataMap(['email' => $emailStr]);
		$email = $dataMap->reqValueObject('email',Email::class, true);
		$this->assertNull($email);
	}


	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqValueObjectNotNull() {
		$this->expectException(InvalidAttributeException::class);
		$emailStr = null;
		$dataMap = new DataMap(['email' => $emailStr]);
		$dataMap->reqValueObject('email',Email::class);
	}

	/**
	 * @throws MissingAttributeFieldException
	 */
	function testReqValueObjectEmptyValue() {
		$this->expectException(InvalidAttributeException::class);
		$emailStr = '';
		$dataMap = new DataMap(['email' => $emailStr]);
		$dataMap->reqValueObject('email', Email::class);
	}

	/**
	 * @throws MissingAttributeFieldException
	 */
	function testReqValueObjectInvalidValue() {
		$this->expectException(InvalidAttributeException::class);
		$emailStr = 'abcd';
		$dataMap = new DataMap(['email' => $emailStr]);
		$dataMap->reqValueObject('email', Email::class);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testOptValueObject() {
		$emailStr = 'hole@radio.ch';
		$dataMap = new DataMap(['email' => $emailStr]);
		$email = $dataMap->optValueObject('email',Email::class);
		$this->assertInstanceOf(Email::class, $email);
		$this->assertEquals($emailStr, $email);
	}
	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testOptValueObjectInvalidType() {
		$this->expectException(InvalidAttributeException::class);

		$dataMap = new DataMap(['email' => 'hole@radio.ch']);
		$dataMap->optValueObject('email',DataMap::class);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testOptValueObjectNoKey() {
		$dataMap = new DataMap();
		$email = $dataMap->optValueObject('email',Email::class);
		$this->assertNull($email);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws IllegalValueException
	 */
	function testOptValueObjectDefaultValue() {
		$emailStr = 'hole@radio.ch';
		$dataMap = new DataMap();
		$email = $dataMap->optValueObject('email',Email::class, defaultValue: new Email($emailStr));
		$this->assertInstanceOf(Email::class, $email);
		$this->assertEquals($emailStr, $email);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptValueObjectNull() {
		$emailStr = null;
		$dataMap = new DataMap(['email' => $emailStr]);
		$email = $dataMap->optValueObject('email',Email::class, nullAllowed: true);
		$this->assertNull($email);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptValueObjectNotNull() {
		$this->expectException(InvalidAttributeException::class);
		$emailStr = null;
		$dataMap = new DataMap(['email' => $emailStr]);
		$dataMap->optValueObject('email',Email::class, nullAllowed: false);
	}

	/**
	 */
	function testOptValueObjectEmptyValue() {
		$this->expectException(InvalidAttributeException::class);
		$emailStr = '';
		$dataMap = new DataMap(['email' => $emailStr]);
		$dataMap->optValueObject('email', Email::class);
	}

	/**
	 */
	function testOptValueObjectInvalidValue() {
		$this->expectException(InvalidAttributeException::class);
		$emailStr = 'abcd';
		$dataMap = new DataMap(['email' => $emailStr]);
		$dataMap->optValueObject('email', Email::class);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqValueObjectArray() {
		$emailStr = 'hole@radio.ch';
		$dataMap = new DataMap(['emails' => ['email' => $emailStr, 'email2' => $emailStr]]);
		$emails = $dataMap->reqValueObjectArray('emails',Email::class);
		foreach ($emails as $email) {
			$this->assertInstanceOf(Email::class, $email);
			$this->assertEquals($emailStr, $email);
		}
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqValueObjectArrayNoValueObject() {
		$this->expectException(InvalidAttributeException::class);
		$emailStr = 'hole@radio.ch';
		$dataMap = new DataMap(['emails' => ['email' => $emailStr, 'email2' => $emailStr]]);
		$dataMap->reqValueObjectArray('emails',DataMap::class);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqValueObjectArrayNull() {
		$dataMap = new DataMap(['emails' => null]);
		$emails = $dataMap->reqValueObjectArray('emails',Email::class, true);
		$this->assertNull($emails);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqValueObjectArrayFieldNull() {
		$dataMap = new DataMap(['emails' => ['email' => null, 'email2' => null]]);
		$emails = $dataMap->reqValueObjectArray('emails',Email::class, fieldNullAllowed: true);
		foreach ($emails as $email) {
			$this->assertNull($email);
		}
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqValueObjectArrayNotNull() {
		$this->expectException(InvalidAttributeException::class);
		$dataMap = new DataMap(['emails' => null]);
		$dataMap->reqValueObjectArray('emails',Email::class, false);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReqValueObjectArrayFieldNotNull() {
		$this->expectException(InvalidAttributeException::class);
		$dataMap = new DataMap(['emails' => ['email' => null, 'email2' => null]]);
		$dataMap->reqValueObjectArray('emails',Email::class, fieldNullAllowed: false);
	}

	/**
	 * @throws MissingAttributeFieldException
	 */
	function testReqValueObjectArrayEmptyValue() {
		$this->expectException(InvalidAttributeException::class);
		$dataMap = new DataMap(['emails' => ['email' => '']]);
		$dataMap->reqValueObjectArray('emails', Email::class);
	}

	/**
	 * @throws MissingAttributeFieldException
	 */
	function testReqValueObjectArrayInvalidValue() {
		$this->expectException(InvalidAttributeException::class);
		$dataMap = new DataMap(['emails' => ['email' => 'abcd']]);
		$dataMap->reqValueObjectArray('emails', Email::class);
	}

	/**
	 * @throws MissingAttributeFieldException
	 * @throws InvalidAttributeException
	 */
	function testReqValueObjectArrayInvalidValueKeyType() {
		$emailStr = 'hole@radio.ch';
		$dataMap = new DataMap(['emails' => ['email' => $emailStr, 'email2' => $emailStr]]);
		$emails = $dataMap->reqValueObjectArray('emails',Email::class, keyType: TypeConstraints::string());
		foreach ($emails as $email) {
			$this->assertInstanceOf(Email::class, $email);
			$this->assertEquals($emailStr, $email);
		}
	}
	/**
	 * @throws MissingAttributeFieldException
	 * @throws InvalidAttributeException
	 */
	function testReqValueObjectArrayInvalidValueInvalidKeyType() {
		$this->expectException(InvalidAttributeException::class);
		$emailStr = 'hole@radio.ch';
		$dataMap = new DataMap(['emails' => ['email' => $emailStr, 'email2' => $emailStr]]);
		$dataMap->reqValueObjectArray('emails',Email::class, keyType: TypeConstraints::int());
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testOptValueObjectArray() {
		$emailStr = 'hole@radio.ch';
		$dataMap = new DataMap(['emails' => ['email' => $emailStr, 'email2' => $emailStr]]);
		$emails = $dataMap->optValueObjectArray('emails',Email::class);
		foreach ($emails as $email) {
			$this->assertInstanceOf(Email::class, $email);
			$this->assertEquals($emailStr, $email);
		}
	}
	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testOptValueObjectArrayInvalidType() {
		$this->expectException(InvalidAttributeException::class);
		$dataMap = new DataMap(['emails' => ['email' => 'hole@radio.ch']]);
		$dataMap->optValueObjectArray('emails',DataMap::class);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testOptValueObjectArrayNoKey() {
		$dataMap = new DataMap();
		$emails = $dataMap->optValueObjectArray('emails',Email::class);
		$this->assertEmpty($emails);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws IllegalValueException
	 */
	function testOptValueObjectArrayDefaultValue() {
		$emailStr = 'hole@radio.ch';
		$dataMap = new DataMap();
		$emails = $dataMap->optValueObjectArray('emails',Email::class, defaultValue: [new Email($emailStr)]);
		foreach ($emails as $email) {
			$this->assertInstanceOf(Email::class, $email);
			$this->assertEquals($emailStr, $email);
		}
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptValueObjectArrayNull() {
		$dataMap = new DataMap(['emails' => null]);
		$emails = $dataMap->optValueObjectArray('emails',Email::class, nullAllowed: true);
		$this->assertNull($emails);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptValueObjectArrayFieldNull() {
		$dataMap = new DataMap(['emails' => ['email' => null]]);
		$emails = $dataMap->optValueObjectArray('emails',Email::class, fieldNullAllowed: true);
		foreach ($emails as $email) {
			$this->assertNull($email);
		}
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptValueObjectArrayNotNull() {
		$this->expectException(InvalidAttributeException::class);

		$dataMap = new DataMap(['emails' => null]);
		$dataMap->optValueObjectArray('emails',Email::class, nullAllowed: false);
	}
	/**
	 * @throws InvalidAttributeException
	 */
	function testOptValueObjectArrayFieldNotNull() {
		$this->expectException(InvalidAttributeException::class);

		$dataMap = new DataMap(['emails' => ['email' => null]]);
		$dataMap->optValueObjectArray('emails',Email::class, fieldNullAllowed: false);
	}

	/**
	 */
	function testOptValueObjectArrayEmptyValue() {
		$this->expectException(InvalidAttributeException::class);
		$dataMap = new DataMap(['emails' => ['email' => '']]);
		$dataMap->optValueObjectArray('emails', Email::class);
	}

	/**
	 */
	function testOptValueObjectArrayInvalidValue() {
		$this->expectException(InvalidAttributeException::class);
		$dataMap = new DataMap(['emails' => ['email' => 'abcd']]);
		$dataMap->optValueObjectArray('emails', Email::class);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testOptValueObjectArrayInvalidValueKeyType() {
		$emailStr = 'hole@radio.ch';
		$dataMap = new DataMap(['emails' => ['email' => $emailStr, 'email2' => $emailStr]]);
		$emails = $dataMap->optValueObjectArray('emails',Email::class, keyType: TypeConstraints::string());
		foreach ($emails as $email) {
			$this->assertInstanceOf(Email::class, $email);
			$this->assertEquals($emailStr, $email);
		}
	}
	/**
	 * @throws InvalidAttributeException
	 */
	function testOptValueObjectArrayInvalidValueInvalidKeyType() {
		$this->expectException(InvalidAttributeException::class);
		$emailStr = 'hole@radio.ch';
		$dataMap = new DataMap(['emails' => ['email' => $emailStr, 'email2' => $emailStr]]);
		$dataMap->optValueObjectArray('emails',Email::class, keyType: TypeConstraints::int());
	}
}