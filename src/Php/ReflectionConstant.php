<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen\TokenReflection\Behaviors\Annotations;
use ApiGen\TokenReflection\Dummy;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Invalid;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\IReflectionConstant;
use ApiGen\TokenReflection\ReflectionElement;
use ApiGen\TokenReflection\ReflectionNamespace;
use Reflector;


class ReflectionConstant implements IReflection, IReflectionConstant, Annotations
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
	 * @var bool
	 */
	private $userDefined;

	/**
	 * @var Broker
	 */
	private $broker;


	/**
	 * @param string $name Constant name
	 * @param mixed $value Constant value
	 * @param Broker $broker Reflection broker
	 * @param ReflectionClass $parent Defining class reflection
	 * @throws RuntimeException If real parent class could not be determined.
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
				throw new RuntimeException('Could not determine constant real parent class.', RuntimeException::DOES_NOT_EXIST, $this);
			}
			$this->declaringClassName = $realParent->getName();
			$this->userDefined = $realParent->isUserDefined();
		} else {
			if ( ! array_key_exists($name, get_defined_constants(FALSE))) {
				$this->userDefined = TRUE;
			} else {
				$declared = get_defined_constants(TRUE);
				$this->userDefined = array_key_exists($name, $declared['user']);
			}
		}
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
	public function getShortName()
	{
		$name = $this->getName();
		if (NULL !== $this->namespaceName && $this->namespaceName !== ReflectionNamespace::NO_NAMESPACE_NAME) {
			$name = substr($name, strlen($this->namespaceName) + 1);
		}
		return $name;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClass()
	{
		if (NULL === $this->declaringClassName) {
			return NULL;
		}
		return $this->getBroker()->getClass($this->declaringClassName);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClassName()
	{
		return $this->declaringClassName;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceName()
	{
		return $this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME ? '' : $this->namespaceName;
	}


	/**
	 * Returns if the function/method is defined within a namespace.
	 *
	 * @return bool
	 */
	public function inNamespace()
	{
		return '' !== $this->getNamespaceName();
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
		return NULL;
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
	public function getValue()
	{
		return $this->value;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getValueDefinition()
	{
		return var_export($this->value, TRUE);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOriginalValueDefinition()
	{
		return token_get_all($this->getValueDefinition());
	}


	/**
	 * {@inheritdoc}
	 */
	public function isInternal()
	{
		return !$this->userDefined;
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
	public function getPrettyName()
	{
		return NULL === $this->declaringClassName ? $this->name : sprintf('%s::%s', $this->declaringClassName, $this->name);
	}


	/**
	 * {@inheritdoc}
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
	 * @param Broker $broker
	 * @param string|object|NULL $class Class name, class instance or null
	 * @param string $constant Constant name
	 * @param bool $return Return the export instead of outputting it
	 * @return string|null
	 * @throws RuntimeException If requested parameter doesn't exist.
	 */
	public static function export(Broker $broker, $class, $constant, $return = FALSE)
	{
		$className = is_object($class) ? get_class($class) : $class;
		$constantName = $constant;
		if (NULL === $className) {
			try {
				$constant = $broker->getConstant($constantName);
			} catch (Exception\BrokerException $e) {
				throw new RuntimeException(sprintf('Constant %s does not exist.', $constantName), RuntimeException::DOES_NOT_EXIST);
			}
		} else {
			$class = $broker->getClass($className);
			if ($class instanceof Invalid\ReflectionClass) {
				throw new RuntimeException('Class is invalid.', RuntimeException::UNSUPPORTED);
			} elseif ($class instanceof Dummy\ReflectionClass) {
				throw new RuntimeException(sprintf('Class %s does not exist.', $className), RuntimeException::DOES_NOT_EXIST);
			}
			$constant = $class->getConstantReflection($constantName);
		}
		if ($return) {
			return $constant->__toString();
		}
		echo $constant->__toString();
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
	 * Returns if the constant definition is valid.
	 *
	 * Internal constants are always valid.
	 *
	 * @return bool
	 */
	public function isValid()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	final public function __get($key)
	{
		return ReflectionElement::get($this, $key);
	}


	/**
	 * {@inheritdoc}
	 */
	final public function __isset($key)
	{
		return ReflectionElement::exists($this, $key);
	}


	/**
	 * Creates a reflection instance.
	 *
	 * Not supported for constants since there is no internal constant reflection.
	 *
	 * @return null
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		return NULL;
	}

}
