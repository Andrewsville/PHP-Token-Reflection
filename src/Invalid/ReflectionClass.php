<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Invalid;

use ApiGen\TokenReflection\Behaviors\Annotations;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception\BrokerException;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\Php;
use ApiGen\TokenReflection\Reflection\ReflectionBase;
use ApiGen\TokenReflection\Reflection\ReflectionFile;
use ReflectionClass as InternalReflectionClass;


class ReflectionClass extends ReflectionElement implements ReflectionClassInterface, Annotations
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * Original definition file name.
	 *
	 * @var string
	 */
	private $fileName;

	/**
	 * @var Broker
	 */
	private $broker;


	/**
	 * @param string $className
	 * @param string $fileName Original definition file name
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
	public function getName()
	{
		return $this->name;
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
	public function getShortName()
	{
		$pos = strrpos($this->name, '\\');
		return FALSE === $pos ? $this->name : substr($this->name, $pos + 1);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceName()
	{
		$pos = strrpos($this->name, '\\');
		return FALSE === $pos ? '' : substr($this->name, 0, $pos);
	}


	/**
	 * {@inheritdoc}
	 */
	public function inNamespace()
	{
		return FALSE !== strrpos($this->name, '\\');
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
	public function getExtension()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getExtensionName()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFileName()
	{
		return $this->fileName;
	}


	/**
	 * @return \ApiGen\TokenReflection\Reflection\ReflectionFile
	 * @throws RuntimeException If the file is not stored inside the broker
	 */
	public function getFileReflection()
	{
		throw new BrokerException($this->getBroker(), sprintf('Class was not parsed from a file', $this->getName()), BrokerException::UNSUPPORTED);
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
		if (is_object($interface)) {
			if ( ! $interface instanceof ReflectionClassInterface) {
				throw new RuntimeException(sprintf('Parameter must be a string or an instance of class reflection, "%s" provided.', get_class($interface)), RuntimeException::INVALID_ARGUMENT, $this);
			}
			$interfaceName = $interface->getName();
			if ( ! $interface->isInterface()) {
				throw new RuntimeException(sprintf('"%s" is not an interface.', $interfaceName), RuntimeException::INVALID_ARGUMENT, $this);
			}
		}
		// Only validation, always returns false
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
		throw new RuntimeException(sprintf('There is no method "%s".', $name), RuntimeException::DOES_NOT_EXIST, $this);
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
		throw new RuntimeException(sprintf('There is no constant "%s".', $name), RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstantReflection($name)
	{
		throw new RuntimeException(sprintf('There is no constant "%s".', $name), RuntimeException::DOES_NOT_EXIST, $this);
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
		throw new RuntimeException(sprintf('There is no property "%s".', $name), RuntimeException::DOES_NOT_EXIST, $this);
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
		throw new RuntimeException(sprintf('There is no static property "%s".', $name), RuntimeException::DOES_NOT_EXIST, $this);
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
	 * Returns names of indirect subclasses.
	 *
	 * @return array
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
			throw new RuntimeException(sprintf('Parameter must be a class instance, "%s" provided.', gettype($object)), RuntimeException::INVALID_ARGUMENT, $this);
		}
		return $this->name === get_class($object) || is_subclass_of($object, $this->name);
	}


	/**
	 * {@inheritdoc}
	 */
	public function newInstanceWithoutConstructor()
	{
		if ( ! class_exists($this->name, TRUE)) {
			throw new RuntimeException('Could not create an instance; class does not exist.', RuntimeException::DOES_NOT_EXIST, $this);
		}
		$reflection = new Php\ReflectionClass($this->name, $this->getBroker());
		return $reflection->newInstanceWithoutConstructor();
	}


	/**
	 * {@inheritdoc}
	 */
	public function newInstance($args)
	{
		return $this->newInstanceArgs(func_get_args());
	}


	/**
	 * {@inheritdoc}
	 */
	public function newInstanceArgs(array $args = [])
	{
		if ( ! class_exists($this->name, TRUE)) {
			throw new RuntimeException('Could not create an instance of class; class does not exist.', RuntimeException::DOES_NOT_EXIST, $this);
		}
		$reflection = new InternalReflectionClass($this->name);
		return $reflection->newInstanceArgs($args);
	}


	/**
	 * {@inheritdoc}
	 */
	public function setStaticPropertyValue($name, $value)
	{
		throw new RuntimeException(sprintf('There is no static property "%s".', $name), RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * Outputs the reflection subject source code.
	 *
	 * @return string
	 */
	public function getSource()
	{
		return '';
	}


	/**
	 * Returns the start position in the file token stream.
	 *
	 * @return int
	 */
	public function getStartPosition()
	{
		return -1;
	}


	/**
	 * Returns the end position in the file token stream.
	 *
	 * @return int
	 */
	public function getEndPosition()
	{
		return -1;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isComplete()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isValid()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isDeprecated()
	{
		return FALSE;
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
	public function __get($key)
	{
		return ReflectionBase::get($this, $key);
	}


	/**
	 * {@inheritdoc}
	 */
	public function __isset($key)
	{
		return ReflectionBase::exists($this, $key);
	}

}
