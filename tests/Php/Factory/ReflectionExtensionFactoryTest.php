<?php

namespace ApiGen\TokenReflection\Tests\Php\Factory;

use ApiGen;
use ApiGen\TokenReflection\Php\Factory\ReflectionExtensionFactory;
use ApiGen\TokenReflection\Tests\TestCase;


class ReflectionExtensionFactoryTest extends TestCase
{

	public function testCreate()
	{
		$this->assertInstanceOf(
			'ApiGen\TokenReflection\Php\ReflectionExtension',
			ReflectionExtensionFactory::create(new \ReflectionExtension('phar'), $this->parser->getStorage())
		);
	}


	/**
	 * Tests an exception thrown when trying to create the reflection from a PHP internal reflection.
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\RuntimeException
	 */
	public function testInternalExtensionReflectionCreate()
	{
		ReflectionExtensionFactory::create(new \ReflectionFunction('create_function'), $this->parser->getStorage());
	}


	/**
	 * Tests an exception thrown when trying to create the reflection from a PHP internal reflection.
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\RuntimeException
	 */
	public function testInternalFunctionReflectionCreate()
	{
		ReflectionExtensionFactory::create(new \ReflectionClass('Exception'), $this->parser->getStorage());
	}

}
