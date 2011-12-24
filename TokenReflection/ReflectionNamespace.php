<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.0 RC 2
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor
 * @author Jaroslav Hanslík
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
	 * List of constant reflections.
	 *
	 * @var array
	 */
	private $constants = array();

	/**
	 * List of function reflections.
	 *
	 * @var array
	 */
	private $functions = array();

	/**
	 * Reflection broker.
	 *
	 * @var \TokenReflection\Broker
	 */
	private $broker;

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
	 * Returns the name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
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
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return boolean
	 */
	public function isTokenized()
	{
		return true;
	}

	/**
	 * Returns if the namespace contains a class of the given name.
	 *
	 * @param string $className Class name
	 * @return boolean
	 */
	public function hasClass($className)
	{
		$className = ltrim($className, '\\');
		if (false === strpos($className, '\\') && self::NO_NAMESPACE_NAME !== $this->getName()) {
			$className = $this->getName() . '\\' . $className;
		}

		return isset($this->classes[$className]);
	}

	/**
	 * Return a class reflection.
	 *
	 * @param string $className Class name
	 * @return \TokenReflection\ReflectionClass
	 * @throws \TokenReflection\Exception\Runtime If the requested class reflection does not exist.
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
	 * Returns class reflections.
	 *
	 * @return array
	 */
	public function getClasses()
	{
		return $this->classes;
	}

	/**
	 * Returns class names (FQN).
	 *
	 * @return array
	 */
	public function getClassNames()
	{
		return array_keys($this->classes);
	}

	/**
	 * Returns class unqualified names (UQN).
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
	 * Returns if the namespace contains a constant of the given name.
	 *
	 * @param string $constantName Constant name
	 * @return boolean
	 */
	public function hasConstant($constantName)
	{
		$constantName = ltrim($constantName, '\\');
		if (false === strpos($constantName, '\\') && self::NO_NAMESPACE_NAME !== $this->getName()) {
			$constantName = $this->getName() . '\\' . $constantName;
		}

		return isset($this->constants[$constantName]);
	}

	/**
	 * Returns a constant reflection.
	 *
	 * @param string $constantName Constant name
	 * @return \TokenReflection\ReflectionConstant
	 * @throws \TokenReflection\Exception\Runtime If the required constant does not exist.
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
	 * Returns constant reflections.
	 *
	 * @return array
	 */
	public function getConstants()
	{
		return $this->constants;
	}

	/**
	 * Returns constant names (FQN).
	 *
	 * @return array
	 */
	public function getConstantNames()
	{
		return array_keys($this->constants);
	}

	/**
	 * Returns constant unqualified names (UQN).
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
	 * Returns if the namespace contains a function of the given name.
	 *
	 * @param string $functionName Function name
	 * @return boolean
	 */
	public function hasFunction($functionName)
	{
		$functionName = ltrim($functionName, '\\');
		if (false === strpos($functionName, '\\') && self::NO_NAMESPACE_NAME !== $this->getName()) {
			$functionName = $this->getName() . '\\' . $functionName;
		}

		return isset($this->functions[$functionName]);
	}

	/**
	 * Returns a function reflection.
	 *
	 * @param string $functionName Function name
	 * @return \TokenReflection\ReflectionFunction
	 * @throws \TokenReflection\Exception\Runtime If the required function does not exist.
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
	 * Returns function reflections.
	 *
	 * @return array
	 */
	public function getFunctions()
	{
		return $this->functions;
	}

	/**
	 * Returns function names (FQN).
	 *
	 * @return array
	 */
	public function getFunctionNames()
	{
		return array_keys($this->functions);
	}

	/**
	 * Returns function unqualified names (UQN).
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
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$buffer = '';
		$count = 0;
		foreach ($this->getClasses() as $class) {
			$string = "\n    " . trim(str_replace("\n", "\n    ", $class->__toString()), ' ');
			$string = str_replace("    \n      - Parameters", "\n      - Parameters", $string);

			$buffer .= $string;
			$count++;
		}
		$classes = sprintf("\n\n  - Classes [%d] {\n%s  }", $count, ltrim($buffer, "\n"));

		$buffer = '';
		$count = 0;
		foreach ($this->getConstants() as $constant) {
			$buffer .= '    ' . $constant->__toString();
			$count++;
		}
		$constants = sprintf("\n\n  - Constants [%d] {\n%s  }", $count, $buffer);

		$buffer = '';
		$count = 0;
		foreach ($this->getFunctions() as $function) {
			$string = "\n    " . trim(str_replace("\n", "\n    ", $function->__toString()), ' ');
			$string = str_replace("    \n      - Parameters", "\n      - Parameters", $string);

			$buffer .= $string;
			$count++;
		}
		$functions = sprintf("\n\n  - Functions [%d] {\n%s  }", $count, ltrim($buffer, "\n"));

		return sprintf(
			"Namespace [ <user> namespace %s ] {  %s%s%s\n}\n",
			$this->getName(),
			$classes,
			$constants,
			$functions
		);
	}

	/**
	 * Exports a reflected object.
	 *
	 * @param \TokenReflection\Broker $broker Broker instance
	 * @param string $namespace Namespace name
	 * @param boolean $return Return the export instead of outputting it
	 * @return string|null
	 * @throws \TokenReflection\Exception\Runtime If requested parameter doesn't exist.
	 */
	public static function export(Broker $broker, $namespace, $return = false)
	{
		$namespaceName = $namespace;

		$namespace = $broker->getNamespace($namespaceName);
		if (null === $namespace) {
			throw new Exception\Runtime(sprintf('Namespace %s does not exist.', $namespaceName), Exception\Runtime::DOES_NOT_EXIST);
		}

		if ($return) {
			return $namespace->__toString();
		}

		echo $namespace->__toString();
	}

	/**
	 * Adds a namespace part from a file.
	 *
	 * @param \TokenReflection\ReflectionFileNamespace $namespace Namespace part
	 * @throws \TokenReflection\Exception\Runtime If one of classes form the namespace are already defined.
	 * @throws \TokenReflection\Exception\Runtime If one of functions form the namespace are already defined.
	 * @throws \TokenReflection\Exception\Runtime If one of constants form the namespace are already defined.
	 */
	public function addFileNamespace(ReflectionFileNamespace $namespace)
	{
		$classes = $namespace->getClasses();
		foreach ($this->classes as $className => $reflection) {
			if (isset($classes[$className])) {
				throw new Exception\Runtime(sprintf('Class "%s" is already defined; in file "%s".', $className, $reflection->getFileName()), Exception\Runtime::ALREADY_EXISTS);
			}
		}
		$this->classes = array_merge($this->classes, $classes);

		$functions = $namespace->getFunctions();
		foreach ($this->functions as $functionName => $reflection) {
			if (isset($functions[$functionName])) {
				throw new Exception\Runtime(sprintf('Function "%s" is already defined; in file "%s".', $functionName, $reflection->getFileName()), Exception\Runtime::ALREADY_EXISTS);
			}
		}
		$this->functions = array_merge($this->functions, $functions);

		$constants = $namespace->getConstants();
		foreach ($this->constants as $constantName => $reflection) {
			if (isset($constants[$constantName])) {
				throw new Exception\Runtime(sprintf('Constant "%s" is already defined; in file "%s".', $constantName, $reflection->getFileName()), Exception\Runtime::ALREADY_EXISTS);
			}
		}
		$this->constants = array_merge($this->constants, $constants);
	}

	/**
	 * Returns the appropriate source code part.
	 *
	 * Impossible for namespaces.
	 *
	 * @throws \TokenReflection\Exception\Runtime If the method is called, because it's unsupported.
	 */
	public function getSource()
	{
		throw new Exception\Runtime('Cannot export source code of a namespace.', Exception\Runtime::UNSUPPORTED);
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
	 * Magic __get method.
	 *
	 * @param string $key Variable name
	 * @return mixed
	 */
	final public function __get($key)
	{
		return ReflectionElement::get($this, $key);
	}

	/**
	 * Magic __isset method.
	 *
	 * @param string $key Variable name
	 * @return boolean
	 */
	final public function __isset($key)
	{
		return ReflectionElement::exists($this, $key);
	}
}