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


}
