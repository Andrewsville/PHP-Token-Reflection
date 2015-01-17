<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Parser;

use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\Reflection\ReflectionAnnotation;
use ApiGen\TokenReflection\Reflection\ReflectionElement;
use ApiGen\TokenReflection\Reflection\ReflectionParameter;
use ApiGen\TokenReflection\Stream\StreamBase;


class ElementParser
{

	/**
	 * @var StreamBase
	 */
	private $tokenStream;

	/**
	 * @var ReflectionInterface
	 */
	private $parent;

	/**
	 * @var ReflectionElement
	 */
	private $reflectionElement;


	public function __construct(StreamBase $tokenStream, ReflectionElement $reflectionElement, ReflectionInterface $parent = NULL)
	{
		$this->reflectionElement = $reflectionElement;
		$this->tokenStream = $tokenStream;
		$this->parent = $parent;
	}


	/**
	 * @param int $startPosition
	 * @return array
	 */
	public function parseDocComment($startPosition)
	{
		$docComment = NULL;
		if ($this instanceof ReflectionParameter) {
			return [new ReflectionAnnotation($this), $startPosition];
		}

		$position = $this->tokenStream->key();
		if ($this->tokenStream->is(T_DOC_COMMENT, $position - 1)) {
			$value = $this->tokenStream->getTokenValue($position - 1);
			$docComment = new ReflectionAnnotation($this->reflectionElement, $value);
			$startPosition--;
		} elseif ($this->tokenStream->is(T_DOC_COMMENT, $position - 2)) {
			$value = $this->tokenStream->getTokenValue($position - 2);
			$docComment = new ReflectionAnnotation($this->reflectionElement, $value);
			$startPosition -= 2;
		}
		if ($docComment === NULL) {
			$docComment = new ReflectionAnnotation($this->reflectionElement);
		}

		return [$docComment, $startPosition];
	}

}
