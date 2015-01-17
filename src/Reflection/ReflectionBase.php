<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection;

use ApiGen;
use ApiGen\TokenReflection\Behaviors\Annotations;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\Reflection;
use ApiGen\TokenReflection\Stream\StreamBase;


abstract class ReflectionBase implements ReflectionInterface, Annotations
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
	 * @var Broker
	 */
	protected $broker;

	/**
	 * @var array
	 */
	private static $methodCache = [];


	public function __construct(StreamBase $tokenStream, Broker $broker, ReflectionInterface $parent = NULL)
	{
		$this->broker = $broker;
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
	public function getBroker()
	{
		return $this->broker;
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


	/**
	 * @return string
	 */
	abstract public function getSource();


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return $this->name;
	}

}
