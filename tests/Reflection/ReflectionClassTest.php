<?php

namespace ApiGen\TokenReflection\Tests\Reflection;

use ApiGen;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Parser\AnnotationParser;
use ApiGen\TokenReflection\Php\ReflectionClass;
use ApiGen\TokenReflection\Reflection\ReflectionAnnotation;
use ApiGen\TokenReflection\Tests\TestCase;
use ReflectionClass as InternalReflectionClass;
use TokenReflection_Test_ClassInstances;


class ReflectionClassTest extends TestCase
{

	/**
	 * @var string
	 */
	protected $type = 'class';


	/**
	 * Tests the dummy class reflection interface.
	 */
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

		require_once $this->getFilePath('dummy');

		foreach ($reflections as $className => $reflection) {
			$instance = $reflection->newInstance(NULL);
			$this->assertTrue($reflection->isInstance($instance));
			$this->assertTrue($instance->wasConstrustorCalled());

			$instance = $reflection->newInstanceArgs([]);
			$this->assertTrue($reflection->isInstance($instance));
			$this->assertTrue($instance->wasConstrustorCalled());

			$instance = $reflection->newInstanceWithoutConstructor();
			$this->assertTrue($reflection->isInstance($instance));
			$this->assertFalse($instance->wasConstrustorCalled());
		}
	}


	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException RuntimeException
	 */
	public function testDummyClassImplementsInterface1()
	{
		$this->getDummyClassReflection()->implementsInterface(new \Exception());
	}


	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException RuntimeException
	 */
	public function testDummyClassImplementsInterface2()
	{
		$this->getDummyClassReflection()->implementsInterface($this->getBroker()->getClass('Exception'));
	}


	/**
	 * Tests an exception thrown when getting a method from a dummy class reflection.
	 *
	 * @expectedException RuntimeException
	 */
	public function testDummyClassGetMethod()
	{
		$this->getDummyClassReflection()->getMethod('any');
	}


	/**
	 * Tests an exception thrown when getting a property from a dummy class reflection.
	 *
	 * @expectedException RuntimeException
	 */
	public function testDummyClassGetProperty()
	{
		$this->getDummyClassReflection()->getProperty('any');
	}


	/**
	 * Tests an exception thrown when getting a static property from a dummy class reflection.
	 *
	 * @expectedException RuntimeException
	 */
	public function testDummyClassGetStaticProperty()
	{
		$this->getDummyClassReflection()->getStaticPropertyValue('any', NULL);
	}


	/**
	 * Tests an exception thrown when setting a static property from a dummy class reflection.
	 *
	 * @expectedException RuntimeException
	 */
	public function testDummyClassSetStaticProperty()
	{
		$this->getDummyClassReflection()->setStaticPropertyValue('foo', 'bar');
	}


	/**
	 * Tests an exception thrown when getting a constant value from a dummy class reflection.
	 *
	 * @expectedException RuntimeException
	 */
	public function testDummyClassGetConstantValue()
	{
		$this->getDummyClassReflection()->getConstant('any');
	}


	/**
	 * Tests an exception thrown when getting a constant reflection from a dummy class reflection.
	 *
	 * @expectedException RuntimeException
	 */
	public function testDummyClassGetConstantReflection()
	{
		$this->getDummyClassReflection()->getConstantReflection('any');
	}


	/**
	 * Tests an exception thrown when providing an invalid argument to isInstance() method.
	 *
	 * @expectedException RuntimeException
	 */
	public function testDummyClassIsInstance()
	{
		$this->getDummyClassReflection()->isInstance(TRUE);
	}


	/**
	 * Tests an exception thrown when trying to instantiate a non existent class.
	 *
	 * @expectedException RuntimeException
	 */
	public function testDummyNewInstanceWithoutConstructor()
	{
		$this->getDummyClassReflection()->newInstanceWithoutConstructor();
	}


	/**
	 * Tests an exception thrown when trying to instantiate a non existent class.
	 *
	 * @expectedException RuntimeException
	 */
	public function testDummyNewInstance()
	{
		$this->getDummyClassReflection()->newInstance(NULL);
	}


	/**
	 * Tests an exception thrown when trying to instantiate a non existent class.
	 *
	 * @expectedException RuntimeException
	 */
	public function testDummyNewInstanceArgs()
	{
		$this->getDummyClassReflection()->newInstanceArgs();
	}


	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassIsSubclassOf()
	{
		$this->getInternalClassReflection()->isSubclassOf(new \Exception());
	}


	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassImplementsInterface1()
	{
		$this->getInternalClassReflection()->implementsInterface(new \Exception());
	}


	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassImplementsInterface2()
	{
		$this->getInternalClassReflection()->implementsInterface($this->getBroker()->getClass('Exception'));
	}


	/**
	 * Tests an exception thrown when providing an invalid class name.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassImplementsInterface3()
	{
		$this->getInternalClassReflection()->implementsInterface('Exception');
	}


	/**
	 * Tests an exception thrown when getting a method from an internal class reflection.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassGetMethod()
	{
		$this->getDummyClassReflection()->getMethod('~non-existent~');
	}


	/**
	 * Tests an exception thrown when getting a property from an internal class reflection.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassGetProperty()
	{
		$this->getDummyClassReflection()->getProperty('~non-existent~');
	}


	/**
	 * Tests an exception thrown when getting a static property from an internal class reflection.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassGetStaticProperty()
	{
		$this->getDummyClassReflection()->getStaticPropertyValue('~non-existent~', NULL);
	}


	/**
	 * Tests an exception thrown when setting a static property from an internal class reflection.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassSetStaticProperty()
	{
		$this->getDummyClassReflection()->setStaticPropertyValue('~non', 'existent~');
	}


	/**
	 * Tests an exception thrown when getting a constant value from an internal class reflection.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassGetConstantValue()
	{
		$this->getDummyClassReflection()->getConstant('~non-existent~');
	}


	/**
	 * Tests an exception thrown when getting a constant reflection from an internal class reflection.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassGetConstantReflection()
	{
		$this->getDummyClassReflection()->getConstantReflection('~non-existent~');
	}


	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassUsesTrait1()
	{
		$this->getInternalClassReflection()->usesTrait(new \Exception());
	}


	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassUsesTrait2()
	{
		$this->getInternalClassReflection()->usesTrait($this->getBroker()->getClass('Exception'));
	}


	/**
	 * Tests an exception thrown when providing an invalid class name.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassUsesTrait3()
	{
		$this->getInternalClassReflection()->usesTrait('Exception');
	}


	/**
	 * Tests an exception thrown when it is impossible to create an instance without invoking the constructor.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassNewInstanceWithoutConstructor1()
	{
		$this->getInternalClassReflection()->newInstanceWithoutConstructor();
	}


	/**
	 * Tests an exception thrown when it is impossible to create an instance without invoking the constructor.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassNewInstanceWithoutConstructor2()
	{
		$reflection = new ReflectionClass('ApiGen\TokenReflection\Exception\RuntimeException', $this->getBroker());
		$reflection->newInstanceWithoutConstructor();
	}


	/**
	 * Tests an exception thrown when trying to create the reflection from a PHP internal reflection.
	 *
	 * @expectedException RuntimeException
	 */
	public function testInternalClassReflectionCreate()
	{
		ReflectionClass::create(new \ReflectionFunction('create_function'), $this->getBroker());
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
		$this->assertSame(['STRING' => 'string', 'int' => 1, 'FLOAT' => 1.1, 'bool' => TRUE, 'PARENT' => 'parent'], $rfl->token->getConstants());
		$this->assertSame(['STRING' => 'string', 'int' => 1, 'FLOAT' => 1.1, 'bool' => TRUE], $rfl->token->getOwnConstants());
		$this->assertSame(range(0, 3), array_keys($rfl->token->getOwnConstantReflections()));
		foreach ($rfl->token->getOwnConstantReflections() as $constant) {
			$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionConstant', $constant);
		}

		$rfl = $this->getClassReflection('noConstants');

		$this->assertSame($rfl->internal->hasConstant('NONEXISTENT'), $rfl->token->hasConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasOwnConstant('NONEXISTENT'));

		$this->assertSame($rfl->internal->getConstant('NONEXISTENT'), $rfl->token->getConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->getConstant('NONEXISTENT'));
		$this->assertSame($rfl->internal->getConstants(), $rfl->token->getConstants());
		$this->assertSame([], $rfl->token->getConstants());
		$this->assertSame([], $rfl->token->getOwnConstants());
		$this->assertSame([], $rfl->token->getOwnConstantReflections());

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

		$filters = [\ReflectionProperty::IS_STATIC, \ReflectionProperty::IS_PUBLIC, \ReflectionProperty::IS_PROTECTED, \ReflectionProperty::IS_PRIVATE];
		foreach ($this->getFilterCombinations($filters) as $filter) {
			$this->assertSame(array_keys($rfl->internal->getProperties($filter)), array_keys($rfl->token->getProperties($filter)));
			foreach ($rfl->token->getProperties($filter) as $property) {
				$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionProperty', $property);
			}
			foreach ($rfl->token->getOwnProperties($filter) as $property) {
				$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionProperty', $property);
			}
		}

		$this->assertSame($rfl->internal->getDefaultProperties(), $rfl->token->getDefaultProperties());
		$this->assertSame(['publicStatic' => TRUE, 'privateStatic' => 'something', 'protectedStatic' => 1, 'public' => FALSE, 'private' => '', 'protected' => 0], $rfl->token->getDefaultProperties());

		$this->assertSame($rfl->internal->getStaticProperties(), $rfl->token->getStaticProperties());
		$this->assertSame(['publicStatic' => TRUE, 'privateStatic' => 'something', 'protectedStatic' => 1], $rfl->token->getStaticProperties());

		$properties = ['public', 'publicStatic', 'protectedStatic', 'protectedStatic', 'private', 'privateStatic'];
		foreach ($properties as $property) {
			$this->assertSame($rfl->internal->hasProperty($property), $rfl->token->hasProperty($property));
			$this->assertTrue($rfl->token->hasProperty($property));

			$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionProperty', $rfl->token->getProperty($property));
		}

		$properties = ['public', 'publicStatic', 'private', 'privateStatic'];
		foreach ($properties as $property) {
			$this->assertTrue($rfl->token->hasOwnProperty($property));
		}
		$properties = ['protectedStatic', 'protectedStatic'];
		foreach ($properties as $property) {
			$this->assertFalse($rfl->token->hasOwnProperty($property));
		}

		$this->assertFalse($rfl->token->hasProperty('nonExistent'));
		try {
			$rfl->token->getProperty('nonExistent');
			$this->fail('Expected exception ApiGen\TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('ApiGen\TokenReflection\Exception\RuntimeException', $e);
		}

		$this->assertSame($rfl->internal->getStaticPropertyValue('publicStatic'), $rfl->token->getStaticPropertyValue('publicStatic'));
		$this->assertTrue($rfl->token->getStaticPropertyValue('publicStatic'));

		try {
			$rfl->token->getStaticPropertyValue('protectedStatic');
			$this->fail('Expected exception ApiGen\TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('ApiGen\TokenReflection\Exception\RuntimeException', $e);
		}

		try {
			$rfl->token->getStaticPropertyValue('privateStatic');
			$this->fail('Expected exception ApiGen\TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('ApiGen\TokenReflection\Exception\RuntimeException', $e);
		}

		$this->assertSame($rfl->internal->setStaticPropertyValue('publicStatic', FALSE), $rfl->token->setStaticPropertyValue('publicStatic', FALSE));
		$this->assertNull($rfl->token->setStaticPropertyValue('publicStatic', FALSE));
		$this->assertSame($rfl->internal->getStaticPropertyValue('publicStatic'), $rfl->token->getStaticPropertyValue('publicStatic'));
		$this->assertFalse($rfl->token->getStaticPropertyValue('publicStatic'));

		try {
			$rfl->token->setStaticPropertyValue('protectedStatic', 0);
			$this->fail('Expected exception ApiGen\TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('ApiGen\TokenReflection\Exception\RuntimeException', $e);
		}

		try {
			$rfl->token->setStaticPropertyValue('privateStatic', '');
			$this->fail('Expected exception ApiGen\TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('ApiGen\TokenReflection\Exception\RuntimeException', $e);
		}

		$rfl = $this->getClassReflection('noProperties');

		$this->assertSame($rfl->internal->getDefaultProperties(), $rfl->token->getDefaultProperties());
		$this->assertSame([], $rfl->token->getDefaultProperties());
		$this->assertSame($rfl->internal->getProperties(), $rfl->token->getProperties());
		$this->assertSame([], $rfl->token->getProperties());
		$this->assertSame([], $rfl->token->getOwnProperties());
		$this->assertSame($rfl->internal->getStaticProperties(), $rfl->token->getStaticProperties());
		$this->assertSame([], $rfl->token->getStaticProperties());

		$this->assertSame($rfl->internal->hasProperty('nonExistent'), $rfl->token->hasProperty('nonExistent'));
		$this->assertFalse($rfl->token->hasProperty('nonExistent'));
		$this->assertFalse($rfl->token->hasOwnProperty('nonExistent'));

		try {
			$rfl->token->getProperty('nonExistent');
			$this->fail('Expected exception ApiGen\TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('ApiGen\TokenReflection\Exception\RuntimeException', $e);
		}

		try {
			$rfl->token->getStaticPropertyValue('nonExistent');
			$this->fail('Expected exception ApiGen\TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('ApiGen\TokenReflection\Exception\RuntimeException', $e);
		}

		try {
			$rfl->token->setStaticPropertyValue('property', 'property');
			$this->fail('Expected exception ApiGen\TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('ApiGen\TokenReflection\Exception\RuntimeException', $e);
		}

		$rfl = $this->getClassReflection('doubleProperties');

		$filters = [\ReflectionProperty::IS_STATIC, \ReflectionProperty::IS_PUBLIC, \ReflectionProperty::IS_PROTECTED, \ReflectionProperty::IS_PRIVATE];
		foreach ($this->getFilterCombinations($filters) as $filter) {
			$this->assertSame(array_keys($rfl->internal->getProperties($filter)), array_keys($rfl->token->getProperties($filter)), $filter);
			foreach ($rfl->token->getProperties($filter) as $property) {
				$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionProperty', $property);
			}
			foreach ($rfl->token->getOwnProperties($filter) as $property) {
				$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionProperty', $property);
			}
		}

		$this->assertSame($rfl->internal->getDefaultProperties(), $rfl->token->getDefaultProperties());
		$this->assertSame(['protectedOne' => 1, 'protectedTwo' => 0, 'publicOne' => TRUE, 'publicTwo' => FALSE, 'privateOne' => 'something', 'privateTwo' => ''], $rfl->token->getDefaultProperties());

		$this->assertSame($rfl->internal->getStaticProperties(), $rfl->token->getStaticProperties());
		$this->assertSame(['protectedOne' => 1, 'protectedTwo' => 0], $rfl->token->getStaticProperties());

		$properties = ['publicOne', 'publicTwo', 'protectedOne', 'protectedTwo', 'privateOne', 'privateTwo'];
		foreach ($properties as $property) {
			$this->assertSame($rfl->internal->hasProperty($property), $rfl->token->hasProperty($property));
			$this->assertTrue($rfl->token->hasProperty($property));

			$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionProperty', $rfl->token->getProperty($property));
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

		$this->assertSame($rfl->internal->isCloneable(), $rfl->token->isCloneable());

		$this->assertTrue($rfl->token->isCloneable());

		$rfl = $this->getClassReflection('privateConstructor');
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertFalse($rfl->token->isInstantiable());

		$this->assertSame($rfl->internal->isCloneable(), $rfl->token->isCloneable());

		$this->assertTrue($rfl->token->isCloneable());

		$rfl = $this->getClassReflection('publicClone');
		$this->assertSame($rfl->internal->isCloneable(), $rfl->token->isCloneable());
		$this->assertTrue($rfl->token->isCloneable());

		$rfl = $this->getClassReflection('privateClone');
		$this->assertSame($rfl->internal->isCloneable(), $rfl->token->isCloneable());
		$this->assertFalse($rfl->token->isCloneable());
	}


	public function testModifiers()
	{
		static $classes = [
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
		];

		require_once $this->getFilePath('modifiers');
		$this->getBroker()->process($this->getFilePath('modifiers'));

		foreach ($classes as $className) {
			$token = $this->getBroker()->getClass($className);
			$internal = new \ReflectionClass($className);
			$this->assertSame($internal->getModifiers(), (xdebug_get_code_coverage() ? 16777216 : 0) + $token->getModifiers(), $className);
		}
	}


	/**
	 * Tests getting of class methods.
	 */
	public function testMethods()
	{
		$rfl = $this->getClassReflection('methods');

		$filters = [\ReflectionMethod::IS_STATIC, \ReflectionMethod::IS_PUBLIC, \ReflectionMethod::IS_PROTECTED, \ReflectionMethod::IS_PRIVATE, \ReflectionMethod::IS_ABSTRACT, \ReflectionMethod::IS_FINAL];
		foreach ($this->getFilterCombinations($filters) as $filter) {
			$this->assertSame(array_keys($rfl->internal->getMethods($filter)), array_keys($rfl->token->getMethods($filter)));
			foreach ($rfl->token->getMethods($filter) as $method) {
				$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionMethod', $method);
			}
			foreach ($rfl->token->getOwnMethods($filter) as $method) {
				$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionMethod', $method);
			}
		}

		$methods = ['__construct', '__destruct', 'publicFinalFunction', 'publicStaticFunction', 'protectedStaticFunction', 'privateStaticFunction', 'publicFunction', 'protectedFunction', 'privateFunction'];
		foreach ($methods as $method) {
			$this->assertSame($rfl->internal->hasMethod($method), $rfl->token->hasMethod($method));
			$this->assertTrue($rfl->token->hasMethod($method));

			$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionMethod', $rfl->token->getMethod($method));
		}

		$methods = ['__construct', '__destruct', 'publicFinalFunction', 'publicStaticFunction', 'privateStaticFunction', 'publicFunction', 'privateFunction'];
		foreach ($methods as $method) {
			$this->assertTrue($rfl->token->hasOwnMethod($method));
		}
		$methods = ['protectedStaticFunction', 'protectedFunction'];
		foreach ($methods as $method) {
			$this->assertFalse($rfl->token->hasOwnMethod($method));
		}

		$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionMethod', $rfl->token->getConstructor());
		$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionMethod', $rfl->token->getDestructor());

		$this->assertFalse($rfl->token->hasMethod('nonExistent'));
		try {
			$rfl->token->getMethod('nonExistent');
			$this->fail('Expected exception ApiGen\TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('ApiGen\TokenReflection\Exception\RuntimeException', $e);
		}

		$rfl = $this->getClassReflection('noMethods');

		$this->assertSame($rfl->internal->getMethods(), $rfl->token->getMethods());
		$this->assertSame([], $rfl->token->getMethods());
		$this->assertSame([], $rfl->token->getOwnMethods());

		try {
			$rfl->token->getMethod('nonExistent');
			$this->fail('Expected exception ApiGen\TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('ApiGen\TokenReflection\Exception\RuntimeException', $e);
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
		$this->assertSame(6, $rfl->token->getEndLine());
	}


	/**
	 * Tests if class is instance of a object and tests creating new instances.
	 */
	public function testInstances()
	{
		$rfl = $this->getClassReflection('instances');

		$this->assertSame($rfl->internal->isInstance(new TokenReflection_Test_ClassInstances(1)), $rfl->token->isInstance(new TokenReflection_Test_ClassInstances(1)));
		$this->assertTrue($rfl->token->isInstance(new TokenReflection_Test_ClassInstances(1)));
		$this->assertSame($rfl->internal->isInstance(new \TokenReflection_Test_ClassInstancesChild(1)), $rfl->token->isInstance(new \TokenReflection_Test_ClassInstancesChild(1)));
		$this->assertTrue($rfl->token->isInstance(new \TokenReflection_Test_ClassInstancesChild(1)));
		$this->assertSame($rfl->internal->isInstance(new \Exception()), $rfl->token->isInstance(new \Exception()));
		$this->assertFalse($rfl->token->isInstance(new \Exception()));

		$this->assertEquals($rfl->internal->newInstance(1), $rfl->token->newInstance(1));
		$this->assertInstanceOf($this->getClassName('instances'), $rfl->token->newInstance(1));
		$this->assertEquals($rfl->internal->newInstanceArgs([1]), $rfl->token->newInstanceArgs([1]));
		$this->assertInstanceOf($this->getClassName('instances'), $rfl->token->newInstanceArgs([1]));
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
		$this->assertSame($rfl->internal->getModifiers(), (xdebug_get_code_coverage() ? 16777216 : 0) + $rfl->token->getModifiers());
		$this->assertSame(InternalReflectionClass::IS_EXPLICIT_ABSTRACT, $rfl->token->getModifiers());

		$rfl = $this->getClassReflection('abstractImplicit');
		$this->assertSame($rfl->internal->isAbstract(), $rfl->token->isAbstract());
		$this->assertTrue($rfl->token->isAbstract());
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertFalse($rfl->token->isInstantiable());
		$this->assertSame($rfl->internal->getModifiers(), (xdebug_get_code_coverage() ? 16777216 : 0) + $rfl->token->getModifiers());
		$this->assertSame(InternalReflectionClass::IS_IMPLICIT_ABSTRACT | InternalReflectionClass::IS_EXPLICIT_ABSTRACT, $rfl->token->getModifiers());

		$rfl = $this->getClassReflection('noAbstract');
		$this->assertSame($rfl->internal->isAbstract(), $rfl->token->isAbstract());
		$this->assertFalse($rfl->token->isAbstract());
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertTrue($rfl->token->isInstantiable());
		$this->assertSame($rfl->internal->getModifiers(), (xdebug_get_code_coverage() ? 16777216 : 0) + $rfl->token->getModifiers());
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
		$this->assertSame($rfl->internal->getModifiers(), (xdebug_get_code_coverage() ? 16777216 : 0) + $rfl->token->getModifiers());
		$this->assertSame(InternalReflectionClass::IS_FINAL, $rfl->token->getModifiers());

		$rfl = $this->getClassReflection('noFinal');
		$this->assertSame($rfl->internal->isFinal(), $rfl->token->isFinal());
		$this->assertFalse($rfl->token->isFinal());
		$this->assertSame($rfl->internal->getModifiers(), (xdebug_get_code_coverage() ? 16777216 : 0) + $rfl->token->getModifiers());
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

		$this->assertSame($rfl->internal->getModifiers(), (xdebug_get_code_coverage() ? 16777216 : 0) + $rfl->token->getModifiers());
		$this->assertSame($rfl->internal->getInterfaceNames(), $rfl->token->getInterfaceNames());
		$this->assertSame(['Traversable', 'Iterator', 'Countable', 'ArrayAccess', 'Serializable'], $rfl->token->getInterfaceNames());
		$this->assertSame(['Countable', 'ArrayAccess', 'Serializable'], $rfl->token->getOwnInterfaceNames());
		$this->assertSame(array_keys($rfl->internal->getInterfaces()), array_keys($rfl->token->getInterfaces()));
		$this->assertSame(['Traversable', 'Iterator', 'Countable', 'ArrayAccess', 'Serializable'], array_keys($rfl->token->getInterfaces()));
		$this->assertSame(['Countable', 'ArrayAccess', 'Serializable'], array_keys($rfl->token->getOwnInterfaces()));
		foreach ($rfl->token->getInterfaces() as $interface) {
			$this->assertInstanceOf('ApiGen\TokenReflection\Php\ReflectionClass', $interface);
		}
		foreach ($rfl->token->getOwnInterfaces() as $interface) {
			$this->assertInstanceOf('ApiGen\TokenReflection\Php\ReflectionClass', $interface);
		}
		$this->assertSame($rfl->internal->implementsInterface('Countable'), $rfl->token->implementsInterface('Countable'));
		$this->assertTrue($rfl->token->implementsInterface('Countable'));
		$this->assertTrue($rfl->token->implementsInterface(new InternalReflectionClass('Countable')));

		$token = $this->getBroker()->getClass('Iterator');
		$this->assertSame(['Traversable'], array_keys($token->getInterfaces()));
		$this->assertSame(['Traversable'], $token->getInterfaceNames());
		$this->assertSame(['Traversable'], array_keys($token->getOwnInterfaces()));
		$this->assertSame(['Traversable'], $token->getOwnInterfaceNames());

		$rfl = $this->getClassReflection('noInterfaces');
		$this->assertSame($rfl->internal->getModifiers(), (xdebug_get_code_coverage() ? 16777216 : 0) + $rfl->token->getModifiers());
		$this->assertSame($rfl->internal->getInterfaceNames(), $rfl->token->getInterfaceNames());
		$this->assertSame([], $rfl->token->getOwnInterfaceNames());
		$this->assertSame([], $rfl->token->getInterfaceNames());
		$this->assertSame($rfl->internal->getInterfaces(), $rfl->token->getInterfaces());
		$this->assertSame([], $rfl->token->getInterfaces());
		$this->assertSame([], $rfl->token->getOwnInterfaces());
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
		foreach (['TokenReflection_Test_ClassGrandGrandParent', 'TokenReflection_Test_ClassGrandParent'] as $parent) {
			$this->assertSame($rfl->internal->isSubclassOf($parent), $rfl->token->isSubclassOf($parent));
			$this->assertTrue($rfl->token->isSubclassOf($parent));
			$this->assertTrue($rfl->token->isSubclassOf($this->getBroker()->getClass($parent)));
		}
		foreach (['TokenReflection_Test_ClassParent', 'Exception', 'DateTime'] as $parent) {
			$this->assertSame($rfl->internal->isSubclassOf($parent), $rfl->token->isSubclassOf($parent));
			$this->assertFalse($rfl->token->isSubclassOf($parent));
		}
		$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionClass', $rfl->token->getParentClass());
		$this->assertSame('TokenReflection_Test_ClassGrandParent', $rfl->token->getParentClassName());

		$this->assertSame(3, count($rfl->token->getParentClasses()));
		foreach ($rfl->token->getParentClasses() as $class) {
			$this->assertInstanceOf('ApiGen\TokenReflection\ReflectionClassInterface', $class);
		}
		$this->assertSame(['TokenReflection_Test_ClassGrandParent', 'TokenReflection_Test_ClassGrandGrandParent', 'ReflectionClass'], $rfl->token->getParentClassNameList());

		$rfl = $this->getClassReflection('noParent');
		$this->assertSame($rfl->internal->isSubclassOf('Exception'), $rfl->token->isSubclassOf('Exception'));
		$this->assertFalse($rfl->token->isSubclassOf('Exception'));
		$this->assertFalse($rfl->token->isSubclassOf(new InternalReflectionClass('Exception')));

		$this->assertSame($rfl->internal->getParentClass(), $rfl->token->getParentClass());
		$this->assertFalse($rfl->token->getParentClass());
		$this->assertSame([], $rfl->token->getParentClasses());
		$this->assertSame([], $rfl->token->getParentClassNameList());
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

		$this->assertInstanceOf('ApiGen\TokenReflection\Php\ReflectionExtension', $rfl->token->getExtension());
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
	 * TestCase getting of documentation comment, when after docComment many line breaks.
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
		$this->assertSame('My Short description.', $rfl->token->getAnnotation(AnnotationParser::SHORT_DESCRIPTION));
		$this->assertSame('Long description. Phew, that was long.', $rfl->token->getAnnotation(AnnotationParser::LONG_DESCRIPTION));

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionClass('TokenReflection_Test_ClassDocCommentInheritanceImplicit');
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_ClassDocCommentInheritanceImplicit');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame($parent->token->getAnnotations(), $rfl->token->getAnnotations());
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
		static $expected = [
			'publicStatic' => 'public static $publicStatic = TRUE;',
			'privateStatic' => 'private static $privateStatic = \'something\';',
			'protectedStatic' => 'protected static $protectedStatic = 1;',
			'public' => 'public $public = FALSE;',
			'protected' => 'protected $protected = 0;',
			'private' => 'private $private = \'\';'
		];

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
		static $expected = [
			'protectedStaticFunction' => "protected static function protectedStaticFunction(\$one = TRUE)\n	{\n	}",
			'protectedFunction' => "protected function protectedFunction(\$two = FALSE)\n	{\n	}",
			'publicStaticFunction' => "public static function publicStaticFunction(\$five = 1.1)\n	{\n	}"
		];

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
		static $expected = [
			'PARENT' => 'PARENT = \'parent\';',
			'STRING' => 'STRING = \'string\';',
			'FLOAT' => 'FLOAT = 1.1;',
			'bool' => 'bool = TRUE;'
		];

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
		static $expected = [
			'methods' => "class TokenReflection_Test_ClassMethods extends TokenReflection_Test_ClassMethodsParent\n{\n\n	public function __construct(\$three)\n	{\n	}\n\n\n	public function __destruct()\n	{\n	}\n\n\n	public final function publicFinalFunction(\$four = 1)\n	{\n	}\n\n\n	public static function publicStaticFunction(\$five = 1.1)\n	{\n	}\n\n\n	private static function privateStaticFunction(\$six = 'string', \$seven = NULL)\n	{\n	}\n\n\n	public function publicFunction(array \$eight = [])\n	{\n	}\n\n\n	private function privateFunction(Foo \$nine = NULL)\n	{\n	}\n\n}",
			'constants' => "class TokenReflection_Test_ClassConstants extends TokenReflection_Test_ClassConstantsParent\n{\n\n	const STRING = 'string';\n	const int = 1;\n	const FLOAT = 1.1;\n	const bool = TRUE;\n\n}",
			'docComment' => "/**\n * TokenReflection_Test_ClassDocComment.\n *\n * @copyright Copyright (c) 2011\n * @author author\n * @see http://php.net\n */\nclass TokenReflection_Test_ClassDocComment\n{\n\n}"
		];

		foreach ($expected as $className => $source) {
			$this->assertSame(
				$source,
				$this->getClassReflection($className)->token->getSource()
			);
		}
	}


	/**
	 * Tests traits support comparing with the internal reflection.
	 *
	 * For PHP 5.4+ only.
	 */
	public function testTraits()
	{
		static $classes = [
			'TokenReflection_Test_ClassTraitsTrait1',
			'TokenReflection_Test_ClassTraitsTrait2',
			'TokenReflection_Test_ClassTraitsTrait3',
			'TokenReflection_Test_ClassTraitsTrait4',
			'TokenReflection_Test_ClassTraits',
			'TokenReflection_Test_ClassTraits2',
			'TokenReflection_Test_ClassTraits3',
			'TokenReflection_Test_ClassTraits4'
		];

		require_once $this->getFilePath('traits');
		$this->getBroker()->process($this->getFilePath('traits'));

		foreach ($classes as $className) {
			$token = $this->getBroker()->getClass($className);
			$internal = new \ReflectionClass($className);

			$this->assertSame($internal->isTrait(), $token->isTrait(), $className);
			// $this->assertSame($internal->getTraitAliases(), $token->getTraitAliases(), $className);
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
		static $expected = [
			'TokenReflection_Test_ClassTraitsTrait1' => [TRUE, [], [], [], 0, 0],
			'TokenReflection_Test_ClassTraitsTrait2' => [TRUE, ['t2privatef' => '(null)::privatef'], ['TokenReflection_Test_ClassTraitsTrait1'], ['TokenReflection_Test_ClassTraitsTrait1'], 6, 3],
			'TokenReflection_Test_ClassTraitsTrait3' => [TRUE, [], [], [], 0, 0],
			'TokenReflection_Test_ClassTraitsTrait4' => [TRUE, [], [], [], 0, 0],
			'TokenReflection_Test_ClassTraits' => [FALSE, ['privatef2' => '(null)::publicf', 'publicf3' => '(null)::protectedf', 'publicfOriginal' => '(null)::publicf'], ['TokenReflection_Test_ClassTraitsTrait1'], ['TokenReflection_Test_ClassTraitsTrait1'], 6, 6],
			'TokenReflection_Test_ClassTraits2' => [FALSE, [], ['TokenReflection_Test_ClassTraitsTrait2'], ['TokenReflection_Test_ClassTraitsTrait2'], 6, 3],
			'TokenReflection_Test_ClassTraits3' => [FALSE, [], ['TokenReflection_Test_ClassTraitsTrait1'], ['TokenReflection_Test_ClassTraitsTrait1'], 6, 2],
			'TokenReflection_Test_ClassTraits4' => [FALSE, [], ['TokenReflection_Test_ClassTraitsTrait3', 'TokenReflection_Test_ClassTraitsTrait4'], ['TokenReflection_Test_ClassTraitsTrait3', 'TokenReflection_Test_ClassTraitsTrait4'], 2, 1]
		];

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
		$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionClass', $token);

		try {
			$token->newInstanceWithoutConstructor();
			$this->fail('TokenReflection\Exception\RuntimeException expected.');
		} catch (\Exception $e) {
			$this->assertInstanceOf('ApiGen\TokenReflection\Exception\RuntimeException', $e);

			if ($e->getCode() !== RuntimeException::UNSUPPORTED) {
				throw $e;
			}
		}

		$token = $this->getBroker()->getClass('Exception');
		$this->assertInstanceOf('ApiGen\TokenReflection\Php\ReflectionClass', $token);

		try {
			$token->newInstanceWithoutConstructor();
			$this->fail('ApiGen\TokenReflection\Exception\RuntimeException expected.');
		} catch (\Exception $e) {
			$this->assertInstanceOf('ApiGen\TokenReflection\Exception\RuntimeException', $e);

			if ($e->getCode() !== RuntimeException::UNSUPPORTED) {
				throw $e;
			}
		}

		$token = $this->getBroker()->getClass('TokenReflection_Test_NewInstanceWithoutConstructor2');
		$internal = new \ReflectionClass('TokenReflection_Test_NewInstanceWithoutConstructor2');
		$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionClass', $token);

		$instance = $token->newInstanceWithoutConstructor();
		$this->assertFalse($instance->check);

		$instance2 = $token->newInstanceArgs();
		$this->assertTrue($instance2->check);

		// Try the internal reflection
		$this->assertEquals($internal->newInstanceWithoutConstructor(), $token->newInstanceWithoutConstructor());
	}


	/**
	 * Tests returning pretty class names.
	 */
	public function testPrettyNames()
	{
		static $names = [
			'ns1\\TokenReflection_Test_ClassPrettyNames',
			'ns2\\ns3\\ns4\\TokenReflection_Test_ClassPrettyNames2',
			'TokenReflection_Test_ClassPrettyNames3'
		];

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
	 * @return ApiGen\TokenReflection\Php\ReflectionClass
	 */
	private function getInternalClassReflection()
	{
		return $this->getBroker()->getClass('Exception');
	}


	/**
	 * Returns a non existent class reflection.
	 *
	 * @return ApiGen\TokenReflection\Dummy\ReflectionClass
	 */
	private function getDummyClassReflection()
	{
		static $className = 'foo_bar';

		if (class_exists($className, FALSE)) {
			$this->markTestSkipped(sprintf('Class %s exists.', $className));
		}

		return $this->getBroker()->getClass($className);
	}

}
