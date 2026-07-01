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
use n2n\util\type\TypeName;
use n2n\util\attr\trait\RetrieveTrait;
use n2n\util\attr\trait\BasicReqAndOptTrait;
use n2n\util\attr\trait\ValueObjReqAndOptTrait;

class DataSet implements AttributeReader, AttributeWriter {
	use RetrieveTrait;
	use BasicReqAndOptTrait;
	use ValueObjReqAndOptTrait;

	private $attrs;

	public function __construct(?array $attrs = null) {
		$this->attrs = (array) $attrs;
	}

	public function isEmpty(): bool {
		return empty($this->attrs);
	}

	public function contains(string $name): bool {
		return array_key_exists($name, $this->attrs);
	}

	/**
	 * @return int[]|string[]
	 */
	public function getNames(): array {
		return array_keys($this->attrs);
	}

	public function hasKey(string $name, $key): bool {
		return array_key_exists($name, $this->attrs)
				&& is_array($this->attrs[$name])
				&& array_key_exists($key, $this->attrs[$name]);
	}

	/**
	 * @param mixed $value
	 */
	public function set(string $name, $value): void {
		$this->attrs[$name] = $value;
	}

	/**
	 * @param mixed $key scalar
	 * @param mixed $value
	 */
	public function add(string $name, string $key, $value): void {
		if(!isset($this->attrs[$name]) || !is_array($this->attrs[$name])) {
			$this->attrs[$name] = array();
		}

		$this->attrs[$name][$key] = $value;
	}
	/**
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function push(string $name, $value): void {
		if(!isset($this->attrs[$name]) || !is_array($this->attrs[$name])) {
			$this->attrs[$name] = array();
		}

		$this->attrs[$name][] = $value;
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	private function retrieve($name, $type, $mandatory, $defaultValue = null, &$found = null): mixed {
		$typeConstraint = TypeConstraint::build($type);

		if ($name !== null && !$this->contains($name)) {
			$found = false;
			if (!$mandatory) return $defaultValue;
			throw new MissingAttributeFieldException('Unknown attribute: ' . $name);
		}

		$found = true;
		$value = $name === null ? $this->attrs : $this->attrs[$name];

		if ($typeConstraint === null) {
			return $value;
		}

		try {
			$typeConstraint->validate($value);
		} catch (ValueIncompatibleWithConstraintsException $e) {
			throw new InvalidAttributeException('Property contains invalid value: ' . ($name ?? '/'), 0, $e);
		}

		return $value;
	}


	/**
	 * @param string|AttributePath|array $name
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param TypeConstraint|null $typeConstraint
	 * @return mixed|null
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @deprecated use {@see self::req()} or {@see self::opt()}
	 */
	public function get($name, bool $mandatory = true, $defaultValue = null, ?TypeConstraint $typeConstraint = null) {
		if ($mandatory) {
			return $this->req($name, $typeConstraint);
		}

		return $this->opt($name, $typeConstraint, $defaultValue);
	}



	/**
	 * @param string $name
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @deprecated use {@see self::reqScalar()} or {@see self::optScalar()}
	 */
	public function getScalar(string $name, bool $mandatory = true, $defaultValue = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->reqScalar($name, $nullAllowed);
		}

		return $this->optScalar($name, $defaultValue, $nullAllowed);
	}


	/**
	 * @param string|AttributePath|array $name
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @deprecated use {@see self::reqString()} or {@see self::optString()}
	 */
	public function getString(string $name, bool $mandatory = true, $defaultValue = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->reqString($name, $nullAllowed);
		}

		return $this->optString($name, $defaultValue, $nullAllowed);
	}


	/**
	 * @param string $name
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @deprecated use {@see self::reqBool()} or {@see self::optBool()}
	 */
	public function getBool(string $name, bool $mandatory = true, $defaultValue = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->reqBool($name, $nullAllowed);
		}

		return $this->optBool($name, $defaultValue, $nullAllowed);
	}


	/**
	 * @param string $name
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param null $fieldType
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @deprecated use {@see self::reqArray()} or {@see self::optArray()}
	 */
	public function getArray(string $name, bool $mandatory = true, $defaultValue = array(), $fieldType = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->reqArray($name, $fieldType, $nullAllowed);
		}

		return $this->optArray($name, $fieldType, $defaultValue, $nullAllowed);
	}


	/**
	 * @param string $name
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @param bool $fieldNullAllowed
	 * @return mixed|null
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @deprecated use {@see self::reqScalarArray()} or {@see self::optScalarArray()}
	 */
	public function getScalarArray(string $name, bool $mandatory = true, $defaultValue = array(), bool $nullAllowed = false, bool $fieldNullAllowed = true) {
		if ($mandatory) {
			return $this->reqScalarArray($name, $nullAllowed, $fieldNullAllowed);
		}

		return $this->optScalarArray($name, $defaultValue, $nullAllowed, $fieldNullAllowed);
	}


	/**
	 * @return DataSet|null
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqDataSet(string $name, bool $nullAllowed = false) {
		return new DataSet($this->reqArray($name, null, $nullAllowed));
	}

	/**
	 * @param mixed $defaultValue
	 * @return DataSet|null
	 * @throws InvalidAttributeException
	 */
	public function optDataSet(string $name, $defaultValue = null, bool $nullAllowed = true) {
		if (null !== ($array = $this->optArray($name, null, $defaultValue, $nullAllowed))) {
			return new DataSet($array);
		}

		return null;
	}

	/**
	 * @return DataSet[]|null
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	public function reqDataSets(string $name, bool $nullAllowed = false) {
		$dataSetDatas = $this->reqArray($name, TypeName::ARRAY, $nullAllowed);
		if ($dataSetDatas === null) {
			return null;
		}

		$dataSets = [];
		foreach ($dataSetDatas as $key => $dataSetData) {
			$dataSets[$key] = new DataSet($dataSetData);
		}
		return $dataSets;
	}

	/**
	 * @param string $name
	 */
	public function remove(string ...$names) {
		foreach ($names as $name) {
			unset($this->attrs[$name]);
		}
	}

	/**
	 * @param string $name
	 * @param mixed $key scalar
	 */
	public function removeKey(string $name, $key) {
		if ($this->hasKey($name, $key)) {
			unset($this->attrs[$name][$key]);
		}
	}

	/**
	 * @param array $attrs
	 */
	public function setAll(array $attrs) {
		$this->attrs = $attrs;
	}

	/**
	 * @return array
	 */
	public function toArray(): array {
		return $this->attrs;
	}

	/**
	 * @param DataSet $dataSet
	 */
	public function append(DataSet $dataSet): void {
		$this->appendAll($dataSet->toArray());
	}

	/**
	 * @param array $attrs
	 */
	public function appendAll(array $attrs, bool $ignoreNull = false): void {
		foreach ($attrs as $key => $value) {
			if ($ignoreNull && $value === null) continue;

			if (is_array($value) && isset($this->attrs[$key]) && is_array($this->attrs[$key])) {
				$value = array_merge($this->attrs[$key], $value);
// 				$value = $this->merge($this->attrs[$key], $value);
			}

			$this->attrs[$key] = $value;
		}
	}

	public function removeNulls(bool $recursive = false): void {
		$this->removeNullsR($this->attrs, $recursive);
	}

	private function removeNullsR(array &$attrs, bool $recursive = false): void {
		foreach ($attrs as $key => $value) {
			if (!isset($attrs[$key])) {
				unset($attrs[$key]);
			} else if ($recursive && is_array($attrs[$key])) {
				$this->removeNullsR($attrs[$key], true);
			}
		}
	}

	/**
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
	 * @return string
	 */
	public function serialize(): string {
		return serialize($this->attrs);
	}

	/**
	 * @param string $serialized
	 */
	public static function createFromSerialized($serialized): DataSet {
		$attrs = StringUtils::unserialize($serialized);
		if (!is_array($attrs)) $attrs = array();
		return new DataSet($attrs);
	}

	function containsAttribute(AttributePath $path): bool {
		return $this->contains((string) $path);
	}

	function readAttribute(AttributePath $path, ?TypeConstraint $typeConstraint = null, bool $mandatory = true, mixed $defaultValue = null): mixed {
		return $this->retrieve($path->isEmpty() ? null : (string) $path, $typeConstraint, $mandatory, $defaultValue);
	}

	function writeAttribute(AttributePath $path, mixed $value): void {
		$this->set((string) $path, $value);
	}

	function removeAttribute(AttributePath $path): bool {
		if (!$this->contains((string) $path)) {
			return false;
		}

		$this->remove((string) $path);
		return true;
	}
}
