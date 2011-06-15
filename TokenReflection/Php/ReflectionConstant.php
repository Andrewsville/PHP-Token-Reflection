<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 2
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
 */

namespace TokenReflection\Php;

use TokenReflection;
use TokenReflection\Broker, TokenReflection\Exception, Reflector;

/**
 * Reflection of a not tokenized but defined constant.
 */
class ReflectionConstant implements IReflection, TokenReflection\IReflectionConstant
{
	/**
	 * Reflection broker.
	 *
	 * @var \TokenReflection\Broker
	 */
	private $broker;

	/**
	 * Name of the declaring class.
	 *
	 * @var string
	 */
	private $declaringClassName;

	/**
	 * Constant namespace name.
	 *
	 * @var string
	 */
	private $namespaceName;

	/**
	 * Constant value.
	 *
	 * @var mixed
	 */
	private $value;

	/**
	 * Constant name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Determined if the constant is user defined.
	 *
	 * @var boolean
	 */
	private $userDefined;

	/**
	 * Constructor.
	 *
	 * @param string $name Constant name
	 * @param mixed $value Constant value
	 * @param \TokenReflection\Broker $broker Reflection broker
	 * @param \TokenReflection\Php\ReflectionClass $parent Defining class reflection
	 */
	public function __construct($name, $value, Broker $broker, ReflectionClass $parent = null)
	{
		$this->name = $name;
		$this->value = $value;
		$this->broker = $broker;

		if (null !== $parent) {
			$this->declaringClassName = $parent->getName();
			$this->userDefined = $parent->isUserDefined();
		} else {
			$declared = get_defined_constants(false);
			if (!isset($declared[$name])) {
				$this->userDefined = true;
			} else {
				$declared = get_defined_constants(true);
				$this->userDefined = isset($declared['user'][$name]);
			}
		}
	}

	/**
	 * Returns if the constant is internal.
	 *
	 * @return boolean
	 */
	public function isInternal()
	{
		return !$this->userDefined;
	}

	/**
	 * Returns if the constant is user defined.
	 *
	 * @return boolean
	 */
	public function isUserDefined()
	{
		return $this->userDefined;
	}

	/**
	 * Returns the name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the constant value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Returns the reflection broker used by this reflection object.
	 *
	 * @return \TokenReflection\Broker
	 */
	public function getBroker()
	{
		return $this->broker;
	}

	/**
	 * Magic __get method.
	 *
	 * @param string $key Variable name
	 * @return mixed
	 */
	final public function __get($key)
	{
		return TokenReflection\ReflectionBase::get($this, $key);
	}

	/**
	 * Magic __isset method.
	 *
	 * @param string $key Variable name
	 * @return boolean
	 */
	final public function __isset($key)
	{
		return TokenReflection\ReflectionBase::exists($this, $key);
	}

	/**
	 * Returns parsed docblock.
	 *
	 * @return array
	 */
	public function getAnnotations()
	{
		return array();
	}

	/**
	 * Returns a particular annotation value.
	 *
	 * @param string $name Annotation name
	 * @return null
	 */
	public function getAnnotation($name)
	{
		return null;
	}

	/**
	 * Checks if there is a particular annotation.
	 *
	 * @param string $name Annotation name
	 * @return boolean
	 */
	public function hasAnnotation($name)
	{
		return false;
	}

	/**
	 * Returns the declaring class reflection.
	 *
	 * @return \TokenReflection\IReflectionClass|null
	 */
	public function getDeclaringClass()
	{
		if (null === $this->declaringClassName) {
			return null;
		}

		return $this->getBroker()->getClass($this->declaringClassName);
	}

	/**
	 * Returns the declaring class name.
	 *
	 * @return string|null
	 */
	public function getDeclaringClassName()
	{
		return $this->declaringClassName;
	}

	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	public function getNamespaceName()
	{
		return $this->namespaceName === TokenReflection\ReflectionNamespace::NO_NAMESPACE_NAME ? '' : $this->namespaceName;
	}

	/**
	 * Returns if the function/method is defined within a namespace.
	 *
	 * @return boolean
	 */
	public function inNamespace()
	{
		return '' !== $this->getNamespaceName();
	}

	/**
	 * Returns the part of the source code defining the constant value.
	 *
	 * @return string
	 */
	public function getValueDefinition()
	{
		return var_export($this->value, true);
	}

	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return boolean
	 */
	public function getDocComment()
	{
		return false;
	}

	/**
	 * Returns the definition start line number in the file.
	 *
	 * @return null
	 */
	public function getStartLine()
	{
		return null;
	}

	/**
	 * Returns the definition end line number in the file.
	 *
	 * @return null
	 */
	public function getEndLine()
	{
		return null;
	}

	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return null
	 */
	public function getFileName()
	{
		return null;
	}

	/**
	 * Returns the unqualified name (UQN).
	 *
	 * @return string
	 */
	public function getShortName()
	{
		$name = $this->getName();
		if (null !== $this->namespaceName && $this->namespaceName !== ReflectionNamespace::NO_NAMESPACE_NAME) {
			$name = substr($name, strlen($this->namespaceName) + 1);
		}

		return $name;
	}

	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return boolean
	 */
	public function isTokenized()
	{
		return false;
	}

	/**
	 * Returns if the reflection subject is deprecated.
	 *
	 * @return boolean
	 */
	public function isDeprecated()
	{
		return false;
	}

	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return array();
	}

	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return sprintf(
			"Constant [ %s %s ] { %s }\n",
			gettype($this->getValue()),
			$this->getShortName(),
			$this->getValue()
		);
	}

	/**
	 * Creates a reflection instance.
	 *
	 * Not supported for constants since there is no internal constant reflection.
	 *
	 * @param \ReflectionClass $internalReflection Internal reflection instance
	 * @param \TokenReflection\Broker $broker Reflection broker instance
	 * @return null
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		return null;
	}
}
