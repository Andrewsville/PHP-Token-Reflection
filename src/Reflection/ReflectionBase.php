<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection;

use ApiGen\TokenReflection\Behaviors\AnnotationsInterface;
use ApiGen\TokenReflection\Parser;
use ApiGen\TokenReflection\Storage\StorageInterface;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\Reflection;
use ApiGen\TokenReflection\Stream\StreamBase;


abstract class ReflectionBase implements ReflectionInterface, AnnotationsInterface
{

	/**
	 * FQN object name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * @var Reflection\ReflectionAnnotation|bool
	 */
	protected $docComment;

	/**
	 * @var StorageInterface
	 */
	protected $storage;


	public function __construct(StreamBase $tokenStream, StorageInterface $storage, ReflectionInterface $parent = NULL)
	{
		$this->storage = $storage;
		if (method_exists($this, 'parseStream')) {
			$this->parseStream($tokenStream, $parent);
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDocComment()
	{
		return $this->docComment->getDocComment();
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasAnnotation($name)
	{
		return $this->docComment->hasAnnotation($name);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getAnnotation($name)
	{
		return $this->docComment->getAnnotation($name);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getAnnotations()
	{
		return $this->docComment->getAnnotations();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getStorage()
	{
		return $this->storage;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isInternal()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isUserDefined()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isTokenized()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isDeprecated()
	{
		return $this->hasAnnotation('deprecated');
	}

}
