<?php

namespace ApiGen\TokenReflection\Tests\Invalid;

use ApiGen;
use ApiGen\TokenReflection\Invalid\ReflectionFunction;
use Mockery;
use PHPUnit_Framework_TestCase;


class ReflectionFunctionTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var ReflectionFunction
	 */
	private $reflectionFunction;


	protected function setUp()
	{
		$brokerMock = Mockery::mock('ApiGen\TokenReflection\Broker\Broker');
		$this->reflectionFunction = new ReflectionFunction('SomeNamespace\\callMe', 'SomeConstant.php', $brokerMock);
	}


	public function testGetName()
	{
		$this->assertSame('SomeNamespace\callMe', $this->reflectionFunction->getName());
		$this->assertSame('SomeNamespace\callMe()', $this->reflectionFunction->getPrettyName());
		$this->assertSame('callMe', $this->reflectionFunction->getShortName());
	}


	public function testGetNamespaceName()
	{
		$this->assertSame('SomeNamespace', $this->reflectionFunction->getNamespaceName());
	}


	public function testNamespace()
	{
		$this->assertSame([], $this->reflectionFunction->getNamespaceAliases());
		$this->assertTrue($this->reflectionFunction->inNamespace());
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\BrokerException
	 */
	public function testGetFileReflection()
	{
		$this->reflectionFunction->getFileReflection();
	}


	public function testExtension()
	{
		$this->assertNull($this->reflectionFunction->getExtension());
		$this->assertFalse($this->reflectionFunction->getExtensionName());
	}


	public function testGetFileName()
	{
		$this->assertSame('SomeConstant.php', $this->reflectionFunction->getFileName());
	}


	public function testGetStartLine()
	{
		$this->assertNull($this->reflectionFunction->getStartLine());
	}


	public function testGetEndLine()
	{
		$this->assertNull($this->reflectionFunction->getEndLine());
	}


	public function testGetDocComment()
	{
		$this->assertFalse($this->reflectionFunction->getDocComment());
	}


	public function testAnnotations()
	{
		$this->assertFalse($this->reflectionFunction->hasAnnotation('...'));
		$this->assertNull($this->reflectionFunction->getAnnotation('...'));
		$this->assertSame([], $this->reflectionFunction->getAnnotations());
	}


	public function testModifiers()
	{
		$this->assertFalse($this->reflectionFunction->isInternal());
	}


	public function testReturnsReference()
	{
		$this->assertFalse($this->reflectionFunction->returnsReference());
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\RuntimeException
	 */
	public function testGetParameter()
	{
		$this->reflectionFunction->getParameter('...');
	}


	public function testParameters()
	{
		$this->assertSame([], $this->reflectionFunction->getParameters());
		$this->assertSame(0, $this->reflectionFunction->getNumberOfParameters());
		$this->assertSame(0, $this->reflectionFunction->getNumberOfRequiredParameters());
	}


	public function testGetStaticVariables()
	{
		$this->assertSame([], $this->reflectionFunction->getStaticVariables());
	}


	public function testIsDisabled()
	{
		$this->assertFalse($this->reflectionFunction->isDisabled());
	}


	public function testIsUserDefined()
	{
		$this->assertTrue($this->reflectionFunction->isUserDefined());
		$this->assertTrue($this->reflectionFunction->isTokenized());
	}


	public function testGetSource()
	{
		$this->assertSame('', $this->reflectionFunction->getSource());
	}


	public function testPosition()
	{
		$this->assertSame(-1, $this->reflectionFunction->getStartPosition());
		$this->assertSame(-1, $this->reflectionFunction->getEndPosition());
	}


	public function testCompleteValidDeprecated()
	{
		$this->assertFalse($this->reflectionFunction->isValid());
		$this->assertFalse($this->reflectionFunction->isDeprecated());
		$this->assertFalse($this->reflectionFunction->isVariadic());
	}


	public function testGetBroker()
	{
		$this->assertInstanceOf('ApiGen\TokenReflection\Broker\Broker', $this->reflectionFunction->getBroker());
	}

}
