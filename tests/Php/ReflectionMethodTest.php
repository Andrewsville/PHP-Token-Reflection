<?php

namespace ApiGen\TokenReflection\Tests\Php;

use ApiGen;
use ApiGen\TokenReflection\Php\ReflectionMethod;
use ApiGen\TokenReflection\Tests\TestCase;


class ReflectionMethodTest extends TestCase
{

	/**
	 * @var string
	 */
	protected $type = 'extension';

	/**
	 * @var ReflectionMethod
	 */
	private $internalReflectionMethod;


	protected function setUp()
	{
		$this->internalReflectionMethod = $this->getStorage()->getClass('Exception')->getConstructor();
	}


	public function testName()
	{
		$this->assertSame('__construct', $this->internalReflectionMethod->getName());
		$this->assertNull($this->internalReflectionMethod->getOriginalName());
		$this->assertSame('Exception::__construct()', $this->internalReflectionMethod->getPrettyName());
		$this->assertSame([], $this->internalReflectionMethod->getNamespaceAliases());
	}


	public function testBasicMethods()
	{
		$this->assertFalse($this->internalReflectionMethod->isTokenized());
		$this->assertFalse($this->internalReflectionMethod->isDeprecated());
		$this->assertFalse($this->internalReflectionMethod->isUserDefined());
		$this->assertTrue($this->internalReflectionMethod->isInternal());

		$this->assertNull($this->internalReflectionMethod->getOriginal());
		$this->assertNull($this->internalReflectionMethod->getOriginalModifiers());
		$this->assertNull($this->internalReflectionMethod->getDeclaringTrait());
		$this->assertNull($this->internalReflectionMethod->getDeclaringTraitName());

	}


	public function testAccessible()
	{
		$this->assertFalse($this->internalReflectionMethod->isAccessible());
		$this->internalReflectionMethod->setAccessible(TRUE);
		$this->assertTrue($this->internalReflectionMethod->isAccessible());
	}


	public function testAnnotations()
	{
		$this->assertFalse($this->internalReflectionMethod->hasAnnotation('...'));
		$this->assertNull($this->internalReflectionMethod->getAnnotation('...'));
		$this->assertSame([], $this->internalReflectionMethod->getAnnotations());
	}


	public function testParameters()
	{
		$this->assertCount(3, $this->internalReflectionMethod->getParameters());
		$this->assertInstanceOf(
			'ApiGen\TokenReflection\ReflectionParameterInterface',
			$this->internalReflectionMethod->getParameter('message')
		);
	}


	public function testIsVariadic()
	{
		$this->assertFalse($this->internalReflectionMethod->isVariadic());
	}


	public function testGetExtension()
	{
		$this->assertInstanceOf('ReflectionExtension', $this->internalReflectionMethod->getExtension());
	}


	public function testGetStorage()
	{
		$this->assertInstanceOf('ApiGen\TokenReflection\Broker\StorageInterface', $this->internalReflectionMethod->getStorage());
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\RuntimeException
	 */
	public function testInternalMethodGetParameter1()
	{
		$this->internalReflectionMethod->getParameter('~non-existent~');
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\RuntimeException
	 */
	public function testInternalMethodGetParameter2()
	{
		$this->internalReflectionMethod->getParameter(999);
	}

}
