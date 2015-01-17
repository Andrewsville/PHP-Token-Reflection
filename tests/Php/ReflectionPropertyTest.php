<?php

namespace ApiGen\TokenReflection\Tests\Php;

use ApiGen;
use ApiGen\TokenReflection\Php\ReflectionProperty;
use ApiGen\TokenReflection\Tests\TestCase;


class ReflectionPropertyTest extends TestCase
{

	/**
	 * @var string
	 */
	protected $type = 'extension';

	/**
	 * @var ReflectionProperty
	 */
	private $internalReflectionProperty;


	protected function setUp()
	{
		$this->internalReflectionProperty = $this->getBroker()->getClass('Exception')->getProperty('message');
	}


	public function testName()
	{
		$this->assertSame('message', $this->internalReflectionProperty->getName());
		$this->assertSame('Exception::$message', $this->internalReflectionProperty->getPrettyName());
		$this->assertSame([], $this->internalReflectionProperty->getNamespaceAliases());
	}


	public function testBasicMethods()
	{
		$this->assertFalse($this->internalReflectionProperty->isTokenized());
		$this->assertFalse($this->internalReflectionProperty->isDeprecated());
		$this->assertFalse($this->internalReflectionProperty->isUserDefined());
		$this->assertTrue($this->internalReflectionProperty->isInternal());

		$this->assertNull($this->internalReflectionProperty->getDeclaringTrait());
		$this->assertNull($this->internalReflectionProperty->getDeclaringTraitName());

		$this->assertNull($this->internalReflectionProperty->getStartLine());
		$this->assertNull($this->internalReflectionProperty->getEndLine());
		$this->assertFalse($this->internalReflectionProperty->getDocComment());
	}


	public function testAccessible()
	{
		$this->assertFalse($this->internalReflectionProperty->isAccessible());
		$this->internalReflectionProperty->setAccessible(TRUE);
		$this->assertTrue($this->internalReflectionProperty->isAccessible());
	}


	public function testGetFileName()
	{
		$this->assertFalse($this->internalReflectionProperty->getFileName());
	}


	public function testAnnotations()
	{
		$this->assertFalse($this->internalReflectionProperty->hasAnnotation('...'));
		$this->assertNull($this->internalReflectionProperty->getAnnotation('...'));
		$this->assertSame([], $this->internalReflectionProperty->getAnnotations());
	}


	public function testValue()
	{
		$this->assertSame('', $this->internalReflectionProperty->getDefaultValue());
		$this->assertSame("''", $this->internalReflectionProperty->getDefaultValueDefinition());
	}


	public function testGetExtension()
	{
		$this->assertInstanceOf('ReflectionExtension', $this->internalReflectionProperty->getExtension());
		$this->assertSame('Core', $this->internalReflectionProperty->getExtensionName());
	}


	public function testGetBroker()
	{
		$this->assertInstanceOf('ApiGen\TokenReflection\Broker\Broker', $this->internalReflectionProperty->getBroker());
	}

}
