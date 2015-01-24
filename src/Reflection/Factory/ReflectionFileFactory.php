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


	public function __construct(StorageInterface $storage)
	{
		$this->storage = $storage;
	}


	/**
	 * @param string $name
	 * @return ReflectionFile
	 */
	public function create($name)
	{
		$tokenStream = new FileStream($name);
		return new ReflectionFile($tokenStream, $this->storage);
	}

}
