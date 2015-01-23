<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Broker;

use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\Reflection\ReflectionFile;
use ApiGen\TokenReflection\ReflectionNamespaceInterface;
use ApiGen\TokenReflection\Stream\StreamBase;


interface BrokerInterface
{

	/**
	 * @var string
	 */
	const CACHE_NAMESPACE = 'namespace';

	/**
	 * @var string
	 */
	const CACHE_CLASS = 'class';

	/**
	 * @var string
	 */
	const CACHE_CONSTANT = 'constant';

	/**
	 * @var string
	 */
	const CACHE_FUNCTION = 'function';


	/**
	 * @param string $name
	 * @return ReflectionFile[]
	 */
	function processFile($name);


	/**
	 * @param string $path
	 * @return ReflectionFile[]
	 */
	function processDirectory($path);


	/**
	 * @param string $name
	 * @return bool
	 */
	function hasNamespace($name);


	/**
	 * @param string $name
	 * @return ReflectionNamespaceInterface|NULL
	 */
	function getNamespace($name);


	/**
	 * @return ReflectionNamespaceInterface[]
	 */
	function getNamespaces();


	/**
	 * @param string $name
	 * @return bool
	 */
	function hasClass($name);


	/**
	 * @param string $name
	 * @return ReflectionClassInterface|NULL
	 */
	function getClass($name);


	/**
	 * @param int $types
	 * @return ReflectionClassInterface[]
	 */
	function getClasses($types = StorageInterface::TOKENIZED_CLASSES);


	/**
	 * @param string $name
	 * @return bool
	 */
	function hasConstant($name);


	/**
	 * Returns a reflection object of a constant (FQN expected).
	 *
	 * @param string $name
	 * @return ReflectionConstantInterface|NULL
	 */
	function getConstant($name);


	/**
	 * @return ReflectionConstantInterface[]
	 */
	function getConstants();


	/**
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
	 * @return ReflectionFunctionInterface[]
	 */
	function getFunctions();


	/**
	 * @param string $name
	 * @return bool
	 */
	function hasFile($name);


	/**
	 * @param string $name
	 * @return ReflectionFile|NULL
	 */
	function getFile($name);


	/**
	 * @return ReflectionFile[]
	 */
	function getFiles();


	/**
	 * @param string $name
	 * @return StreamBase|NULL
	 */
	function getFileTokens($name);

}
