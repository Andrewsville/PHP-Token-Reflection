<?php
/**
 * PHP Token Reflection
 *
 * Development version
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file license.txt.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
 */

namespace TokenReflection\Php;
use TokenReflection;

use TokenReflection\Broker;
use Reflector, ReflectionParameter as InternalReflectionParameter, ReflectionFunctionAbstract as InternalReflectionFunctionAbstract;
use RuntimeException;

/**
 * Reflection of a not tokenized but defined class parameter.
 *
 * Descendant of the internal reflection with additional features.
 */
class ReflectionParameter extends InternalReflectionParameter implements IReflection, TokenReflection\IReflectionParameter
{
	/**
	 * Reflection broker.
	 *
	 * @var \TokenReflection\Broker
	 */
	private $broker;

	/**
	 * Determined if the parameter (along with the function/method) is user defined.
	 *
	 * @var boolean
	 */
	private $userDefined;

	/**
	 * Constructor.
	 *
	 * @param string|array Defining function/method
	 * @param string $paramName Parameter name
	 * @param \TokenReflection\Broker $broker Reflection broker
	 * @param \ReflectionFunctionAbstract $parent Parent reflection object
	 */
	public function __construct($function, $paramName, Broker $broker, InternalReflectionFunctionAbstract $parent)
	{
		parent::__construct($function, $paramName);
		$this->broker = $broker;
		$this->userDefined = $parent->isUserDefined();
	}

	/**
	 * Returns if the parameter is internal.
	 *
	 * @return boolean
	 */
	public function isInternal()
	{
		return !$this->userDefined;
	}

	/**
	 * Returns if the parameter is user defined.
	 *
	 * @return boolean
	 */
	public function isUserDefined()
	{
		return $this->userDefined;
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
	final public function __isset($key) {
		return TokenReflection\ReflectionBase::exists($this, $key);
	}

	/**
	 * Returns the declaring class reflection.
	 *
	 * @return \TokenReflection\Php\IReflectionClass
	 */
	public function getDeclaringClass()
	{
		$class = parent::getDeclaringClass();
		return $class ? ReflectionClass::create($class, $this->broker) : null;
	}

	/**
	 * Returns the declaring class name.
	 *
	 * @return string|null
	 */
	public function getDeclaringClassName()
	{
		$class = parent::getDeclaringClass();
		return $class ? $class->getName() : null;
	}

	/**
	 * Returns the required class name of the value.
	 *
	 * @return string|null
	 */
	public function getClassName()
	{
		return $this->getClass() ? $this->getClass()->getName() : null;
	}

	/**
	 * Returns the declaring function reflection.
	 *
	 * @return \TokenReflection\Php\ReflectionFunction|\TokenReflection\Php\ReflectionMethod
	 */
	public function getDeclaringFunction()
	{
		$class = $this->getDeclaringClass();
		$function = parent::getDeclaringFunction();

		if ($class) {
			return $class->getMethod($function->getName());
		} else {
			return ReflectionFunction::create($function, $this->broker);
		}
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
	 * Returns the part of the source code defining the paramter default value.
	 *
	 * @return string
	 */
	public function getDefaultValueDefinition()
	{
		$value = $this->getDefaultValue();
		return null === $value ? null : var_export($value, true);
	}

	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return string|false
	 */
	public function getDocComment()
	{
		return false;
	}

	/**
	 * Returns the docblock definition of the parameter.
	 *
	 * @return string|false
	 */
	public function getInheritedDocComment()
	{
		return $this->getDocComment();
	}

	/**
	 * Returns the definition start line number in the file.
	 *
	 * @return integer
	 */
	public function getStartLine()
	{
		return false;
	}

	/**
	 * Returns the definition end line number in the file.
	 *
	 * @return integer
	 */
	public function getEndLine()
	{
		return false;
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
	 * Creates a reflection instance.
	 *
	 * @param \ReflectionParamter Internal reflection instance
	 * @param \TokenReflection\Broker Reflection broker instance
	 * @return \TokenReflection\Php\ReflectionParameter
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		static $cache = array();

		if (!$internalReflection instanceof InternalReflectionParameter) {
			throw new RuntimeException(sprintf('Invalid reflection instance provided (%s), ReflectionParameter expected.', get_class($internalReflection)));
		}

		$class = $internalReflection->getDeclaringClass();
		$function = $internalReflection->getDeclaringFunction();

		$key = $class ? $class->getName() . '::' : '';
		$key .= $function->getName() . '(' . $internalReflection->getName() . ')';

		if (!isset($cache[$key])) {
			$cache[$key] = new self($class ? array($class->getName(), $function->getName()) : $function->getName(), $internalReflection->getName(), $broker, $function);
		}

		return $cache[$key];
	}
}
