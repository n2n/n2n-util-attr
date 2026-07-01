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
namespace n2n\util\attr;

use n2n\util\type\TypeConstraint;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use n2n\util\StringUtils;
use n2n\util\type\TypeUtils;
use n2n\util\type\TypeConstraints;
use n2n\util\ex\NotYetImplementedException;
use n2n\util\attr\trait\RetrieveTrait;
use n2n\util\attr\trait\BasicReqAndOptTrait;
use n2n\util\attr\trait\ValueObjReqAndOptTrait;

class DataMap implements AttributeReader, AttributeWriter, \JsonSerializable {
	use RetrieveTrait;
	use BasicReqAndOptTrait;
	use ValueObjReqAndOptTrait;

	private array $data;

	/**
	 * @param array|null $data
	 */
	public function __construct(?array $data = null) {
		$this->data = (array) $data;
	}


	/**
	 *
	 * @return boolean
	 */
	public function isEmpty(): bool {
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
	private function retrieve($path, $type, $mandatory, $defaultValue = null, &$found = null): mixed {
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
	 * @see \n2n\util\attr\AttributeReader::containsAttribute()
	 */
	function containsAttribute(AttributePath $path): bool {
		return $this->has($path);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\util\attr\AttributeReader::readAttribute()
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
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function mapStrings(array $paths, \Closure $closure): static {
		foreach (AttributePath::createArray($paths) as $path) {
			$this->mapString($path, $closure);
		}

		return $this;
	}

	/**
	 * @param $path
	 * @param \Closure $closure
	 * @return DataMap
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function mapString($path, \Closure $closure): DataMap {
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
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function cleanString($path, bool $simpleWhitespacesOnly = true) {
		$this->mapString($path, fn ($value) => StringUtils::clean($value, $simpleWhitespacesOnly));
		return $this;
	}

	/**
	 * @param array $paths
	 * @param bool $simpleWhitespacesOnly
	 * @return DataMap
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
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
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function has($path) {
		$found = false;
		$this->retrieve($path, null, false, null, $found);
		return $found;
	}



	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function getString($path, bool $mandatory = true, $defaultValue = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->reqString($path, $nullAllowed);
		}
		
		return $this->optString($path, $defaultValue, $nullAllowed);
	}

	/**
	 * @param string|AttributePath|string[] $path
	 * @param bool $nullAllowed
	 * @return DataSet|null
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqDataSet($path, bool $nullAllowed = false): ?DataSet {
		if (null !== ($array = $this->reqArray($path, null, $nullAllowed))) {
			return new DataSet($array);
		}
		
		return null;
	}

	/**
	 * @param string|AttributePath|string[] $path
	 * @param mixed $defaultValue
	 * @param bool $nullAllowed
	 * @throws InvalidAttributeException
	 */
	public function optDataSet($path, $defaultValue = null, bool $nullAllowed = true): ?DataSet {
		if (null !== ($array = $this->optArray($path, null, $defaultValue, $nullAllowed))) {
			return new DataSet($array);
		}
		
		return null;
	}


	/**
	 * @param string|AttributePath|string[] $path
	 * @param bool $nullAllowed
	 * @return DataMap|null
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
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
	 * @return \n2n\util\attr\DataMap|null
	 * @throws InvalidAttributeException
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
	 * @deprecated
	 */
	public function removeKey(string $name, $key) {
		if ($this->has($name)) {
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
		return new DataSet($attrs);
	}

	public function jsonSerialize(): array {
		return $this->data;
	}
}
