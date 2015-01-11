<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */
namespace ApiGen\TokenReflection\Broker;

use ApiGen\TokenReflection;


/**
 * Defines methods for storing and retrieving reflection objects.
 */
interface Backend
{

	/**
	 * Identifier of the tokenized classes list.
	 *
	 * @var integer
	 */
	const TOKENIZED_CLASSES = 1;

	/**
	 * Identifier of the internal classes list.
	 *
	 * @var integer
	 */
	const INTERNAL_CLASSES = 2;

	/**
	 * Identifier of the nonexisten classes list.
	 *
	 * @var integer
	 */
	const NONEXISTENT_CLASSES = 4;


	/**
	 * Returns if there was such namespace processed (FQN expected).
	 *
	 * @param string $namespaceName Namespace name
	 * @return boolean
	 */
	public function hasNamespace($namespaceName);


	/**
	 * Returns a reflection object of the given namespace.
	 *
	 * @param string $namespaceName Namespace name
	 * @return ApiGen\TokenReflection\IReflectionNamespace|null
	 */
	public function getNamespace($namespaceName);


	/**
	 * Returns if there was such class processed (FQN expected).
	 *
	 * @param string $className Class name
	 * @return boolean
	 */
	public function hasClass($className);


	/**
	 * Returns a reflection object of the given class (FQN expected).
	 *
	 * @param string $className CLass bame
	 * @return ApiGen\TokenReflection\IReflectionClass|null
	 */
	public function getClass($className);


	/**
	 * Returns all classes from all namespaces.
	 *
	 * @param integer $type Returned class types (multiple values may be OR-ed)
	 * @return array
	 */
	public function getClasses($type = Backend::TOKENIZED_CLASSES);


	/**
	 * Returns if there was such constant processed (FQN expected).
	 *
	 * @param string $constantName Constant name
	 * @return boolean
	 */
	public function hasConstant($constantName);


	/**
	 * Returns a reflection object of a constant (FQN expected).
	 *
	 * @param string $constantName Constant name
	 * @return ApiGen\TokenReflection\IReflectionConstant|null
	 */
	public function getConstant($constantName);


	/**
	 * Returns all constants from all namespaces.
	 *
	 * @return array
	 */
	public function getConstants();


	/**
	 * Returns if there was such function processed (FQN expected).
	 *
	 * @param string $functionName Function name
	 * @return boolean
	 */
	public function hasFunction($functionName);


	/**
	 * Returns a reflection object of a function (FQN expected).
	 *
	 * @param string $functionName Function name
	 * @return ApiGen\TokenReflection\IReflectionFunction|null
	 */
	public function getFunction($functionName);


	/**
	 * Returns all functions from all namespaces.
	 *
	 * @return array
	 */
	public function getFunctions();


	/**
	 * Returns if the given file was already processed.
	 *
	 * @param string $fileName File name
	 * @return boolean
	 */
	public function isFileProcessed($fileName);


	/**
	 * Returns if a file with the given filename has been processed.
	 *
	 * @param string $fileName File name
	 * @return boolean
	 */
	public function hasFile($fileName);


	/**
	 * Returns a file reflection.
	 *
	 * @param string $fileName File name
	 * @return ApiGen\TokenReflection\ReflectionFile
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested file has not been processed
	 */
	public function getFile($fileName);


	/**
	 * Returns file reflections.
	 *
	 * @return array
	 */
	public function getFiles();


	/**
	 * Returns an array of tokens for a particular file.
	 *
	 * @param string $fileName File name
	 * @return ApiGen\TokenReflection\Stream\StreamBase
	 */
	public function getFileTokens($fileName);


	/**
	 * Adds a file to the backend storage.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token stream
	 * @param ApiGen\TokenReflection\ReflectionFile $file File reflection object
	 * @return ApiGen\TokenReflection\Broker\Backend
	 */
	public function addFile(TokenReflection\Stream\StreamBase $tokenStream, TokenReflection\ReflectionFile $file);


	/**
	 * Sets the reflection broker instance.
	 *
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker
	 * @return ApiGen\TokenReflection\Broker\Backend
	 */
	public function setBroker(TokenReflection\Broker $broker);


	/**
	 * Returns the reflection broker instance.
	 *
	 * @return ApiGen\TokenReflection\Broker $broker Reflection broker
	 */
	public function getBroker();


	/**
	 * Sets if token streams are stored in the backend.
	 *
	 * @param boolean $store
	 * @return ApiGen\TokenReflection\Broker\Backend
	 */
	public function setStoringTokenStreams($store);


	/**
	 * Returns if token streams are stored in the backend.
	 *
	 * @return boolean
	 */
	public function getStoringTokenStreams();
}
