<?php

namespace n2n\util\attr\mock;
use n2n\spec\valobj\scalar\StringValueObject;
use n2n\spec\valobj\err\IllegalValueException;

class StringValueObjectMock implements StringValueObject , \Stringable {
	public function __toString(): string {
		return $this->toScalar();
	}
	public function __construct(public string $value) {
		if (mb_strlen($this->value) > 10)
			throw new IllegalValueException('String value is too long');
	}

	function toScalar(): string {
		return $this->value;
	}
}