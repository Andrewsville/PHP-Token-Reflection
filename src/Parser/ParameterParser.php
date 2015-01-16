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
use ApiGen\TokenReflection\IReflectionParameter;
use ApiGen\TokenReflection\Stream\StreamBase;


class ParameterParser
{

	/**
	 * @var StreamBase
	 */
	private $tokenStream;

	/**
	 * @var IReflectionParameter
	 */
	private $reflectionParameter;

	/**
	 * @var IReflection|IReflectionClass
	 */
	private $parent;


	public function __construct(StreamBase $tokenStream, IReflectionParameter $reflectionParameter, IReflection $parent = NULL)
	{
		$this->tokenStream = $tokenStream;
		$this->reflectionParameter = $reflectionParameter;
		$this->parent = $parent;
	}


}
