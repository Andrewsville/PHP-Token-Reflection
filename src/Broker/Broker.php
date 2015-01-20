<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Broker;

use ApiGen\TokenReflection\Exception\BrokerException;
use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\Exception\StreamException;
use ApiGen\TokenReflection\Reflection\ReflectionConstant;
use ApiGen\TokenReflection\Reflection\ReflectionFunction;
use ApiGen\TokenReflection\Reflection\ReflectionNamespace;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\Reflection\ReflectionFile;
use ApiGen\TokenReflection\ReflectionNamespaceInterface;
use ApiGen\TokenReflection\Stream\FileStream;
use ApiGen\TokenReflection\Stream\StreamBase;
use Nette\Utils\Finder;
use SplFileInfo;


class Broker
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
	 * Namespace/class backend.
	 *
	 * @var BackendInterface
	 */
	private $backend;

	/**
	 * Tokenized reflection objects cache.
	 *
	 * @var array
	 */
	private $cache;


	public function __construct(BackendInterface $backend)
	{
		$this->cache = [
			self::CACHE_NAMESPACE => [],
			self::CACHE_CLASS => [],
			self::CACHE_CONSTANT => [],
			self::CACHE_FUNCTION => []
		];

		$this->backend = $backend->setBroker($this);
	}


	/**
	 * Parses a file and returns the appropriate reflection object.
	 *
	 * @param string $fileName Filename
	 * @return bool|ReflectionFile
	 * @throws BrokerException If the file could not be processed.
	 */
	public function processFile($fileName)
	{
		try {
			if ($this->backend->isFileProcessed($fileName)) {
				$tokens = $this->backend->getFileTokens($fileName);

			} else {
				$tokens = new FileStream($fileName);
			}
			$reflectionFile = new ReflectionFile($tokens, $this);
			if ( ! $this->backend->isFileProcessed($fileName)) {
				$this->backend->addFile($tokens, $reflectionFile);
				// Clear the cache - leave only tokenized reflections
				foreach ($this->cache as $type => $cached) {
					if ( ! empty($cached)) {
						$this->cache[$type] = array_filter($cached, function (ReflectionInterface $reflection = NULL) {
							if ($reflection) {
								return $reflection->isTokenized();
							}
							return FALSE;
						});
					}
				}
			}
			return $reflectionFile;

		} catch (ParseException $e) {
			throw $e;

		} catch (StreamException $e) {
			throw new BrokerException(sprintf('Could not process %s file.', $fileName));
		}
	}


	/**
	 * Processes recursively a directory and returns an array of file reflection objects.
	 *
	 * @param string|ReflectionFile[] $path
	 */
	public function processDirectory($path)
	{
		$realPath = realpath($path);
		if ( ! is_dir($realPath)) {
			throw new BrokerException(sprintf('Directory %s does not exist.', $path), BrokerException::DOES_NOT_EXIST);
		}
		try {
			$result = [];
			foreach (Finder::findFiles('*')->in($realPath) as $entry) {
				/** @var SplFileInfo $entry */
				$result[$entry->getPathName()] = $this->processFile($entry->getPathName());
			}
			return $result;

		} catch (ParseException $e) {
			throw $e;

		} catch (StreamException $e) {
			throw new BrokerException(sprintf('Could not process %s directory.', $realPath));
		}
	}


	/**
	 * Returns if the broker contains a namespace of the given name.
	 *
	 * @param string $namespaceName Namespace name
	 * @return bool
	 */
	public function hasNamespace($namespaceName)
	{
		return isset($this->cache[self::CACHE_NAMESPACE][$namespaceName]) || $this->backend->hasNamespace($namespaceName);
	}


	/**
	 * @param string $namespaceName
	 * @return ReflectionNamespace|NULL
	 */
	public function getNamespace($namespaceName)
	{
		$namespaceName = ltrim($namespaceName, '\\');
		if (isset($this->cache[self::CACHE_NAMESPACE][$namespaceName])) {
			return $this->cache[self::CACHE_NAMESPACE][$namespaceName];
		}
		$namespace = $this->backend->getNamespace($namespaceName);
		if ($namespace !== NULL) {
			$this->cache[self::CACHE_NAMESPACE][$namespaceName] = $namespace;
		}
		return $namespace;
	}


	/**
	 * @return array|ReflectionNamespaceInterface[]
	 */
	public function getNamespaces()
	{
		$namespaces = [];
		foreach (array_keys($this->backend->getNamespaces()) as $name) {
			$namespaces[] = $this->getNamespace($name);
		}
		return $namespaces;
	}


	/**
	 * Returns if the broker contains a class of the given name.
	 *
	 * @param string $className Class name
	 * @return bool
	 */
	public function hasClass($className)
	{
		return isset($this->cache[self::CACHE_CLASS][$className]) || $this->backend->hasClass($className);
	}


	/**
	 * @param string $className
	 * @return ReflectionClassInterface|NULL
	 */
	public function getClass($className)
	{
		$className = ltrim($className, '\\');
		if (isset($this->cache[self::CACHE_CLASS][$className])) {
			return $this->cache[self::CACHE_CLASS][$className];
		}
		$this->cache[self::CACHE_CLASS][$className] = $this->backend->getClass($className);
		return $this->cache[self::CACHE_CLASS][$className];
	}


	/**
	 * @param int $types
	 * @return array|ReflectionClassInterface[]
	 */
	public function getClasses($types = BackendInterface::TOKENIZED_CLASSES)
	{
		return $this->backend->getClasses($types);
	}


	/**
	 * @param string $constantName
	 * @return bool
	 */
	public function hasConstant($constantName)
	{
		return isset($this->cache[self::CACHE_CONSTANT][$constantName]) || $this->backend->hasConstant($constantName);
	}


	/**
	 * Returns a reflection object of a constant (FQN expected).
	 *
	 * @param string $constantName
	 * @return ReflectionConstant|NULL
	 */
	public function getConstant($constantName)
	{
		$constantName = ltrim($constantName, '\\');
		if (isset($this->cache[self::CACHE_CONSTANT][$constantName])) {
			return $this->cache[self::CACHE_CONSTANT][$constantName];
		}
		if ($constant = $this->backend->getConstant($constantName)) {
			$this->cache[self::CACHE_CONSTANT][$constantName] = $constant;
		}
		return $constant;
	}


	/**
	 * @return array|ReflectionConstant[]
	 */
	public function getConstants()
	{
		return $this->backend->getConstants();
	}


	/**
	 * @param string $functionName
	 * @return bool
	 */
	public function hasFunction($functionName)
	{
		return isset($this->cache[self::CACHE_FUNCTION][$functionName]) || $this->backend->hasFunction($functionName);
	}


	/**
	 * Returns a reflection object of a function (FQN expected).
	 *
	 * @param string $functionName
	 * @return ReflectionFunction|NULL
	 */
	public function getFunction($functionName)
	{
		$functionName = ltrim($functionName, '\\');
		if (isset($this->cache[self::CACHE_FUNCTION][$functionName])) {
			return $this->cache[self::CACHE_FUNCTION][$functionName];
		}
		if ($function = $this->backend->getFunction($functionName)) {
			$this->cache[self::CACHE_FUNCTION][$functionName] = $function;
		}
		return $function;
	}


	/**
	 * @return array|ReflectionFunction[]
	 */
	public function getFunctions()
	{
		return $this->backend->getFunctions();
	}


	/**
	 * @param string $fileName
	 * @return bool
	 */
	public function hasFile($fileName)
	{
		return $this->backend->hasFile($fileName);
	}


	/**
	 * @param string $fileName
	 * @return ReflectionFile|NULL
	 */
	public function getFile($fileName)
	{
		return $this->backend->getFile($fileName);
	}


	/**
	 * @return array|ReflectionFile[]
	 */
	public function getFiles()
	{
		return $this->backend->getFiles();
	}


	/**
	 * @param string $fileName
	 * @return StreamBase|NULL
	 */
	public function getFileTokens($fileName)
	{
		return $this->backend->getFileTokens($fileName);
	}

}
