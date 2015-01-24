<?php

namespace ApiGen\TokenReflection\Tests\Php\Factory;

use ApiGen\TokenReflection\Php\Factory\ReflectionClassFactory;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Tests\TestCase;


class ReflectionClassFactoryTest extends TestCase
{

	/**
	 * @expectedException RuntimeException
	 */
	public function testInternalClassReflectionCreate()
	{
		ReflectionClassFactory::create(new \ReflectionFunction('create_function'), $this->broker->getStorage());
	}

}
