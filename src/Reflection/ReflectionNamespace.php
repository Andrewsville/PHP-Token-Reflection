<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection;

use ApiGen\TokenReflection\Behaviors\ReasonsInterface;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Broker\StorageInterface;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\ReflectionNamespaceInterface;


class ReflectionNamespace implements ReflectionNamespaceInterface
{

	/**
	 * The name of the pseudo-namespace meaning there is no namespace.
	 *
	 * @var string
	 */
	const NO_NAMESPACE_NAME = 'no-namespace';

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var ReflectionClassInterface[]|ReasonsInterface[]
	 */
	private $classes = [];

	/**
	 * @var ReflectionConstantInterface[]|ReasonsInterface[]
	 */
	private $constants = [];

	/**
	 * @var ReflectionFunctionInterface[]|ReasonsInterface[]
	 */
	private $functions = [];

	/**
	 * @var StorageInterface
	 */
	private $storage;


	/**
	 * @param string $name
	 * @param StorageInterface $storage
	 */
	public function __construct($name, StorageInterface $storage)
	{
		$this->name = $name;
		$this->storage = $storage;
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
		$className = $this->getFqnElementName($className);
		return isset($this->classes[$className]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClass($className)
	{
		$className = $this->getFqnElementName($className);
		if ( ! $this->hasClass($className)) {
			throw new RuntimeException(sprintf('Class "%s" does not exist.', $className));
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
		$constantName = $this->getFqnElementName($constantName);
		return isset($this->constants[$constantName]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstant($constantName)
	{
		$constantName = $this->getFqnElementName($constantName);
		if ( ! $this->hasConstant($constantName)) {
			throw new RuntimeException(sprintf('Constant "%s" does not exist.', $constantName));
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
		$functionName = $this->getFqnElementName($functionName);
		return isset($this->functions[$functionName]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFunction($functionName)
	{
		$functionName = $this->getFqnElementName($functionName);
		if ( ! $this->hasFunction($functionName)) {
			throw new RuntimeException(sprintf('Function "%s" does not exist.', $functionName));
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
	 * @return ReflectionNamespace
	 */
	public function addFileNamespace(ReflectionFileNamespace $namespace)
	{
		foreach ($namespace->getClasses() as $className => $reflection) {
			$this->classes[$className] = $reflection;
		}

		foreach ($namespace->getFunctions() as $functionName => $reflection) {
			$this->functions[$functionName] = $reflection;
		}

		foreach ($namespace->getConstants() as $constantName => $reflection) {
			$this->constants[$constantName] = $reflection;
		}

		return $this;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getStorage()
	{
		return $this->storage;
	}


	/**
	 * @param string $elementName
	 * @return string
	 */
	private function getFqnElementName($elementName)
	{
		$elementName = ltrim($elementName, '\\');
		if (strpos($elementName, '\\') === FALSE && $this->getName() !== self::NO_NAMESPACE_NAME) {
			$elementName = $this->getName() . '\\' . $elementName;
		}
		return $elementName;
	}

}
