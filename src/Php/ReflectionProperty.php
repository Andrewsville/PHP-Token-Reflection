<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen\TokenReflection\Behaviors\AnnotationsInterface;
use ApiGen\TokenReflection\Behaviors\ExtensionInterface;
use ApiGen\TokenReflection\Storage\StorageInterface;
use ApiGen\TokenReflection\Php\Factory\ReflectionClassFactory;
use ApiGen\TokenReflection\ReflectionPropertyInterface;
use ReflectionProperty as InternalReflectionProperty;


class ReflectionProperty extends InternalReflectionProperty implements ReflectionPropertyInterface, AnnotationsInterface, ExtensionInterface
{

	/**
	 * @var StorageInterface
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
	 * @param string $propertyName
	 * @param StorageInterface $storage
	 */
	public function __construct($class, $propertyName, StorageInterface $storage)
	{
		parent::__construct($class, $propertyName);
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
	public function getStartLine()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getEndLine()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDocComment()
	{
		return FALSE;
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
	 * Returns the property default value.
	 *
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		$values = $this->getDeclaringClass()->getDefaultProperties();
		return $values[$this->getName()];
	}


	/**
	 * Returns the part of the source code defining the property default value.
	 *
	 * @return string
	 */
	public function getDefaultValueDefinition()
	{
		$value = $this->getDefaultValue();
		return NULL === $value ? NULL : var_export($value, TRUE);
	}


	/**
	 * Returns if the property is internal.
	 *
	 * @return bool
	 */
	public function isInternal()
	{
		return $this->getDeclaringClass()->isInternal();
	}


	/**
	 * Returns if the property is user defined.
	 *
	 * @return bool
	 */
	public function isUserDefined()
	{
		return $this->getDeclaringClass()->isUserDefined();
	}


	/**
	 * {@inheritdoc}
	 */
	public function isTokenized()
	{
		return FALSE;
	}


	/**
	 * Returns if the reflection subject is deprecated.
	 *
	 * @return bool
	 */
	public function isDeprecated()
	{
		return FALSE;
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
	public function getNamespaceAliases()
	{
		return [];
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
	public function isAccessible()
	{
		return $this->accessible;
	}


	/**
	 * {@inheritdoc}
	 */
	public function setAccessible($accessible)
	{
		$this->accessible = (bool) $accessible;
		parent::setAccessible($accessible);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getExtension()
	{
		return $this->getDeclaringClass()->getExtension();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getExtensionName()
	{
		$extension = $this->getExtension();
		return $extension ? $extension->getName() : FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFileName()
	{
		return $this->getDeclaringClass()->getFileName();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return sprintf('%s::$%s', $this->getDeclaringClassName(), $this->getName());
	}

}
