<?php

namespace ApiGen\TokenReflection\Tests\Php;

use ApiGen;
use ApiGen\TokenReflection\Php\ReflectionParameter;
use ApiGen\TokenReflection\Tests\TestCase;


class ReflectionParameterTest extends TestCase
{

	/**
	 * @var string
	 */
	protected $type = 'extension';

	/**
	 * @var ReflectionParameter
	 */
	private $internalReflectionParameter;


	protected function setUp()
	{
		$this->internalReflectionParameter = $this->getBroker()
			->getClass('Exception')->getConstructor()->getParameter('message');
	}


	public function testName()
	{
		$this->assertSame('message', $this->internalReflectionParameter->getName());
		$this->assertSame('Exception::__construct($message)', $this->internalReflectionParameter->getPrettyName());
		$this->assertSame([], $this->internalReflectionParameter->getNamespaceAliases());
	}


	public function testBasicMethods()
	{
		$this->assertFalse($this->internalReflectionParameter->isTokenized());
		$this->assertFalse($this->internalReflectionParameter->isDeprecated());
		$this->assertFalse($this->internalReflectionParameter->isUserDefined());
		$this->assertTrue($this->internalReflectionParameter->isInternal());

		$this->assertNull($this->internalReflectionParameter->getStartLine());
		$this->assertNull($this->internalReflectionParameter->getEndLine());
		$this->assertFalse($this->internalReflectionParameter->getDocComment());
	}


	public function testClasses()
	{
		$this->assertNull($this->internalReflectionParameter->getClassName());
		$this->assertSame('Exception', $this->internalReflectionParameter->getDeclaringClassName());
		$this->assertSame('__construct', $this->internalReflectionParameter->getDeclaringFunctionName());
	}


	public function testGetFileName()
	{
		$this->assertFalse($this->internalReflectionParameter->getFileName());
	}


	public function testAnnotations()
	{
		$this->assertFalse($this->internalReflectionParameter->hasAnnotation('...'));
		$this->assertNull($this->internalReflectionParameter->getAnnotation('...'));
		$this->assertSame([], $this->internalReflectionParameter->getAnnotations());
	}


	public function testDefaultValue()
	{
		$this->assertFalse($this->internalReflectionParameter->isDefaultValueConstant());
		$this->assertNull($this->internalReflectionParameter->getDefaultValueConstantName());
	}


	public function testGetOriginalTypeHint()
	{
		$this->assertNull($this->internalReflectionParameter->getOriginalTypeHint());
	}


	public function testCanBePassedByValue()
	{
		$this->assertTrue($this->internalReflectionParameter->canBePassedByValue());
	}


	public function testIsVariadic()
	{
		$this->assertFalse($this->internalReflectionParameter->isVariadic());
	}


	/**
	 * @expectedException \ReflectionException
	 */
	public function testGetDefaultValue()
	{
		$this->internalReflectionParameter->getDefaultValue();
	}


	/**
	 * @expectedException \ReflectionException
	 */
	public function testGetDefaultValueDefinition()
	{
		$this->internalReflectionParameter->getDefaultValueDefinition();
	}


	public function testGetExtension()
	{
		$this->assertInstanceOf('ReflectionExtension', $this->internalReflectionParameter->getExtension());
		$this->assertSame('Core', $this->internalReflectionParameter->getExtensionName());
	}


	public function testGetBroker()
	{
		$this->assertInstanceOf('ApiGen\TokenReflection\Broker\Broker', $this->internalReflectionParameter->getBroker());
	}

}
