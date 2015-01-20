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
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\FileProcessingException;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Invalid;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\ReflectionInterface;
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
	 * @var Broker
	 */
	private $broker;

	/**
	 * @var array
	 */
	private $errors = [];


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
			throw new RuntimeException(sprintf('Class "%s" does not exist.', $className), RuntimeException::DOES_NOT_EXIST);
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
			throw new RuntimeException(sprintf('Constant "%s" does not exist.', $constantName), RuntimeException::DOES_NOT_EXIST);
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
			throw new RuntimeException(sprintf('Function "%s" does not exist.', $functionName), RuntimeException::DOES_NOT_EXIST);
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


	public function getSource()
	{
		throw new RuntimeException('Cannot export source code of a namespace.', RuntimeException::UNSUPPORTED);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getBroker()
	{
		return $this->broker;
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
