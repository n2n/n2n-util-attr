<?php
namespace n2n\util\attr\mock;

use n2n\spec\valobj\err\IllegalValueException;
use n2n\util\ex\ExUtils;
use n2n\spec\valobj\scalar\StringValueObject;

final class EmailMock implements StringValueObject, \Stringable, \JsonSerializable {

	public function __construct(private string $value) {
		IllegalValueException::assertTrue(
				strlen($value) >= 0
				&& strtolower($value) === $value
				&& false !== filter_var($value, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE),
				'Illegal e-mail value: ' . $this->value);
	}

	function equals(StringValueObject|string|null $stringValueObject): bool {
		if ($stringValueObject === null) {
			return false;
		}

		if (is_string($stringValueObject)) {
			return $stringValueObject === $this->value;
		}
		return $this->toScalar() === $stringValueObject->toScalar();
	}

	public function __toString(): string {
		return $this->toScalar();
	}

	function jsonSerialize(): string {
		return $this->toScalar();
	}

	function toScalar(): string {
		return $this->value;
	}

	static function from(string|\Stringable|null $value, bool $lenient = false): ?EmailMock {
		return ExUtils::try(fn () => self::checkedFrom($value, $lenient));
	}

	/**
	 * @throws IllegalValueException
	 */
	static function checkedFrom(string|\Stringable|null $value, bool $lenient = false): ?EmailMock {
		if ($value === null) {
			return null;
		}

		if ($lenient) {
			$value = strtolower(trim((string) $value));
		}

		if ($value === null || ($value === '' && $lenient)) {
			return null;
		}

		return new EmailMock($value);
	}
}