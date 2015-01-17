<?php

namespace ApiGen\TokenReflection\Tests\Invalid;

use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Invalid\ReflectionClass;
use Mockery;
use PHPUnit_Framework_TestCase;


class ReflectionClassTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var ReflectionClass
	 */
	private $reflectionClass;


	protected function setUp()
	{
		$brokerMock = Mockery::mock('ApiGen\TokenReflection\Broker\Broker');
		$this->reflectionClass = new ReflectionClass('SomeNamespace\\SomeInvalidClass', 'SomeClass.php', $brokerMock);
	}


	public function testGetName()
	{
		$this->assertSame('SomeNamespace\SomeInvalidClass', $this->reflectionClass->getName());
		$this->assertSame('SomeNamespace\SomeInvalidClass', $this->reflectionClass->getPrettyName());
		$this->assertSame('SomeInvalidClass', $this->reflectionClass->getShortName());
	}


	public function testGetNamespaceName()
	{
		$this->assertSame('SomeNamespace', $this->reflectionClass->getNamespaceName());
	}


	public function testNamespace()
	{
		$this->assertSame([], $this->reflectionClass->getNamespaceAliases());
		$this->assertTrue($this->reflectionClass->inNamespace());
	}


	public function testExtension()
	{
		$this->assertNull($this->reflectionClass->getExtension());
		$this->assertFalse($this->reflectionClass->getExtensionName());
	}


	public function testGetFileName()
	{
		$this->assertSame('SomeClass.php', $this->reflectionClass->getFileName());
	}


	public function testGetStartLine()
	{
		$this->assertNull($this->reflectionClass->getStartLine());
	}


	public function testGetEndLine()
	{
		$this->assertNull($this->reflectionClass->getEndLine());
	}


	public function testGetDocComment()
	{
		$this->assertFalse($this->reflectionClass->getDocComment());
	}


	public function testAnnotations()
	{
		$this->assertFalse($this->reflectionClass->hasAnnotation('...'));
		$this->assertNull($this->reflectionClass->getAnnotation('...'));
		$this->assertSame([], $this->reflectionClass->getAnnotations());
	}


	public function testModifiers()
	{
		$this->assertSame(0, $this->reflectionClass->getModifiers());
		$this->assertFalse($this->reflectionClass->isAbstract());
		$this->assertFalse($this->reflectionClass->isFinal());
		$this->assertFalse($this->reflectionClass->isInterface());
		$this->assertFalse($this->reflectionClass->isIterateable());
		$this->assertFalse($this->reflectionClass->isException());
		$this->assertFalse($this->reflectionClass->isTrait());
		$this->assertFalse($this->reflectionClass->isInstantiable());
		$this->assertFalse($this->reflectionClass->isCloneable());
		$this->assertFalse($this->reflectionClass->isInternal());
	}


	public function testTraits()
	{
		$this->assertSame([], $this->reflectionClass->getTraits());
		$this->assertSame([], $this->reflectionClass->getOwnTraits());
		$this->assertSame([], $this->reflectionClass->getTraitNames());
		$this->assertSame([], $this->reflectionClass->getOwnTraitNames());
		$this->assertSame([], $this->reflectionClass->getTraitAliases());
		$this->assertFalse($this->reflectionClass->usesTrait('...'));
	}


	public function testIsUserDefined()
	{
		$this->assertTrue($this->reflectionClass->isUserDefined());
		$this->assertTrue($this->reflectionClass->isTokenized());
	}


	public function testParents()
	{
		$this->assertFalse($this->reflectionClass->getParentClass());
		$this->assertSame([], $this->reflectionClass->getParentClasses());
		$this->assertSame([], $this->reflectionClass->getParentClasses());
		$this->assertNull($this->reflectionClass->getParentClassName());
		$this->assertSame([], $this->reflectionClass->getParentClassNameList());
		$this->assertFalse($this->reflectionClass->isSubclassOf('...'));
	}


	public function testChildren()
	{
		$this->assertSame([], $this->reflectionClass->getDirectSubclasses());
		$this->assertSame([], $this->reflectionClass->getDirectSubclassNames());
		$this->assertSame([], $this->reflectionClass->getIndirectSubclasses());
		$this->assertSame([], $this->reflectionClass->getIndirectSubclassNames());
		$this->assertSame([], $this->reflectionClass->getDirectImplementers());
		$this->assertSame([], $this->reflectionClass->getDirectImplementerNames());
		$this->assertSame([], $this->reflectionClass->getIndirectImplementers());
		$this->assertSame([], $this->reflectionClass->getIndirectImplementerNames());
	}


	public function testInterfaces()
	{
		$this->assertFalse($this->reflectionClass->implementsInterface('...'));
		$this->assertSame([], $this->reflectionClass->getInterfaces());
		$this->assertSame([], $this->reflectionClass->getOwnInterfaces());
		$this->assertSame([], $this->reflectionClass->getInterfaceNames());
		$this->assertSame([], $this->reflectionClass->getOwnInterfaceNames());
	}


	public function testCtors()
	{
		$this->assertNull($this->reflectionClass->getConstructor());
		$this->assertNull($this->reflectionClass->getDestructor());
	}


	public function testMethods()
	{
		$this->assertFalse($this->reflectionClass->hasMethod('...'));
		$this->assertFalse($this->reflectionClass->hasOwnMethod('...'));
		$this->assertSame([], $this->reflectionClass->getMethods());
		$this->assertSame([], $this->reflectionClass->getOwnMethods());

		$this->assertFalse($this->reflectionClass->hasTraitMethod('...'));
		$this->assertSame([], $this->reflectionClass->getTraitMethods());
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testGetMethod()
	{
		$this->reflectionClass->getMethod('...');
	}


	public function testConstants()
	{
		$this->assertFalse($this->reflectionClass->hasConstant('...'));
		$this->assertFalse($this->reflectionClass->hasOwnConstant('...'));
		$this->assertSame([], $this->reflectionClass->getConstants());
		$this->assertSame([], $this->reflectionClass->getOwnConstants());
		$this->assertSame([], $this->reflectionClass->getConstantReflections());
		$this->assertSame([], $this->reflectionClass->getOwnConstantReflections());
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testGetConstant()
	{
		$this->reflectionClass->getConstant('...');
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testGetConstantReflection()
	{
		$this->reflectionClass->getConstantReflection('...');
	}


	public function testProperties()
	{
		$this->assertSame([], $this->reflectionClass->getDefaultProperties());
		$this->assertFalse($this->reflectionClass->hasProperty('...'));
		$this->assertFalse($this->reflectionClass->hasOwnProperty('...'));
		$this->assertFalse($this->reflectionClass->hasTraitProperty('...'));
		$this->assertSame([], $this->reflectionClass->getProperties());
		$this->assertSame([], $this->reflectionClass->getOwnProperties());
		$this->assertSame([], $this->reflectionClass->getTraitProperties());
		$this->assertSame([], $this->reflectionClass->getStaticProperties());
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testGetProperty()
	{
		$this->reflectionClass->getProperty('...');
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testGetStaticPropertyValue()
	{
		$this->reflectionClass->getStaticPropertyValue('...');
	}


	public function testIsInstance()
	{
		$this->assertFalse($this->reflectionClass->isInstance($this->reflectionClass));
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testIsInstanceWithString()
	{
		$this->reflectionClass->isInstance('...');
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testSetStaticPropertyValue()
	{
		$this->reflectionClass->setStaticPropertyValue('name', 'value');
	}


	public function testGetSource()
	{
		$this->assertSame('', $this->reflectionClass->getSource());
	}


	public function testPosition()
	{
		$this->assertSame(-1, $this->reflectionClass->getStartPosition());
		$this->assertSame(-1, $this->reflectionClass->getEndPosition());
	}


	public function testCompleteValidDeprecated()
	{
		$this->assertTrue($this->reflectionClass->isComplete());
		$this->assertFalse($this->reflectionClass->isValid());
		$this->assertFalse($this->reflectionClass->isDeprecated());
	}


	public function testGetBroker()
	{
		$this->assertInstanceOf('ApiGen\TokenReflection\Broker\Broker', $this->reflectionClass->getBroker());
	}

}
