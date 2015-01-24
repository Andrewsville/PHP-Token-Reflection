<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Parser;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\Reflection\ReflectionFunctionBase;
use ApiGen\TokenReflection\Reflection\ReflectionParameter;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\ReflectionFunctionBaseInterface;
use ApiGen\TokenReflection\ReflectionMethodInterface;
use ApiGen\TokenReflection\Stream\StreamBase;


abstract class FunctionBaseParser
{

	/**
	 * @var StreamBase
	 */
	protected $tokenStream;

	/**
	 * @var ReflectionFunctionBaseInterface|ReflectionFunctionInterface|ReflectionMethodInterface
	 */
	protected $reflectionFunction;

	/**
	 * @var ReflectionInterface|ReflectionClassInterface
	 */
	protected $parent;


	public function __construct(StreamBase $tokenStream, ReflectionFunctionBaseInterface &$reflectionFunction, ReflectionInterface $parent = NULL)
	{
		$this->tokenStream = $tokenStream;
		$this->reflectionFunction = $reflectionFunction;
		$this->parent = $parent;
	}


	/**
	 * @return string
	 */
	public function parseName()
	{
		$name = $this->tokenStream->getTokenValue();
		$this->tokenStream->skipWhitespaces(TRUE);
		return $name;
	}


	/**
	 * @return bool
	 */
	public function parseReturnReference()
	{
		$returnsReference = FALSE;
		if ( ! $this->tokenStream->is(T_FUNCTION)) {
			throw new ParseException('Could not find the function keyword.', ParseException::UNEXPECTED_TOKEN);
		}
		$this->tokenStream->skipWhitespaces(TRUE);
		$type = $this->tokenStream->getType();
		if ($type === '&') {
			$returnsReference = TRUE;
			$this->tokenStream->skipWhitespaces(TRUE);

		} elseif ($type !== T_STRING) {
			throw new ParseException('Unexpected token found.', ParseException::UNEXPECTED_TOKEN);
		}
		return $returnsReference;
	}


	/**
	 * @return ReflectionParameter[]
	 */
	public function parseParameters()
	{
		if ( ! $this->tokenStream->is('(')) {
			throw new ParseException('Could find the start token.', ParseException::UNEXPECTED_TOKEN);
		}

		static $accepted = [T_NS_SEPARATOR => TRUE, T_STRING => TRUE, T_ARRAY => TRUE, T_CALLABLE => TRUE, T_VARIABLE => TRUE, '&' => TRUE];
		if (PHP_VERSION_ID >= 50600 && ! isset($accepted[T_ELLIPSIS])) {
			$accepted += [T_ELLIPSIS => TRUE];
		}

		$this->tokenStream->skipWhitespaces(TRUE);

		$parameters = [];

		while (($type = $this->tokenStream->getType()) !== NULL && $type !== ')') {
			if (isset($accepted[$type])) {
				$parameters[] = new ReflectionParameter($this->tokenStream, $this->reflectionFunction->getStorage(), $this->reflectionFunction);
			}
			if ($this->tokenStream->is(')')) {
				break;
			}
			$this->tokenStream->skipWhitespaces(TRUE);
		}

		$this->tokenStream->skipWhitespaces();
		return $parameters;
	}


	/**
	 * @return array
	 */
	public function parseStaticVariables()
	{
		$staticVariablesDefinition = [];

		$type = $this->tokenStream->getType();
		if ($type === '{') {
			$this->tokenStream->skipWhitespaces(TRUE);
			while (($type = $this->tokenStream->getType()) !== '}') {
				switch ($type) {
					case T_STATIC:
						$type = $this->tokenStream->skipWhitespaces(TRUE)->getType();
						if ($type !== T_VARIABLE) {
							// Late static binding
							break;
						}
						while ($type === T_VARIABLE) {
							$variableName = $this->tokenStream->getTokenValue();
							$variableDefinition = [];
							$type = $this->tokenStream->skipWhitespaces(TRUE)->getType();
							if ($type === '=') {
								$type = $this->tokenStream->skipWhitespaces(TRUE)->getType();
								$level = 0;
								while ($this->tokenStream->valid()) {
									switch ($type) {
										case '(':
										case '[':
										case '{':
										case T_CURLY_OPEN:
										case T_DOLLAR_OPEN_CURLY_BRACES:
											$level++;
											break;
										case ')':
										case ']':
										case '}':
											$level--;
											break;
										case ';':
										case ',':
											if ($level === 0) {
												break 2;
											}
										default:
											break;
									}
									$variableDefinition[] = $this->tokenStream->current();
									$type = $this->tokenStream->skipWhitespaces(TRUE)->getType();
								}
								if ( ! $this->tokenStream->valid()) {
									throw new ParseException('Invalid end of token stream.', ParseException::READ_BEYOND_EOS);
								}
							}
							$staticVariablesDefinition[substr($variableName, 1)] = $variableDefinition;
							if (',' === $type) {
								$type = $this->tokenStream->skipWhitespaces(TRUE)->getType();
							} else {
								break;
							}
						}
						break;
					case T_FUNCTION:
						// Anonymous function -> skip to its end
						if ( ! $this->tokenStream->find('{')) {
							throw new ParseException('Could not find beginning of the anonymous function.', ParseException::UNEXPECTED_TOKEN);
						}
						// Break missing intentionally
					case '{':
					case '[':
					case '(':
					case T_CURLY_OPEN:
					case T_DOLLAR_OPEN_CURLY_BRACES:
						$this->tokenStream->findMatchingBracket()->skipWhitespaces(TRUE);
						break;
					default:
						$this->tokenStream->skipWhitespaces();
						break;
				}
			}

		} elseif ($type !== ';') {
			throw new ParseException('Unexpected token found.', ParseException::UNEXPECTED_TOKEN);
		}
		return $staticVariablesDefinition;
	}

}
