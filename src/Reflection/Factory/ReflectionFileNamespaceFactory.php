<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection\Factory;

use ApiGen\TokenReflection\Reflection\ReflectionFile;
use ApiGen\TokenReflection\Reflection\ReflectionFileNamespace;
use ApiGen\TokenReflection\Storage\StorageInterface;
use ApiGen\TokenReflection\Stream\StreamBase;


class ReflectionFileNamespaceFactory
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
	 * @return ReflectionFileNamespace
	 */
	public function create(StreamBase $streamBase, ReflectionFile $reflectionFile)
	{
		return new ReflectionFileNamespace($streamBase, $this->storage, $reflectionFile);
	}

}
