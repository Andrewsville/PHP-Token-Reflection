<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Parser;

use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\Reflection\ReflectionParameter;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionParameterInterface;
use ApiGen\TokenReflection\Stream\StreamBase;


class ParameterParser
{

	/**
	 * @var StreamBase
	 */
	private $tokenStream;

	/**
	 * @var ReflectionParameterInterface
	 */
	private $reflectionParameter;

	/**
	 * @var ReflectionInterface|ReflectionClassInterface
	 */
	private $parent;


	public function __construct(StreamBase $tokenStream, ReflectionParameterInterface $reflectionParameter, ReflectionInterface $parent = NULL)
	{
		$this->tokenStream = $tokenStream;
		$this->reflectionParameter = $reflectionParameter;
		$this->parent = $parent;
	}


	/**
	 * @return string
	 */
	public function parseName()
	{
		if ( ! $this->tokenStream->is(T_VARIABLE)) {
			throw new ParseException($this->reflectionParameter, $this->tokenStream, 'The parameter name could not be determined.', ParseException::UNEXPECTED_TOKEN);
		}
		$name = substr($this->tokenStream->getTokenValue(), 1);
		$this->tokenStream->skipWhitespaces(TRUE);
		return $name;
	}


	/**
	 * @return bool
	 */
	public function parseIsVariadic()
	{
		if (PHP_VERSION_ID >= 50600 && $this->tokenStream->is(T_ELLIPSIS)) {
			$this->tokenStream->skipWhitespaces(TRUE);
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * @return string[]
	 */
	public function parseTypeHint()
	{
		$type = $this->tokenStream->getType();
		$typeHint = NULL;
		$originalTypeHint = NULL;

		if ($type === T_ARRAY) {
			$typeHint = ReflectionParameter::ARRAY_TYPE_HINT;
			$originalTypeHint = ReflectionParameter::ARRAY_TYPE_HINT;
			$this->tokenStream->skipWhitespaces(TRUE);

		} elseif ($type === T_CALLABLE) {
			$typeHint = ReflectionParameter::CALLABLE_TYPE_HINT;
			$originalTypeHint = ReflectionParameter::CALLABLE_TYPE_HINT;
			$this->tokenStream->skipWhitespaces(TRUE);

		} elseif ($type === T_STRING || $type === T_NS_SEPARATOR) {
			$className = '';
			do {
				$className .= $this->tokenStream->getTokenValue();
				$this->tokenStream->skipWhitespaces(TRUE);
				$type = $this->tokenStream->getType();
			} while ($type === T_STRING || $type === T_NS_SEPARATOR);

			if (ltrim($className, '\\') === '') {
				throw new ParseException($this->reflectionParameter, $this->tokenStream, sprintf('Invalid class name definition: "%s".', $className), ParseException::LOGICAL_ERROR);
			}

			$originalTypeHint = $className;
		}

		return [$typeHint, $originalTypeHint];
	}



	/**
	 * @return bool
	 */
	public function parsePassedByReference()
	{
		if ($this->tokenStream->is('&')) {
			$this->tokenStream->skipWhitespaces(TRUE);
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * @return array
	 */
	public function parseDefaultValue()
	{
		$defaultValueDefinition = [];
		if ($this->tokenStream->is('=')) {
			$this->tokenStream->skipWhitespaces(TRUE);
			$level = 0;
			while (NULL !== ($type = $this->tokenStream->getType())) {
				switch ($type) {
					case ')':
						if ($level === 0) {
							break 2;
						}
					case '}':
					case ']':
						$level--;
						break;
					case '(':
					case '{':
					case '[':
						$level++;
						break;
					case ',':
						if ($level === 0) {
							break 2;
						}
						break;
					default:
						break;
				}
				$defaultValueDefinition[] = $this->tokenStream->current();
				$this->tokenStream->next();
			}
			if ($type !== ')' && $type !== ',') {
				throw new ParseException($this->reflectionParameter, $this->tokenStream, 'The property default value is not terminated properly. Expected "," or ")".', ParseException::UNEXPECTED_TOKEN);
			}
		}

		return $defaultValueDefinition;
	}

}
