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
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\ReflectionParameterInterface;
use ApiGen\TokenReflection\Reflection\ReflectionElement;
use Reflector;
use ReflectionParameter as InternalReflectionParameter;
use ReflectionFunctionAbstract as InternalReflectionFunctionAbstract;


class ReflectionParameter extends InternalReflectionParameter implements ReflectionInterface, ReflectionParameterInterface, AnnotationsInterface, ExtensionInterface
{

	/**
	 * @var bool
	 */
	private $userDefined;

	/**
	 * @var Broker
	 */
	private $broker;


	/**
	 * @param string|array $function Defining function/method
	 * @param string $paramName
	 * @param Broker $broker
	 * @param \ReflectionFunctionAbstract $parent Parent reflection object
	 */
	public function __construct($function, $paramName, Broker $broker, InternalReflectionFunctionAbstract $parent)
	{
		parent::__construct($function, $paramName);
		$this->broker = $broker;
		$this->userDefined = $parent->isUserDefined();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClass()
	{
		$class = parent::getDeclaringClass();
		return $class ? ReflectionClass::create($class, $this->broker) : NULL;
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
		return $class ? $class->getMethod($function->getName()) : ReflectionFunction::create($function, $this->broker);
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
	public function getBroker()
	{
		return $this->broker;
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


	/**
	 * Creates a reflection instance.
	 *
	 * @return ReflectionParameter
	 * @throws RuntimeException If an invalid internal reflection object was provided.
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		static $cache = [];
		if ( ! $internalReflection instanceof InternalReflectionParameter) {
			throw new RuntimeException('Invalid reflection instance provided, ReflectionParameter expected.', RuntimeException::INVALID_ARGUMENT);
		}
		$class = $internalReflection->getDeclaringClass();
		$function = $internalReflection->getDeclaringFunction();
		$key = $class ? $class->getName() . '::' : '';
		$key .= $function->getName() . '(' . $internalReflection->getName() . ')';
		if ( ! isset($cache[$key])) {
			$cache[$key] = new self($class ? [$class->getName(), $function->getName()] : $function->getName(), $internalReflection->getName(), $broker, $function);
		}
		return $cache[$key];
	}

}
