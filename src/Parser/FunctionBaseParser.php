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
	 * @var ReflectionFunctionInterface|ReflectionMethodInterface
	 */
	protected $reflectionFunction;

	/**
	 * @var ReflectionInterface|ReflectionClassInterface
	 */
	protected $parent;


	public function __construct(StreamBase $tokenStream, ReflectionFunctionBaseInterface $reflectionFunction, ReflectionInterface $parent = NULL)
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
