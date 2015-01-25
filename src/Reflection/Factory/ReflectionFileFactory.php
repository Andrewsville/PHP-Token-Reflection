<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection\Factory;

use ApiGen\TokenReflection\Reflection\ReflectionFile;
use ApiGen\TokenReflection\Storage\StorageInterface;
use ApiGen\TokenReflection\Stream\FileStream;
use ApiGen\TokenReflection\Stream\StreamBase;


class ReflectionFileFactory
{

	/**
	 * @var StorageInterface
	 */
	private $storage;

	/**
	 * @var ReflectionAnnotationFactory
	 */
	private $reflectionAnnotationFactory;

	/**
	 * @var ReflectionFileNamespaceFactory
	 */
	private $reflectionFileNamespaceFactory;


	public function __construct(
		StorageInterface $storage,
		ReflectionAnnotationFactory $reflectionAnnotationFactory,
		ReflectionFileNamespaceFactory $reflectionFileNamespaceFactory
	) {
		$this->storage = $storage;
		$this->reflectionAnnotationFactory = $reflectionAnnotationFactory;
		$this->reflectionFileNamespaceFactory = $reflectionFileNamespaceFactory;
	}


	/**
	 * @param string $name
	 * @return ReflectionFile
	 */
	public function create($name)
	{
		$tokenStream = new FileStream($name);
		$reflectionFile = new ReflectionFile($tokenStream, $this->storage);
		$reflectionFile->setReflectionAnnotationFactory($this->reflectionAnnotationFactory);
		$reflectionFile->setReflectionFileNamespaceFactory($this->reflectionFileNamespaceFactory);
		return $reflectionFile;
	}

}
