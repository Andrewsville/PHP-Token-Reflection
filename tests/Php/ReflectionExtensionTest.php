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
		$this->internalReflectionExtension = new ReflectionExtension('phar', $this->getBroker());
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

		$this->assertSame('Phar', $this->internalReflectionExtension->getPrettyName());
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
		$this->assertSame([], $this->internalReflectionExtension->getFunctionNames());
	}


	public function testGetBroker()
	{
		$this->assertInstanceOf('ApiGen\TokenReflection\Broker\Broker', $this->internalReflectionExtension->getBroker());
	}


	public function testCreate()
	{
		$this->assertInstanceOf(
			'ApiGen\TokenReflection\Php\ReflectionExtension',
			ReflectionExtension::create(new \ReflectionExtension('phar'), $this->getBroker())
		);
	}

}