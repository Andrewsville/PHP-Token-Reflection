<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Parser;

use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\Reflection\ReflectionClass;
use ApiGen\TokenReflection\Reflection\ReflectionNamespace;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\Resolver;
use ApiGen\TokenReflection\Stream\StreamBase;
use ReflectionClass as InternalReflectionClass;


class ClassParser
{

	/**
	 * @var StreamBase
	 */
	private $tokenStream;

	/**
	 * @var ReflectionClassInterface|ReflectionClass
	 */
	private $reflectionClass;

	/**
	 * @var ReflectionInterface
	 */
	private $parent;


	public function __construct(StreamBase $tokenStream, ReflectionClassInterface $reflectionClass, ReflectionInterface $parent = NULL)
	{
		$this->tokenStream = $tokenStream;
		$this->reflectionClass = $reflectionClass;
		$this->parent = $parent;
	}


	/**
	 * @return array
	 */
	public function parseModifiers()
	{
		$modifiers = 0;
		$type = '';
		while (TRUE) {
			switch ($this->tokenStream->getType()) {
				case NULL:
					break 2;
				case T_ABSTRACT:
					$modifiers = InternalReflectionClass::IS_EXPLICIT_ABSTRACT;
					break;
				case T_FINAL:
					$modifiers = InternalReflectionClass::IS_FINAL;
					break;
				case T_INTERFACE:
					$modifiers = ReflectionClass::IS_INTERFACE;
					$type = ReflectionClass::IS_INTERFACE;
					$this->tokenStream->skipWhitespaces(TRUE);
					break 2;
				case T_TRAIT:
					$modifiers = ReflectionClass::IS_TRAIT;
					$type = ReflectionClass::IS_TRAIT;
					$this->tokenStream->skipWhitespaces(TRUE);
					break 2;
				case T_CLASS:
					$this->tokenStream->skipWhitespaces(TRUE);
					break 2;
				default:
					break;
			}
			$this->tokenStream->skipWhitespaces(TRUE);
		}
		return [
			$modifiers, $type
		];
	}


	/**
	 * @return string
	 */
	public function parseName()
	{
		if ( ! $this->tokenStream->is(T_STRING)) {
			throw new ParseException($this->reflectionClass, $this->tokenStream, 'Unexpected token found.', ParseException::UNEXPECTED_TOKEN);
		}

		if ($this->reflectionClass->getNamespaceName() === ReflectionNamespace::NO_NAMESPACE_NAME) {
			$name = $this->tokenStream->getTokenValue();

		} else {
			$name = $this->reflectionClass->getNamespaceName() . '\\' . $this->tokenStream->getTokenValue();
		}
		$this->tokenStream->skipWhitespaces(TRUE);
		return $name;
	}

}
