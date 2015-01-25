<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\PhpParser;

use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Reflection\ReflectionFileNamespace;
use ApiGen\TokenReflection\Reflection\ReflectionNamespace;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\ReflectionNamespaceInterface;


class NamespaceReflection implements ReflectionNamespaceInterface
{

	/**
	 * @var string
	 */
	const NAMESPACE_SEP = '\\';

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var ReflectionClassInterface[]
	 */
	private $classes;

	/**
	 * @var ReflectionConstantInterface[]
	 */
	private $constants;

	/**
	 * @var ReflectionFunctionInterface[]
	 */
	private $functions;


	/**
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
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
	public function getPrettyName()
	{
		return $this->getName();
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
	public function hasClass($name)
	{
		$name = $this->getFqnElementName($name);
		return isset($this->classes[$name]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClass($name)
	{
		$name = $this->getFqnElementName($name);
		if ($this->hasClass($name)) {
			return $this->classes[$name];
		}

		throw new RuntimeException("Class '$name' does not exist.");
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
	public function hasConstant($name)
	{
		$name= $this->getFqnElementName($name);
		return isset($this->constants[$name]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstant($name)
	{
		$name = $this->getFqnElementName($name);
		if ($this->hasConstant($name)) {
			return $this->constants[$name];
		}
		throw new RuntimeException("Constant '$name' does not exist.");
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstants()
	{
		return $this->constants;
	}




	/**
	 * Returns if the namespace contains a function of the given name.
	 *
	 * @param string $functionName
	 * @return bool
	 */
	function hasFunction($functionName)
	{
		// TODO: Implement hasFunction() method.
	}


	/**
	 * Returns a function reflection.
	 *
	 * @param string $functionName
	 * @return ReflectionFunctionInterface
	 */
	function getFunction($functionName)
	{
		// TODO: Implement getFunction() method.
	}


	/**
	 * Returns function reflections.
	 *
	 * @return array|ReflectionFunctionInterface[]
	 */
	function getFunctions()
	{
		// TODO: Implement getFunctions() method.
	}


	/**
	 * @return ReflectionNamespaceInterface
	 */
	function addFileNamespace(ReflectionFileNamespace $namespace)
	{
		// TODO: Implement addFileNamespace() method.
	}



	/**
	 * @param string $elementName
	 * @return string
	 */
	private function getFqnElementName($elementName)
	{
		$elementName = ltrim($elementName, self::NAMESPACE_SEP);
		if (strpos($elementName, self::NAMESPACE_SEP) === FALSE && $this->getName() !== ReflectionNamespace::NO_NAMESPACE_NAME) {
			$elementName = $this->getName() . self::NAMESPACE_SEP . $elementName;
		}
		return $elementName;
	}

}
