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
			$reflectionFile = new ReflectionFile($tokens, $this->storage);
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

}
