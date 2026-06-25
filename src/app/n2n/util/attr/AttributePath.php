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

use n2n\util\type\TypeUtils;
use n2n\util\type\ArgUtils;
use n2n\util\StringUtils;
use InvalidArgumentException;
use n2n\util\col\ArrayUtils;

class AttributePath {
	const SEPARATOR = '/';
//	const WILDHARD = '*';

	/**
	 * @var string[]
	 */
	private array $names = [];
	
	public function __construct(array $names) {
		ArgUtils::valArray($names, 'scalar');

		foreach ($names as $name) {
			$name = (string) $name;

			if ($name === '') {
				continue;
			}

			if (StringUtils::contains(self::SEPARATOR, $name)) {
				throw new InvalidArgumentException('Part of path contains invalid char ' . self::SEPARATOR
						. ': ' . $name);
			}

			$this->names[] = $name;
		}
	}

	function isEmpty(): bool {
		return empty($this->names);
	}

	public function size(): int {
		return count($this->names);
	}

	/**
	 * @deprecated use {@link self::slice()}
	 */
	public function slices(int $offset, ?int $length = null): AttributePath {
		return new AttributePath(array_slice($this->names, $offset, $length));
	}

	public function slice(int $offset, ?int $length = null): AttributePath {
		return new AttributePath(array_slice($this->names, $offset, $length));
	}

	function startsWith(AttributePath|array $names): bool {
		if ($names instanceof AttributePath) {
			$names = $names->toArray();
		}

		$size = count($names);
		return  $size <= $this->size() && $names === array_slice($this->names, 0, $size);
	}
	function ext(mixed $attributePath): AttributePath {
		if ($attributePath === null) {
			return $this;
		}

		return new AttributePath([...$this->names, ...AttributePath::create($attributePath)->toArray()]);
	}

	function getLast(): ?string {
		return ArrayUtils::end($this->names);
	}


	/**
	 * @return string[]
	 */
	public function toArray(): array {
		return $this->names;
	}
	
	/**
	 * @param string|string[]|AttributePath $expression
	 * @return NULL|\n2n\util\attr\AttributePath
	 */
	public static function build(mixed $expression): ?AttributePath {
		if ($expression === null) {
			return null;
		}
		
		return self::create($expression);
	}
	
	/**
	 * @param string|string[]|AttributePath $expression
	 * @throws \InvalidArgumentException
	 * @return \n2n\util\attr\AttributePath
	 */
	public static function create(mixed $expression): AttributePath {
		if ($expression instanceof AttributePath) {
			return $expression;
		}
		
		if (is_array($expression)) {
			return new AttributePath($expression);
		}
		
		if (is_scalar($expression)) {
			return new AttributePath(explode(self::SEPARATOR, $expression));
		}
		
		throw new \InvalidArgumentException('Invalid AttributePath expression type: ' 
				. TypeUtils::getTypeInfo($expression));
	}
	
	/**
	 * @param array $expressions
	 * @return \n2n\util\attr\AttributePath[]
	 */
	public static function createArray(array $expressions) {
		$paths = [];
		foreach ($expressions as $expression) {
			$paths[] = self::create($expression);
		}
		return $paths;
	}
	
	public function __toString(): string {
		return implode(self::SEPARATOR, $this->names);
	}

	function toAbsoluteString(): string {
		return self::SEPARATOR . $this->__toString();
	}

	function equals(mixed $arg): bool {
		return $arg instanceof AttributePath && $arg->names === $this->names;
	}


	
//	/**
//	 * @param string $pathPart
//	 * @param string $name
//	 * @return boolean
//	 */
//	public static function match(string $pathPart, string $name) {
//		return self::matchesWildcard($pathPart) || $pathPart == $name;
//	}
//
//	/**
//	 * @param string $pathPart
//	 * @return boolean
//	 */
//	public static function matchesWildcard(string $pathPart) {
//		return self::WILDHARD == $pathPart;
//	}
}
