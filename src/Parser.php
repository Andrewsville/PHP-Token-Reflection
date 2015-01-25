<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

use ApiGen\TokenReflection\Exception\ParserException;
use ApiGen\TokenReflection\Reflection\Factory\ReflectionFileFactory;
use ApiGen\TokenReflection\Reflection\Factory\ReflectionNamespaceFactory;
use ApiGen\TokenReflection\Reflection\ReflectionFile;
use ApiGen\TokenReflection\Reflection\ReflectionNamespace;
use ApiGen\TokenReflection\Storage\StorageInterface;
use Nette\Utils\Finder;
use SplFileInfo;


class Parser implements ParserInterface
{

	/**
	 * @var StorageInterface
	 */
	private $storage;

	/**
	 * @var ReflectionNamespaceFactory
	 */
	private $reflectionNamespaceFactory;

	/**
	 * @var ReflectionFileFactory
	 */
	private $reflectionFileFactory;


	public function __construct(
		StorageInterface $storage,
		ReflectionNamespaceFactory $reflectionNamespaceFactory,
		ReflectionFileFactory $reflectionFileFactory
	) {
		$this->storage = $storage;
		$this->storage->addNamespace(
			ReflectionNamespace::NO_NAMESPACE_NAME,
			$reflectionNamespaceFactory->create(ReflectionNamespace::NO_NAMESPACE_NAME)
		);
		$this->reflectionNamespaceFactory = $reflectionNamespaceFactory;
		$this->reflectionFileFactory = $reflectionFileFactory;
	}


	/**
	 * @param string $path
	 * @return ReflectionFile[]
	 */
	public function parseDirectory($path)
	{
		$realPath = realpath($path);
		if ( ! is_dir($realPath)) {
			throw new ParserException(sprintf('Directory %s does not exist.', $path));
		}

		$result = [];
		foreach (Finder::findFiles('*')->in($realPath) as $entry) {
			/** @var SplFileInfo $entry */
			$result[$entry->getPathName()] = $this->parseFile($entry->getPathName());
		}

		return $result;
	}


	/**
	 * @param string $name
	 * @return ReflectionFile
	 */
	public function parseFile($name)
	{
		$reflectionFile = $this->reflectionFileFactory->create($name);
		$this->storage->addFile($reflectionFile);
		$this->loadNamespacesFromFile($reflectionFile);
		return $reflectionFile;
	}


	/**
	 * @return StorageInterface
	 */
	public function getStorage()
	{
		return $this->storage;
	}


	private function loadNamespacesFromFile(ReflectionFile $reflectionFile)
	{
		foreach ($reflectionFile->getNamespaces() as $fileNamespace) {
			$namespaceName = $fileNamespace->getName();
			if ( ! $this->storage->hasNamespace($namespaceName)) {
				$this->storage->addNamespace($namespaceName, $this->reflectionNamespaceFactory->create($namespaceName));
			}
			$this->storage->getNamespace($namespaceName)->addFileNamespace($fileNamespace);
		}
	}

}
