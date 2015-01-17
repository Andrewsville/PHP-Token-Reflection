<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen;
use ApiGen\TokenReflection\Behaviors\Annotations;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\Reflection\ReflectionElement;
use Reflector;
use ReflectionFunction as InternalReflectionFunction;
use ReflectionParameter as InternalReflectionParameter;


class ReflectionFunction extends InternalReflectionFunction implements IReflection, ReflectionFunctionInterface, Annotations
{

	/**
	 * Function parameter reflections.
	 *
	 * @var array
	 */
	private $parameters;

	/**
	 * @var Broker
	 */
	private $broker;


	/**
	 * @param string $functionName
	 * @param Broker $broker
	 */
	public function __construct($functionName, Broker $broker)
	{
		parent::__construct($functionName);
		$this->broker = $broker;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getExtension()
	{
		return ReflectionExtension::create(parent::getExtension(), $this->broker);
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
				throw new RuntimeException(sprintf('There is no parameter at position "%d".', $parameter), RuntimeException::DOES_NOT_EXIST, $this);
			}
			return $parameters[$parameter];
		} else {
			foreach ($parameters as $reflection) {
				if ($reflection->getName() === $parameter) {
					return $reflection;
				}
			}
			throw new RuntimeException(sprintf('There is no parameter "%s".', $parameter), RuntimeException::DOES_NOT_EXIST, $this);
		}
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getBroker()
	{
		return $this->broker;
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
	public function __get($key)
	{
		return ReflectionElement::get($this, $key);
	}


	/**
	 * {@inheritdoc}
	 */
	public function __isset($key)
	{
		return ReflectionElement::exists($this, $key);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClosure()
	{
		return parent::getClosure();
	}


	/**
	 * {@inheritdoc}
	 */
	public function isValid()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return $this->getName() . '()';
	}


	/**
	 * {@inheritdoc}
	 */
	public function isVariadic()
	{
		return PHP_VERSION_ID >= 50600 ? parent::isVariadic() : FALSE;
	}


	/**
	 * Creates a reflection instance.
	 *
	 * @return ReflectionFunction
	 * @throws RuntimeException If an invalid internal reflection object was provided.
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		if ( ! $internalReflection instanceof InternalReflectionFunction) {
			throw new RuntimeException('Invalid reflection instance provided, ReflectionFunction expected.', RuntimeException::INVALID_ARGUMENT);
		}
		return $broker->getFunction($internalReflection->getName());
	}

}
