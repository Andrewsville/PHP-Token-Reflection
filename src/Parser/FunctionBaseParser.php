<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Parser;

use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\IReflection;
use ApiGen\TokenReflection\IReflectionClass;
use ApiGen\TokenReflection\IReflectionFunction;
use ApiGen\TokenReflection\IReflectionFunctionBase;
use ApiGen\TokenReflection\IReflectionMethod;
use ApiGen\TokenReflection\Stream\StreamBase;


abstract class FunctionBaseParser
{

	/**
	 * @var StreamBase
	 */
	protected $tokenStream;

	/**
	 * @var IReflectionFunction|IReflectionMethod
	 */
	protected $reflectionFunction;

	/**
	 * @var IReflection|IReflectionClass
	 */
	protected $parent;


	public function __construct(StreamBase $tokenStream, IReflectionFunctionBase $reflectionFunction, IReflection $parent = NULL)
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
			throw new ParseException($this->reflectionFunction, $this->tokenStream, 'Could not find the function keyword.', ParseException::UNEXPECTED_TOKEN);
		}
		$this->tokenStream->skipWhitespaces(TRUE);
		$type = $this->tokenStream->getType();
		if ('&' === $type) {
			$returnsReference = TRUE;
			$this->tokenStream->skipWhitespaces(TRUE);

		} elseif (T_STRING !== $type) {
			throw new ParseException($this->reflectionFunction, $this->tokenStream, 'Unexpected token found.', ParseException::UNEXPECTED_TOKEN);
		}
		return $returnsReference;
	}

}
