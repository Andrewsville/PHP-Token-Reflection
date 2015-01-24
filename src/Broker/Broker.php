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
use ApiGen\TokenReflection\Reflection\ReflectionFile;
use ApiGen\TokenReflection\Stream\FileStream;
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
		$this->storage = $storage;
	}


	/**
	 * @return StorageInterface
	 */
	public function getStorage()
	{
		return $this->storage;
	}


	/**
	 * @param string $fileName
	 * @return ReflectionFile
	 */
	public function processFile($fileName)
	{
		if ($this->storage->isFileProcessed($fileName)) {
			$tokens = $this->storage->getFileTokens($fileName);
			$reflectionFile = new ReflectionFile($tokens, $this->storage);

		} else {
			$tokens = new FileStream($fileName);
			$reflectionFile = new ReflectionFile($tokens, $this->storage);
			$this->storage->addFile($tokens, $reflectionFile);
		}

		return $reflectionFile;
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

		$result = [];
		foreach (Finder::findFiles('*')->in($realPath) as $entry) {
			/** @var SplFileInfo $entry */
			$result[$entry->getPathName()] = $this->processFile($entry->getPathName());
		}
		return $result;
	}

}
