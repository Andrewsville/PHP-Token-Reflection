<?php
/**
 * PHP Token Reflection
 *
 * Version 1.3.1
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor
 * @author Jaroslav Hanslík
 */

namespace TokenReflection;

use ReflectionClass as InternalReflectionClass;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Class test.
 */
class ReflectionClassTest extends Test
{
	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type = 'class';

	/**
	 * Tests the dummy class reflection interface.
	 */
	public function testDummyClass()
	{
		static $classNames = array(
			'ns\\non-existent',
			'non-existent'
		);

		$broker = $this->getBroker();

		foreach ($classNames as $className) {
			$this->assertFalse($broker->hasClass($className));

			$class = $broker->getClass($className);
			$this->assertInstanceOf('TokenReflection\Dummy\ReflectionClass', $class);

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

			$this->assertNull($class->getExtension());
			$this->assertFalse($class->getExtensionName());

			$this->assertNull($class->getFileName());
			$this->assertNull($class->getEndLine());
			$this->assertNull($class->getStartLine());

			$this->assertFalse($class->getDocComment());
			$this->assertSame(array(), $class->getAnnotations());
			$this->assertFalse($class->hasAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));
			$this->assertNull($class->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));

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

			$this->assertFalse($class->isTrait());
			$this->assertSame(array(), $class->getTraits());
			$this->assertSame(array(), $class->getTraitNames());
			$this->assertSame(array(), $class->getOwnTraits());
			$this->assertSame(array(), $class->getOwnTraitNames());
			$this->assertSame(array(), $class->getTraitAliases());
			$this->assertFalse($class->usesTrait('Any'));

			$this->assertFalse($class->isSubclassOf('Any'));
			$this->assertFalse($class->getParentClass());
			$this->assertNull($class->getParentClassName());
			$this->assertSame(array(), $class->getParentClasses());
			$this->assertSame(array(), $class->getParentClassNameList());

			$this->assertFalse($class->implementsInterface('Traversable'));
			$this->assertFalse($class->implementsInterface($broker->getClass('Traversable')));
			$this->assertSame(array(), $class->getInterfaces());
			$this->assertSame(array(), $class->getOwnInterfaces());
			$this->assertSame(array(), $class->getInterfaceNames());
			$this->assertSame(array(), $class->getOwnInterfaceNames());

			$this->assertNull($class->getConstructor());
			$this->assertNull($class->getDestructor());

			$this->assertFalse($class->hasMethod('Any'));
			$this->assertFalse($class->hasOwnMethod('Any'));
			$this->assertFalse($class->hasTraitMethod('Any'));
			$this->assertSame(array(), $class->getMethods());
			$this->assertSame(array(), $class->getOwnMethods());
			$this->assertSame(array(), $class->getTraitMethods());

			$this->assertFalse($class->hasConstant('Any'));
			$this->assertFalse($class->hasOwnConstant('Any'));
			$this->assertSame(array(), $class->getConstants());
			$this->assertSame(array(), $class->getOwnConstants());
			$this->assertSame(array(), $class->getConstantReflections());
			$this->assertSame(array(), $class->getOwnConstantReflections());

			$this->assertSame(array(), $class->getDefaultProperties());
			$this->assertFalse($class->hasProperty('Any'));
			$this->assertFalse($class->hasOwnProperty('Any'));
			$this->assertFalse($class->hasTraitProperty('Any'));
			$this->assertSame(array(), $class->getProperties());
			$this->assertSame(array(), $class->getOwnProperties());
			$this->assertSame(array(), $class->getTraitProperties());
			$this->assertSame(array(), $class->getStaticProperties());

			$this->assertSame(array(), $class->getDirectSubclasses());
			$this->assertSame(array(), $class->getDirectSubclassNames());
			$this->assertSame(array(), $class->getDirectImplementers());
			$this->assertSame(array(), $class->getDirectImplementerNames());
			$this->assertSame(array(), $class->getIndirectSubclasses());
			$this->assertSame(array(), $class->getIndirectSubclassNames());
			$this->assertSame(array(), $class->getIndirectImplementers());
			$this->assertSame(array(), $class->getIndirectImplementerNames());

			$this->assertFalse($class->isInstance(new \Exception()));

			$this->assertSame('', $class->getSource());

			$this->assertSame($broker, $class->getBroker());
		}
	}

	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassImplementsInterface1()
	{
		$this->getDummyClassReflection()->implementsInterface(new \Exception());
	}

	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassImplementsInterface2()
	{
		$this->getDummyClassReflection()->implementsInterface($this->getBroker()->getClass('Exception'));
	}

	/**
	 * Tests an exception thrown when getting a method from a dummy class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassGetMethod()
	{
		$this->getDummyClassReflection()->getMethod('any');
	}

	/**
	 * Tests an exception thrown when getting a property from a dummy class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassGetProperty()
	{
		$this->getDummyClassReflection()->getProperty('any');
	}

	/**
	 * Tests an exception thrown when getting a static property from a dummy class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassGetStaticProperty()
	{
		$this->getDummyClassReflection()->getStaticPropertyValue('any', null);
	}

	/**
	 * Tests an exception thrown when setting a static property from a dummy class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassSetStaticProperty()
	{
		$this->getDummyClassReflection()->setStaticPropertyValue('foo', 'bar');
	}

	/**
	 * Tests an exception thrown when getting a constant value from a dummy class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassGetConstantValue()
	{
		$this->getDummyClassReflection()->getConstant('any');
	}

	/**
	 * Tests an exception thrown when getting a constant reflection from a dummy class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassGetConstantReflection()
	{
		$this->getDummyClassReflection()->getConstantReflection('any');
	}

	/**
	 * Tests an exception thrown when providing an invalid argument to isInstance() method.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassIsInstance()
	{
		$this->getDummyClassReflection()->isInstance(true);
	}

	/**
	 * Tests an exception thrown when trying to instantiate a non existent class.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyNewInstanceWithoutConstructor()
	{
		$this->getDummyClassReflection()->newInstanceWithoutConstructor();
	}

	/**
	 * Tests an exception thrown when trying to instantiate a non existent class.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyNewInstance()
	{
		$this->getDummyClassReflection()->newInstance(null);
	}

	/**
	 * Tests an exception thrown when trying to instantiate a non existent class.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyNewInstanceArgs()
	{
		$this->getDummyClassReflection()->newInstanceArgs();
	}

	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassIsSubclassOf()
	{
		$this->getInternalClassReflection()->isSubclassOf(new \Exception());
	}

	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassImplementsInterface1()
	{
		$this->getInternalClassReflection()->implementsInterface(new \Exception());
	}

	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassImplementsInterface2()
	{
		$this->getInternalClassReflection()->implementsInterface($this->getBroker()->getClass('Exception'));
	}

	/**
	 * Tests an exception thrown when providing an invalid class name.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassImplementsInterface3()
	{
		$this->getInternalClassReflection()->implementsInterface('Exception');
	}

	/**
	 * Tests an exception thrown when getting a method from an internal class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassGetMethod()
	{
		$this->getDummyClassReflection()->getMethod('~non-existent~');
	}

	/**
	 * Tests an exception thrown when getting a property from an internal class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassGetProperty()
	{
		$this->getDummyClassReflection()->getProperty('~non-existent~');
	}

	/**
	 * Tests an exception thrown when getting a static property from an internal class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassGetStaticProperty()
	{
		$this->getDummyClassReflection()->getStaticPropertyValue('~non-existent~', null);
	}

	/**
	 * Tests an exception thrown when setting a static property from an internal class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassSetStaticProperty()
	{
		$this->getDummyClassReflection()->setStaticPropertyValue('~non', 'existent~');
	}

	/**
	 * Tests an exception thrown when getting a constant value from an internal class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassGetConstantValue()
	{
		$this->getDummyClassReflection()->getConstant('~non-existent~');
	}

	/**
	 * Tests an exception thrown when getting a constant reflection from an internal class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassGetConstantReflection()
	{
		$this->getDummyClassReflection()->getConstantReflection('~non-existent~');
	}

	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassUsesTrait1()
	{
		$this->getInternalClassReflection()->usesTrait(new \Exception());
	}

	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassUsesTrait2()
	{
		$this->getInternalClassReflection()->usesTrait($this->getBroker()->getClass('Exception'));
	}

	/**
	 * Tests an exception thrown when providing an invalid class name.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassUsesTrait3()
	{
		$this->getInternalClassReflection()->usesTrait('Exception');
	}

	/**
	 * Tests an exception thrown when it is impossible to create an instance without invoking the constructor.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassNewInstanceWithoutConstructor1()
	{
		$this->getInternalClassReflection()->newInstanceWithoutConstructor();
	}

	/**
	 * Tests an exception thrown when it is impossible to create an instance without invoking the constructor.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassNewInstanceWithoutConstructor2()
	{
		$reflection = new Php\ReflectionClass('TokenReflection\Exception\RuntimeException', $this->getBroker());
		$reflection->newInstanceWithoutConstructor();
	}

	/**
	 * Tests an exception thrown when trying to create the reflection from a PHP internal reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassReflectionCreate()
	{
		Php\ReflectionClass::create(new \ReflectionFunction('create_function'), $this->getBroker());
	}

	/**
	 * Tests getting of class constants.
	 */
	public function testConstants()
	{
		$rfl = $this->getClassReflection('constants');

		$this->assertSame($rfl->internal->hasConstant('STRING'), $rfl->token->hasConstant('STRING'));
		$this->assertTrue($rfl->token->hasConstant('STRING'));
		$this->assertTrue($rfl->token->hasOwnConstant('STRING'));
		$this->assertSame($rfl->internal->hasConstant('NONEXISTENT'), $rfl->token->hasConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasOwnConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasOwnConstant('PARENT'));

		$this->assertSame($rfl->internal->getConstant('STRING'), $rfl->token->getConstant('STRING'));
		$this->assertSame('string', $rfl->token->getConstant('STRING'));
		$this->assertSame($rfl->internal->getConstant('NONEXISTENT'), $rfl->token->getConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->getConstant('NONEXISTENT'));
		$this->assertSame($rfl->internal->getConstants(), $rfl->token->getConstants());
		$this->assertSame(array('STRING' => 'string', 'INTEGER' => 1, 'FLOAT' => 1.1, 'BOOLEAN' => true, 'PARENT' => 'parent'), $rfl->token->getConstants());
		$this->assertSame(array('STRING' => 'string', 'INTEGER' => 1, 'FLOAT' => 1.1, 'BOOLEAN' => true), $rfl->token->getOwnConstants());
		$this->assertSame(range(0, 3), array_keys($rfl->token->getOwnConstantReflections()));
		foreach ($rfl->token->getOwnConstantReflections() as $constant) {
			$this->assertInstanceOf('TokenReflection\ReflectionConstant', $constant);
		}

		$rfl = $this->getClassReflection('noConstants');

		$this->assertSame($rfl->internal->hasConstant('NONEXISTENT'), $rfl->token->hasConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasOwnConstant('NONEXISTENT'));

		$this->assertSame($rfl->internal->getConstant('NONEXISTENT'), $rfl->token->getConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->getConstant('NONEXISTENT'));
		$this->assertSame($rfl->internal->getConstants(), $rfl->token->getConstants());
		$this->assertSame(array(), $rfl->token->getConstants());
		$this->assertSame(array(), $rfl->token->getOwnConstants());
		$this->assertSame(array(), $rfl->token->getOwnConstantReflections());

		$token = $this->getBroker()->getClass('RecursiveDirectoryIterator');
		$this->assertTrue($token->hasConstant('CURRENT_AS_PATHNAME'));
		$this->assertFalse($token->hasOwnConstant('CURRENT_AS_PATHNAME'));
		$this->assertSame(0, count($token->getOwnConstants()));
		$this->assertSame(0, count($token->getOwnConstantReflections()));
		$this->assertSame('FilesystemIterator', $token->getConstantReflection('CURRENT_AS_PATHNAME')->getDeclaringClassName());
	}

	/**
	 * Tests getting of class properties.
	 */
	public function testProperties()
	{
		$rfl = $this->getClassReflection('properties');

		$filters = array(\ReflectionProperty::IS_STATIC, \ReflectionProperty::IS_PUBLIC, \ReflectionProperty::IS_PROTECTED, \ReflectionProperty::IS_PRIVATE);
		foreach ($this->getFilterCombinations($filters) as $filter) {
			$this->assertSame(array_keys($rfl->internal->getProperties($filter)), array_keys($rfl->token->getProperties($filter)));
			foreach ($rfl->token->getProperties($filter) as $property) {
				$this->assertInstanceOf('TokenReflection\ReflectionProperty', $property);
			}
			foreach ($rfl->token->getOwnProperties($filter) as $property) {
				$this->assertInstanceOf('TokenReflection\ReflectionProperty', $property);
			}
		}

		$this->assertSame($rfl->internal->getDefaultProperties(), $rfl->token->getDefaultProperties());
		$this->assertSame(array('publicStatic' => true, 'privateStatic' => 'something', 'protectedStatic' => 1, 'public' => false, 'private' => '', 'protected' => 0), $rfl->token->getDefaultProperties());

		$this->assertSame($rfl->internal->getStaticProperties(), $rfl->token->getStaticProperties());
		$this->assertSame(array('publicStatic' => true, 'privateStatic' => 'something', 'protectedStatic' => 1), $rfl->token->getStaticProperties());

		$properties = array('public', 'publicStatic', 'protectedStatic', 'protectedStatic', 'private', 'privateStatic');
		foreach ($properties as $property) {
			$this->assertSame($rfl->internal->hasProperty($property), $rfl->token->hasProperty($property));
			$this->assertTrue($rfl->token->hasProperty($property));

			$this->assertInstanceOf('TokenReflection\ReflectionProperty', $rfl->token->getProperty($property));
		}

		$properties = array('public', 'publicStatic', 'private', 'privateStatic');
		foreach ($properties as $property) {
			$this->assertTrue($rfl->token->hasOwnProperty($property));
		}
		$properties = array('protectedStatic', 'protectedStatic');
		foreach ($properties as $property) {
			$this->assertFalse($rfl->token->hasOwnProperty($property));
		}

		$this->assertFalse($rfl->token->hasProperty('nonExistent'));
		try {
			$rfl->token->getProperty('nonExistent');
			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}

		$this->assertSame($rfl->internal->getStaticPropertyValue('publicStatic'), $rfl->token->getStaticPropertyValue('publicStatic'));
		$this->assertTrue($rfl->token->getStaticPropertyValue('publicStatic'));

		try {
			$rfl->token->getStaticPropertyValue('protectedStatic');
			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}

		try {
			$rfl->token->getStaticPropertyValue('privateStatic');
			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}

		$this->assertSame($rfl->internal->setStaticPropertyValue('publicStatic', false), $rfl->token->setStaticPropertyValue('publicStatic', false));
		$this->assertNull($rfl->token->setStaticPropertyValue('publicStatic', false));
		$this->assertSame($rfl->internal->getStaticPropertyValue('publicStatic'), $rfl->token->getStaticPropertyValue('publicStatic'));
		$this->assertFalse($rfl->token->getStaticPropertyValue('publicStatic'));

		try {
			$rfl->token->setStaticPropertyValue('protectedStatic', 0);
			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}

		try {
			$rfl->token->setStaticPropertyValue('privateStatic', '');
			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}

		$rfl = $this->getClassReflection('noProperties');

		$this->assertSame($rfl->internal->getDefaultProperties(), $rfl->token->getDefaultProperties());
		$this->assertSame(array(), $rfl->token->getDefaultProperties());
		$this->assertSame($rfl->internal->getProperties(), $rfl->token->getProperties());
		$this->assertSame(array(), $rfl->token->getProperties());
		$this->assertSame(array(), $rfl->token->getOwnProperties());
		$this->assertSame($rfl->internal->getStaticProperties(), $rfl->token->getStaticProperties());
		$this->assertSame(array(), $rfl->token->getStaticProperties());

		$this->assertSame($rfl->internal->hasProperty('nonExistent'), $rfl->token->hasProperty('nonExistent'));
		$this->assertFalse($rfl->token->hasProperty('nonExistent'));
		$this->assertFalse($rfl->token->hasOwnProperty('nonExistent'));

		try {
			$rfl->token->getProperty('nonExistent');
			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}

		try {
			$rfl->token->getStaticPropertyValue('nonExistent');
			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}

		try {
			$rfl->token->setStaticPropertyValue('property', 'property');
			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}

		$rfl = $this->getClassReflection('doubleProperties');

		$filters = array(\ReflectionProperty::IS_STATIC, \ReflectionProperty::IS_PUBLIC, \ReflectionProperty::IS_PROTECTED, \ReflectionProperty::IS_PRIVATE);
		foreach ($this->getFilterCombinations($filters) as $filter) {
			$this->assertSame(array_keys($rfl->internal->getProperties($filter)), array_keys($rfl->token->getProperties($filter)), $filter);
			foreach ($rfl->token->getProperties($filter) as $property) {
				$this->assertInstanceOf('TokenReflection\ReflectionProperty', $property);
			}
			foreach ($rfl->token->getOwnProperties($filter) as $property) {
				$this->assertInstanceOf('TokenReflection\ReflectionProperty', $property);
			}
		}

		$this->assertSame($rfl->internal->getDefaultProperties(), $rfl->token->getDefaultProperties());
		$this->assertSame(array('protectedOne' => 1, 'protectedTwo' => 0, 'publicOne' => true, 'publicTwo' => false, 'privateOne' => 'something', 'privateTwo' => ''), $rfl->token->getDefaultProperties());

		$this->assertSame($rfl->internal->getStaticProperties(), $rfl->token->getStaticProperties());
		$this->assertSame(array('protectedOne' => 1, 'protectedTwo' => 0), $rfl->token->getStaticProperties());

		$properties = array('publicOne', 'publicTwo', 'protectedOne', 'protectedTwo', 'privateOne', 'privateTwo');
		foreach ($properties as $property) {
			$this->assertSame($rfl->internal->hasProperty($property), $rfl->token->hasProperty($property));
			$this->assertTrue($rfl->token->hasProperty($property));

			$this->assertInstanceOf('TokenReflection\ReflectionProperty', $rfl->token->getProperty($property));
		}
	}

	/**
	 * Tests if class is instantiable or cloneable.
	 */
	public function testInstantiableCloneable()
	{
		$rfl = $this->getClassReflection('publicConstructor');
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertTrue($rfl->token->isInstantiable());
		if (PHP_VERSION_ID >= 50400) {
			$this->assertSame($rfl->internal->isCloneable(), $rfl->token->isCloneable());
		}
		$this->assertTrue($rfl->token->isCloneable());

		$rfl = $this->getClassReflection('privateConstructor');
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertFalse($rfl->token->isInstantiable());
		if (PHP_VERSION_ID >= 50400) {
			$this->assertSame($rfl->internal->isCloneable(), $rfl->token->isCloneable());
		}
		$this->assertTrue($rfl->token->isCloneable());

		$rfl = $this->getClassReflection('publicClone');
		if (PHP_VERSION_ID >= 50400) {
			$this->assertSame($rfl->internal->isCloneable(), $rfl->token->isCloneable());
		}
		$this->assertTrue($rfl->token->isCloneable());

		$rfl = $this->getClassReflection('privateClone');
		if (PHP_VERSION_ID >= 50400) {
			$this->assertSame($rfl->internal->isCloneable(), $rfl->token->isCloneable());
		}
		$this->assertFalse($rfl->token->isCloneable());
	}

	/**
	 * Tests class modifiers.
	 */
	public function testModifiers()
	{
		static $classes = array(
			'TokenReflection_Test_ClassModifiersIface1',
			'TokenReflection_Test_ClassModifiersIface2',
			'TokenReflection_Test_ClassModifiersIface3',
			'TokenReflection_Test_ClassModifiersIface4',
			'TokenReflection_Test_ClassModifiersClass1',
			'TokenReflection_Test_ClassModifiersClass2',
			'TokenReflection_Test_ClassModifiersClass3',
			'TokenReflection_Test_ClassModifiersClass4',
			'TokenReflection_Test_ClassModifiersClass5',
			'TokenReflection_Test_ClassModifiersClass6',
			'TokenReflection_Test_ClassModifiersClass7',
			'TokenReflection_Test_ClassModifiersClass8',
		);

		require_once $this->getFilePath('modifiers');
		$this->getBroker()->process($this->getFilePath('modifiers'));

		foreach ($classes as $className) {
			$token = $this->getBroker()->getClass($className);
			$internal = new \ReflectionClass($className);

			$this->assertSame($internal->getModifiers(), $token->getModifiers(), $className);
		}
	}

	/**
	 * Tests getting of class methods.
	 */
	public function testMethods()
	{
		$rfl = $this->getClassReflection('methods');

		$filters = array(\ReflectionMethod::IS_STATIC, \ReflectionMethod::IS_PUBLIC, \ReflectionMethod::IS_PROTECTED, \ReflectionMethod::IS_PRIVATE, \ReflectionMethod::IS_ABSTRACT, \ReflectionMethod::IS_FINAL);
		foreach ($this->getFilterCombinations($filters) as $filter) {
			$this->assertSame(array_keys($rfl->internal->getMethods($filter)), array_keys($rfl->token->getMethods($filter)));
			foreach ($rfl->token->getMethods($filter) as $method) {
				$this->assertInstanceOf('TokenReflection\ReflectionMethod', $method);
			}
			foreach ($rfl->token->getOwnMethods($filter) as $method) {
				$this->assertInstanceOf('TokenReflection\ReflectionMethod', $method);
			}
		}

		$methods = array('__construct', '__destruct', 'publicFinalFunction', 'publicStaticFunction', 'protectedStaticFunction', 'privateStaticFunction', 'publicFunction', 'protectedFunction', 'privateFunction');
		foreach ($methods as $method) {
			$this->assertSame($rfl->internal->hasMethod($method), $rfl->token->hasMethod($method));
			$this->assertTrue($rfl->token->hasMethod($method));

			$this->assertInstanceOf('TokenReflection\ReflectionMethod', $rfl->token->getMethod($method));
		}

		$methods = array('__construct', '__destruct', 'publicFinalFunction', 'publicStaticFunction', 'privateStaticFunction', 'publicFunction', 'privateFunction');
		foreach ($methods as $method) {
			$this->assertTrue($rfl->token->hasOwnMethod($method));
		}
		$methods = array('protectedStaticFunction', 'protectedFunction');
		foreach ($methods as $method) {
			$this->assertFalse($rfl->token->hasOwnMethod($method));
		}

		$this->assertInstanceOf('TokenReflection\ReflectionMethod', $rfl->token->getConstructor());
		$this->assertInstanceOf('TokenReflection\ReflectionMethod', $rfl->token->getDestructor());

		$this->assertFalse($rfl->token->hasMethod('nonExistent'));
		try {
			$rfl->token->getMethod('nonExistent');
			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}

		$rfl = $this->getClassReflection('noMethods');

		$this->assertSame($rfl->internal->getMethods(), $rfl->token->getMethods());
		$this->assertSame(array(), $rfl->token->getMethods());
		$this->assertSame(array(), $rfl->token->getOwnMethods());

		try {
			$rfl->token->getMethod('nonExistent');
			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}

		$this->assertSame($rfl->internal->hasMethod('nonExistent'), $rfl->token->hasMethod('nonExistent'));
		$this->assertFalse($rfl->token->hasMethod('nonExistent'));
		$this->assertFalse($rfl->token->hasOwnMethod('nonExistent'));

		$this->assertSame($rfl->internal->getConstructor(), $rfl->token->getConstructor());
		$this->assertNull($rfl->token->getConstructor());
		$this->assertNull($rfl->token->getDestructor());
	}

	/**
	 * Tests getting of start and end line.
	 */
	public function testLines()
	{
		$rfl = $this->getClassReflection('lines');
		$this->assertSame($rfl->internal->getStartLine(), $rfl->token->getStartLine());
		$this->assertSame(3, $rfl->token->getStartLine());
		$this->assertSame($rfl->internal->getEndLine(), $rfl->token->getEndLine());
		$this->assertSame(5, $rfl->token->getEndLine());
	}

	/**
	 * Tests if class is instance of a object and tests creating new instances.
	 */
	public function testInstances()
	{
		$rfl = $this->getClassReflection('instances');

		$this->assertSame($rfl->internal->isInstance(new \TokenReflection_Test_ClassInstances(1)), $rfl->token->isInstance(new \TokenReflection_Test_ClassInstances(1)));
		$this->assertTrue($rfl->token->isInstance(new \TokenReflection_Test_ClassInstances(1)));
		$this->assertSame($rfl->internal->isInstance(new \TokenReflection_Test_ClassInstancesChild(1)), $rfl->token->isInstance(new \TokenReflection_Test_ClassInstancesChild(1)));
		$this->assertTrue($rfl->token->isInstance(new \TokenReflection_Test_ClassInstancesChild(1)));
		$this->assertSame($rfl->internal->isInstance(new \Exception()), $rfl->token->isInstance(new \Exception()));
		$this->assertFalse($rfl->token->isInstance(new \Exception()));

		$this->assertEquals($rfl->internal->newInstance(1), $rfl->token->newInstance(1));
		$this->assertInstanceOf($this->getClassName('instances'), $rfl->token->newInstance(1));
		$this->assertEquals($rfl->internal->newInstanceArgs(array(1)), $rfl->token->newInstanceArgs(array(1)));
		$this->assertInstanceOf($this->getClassName('instances'), $rfl->token->newInstanceArgs(array(1)));
	}

	/**
	 * Tests if class is abstract.
	 */
	public function testAbstract()
	{
		$rfl = $this->getClassReflection('abstract');
		$this->assertSame($rfl->internal->isAbstract(), $rfl->token->isAbstract());
		$this->assertTrue($rfl->token->isAbstract());
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertFalse($rfl->token->isInstantiable());
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertSame(InternalReflectionClass::IS_EXPLICIT_ABSTRACT, $rfl->token->getModifiers());

		$rfl = $this->getClassReflection('abstractImplicit');
		$this->assertSame($rfl->internal->isAbstract(), $rfl->token->isAbstract());
		$this->assertTrue($rfl->token->isAbstract());
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertFalse($rfl->token->isInstantiable());
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertSame(InternalReflectionClass::IS_IMPLICIT_ABSTRACT | InternalReflectionClass::IS_EXPLICIT_ABSTRACT, $rfl->token->getModifiers());

		$rfl = $this->getClassReflection('noAbstract');
		$this->assertSame($rfl->internal->isAbstract(), $rfl->token->isAbstract());
		$this->assertFalse($rfl->token->isAbstract());
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertTrue($rfl->token->isInstantiable());
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertSame(0, $rfl->token->getModifiers());
	}

	/**
	 * Tests if class is final.
	 */
	public function testFinal()
	{
		$rfl = $this->getClassReflection('final');
		$this->assertSame($rfl->internal->isFinal(), $rfl->token->isFinal());
		$this->assertTrue($rfl->token->isFinal());
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertSame(InternalReflectionClass::IS_FINAL, $rfl->token->getModifiers());

		$rfl = $this->getClassReflection('noFinal');
		$this->assertSame($rfl->internal->isFinal(), $rfl->token->isFinal());
		$this->assertFalse($rfl->token->isFinal());
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertSame(0, $rfl->token->getModifiers());
	}

	/**
	 * Tests if class is an interface.
	 */
	public function testInterface()
	{
		$rfl = $this->getClassReflection('interface');
		$this->assertSame($rfl->internal->isInterface(), $rfl->token->isInterface());
		$this->assertTrue($rfl->token->isInterface());
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertFalse($rfl->token->isInstantiable());

		$rfl = $this->getClassReflection('noInterface');
		$this->assertSame($rfl->internal->isInterface(), $rfl->token->isInterface());
		$this->assertFalse($rfl->token->isInterface());
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertTrue($rfl->token->isInstantiable());
	}

	/**
	 * Tests if class implements interfaces.
	 */
	public function testInterfaces()
	{
		$rfl = $this->getClassReflection('interfaces');

		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertSame($rfl->internal->getInterfaceNames(), $rfl->token->getInterfaceNames());
		$this->assertSame(array('Traversable', 'Iterator', 'Countable', 'ArrayAccess', 'Serializable'), $rfl->token->getInterfaceNames());
		$this->assertSame(array('Countable', 'ArrayAccess', 'Serializable'), $rfl->token->getOwnInterfaceNames());
		$this->assertSame(array_keys($rfl->internal->getInterfaces()), array_keys($rfl->token->getInterfaces()));
		$this->assertSame(array('Traversable', 'Iterator', 'Countable', 'ArrayAccess', 'Serializable'), array_keys($rfl->token->getInterfaces()));
		$this->assertSame(array('Countable', 'ArrayAccess', 'Serializable'), array_keys($rfl->token->getOwnInterfaces()));
		foreach ($rfl->token->getInterfaces() as $interface) {
			$this->assertInstanceOf('TokenReflection\Php\ReflectionClass', $interface);
		}
		foreach ($rfl->token->getOwnInterfaces() as $interface) {
			$this->assertInstanceOf('TokenReflection\Php\ReflectionClass', $interface);
		}
		$this->assertSame($rfl->internal->implementsInterface('Countable'), $rfl->token->implementsInterface('Countable'));
		$this->assertTrue($rfl->token->implementsInterface('Countable'));
		$this->assertTrue($rfl->token->implementsInterface(new InternalReflectionClass('Countable')));

		$token = $this->getBroker()->getClass('Iterator');
		$this->assertSame(array('Traversable'), array_keys($token->getInterfaces()));
		$this->assertSame(array('Traversable'), $token->getInterfaceNames());
		$this->assertSame(array('Traversable'), array_keys($token->getOwnInterfaces()));
		$this->assertSame(array('Traversable'), $token->getOwnInterfaceNames());

		$rfl = $this->getClassReflection('noInterfaces');
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertSame($rfl->internal->getInterfaceNames(), $rfl->token->getInterfaceNames());
		$this->assertSame(array(), $rfl->token->getOwnInterfaceNames());
		$this->assertSame(array(), $rfl->token->getInterfaceNames());
		$this->assertSame($rfl->internal->getInterfaces(), $rfl->token->getInterfaces());
		$this->assertSame(array(), $rfl->token->getInterfaces());
		$this->assertSame(array(), $rfl->token->getOwnInterfaces());
		$this->assertSame($rfl->internal->implementsInterface('Countable'), $rfl->token->implementsInterface('Countable'));
		$this->assertFalse($rfl->token->implementsInterface('Countable'));
		$this->assertFalse($rfl->token->implementsInterface(new InternalReflectionClass('Countable')));
	}

	/**
	 * Tests if class is iterator.
	 */
	public function testIterator()
	{
		$rfl = $this->getClassReflection('iterator');
		$this->assertSame($rfl->internal->isIterateable(), $rfl->token->isIterateable());
		$this->assertTrue($rfl->token->isIterateable());

		$rfl = $this->getClassReflection('noIterator');
		$this->assertSame($rfl->internal->isIterateable(), $rfl->token->isIterateable());
		$this->assertFalse($rfl->token->isIterateable());
	}

	/**
	 * Tests if class has parent.
	 */
	public function testParent()
	{
		$rfl = $this->getClassReflection('parent');
		foreach (array('TokenReflection_Test_ClassGrandGrandParent', 'TokenReflection_Test_ClassGrandParent') as $parent) {
			$this->assertSame($rfl->internal->isSubclassOf($parent), $rfl->token->isSubclassOf($parent));
			$this->assertTrue($rfl->token->isSubclassOf($parent));
			$this->assertTrue($rfl->token->isSubclassOf($this->getBroker()->getClass($parent)));
		}
		foreach (array('TokenReflection_Test_ClassParent', 'Exception', 'DateTime') as $parent) {
			$this->assertSame($rfl->internal->isSubclassOf($parent), $rfl->token->isSubclassOf($parent));
			$this->assertFalse($rfl->token->isSubclassOf($parent));
		}
		$this->assertInstanceOf('TokenReflection\ReflectionClass', $rfl->token->getParentClass());
		$this->assertSame('TokenReflection_Test_ClassGrandParent', $rfl->token->getParentClassName());

		$this->assertSame(3, count($rfl->token->getParentClasses()));
		foreach ($rfl->token->getParentClasses() as $class) {
			$this->assertInstanceOf('TokenReflection\IReflectionClass', $class);
		}
		$this->assertSame(array('TokenReflection_Test_ClassGrandParent', 'TokenReflection_Test_ClassGrandGrandParent', 'ReflectionClass'), $rfl->token->getParentClassNameList());

		$rfl = $this->getClassReflection('noParent');
		$this->assertSame($rfl->internal->isSubclassOf('Exception'), $rfl->token->isSubclassOf('Exception'));
		$this->assertFalse($rfl->token->isSubclassOf('Exception'));
		$this->assertFalse($rfl->token->isSubclassOf(new InternalReflectionClass('Exception')));

		$this->assertSame($rfl->internal->getParentClass(), $rfl->token->getParentClass());
		$this->assertFalse($rfl->token->getParentClass());
		$this->assertSame(array(), $rfl->token->getParentClasses());
		$this->assertSame(array(), $rfl->token->getParentClassNameList());
	}

	/**
	 * Tests if class is user defined or internal.
	 */
	public function testUserDefined()
	{
		$rfl = $this->getClassReflection('userDefined');

		$this->assertSame($rfl->internal->isUserDefined(), $rfl->token->isUserDefined());
		$this->assertTrue($rfl->token->isUserDefined());
		$this->assertSame($rfl->internal->getFileName(), $rfl->token->getFileName());
		$this->assertSame($this->getFilePath('userDefined'), $rfl->token->getFileName());
		$this->assertSame($rfl->internal->isInternal(), $rfl->token->isInternal());
		$this->assertFalse($rfl->token->isInternal());

		$this->assertSame($rfl->internal->getExtension(), $rfl->token->getExtension());
		$this->assertNull($rfl->token->getExtension());
		$this->assertSame($rfl->internal->getExtensionName(), $rfl->token->getExtensionName());
		$this->assertFalse($rfl->token->getExtensionName());

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionClass('Exception');
		$rfl->token = $this->getBroker()->getClass('Exception');

		$this->assertSame($rfl->internal->isUserDefined(), $rfl->token->isUserDefined());
		$this->assertFalse($rfl->token->isUserDefined());
		$this->assertSame($rfl->internal->getFileName(), $rfl->token->getFileName());
		$this->assertFalse($rfl->token->getFileName());
		$this->assertSame($rfl->internal->isInternal(), $rfl->token->isInternal());
		$this->assertTrue($rfl->token->isInternal());

		$this->assertInstanceOf('TokenReflection\Php\ReflectionExtension', $rfl->token->getExtension());
		$this->assertSame($rfl->internal->getExtensionName(), $rfl->token->getExtensionName());
		$this->assertSame('Core', $rfl->token->getExtensionName());
	}

	/**
	 * Tests getting of documentation comment.
	 */
	public function testDocComment()
	{
		$rfl = $this->getClassReflection('docComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame("/**\n * TokenReflection_Test_ClassDocComment.\n *\n * @copyright Copyright (c) 2011\n * @author author\n * @see http://php.net\n */", $rfl->token->getDocComment());

		$rfl = $this->getClassReflection('noDocComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertFalse($rfl->token->getDocComment());
	}

	/**
	 * Test getting of documentation comment, when after docComment many line breaks.
	 */
	public function testDocCommentManyLines()
	{
		$rfl = $this->getClassReflection('docCommentManyLines');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame("/**\n * TokenReflection_Test_ClassDocCommentManyLines.\n *\n * @copyright Copyright (c) 2011\n * @author author\n * @see http://php.net\n */", $rfl->token->getDocComment());

		$rfl = $this->getClassReflection('noDocComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertFalse($rfl->token->getDocComment());
	}

	/**
	 * Tests getting of inherited documentation comment.
	 */
	public function testDocCommentInheritance()
	{
		require_once $this->getFilePath('docCommentInheritance');
		$this->getBroker()->processFile($this->getFilePath('docCommentInheritance'));

		$parent = new \stdClass();
		$parent->internal = new InternalReflectionClass('TokenReflection_Test_ClassDocCommentInheritanceParent');
		$parent->token = $this->getBroker()->getClass('TokenReflection_Test_ClassDocCommentInheritanceParent');
		$this->assertSame($parent->internal->getDocComment(), $parent->token->getDocComment());

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionClass('TokenReflection_Test_ClassDocCommentInheritanceExplicit');
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_ClassDocCommentInheritanceExplicit');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame('My Short description.', $rfl->token->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));
		$this->assertSame('Long description. Phew, that was long.', $rfl->token->getAnnotation(ReflectionAnnotation::LONG_DESCRIPTION));

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionClass('TokenReflection_Test_ClassDocCommentInheritanceImplicit');
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_ClassDocCommentInheritanceImplicit');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame($parent->token->getAnnotations(), $rfl->token->getAnnotations());
	}

	/**
	 * Tests getting of copydoc documentation comment.
	 */
	public function testDocCommentCopydoc()
	{
		require_once $this->getFilePath('docCommentCopydoc');
		$this->getBroker()->processFile($this->getFilePath('docCommentCopydoc'));

		$parent = new \stdClass();
		$parent->internal = new InternalReflectionClass('TokenReflection_Test_ClassDocCommentCopydocParent');
		$parent->token = $this->getBroker()->getClass('TokenReflection_Test_ClassDocCommentCopydocParent');
		$this->assertSame($parent->internal->getDocComment(), $parent->token->getDocComment());

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionClass('TokenReflection_Test_ClassDocCommentCopydocFound');
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_ClassDocCommentCopydocFound');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame('Short description.', $rfl->token->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));
		$this->assertSame('Long description.', $rfl->token->getAnnotation(ReflectionAnnotation::LONG_DESCRIPTION));

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionClass('TokenReflection_Test_ClassDocCommentCopydocOverwritten');
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_ClassDocCommentCopydocOverwritten');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame('Whatever.', $rfl->token->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));
		$this->assertSame('Long description.', $rfl->token->getAnnotation(ReflectionAnnotation::LONG_DESCRIPTION));
		$this->assertSame(array('None'), $rfl->token->getAnnotation('license'));
		$this->assertSame(array('Another author'), $rfl->token->getAnnotation('author'));

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionClass('TokenReflection_Test_ClassDocCommentCopydocDouble');
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_ClassDocCommentCopydocDouble');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame('Short description.', $rfl->token->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));
		$this->assertSame('Long description.', $rfl->token->getAnnotation(ReflectionAnnotation::LONG_DESCRIPTION));
		$this->assertSame(array('None'), $rfl->token->getAnnotation('license'));
		$this->assertSame(array('Author'), $rfl->token->getAnnotation('author'));

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionClass('TokenReflection_Test_ClassDocCommentCopydocRecursive');
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_ClassDocCommentCopydocRecursive');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame('Short description.', $rfl->token->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));
		$this->assertSame('Long description.', $rfl->token->getAnnotation(ReflectionAnnotation::LONG_DESCRIPTION));
		$this->assertSame(array('None'), $rfl->token->getAnnotation('license'));
		$this->assertSame(array('Author'), $rfl->token->getAnnotation('author'));

		static $emptys = array(
			'TokenReflection_Test_ClassDocCommentCopydocNotFound',
			'TokenReflection_Test_ClassDocCommentCopydocCircle11',
			'TokenReflection_Test_ClassDocCommentCopydocCircle12',
			'TokenReflection_Test_ClassDocCommentCopydocCircle21',
			'TokenReflection_Test_ClassDocCommentCopydocCircle22',
			'TokenReflection_Test_ClassDocCommentCopydocCircle23',
			'TokenReflection_Test_ClassDocCommentCopydocCircleSelf'
		);
		foreach ($emptys as $empty) {
			$rfl = new \stdClass();
			$rfl->internal = new InternalReflectionClass($empty);
			$rfl->token = $this->getBroker()->getClass($empty);
			$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
			$this->assertSame(array(), $rfl->token->getAnnotations());
		}
	}

	/**
	 * Tests if class is defined in namespace.
	 */
	public function testInNamespace()
	{
		require_once $this->getFilePath('inNamespace');
		$this->getBroker()->processFile($this->getFilePath('inNamespace'));

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionClass('TokenReflection\Test\ClassInNamespace');
		$rfl->token = $this->getBroker()->getClass('TokenReflection\Test\ClassInNamespace');

		$this->assertSame($rfl->internal->inNamespace(), $rfl->token->inNamespace());
		$this->assertTrue($rfl->token->inNamespace());
		$this->assertSame($rfl->internal->getNamespaceName(), $rfl->token->getNamespaceName());
		$this->assertSame('TokenReflection\Test', $rfl->token->getNamespaceName());
		$this->assertSame($rfl->internal->getName(), $rfl->token->getName());
		$this->assertSame('TokenReflection\Test\ClassInNamespace', $rfl->token->getName());
		$this->assertSame($rfl->internal->getShortName(), $rfl->token->getShortName());
		$this->assertSame('ClassInNamespace', $rfl->token->getShortName());

		$rfl = $this->getClassReflection('noNamespace');
		$this->assertSame($rfl->internal->inNamespace(), $rfl->token->inNamespace());
		$this->assertFalse($rfl->token->inNamespace());
		$this->assertSame($rfl->internal->getNamespaceName(), $rfl->token->getNamespaceName());
		$this->assertSame('', $rfl->token->getNamespaceName());
		$this->assertSame($rfl->internal->getName(), $rfl->token->getName());
		$this->assertSame($this->getClassName('noNamespace'), $rfl->token->getName());
		$this->assertSame($rfl->internal->getShortName(), $rfl->token->getShortName());
		$this->assertSame($this->getClassName('noNamespace'), $rfl->token->getShortName());
	}

	/**
	 * Tests getting of property source code.
	 */
	public function testPropertyGetSource()
	{
		static $expected = array(
			'publicStatic' => 'public static $publicStatic = true;',
			'privateStatic' => 'private static $privateStatic = \'something\';',
			'protectedStatic' => 'protected static $protectedStatic = 1;',
			'public' => 'public $public = false;',
			'protected' => 'protected $protected = 0;',
			'private' => 'private $private = \'\';'
		);

		$rfl = $this->getClassReflection('properties')->token;
		foreach ($expected as $propertyName => $source) {
			$this->assertSame($source, $rfl->getProperty($propertyName)->getSource());
		}
	}

	/**
	 * Tests getting of method source code.
	 */
	public function testMethodGetSource()
	{
		static $expected = array(
			'protectedStaticFunction' => "protected static function protectedStaticFunction(\$one = true)\n	{\n	}",
			'protectedFunction' => "protected function protectedFunction(\$two = false)\n	{\n	}",
			'publicStaticFunction' => "public static function publicStaticFunction(\$five = 1.1)\n	{\n	}"
		);

		$rfl = $this->getClassReflection('methods')->token;
		foreach ($expected as $methodName => $source) {
			$this->assertSame($source, $rfl->getMethod($methodName)->getSource());
		}
	}

	/**
	 * Tests getting of constant source code.
	 */
	public function testConstantGetSource()
	{
		static $expected = array(
			'PARENT' => 'PARENT = \'parent\';',
			'STRING' => 'STRING = \'string\';',
			'FLOAT' => 'FLOAT = 1.1;',
			'BOOLEAN' => 'BOOLEAN = true;'
		);

		$rfl = $this->getClassReflection('constants')->token;
		foreach ($expected as $constantName => $source) {
			$this->assertSame($source, $rfl->getConstantReflection($constantName)->getSource());
		}
	}

	/**
	 * Tests getting of class source code.
	 */
	public function testClassGetSource()
	{
		static $expected = array(
			'methods' => "class TokenReflection_Test_ClassMethods extends TokenReflection_Test_ClassMethodsParent\n{\n	public function __construct(\$three)\n	{\n	}\n\n	public function __destruct()\n	{\n	}\n\n	public final function publicFinalFunction(\$four = 1)\n	{\n	}\n\n	public static function publicStaticFunction(\$five = 1.1)\n	{\n	}\n\n	private static function privateStaticFunction(\$six = 'string', \$seven = null)\n	{\n	}\n\n	public function publicFunction(array \$eight = array())\n	{\n	}\n\n	private function privateFunction(Foo \$nine = null)\n	{\n	}\n}",
			'constants' => "class TokenReflection_Test_ClassConstants extends TokenReflection_Test_ClassConstantsParent\n{\n	const STRING = 'string';\n	const INTEGER = 1;\n	const FLOAT = 1.1;\n	const BOOLEAN = true;\n}",
			'docComment' => "/**\n * TokenReflection_Test_ClassDocComment.\n *\n * @copyright Copyright (c) 2011\n * @author author\n * @see http://php.net\n */\nclass TokenReflection_Test_ClassDocComment\n{\n}"
		);

		foreach ($expected as $className => $source) {
			$this->assertSame(
				$source,
				$this->getClassReflection($className)->token->getSource()
			);
		}
	}

	/**
	 * Tests export.
	 */
	public function testToString()
	{
		$tests = array(
			'lines', 'docComment', 'noDocComment',
			'constants', 'noConstants', 'properties', 'noProperties', 'doubleProperties',
			'publicConstructor', 'privateConstructor', 'publicClone', 'privateClone',
			'methods', 'noMethods', 'instances', 'abstract', 'abstractImplicit', 'noAbstract', 'final', 'noFinal',
			'interface', 'noInterface', 'interfaces', 'noInterfaces',
			'iterator', 'noIterator', 'parent', 'noParent',
			'userDefined', 'noNamespace',
		);
		if (PHP_VERSION_ID >= 50400) {
			// Test traits only on PHP >= 5.4
			$tests[] = 'traits';
		}

		foreach ($tests as $test) {
			$rfl = $this->getClassReflection($test);
			$this->assertSame($rfl->internal->__toString(), $rfl->token->__toString());
			$this->assertSame(InternalReflectionClass::export($this->getClassName($test), true), ReflectionClass::export($this->getBroker(), $this->getClassName($test), true));

			// Test loading from a string
			$rfl = $this->getClassReflection($test, true);
			$this->assertSame($rfl->internal->__toString(), $rfl->token->__toString());
		}

		$this->assertSame(InternalReflectionClass::export('ReflectionClass', true), ReflectionClass::export($this->getBroker(), 'ReflectionClass', true));
		$this->assertSame(InternalReflectionClass::export(new InternalReflectionClass('ReflectionClass'), true), ReflectionClass::export($this->getBroker(), new InternalReflectionClass('ReflectionClass'), true));
	}

	/**
	 * Tests traits support comparing with the internal reflection.
	 *
	 * For PHP 5.4+ only.
	 */
	public function testTraits()
	{
		if (PHP_VERSION_ID < 50400) {
			$this->markTestSkipped('Requires PHP 5.4 or higher.');
		}

		static $classes = array(
			'TokenReflection_Test_ClassTraitsTrait1',
			'TokenReflection_Test_ClassTraitsTrait2',
			'TokenReflection_Test_ClassTraitsTrait3',
			'TokenReflection_Test_ClassTraitsTrait4',
			'TokenReflection_Test_ClassTraits',
			'TokenReflection_Test_ClassTraits2',
			'TokenReflection_Test_ClassTraits3',
			'TokenReflection_Test_ClassTraits4'
		);

		require_once $this->getFilePath('traits');
		$this->getBroker()->process($this->getFilePath('traits'));

		foreach ($classes as $className) {
			$token = $this->getBroker()->getClass($className);
			$internal = new \ReflectionClass($className);

			$this->assertSame($internal->isTrait(), $token->isTrait(), $className);
			$this->assertSame($internal->getTraitAliases(), $token->getTraitAliases(), $className);
			$this->assertSame($internal->getTraitNames(), $token->getTraitNames(), $className);
			$this->assertSame(count($internal->getTraits()), count($token->getTraits()), $className);
			foreach ($internal->getTraits() as $trait) {
				$this->assertTrue($token->usesTrait($trait->getName()), $className);
			}
		}
	}

	/**
	 * Tests traits support comparing with expected values.
	 */
	public function testTraits2()
	{
		static $expected = array(
			'TokenReflection_Test_ClassTraitsTrait1' => array(true, array(), array(), array(), 0, 0),
			'TokenReflection_Test_ClassTraitsTrait2' => array(true, array('t2privatef' => '(null)::privatef'), array('TokenReflection_Test_ClassTraitsTrait1'), array('TokenReflection_Test_ClassTraitsTrait1'), 6, 3),
			'TokenReflection_Test_ClassTraitsTrait3' => array(true, array(), array(), array(), 0, 0),
			'TokenReflection_Test_ClassTraitsTrait4' => array(true, array(), array(), array(), 0, 0),
			'TokenReflection_Test_ClassTraits' => array(false, array('privatef2' => '(null)::publicf', 'publicf3' => '(null)::protectedf', 'publicfOriginal' => '(null)::publicf'), array('TokenReflection_Test_ClassTraitsTrait1'), array('TokenReflection_Test_ClassTraitsTrait1'), 6, 6),
			'TokenReflection_Test_ClassTraits2' => array(false, array(), array('TokenReflection_Test_ClassTraitsTrait2'), array('TokenReflection_Test_ClassTraitsTrait2'), 6, 3),
			'TokenReflection_Test_ClassTraits3' => array(false, array(), array('TokenReflection_Test_ClassTraitsTrait1'), array('TokenReflection_Test_ClassTraitsTrait1'), 6, 2),
			'TokenReflection_Test_ClassTraits4' => array(false, array(), array('TokenReflection_Test_ClassTraitsTrait3', 'TokenReflection_Test_ClassTraitsTrait4'), array('TokenReflection_Test_ClassTraitsTrait3', 'TokenReflection_Test_ClassTraitsTrait4'), 2, 1)
		);

		$this->getBroker()->process($this->getFilePath('traits'));
		foreach ($expected as $className => $definition) {
			$reflection = $this->getBroker()->getClass($className);

			$this->assertSame($definition[0], $reflection->isTrait(), $className);
			$this->assertSame($definition[1], $reflection->getTraitAliases(), $className);
			$this->assertSame($definition[2], $reflection->getTraitNames(), $className);
			$this->assertSame(count($definition[2]), count($reflection->getTraits()), $className);
			foreach ($definition[2] as $traitName) {
				$this->assertTrue($reflection->usesTrait($traitName), $className);
			}

			$this->assertSame($definition[3], $reflection->getOwnTraitNames(), $className);
			$this->assertSame(count($definition[3]), count($reflection->getOwnTraits()), $className);
			foreach ($definition[3] as $traitName) {
				$this->assertTrue($reflection->usesTrait($traitName), $className);
			}

			foreach ($reflection->getTraitProperties() as $property) {
				$this->assertTrue($reflection->hasProperty($property->getName()), $className);
				$this->assertNotNull($property->getDeclaringTraitName(), $className);
			}
			$this->assertSame($definition[4], count($reflection->getTraitProperties()), $className);

			foreach ($reflection->getTraitMethods() as $method) {
				$this->assertTrue($reflection->hasMethod($method->getName()), $className);
				$this->assertNotNull($method->getDeclaringTraitName(), $className);
			}
			$this->assertSame($definition[5], count($reflection->getTraitMethods()), $className);
		}
	}

	/**
	 * Tests creating class instances without calling the constructor.
	 */
	public function testNewInstanceWithoutConstructor()
	{
		require_once $this->getFilePath('newInstanceWithoutConstructor');
		$this->getBroker()->process($this->getFilePath('newInstanceWithoutConstructor'));

		$token = $this->getBroker()->getClass('TokenReflection_Test_NewInstanceWithoutConstructor1');
		$this->assertInstanceOf('TokenReflection\ReflectionClass', $token);

		try {
			$token->newInstanceWithoutConstructor();
			$this->fail('TokenReflection\Exception\RuntimeException expected.');
		} catch (\Exception $e) {
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);

			if ($e->getCode() !== Exception\RuntimeException::UNSUPPORTED) {
				throw $e;
			}
		}

		if (PHP_VERSION_ID >= 50400) {
			// Try the internal reflection
			$internal = new \ReflectionClass('TokenReflection_Test_NewInstanceWithoutConstructor1');
			try {
				$internal->newInstanceWithoutConstructor();
				$this->fail('ReflectionException expected.');
			} catch (\Exception $e) {
				$this->assertInstanceOf('ReflectionException', $e);
			}
		}

		$token = $this->getBroker()->getClass('Exception');
		$this->assertInstanceOf('TokenReflection\Php\ReflectionClass', $token);

		try {
			$token->newInstanceWithoutConstructor();
			$this->fail('TokenReflection\Exception\RuntimeException expected.');
		} catch (\Exception $e) {
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);

			if ($e->getCode() !== Exception\RuntimeException::UNSUPPORTED) {
				throw $e;
			}
		}

		if (PHP_VERSION_ID >= 50400) {
			// Try the internal reflection
			$internal = new \ReflectionClass('Exception');
			try {
				$internal->newInstanceWithoutConstructor();
				$this->fail('ReflectionException expected.');
			} catch (\Exception $e) {
				$this->assertInstanceOf('ReflectionException', $e);
			}
		}

		$token = $this->getBroker()->getClass('TokenReflection_Test_NewInstanceWithoutConstructor2');
		$internal = new \ReflectionClass('TokenReflection_Test_NewInstanceWithoutConstructor2');
		$this->assertInstanceOf('TokenReflection\ReflectionClass', $token);

		$instance = $token->newInstanceWithoutConstructor();
		$this->assertFalse($instance->check);

		$instance2 = $token->newInstanceArgs();
		$this->assertTrue($instance2->check);

		if (PHP_VERSION_ID >= 50400) {
			// Try the internal reflection
			$this->assertEquals($internal->newInstanceWithoutConstructor(), $token->newInstanceWithoutConstructor());
		}
	}

	/**
	 * Tests returning pretty class names.
	 */
	public function testPrettyNames()
	{
		static $names = array(
			'ns1\\TokenReflection_Test_ClassPrettyNames',
			'ns2\\ns3\\ns4\\TokenReflection_Test_ClassPrettyNames2',
			'TokenReflection_Test_ClassPrettyNames3'
		);

		$broker = $this->getBroker();
		$broker->processFile($this->getFilePath('pretty-names'));

		foreach ($names as $name) {
			$this->assertTrue($broker->hasClass($name), $name);

			$rfl = $broker->getClass($name);
			$this->assertSame($rfl->getName(), $rfl->getPrettyName(), $name);
		}
	}

	/**
	 * Returns an internal class reflection.
	 *
	 * @return \TokenReflection\Php\ReflectionClass
	 */
	private function getInternalClassReflection()
	{
		return $this->getBroker()->getClass('Exception');
	}

	/**
	 * Returns a non existent class reflection.
	 *
	 * @return \TokenReflection\Dummy\ReflectionClass
	 */
	private function getDummyClassReflection()
	{
		static $className = 'foo_bar';

		if (class_exists($className, false)) {
			$this->markTestSkipped(sprintf('Class %s exists.', $className));
		}

		return $this->getBroker()->getClass($className);
	}
}
