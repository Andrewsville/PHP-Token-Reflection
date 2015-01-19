<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Broker;

use ApiGen;
use ApiGen\TokenReflection\Broker\BackendInterface;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\BrokerException;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\Reflection\ReflectionFile;
use ApiGen\TokenReflection\Stream\FileStream;
use ApiGen\TokenReflection\Stream\StringStream;
use Nette\Utils\Finder;
use SplFileInfo;


/**
 * Parses files and directories and stores their structure.
 */
class Broker
{

	/**
	 * Turns on saving of parsed token streams.
	 *
	 * @var int
	 */
	const OPTION_SAVE_TOKEN_STREAM = 0x0001;

	/**
	 * Turns on parsing function/method body.
	 *
	 * This effectively turns on parsing of static variables in functions/methods.
	 *
	 * @var int
	 */
	const OPTION_PARSE_FUNCTION_BODY = 0x0002;

	/**
	 * Default options.
	 *
	 * @var int
	 */
	const OPTION_DEFAULT = 0x0003;

	/**
	 * Cache identifier for namespaces.
	 *
	 * @var string
	 */
	const CACHE_NAMESPACE = 'namespace';

	/**
	 * Cache identifier for classes.
	 *
	 * @var string
	 */
	const CACHE_CLASS = 'class';

	/**
	 * Cache identifier for constants.
	 *
	 * @var string
	 */
	const CACHE_CONSTANT = 'constant';

	/**
	 * Cache identifier for functions.
	 *
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

	/**
	 * Broker/parser options.
	 *
	 * @var int
	 */
	private $options;


	/**
	 * @param BackendInterface $backend Broker backend instance
	 * @param int $options Broker/parsing options
	 */
	public function __construct(BackendInterface $backend, $options = self::OPTION_DEFAULT)
	{
		$this->cache = [
			self::CACHE_NAMESPACE => [],
			self::CACHE_CLASS => [],
			self::CACHE_CONSTANT => [],
			self::CACHE_FUNCTION => []
		];
		$this->options = $options;
		$this->backend = $backend
			->setBroker($this)
			->setStoringTokenStreams((bool) ($options & self::OPTION_SAVE_TOKEN_STREAM));
	}


	/**
	 * Returns broker/parser options.
	 *
	 * @return int
	 */
	public function getOptions()
	{
		return $this->options;
	}


	/**
	 * Returns if a particular option setting is set.
	 *
	 * @param int $option Option setting
	 * @return bool
	 */
	public function isOptionSet($option)
	{
		return (bool) ($this->options & $option);
	}


	/**
	 * Parses a string with the PHP source code using the given file name and returns the appropriate reflection object.
	 *
	 * @param string $source PHP source code
	 * @param string $fileName Used file name
	 * @param bool $returnReflectionFile Returns the appropriate ApiGen\TokenReflection\Reflection\ReflectionFile instance(s)
	 * @return bool|ReflectionFile
	 */
	public function processString($source, $fileName, $returnReflectionFile = FALSE)
	{
		if ($this->backend->isFileProcessed($fileName)) {
			$tokens = $this->backend->getFileTokens($fileName);
		} else {
			$tokens = new StringStream($source, $fileName);
		}
		$reflectionFile = new ReflectionFile($tokens, $this);
		if ( ! $this->backend->isFileProcessed($fileName)) {
			$this->backend->addFile($tokens, $reflectionFile);
			// Clear the cache - leave only tokenized reflections
			foreach ($this->cache as $type => $cached) {
				if ( ! empty($cached)) {
					$this->cache[$type] = array_filter($cached, function (ReflectionInterface $reflection) {
						return $reflection->isTokenized();
					});
				}
			}
		}
		return $returnReflectionFile ? $reflectionFile : TRUE;
	}


	/**
	 * Parses a file and returns the appropriate reflection object.
	 *
	 * @param string $fileName Filename
	 * @param bool $returnReflectionFile Returns the appropriate ApiGen\TokenReflection\Reflection\ReflectionFile instance(s)
	 * @return bool|ReflectionFile
	 * @throws BrokerException If the file could not be processed.
	 */
	public function processFile($fileName, $returnReflectionFile = FALSE)
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
						$this->cache[$type] = array_filter($cached, function (ReflectionInterface $reflection) {
							return $reflection->isTokenized();
						});
					}
				}
			}
			return $returnReflectionFile ? $reflectionFile : TRUE;
		} catch (Exception\ParseException $e) {
			throw $e;
		} catch (Exception\StreamException $e) {
			throw new BrokerException('Could not process the file.', 0, $e);
		}
	}


	/**
	 * Processes recursively a directory and returns an array of file reflection objects.
	 *
	 * @param string $path
	 * @param bool $returnReflectionFile Returns the appropriate ApiGen\TokenReflection\Reflection\ReflectionFile instance(s)
	 * @return bool|ReflectionFile[]|SplFileInfo[]
	 * @throws BrokerException If the given directory does not exist.
	 * @throws BrokerException If the given directory could not be processed.
	 */
	public function processDirectory($path, $returnReflectionFile = FALSE)
	{
		$realPath = realpath($path);
		if ( ! is_dir($realPath)) {
			throw new BrokerException('File does not exist.', BrokerException::DOES_NOT_EXIST);
		}
		try {
			$result = [];
			foreach (Finder::findFiles('*')->in($realPath) as $entry) {
				/** @var SplFileInfo $entry */
				$result[$entry->getPathName()] = $this->processFile($entry->getPathName(), $returnReflectionFile);
			}
			return $returnReflectionFile ? $result : TRUE;
		} catch (Exception\ParseException $e) {
			throw $e;
		} catch (Exception\StreamException $e) {
			throw new BrokerException('Could not process the directory.', 0, $e);
		}
	}


	/**
	 * Process a file or directory.
	 *
	 * @param string $path Path
	 * @param bool $returnReflectionFile Returns the appropriate ApiGen\TokenReflection\Reflection\ReflectionFile instance(s)
	 * @return bool|array|ReflectionFile
	 * @throws BrokerException If the target does not exist.
	 */
	public function process($path, $returnReflectionFile = FALSE)
	{
		if (is_dir($path)) {
			return $this->processDirectory($path, [], $returnReflectionFile);
		} elseif (is_file($path)) {
			return $this->processFile($path, $returnReflectionFile);
		} else {
			throw new BrokerException('The given directory/file does not exist.', BrokerException::DOES_NOT_EXIST);
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
	 * Returns a reflection object of the given namespace.
	 *
	 * @param string $namespaceName Namespace name
	 * @return \ApiGen\TokenReflection\Reflection\ReflectionNamespace|null
	 */
	public function getNamespace($namespaceName)
	{
		$namespaceName = ltrim($namespaceName, '\\');
		if (isset($this->cache[self::CACHE_NAMESPACE][$namespaceName])) {
			return $this->cache[self::CACHE_NAMESPACE][$namespaceName];
		}
		$namespace = $this->backend->getNamespace($namespaceName);
		if (NULL !== $namespace) {
			$this->cache[self::CACHE_NAMESPACE][$namespaceName] = $namespace;
		}
		return $namespace;
	}


	/**
	 * Returns a list of reflection objects for all namespaces.
	 *
	 * @return array
	 */
	public function getNamespaces()
	{
		$namespaces = [];
		foreach(array_keys($this->backend->getNamespaces()) as $name) {
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
	 * Returns a reflection object of the given class (FQN expected).
	 *
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
	 * Returns all classes from all namespaces.
	 *
	 * @param int $types Returned class types (multiple values may be OR-ed)
	 * @return array|ReflectionClassInterface[]
	 */
	public function getClasses($types = BackendInterface::TOKENIZED_CLASSES)
	{
		return $this->backend->getClasses($types);
	}


	/**
	 * Returns if the broker contains a constant of the given name.
	 *
	 * @param string $constantName Constant name
	 * @return bool
	 */
	public function hasConstant($constantName)
	{
		return isset($this->cache[self::CACHE_CONSTANT][$constantName]) || $this->backend->hasConstant($constantName);
	}


	/**
	 * Returns a reflection object of a constant (FQN expected).
	 *
	 * @param string $constantName Constant name
	 * @return \ApiGen\TokenReflection\Reflection\ReflectionConstant|null
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
	 * Returns all constants from all namespaces.
	 *
	 * @return array
	 */
	public function getConstants()
	{
		return $this->backend->getConstants();
	}


	/**
	 * Returns if the broker contains a function of the given name.
	 *
	 * @param string $functionName Function name
	 * @return bool
	 */
	public function hasFunction($functionName)
	{
		return isset($this->cache[self::CACHE_FUNCTION][$functionName]) || $this->backend->hasFunction($functionName);
	}


	/**
	 * Returns a reflection object of a function (FQN expected).
	 *
	 * @param string $functionName Function name
	 * @return \ApiGen\TokenReflection\Reflection\ReflectionFunction|null
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
	 * Returns all functions from all namespaces.
	 *
	 * @return array
	 */
	public function getFunctions()
	{
		return $this->backend->getFunctions();
	}


	/**
	 * Returns if the broker contains a file reflection of the given name.
	 *
	 * @param string $fileName File name
	 * @return bool
	 */
	public function hasFile($fileName)
	{
		return $this->backend->hasFile($fileName);
	}


	/**
	 * Returns a reflection object of a file.
	 *
	 * @param string $fileName File name
	 * @return ReflectionFile|null
	 */
	public function getFile($fileName)
	{
		return $this->backend->getFile($fileName);
	}


	/**
	 * Returns all processed files reflections.
	 *
	 * @return array
	 */
	public function getFiles()
	{
		return $this->backend->getFiles();
	}


	/**
	 * Returns an array of tokens from a processed file.
	 *
	 * @param string $fileName File name
	 * @return ApiGen\TokenReflection\Stream\StreamBase|null
	 */
	public function getFileTokens($fileName)
	{
		return $this->backend->getFileTokens($fileName);
	}


	/**
	 * Returns a real system path.
	 *
	 * @param string $path Source path
	 * @return string|bool
	 */
	public static function getRealPath($path)
	{
		return realpath($path);
	}

}
