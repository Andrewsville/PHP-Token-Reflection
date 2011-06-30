<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 4
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor
 * @author Jaroslav Hanslík
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
	 * Constant name.
	 *
	 * @var string
	 */
	private $name;

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
	 * Determined if the constant is user defined.
	 *
	 * @var boolean
	 */
	private $userDefined;

	/**
	 * Reflection broker.
	 *
	 * @var \TokenReflection\Broker
	 */
	private $broker;

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
	 * Returns the name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
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
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return null
	 */
	public function getFileName()
	{
		return null;
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
	 * Returns the appropriate docblock definition.
	 *
	 * @return boolean
	 */
	public function getDocComment()
	{
		return false;
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
	 * Returns parsed docblock.
	 *
	 * @return array
	 */
	public function getAnnotations()
	{
		return array();
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
	 * Returns the part of the source code defining the constant value.
	 *
	 * @return string
	 */
	public function getValueDefinition()
	{
		return var_export($this->value, true);
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
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return sprintf(
			"Constant [ %s %s ] { %s }\n",
			gettype($this->getValue()),
			$this->getName(),
			$this->getValue()
		);
	}

	/**
	 * Exports a reflected object.
	 *
	 * @param \TokenReflection\Broker $broker Broker instance
	 * @param string|object|null $class Class name, class instance or null
	 * @param string $constant Constant name
	 * @param boolean $return Return the export instead of outputting it
	 * @return string|null
	 * @throws \TokenReflection\Exception\Runtime If requested parameter doesn't exist
	 */
	public static function export(Broker $broker, $class, $constant, $return = false)
	{
		$className = is_object($class) ? get_class($class) : $class;
		$constantName = $constant;

		if (null === $className) {
			$constant = $broker->getConstant($constantName);
			if (null === $constant) {
				throw new Exception\Runtime(sprintf('Constant %s does not exist.', $constantName), Exception\Runtime::DOES_NOT_EXIST);
			}
		} else {
			$class = $broker->getClass($className);
			if ($class instanceof Dummy\ReflectionClass) {
				throw new Exception\Runtime(sprintf('Class %s does not exist.', $className), Exception\Runtime::DOES_NOT_EXIST);
			}
			$constant = $class->getConstantReflection($constantName);
		}

		if ($return) {
			return $constant->__toString();
		}

		echo $constant->__toString();
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
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return array();
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
