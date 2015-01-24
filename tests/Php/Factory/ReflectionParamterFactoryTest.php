<?php

namespace ApiGen\TokenReflection\Tests\Php\Factory;

use ApiGen;
use ApiGen\TokenReflection\Php\Factory\ReflectionParameterFactory;
use ApiGen\TokenReflection\Tests\TestCase;


class ReflectionParameterFactoryTest extends TestCase
{

	/**
	 * @expectedException ApiGen\TokenReflection\Exception\RuntimeException
	 */
	public function testInternalParameterReflectionCreate()
	{
		ReflectionParameterFactory::create(new \ReflectionClass('Exception'), $this->getStorage());
	}

}
