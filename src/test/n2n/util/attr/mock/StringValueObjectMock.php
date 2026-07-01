<?php

namespace n2n\util\attr\mock;
use n2n\spec\valobj\scalar\StringValueObject;

class StringValueObjectMock implements StringValueObject {
	public function __construct(public string $value) {
	}

	function toScalar(): string {
		return $this->value;
	}
}