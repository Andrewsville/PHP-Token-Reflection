<?php

namespace ApiGen\TokenReflection\Tests\Php;

use ApiGen;
use ApiGen\TokenReflection\Php\ReflectionExtension;
use ApiGen\TokenReflection\Tests\TestCase;


class ReflectionExtensionTest extends TestCase
{

	/**
	 * @var string
	 */
	protected $type = 'extension';

	/**
	 * @var ReflectionExtension
	 */
	private $internalReflectionExtension;


	protected function setUp()
	{
		parent::setUp();
		$this->internalReflectionExtension = new ReflectionExtension('phar', $this->parser->getStorage());
	}


	public function testName()
	{
		$this->assertSame('Phar', $this->internalReflectionExtension->getName());
	}

	public function testBasicMethods()
	{
		$this->assertFalse($this->internalReflectionExtension->isTokenized());
		$this->assertFalse($this->internalReflectionExtension->isDeprecated());
		$this->assertFalse($this->internalReflectionExtension->isUserDefined());
		$this->assertTrue($this->internalReflectionExtension->isInternal());
	}


	public function testClasses()
	{
		$this->assertNull($this->internalReflectionExtension->getClass('...'));
		$this->assertCount(4, $this->internalReflectionExtension->getClasses());
	}


	public function testConstants()
	{
		$this->assertNull($this->internalReflectionExtension->getConstant('...'));
		$this->assertSame([], $this->internalReflectionExtension->getConstants());
		$this->assertNull($this->internalReflectionExtension->getConstantReflection('...'));
		$this->assertSame([], $this->internalReflectionExtension->getConstantReflections());
	}


	public function testFunctions()
	{
		$this->assertNull($this->internalReflectionExtension->getFunction('...'));
		$this->assertSame([], $this->internalReflectionExtension->getFunctions());
	}


	public function testGetStorage()
	{
		$this->assertInstanceOf(
			'ApiGen\TokenReflection\Storage\StorageInterface',
			$this->internalReflectionExtension->getStorage()
		);
	}

}
