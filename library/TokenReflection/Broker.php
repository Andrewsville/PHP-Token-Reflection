<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 2
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file license.txt.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
 */

namespace TokenReflection;

use TokenReflection\Broker, TokenReflection\Exception;
use RecursiveDirectoryIterator, RecursiveIteratorIterator;

/**
 * Reflection broker.
 *
 * Parses files and directories and stores their structure.
 */
class Broker
{
	/**
	 * Cache identifier for classes.
	 *
	 * @var string
	 */
	const CACHE_CLASS = 'class';

	/**
	 * Cache identifier for functions.
	 *
	 * @var string
	 */
	const CACHE_FUNCTION = 'function';

	/**
	 * Cache identifier for constants.
	 *
	 * @var string
	 */
	const CACHE_CONSTANT = 'constant';

	/**
	 * Cache identifier for namespaces.
	 *
	 * @var string
	 */
	const CACHE_NAMESPACE = 'namespace';

	/**
	 * Namespace/class backend.
	 *
	 * @var \TokenReflection\Broker\Backend
	 */
	private $backend;

	/**
	 * Tokenized reflection objects cache.
	 *
	 * @var array
	 */
	private $cache;

	/**
	 * Constructor.
	 *
	 * @param \TokenReflection\Broker\Backend $backend Broker backend instance
	 * @param boolean $storingTokenStream Determines if token streams should by stored in backend
	 */
	public function __construct(Broker\Backend $backend, $storingTokenStream = true)
	{
		$this->cache = array(
			self::CACHE_CLASS => array(),
			self::CACHE_CONSTANT => array(),
			self::CACHE_FUNCTION => array(),
			self::CACHE_NAMESPACE => array()
		);

		$this->backend = $backend
			->setBroker($this)
			->setStoringTokenStreams($storingTokenStream);
	}

	/**
	 * Parses a file a returns the appropriate reflection object.
	 *
	 * @param string $fileName Filename
	 * @return \TokenReflection\ReflectionFile
	 * @throws \TokenReflection\Exception\Parse If the requested file could not be processed
	 */
	public function processFile($fileName)
	{
		try {
			$realName = realpath($fileName);
			if (false === $realName) {
				throw new Exception\Parse('File does not exist.', Exception\Parse::FILE_DOES_NOT_EXIST);
			}

			if ($this->backend->isFileProcessed($realName)) {
				$tokens = $this->backend->getFileTokens($realName);
			} else {
				$contents = @file_get_contents($realName);
				if (false === $contents) {
					throw new Exception\Parse('File is not readable.', Exception\Parse::FILE_NOT_READABLE);
				}

				$tokens = new Stream(@token_get_all(str_replace(array("\r\n", "\r"), "\n", $contents)), $realName);
			}

			$reflectionFile = new ReflectionFile($tokens, $this);
			if (!$this->backend->isFileProcessed($realName)) {
				$this->backend->addFile($reflectionFile);

				// Clear the cache - leave only tokenized reflections
				foreach ($this->cache as $type => $cached) {
					if (!empty($cached)) {
						$this->cache[$type] = array_filter($cached, function(IReflection $reflection) {
							return $reflection->isTokenized();
						});
					}
				}
			}
			return $reflectionFile;
		} catch (Exception $e) {
			throw new Exception\Parse(sprintf('Could not process file %s.', $fileName), 0, $e);
		}
	}

	/**
	 * Processes recursively a directory and returns an array of file reflection objects.
	 *
	 * @param string $path Directora path
	 * @return array
	 * @throws \TokenReflection\Exception\Parse If the requested directory could not be processed
	 */
	public function processDirectory($path)
	{
		try {
			$realPath = realpath($path);
			if (false === $realPath) {
				throw new Exception\Parse('Directory does not exist.', Exception\Parse::FILE_DOES_NOT_EXIST);
			}

			$result = array();
			foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($realPath)) as $entry) {
				if ($entry->isFile()) {
					$result[$entry->getPathName()] = $this->processFile($entry->getPathName());
				}
			}

			return $result;
		} catch (Exception $e) {
			throw new Exception\Parse(sprintf('Could not process directory %s.', $path), 0, $e);
		}
	}

	/**
	 * Returns a reflection object of the given namespace.
	 *
	 * @param string $namespaceName Namespace name
	 * @return \TokenReflection\ReflectionNamespace|null
	 */
	public function getNamespace($namespaceName)
	{
		$namespaceName = ltrim($namespaceName, '\\');

		if (isset($this->cache[self::CACHE_NAMESPACE][$namespaceName])) {
			return $this->cache[self::CACHE_NAMESPACE][$namespaceName];
		}

		$namespace = $this->backend->getNamespace($namespaceName);
		if (null !== $namespace) {
			$this->cache[self::CACHE_NAMESPACE][$namespaceName] = $namespaceName;
		}

		return $namespace;
	}

	/**
	 * Returns a reflection object of the given class (FQN expected).
	 *
	 * @param string $className CLass bame
	 * @return \TokenReflection\ReflectionClass|null
	 *
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
	 * Returns a reflection object of a function (FQN expected).
	 *
	 * @param string $functionName Function name
	 * @return \TokenReflection\ReflectionFunction|null
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
	 * Returns a reflection object of a constant (FQN expected).
	 *
	 * @param string $constantName Constant name
	 * @return \TokenReflection\ReflectionConstant|null
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
	 * Returns an array of tokens from a processed file.
	 *
	 * @param string $fileName File name
	 * @return \TokenReflection\Stream|null
	 * @throws \TokenReflection\Exception\Runtime If there is no stored token stream for the provided filename
	 */
	public function getFileTokens($fileName)
	{
		try {
			return $this->backend->getFileTokens($fileName);
		} catch (Exception $e) {
			throw new Exception\Runtime(sprintf('Could not retrieve token stream for file %s.', $fileName), 0, $e);
		}
	}

	/**
	 * Returns all classes from all namespaces.
	 *
	 * @param integer $types Returned class types (multiple values may be OR-ed)
	 * @return array
	 */
	public function getClasses($types = Broker\Backend::TOKENIZED_CLASSES)
	{
		return $this->backend->getClasses($types);
	}

	/**
	 * Returns all functions from all namespaces.
	 *
	 * @return array
	 */
	public function getFunctions()
	{
		return $this->backend->getFunctions();
	}

	/**
	 * Returns all constants from all namespaces.
	 *
	 * @return array
	 */
	public function getConstants()
	{
		return $this->backend->getConstants();
	}
}
