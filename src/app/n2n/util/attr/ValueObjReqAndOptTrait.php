<?php

namespace n2n\util\attr;

use n2n\util\type\TypeUtils;
use n2n\util\type\TypeConstraints;
use n2n\util\type\TypeConstraint;
use n2n\spec\valobj\scalar\StringValueObject;
use n2n\spec\valobj\scalar\IntValueObject;
use n2n\spec\valobj\scalar\FloatValueObject;
use n2n\spec\valobj\scalar\BoolValueObject;

/**
 */
trait ValueObjReqAndOptTrait {
	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	protected function reqValueObject(string $expectedClass, $path, string $typeName, bool $nullAllowed = false) {
		if (!TypeUtils::isTypeA($typeName, $expectedClass)) {
			throw new InvalidAttributeException('Property \'' . $path
					. '\' contains invalid value. Reason: Type is not ' . $expectedClass, 0, null
			);
		}

		$constraint = $nullAllowed
				? TypeConstraint::createSimple($typeName, true)
				: TypeConstraints::type($typeName);

		return $this->req($path, $constraint);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	protected function optValueObject(string $expectedClass, $path, string $typeName,
			$defaultValue = null, bool $nullAllowed = true) {
		if (!TypeUtils::isTypeA($typeName, $expectedClass)) {
			throw new InvalidAttributeException('Property \'' . $path
					. '\' contains invalid value. Reason: Type is not ' . $expectedClass, 0, null
			);
		}

		$constraint = $nullAllowed
				? TypeConstraint::createSimple($typeName, true)
				: TypeConstraints::type($typeName);

		return $this->opt($path, $constraint, $defaultValue);
	}


	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqStringValueObject($path, string $typeName, bool $nullAllowed = false) {
		return $this->reqValueObject(StringValueObject::class, $path, $typeName, $nullAllowed);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	public function optStringValueObject($path, string $typeName, $defaultValue = null, bool $nullAllowed = true) {
		return $this->optValueObject(StringValueObject::class, $path, $typeName, $defaultValue, $nullAllowed);
	}


	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqIntValueObject($path, string $typeName, bool $nullAllowed = false) {
		return $this->reqValueObject(IntValueObject::class, $path, $typeName, $nullAllowed);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	public function optIntValueObject($path, string $typeName, $defaultValue = null, bool $nullAllowed = true) {
		return $this->optValueObject(IntValueObject::class, $path, $typeName, $defaultValue, $nullAllowed);
	}


	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqFloatValueObject($path, string $typeName, bool $nullAllowed = false) {
		return $this->reqValueObject(FloatValueObject::class, $path, $typeName, $nullAllowed);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	public function optFloatValueObject($path, string $typeName, $defaultValue = null, bool $nullAllowed = true) {
		return $this->optValueObject(FloatValueObject::class, $path, $typeName, $defaultValue, $nullAllowed);
	}


	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqBoolValueObject($path, string $typeName, bool $nullAllowed = false) {
		return $this->reqValueObject(BoolValueObject::class, $path, $typeName, $nullAllowed);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	public function optBoolValueObject($path, string $typeName, $defaultValue = null, bool $nullAllowed = true) {
		return $this->optValueObject(BoolValueObject::class, $path, $typeName, $defaultValue, $nullAllowed);
	}

}