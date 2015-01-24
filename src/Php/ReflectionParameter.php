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
use ApiGen\TokenReflection\Broker\StorageInterface;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Php\Factory\ReflectionClassFactory;
use ApiGen\TokenReflection\Php\Factory\ReflectionFunctionFactory;
use ApiGen\TokenReflection\ReflectionParameterInterface;
use ReflectionParameter as InternalReflectionParameter;
use ReflectionFunctionAbstract as InternalReflectionFunctionAbstract;


class ReflectionParameter extends InternalReflectionParameter implements ReflectionInterface, ReflectionParameterInterface, AnnotationsInterface, ExtensionInterface
{

	/**
	 * @var bool
	 */
	private $userDefined;

	/**
	 * @var StorageInterface
	 */
	private $storage;


	/**
	 * @param string|array $function Defining function/method
	 * @param string $paramName
	 * @param StorageInterface $storage
	 * @param \ReflectionFunctionAbstract $parent Parent reflection object
	 */
	public function __construct($function, $paramName, StorageInterface $storage, InternalReflectionFunctionAbstract $parent)
	{
		parent::__construct($function, $paramName);
		$this->storage = $storage;
		$this->userDefined = $parent->isUserDefined();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClass()
	{
		$class = parent::getDeclaringClass();
		return $class ? ReflectionClassFactory::create($class, $this->storage) : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClassName()
	{
		$class = parent::getDeclaringClass();
		return $class ? $class->getName() : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceAliases()
	{
		return $this->getDeclaringFunction()->getNamespaceAliases();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFileName()
	{
		return $this->getDeclaringFunction()->getFileName();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getExtension()
	{
		return $this->getDeclaringFunction()->getExtension();
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
	public function getDeclaringFunction()
	{
		$class = $this->getDeclaringClass();
		$function = parent::getDeclaringFunction();
		return $class ? $class->getMethod($function->getName()) : ReflectionFunctionFactory::create($function, $this->storage);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringFunctionName()
	{
		$function = parent::getDeclaringFunction();
		return $function ? $function->getName() : $function;
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
	public function getDefaultValueDefinition()
	{
		return $this->getDefaultValue() === NULL ? NULL : var_export($this->getDefaultValue(), TRUE);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDefaultValueConstantName()
	{
		if ( ! $this->isOptional()) {
			throw new Exception\RuntimeException('Property is not optional.', Exception\RuntimeException::UNSUPPORTED, $this);
		}
		return parent::isDefaultValueConstant() ? parent::getDefaultValueConstantName() : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOriginalTypeHint()
	{
		return ! $this->isArray() && !$this->isCallable() ? $this->getClass() : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClassName()
	{
		return $this->getClass() ? $this->getClass()->getName() : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isInternal()
	{
		return ! $this->userDefined;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isUserDefined()
	{
		return $this->userDefined;
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
	 * Returns if the paramter value can be passed by value.
	 *
	 * @return bool
	 */
	public function canBePassedByValue()
	{
		return method_exists($this, 'canBePassedByValue') ? parent::canBePassedByValue() : !$this->isPassedByReference();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return str_replace('()', '($' . $this->getName() . ')', $this->getDeclaringFunction()->getPrettyName());
	}


	/**
	 * {@inheritdoc}
	 */
	public function isVariadic()
	{
		return PHP_VERSION_ID >= 50600 && parent::isVariadic();
	}

}
