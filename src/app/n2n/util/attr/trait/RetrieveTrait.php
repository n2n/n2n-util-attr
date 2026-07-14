<?php

namespace n2n\util\attr\trait;

use n2n\util\type\TypeConstraint;
use n2n\util\ex\IllegalStateException;
use ReflectionClass;
use n2n\util\attr\MissingAttributeFieldException;
use n2n\util\attr\InvalidAttributeException;
use InvalidArgumentException;

trait RetrieveTrait {
	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	protected abstract function retrieve(mixed $path,
			TypeConstraint|null|ReflectionClass|string $type,
			bool $mandatory, mixed $defaultValue = null, mixed &$found = null): mixed;

	/**
	 * @param mixed $path
	 * @param TypeConstraint|null $type
	 * @throws MissingAttributeFieldException
	 * @throws InvalidAttributeException
	 * all Type based req functions are served by {@link BasicReqAndOptTrait}
	 * all ValueObjectType based req functions are served by {@link ValueObjReqAndOptTrait}
	 */
	public function req(mixed $path, mixed $type = null): mixed {
		return $this->retrieve($path, $type, true);
	}

	/**
	 * @param mixed $path
	 * @param TypeConstraint|null $type
	 * @param mixed|null $defaultValue
	 * @throws InvalidAttributeException
	 * all Type based opt functions are served by {@link BasicReqAndOptTrait}
	 * all ValueObjectType based opt functions are served by {@link ValueObjReqAndOptTrait}
	 */
	public function opt(mixed $path, mixed $type = null, mixed $defaultValue = null): mixed {
		try {
			return $this->retrieve($path, $type, false, $defaultValue);
		} catch (MissingAttributeFieldException $e) {
			throw new IllegalStateException('opt() must ignore missing attributes.', previous: $e);
		}
	}
}