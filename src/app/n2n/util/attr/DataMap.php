<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\util\type\attrs;

use n2n\util\type\TypeConstraint;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use n2n\util\col\ArrayUtils;
use n2n\util\StringUtils;
use n2n\util\type\TypeUtils;
use n2n\util\type\TypeConstraints;
use n2n\util\ex\NotYetImplementedException;
use n2n\util\EnumUtils;
use n2n\util\ex\IllegalStateException;

class DataMap implements AttributeReader, AttributeWriter, \JsonSerializable {

	private array $data;

	/**
	 *
	 * @param array $attrs
	 */
	public function __construct(?array $data = null) {
		$this->data = (array) $data;
	}

	/**
	 *
	 * @return boolean
	 */
	public function isEmpty() {
		return empty($this->data);
	}


	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	private function findR(&$attrs, array $nextNames, array $prevNames, $mandatory, &$found) {
		if (empty($nextNames)) {
			$found = true;
			return $attrs;
		}
		
		$nextName = array_shift($nextNames);
		
		if (!is_array($attrs)) {
			throw new InvalidAttributeException('Property \'' . new AttributePath($prevNames)
					. '\' must be an array. ' . TypeUtils::getTypeInfo($attrs) . ' given.');
		}
		
		$prevNames[] = $nextName;
		
		if (!array_key_exists($nextName, $attrs)) {
			if (!$mandatory) {
				$found = false;
				return null;
			}
			throw new MissingAttributeFieldException('Missing property: '  . new AttributePath($prevNames));
		}
		
		return $this->findR($attrs[$nextName], $nextNames, $prevNames, $mandatory, $found);
		
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	private function retrieve($path, $type, $mandatory, $defaultValue = null, &$found = null) {
		$attributePath = AttributePath::create($path);
		$typeConstraint = TypeConstraint::build($type);
		
		$value = $this->findR($this->data, $attributePath->toArray(), array(), $mandatory, $found);
		
		if (!$found) return $defaultValue;
		
		if ($typeConstraint === null) {
			return $value;
		}
		
		try {
			return $typeConstraint->validate($value);
		} catch (ValueIncompatibleWithConstraintsException $e) {
			throw new InvalidAttributeException('Property contains invalid value: ' . $attributePath, 0, $e);
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\util\type\attrs\AttributeReader::containsAttribute()
	 */
	function containsAttribute(AttributePath $path): bool {
		return $this->has($path);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\util\type\attrs\AttributeReader::readAttribute()
	 */
	function readAttribute(AttributePath $path, ?TypeConstraint $typeConstraint = null, bool $mandatory = true, 
			mixed $defaultValue = null): mixed {
		if ($mandatory) {
			return $this->req($path, $typeConstraint);
		}
		
		return $this->opt($path, $typeConstraint, $defaultValue);
	}

	function writeAttribute(AttributePath $path, mixed $value): void {
		$this->set($path, $value);
	}

	function removeAttribute(AttributePath $path): bool {
		throw new NotYetImplementedException();
	}

	/**
	 * @param array $paths
	 * @param \Closure $closure
	 * @return DataMap
	 */
	function mapStrings(array $paths, \Closure $closure) {
		foreach (AttributePath::createArray($paths) as $path) {
			$this->mapString($path, $closure);
		}

		return $this;
	}

	/**
	 * @param $path
	 * @param \Closure $closure
	 * @return DataMap
	 */
	function mapString($path, \Closure $closure) {
		$path = AttributePath::create($path);

		$found = false;
		$value = $this->retrieve($path, TypeConstraints::string(true), false, null, $found);

		if (!$found || $value === null) {
			return $this;
		}

		$this->set($path, $closure($value));

		return $this;
	}

	/**
	 * @param $path
	 * @param bool $simpleWhitespacesOnly
	 * @return DataMap
	 */
	function cleanString($path, bool $simpleWhitespacesOnly = true) {
		$this->mapString($path, fn ($value) => StringUtils::clean($value, $simpleWhitespacesOnly));
		return $this;
	}

	/**
	 * @param array $paths
	 * @param bool $simpleWhitespacesOnly
	 * @return DataMap
	 */
	function cleanStrings(array $paths, bool $simpleWhitespacesOnly = true) {
		$paths = AttributePath::createArray($paths);

		foreach ($paths as $path) {
			$this->cleanString($path, $simpleWhitespacesOnly);
		}

		return $this;
	}

	/**
	 * @param string|AttributePath $path
	 * @return bool
	 */
	function has($path) {
		$found = false;
		$this->retrieve($path, null, false, null, $found);
		return $found;
	}

	/**
	 * @param mixed $path
	 * @param TypeConstraint $typeConstraint
	 * @return mixed
	 * @throws MissingAttributeFieldException
	 * @throws InvalidAttributeException
	 */
	public function req($path, $type = null) {
		return $this->retrieve($path, $type, true);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	public function opt($path, $type = null, $defaultValue = null) {
		try {
			return $this->retrieve($path, $type, false, $defaultValue);
		} catch (MissingAttributeFieldException $e) {
			throw new IllegalStateException('opt() must ignore missing attributes.', previous: $e);
		}
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqScalar($path, bool $nullAllowed = false) {
		return $this->req($path, TypeConstraint::createSimple('scalar', $nullAllowed));
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function optScalar($path, $defaultValue = null, bool $nullAllowed = true) {
		return $this->opt($path, TypeConstraint::createSimple('scalar', $nullAllowed));
	}

	public function getString($path, bool $mandatory = true, $defaultValue = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->reqString($path, $nullAllowed);
		}
		
		return $this->optString($path, $defaultValue, $nullAllowed);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqString($name, bool $nullAllowed = false, bool $lenient = true) {
		if (!$lenient) {
			return $this->req($name, TypeConstraint::createSimple('string', $nullAllowed));
		}
		
		if (null !== ($value = $this->reqScalar($name, $nullAllowed))) {
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
	public function reqArray($name, $fieldType = null, bool $nullAllowed = false, $keyType = null) {
		return $this->req($name, TypeConstraint::createArrayLike('array', $nullAllowed, $fieldType, arrayKeyType: $keyType));
	}

	/**
	 * @throws InvalidAttributeException
	 */
	public function optArray($name, $fieldType = null, $defaultValue = [], bool $nullAllowed = false, $keyType = null) {
		return $this->opt($name, TypeConstraint::createArrayLike('array', $nullAllowed, $fieldType, arrayKeyType: $keyType), $defaultValue);
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqScalarArray($name, bool $nullAllowed = false, bool $fieldNullAllowed = false) {
		return $this->reqArray($name, TypeConstraint::createSimple('scalar', $fieldNullAllowed), $nullAllowed);
	}

	/**
	 * @throws InvalidAttributeException
	 */
	public function optScalarArray($name, $defaultValue = [], bool $nullAllowed = false, bool $fieldNullAllowed = false) {
		return $this->optArray($name, TypeConstraint::createSimple('scalar', $fieldNullAllowed), $defaultValue, $nullAllowed);
	}
	
	/**
	 * @param string|AttributePath|string[] $path
	 * @param mixed $defaultValue
	 * @param bool $nullAllowed
	 * @return \n2n\util\type\attrs\Attributes|null
	 */
	public function reqDataSet($path, bool $nullAllowed = false) {
		if (null !== ($array = $this->reqArray($path, null, $nullAllowed))) {
			return new Attributes($array);
		}
		
		return null;
	}
	
	/**
	 * @param string|AttributePath|string[] $path
	 * @param mixed $defaultValue
	 * @param bool $nullAllowed
	 */
	public function optDataSet($path, $defaultValue = null, bool $nullAllowed = true) {
		if (null !== ($array = $this->optArray($path, null, $defaultValue, $nullAllowed))) {
			return new Attributes($array);
		}
		
		return null;
	}
	
	
	
	/**
	 * @param string|AttributePath|string[] $path
	 * @param mixed $defaultValue
	 * @param bool $nullAllowed
	 * @return \n2n\util\type\attrs\DataMap|null
	 */
	public function reqDataMap($path, bool $nullAllowed = false) {
		if (null !== ($array = $this->reqArray($path, null, $nullAllowed))) {
			return new DataMap($array);
		}
		
		return null;
	}
	
	/**
	 *
	 * @param mixed $path must be compatible with {@link AttributePath::create()}.
	 * @param mixed $defaultValue
	 * @param bool $nullAllowed
	 * @return \n2n\util\type\attrs\DataMap|null
	 */
	public function optDataMap(mixed $path, $defaultValue = null, bool $nullAllowed = true) {
		if (null !== ($array = $this->optArray($path, null, $defaultValue, $nullAllowed))) {
			return new DataMap($array);
		}
		
		return null;
	}
	
	/**
	 * @param string $name
	 * @param mixed $key scalar
	 */
	public function removeKey(string $name, $key) {
		if ($this->hasKey($name, $key)) {
			unset($this->data[$name][$key]);
		}
	}
	/**
	 *
	 * @param mixed $path must be compatible with {@link AttributePath::create()}.
	 * @param mixed $value;
	 */
	public function set(mixed $path, mixed $value): static {
		$names = AttributePath::create($path)->toArray();
		
		$data = &$this->data;
		while (true) {
			$name = array_shift($names);
			if (empty($names)) {
				$data[$name] = $value;
				return $this;
			}
			
			if (!isset($data[$name]) || !is_array($data[$name])) {
				$data[$name] = [];
			}
			
			$data = &$data[$name];
		}

		return $this;
	}
	
	/**
	 *
	 * @param array $data
	 */
	public function setAll(array $data) {
		$this->data = $data;
	}
	/**
	 *
	 * @return array
	 */
	public function toArray() {
		return $this->data;
	}
	
	public function removeNulls(bool $recursive = false) {
		$this->removeNullsR($this->data, $recursive);
	}
	
	private function removeNullsR(array &$attrs, bool $recursive = false) {
		foreach ($attrs as $key => $value) {
			if (!isset($attrs[$key])) {
				unset($attrs[$key]);
			} else if ($recursive && is_array($attrs[$key])) {
				$this->removeNullsR($attrs[$key], true);
			}
		}
	}
	
	/**
	 *
	 * @param array $attrs
	 * @param array $attrs2
	 */
	protected function merge(array $attrs, array $attrs2) {
		foreach ($attrs2 as $key => $value) {
			if (is_numeric($key)) {
				$attrs[] = $attrs2[$key];
				continue;
			}
			
			if (!array_key_exists($key, $attrs)) {
				$attrs[$key] = $value;
				continue;
			}
			
			if (is_array($attrs[$key])) {
				$attrs[$key] = $this->merge($attrs[$key], $attrs2[$key]);
				continue;
			}
			
			$attrs[$key] = $value;
		}
		
		return $attrs;
	}
	/**
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize($this->data);
	}
	/**
	 *
	 * @param string $serialized
	 * @param \n2n\util\UnserializationFailedException
	 */
	public static function createFromSerialized($serialized) {
		$attrs = StringUtils::unserialize($serialized);
		if (!is_array($attrs)) $attrs = array();
		return new Attributes($attrs);
	}

	public function jsonSerialize(): array {
		return $this->data;
	}
}
