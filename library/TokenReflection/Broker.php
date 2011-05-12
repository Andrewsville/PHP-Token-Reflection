<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0beta1
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
use TokenReflection;

use TokenReflection\Broker, ArrayIterator, RecursiveDirectoryIterator, RecursiveIteratorIterator;
use RuntimeException;

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
	public function __construct(Broker\Backend $backend, $storingTokenStream = true) {
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
	 * @param string $filename Filename
	 * @return \TokenReflection\ReflectionFile
	 * @throws \TokenReflection\Exception If the requested file does not exist
	 */
	public function processFile($filename)
	{
		$realName = realpath($filename);
		if (false === $realName) {
			throw new Exception(sprintf('File %s does not exist', $filename), Exception::FILE_DOES_NOT_EXIST);
		}

		if ($this->backend->isFileProcessed($realName)) {
			$tokens = $this->backend->getFileTokens($realName);
		} else {
			$contents = @file_get_contents($filename);
			if (false === $contents) {
				throw new Exception('File is not readable', Exception::FILE_NOT_READABLE);
			}

			$tokens = @token_get_all(str_replace(array("\r\n", "\r"), "\n", $contents));
		}

		$reflectionFile = new ReflectionFile($realName, $tokens, $this);
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
	}

	/**
	 * Processes recursively a directory and returns an array of file reflection objects.
	 *
	 * @param string $path Directora path
	 * @return array
	 * @throws \TokenReflection\Exception If the requested directory does not exist
	 */
	public function processDirectory($path)
	{
		$realPath = realpath($path);
		if (false === $realPath) {
			throw new Exception(sprintf('Directory %s does not exist', $path), Exception::FILE_DOES_NOT_EXIST);
		}

		$result = array();
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($realPath)) as $entry) {
			if ($entry->isFile()) {
				$result[$entry->getPathName()] = $this->processFile($entry->getPathName());
			}
		}

		return $result;
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
	 * @return array
	 */
	public function getFileTokens($fileName)
	{
		if (!$this->backend->getStoringTokenStreams) {
			throw new RuntimeException('Token streams storing is turned off.');
		}

		return $this->backend->getFileTokens($fileName);
	}

	/**
	 * Returns all classes from all namespaces.
	 *
	 * @param integer $type Returned class types (multiple values may be OR-ed)
	 * @return array
	 */
	public function getClasses($types = Broker\Backend::TOKENIZED_CLASSES)
	{
		return $this->backend->getClasses($types);
	}
}
