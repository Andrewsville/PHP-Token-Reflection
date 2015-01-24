<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen;
use ApiGen\TokenReflection\Behaviors\AnnotationsInterface;
use ApiGen\TokenReflection\Behaviors\ExtensionInterface;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Broker\StorageInterface;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Php\Factory\ReflectionClassFactory;
use ApiGen\TokenReflection\Php\Factory\ReflectionParameterFactory;
use ApiGen\TokenReflection\ReflectionMethodInterface;
use ReflectionMethod as InternalReflectionMethod;
use ReflectionParameter as InternalReflectionParameter;


class ReflectionMethod extends InternalReflectionMethod implements ReflectionInterface, ReflectionMethodInterface, AnnotationsInterface, ExtensionInterface
{

	/**
	 * @var array
	 */
	private $parameters;

	/**
	 * @var Broker
	 */
	private $storage;

	/**
	 * Is the property accessible despite its access level.
	 *
	 * @var bool
	 */
	private $accessible = FALSE;


	/**
	 * @param string|ReflectionClass|\ReflectionClass $class Defining class
	 * @param string $methodName
	 * @param StorageInterface $storage
	 */
	public function __construct($class, $methodName, StorageInterface $storage)
	{
		parent::__construct($class, $methodName);
		$this->storage = $storage;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClass()
	{
		return ReflectionClassFactory::create(parent::getDeclaringClass(), $this->storage);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClassName()
	{
		return $this->getDeclaringClass()->getName();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceAliases()
	{
		return $this->getDeclaringClass()->getNamespaceAliases();
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasAnnotation($name)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getAnnotation($name)
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getAnnotations()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function isTokenized()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParameter($parameter)
	{
		$parameters = $this->getParameters();
		if (is_numeric($parameter)) {
			if ( ! isset($parameters[$parameter])) {
				throw new RuntimeException(sprintf('There is no parameter at position "%d".', $parameter));
			}
			return $parameters[$parameter];

		} else {
			foreach ($parameters as $reflection) {
				if ($reflection->getName() === $parameter) {
					return $reflection;
				}
			}
			throw new RuntimeException(sprintf('There is no parameter "%s".', $parameter));
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParameters()
	{
		if ($this->parameters === NULL) {
			$this->parameters = array_map(function (InternalReflectionParameter $parameter) {
				return ReflectionParameterFactory::create($parameter, $this->storage);
			}, parent::getParameters());
		}
		return $this->parameters;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isAccessible()
	{
		return $this->accessible;
	}


	/**
	 * {@inheritdoc}
	 */
	public function setAccessible($accessible)
	{
		$this->accessible = $accessible;
		parent::setAccessible($accessible);
	}


	/**
	 * {@inheritdoc}
	 */
	public function is($filter = NULL)
	{
		return $filter === NULL || ($this->getModifiers() & $filter);
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
	public function getOriginalName()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOriginal()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOriginalModifiers()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringTrait()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringTraitName()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return sprintf('%s::%s()', $this->getDeclaringClassName(), $this->getName());
	}


	/**
	 * {@inheritdoc}
	 */
	public function isVariadic()
	{
		return PHP_VERSION_ID >= 50600 ? parent::isVariadic() : FALSE;
	}

}
