<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection;

use ApiGen\TokenReflection\Behaviors\AnnotationsInterface;
use ApiGen\TokenReflection\Parser\AnnotationParser;
use ApiGen\TokenReflection\Reflection\ReflectionBase;


class ReflectionAnnotation implements AnnotationsInterface
{

	/**
	 * @var array
	 */
	private $annotations;

	/**
	 * Element docblock.
	 *
	 * False if none.
	 *
	 * @var string|bool
	 */
	private $docComment;

	/**
	 * Parent reflection object.
	 *
	 * @var ReflectionBase
	 */
	private $reflection;


	/**
	 * @param ReflectionBase $reflection
	 * @param string|bool $docComment
	 */
	public function __construct(ReflectionBase $reflection, $docComment = FALSE)
	{
		$this->reflection = $reflection;
		$this->docComment = $docComment ?: FALSE;
		$this->annotationParser = new AnnotationParser($reflection, $docComment);
	}


	/**
	 * @return string|bool
	 */
	public function getDocComment()
	{
		return $this->docComment;
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasAnnotation($annotation)
	{
		if ($this->annotations === NULL) {
			$this->annotations = $this->annotationParser->parse();
		}
		return isset($this->annotations[$annotation]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getAnnotation($annotation)
	{
		if ($this->hasAnnotation($annotation)) {
			return $this->annotations[$annotation];
		}
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getAnnotations()
	{
		if ($this->annotations === NULL) {
			$this->annotations = $this->annotationParser->parse();
		}
		return $this->annotations;
	}

}
