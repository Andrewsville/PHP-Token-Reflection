<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection\Factory;

use ApiGen\TokenReflection\Storage\StorageInterface;
use ApiGen\TokenReflection\Reflection\ReflectionNamespace;


class ReflectionNamespaceFactory
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
	 * @return ReflectionNamespace
	 */
	public function create($name)
	{
		return new ReflectionNamespace($name, $this->storage);
	}

}
