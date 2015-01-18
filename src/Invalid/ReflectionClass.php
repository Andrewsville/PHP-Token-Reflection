<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Invalid;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\ReflectionClassInterface;


class ReflectionClass extends ReflectionElement implements ReflectionClassInterface
{

	/**
	 * @param string $className
	 * @param string $fileName
	 * @param Broker $broker
	 */
	public function __construct($className, $fileName, Broker $broker)
	{
		$this->name = ltrim($className, '\\');
		$this->fileName = $fileName;
		$this->broker = $broker;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return $this->name;
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
	public function getModifiers()
	{
		return 0;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isAbstract()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isFinal()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isInterface()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isException()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isInstantiable()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getTraits()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnTraits()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getTraitNames()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnTraitNames()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getTraitAliases()
	{
		return [];
	}


	/**
	 * @return bool
	 */
	public function isTrait()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function usesTrait($trait)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isCloneable()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isIterateable()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isSubclassOf($class)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParentClass()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParentClasses()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParentClassNameList()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParentClassName()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function implementsInterface($interface)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getInterfaces()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getInterfaceNames()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnInterfaces()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnInterfaceNames()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstructor()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDestructor()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasMethod($name)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getMethod($name)
	{
		throw new RuntimeException(sprintf('There is no method "%s".', $name), RuntimeException::DOES_NOT_EXIST);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getMethods($filter = NULL)
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasOwnMethod($name)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnMethods($filter = NULL)
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasTraitMethod($name)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getTraitMethods($filter = NULL)
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasConstant($name)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstant($name)
	{
		throw new RuntimeException(sprintf('There is no constant "%s".', $name), RuntimeException::DOES_NOT_EXIST);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstantReflection($name)
	{
		throw new RuntimeException(sprintf('There is no constant "%s".', $name), RuntimeException::DOES_NOT_EXIST);
	}


	/**
	 * Returns an array of constant values.
	 *
	 * @return array
	 */
	public function getConstants()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstantReflections()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasOwnConstant($name)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnConstants()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnConstantReflections()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDefaultProperties()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasProperty($name)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getProperties($filter = NULL)
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getProperty($name)
	{
		throw new RuntimeException(sprintf('There is no property "%s".', $name), RuntimeException::DOES_NOT_EXIST);
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasOwnProperty($name)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnProperties($filter = NULL)
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasTraitProperty($name)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getTraitProperties($filter = NULL)
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getStaticProperties()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getStaticPropertyValue($name, $default = NULL)
	{
		throw new RuntimeException(sprintf('There is no static property "%s".', $name), RuntimeException::DOES_NOT_EXIST);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDirectSubclasses()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDirectSubclassNames()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getIndirectSubclasses()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getIndirectSubclassNames()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDirectImplementers()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDirectImplementerNames()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getIndirectImplementers()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getIndirectImplementerNames()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function isInstance($object)
	{
		if ( ! is_object($object)) {
			throw new RuntimeException(sprintf('Parameter must be a class instance, "%s" provided.', gettype($object)), RuntimeException::INVALID_ARGUMENT);
		}
		return $this->name === get_class($object) || is_subclass_of($object, $this->name);
	}


	/**
	 * {@inheritdoc}
	 */
	public function setStaticPropertyValue($name, $value)
	{
		throw new RuntimeException(sprintf('There is no static property "%s".', $name), RuntimeException::DOES_NOT_EXIST);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isComplete()
	{
		return TRUE;
	}

}
