<?php

namespace n2n\util\attr\trait;

use n2n\util\type\TypeUtils;
use n2n\spec\valobj\scalar\StringValueObject;
use n2n\spec\valobj\scalar\IntValueObject;
use n2n\spec\valobj\scalar\FloatValueObject;
use n2n\spec\valobj\scalar\BoolValueObject;
use n2n\util\attr\MissingAttributeFieldException;
use n2n\util\attr\InvalidAttributeException;
use n2n\spec\valobj\err\IllegalValueException;
use ReflectionClass;
use InvalidArgumentException;
use n2n\util\attr\AttributePath;

/**
 */
trait ValueObjReqAndOptTrait {
	use RetrieveTrait;
	use BasicReqAndOptTrait;


	/**
	 * @throws InvalidAttributeException
	 */
	private function buildValueObject(string|int|float|bool|null $value, string $typeName, AttributePath $path): ?object {
		if ($value === null) {
			return null;
		}

		try {
			$class = new ReflectionClass($typeName);
			return $class->newInstance($value);
		} catch (\ReflectionException $e) {
			//should never happen because ensureTypeMatches ensure Type is correct
			throw new \InvalidArgumentException('Cloud not create object based on type name: ' . $typeName,
					previous: $e);
		} catch (IllegalValueException $e) {
			throw new InvalidAttributeException('Property "' . $path
					. '" contains invalid value. Reason: ' . $e->getMessage(), previous: $e);
		}
	}

	private function ensureTypeMatches(string $typeName, string $expectedTypeName, mixed $defaultValue = null): void {
		if (!TypeUtils::isTypeA($typeName, $expectedTypeName)) {
			throw new \InvalidArgumentException('Type must implement ' . $expectedTypeName . '. '
					. $typeName . ' given.');
		}

		if ($defaultValue !== null && !($defaultValue instanceof $typeName)) {
			throw new \InvalidArgumentException('Default value must implement ' . $typeName
					. ' or be null. Given: ' . TypeUtils::getTypeInfo($defaultValue) . '.');
		}
	}

	/**
	 * @template T
	 * @param class-string<T> $typeName
	 * @return T|null
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqStringValueObject($path, string $typeName, bool $nullAllowed = false, bool $lenient = true): mixed {
		$this->ensureTypeMatches($typeName, StringValueObject::class);

		$path = AttributePath::create($path);
		$value = $this->req($path, ($lenient ? null : $typeName));
		if ($value instanceof $typeName) {
			return $value;
		}
		$value = $this->reqString($path, $nullAllowed, $lenient);

		return $this->buildValueObject($value, $typeName, $path);
	}

	/**
	 * @template T
	 * @param class-string<T> $typeName
	 * @param T|null $defaultValue
	 * @return T|null
	 * @throws InvalidArgumentException
	 * @throws InvalidAttributeException
	 */
	public function optStringValueObject($path, string $typeName, mixed $defaultValue = null, bool $nullAllowed = true,
			bool $lenient = true): mixed {
		$this->ensureTypeMatches($typeName, StringValueObject::class, $defaultValue);
		$path = AttributePath::create($path);

		$value = $this->opt($path, ($lenient ? null : $typeName), $defaultValue);
		if ($value instanceof $typeName) {
			return $value;
		}
		$value = $this->optString($path, null, $nullAllowed, $lenient);

		return $this->buildValueObject($value, $typeName, $path);
	}


	/**
	 * @template T
	 * @param class-string<T> $typeName
	 * @return T|null
	 * @throws InvalidArgumentException
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqIntValueObject($path, string $typeName, bool $nullAllowed = false,
			bool $lenient = true): mixed {
		$this->ensureTypeMatches($typeName, IntValueObject::class);
		$path = AttributePath::create($path);
		
		$value = $this->req($path, ($lenient ? null : $typeName));
		if ($value instanceof $typeName) {
			return $value;
		}
		$value = $this->reqInt($path, $nullAllowed, $lenient);

		return $this->buildValueObject($value, $typeName, $path);
	}

	/**
	 * @template T
	 * @param class-string<T> $typeName
	 * @return T|null
	 * @throws InvalidArgumentException
	 * @throws InvalidAttributeException
	 */
	public function optIntValueObject($path, string $typeName, $defaultValue = null, bool $nullAllowed = true,
			bool $lenient = true): mixed {
		$this->ensureTypeMatches($typeName, IntValueObject::class, $defaultValue);
		$path = AttributePath::create($path);

		$value = $this->opt($path, ($lenient ? null : $typeName));
		if ($value instanceof $typeName) {
			return $value;
		}
		$value = $this->optInt($path, $nullAllowed, $lenient);

		return $this->buildValueObject($value, $typeName, $path);
	}


	/**
	 * @template T
	 * @param class-string<T> $typeName
	 * @return T|null
	 * @throws InvalidArgumentException
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqFloatValueObject($path, string $typeName, bool $nullAllowed = false,
			bool $lenient = true): mixed {
		$this->ensureTypeMatches($typeName, FloatValueObject::class);
		$path = AttributePath::create($path);

		$value = $this->req($path, ($lenient ? null : $typeName));
		if ($value instanceof $typeName) {
			return $value;
		}
		$value = $this->reqFloat($path, $nullAllowed, $lenient);

		return $this->buildValueObject($value, $typeName, $path);
	}

	/**
	 * @template T
	 * @param class-string<T> $typeName
	 * @return T|null
	 * @throws InvalidArgumentException
	 * @throws InvalidAttributeException
	 */
	public function optFloatValueObject($path, string $typeName, $defaultValue = null, bool $nullAllowed = true,
			bool $lenient = true): mixed {
		$this->ensureTypeMatches($typeName, FloatValueObject::class, $defaultValue);
		$path = AttributePath::create($path);

		$value = $this->opt($path, ($lenient ? null : $typeName));
		if ($value instanceof $typeName) {
			return $value;
		}
		$value = $this->optFloat($path, $nullAllowed, $lenient);

		return $this->buildValueObject($value, $typeName, $path);
	}


	/**
	 * @template T
	 * @param class-string<T> $typeName
	 * @return T|null
	 * @throws InvalidArgumentException
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqBoolValueObject($path, string $typeName, bool $nullAllowed = false,
			bool $lenient = true): mixed {
		$this->ensureTypeMatches($typeName, BoolValueObject::class);
		$path = AttributePath::create($path);

		$value = $this->req($path, ($lenient ? null : $typeName));
		if ($value instanceof $typeName) {
			return $value;
		}
		$value = $this->reqBool($path, $nullAllowed, $lenient);

		return $this->buildValueObject($value, $typeName, $path);
	}

	/**
	 * @template T
	 * @param class-string<T> $typeName
	 * @return T|null
	 * @throws InvalidArgumentException
	 * @throws InvalidAttributeException
	 */
	public function optBoolValueObject($path, string $typeName, $defaultValue = null, bool $nullAllowed = true,
			bool $lenient = true): mixed {
		$this->ensureTypeMatches($typeName, BoolValueObject::class, $defaultValue);
		$path = AttributePath::create($path);

		$value = $this->opt($path, ($lenient ? null : $typeName));
		if ($value instanceof $typeName) {
			return $value;
		}
		$value = $this->optBool($path, $nullAllowed, $lenient);

		return $this->buildValueObject($value, $typeName, $path);
	}

}