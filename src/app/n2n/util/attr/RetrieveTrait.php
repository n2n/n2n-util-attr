<?php

namespace n2n\util\attr;

use n2n\util\type\attrs\AttributePath;
use n2n\util\type\TypeConstraint;
use n2n\util\ex\IllegalStateException;

trait RetrieveTrait {
	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	protected abstract function retrieve(AttributePath|\Stringable|array|string|null $path,
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
	public function req($path, ?TypeConstraint $type = null) {
		return $this->retrieve($path, $type, true);
	}

	/**
	 * @param mixed $path
	 * @param TypeConstraint|null $type
	 * @param mixed $defaultValue
	 * @throws InvalidAttributeException
	 * all Type based opt functions are served by {@link BasicReqAndOptTrait}
	 * all ValueObjectType based opt functions are served by {@link ValueObjReqAndOptTrait}
	 */
	public function opt($path, ?TypeConstraint $type = null, $defaultValue = null) {
		try {
			return $this->retrieve($path, $type, false, $defaultValue);
		} catch (MissingAttributeFieldException $e) {
			throw new IllegalStateException('opt() must ignore missing attributes.', previous: $e);
		}
	}
}