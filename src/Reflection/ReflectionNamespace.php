<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\FileProcessingException;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Invalid;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\ReflectionNamespaceInterface;
use ApiGen\TokenReflection\Reflection\ReflectionElement;
use ApiGen\TokenReflection\Reflection\ReflectionFileNamespace;


class ReflectionNamespace implements ReflectionNamespaceInterface
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
	 * @var array|ReflectionClassInterface[]
	 */
	private $classes = [];

	/**
	 * List of constant reflections.
	 *
	 * @var array|ReflectionConstantInterface[]
	 */
	private $constants = [];

	/**
	 * List of function reflections.
	 *
	 * @var array|ReflectionFunctionInterface[]
	 */
	private $functions = [];

	/**
	 * @var Broker
	 */
	private $broker;


	/**
	 * @param string $name
	 * @param Broker $broker
	 */
	public function __construct($name, Broker $broker)
	{
		$this->name = $name;
		$this->broker = $broker;
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
	public function isInternal()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isUserDefined()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isTokenized()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasClass($className)
	{
		$className = ltrim($className, '\\');
		if (FALSE === strpos($className, '\\') && self::NO_NAMESPACE_NAME !== $this->getName()) {
			$className = $this->getName() . '\\' . $className;
		}
		return isset($this->classes[$className]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClass($className)
	{
		$className = ltrim($className, '\\');
		if (FALSE === strpos($className, '\\') && self::NO_NAMESPACE_NAME !== $this->getName()) {
			$className = $this->getName() . '\\' . $className;
		}
		if ( ! isset($this->classes[$className])) {
			throw new RuntimeException(sprintf('Class "%s" does not exist.', $className), RuntimeException::DOES_NOT_EXIST, $this);
		}
		return $this->classes[$className];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClasses()
	{
		return $this->classes;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClassNames()
	{
		return array_keys($this->classes);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClassShortNames()
	{
		return array_map(function (ReflectionClassInterface $class) {
			return $class->getShortName();
		}, $this->classes);
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasConstant($constantName)
	{
		$constantName = ltrim($constantName, '\\');
		if (FALSE === strpos($constantName, '\\') && self::NO_NAMESPACE_NAME !== $this->getName()) {
			$constantName = $this->getName() . '\\' . $constantName;
		}
		return isset($this->constants[$constantName]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstant($constantName)
	{
		$constantName = ltrim($constantName, '\\');
		if (FALSE === strpos($constantName, '\\') && self::NO_NAMESPACE_NAME !== $this->getName()) {
			$constantName = $this->getName() . '\\' . $constantName;
		}
		if ( ! isset($this->constants[$constantName])) {
			throw new RuntimeException(sprintf('Constant "%s" does not exist.', $constantName), RuntimeException::DOES_NOT_EXIST, $this);
		}
		return $this->constants[$constantName];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstants()
	{
		return $this->constants;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstantNames()
	{
		return array_keys($this->constants);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstantShortNames()
	{
		return array_map(function (ReflectionConstantInterface $constant) {
			return $constant->getShortName();
		}, $this->constants);
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasFunction($functionName)
	{
		$functionName = ltrim($functionName, '\\');
		if (FALSE === strpos($functionName, '\\') && self::NO_NAMESPACE_NAME !== $this->getName()) {
			$functionName = $this->getName() . '\\' . $functionName;
		}
		return isset($this->functions[$functionName]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFunction($functionName)
	{
		$functionName = ltrim($functionName, '\\');
		if (FALSE === strpos($functionName, '\\') && self::NO_NAMESPACE_NAME !== $this->getName()) {
			$functionName = $this->getName() . '\\' . $functionName;
		}
		if ( ! isset($this->functions[$functionName])) {
			throw new RuntimeException(sprintf('Function "%s" does not exist.', $functionName), RuntimeException::DOES_NOT_EXIST, $this);
		}
		return $this->functions[$functionName];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFunctions()
	{
		return $this->functions;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFunctionNames()
	{
		return array_keys($this->functions);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFunctionShortNames()
	{
		return array_map(function (ReflectionFunctionInterface $function) {
			return $function->getShortName();
		}, $this->functions);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return $this->name;
	}


	/**
	 * Adds a namespace part from a file.
	 *
	 * @return ReflectionNamespace
	 * @throws FileProcessingException If one of classes, functions or constants form the namespace are already defined
	 */
	public function addFileNamespace(ReflectionFileNamespace $namespace)
	{
		$errors = [];
		foreach ($namespace->getClasses() as $className => $reflection) {
			if ($reflection instanceof Invalid\ReflectionClass) {
				$errors = array_merge($errors, $reflection->getReasons());
			}
			if (isset($this->classes[$className])) {
				if ( ! $this->classes[$className] instanceof Invalid\ReflectionClass) {
					$this->classes[$className] = new Invalid\ReflectionClass($className, $this->classes[$className]->getFileName(), $this->getBroker());
				}
				$error = new RuntimeException(
					sprintf('Class %s was redeclared (previously declared in file %s).', $className, $this->classes[$className]->getFileName()),
					RuntimeException::ALREADY_EXISTS,
					$reflection
				);
				$errors[] = $error;
				$this->classes[$className]->addReason($error);
				if ($reflection instanceof Invalid\ReflectionClass) {
					foreach ($reflection->getReasons() as $reason) {
						$this->classes[$className]->addReason($reason);
					}
				}
			} else {
				$this->classes[$className] = $reflection;
			}
		}
		foreach ($namespace->getFunctions() as $functionName => $reflection) {
			if ($reflection instanceof Invalid\ReflectionFunction) {
				$errors = array_merge($errors, $reflection->getReasons());
			}
			if (isset($this->functions[$functionName])) {
				if ( ! $this->functions[$functionName] instanceof Invalid\ReflectionFunction) {
					$this->functions[$functionName] = new Invalid\ReflectionFunction($functionName, $this->functions[$functionName]->getFileName(), $this->getBroker());
				}
				$error = new RuntimeException(
					sprintf('Function %s was redeclared (previousy declared in file %s).', $functionName, $this->functions[$functionName]->getFileName()),
					RuntimeException::ALREADY_EXISTS,
					$reflection
				);
				$errors[] = $error;
				$this->functions[$functionName]->addReason($error);
				if ($reflection instanceof Invalid\ReflectionFunction) {
					foreach ($reflection->getReasons() as $reason) {
						$this->functions[$functionName]->addReason($reason);
					}
				}
			} else {
				$this->functions[$functionName] = $reflection;
			}
		}
		foreach ($namespace->getConstants() as $constantName => $reflection) {
			if ($reflection instanceof Invalid\ReflectionConstant) {
				$errors = array_merge($errors, $reflection->getReasons());
			}
			if (isset($this->constants[$constantName])) {
				if ( ! $this->constants[$constantName] instanceof Invalid\ReflectionConstant) {
					$this->constants[$constantName] = new Invalid\ReflectionConstant($constantName, $this->constants[$constantName]->getFileName(), $this->getBroker());
				}
				$error = new RuntimeException(
					sprintf('Constant %s was redeclared (previuosly declared in file %s).', $constantName, $this->constants[$constantName]->getFileName()),
					RuntimeException::ALREADY_EXISTS,
					$reflection
				);
				$errors[] = $error;
				$this->constants[$constantName]->addReason($error);
				if ($reflection instanceof Invalid\ReflectionConstant) {
					foreach ($reflection->getReasons() as $reason) {
						$this->constants[$constantName]->addReason($reason);
					}
				}
			} else {
				$this->constants[$constantName] = $reflection;
			}
		}
		if ( ! empty($errors)) {
			throw new FileProcessingException($errors, NULL);
		}
		return $this;
	}


	/**
	 * Returns the appropriate source code part.
	 *
	 * Impossible for namespaces.
	 *
	 * @throws RuntimeException If the method is called, because it's unsupported.
	 */
	public function getSource()
	{
		throw new RuntimeException('Cannot export source code of a namespace.', RuntimeException::UNSUPPORTED, $this);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getBroker()
	{
		return $this->broker;
	}

}
