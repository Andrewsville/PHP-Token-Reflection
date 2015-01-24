<?php

namespace ApiGen\TokenReflection\Tests\Php\Factory;

use ApiGen;
use ApiGen\TokenReflection\Php\Factory\ReflectionPropertyFactory;
use ApiGen\TokenReflection\Tests\TestCase;


class ReflectionPropertyFactoryTest extends TestCase
{

	/**
	 * Tests an exception thrown when trying to create the reflection from a PHP internal reflection.
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\RuntimeException
	 */
	public function testInternalPropertyReflectionCreate()
	{
		ReflectionPropertyFactory::create(new \ReflectionClass('Exception'), $this->getStorage());
	}

}
