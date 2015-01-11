<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen\TokenReflection;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\RuntimeException;
use Reflector;
use ReflectionParameter as InternalReflectionParameter;
use ReflectionFunctionAbstract as InternalReflectionFunctionAbstract;


class ReflectionParameter extends InternalReflectionParameter implements IReflection, TokenReflection\IReflectionParameter
{

	/**
	 * Determined if the parameter (along with the function/method) is user defined.
	 *
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
	 * Returns the declaring class reflection.
	 *
	 * @return ApiGen\TokenReflection\IReflectionClass
	 */
	public function getDeclaringClass()
	{
		$class = parent::getDeclaringClass();
		return $class ? ReflectionClass::create($class, $this->broker) : NULL;
	}


	/**
	 * Returns the declaring class name.
	 *
	 * @return string|null
	 */
	public function getDeclaringClassName()
	{
		$class = parent::getDeclaringClass();
		return $class ? $class->getName() : NULL;
	}


	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return $this->getDeclaringFunction()->getNamespaceAliases();
	}


	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return string
	 */
	public function getFileName()
	{
		return $this->getDeclaringFunction()->getFileName();
	}


	/**
	 * Returns the PHP extension reflection.
	 *
	 * @return ApiGen\TokenReflection\Php\ReflectionExtension
	 */
	public function getExtension()
	{
		return $this->getDeclaringFunction()->getExtension();
	}


	/**
	 * Returns the PHP extension name.
	 *
	 * @return string|bool
	 */
	public function getExtensionName()
	{
		$extension = $this->getExtension();
		return $extension ? $extension->getName() : FALSE;
	}


	/**
	 * Checks if there is a particular annotation.
	 *
	 * @param string $name Annotation name
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		return FALSE;
	}


	/**
	 * Returns a particular annotation value.
	 *
	 * @param string $name Annotation name
	 * @return null
	 */
	public function getAnnotation($name)
	{
		return NULL;
	}


	/**
	 * Returns parsed docblock.
	 *
	 * @return array
	 */
	public function getAnnotations()
	{
		return [];
	}


	/**
	 * Returns the declaring function reflection.
	 *
	 * @return ReflectionFunction|ReflectionMethod
	 */
	public function getDeclaringFunction()
	{
		$class = $this->getDeclaringClass();
		$function = parent::getDeclaringFunction();
		return $class ? $class->getMethod($function->getName()) : ReflectionFunction::create($function, $this->broker);
	}


	/**
	 * Returns the declaring function name.
	 *
	 * @return string|null
	 */
	public function getDeclaringFunctionName()
	{
		$function = parent::getDeclaringFunction();
		return $function ? $function->getName() : $function;
	}


	/**
	 * Returns the definition start line number in the file.
	 *
	 * @return null
	 */
	public function getStartLine()
	{
		return NULL;
	}


	/**
	 * Returns the definition end line number in the file.
	 *
	 * @return null
	 */
	public function getEndLine()
	{
		return NULL;
	}


	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return bool
	 */
	public function getDocComment()
	{
		return FALSE;
	}


	/**
	 * Returns the part of the source code defining the paramter default value.
	 *
	 * @return string|null
	 */
	public function getDefaultValueDefinition()
	{
		$value = $this->getDefaultValue();
		return NULL === $value ? NULL : var_export($value, TRUE);
	}


	/**
	 * Returns if the default value is defined by a constant.
	 *
	 * @return bool
	 */
	public function isDefaultValueConstant()
	{
		return PHP_VERSION_ID >= 50406 && parent::isDefaultValueAvailable();
	}


	/**
	 * Returns the name of the default value constant.
	 *
	 * @return string|null
	 */
	public function getDefaultValueConstantName()
	{
		if ( ! $this->isOptional()) {
			throw new Exception\RuntimeException('Property is not optional.', Exception\RuntimeException::UNSUPPORTED, $this);
		}
		return $this->isDefaultValueConstant() ? parent::getDefaultValueConstantName : NULL;
	}


	/**
	 * Returns the original type hint as defined in the source code.
	 *
	 * @return string|null
	 */
	public function getOriginalTypeHint()
	{
		return !$this->isArray() && !$this->isCallable() ? $this->getClass() : NULL;
	}


	/**
	 * Returns the required class name of the value.
	 *
	 * @return string|null
	 */
	public function getClassName()
	{
		return $this->getClass() ? $this->getClass()->getName() : NULL;
	}


	/**
	 * Returns if the parameter is internal.
	 *
	 * @return bool
	 */
	public function isInternal()
	{
		return !$this->userDefined;
	}


	/**
	 * Returns if the parameter is user defined.
	 *
	 * @return bool
	 */
	public function isUserDefined()
	{
		return $this->userDefined;
	}


	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return bool
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
	 * Returns the reflection broker used by this reflection object.
	 *
	 * @return ApiGen\TokenReflection\Broker
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
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
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
	 * @param string $key Variable name
	 * @return mixed
	 */
	final public function __get($key)
	{
		return TokenReflection\ReflectionElement::get($this, $key);
	}


	/**
	 * @param string $key Variable name
	 * @return bool
	 */
	final public function __isset($key)
	{
		return TokenReflection\ReflectionElement::exists($this, $key);
	}


	/**
	 * Creates a reflection instance.
	 *
	 * @param \ReflectionClass $internalReflection Internal reflection instance
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker instance
	 * @return ApiGen\TokenReflection\Php\ReflectionParameter
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If an invalid internal reflection object was provided.
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