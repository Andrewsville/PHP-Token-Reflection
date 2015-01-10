<?php

namespace ApiGen\TokenReflection\Tests;

use ApiGen;
use ApiGen\TokenReflection\Php\ReflectionExtension;


class ReflectionExtensionTest extends TestCase
{
	/**
	 * @var string
	 */
	protected $type = 'extension';


	/**
	 * Tests an exception thrown when trying to create the reflection from a PHP internal reflection.
	 * @expectedException ApiGen\TokenReflection\Exception\RuntimeException
	 */
	public function testInternalExtensionReflectionCreate()
	{
		ReflectionExtension::create(new \ReflectionFunction('create_function'), $this->getBroker());
	}

}
