<?php

namespace ApiGen\TokenReflection\Tests\Invalid;

use ApiGen;
use ApiGen\TokenReflection\Invalid\ReflectionConstant;
use Mockery;
use PHPUnit_Framework_TestCase;


class ReflectionConstantTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var ReflectionConstant
	 */
	private $reflectionConstant;


	protected function setUp()
	{
		$brokerMock = Mockery::mock('ApiGen\TokenReflection\Broker\Broker');
		$this->reflectionConstant = new ReflectionConstant('SomeNamespace\\CONSTANT', 'SomeConstant.php', $brokerMock);
	}


	public function testGetName()
	{
		$this->assertSame('SomeNamespace\CONSTANT', $this->reflectionConstant->getName());
		$this->assertSame('SomeNamespace\CONSTANT', $this->reflectionConstant->getPrettyName());
		$this->assertSame('CONSTANT', $this->reflectionConstant->getShortName());
	}


	public function testGetNamespaceName()
	{
		$this->assertSame('SomeNamespace', $this->reflectionConstant->getNamespaceName());
	}


	public function testNamespace()
	{
		$this->assertSame([], $this->reflectionConstant->getNamespaceAliases());
		$this->assertTrue($this->reflectionConstant->inNamespace());
	}


	public function testDeclaringClass()
	{
		$this->assertNull($this->reflectionConstant->getDeclaringClass());
		$this->assertNull($this->reflectionConstant->getDeclaringClassName());
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\BrokerException
	 */
	public function testGetFileReflection()
	{
		$this->reflectionConstant->getFileReflection();
	}


	public function testExtension()
	{
		$this->assertNull($this->reflectionConstant->getExtension());
		$this->assertFalse($this->reflectionConstant->getExtensionName());
	}


	public function testGetFileName()
	{
		$this->assertSame('SomeConstant.php', $this->reflectionConstant->getFileName());
	}


	public function testGetStartLine()
	{
		$this->assertNull($this->reflectionConstant->getStartLine());
	}


	public function testGetEndLine()
	{
		$this->assertNull($this->reflectionConstant->getEndLine());
	}


	public function testGetDocComment()
	{
		$this->assertFalse($this->reflectionConstant->getDocComment());
	}


	public function testAnnotations()
	{
		$this->assertFalse($this->reflectionConstant->hasAnnotation('...'));
		$this->assertNull($this->reflectionConstant->getAnnotation('...'));
		$this->assertSame([], $this->reflectionConstant->getAnnotations());
	}


	public function testValue()
	{
		$this->assertNull($this->reflectionConstant->getValue());
		$this->assertNull($this->reflectionConstant->getValueDefinition());
		$this->assertNull($this->reflectionConstant->getOriginalValueDefinition());
	}


	public function testModifiers()
	{
		$this->assertFalse($this->reflectionConstant->isInternal());
	}


	public function testIsUserDefined()
	{
		$this->assertTrue($this->reflectionConstant->isUserDefined());
		$this->assertTrue($this->reflectionConstant->isTokenized());
	}


	public function testGetSource()
	{
		$this->assertSame('', $this->reflectionConstant->getSource());
	}


	public function testPosition()
	{
		$this->assertSame(-1, $this->reflectionConstant->getStartPosition());
		$this->assertSame(-1, $this->reflectionConstant->getEndPosition());
	}


	public function testCompleteValidDeprecated()
	{
		$this->assertFalse($this->reflectionConstant->isValid());
		$this->assertFalse($this->reflectionConstant->isDeprecated());
	}


	public function testGetBroker()
	{
		$this->assertInstanceOf('ApiGen\TokenReflection\Broker\Broker', $this->reflectionConstant->getBroker());
	}

}
