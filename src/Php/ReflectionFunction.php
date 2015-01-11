<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen;
use ApiGen\TokenReflection;
use ApiGen\TokenReflection\Broker;
use ApiGen\TokenReflection\Exception;
use Reflector, ReflectionFunction as InternalReflectionFunction, ReflectionParameter as InternalReflectionParameter;


/**
 * Reflection of a not tokenized but defined function.
 *
 * Descendant of the internal reflection with additional features.
 */
class ReflectionFunction extends InternalReflectionFunction implements IReflection, TokenReflection\IReflectionFunction
{

	/**
	 * Function parameter reflections.
	 *
	 * @var array
	 */
	private $parameters;

	/**
	 * Reflection broker.
	 *
	 * @var ApiGen\TokenReflection\Broker
	 */
	private $broker;


	/**
	 * Constructor.
	 *
	 * @param string $functionName Function name
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker
	 */
	public function __construct($functionName, Broker $broker)
	{
		parent::__construct($functionName);
		$this->broker = $broker;
	}


	/**
	 * Returns the PHP extension reflection.
	 *
	 * @return ApiGen\TokenReflection\IReflectionExtension
	 */
	public function getExtension()
	{
		return ReflectionExtension::create(parent::getExtension(), $this->broker);
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
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return bool
	 */
	public function isTokenized()
	{
		return FALSE;
	}


	/**
	 * Returns a particular parameter.
	 *
	 * @param int|string $parameter Parameter name or position
	 * @return ApiGen\TokenReflection\Php\ReflectionParameter
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If there is no parameter of the given name.
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If there is no parameter at the given position.
	 */
	public function getParameter($parameter)
	{
		$parameters = $this->getParameters();
		if (is_numeric($parameter)) {
			if ( ! isset($parameters[$parameter])) {
				throw new Exception\RuntimeException(sprintf('There is no parameter at position "%d".', $parameter), Exception\RuntimeException::DOES_NOT_EXIST, $this);
			}
			return $parameters[$parameter];
		} else {
			foreach ($parameters as $reflection) {
				if ($reflection->getName() === $parameter) {
					return $reflection;
				}
			}
			throw new TokenReflection\Exception\RuntimeException(sprintf('There is no parameter "%s".', $parameter), TokenReflection\Exception\RuntimeException::DOES_NOT_EXIST, $this);
		}
	}


	/**
	 * Returns function parameters.
	 *
	 * @return array
	 */
	public function getParameters()
	{
		if (NULL === $this->parameters) {
			$broker = $this->broker;
			$parent = $this;
			$this->parameters = array_map(function (InternalReflectionParameter $parameter) use ($broker, $parent) {
				return ReflectionParameter::create($parameter, $broker, $parent);
			}, parent::getParameters());
		}
		return $this->parameters;
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
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return [];
	}


	/**
	 * Magic __get method.
	 *
	 * @param string $key Variable name
	 * @return mixed
	 */
	final public function __get($key)
	{
		return TokenReflection\ReflectionElement::get($this, $key);
	}


	/**
	 * Magic __isset method.
	 *
	 * @param string $key Variable name
	 * @return bool
	 */
	final public function __isset($key)
	{
		return TokenReflection\ReflectionElement::exists($this, $key);
	}


	/**
	 * Returns the function/method as closure.
	 *
	 * @return \Closure
	 */
	public function getClosure()
	{
		return parent::getClosure();
	}


	/**
	 * Returns the closure scope class.
	 *
	 * @return string|null
	 */
	public function getClosureScopeClass()
	{
		return parent::getClosureScopeClass();
	}


	/**
	 * Returns this pointer bound to closure.
	 *
	 * @return null
	 */
	public function getClosureThis()
	{
		return parent::getClosureThis();
	}


	/**
	 * Returns if the function definition is valid.
	 *
	 * Internal functions are always valid.
	 *
	 * @return bool
	 */
	public function isValid()
	{
		return TRUE;
	}


	/**
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	public function getPrettyName()
	{
		return $this->getName() . '()';
	}


	/**
	 * Creates a reflection instance.
	 *
	 * @param \ReflectionClass $internalReflection Internal reflection instance
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker instance
	 * @return ApiGen\TokenReflection\Php\ReflectionFunction
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If an invalid internal reflection object was provided.
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		if ( ! $internalReflection instanceof InternalReflectionFunction) {
			throw new Exception\RuntimeException('Invalid reflection instance provided, ReflectionFunction expected.', Exception\RuntimeException::INVALID_ARGUMENT);
		}
		return $broker->getFunction($internalReflection->getName());
	}

}
