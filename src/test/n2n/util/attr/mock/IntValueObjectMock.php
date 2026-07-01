<?php

namespace n2n\util\attr\mock;
use n2n\spec\valobj\scalar\IntValueObject;

class IntValueObjectMock implements IntValueObject {
	public function __construct(public int $value) {
	}

	function toScalar(): int {
		return $this->value;
	}
}