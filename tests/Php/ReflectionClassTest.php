<?php

namespace ApiGen\TokenReflection\Tests\Php;

use ApiGen;
use ApiGen\TokenReflection\Php\ReflectionClass;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Tests\TestCase;
use Mockery;


class ReflectionClassTest extends TestCase
{

	/**
	 * @var string
	 */
	protected $type = 'class';

	/**
	 * @var ReflectionClass
	 */
	private $internalReflectionClass;


	protected function setUp()
	{
		parent::setUp();
		$this->internalReflectionClass = $this->broker->getStorage()->getClass('Exception');
	}


	public function testBasicMethods()
	{
		$this->assertFalse($this->internalReflectionClass->hasAnnotation('...'));
		$this->assertNull($this->internalReflectionClass->getAnnotation('...'));
		$this->assertSame([], $this->internalReflectionClass->getAnnotations());

		$this->assertTrue($this->internalReflectionClass->isException());
		$this->assertTrue($this->internalReflectionClass->isCloneable());

		$this->assertFalse($this->internalReflectionClass->isTokenized());
		$this->assertFalse($this->internalReflectionClass->isDeprecated());
		$this->assertTrue($this->internalReflectionClass->isComplete());

		$this->assertSame('Exception', $this->internalReflectionClass->getPrettyName());
	}


	public function testNamespaces()
	{
		$this->assertSame([], $this->internalReflectionClass->getNamespaceAliases());
	}


	public function testParents()
	{
		$this->assertNull($this->internalReflectionClass->getParentClass());
		$this->assertSame([], $this->internalReflectionClass->getParentClasses());
		$this->assertNull($this->internalReflectionClass->getParentClassName());
		$this->assertSame([], $this->internalReflectionClass->getParentClassNameList());
	}


	public function testCtor()
	{
		$this->assertInstanceOf(
			'ApiGen\TokenReflection\Php\ReflectionMethod', $this->internalReflectionClass->getConstructor()
		);
		$this->assertNull($this->internalReflectionClass->getDestructor());
	}


	public function testMethods()
	{
		$this->assertFalse($this->internalReflectionClass->hasMethod('...'));
		$this->assertFalse($this->internalReflectionClass->hasOwnMethod('...'));
		$this->assertInstanceOf(
			'ApiGen\TokenReflection\Php\ReflectionMethod', $this->internalReflectionClass->getMethod('getMessage')
		);

		$this->assertCount(10, $this->internalReflectionClass->getOwnMethods());

		$this->assertFalse($this->internalReflectionClass->hasTraitMethod('...'));
		$this->assertSame([], $this->internalReflectionClass->getTraitMethods());
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testGetConstantReflection()
	{
		$this->internalReflectionClass->getConstantReflection('...');
	}


	public function testConstants()
	{
		$this->assertSame([], $this->internalReflectionClass->getConstantReflections());
		$this->assertFalse($this->internalReflectionClass->hasOwnConstant('...'));
		$this->assertSame([], $this->internalReflectionClass->getOwnConstants());
		$this->assertSame([], $this->internalReflectionClass->getOwnConstantReflections());
	}


	public function testProperties()
	{
		$this->assertInstanceOf('ApiGen\TokenReflection\Php\ReflectionProperty', $this->internalReflectionClass->getProperty('message'));
		$this->assertCount(7, $this->internalReflectionClass->getProperties());
		$this->assertFalse($this->internalReflectionClass->hasOwnProperty('...'));
		$this->assertCount(7, $this->internalReflectionClass->getOwnProperties());
		$this->assertFalse($this->internalReflectionClass->hasTraitProperty('...'));
	}


	public function testStaticProperties()
	{
		$this->assertCount(0, $this->internalReflectionClass->getStaticProperties());
		$this->assertSame('two', $this->internalReflectionClass->getStaticPropertyValue('one', 'two'));
	}


	public function testSubclasses()
	{
		$this->assertSame([], $this->internalReflectionClass->getDirectSubclasses());
		$this->assertSame([], $this->internalReflectionClass->getDirectSubclassNames());
		$this->assertSame([], $this->internalReflectionClass->getIndirectSubclasses());
		$this->assertSame([], $this->internalReflectionClass->getIndirectSubclassNames());
	}


	public function testImplementers()
	{
		$this->assertSame([], $this->internalReflectionClass->getDirectImplementers());
		$this->assertSame([], $this->internalReflectionClass->getDirectImplementerNames());
		$this->assertSame([], $this->internalReflectionClass->getIndirectImplementers());
		$this->assertSame([], $this->internalReflectionClass->getIndirectImplementerNames());
	}


	public function testInterfaces()
	{
		$this->assertSame([], $this->internalReflectionClass->getInterfaces());
		$this->assertSame([], $this->internalReflectionClass->getInterfaceNames());
		$this->assertSame([], $this->internalReflectionClass->getOwnInterfaces());
		$this->assertSame([], $this->internalReflectionClass->getOwnInterfaceNames());
	}


	public function testInternalClassIsSubclassOf()
	{
		$classReflectionMock = Mockery::mock('ApiGen\TokenReflection\Reflection\ReflectionClass');
		$classReflectionMock->shouldReceive('getName')->andReturn('SomeClass');
		$this->internalReflectionClass->isSubclassOf($classReflectionMock);
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testInternalClassIsSubclassOfInvalidObject()
	{
		$this->internalReflectionClass->isSubclassOf(new \Exception());
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testInternalClassImplementsInterface1()
	{
		$this->internalReflectionClass->implementsInterface(new \Exception());
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testInternalClassImplementsInterface2()
	{
		$this->internalReflectionClass->implementsInterface($this->broker->getStorage()->getClass('Exception'));
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testInternalClassImplementsInterface3()
	{
		$this->internalReflectionClass->implementsInterface('Exception');
	}


	public function testTraits()
	{
		$this->assertFalse($this->internalReflectionClass->usesTrait(new \Exception()));
		$this->assertFalse($this->internalReflectionClass->usesTrait($this->broker->getStorage()->getClass('Exception')));
		$this->assertFalse($this->internalReflectionClass->usesTrait('Exception'));

		$this->assertSame([], $this->internalReflectionClass->getOwnTraits());
		$this->assertSame([], $this->internalReflectionClass->getOwnTraitNames());
		$this->assertSame([], $this->internalReflectionClass->getTraitProperties());
	}


	public function testGetExtension()
	{
		$this->assertInstanceOf('ApiGen\TokenReflection\Php\ReflectionExtension', $this->internalReflectionClass->getExtension());
	}


	public function testGetStorage()
	{
		$this->assertInstanceOf(
			'ApiGen\TokenReflection\Broker\StorageInterface',
			$this->internalReflectionClass->getStorage()
		);
	}

}
