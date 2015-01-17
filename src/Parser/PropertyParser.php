<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Parser;

use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionPropertyInterface;
use ApiGen\TokenReflection\Stream\StreamBase;
use ReflectionProperty as InternalReflectionProperty;


class PropertyParser
{

	/**
	 * @var StreamBase
	 */
	private $tokenStream;

	/**
	 * @var ReflectionPropertyInterface
	 */
	private $reflectionProperty;

	/**
	 * @var ReflectionInterface|ReflectionClassInterface
	 */
	private $parent;


	public function __construct(StreamBase $tokenStream, ReflectionPropertyInterface $reflectionProperty, ReflectionInterface $parent = NULL)
	{
		$this->tokenStream = $tokenStream;
		$this->reflectionProperty = $reflectionProperty;
		$this->parent = $parent;
	}


	/**
	 * Parses the property name.
	 *
	 * @return string
	 */
	public function parseName()
	{
		if ( ! $this->tokenStream->is(T_VARIABLE)) {
			throw new ParseException($this->reflectionProperty, $this->tokenStream, 'The property name could not be determined.', ParseException::LOGICAL_ERROR);
		}
		$name = substr($this->tokenStream->getTokenValue(), 1);
		$this->tokenStream->skipWhitespaces(TRUE);
		return $name;
	}


	/**
	 * @return array
	 */
	public function parseDefaultValue()
	{
		$defaultValueDefinition = [];
		$type = $this->tokenStream->getType();
		if (';' === $type || ',' === $type) {
			// No default value
			return $this;
		}
		if ('=' === $type) {
			$this->tokenStream->skipWhitespaces(TRUE);
		}
		$level = 0;
		while (NULL !== ($type = $this->tokenStream->getType())) {
			switch ($type) {
				case ',':
					if (0 !== $level) {
						break;
					}
				case ';':
					break 2;
				case ')':
				case ']':
				case '}':
					$level--;
					break;
				case '(':
				case '{':
				case '[':
					$level++;
					break;
				default:
					break;
			}
			$defaultValueDefinition[] = $this->tokenStream->current();
			$this->tokenStream->next();
		}
		if (',' !== $type && ';' !== $type) {
			throw new ParseException($this->reflectionProperty, $this->tokenStream, 'The property default value is not terminated properly. Expected "," or ";".', ParseException::UNEXPECTED_TOKEN);
		}
		return $defaultValueDefinition;
	}


	/**
	 * @return int
	 */
	public function parseModifiers()
	{
		$modifiers = 0;
		while (TRUE) {
			switch ($this->tokenStream->getType()) {
				case T_PUBLIC:
				case T_VAR:
					$modifiers |= InternalReflectionProperty::IS_PUBLIC;
					break;
				case T_PROTECTED:
					$modifiers |= InternalReflectionProperty::IS_PROTECTED;
					break;
				case T_PRIVATE:
					$modifiers |= InternalReflectionProperty::IS_PRIVATE;
					break;
				case T_STATIC:
					$modifiers |= InternalReflectionProperty::IS_STATIC;
					break;
				default:
					break 2;
			}
			$this->tokenStream->skipWhitespaces(TRUE);
		}

		if (InternalReflectionProperty::IS_STATIC === $modifiers) {
			$modifiers |= InternalReflectionProperty::IS_PUBLIC;
		} elseif (0 === $modifiers) {
			$parentProperties = $this->parent->getOwnProperties();
			if (empty($parentProperties)) {
				throw new ParseException($this->reflectionProperty, $this->tokenStream, 'No access level defined and no previous defining class property present.', ParseException::LOGICAL_ERROR);
			}
			$sibling = array_pop($parentProperties);
			if ($sibling->isPublic()) {
				$modifiers = InternalReflectionProperty::IS_PUBLIC;
			} elseif ($sibling->isPrivate()) {
				$modifiers = InternalReflectionProperty::IS_PRIVATE;
			} elseif ($sibling->isProtected()) {
				$modifiers = InternalReflectionProperty::IS_PROTECTED;
			} else {
				throw new ParseException($this->reflectionProperty, $this->tokenStream, sprintf('Property sibling "%s" has no access level defined.', $sibling->getName()), NULL);
			}
			if ($sibling->isStatic()) {
				$modifiers |= InternalReflectionProperty::IS_STATIC;
			}
		}
		return $modifiers;
	}

}
