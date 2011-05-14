<?php

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

class ReflectionClassTest extends Test
{
	protected $type = 'class';

	public function testConstants()
	{
		$rfl = $this->getClassReflection('constants');

		$this->assertEquals($rfl->internal->hasConstant('STRING'), $rfl->token->hasConstant('STRING'));
		$this->assertTrue($rfl->token->hasConstant('STRING'));
		$this->assertTrue($rfl->token->hasOwnConstant('STRING'));
		$this->assertEquals($rfl->internal->hasConstant('NONEXISTENT'), $rfl->token->hasConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasOwnConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasOwnConstant('PARENT'));

		$this->assertEquals($rfl->internal->getConstant('STRING'), $rfl->token->getConstant('STRING'));
		$this->assertEquals('string', $rfl->token->getConstant('STRING'));
		$this->assertEquals($rfl->internal->getConstant('NONEXISTENT'), $rfl->token->getConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->getConstant('NONEXISTENT'));
		$this->assertEquals($rfl->internal->getConstants(), $rfl->token->getConstants());
		$this->assertEquals(array('STRING' => 'string', 'FLOAT' => 1.1, 'INTEGER' => 1, 'BOOLEAN' => true, 'PARENT' => 'parent'), $rfl->token->getConstants());
		$this->assertEquals(array('STRING' => 'string', 'FLOAT' => 1.1, 'INTEGER' => 1, 'BOOLEAN' => true), $rfl->token->getOwnConstants());
		$this->assertEquals(array('STRING', 'INTEGER', 'FLOAT', 'BOOLEAN'), array_keys($rfl->token->getOwnConstantReflections()));
		foreach ($rfl->token->getOwnConstantReflections() as $constant) {
			$this->assertInstanceOf('TokenReflection\ReflectionConstant', $constant);
		}

		$rfl = $this->getClassReflection('noConstants');

		$this->assertEquals($rfl->internal->hasConstant('NONEXISTENT'), $rfl->token->hasConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasOwnConstant('NONEXISTENT'));

		$this->assertEquals($rfl->internal->getConstant('NONEXISTENT'), $rfl->token->getConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->getConstant('NONEXISTENT'));
		$this->assertEquals($rfl->internal->getConstants(), $rfl->token->getConstants());
		$this->assertEquals(array(), $rfl->token->getConstants());
		$this->assertEquals(array(), $rfl->token->getOwnConstants());
		$this->assertEquals(array(), $rfl->token->getOwnConstantReflections());
	}

	public function testProperties()
	{
		ReflectionProperty::setParseValueDefinitions(true);
		$rfl = $this->getClassReflection('properties');

		$filters = array(\ReflectionProperty::IS_STATIC, \ReflectionProperty::IS_PUBLIC, \ReflectionProperty::IS_PROTECTED, \ReflectionProperty::IS_PRIVATE);
		foreach ($this->getFilterCombinations($filters) as $filter) {
			$this->assertEquals(count($rfl->internal->getProperties($filter)), count($rfl->token->getProperties($filter)));
			foreach ($rfl->token->getProperties($filter) as $property) {
				$this->assertInstanceOf('TokenReflection\ReflectionProperty', $property);
			}
			foreach ($rfl->token->getOwnProperties($filter) as $property) {
				$this->assertInstanceOf('TokenReflection\ReflectionProperty', $property);
			}
		}

		$this->assertEquals($rfl->internal->getDefaultProperties(), $rfl->token->getDefaultProperties());
		$this->assertEquals(array('publicStatic' => true, 'protectedStatic' => 1, 'privateStatic' => 'something', 'public' => false, 'protected' => 0, 'private' => ''), $rfl->token->getDefaultProperties());

		$this->assertEquals($rfl->internal->getStaticProperties(), $rfl->token->getStaticProperties());
		$this->assertEquals(array('publicStatic' => true, 'protectedStatic' => 1, 'privateStatic' => 'something'), $rfl->token->getStaticProperties());

		$properties = array('public', 'publicStatic', 'protectedStatic', 'protectedStatic', 'private', 'privateStatic');
		foreach ($properties as $property) {
			$this->assertEquals($rfl->internal->hasProperty($property), $rfl->token->hasProperty($property));
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
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		$this->assertEquals($rfl->internal->getStaticPropertyValue('publicStatic'), $rfl->token->getStaticPropertyValue('publicStatic'));
		$this->assertTrue($rfl->token->getStaticPropertyValue('publicStatic'));

		try {
			$rfl->token->getStaticPropertyValue('protectedStatic');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		try {
			$rfl->token->getStaticPropertyValue('privateStatic');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		$this->assertEquals($rfl->internal->setStaticPropertyValue('publicStatic', false), $rfl->token->setStaticPropertyValue('publicStatic', false));
		$this->assertNull($rfl->token->setStaticPropertyValue('publicStatic', false));
		$this->assertEquals($rfl->internal->getStaticPropertyValue('publicStatic'), $rfl->token->getStaticPropertyValue('publicStatic'));
		$this->assertFalse($rfl->token->getStaticPropertyValue('publicStatic'));

		try {
			$rfl->token->setStaticPropertyValue('protectedStatic', 0);
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		try {
			$rfl->token->setStaticPropertyValue('privateStatic', '');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		$rfl = $this->getClassReflection('noProperties');

		$this->assertEquals($rfl->internal->getDefaultProperties(), $rfl->token->getDefaultProperties());
		$this->assertEquals(array(), $rfl->token->getDefaultProperties());
		$this->assertEquals($rfl->internal->getProperties(), $rfl->token->getProperties());
		$this->assertEquals(array(), $rfl->token->getProperties());
		$this->assertEquals(array(), $rfl->token->getOwnProperties());
		$this->assertEquals($rfl->internal->getStaticProperties(), $rfl->token->getStaticProperties());
		$this->assertEquals(array(), $rfl->token->getStaticProperties());

		$this->assertEquals($rfl->internal->hasProperty('nonExistent'), $rfl->token->hasProperty('nonExistent'));
		$this->assertFalse($rfl->token->hasProperty('nonExistent'));
		$this->assertFalse($rfl->token->hasOwnProperty('nonExistent'));

		try {
			$rfl->token->getProperty('nonExistent');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		try {
			$rfl->token->getStaticPropertyValue('nonExistent');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		try {
			$rfl->token->setStaticPropertyValue('property', 'property');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		$rfl = $this->getClassReflection('doubleProperties');

		$filters = array(\ReflectionProperty::IS_STATIC, \ReflectionProperty::IS_PUBLIC, \ReflectionProperty::IS_PROTECTED, \ReflectionProperty::IS_PRIVATE);
		foreach ($this->getFilterCombinations($filters) as $filter) {
			$this->assertEquals(count($rfl->internal->getProperties($filter)), count($rfl->token->getProperties($filter)), $filter);
			foreach ($rfl->token->getProperties($filter) as $property) {
				$this->assertInstanceOf('TokenReflection\ReflectionProperty', $property);
			}
			foreach ($rfl->token->getOwnProperties($filter) as $property) {
				$this->assertInstanceOf('TokenReflection\ReflectionProperty', $property);
			}
		}

		$this->assertEquals($rfl->internal->getDefaultProperties(), $rfl->token->getDefaultProperties());
		$this->assertEquals(array('publicOne' => true, 'publicTwo' => false, 'protectedOne' => 1, 'protectedTwo' => 0, 'privateOne' => 'something', 'privateTwo' => ''), $rfl->token->getDefaultProperties());

		$this->assertEquals($rfl->internal->getStaticProperties(), $rfl->token->getStaticProperties());
		$this->assertEquals(array('protectedOne' => 1, 'protectedTwo' => 0), $rfl->token->getStaticProperties());

		$properties = array('publicOne', 'publicTwo', 'protectedOne', 'protectedTwo', 'privateOne', 'privateTwo');
		foreach ($properties as $property) {
			$this->assertEquals($rfl->internal->hasProperty($property), $rfl->token->hasProperty($property));
			$this->assertTrue($rfl->token->hasProperty($property));

			$this->assertInstanceOf('TokenReflection\ReflectionProperty', $rfl->token->getProperty($property));
		}

		ReflectionProperty::setParseValueDefinitions(false);
	}

	public function testInstantiableCloneable()
	{
		$rfl = $this->getClassReflection('publicConstructor');
		$this->assertEquals($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertTrue($rfl->token->isInstantiable());
		// $this->assertEquals($rfl->internal->isCloneable(), $rfl->token->isCloneable());
		$this->assertTrue($rfl->token->isCloneable());

		$rfl = $this->getClassReflection('privateConstructor');
		$this->assertEquals($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertFalse($rfl->token->isInstantiable());
		// $this->assertEquals($rfl->internal->isCloneable(), $rfl->token->isCloneable());
		$this->assertFalse($rfl->token->isCloneable());

		$rfl = $this->getClassReflection('publicClone');
		// $this->assertEquals($rfl->internal->isCloneable(), $rfl->token->isCloneable());
		$this->assertTrue($rfl->token->isCloneable());

		$rfl = $this->getClassReflection('privateClone');
		// $this->assertEquals($rfl->internal->isCloneable(), $rfl->token->isCloneable());
		$this->assertFalse($rfl->token->isCloneable());
	}

	public function testMethods()
	{
		$rfl = $this->getClassReflection('methods');

		$filters = array(\ReflectionMethod::IS_STATIC, \ReflectionMethod::IS_PUBLIC, \ReflectionMethod::IS_PROTECTED, \ReflectionMethod::IS_PRIVATE, \ReflectionMethod::IS_ABSTRACT, \ReflectionMethod::IS_FINAL);
		foreach ($this->getFilterCombinations($filters) as $filter) {
			$this->assertEquals(count($rfl->internal->getMethods($filter)), count($rfl->token->getMethods($filter)));
			foreach ($rfl->token->getMethods($filter) as $method) {
				$this->assertInstanceOf('TokenReflection\ReflectionMethod', $method);
			}
			foreach ($rfl->token->getOwnMethods($filter) as $method) {
				$this->assertInstanceOf('TokenReflection\ReflectionMethod', $method);
			}
		}

		$methods = array('__construct', '__destruct', 'publicFinalFunction', 'publicStaticFunction', 'protectedStaticFunction', 'privateStaticFunction', 'publicFunction', 'protectedFunction', 'privateFunction');
		foreach ($methods as $method) {
			$this->assertEquals($rfl->internal->hasMethod($method), $rfl->token->hasMethod($method));
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
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		$rfl = $this->getClassReflection('noMethods');

		$this->assertEquals($rfl->internal->getMethods(), $rfl->token->getMethods());
		$this->assertEquals(array(), $rfl->token->getMethods());
		$this->assertEquals(array(), $rfl->token->getOwnMethods());

		try {
			$rfl->token->getMethod('nonExistent');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		$this->assertEquals($rfl->internal->hasMethod('nonExistent'), $rfl->token->hasMethod('nonExistent'));
		$this->assertFalse($rfl->token->hasMethod('nonExistent'));
		$this->assertFalse($rfl->token->hasOwnMethod('nonExistent'));

		$this->assertEquals($rfl->internal->getConstructor(), $rfl->token->getConstructor());
		$this->assertNull($rfl->token->getConstructor());
		$this->assertNull($rfl->token->getDestructor());
	}

	public function testLines()
	{
		$rfl = $this->getClassReflection('lines');
		$this->assertEquals($rfl->internal->getStartLine(), $rfl->token->getStartLine());
		$this->assertEquals(3, $rfl->token->getStartLine());
		$this->assertEquals($rfl->internal->getEndLine(), $rfl->token->getEndLine());
		$this->assertEquals(5, $rfl->token->getEndLine());
	}

	public function testInstances()
	{
		$rfl = $this->getClassReflection('instances');

		$this->assertEquals($rfl->internal->isInstance(new \TokenReflection_Test_ClassInstances(1)), $rfl->token->isInstance(new \TokenReflection_Test_ClassInstances(1)));
		$this->assertTrue($rfl->token->isInstance(new \TokenReflection_Test_ClassInstances(1)));
		$this->assertEquals($rfl->internal->isInstance(new \TokenReflection_Test_ClassInstancesChild(1)), $rfl->token->isInstance(new \TokenReflection_Test_ClassInstancesChild(1)));
		$this->assertTrue($rfl->token->isInstance(new \TokenReflection_Test_ClassInstancesChild(1)));
		$this->assertEquals($rfl->internal->isInstance(new \Exception()), $rfl->token->isInstance(new \Exception()));
		$this->assertFalse($rfl->token->isInstance(new \Exception()));

		$this->assertEquals($rfl->internal->newInstance(1), $rfl->token->newInstance(1));
		$this->assertInstanceOf($this->getClassName('instances'), $rfl->token->newInstance(1));
		$this->assertEquals($rfl->internal->newInstanceArgs(array(1)), $rfl->token->newInstanceArgs(array(1)));
		$this->assertInstanceOf($this->getClassName('instances'), $rfl->token->newInstanceArgs(array(1)));
	}

	public function testAbstract()
	{
		$rfl = $this->getClassReflection('abstract');
		$this->assertEquals($rfl->internal->isAbstract(), $rfl->token->isAbstract());
		$this->assertTrue($rfl->token->isAbstract());
		$this->assertEquals($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertFalse($rfl->token->isInstantiable());
		$this->assertEquals($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertEquals(\ReflectionClass::IS_EXPLICIT_ABSTRACT, $rfl->token->getModifiers());

		$rfl = $this->getClassReflection('abstractImplicit');
		$this->assertEquals($rfl->internal->isAbstract(), $rfl->token->isAbstract());
		$this->assertTrue($rfl->token->isAbstract());
		$this->assertEquals($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertFalse($rfl->token->isInstantiable());
		$this->assertEquals($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertEquals(\ReflectionClass::IS_IMPLICIT_ABSTRACT | \ReflectionClass::IS_EXPLICIT_ABSTRACT, $rfl->token->getModifiers());

		$rfl = $this->getClassReflection('noAbstract');
		$this->assertEquals($rfl->internal->isAbstract(), $rfl->token->isAbstract());
		$this->assertFalse($rfl->token->isAbstract());
		$this->assertEquals($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertTrue($rfl->token->isInstantiable());
		$this->assertEquals($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertEquals(0, $rfl->token->getModifiers());
	}

	public function testFinal()
	{
		$rfl = $this->getClassReflection('final');
		$this->assertEquals($rfl->internal->isFinal(), $rfl->token->isFinal());
		$this->assertTrue($rfl->token->isFinal());
		$this->assertEquals($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertEquals(\ReflectionClass::IS_FINAL, $rfl->token->getModifiers());

		$rfl = $this->getClassReflection('noFinal');
		$this->assertEquals($rfl->internal->isFinal(), $rfl->token->isFinal());
		$this->assertFalse($rfl->token->isFinal());
		$this->assertEquals($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertEquals(0, $rfl->token->getModifiers());
	}

	public function testInterface()
	{
		$rfl = $this->getClassReflection('interface');
		$this->assertEquals($rfl->internal->isInterface(), $rfl->token->isInterface());
		$this->assertTrue($rfl->token->isInterface());
		$this->assertEquals($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertFalse($rfl->token->isInstantiable());

		$rfl = $this->getClassReflection('noInterface');
		$this->assertEquals($rfl->internal->isInterface(), $rfl->token->isInterface());
		$this->assertFalse($rfl->token->isInterface());
		$this->assertEquals($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertTrue($rfl->token->isInstantiable());
	}

	public function testInterfaces()
	{
		$rfl = $this->getClassReflection('interfaces');

		$this->assertEquals($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertEquals($rfl->internal->getInterfaceNames(), $rfl->token->getInterfaceNames());
		$this->assertEquals(array('Traversable', 'Iterator', 'Countable'), $rfl->token->getInterfaceNames());
		$this->assertEquals(array('Countable'), $rfl->token->getOwnInterfaceNames());
		$this->assertEquals(array_keys($rfl->internal->getInterfaces()), array_keys($rfl->token->getInterfaces()));
		$this->assertEquals(array('Traversable', 'Iterator', 'Countable'), array_keys($rfl->token->getInterfaces()));
		$this->assertEquals(array('Countable'), array_keys($rfl->token->getOwnInterfaces()));
		foreach ($rfl->token->getInterfaces() as $interface) {
			$this->assertInstanceOf('TokenReflection\Php\ReflectionClass', $interface);
		}
		foreach ($rfl->token->getOwnInterfaces() as $interface) {
			$this->assertInstanceOf('TokenReflection\Php\ReflectionClass', $interface);
		}
		$this->assertEquals($rfl->internal->implementsInterface('Countable'), $rfl->token->implementsInterface('Countable'));
		$this->assertTrue($rfl->token->implementsInterface('Countable'));
		$this->assertTrue($rfl->token->implementsInterface(new \ReflectionClass('Countable')));

		$rfl = $this->getClassReflection('noInterfaces');
		$this->assertEquals($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertEquals($rfl->internal->getInterfaceNames(), $rfl->token->getInterfaceNames());
		$this->assertEquals(array(), $rfl->token->getOwnInterfaceNames());
		$this->assertEquals(array(), $rfl->token->getInterfaceNames());
		$this->assertEquals($rfl->internal->getInterfaces(), $rfl->token->getInterfaces());
		$this->assertEquals(array(), $rfl->token->getInterfaces());
		$this->assertEquals(array(), $rfl->token->getOwnInterfaces());
		$this->assertEquals($rfl->internal->implementsInterface('Countable'), $rfl->token->implementsInterface('Countable'));
		$this->assertFalse($rfl->token->implementsInterface('Countable'));
		$this->assertFalse($rfl->token->implementsInterface(new \ReflectionClass('Countable')));
	}

	public function testIterator()
	{
		$rfl = $this->getClassReflection('iterator');
		$this->assertEquals($rfl->internal->isIterateable(), $rfl->token->isIterateable());
		$this->assertTrue($rfl->token->isIterateable());

		$rfl = $this->getClassReflection('noIterator');
		$this->assertEquals($rfl->internal->isIterateable(), $rfl->token->isIterateable());
		$this->assertFalse($rfl->token->isIterateable());
	}

	public function testParent()
	{
		$rfl = $this->getClassReflection('parent');
		foreach (array('TokenReflection_Test_ClassGrandGrandParent', 'TokenReflection_Test_ClassGrandParent') as $parent) {
			$this->assertEquals($rfl->internal->isSubclassOf($parent), $rfl->token->isSubclassOf($parent));
			$this->assertTrue($rfl->token->isSubclassOf($parent));
			$this->assertTrue($rfl->token->isSubclassOf($this->getBroker()->getClass($parent)));
		}
		foreach (array('TokenReflection_Test_ClassParent', 'Exception', 'DateTime') as $parent) {
			$this->assertEquals($rfl->internal->isSubclassOf($parent), $rfl->token->isSubclassOf($parent));
			$this->assertFalse($rfl->token->isSubclassOf($parent));
		}
		$this->assertInstanceOf('TokenReflection\ReflectionClass', $rfl->token->getParentClass());
		$this->assertEquals('TokenReflection_Test_ClassGrandParent', $rfl->token->getParentClassName());

		$this->assertEquals(2, count($rfl->token->getParentClasses()));
		foreach ($rfl->token->getParentClasses() as $class) {
			$this->assertInstanceOf('TokenReflection\ReflectionClass', $class);
		}
		$this->assertEquals(array('TokenReflection_Test_ClassGrandParent', 'TokenReflection_Test_ClassGrandGrandParent'), $rfl->token->getParentClassNameList());

		$rfl = $this->getClassReflection('noParent');
		$this->assertEquals($rfl->internal->isSubclassOf('Exception'), $rfl->token->isSubclassOf('Exception'));
		$this->assertFalse($rfl->token->isSubclassOf('Exception'));
		$this->assertFalse($rfl->token->isSubclassOf(new \ReflectionClass('Exception')));

		$this->assertEquals($rfl->internal->getParentClass(), $rfl->token->getParentClass());
		$this->assertNull($rfl->token->getParentClass());
		$this->assertEquals(array(), $rfl->token->getParentClasses());
		$this->assertEquals(array(), $rfl->token->getParentClassNameList());
	}

	public function testUserDefined()
	{
		$rfl = $this->getClassReflection('userDefined');

		$this->assertEquals($rfl->internal->isUserDefined(), $rfl->token->isUserDefined());
		$this->assertTrue($rfl->token->isUserDefined());
		$this->assertEquals($rfl->internal->getFileName(), $rfl->token->getFileName());
		$this->assertEquals($this->getFilePath('userDefined'), $rfl->token->getFileName());
		$this->assertEquals($rfl->internal->isInternal(), $rfl->token->isInternal());
		$this->assertFalse($rfl->token->isInternal());

		$this->assertEquals($rfl->internal->getExtension(), $rfl->token->getExtension());
		$this->assertNull($rfl->token->getExtension());
		$this->assertEquals($rfl->internal->getExtensionName(), $rfl->token->getExtensionName());
		$this->assertNull($rfl->token->getExtensionName());

		$rfl = new \stdClass();
		$rfl->internal = new \ReflectionClass('Exception');
		$rfl->token = $this->getBroker()->getClass('Exception');

		$this->assertEquals($rfl->internal->isUserDefined(), $rfl->token->isUserDefined());
		$this->assertFalse($rfl->token->isUserDefined());
		$this->assertEquals($rfl->internal->getFileName(), $rfl->token->getFileName());
		$this->assertFalse($rfl->token->getFileName());
		$this->assertEquals($rfl->internal->isInternal(), $rfl->token->isInternal());
		$this->assertTrue($rfl->token->isInternal());

		$this->assertInstanceOf('TokenReflection\Php\ReflectionExtension', $rfl->token->getExtension());
		$this->assertEquals($rfl->internal->getExtensionName(), $rfl->token->getExtensionName());
		$this->assertEquals('Core', $rfl->token->getExtensionName());
	}

	public function testDocComment()
	{
		$rfl = $this->getClassReflection('docComment');
		$this->assertEquals($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertEquals("/**\n * TokenReflection_Test_ClassDocComment.\n *\n * @copyright Copyright (c) 2011\n * @author author\n * @see http://php.net\n */", $rfl->token->getDocComment());

		$rfl = $this->getClassReflection('noDocComment');
		$this->assertEquals($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertFalse($rfl->token->getDocComment());
	}

	public function testInNamespace()
	{
		require_once $this->getFilePath('inNamespace');
		$this->getBroker()->processFile($this->getFilePath('inNamespace'));

		$rfl = new \stdClass();
		$rfl->internal = new \ReflectionClass('TokenReflection\Test\ClassInNamespace');
		$rfl->token = $this->getBroker()->getClass('TokenReflection\Test\ClassInNamespace');

		$this->assertEquals($rfl->internal->inNamespace(), $rfl->token->inNamespace());
		$this->assertTrue($rfl->token->inNamespace());
		$this->assertEquals($rfl->internal->getNamespaceName(), $rfl->token->getNamespaceName());
		$this->assertEquals('TokenReflection\Test', $rfl->token->getNamespaceName());
		$this->assertEquals($rfl->internal->getName(), $rfl->token->getName());
		$this->assertEquals('TokenReflection\Test\ClassInNamespace', $rfl->token->getName());
		$this->assertEquals($rfl->internal->getShortName(), $rfl->token->getShortName());
		$this->assertEquals('ClassInNamespace', $rfl->token->getShortName());

		$rfl = $this->getClassReflection('noNamespace');
		$this->assertEquals($rfl->internal->inNamespace(), $rfl->token->inNamespace());
		$this->assertFalse($rfl->token->inNamespace());
		$this->assertEquals($rfl->internal->getNamespaceName(), $rfl->token->getNamespaceName());
		$this->assertEquals('', $rfl->token->getNamespaceName());
		$this->assertEquals($rfl->internal->getName(), $rfl->token->getName());
		$this->assertEquals($this->getClassName('noNamespace'), $rfl->token->getName());
		$this->assertEquals($rfl->internal->getShortName(), $rfl->token->getShortName());
		$this->assertEquals($this->getClassName('noNamespace'), $rfl->token->getShortName());
	}

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

	public function testMethodGetSource()
	{
		static $expected = array(
			'protectedStaticFunction' => "protected static function protectedStaticFunction()\n	{\n	}",
			'protectedFunction' => "protected function protectedFunction()\n	{\n	}",
			'publicStaticFunction' => "public static function publicStaticFunction()\n	{\n	}"
		);

		$rfl = $this->getClassReflection('methods')->token;
		foreach ($expected as $methodName => $source) {
			$this->assertSame($source, $rfl->getMethod($methodName)->getSource());
		}
	}

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

	public function testClassGetSource()
	{
		static $expected = array(
			'methods' => "class TokenReflection_Test_ClassMethods extends TokenReflection_Test_ClassMethodsParent\n{\n	public function __construct()\n	{\n	}\n\n	public function __destruct()\n	{\n	}\n\n	public final function publicFinalFunction()\n	{\n	}\n\n	public static function publicStaticFunction()\n	{\n	}\n\n	private static function privateStaticFunction()\n	{\n	}\n\n	public function publicFunction()\n	{\n	}\n\n	private function privateFunction()\n	{\n	}\n}",
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
}
