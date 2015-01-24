<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Storage;

use ApiGen;
use ApiGen\TokenReflection;
use ApiGen\TokenReflection\Reflection\ReflectionFile;
use ApiGen\TokenReflection\Reflection\ReflectionNamespace;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\ReflectionNamespaceInterface;
use ApiGen\TokenReflection\Stream\StreamBase;


interface StorageInterface
{

	/**
	 * Identifier of the tokenized classes list.
	 *
	 * @var int
	 */
	const TOKENIZED_CLASSES = 1;

	/**
	 * Identifier of the internal classes list.
	 *
	 * @var int
	 */
	const INTERNAL_CLASSES = 2;

	/**
	 * Identifier of the nonexisten classes list.
	 *
	 * @var int
	 */
	const NONEXISTENT_CLASSES = 4;


	/**
	 * Adds new namespace reflection
	 * @param string $name
	 * @param ReflectionNamespace $reflectionNamespace
	 */
	function addNamespace($name, ReflectionNamespace $reflectionNamespace);


	/**
	 * Returns if there was such namespace processed (FQN expected).
	 *
	 * @param string $name
	 * @return bool
	 */
	function hasNamespace($name);


	/**
	 * Returns a reflection object of the given namespace.
	 *
	 * @param string $name
	 * @return ReflectionNamespaceInterface|NULL
	 */
	function getNamespace($name);


	/**
	 * Returns all namespaces.
	 *
	 * @return array
	 */
	function getNamespaces();


	/**
	 * Returns if there was such class processed (FQN expected).
	 *
	 * @param string $name
	 * @return bool
	 */
	function hasClass($name);


	/**
	 * Returns a reflection object of the given class (FQN expected).
	 *
	 * @param string $name
	 * @return ReflectionClassInterface|NULL
	 */
	function getClass($name);


	/**
	 * Returns all classes from all namespaces.
	 *
	 * @param int $type Returned class types (multiple values may be OR-ed)
	 * @return array
	 */
	function getClasses($type = StorageInterface::TOKENIZED_CLASSES);


	/**
	 * Returns if there was such constant processed (FQN expected).
	 *
	 * @param string $constantName
	 * @return bool
	 */
	function hasConstant($constantName);


	/**
	 * Returns a reflection object of a constant (FQN expected).
	 *
	 * @param string $name
	 * @return ReflectionConstantInterface|NULL
	 */
	function getConstant($name);


	/**
	 * Returns all constants from all namespaces.
	 *
	 * @return array
	 */
	function getConstants();


	/**
	 * Returns if there was such function processed (FQN expected).
	 *
	 * @param string $name
	 * @return bool
	 */
	function hasFunction($name);


	/**
	 * Returns a reflection object of a function (FQN expected).
	 *
	 * @param string $name
	 * @return ReflectionFunctionInterface|NULL
	 */
	function getFunction($name);


	/**
	 * Returns all functions from all namespaces.
	 *
	 * @return array
	 */
	function getFunctions();


	function addFile(ReflectionFile $file);


	/**
	 * Returns if a file with the given filename has been processed.
	 *
	 * @param string $name
	 * @return bool
	 */
	function hasFile($name);


	/**
	 * @param string $name
	 * @return ReflectionFile
	 */
	function getFile($name);


	/**
	 * @return ReflectionFile[]
	 */
	function getFiles();

}
