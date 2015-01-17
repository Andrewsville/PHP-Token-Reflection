<?php

namespace ApiGen\TokenReflection\Tests\Dummy;

use ApiGen;
use ApiGen\TokenReflection\Dummy\ReflectionClass;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Parser\AnnotationParser;
use ApiGen\TokenReflection\Tests\TestCase;


class ReflectionClassTest extends TestCase
{

	/**
	 * @var string
	 */
	protected $type = 'class';

	/**
	 * @var ReflectionClass
	 */
	private $dummyReflectionClass;


	protected function setUp()
	{
		$this->dummyReflectionClass = $this->getBroker()->getClass('foo_bar');
	}


	public function testDummyClass()
	{
		static $classNames = [
			'ns\\nonexistent',
			'nonexistent'
		];

		$broker = $this->getBroker();

		$reflections = [];

		foreach ($classNames as $className) {
			$this->assertFalse($broker->hasClass($className));

			/** @var ReflectionClass $class */
			$class = $broker->getClass($className);
			$this->assertInstanceOf('ApiGen\TokenReflection\Dummy\ReflectionClass', $class);

			$reflections[$className] = $class;

			$nameParts = explode('\\', $className);
			if (1 === count($nameParts)) {
				$shortName = $nameParts[0];
				$namespaceName = '';
			} else {
				$shortName = array_pop($nameParts);
				$namespaceName = implode('\\', $nameParts);
			}

			$this->assertSame($className, $class->getName());
			$this->assertSame($className, $class->getPrettyName());
			$this->assertSame($shortName, $class->getShortName());
			$this->assertSame($namespaceName, $class->getNamespaceName());

			if (empty($namespaceName)) {
				$this->assertFalse($class->inNamespace());
			} else {
				$this->assertTrue($class->inNamespace());
			}
			$this->assertSame([], $class->getNamespaceAliases());

			$this->assertNull($class->getExtension());
			$this->assertFalse($class->getExtensionName());

			$this->assertNull($class->getFileName());
			$this->assertNull($class->getEndLine());
			$this->assertSame(-1, $class->getStartPosition());
			$this->assertSame(-1, $class->getEndPosition());
			$this->assertNull($class->getStartLine());

			try {
				$class->getFileReflection();
				$this->fail('Exception\\BrokerException expected');
			} catch (\Exception $e) {
				$this->assertInstanceOf('ApiGen\TokenReflection\\Exception\\BrokerException', $e);
			}

			$this->assertFalse($class->getDocComment());
			$this->assertSame([], $class->getAnnotations());
			$this->assertFalse($class->hasAnnotation(AnnotationParser::SHORT_DESCRIPTION));
			$this->assertNull($class->getAnnotation(AnnotationParser::SHORT_DESCRIPTION));

			$this->assertSame(0, $class->getModifiers());

			$this->assertFalse($class->isAbstract());
			$this->assertFalse($class->isFinal());
			$this->assertFalse($class->isInternal());
			$this->assertFalse($class->isInterface());
			$this->assertFalse($class->isException());
			$this->assertFalse($class->isInstantiable());
			$this->assertFalse($class->isCloneable());
			$this->assertFalse($class->isIterateable());
			$this->assertFalse($class->isInternal());
			$this->assertFalse($class->isUserDefined());
			$this->assertFalse($class->isTokenized());
			$this->assertFalse($class->isComplete());
			$this->assertTrue($class->isValid());
			$this->assertFalse($class->isDeprecated());

			$this->assertFalse($class->isTrait());
			$this->assertSame([], $class->getTraits());
			$this->assertSame([], $class->getTraitNames());
			$this->assertSame([], $class->getOwnTraits());
			$this->assertSame([], $class->getOwnTraitNames());
			$this->assertSame([], $class->getTraitAliases());
			$this->assertFalse($class->usesTrait('Any'));

			$this->assertFalse($class->isSubclassOf('Any'));
			$this->assertFalse($class->getParentClass());
			$this->assertNull($class->getParentClassName());
			$this->assertSame([], $class->getParentClasses());
			$this->assertSame([], $class->getParentClassNameList());

			$this->assertFalse($class->implementsInterface('Traversable'));
			$this->assertFalse($class->implementsInterface($broker->getClass('Traversable')));
			$this->assertSame([], $class->getInterfaces());
			$this->assertSame([], $class->getOwnInterfaces());
			$this->assertSame([], $class->getInterfaceNames());
			$this->assertSame([], $class->getOwnInterfaceNames());

			$this->assertNull($class->getConstructor());
			$this->assertNull($class->getDestructor());

			$this->assertFalse($class->hasMethod('Any'));
			$this->assertFalse($class->hasOwnMethod('Any'));
			$this->assertFalse($class->hasTraitMethod('Any'));
			$this->assertSame([], $class->getMethods());
			$this->assertSame([], $class->getOwnMethods());
			$this->assertSame([], $class->getTraitMethods());

			$this->assertFalse($class->hasConstant('Any'));
			$this->assertFalse($class->hasOwnConstant('Any'));
			$this->assertSame([], $class->getConstants());
			$this->assertSame([], $class->getOwnConstants());
			$this->assertSame([], $class->getConstantReflections());
			$this->assertSame([], $class->getOwnConstantReflections());

			$this->assertSame([], $class->getDefaultProperties());
			$this->assertFalse($class->hasProperty('Any'));
			$this->assertFalse($class->hasOwnProperty('Any'));
			$this->assertFalse($class->hasTraitProperty('Any'));
			$this->assertSame([], $class->getProperties());
			$this->assertSame([], $class->getOwnProperties());
			$this->assertSame([], $class->getTraitProperties());
			$this->assertSame([], $class->getStaticProperties());

			$this->assertSame([], $class->getDirectSubclasses());
			$this->assertSame([], $class->getDirectSubclassNames());
			$this->assertSame([], $class->getDirectImplementers());
			$this->assertSame([], $class->getDirectImplementerNames());
			$this->assertSame([], $class->getIndirectSubclasses());
			$this->assertSame([], $class->getIndirectSubclassNames());
			$this->assertSame([], $class->getIndirectImplementers());
			$this->assertSame([], $class->getIndirectImplementerNames());

			$this->assertFalse($class->isInstance(new \Exception()));

			$this->assertSame('', $class->getSource());

			$this->assertSame($broker, $class->getBroker());
		}
	}


	public function testDummyClassImplementsInterface1()
	{
		$this->assertFalse($this->dummyReflectionClass->implementsInterface('...'));
	}


	public function testDummyClassImplementsInterface2()
	{
		$this->assertFalse(
			$this->dummyReflectionClass->implementsInterface($this->getBroker()->getClass('Exception'))
		);
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testDummyClassGetMethod()
	{
		$this->dummyReflectionClass->getMethod('any');
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testDummyClassGetProperty()
	{
		$this->dummyReflectionClass->getProperty('any');
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testDummyClassGetStaticProperty()
	{
		$this->dummyReflectionClass->getStaticPropertyValue('any', NULL);
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testDummyClassSetStaticProperty()
	{
		$this->dummyReflectionClass->setStaticPropertyValue('foo', 'bar');
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testDummyClassGetConstantValue()
	{
		$this->dummyReflectionClass->getConstant('any');
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testDummyClassGetConstantReflection()
	{
		$this->dummyReflectionClass->getConstantReflection('any');
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testDummyClassIsInstance()
	{
		$this->dummyReflectionClass->isInstance(TRUE);
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testInternalClassGetMethod()
	{
		$this->dummyReflectionClass->getMethod('~non-existent~');
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testInternalClassGetProperty()
	{
		$this->dummyReflectionClass->getProperty('~non-existent~');
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testInternalClassGetStaticProperty()
	{
		$this->dummyReflectionClass->getStaticPropertyValue('~non-existent~', NULL);
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testInternalClassSetStaticProperty()
	{
		$this->dummyReflectionClass->setStaticPropertyValue('~non', 'existent~');
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testInternalClassGetConstantValue()
	{
		$this->dummyReflectionClass->getConstant('~non-existent~');
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testInternalClassGetConstantReflection()
	{
		$this->dummyReflectionClass->getConstantReflection('~non-existent~');
	}

}
