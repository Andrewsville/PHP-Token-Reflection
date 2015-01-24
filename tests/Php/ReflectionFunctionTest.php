<?php

namespace ApiGen\TokenReflection\Tests\Php;

use ApiGen;
use ApiGen\TokenReflection\Php\ReflectionFunction;
use ApiGen\TokenReflection\Tests\TestCase;


class ReflectionFunctionTest extends TestCase
{

	/**
	 * @var string
	 */
	protected $type = 'extension';

	/**
	 * @var ReflectionFunction
	 */
	private $internalReflectionFunction;


	protected function setUp()
	{
		$this->internalReflectionFunction = new ReflectionFunction('count', $this->getStorage());
	}


	public function testName()
	{
		$this->assertSame('count', $this->internalReflectionFunction->getName());
		$this->assertSame('count()', $this->internalReflectionFunction->getPrettyName());
		$this->assertSame([], $this->internalReflectionFunction->getNamespaceAliases());
	}


	public function testBasicMethods()
	{
		$this->assertFalse($this->internalReflectionFunction->isTokenized());
		$this->assertFalse($this->internalReflectionFunction->isDeprecated());
		$this->assertFalse($this->internalReflectionFunction->isUserDefined());
		$this->assertTrue($this->internalReflectionFunction->isInternal());
	}


	public function testAnnotations()
	{
		$this->assertFalse($this->internalReflectionFunction->hasAnnotation('...'));
		$this->assertNull($this->internalReflectionFunction->getAnnotation('...'));
		$this->assertSame([], $this->internalReflectionFunction->getAnnotations());
	}


	public function testParameters()
	{
		$this->assertCount(2, $this->internalReflectionFunction->getParameters());
		$this->assertInstanceOf(
			'ApiGen\TokenReflection\ReflectionParameterInterface',
			$this->internalReflectionFunction->getParameter('var')
		);
	}


	public function testIsVariadic()
	{
		$this->assertFalse($this->internalReflectionFunction->isVariadic());
	}


	public function testGetExtension()
	{
		$this->assertInstanceOf('ApiGen\TokenReflection\Php\ReflectionExtension', $this->internalReflectionFunction->getExtension());
	}


	public function testGetStorage()
	{
		$this->assertInstanceOf('ApiGen\TokenReflection\Broker\StorageInterface', $this->internalReflectionFunction->getStorage());
	}

}
