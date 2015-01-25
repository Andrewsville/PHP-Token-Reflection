<?php

namespace ApiGen\TokenReflection\Tests\Php\Factory;

use ApiGen\TokenReflection\Php\Factory\ReflectionFunctionFactory;
use ApiGen\TokenReflection\Tests\TestCase;


class ReflectionFunctionFactoryTest extends TestCase
{

	public function testCreate()
	{
		$this->assertInstanceOf(
			'ApiGen\TokenReflection\Php\ReflectionFunction',
			ReflectionFunctionFactory::create(new \ReflectionFunction('count'), $this->parser->getStorage())
		);
	}

}
