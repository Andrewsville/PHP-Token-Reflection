<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen\TokenReflection;
use ApiGen\TokenReflection\Dummy;
use ApiGen\TokenReflection\Invalid;
use ApiGen\TokenReflection\Broker;
use ApiGen\TokenReflection\Exception;
use Reflector;


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
	 * @var ApiGen\TokenReflection\Broker
	 */
	private $broker;


	/**
	 * Constructor.
	 *
	 * @param string $name Constant name
	 * @param mixed $value Constant value
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker
	 * @param ApiGen\TokenReflection\Php\ReflectionClass $parent Defining class reflection
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If real parent class could not be determined.
	 */
	public function __construct($name, $value, Broker $broker, ReflectionClass $parent = NULL)
	{
		$this->name = $name;
		$this->value = $value;
		$this->broker = $broker;
		if (NULL !== $parent) {
			$realParent = NULL;
			if (array_key_exists($name, $parent->getOwnConstants())) {
				$realParent = $parent;
			}
			if (NULL === $realParent) {
				foreach ($parent->getParentClasses() as $grandParent) {
					if (array_key_exists($name, $grandParent->getOwnConstants())) {
						$realParent = $grandParent;
						break;
					}
				}
			}
			if (NULL === $realParent) {
				foreach ($parent->getInterfaces() as $interface) {
					if (array_key_exists($name, $interface->getOwnConstants())) {
						$realParent = $interface;
						break;
					}
				}
			}
			if (NULL === $realParent) {
				throw new TokenReflection\Exception\RuntimeException('Could not determine constant real parent class.', TokenReflection\Exception\RuntimeException::DOES_NOT_EXIST, $this);
			}
			$this->declaringClassName = $realParent->getName();
			$this->userDefined = $realParent->isUserDefined();
		} else {
			if (!array_key_exists($name, get_defined_constants(FALSE))) {
				$this->userDefined = TRUE;
			} else {
				$declared = get_defined_constants(TRUE);
				$this->userDefined = array_key_exists($name, $declared['user']);
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
		if (NULL !== $this->namespaceName && $this->namespaceName !== ReflectionNamespace::NO_NAMESPACE_NAME) {
			$name = substr($name, strlen($this->namespaceName) + 1);
		}
		return $name;
	}


	/**
	 * Returns the declaring class reflection.
	 *
	 * @return ApiGen\TokenReflection\IReflectionClass|null
	 */
	public function getDeclaringClass()
	{
		if (NULL === $this->declaringClassName) {
			return NULL;
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
	 * Returns the PHP extension reflection.
	 *
	 * @return null
	 */
	public function getExtension()
	{
		// @todo
		return NULL;
	}


	/**
	 * Returns the PHP extension name.
	 *
	 * @return boolean
	 */
	public function getExtensionName()
	{
		return FALSE;
	}


	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return null
	 */
	public function getFileName()
	{
		return NULL;
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
	 * @return boolean
	 */
	public function getDocComment()
	{
		return FALSE;
	}


	/**
	 * Checks if there is a particular annotation.
	 *
	 * @param string $name Annotation name
	 * @return boolean
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
		return var_export($this->value, TRUE);
	}


	/**
	 * Returns the originaly provided value definition.
	 *
	 * @return string
	 */
	public function getOriginalValueDefinition()
	{
		return token_get_all($this->getValueDefinition());
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
		return FALSE;
	}


	/**
	 * Returns if the reflection subject is deprecated.
	 *
	 * @return boolean
	 */
	public function isDeprecated()
	{
		return FALSE;
	}


	/**
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	public function getPrettyName()
	{
		return NULL === $this->declaringClassName ? $this->name : sprintf('%s::%s', $this->declaringClassName, $this->name);
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
	 * @param ApiGen\TokenReflection\Broker $broker Broker instance
	 * @param string|object|null $class Class name, class instance or null
	 * @param string $constant Constant name
	 * @param boolean $return Return the export instead of outputting it
	 * @return string|null
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If requested parameter doesn't exist.
	 */
	public static function export(Broker $broker, $class, $constant, $return = FALSE)
	{
		$className = is_object($class) ? get_class($class) : $class;
		$constantName = $constant;
		if (NULL === $className) {
			try {
				$constant = $broker->getConstant($constantName);
			} catch (Exception\BrokerException $e) {
				throw new Exception\RuntimeException(sprintf('Constant %s does not exist.', $constantName), Exception\RuntimeException::DOES_NOT_EXIST);
			}
		} else {
			$class = $broker->getClass($className);
			if ($class instanceof Invalid\ReflectionClass) {
				throw new Exception\RuntimeException('Class is invalid.', Exception\RuntimeException::UNSUPPORTED);
			} elseif ($class instanceof Dummy\ReflectionClass) {
				throw new Exception\RuntimeException(sprintf('Class %s does not exist.', $className), Exception\RuntimeException::DOES_NOT_EXIST);
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
	 * Returns if the constant definition is valid.
	 *
	 * Internal constants are always valid.
	 *
	 * @return boolean
	 */
	public function isValid()
	{
		return TRUE;
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
	 * @return boolean
	 */
	final public function __isset($key)
	{
		return TokenReflection\ReflectionElement::exists($this, $key);
	}


	/**
	 * Creates a reflection instance.
	 *
	 * Not supported for constants since there is no internal constant reflection.
	 *
	 * @param \ReflectionClass $internalReflection Internal reflection instance
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker instance
	 * @return null
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		return NULL;
	}

}
