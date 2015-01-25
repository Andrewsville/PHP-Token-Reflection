<?php

namespace ApiGen\TokenReflection\Tests\Reflection;

use ApiGen;
use ApiGen\TokenReflection\Parser\AnnotationParser;
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

		$token = $this->parser->getStorage()->getClass('RecursiveDirectoryIterator');
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
		$this->parser->parseFile($this->getFilePath('modifiers'));

		foreach ($classes as $className) {
			$token = $this->parser->getStorage()->getClass($className);
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

		$token = $this->parser->getStorage()->getClass('Iterator');
		$this->assertSame(['Traversable'], array_keys($token->getInterfaces()));
		$this->assertSame(['Traversable'], array_keys($token->getOwnInterfaces()));

		$rfl = $this->getClassReflection('noInterfaces');
		$this->assertSame($rfl->internal->getModifiers(), (xdebug_get_code_coverage() ? 16777216 : 0) + $rfl->token->getModifiers());
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
			$this->assertTrue($rfl->token->isSubclassOf($this->parser->getStorage()->getClass($parent)));
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

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionClass('Exception');
		$rfl->token = $this->parser->getStorage()->getClass('Exception');

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
		$this->parser->parseFile($this->getFilePath('docCommentInheritance'));

		$parent = new \stdClass();
		$parent->internal = new InternalReflectionClass('TokenReflection_Test_ClassDocCommentInheritanceParent');
		$parent->token = $this->parser->getStorage()->getClass('TokenReflection_Test_ClassDocCommentInheritanceParent');
		$this->assertSame($parent->internal->getDocComment(), $parent->token->getDocComment());

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionClass('TokenReflection_Test_ClassDocCommentInheritanceExplicit');
		$rfl->token = $this->parser->getStorage()->getClass('TokenReflection_Test_ClassDocCommentInheritanceExplicit');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame('My Short description.', $rfl->token->getAnnotation(AnnotationParser::SHORT_DESCRIPTION));
		$this->assertSame('Long description. Phew, that was long.', $rfl->token->getAnnotation(AnnotationParser::LONG_DESCRIPTION));

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionClass('TokenReflection_Test_ClassDocCommentInheritanceImplicit');
		$rfl->token = $this->parser->getStorage()->getClass('TokenReflection_Test_ClassDocCommentInheritanceImplicit');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame($parent->token->getAnnotations(), $rfl->token->getAnnotations());
	}


	/**
	 * Tests if class is defined in namespace.
	 */
	public function testInNamespace()
	{
		require_once $this->getFilePath('inNamespace');
		$this->parser->parseFile($this->getFilePath('inNamespace'));

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionClass('TokenReflection\Test\ClassInNamespace');
		$rfl->token = $this->parser->getStorage()->getClass('TokenReflection\Test\ClassInNamespace');

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
		$this->parser->parseFile($this->getFilePath('traits'));

		foreach ($classes as $className) {
			$token = $this->parser->getStorage()->getClass($className);
			$internal = new \ReflectionClass($className);

			$this->assertSame($internal->isTrait(), $token->isTrait(), $className);
			// $this->assertSame($internal->getTraitAliases(), $token->getTraitAliases(), $className);
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

		$this->parser->parseFile($this->getFilePath('traits'));
		foreach ($expected as $className => $definition) {
			$reflection = $this->parser->getStorage()->getClass($className);

			$this->assertSame($definition[0], $reflection->isTrait(), $className);
			$this->assertSame($definition[1], $reflection->getTraitAliases(), $className);
			$this->assertSame(count($definition[2]), count($reflection->getTraits()), $className);
			foreach ($definition[2] as $traitName) {
				$this->assertTrue($reflection->usesTrait($traitName), $className);
			}

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
	 * Tests returning pretty class names.
	 */
	public function testPrettyNames()
	{
		static $names = [
			'ns1\\TokenReflection_Test_ClassPrettyNames',
			'ns2\\ns3\\ns4\\TokenReflection_Test_ClassPrettyNames2',
			'TokenReflection_Test_ClassPrettyNames3'
		];

		$broker = $this->parser;
		$broker->parseFile($this->getFilePath('pretty-names'));

		foreach ($names as $name) {
			$this->assertTrue($this->parser->getStorage()->hasClass($name), $name);

			$rfl = $this->parser->getStorage()->getClass($name);
		}
	}

}
