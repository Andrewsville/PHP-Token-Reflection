<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 2
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file license.txt.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
 */

namespace TokenReflection;

use TokenReflection\Exception;

/**
 * Tokenized namespace reflection.
 */
class ReflectionNamespace implements IReflectionNamespace
{
	/**
	 * The name of the pseudo-namespace meaning there is no namespace.
	 *
	 * This name is chosen so that no real namespace could ever have it.
	 *
	 * @var string
	 */
	const NO_NAMESPACE_NAME = 'no-namespace';

	/**
	 * Reflection broker.
	 *
	 * @var \TokenReflection\Broker
	 */
	private $broker;

	/**
	 * Namespace name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * List of class reflections.
	 *
	 * @var array
	 */
	private $classes = array();

	/**
	 * List of function reflections.
	 *
	 * @var array
	 */
	private $functions = array();

	/**
	 * List of constant reflections.
	 *
	 * @var array
	 */
	private $constants = array();

	/**
	 * Constructor.
	 *
	 * @param string $name Namespace name
	 * @param \TokenReflection\Broker $broker Reflection broker
	 */
	public function __construct($name, Broker $broker)
	{
		$this->name = $name;
		$this->broker = $broker;
	}

	/**
	 * Return a class reflection.
	 *
	 * @param string $className Class name
	 * @return \TokenReflection\ReflectionClass
	 * @throws \TokenReflection\Exception\Runtime If the requested class reflection does not exist
	 */
	public function getClass($className)
	{
		$className = ltrim($className, '\\');
		if (false === strpos($className, '\\') && self::NO_NAMESPACE_NAME !== $this->getName()) {
			$className = $this->getName() . '\\' . $className;
		}

		if (!isset($this->classes[$className])) {
			throw new Exception\Runtime(sprintf('Class "%s" does not exist.', $className), Exception\Runtime::DOES_NOT_EXIST);
		}

		return $this->classes[$className];
	}

	/**
	 * Returns an array of all class reflections.
	 *
	 * @return array
	 */
	public function getClasses()
	{
		return $this->classes;
	}

	/**
	 * Returns an array of all class names (FQN).
	 *
	 * @return array
	 */
	public function getClassNames()
	{
		return array_keys($this->classes);
	}

	/**
	 * Returns an array of all class names (UQN).
	 *
	 * @return array
	 */
	public function getClassShortNames()
	{
		return array_map(function(IReflectionClass $class) {
			return $class->getShortName();
		}, $this->classes);
	}

	/**
	 * Returns a function reflection.
	 *
	 * @param string $functionName Function name
	 * @return \TokenReflection\ReflectionFunction
	 * @throws \TokenReflection\Exception\Runtime If the required function does not exist
	 */
	public function getFunction($functionName)
	{
		$functionName = ltrim($functionName, '\\');
		if (false === strpos($functionName, '\\') && self::NO_NAMESPACE_NAME !== $this->getName()) {
			$functionName = $this->getName() . '\\' . $functionName;
		}

		if (!isset($this->functions[$functionName])) {
			throw new Exception\Runtime(sprintf('Function "%s" does not exist.', $functionName), Exception\Runtime::DOES_NOT_EXIST);
		}

		return $this->functions[$functionName];
	}

	/**
	 * Returns all function reflections.
	 *
	 * @return array
	 */
	public function getFunctions()
	{
		return $this->functions;
	}

	/**
	 * Returns all function names (FQN).
	 *
	 * @return array
	 */
	public function getFunctionNames()
	{
		return array_keys($this->functions);
	}

	/**
	 * Returns all function names (UQN).
	 *
	 * @return array
	 */
	public function getFunctionShortNames()
	{
		return array_map(function(IReflectionFunction $function) {
			return $function->getShortName();
		}, $this->functions);
	}

	/**
	 * Returns a constant reflection.
	 *
	 * @param string $constantName Constant name
	 * @return \TokenReflection\ReflectionConstant
	 * @throws \TokenReflection\Exception\Runtime If the required constant does not exist
	 */
	public function getConstant($constantName)
	{
		$constantName = ltrim($constantName, '\\');
		if (false === strpos($constantName, '\\') && self::NO_NAMESPACE_NAME !== $this->getName()) {
			$constantName = $this->getName() . '\\' . $constantName;
		}

		if (!isset($this->constants[$constantName])) {
			throw new Exception\Runtime(sprintf('Constant "%s" does not exist.', $constantName), Exception\Runtime::DOES_NOT_EXIST);
		}

		return $this->constants[$constantName];
	}

	/**
	 * Returns all constant reflections.
	 *
	 * @return array
	 */
	public function getConstants()
	{
		return $this->constants;
	}

	/**
	 * Returns all constant names (FQN).
	 *
	 * @return array
	 */
	public function getConstantNames()
	{
		return array_keys($this->constants);
	}

	/**
	 * Returns all constant names (UQN).
	 *
	 * @return array
	 */
	public function getConstantShortNames()
	{
		return array_map(function(IReflectionConstant $constant) {
			return $constant->getShortName();
		}, $this->constants);
	}

	/**
	 * Returns the reflection subject name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the reflection broker used by this reflection object.
	 *
	 * @return \TokenReflection\Broker|null
	 */
	public function getBroker()
	{
		return $this->broker;
	}

	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return boolean
	 */
	public function isTokenized()
	{
		return true;
	}

	/**
	 * Returns if the namespace is internal.
	 *
	 * Always false.
	 *
	 * @return boolean
	 */
	public function isInternal()
	{
		return false;
	}

	/**
	 * Returns if the namespace is user defined.
	 *
	 * Always true.
	 *
	 * @return boolean
	 */
	public function isUserDefined()
	{
		return true;
	}


	/**
	 * Adds a namespace part from a file.
	 *
	 * @param \TokenReflection\ReflectionFileNamespace $namespace Namespace part
	 * @throws \TokenReflection\Exception\Runtime If one of classes form the namespace are already defined
	 * @throws \TokenReflection\Exception\Runtime If one of functions form the namespace are already defined
	 * @throws \TokenReflection\Exception\Runtime If one of constants form the namespace are already defined
	 */
	public function addFileNamespace(ReflectionFileNamespace $namespace)
	{
		$classes = $namespace->getClasses();
		foreach ($this->classes as $className => $reflection) {
			if (isset($classes[$className])) {
				throw new Exception\Runtime(sprintf('Class "%s" is already defined; in file "%s".', $className, $classes[$className]->getFileName()), Exception\Runtime::ALREADY_EXISTS);
			}
		}
		$this->classes = array_merge($this->classes, $classes);

		$functions = $namespace->getFunctions();
		foreach ($this->functions as $functionName => $reflection) {
			if (isset($functions[$functionName])) {
				throw new Exception\Runtime(sprintf('Function "%s" is already defined; in file "%s".', $functionName, $functions[$functionName]->getFileName()), Exception\Runtime::ALREADY_EXISTS);
			}
		}
		$this->functions = array_merge($this->functions, $functions);

		$constants = $namespace->getConstants();
		foreach ($this->constants as $constantName => $reflection) {
			if (isset($constants[$constantName])) {
				throw new Exception\Runtime(sprintf('Constant "%s" is already defined; in file "%s".', $constantName, $constants[$constantName]->getFileName()), Exception\Runtime::ALREADY_EXISTS);
			}
		}
		$this->constants = array_merge($this->constants, $constants);
	}

	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString()
	{
		// @todo
		return '';
	}

	/**
	 * Returns the appropriate source code part.
	 *
	 * Impossible for namespaces.
	 *
	 * @throws \TokenReflection\Exception\RuntimeException
	 */
	public function getSource()
	{
		throw new \RuntimeException('Cannot export source code of a namespace.');
	}

	/**
	 * Exports a reflected object.
	 *
	 * @param string $argument Reflection object name
	 * @param boolean $return Return the export instead of outputting it
	 * @return string|null
	 */
	public static function export($argument, $return = false)
	{
		return ReflectionBase::export($argument, $return);
	}

	/**
	 * Magic __get method.
	 *
	 * @param string $key Variable name
	 * @return mixed
	 */
	final public function __get($key)
	{
		return ReflectionBase::get($this, $key);
	}

	/**
	 * Magic __isset method.
	 *
	 * @param string $key Variable name
	 * @return boolean
	 */
	final public function __isset($key)
	{
		return ReflectionBase::exists($this, $key);
	}
}