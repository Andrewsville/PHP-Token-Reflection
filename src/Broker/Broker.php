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
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\Reflection\ReflectionFile;
use ApiGen\TokenReflection\ReflectionNamespaceInterface;
use ApiGen\TokenReflection\Stream\FileStream;
use ApiGen\TokenReflection\Stream\StreamBase;
use Nette\Utils\Finder;
use SplFileInfo;


class Broker implements BrokerInterface
{

	/**
	 * @var StorageInterface
	 */
	private $storage;


	public function __construct(StorageInterface $storage)
	{
		$this->storage = $storage->setBroker($this); // todo: remove circular dependency
	}


	/**
	 * Parses a file and returns the appropriate reflection object.
	 *
	 * @param string $fileName
	 * @return ReflectionFile
	 * @throws BrokerException If the file could not be processed.
	 */
	public function processFile($fileName)
	{
		try {
			if ($this->storage->isFileProcessed($fileName)) {
				$tokens = $this->storage->getFileTokens($fileName);

			} else {
				$tokens = new FileStream($fileName);
			}
			$reflectionFile = new ReflectionFile($tokens, $this);
			if ( ! $this->storage->isFileProcessed($fileName)) {
				$this->storage->addFile($tokens, $reflectionFile);
			}
			return $reflectionFile;

		} catch (ParseException $e) {
			throw $e;
		}
	}


	/**
	 * @param string $path
	 * @return ReflectionFile[]
	 */
	public function processDirectory($path)
	{
		$realPath = realpath($path);
		if ( ! is_dir($realPath)) {
			throw new BrokerException(sprintf('Directory %s does not exist.', $path));
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
		}
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasNamespace($name)
	{
		return $this->storage->hasNamespace($name);
	}


	/**
	 * @param string $name
	 * @return ReflectionNamespaceInterface|NULL
	 */
	public function getNamespace($name)
	{
		return $this->storage->getNamespace($name);
	}


	/**
	 * @return ReflectionNamespaceInterface[]
	 */
	public function getNamespaces()
	{
		return $this->storage->getNamespaces();
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasClass($name)
	{
		return $this->storage->hasClass($name);
	}


	/**
	 * @param string $name
	 * @return ReflectionClassInterface
	 */
	public function getClass($name)
	{
		return $this->storage->getClass($name);
	}


	/**
	 * @param int $types
	 * @return ReflectionClassInterface[]
	 */
	public function getClasses($types = StorageInterface::TOKENIZED_CLASSES)
	{
		return $this->storage->getClasses($types);
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasConstant($name)
	{
		return $this->storage->hasConstant($name);
	}


	/**
	 * Returns a reflection object of a constant (FQN expected).
	 *
	 * @param string $name
	 * @return ReflectionConstantInterface|NULL
	 */
	public function getConstant($name)
	{
		return $this->storage->getConstant($name);
	}


	/**
	 * @return ReflectionConstantInterface[]
	 */
	public function getConstants()
	{
		return $this->storage->getConstants();
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasFunction($name)
	{
		return $this->storage->hasFunction($name);
	}


	/**
	 * Returns a reflection object of a function (FQN expected).
	 *
	 * @param string $name
	 * @return ReflectionFunctionInterface|NULL
	 */
	public function getFunction($name)
	{
		return $this->storage->getFunction($name);
	}


	/**
	 * @return array|ReflectionFunctionInterface[]
	 */
	public function getFunctions()
	{
		return $this->storage->getFunctions();
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasFile($name)
	{
		return $this->storage->hasFile($name);
	}


	/**
	 * @param string $name
	 * @return ReflectionFile|NULL
	 */
	public function getFile($name)
	{
		return $this->storage->getFile($name);
	}


	/**
	 * @return ReflectionFile[]
	 */
	public function getFiles()
	{
		return $this->storage->getFiles();
	}


	/**
	 * @param string $fileName
	 * @return StreamBase|NULL
	 */
	public function getFileTokens($fileName)
	{
		return $this->storage->getFileTokens($fileName);
	}

}
