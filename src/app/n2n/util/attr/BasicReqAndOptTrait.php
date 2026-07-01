<?php

namespace n2n\util\attr;

use n2n\util\type\TypeConstraint;
use n2n\util\EnumUtils;

/**
 */
trait BasicReqAndOptTrait {
	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqScalar($path, bool $nullAllowed = false) {
		return $this->req($path, TypeConstraint::createSimple('scalar', $nullAllowed));
	}

	/**
	 * @throws InvalidAttributeException
	 */
	public function optScalar($path, $defaultValue = null, bool $nullAllowed = true) {
		return $this->opt($path, TypeConstraint::createSimple('scalar', $nullAllowed), $defaultValue);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqString($path, bool $nullAllowed = false, bool $lenient = true) {
		if (!$lenient) {
			return $this->req($path, TypeConstraint::createSimple('string', $nullAllowed));
		}

		if (null !== ($value = $this->reqScalar($path, $nullAllowed))) {
			return (string) $value;
		}

		return null;
	}

	/**
	 * @throws InvalidAttributeException
	 */
	public function optString($path, $defaultValue = null, $nullAllowed = true, bool $lenient = true) {
		if (!$lenient) {
			return $this->opt($path, TypeConstraint::createSimple('string', $nullAllowed), $defaultValue);
		}

		if (null !== ($value = $this->optScalar($path, $defaultValue, $nullAllowed))) {
			return (string) $value;
		}

		return null;
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqBool($path, bool $nullAllowed = false, $lenient = true) {
		if (!$lenient) {
			return $this->req($path, TypeConstraint::createSimple('bool', $nullAllowed));
		}

		if (null !== ($value = $this->reqScalar($path, $nullAllowed))) {
			return (bool) $value;
		}

		return null;
	}

	/**
	 * @throws InvalidAttributeException
	 */
	public function optBool($path, $defaultValue = null, bool $nullAllowed = true, $lenient = true) {
		if (!$lenient) {
			return $this->opt($path, TypeConstraint::createSimple('bool', $nullAllowed), $defaultValue);
		}

		if (null !== ($value = $this->optScalar($path, $defaultValue, $nullAllowed))) {
			return (bool) $value;
		}

		return $defaultValue;
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqNumeric($path, bool $nullAllowed = false) {
		return $this->req($path, TypeConstraint::createSimple('numeric', $nullAllowed));
	}

	/**
	 * @throws InvalidAttributeException
	 */
	public function optNumeric($path, $defaultValue = null, bool $nullAllowed = true) {
		return $this->opt($path, TypeConstraint::createSimple('numeric', $nullAllowed), $defaultValue);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqInt($path, bool $nullAllowed = false, $lenient = true) {
		if (!$lenient) {
			return $this->req($path, TypeConstraint::createSimple('int', $nullAllowed));
		}

		if (null !== ($value = $this->reqNumeric($path, $nullAllowed))) {
			return (int) $value;
		}

		return null;
	}

	/**
	 * @throws InvalidAttributeException
	 */
	public function optInt($path, $defaultValue = null, bool $nullAllowed = true, $lenient = true) {
		if (!$lenient) {
			return $this->opt($path, TypeConstraint::createSimple('int', $nullAllowed), $defaultValue);
		}

		if (null !== ($value = $this->optNumeric($path, $defaultValue))) {
			return (int) $value;
		}

		return null;
	}

	/**
	 * @param mixed $path must be compatible with {@link AttributePath::create()}.
	 * @param array $allowedValues
	 * @param bool $nullAllowed
	 * @return mixed
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqEnum(mixed $path, array $allowedValues, bool $nullAllowed = false): mixed {
		return $this->getEnum($path, $allowedValues, true, null, $nullAllowed);
	}

	/**
	 * @param mixed $path must be compatible with {@link AttributePath::create()}.
	 * @param array $allowedValues
	 * @param mixed $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function optEnum(mixed $path, array $allowedValues, mixed $defaultValue = null, bool $nullAllowed = true): mixed {
		return $this->getEnum($path, $allowedValues, false, $defaultValue, $nullAllowed);
	}

	/**
	 * @param mixed $path must be compatible with {@link AttributePath::create()}.
	 * @param array $allowedValues
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	private function getEnum(mixed $path, array $allowedValues, bool $mandatory = true, mixed $defaultValue = null,
			bool $nullAllowed = false): mixed {
		$found = null;
		$value = $this->retrieve($path, null, $mandatory, $defaultValue, $found);
		if (!$found) return $defaultValue;

		if ($nullAllowed && $value === null) {
			return $value;
		}

		try {
			return EnumUtils::valueToPseudoUnit($value, $allowedValues);
		} catch (\InvalidArgumentException $e) {
			throw new InvalidAttributeException('Property \'' . $path
					. '\' contains invalid value. Reason: ' . $e->getMessage(), 0, $e);
		}
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqArray($path, $fieldType = null, bool $nullAllowed = false, $keyType = null) {
		return $this->req($path, TypeConstraint::createArrayLike('array', $nullAllowed, $fieldType, arrayKeyType: $keyType));
	}

	/**
	 * @throws InvalidAttributeException
	 */
	public function optArray($path, $fieldType = null, $defaultValue = [], bool $nullAllowed = false, $keyType = null) {
		return $this->opt($path, TypeConstraint::createArrayLike('array', $nullAllowed, $fieldType, arrayKeyType: $keyType), $defaultValue);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqScalarArray($path, bool $nullAllowed = false, bool $fieldNullAllowed = false) {
		return $this->reqArray($path, TypeConstraint::createSimple('scalar', $fieldNullAllowed), $nullAllowed);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	public function optScalarArray($path, $defaultValue = [], bool $nullAllowed = false, bool $fieldNullAllowed = false) {
		return $this->optArray($path, TypeConstraint::createSimple('scalar', $fieldNullAllowed), $defaultValue, $nullAllowed);
	}
}