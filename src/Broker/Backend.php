<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Broker;

use ApiGen;
use ApiGen\TokenReflection;
use ApiGen\TokenReflection\ReflectionFile;
use ApiGen\TokenReflection\Stream\StreamBase;


/**
 * Defines methods for storing and retrieving reflection objects.
 */
interface Backend
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
	 * Returns if there was such namespace processed (FQN expected).
	 *
	 * @param string $namespaceName Namespace name
	 * @return bool
	 */
	function hasNamespace($namespaceName);


	/**
	 * Returns a reflection object of the given namespace.
	 *
	 * @param string $namespaceName Namespace name
	 * @return ApiGen\TokenReflection\IReflectionNamespace|NULL
	 */
	function getNamespace($namespaceName);


	/**
	 * Returns if there was such class processed (FQN expected).
	 *
	 * @param string $className Class name
	 * @return bool
	 */
	function hasClass($className);


	/**
	 * Returns a reflection object of the given class (FQN expected).
	 *
	 * @param string $className CLass bame
	 * @return ApiGen\TokenReflection\IReflectionClass|NULL
	 */
	function getClass($className);


	/**
	 * Returns all classes from all namespaces.
	 *
	 * @param int $type Returned class types (multiple values may be OR-ed)
	 * @return array
	 */
	function getClasses($type = Backend::TOKENIZED_CLASSES);


	/**
	 * Returns if there was such constant processed (FQN expected).
	 *
	 * @param string $constantName Constant name
	 * @return bool
	 */
	function hasConstant($constantName);


	/**
	 * Returns a reflection object of a constant (FQN expected).
	 *
	 * @param string $constantName Constant name
	 * @return ApiGen\TokenReflection\IReflectionConstant|NULL
	 */
	function getConstant($constantName);


	/**
	 * Returns all constants from all namespaces.
	 *
	 * @return array
	 */
	function getConstants();


	/**
	 * Returns if there was such function processed (FQN expected).
	 *
	 * @param string $functionName Function name
	 * @return bool
	 */
	function hasFunction($functionName);


	/**
	 * Returns a reflection object of a function (FQN expected).
	 *
	 * @param string $functionName Function name
	 * @return ApiGen\TokenReflection\IReflectionFunction|NULL
	 */
	function getFunction($functionName);


	/**
	 * Returns all functions from all namespaces.
	 *
	 * @return array
	 */
	function getFunctions();


	/**
	 * Returns if the given file was already processed.
	 *
	 * @param string $fileName File name
	 * @return bool
	 */
	function isFileProcessed($fileName);


	/**
	 * Returns if a file with the given filename has been processed.
	 *
	 * @param string $fileName File name
	 * @return bool
	 */
	function hasFile($fileName);


	/**
	 * Returns a file reflection.
	 *
	 * @param string $fileName File name
	 * @return ReflectionFile
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested file has not been processed
	 */
	function getFile($fileName);


	/**
	 * Returns file reflections.
	 *
	 * @return array
	 */
	function getFiles();


	/**
	 * Returns an array of tokens for a particular file.
	 *
	 * @param string $fileName File name
	 * @return StreamBase
	 */
	function getFileTokens($fileName);


	/**
	 * Adds a file to the backend storage.
	 *
	 * @return ApiGen\TokenReflection\Broker\Backend
	 */
	function addFile(StreamBase $tokenStream, ReflectionFile $file);


	/**
	 * @return Backend
	 */
	function setBroker(Broker $broker);


	/**
	 * @return Broker
	 */
	function getBroker();


	/**
	 * Sets if token streams are stored in the backend.
	 *
	 * @param bool $store
	 * @return Backend
	 */
	function setStoringTokenStreams($store);


	/**
	 * Returns if token streams are stored in the backend.
	 *
	 * @return bool
	 */
	function getStoringTokenStreams();

}
