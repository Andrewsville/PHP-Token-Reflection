<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\PhpParser;

use ApiGen\TokenReflection\Reflection\ReflectionFileNamespace;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\ReflectionNamespaceInterface;


class NamespaceReflection implements ReflectionNamespaceInterface
{

	public function __construct($name)
	{
		dump($name);
	}

	/**
	 * Returns the name (FQN).
	 *
	 * @return string
	 */
	function getName()
	{
		// TODO: Implement getName() method.
	}


	/**
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	function getPrettyName()
	{
		// TODO: Implement getPrettyName() method.
	}


	/**
	 * Returns if the reflection object is internal.
	 *
	 * @return bool
	 */
	function isInternal()
	{
		// TODO: Implement isInternal() method.
	}


	/**
	 * Returns if the reflection object is user defined.
	 *
	 * @return bool
	 */
	function isUserDefined()
	{
		// TODO: Implement isUserDefined() method.
	}


	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return bool
	 */
	function isTokenized()
	{
		// TODO: Implement isTokenized() method.
	}


	/**
	 * Returns if the namespace contains a class of the given name.
	 *
	 * @param string $className Class name
	 * @return bool
	 */
	function hasClass($className)
	{
		// TODO: Implement hasClass() method.
	}


	/**
	 * Return a class reflection.
	 *
	 * @param string $className
	 * @return ReflectionClassInterface
	 */
	function getClass($className)
	{
		// TODO: Implement getClass() method.
	}


	/**
	 * Returns class reflections.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getClasses()
	{
		// TODO: Implement getClasses() method.
	}


	/**
	 * Returns class names (FQN).
	 *
	 * @return array|string[]
	 */
	function getClassNames()
	{
		// TODO: Implement getClassNames() method.
	}


	/**
	 * Returns class unqualified names (UQN).
	 *
	 * @return array|string[]
	 */
	function getClassShortNames()
	{
		// TODO: Implement getClassShortNames() method.
	}


	/**
	 * Returns if the namespace contains a constant of the given name.
	 *
	 * @param string $constantName
	 * @return bool
	 */
	function hasConstant($constantName)
	{
		// TODO: Implement hasConstant() method.
	}


	/**
	 * Returns a constant reflection.
	 *
	 * @param string $constantName
	 * @return ReflectionConstantInterface
	 */
	function getConstant($constantName)
	{
		// TODO: Implement getConstant() method.
	}


	/**
	 * Returns constant reflections.
	 *
	 * @return array|ReflectionConstantInterface[]
	 */
	function getConstants()
	{
		// TODO: Implement getConstants() method.
	}


	/**
	 * Returns constant names (FQN).
	 *
	 * @return array|string[]
	 */
	function getConstantNames()
	{
		// TODO: Implement getConstantNames() method.
	}


	/**
	 * Returns constant unqualified names (UQN).
	 *
	 * @return array|string[]
	 */
	function getConstantShortNames()
	{
		// TODO: Implement getConstantShortNames() method.
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
	 * Returns function names (FQN).
	 *
	 * @return array|string[]
	 */
	function getFunctionNames()
	{
		// TODO: Implement getFunctionNames() method.
	}


	/**
	 * Returns function unqualified names (UQN).
	 *
	 * @return array|string[]
	 */
	function getFunctionShortNames()
	{
		// TODO: Implement getFunctionShortNames() method.
	}


	/**
	 * @return ReflectionNamespaceInterface
	 */
	function addFileNamespace(ReflectionFileNamespace $namespace)
	{
		// TODO: Implement addFileNamespace() method.
	}
}
