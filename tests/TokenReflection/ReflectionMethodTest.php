<?php

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

class ReflectionMethodTest extends Test
{
	protected $type = 'method';

	public function testLines()
	{
		$rfl = $this->getMethodReflection('lines');
		$this->assertEquals($rfl->internal->getStartLine(), $rfl->token->getStartLine());
		$this->assertEquals(5, $rfl->token->getStartLine());
		$this->assertEquals($rfl->internal->getEndLine(), $rfl->token->getEndLine());
		$this->assertEquals(7, $rfl->token->getEndLine());
	}

	public function testComment()
	{
		$rfl = $this->getMethodReflection('docComment');
		$this->assertEquals($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertEquals("/**\n\t * This is a method.\n\t */", $rfl->token->getDocComment());

		$rfl = $this->getMethodReflection('noComment');
		$this->assertEquals($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertFalse($rfl->token->getDocComment());
	}

	public function testStaticVariables()
	{
		/**
		 * @todo
		 */
		return;

		$rfl = $this->getMethodReflection('staticVariables');

		$this->assertEquals($rfl->internal->getStaticVariables(), $rfl->token->getStaticVariables());
		$this->assertEquals(array('string' => 'string', 'integer' => 1, 'float' => 1.1, 'boolean' => true, 'null' => null, 'array' => array(1 => 1)), $rfl->token->getStaticVariables());
	}

	public function testClosure()
	{
		$rfl = $this->getMethodReflection('noClosure');
		$this->assertEquals($rfl->internal->isClosure(), $rfl->token->isClosure());
		$this->assertFalse($rfl->token->isClosure());
	}

	public function testDeprecated()
	{
		$rfl = $this->getMethodReflection('noDeprecated');
		$this->assertEquals($rfl->internal->isDeprecated(), $rfl->token->isDeprecated());
		$this->assertFalse($rfl->token->isDeprecated());
	}

	public function testConstructorDestructor()
	{
		$rfl = $this->getClassReflection('constructorDestructor');

		$internal = $rfl->internal->getMethod('__construct');
		$token = $rfl->token->getMethod('__construct');

		$this->assertEquals($internal->isConstructor(), $token->isConstructor());
		$this->assertTrue($token->isConstructor());
		$this->assertEquals($internal->isDestructor(), $token->isDestructor());
		$this->assertFalse($token->isDestructor());

		$internal = $rfl->internal->getMethod('__destruct');
		$token = $rfl->token->getMethod('__destruct');

		$this->assertEquals($internal->isConstructor(), $token->isConstructor());
		$this->assertFalse($token->isConstructor());
		$this->assertEquals($internal->isDestructor(), $token->isDestructor());
		$this->assertTrue($token->isDestructor());

		$rfl = $this->getClassReflection('namedConstructor');

		$internal = $rfl->internal->getMethod($this->getClassName('namedConstructor'));
		$token = $rfl->token->getMethod($this->getClassName('namedConstructor'));

		$this->assertEquals($internal->isConstructor(), $token->isConstructor());
		$this->assertTrue($token->isConstructor());

		require_once $this->getFilePath('namedConstructorInNamespace');
		$this->getBroker()->processFile($this->getFilePath('namedConstructorInNamespace'));

		$class = new \ReflectionClass('TokenReflection\Test\MethodNamedConstructor');
		$internal = $class->getMethod('MethodNamedConstructor');
		$token = $this->getBroker()->getClass('TokenReflection\Test\MethodNamedConstructor')->getMethod('MethodNamedConstructor');

		$this->assertEquals($internal->isConstructor(), $token->isConstructor());
		$this->assertFalse($token->isConstructor());
	}

	public function testClone()
	{
		$rfl = $this->getClassReflection('clone');

		$this->assertEquals($rfl->internal->getMethod('__clone')->getModifiers(), $rfl->token->getMethod('__clone')->getModifiers());
		$this->assertEquals($rfl->internal->getMethod('noClone')->getModifiers(), $rfl->token->getMethod('noClone')->getModifiers());
	}

	public function testDeclaringClass()
	{
		$rfl = $this->getClassReflection('declaringClass');

		foreach (array('parent' => 'Parent', 'child' => '', 'parentOverlay' => '') as $method => $class) {
			$internal = $rfl->internal->getMethod($method);
			$token = $rfl->token->getMethod($method);

			$this->assertEquals($internal->getDeclaringClass()->getName(), $token->getDeclaringClass()->getName());
			$this->assertEquals('TokenReflection_Test_MethodDeclaringClass' .  $class, $token->getDeclaringClass()->getName());
			$this->assertEquals('TokenReflection_Test_MethodDeclaringClass' .  $class, $token->getDeclaringClassName());
			$this->assertEquals('TokenReflection_Test_MethodDeclaringClass' .  $class, $token->getClass());
			$this->assertInstanceOf('TokenReflection\ReflectionClass', $token->getDeclaringClass());
		}
	}

	public function testModifiers()
	{
		$rfl = $this->getClassReflection('modifiers');

		foreach (array('public', 'protected', 'private') as $name) {
			$abstractName = $name . 'Abstract';
			$finalName = $name . 'Final';
			$staticName = $name . 'Static';

			$method = 'is' . ucfirst($name);
			$oppositeMethod = 'private' === $name ? 'isPublic' : 'isPrivate';

			$internal = $rfl->internal->getMethod($name . 'NoStatic');
			$token = $rfl->token->getMethod($name . 'NoStatic');

			$this->assertEquals($internal->$method(), $internal->$method());
			$this->assertTrue($token->$method());
			$this->assertEquals($internal->$oppositeMethod(), $internal->$oppositeMethod());
			$this->assertFalse($token->$oppositeMethod());
			$this->assertEquals($internal->isStatic(), $internal->isStatic());
			$this->assertFalse($token->isStatic());
			$this->assertEquals($internal->isFinal(), $internal->isFinal());
			$this->assertFalse($token->isFinal());
			$this->assertEquals($internal->isAbstract(), $internal->isAbstract());
			$this->assertFalse($token->isAbstract());
			$this->assertEquals($internal->getModifiers(), $token->getModifiers());
			$this->assertGreaterThan(0, $token->getModifiers() & constant('\ReflectionMethod::IS_' . strtoupper($name)));

			if ('private' !== $name) {
				$internal = $rfl->internal->getMethod($abstractName);
				$token = $rfl->token->getMethod($abstractName);

				$this->assertEquals($internal->$method(), $internal->$method());
				$this->assertTrue($token->$method());
				$this->assertEquals($internal->$oppositeMethod(), $internal->$oppositeMethod());
				$this->assertFalse($token->$oppositeMethod());
				$this->assertEquals($internal->isStatic(), $internal->isStatic());
				$this->assertFalse($token->isStatic());
				$this->assertEquals($internal->isFinal(), $internal->isFinal());
				$this->assertFalse($token->isFinal());
				$this->assertEquals($internal->isAbstract(), $internal->isAbstract());
				$this->assertTrue($token->isAbstract());
				$this->assertEquals($internal->getModifiers(), $token->getModifiers());
				$this->assertGreaterThan(0, $token->getModifiers() & constant('\ReflectionMethod::IS_' . strtoupper($name)));
				$this->assertGreaterThan(0, $token->getModifiers() & \ReflectionMethod::IS_ABSTRACT);
			}

			$internal = $rfl->internal->getMethod($finalName);
			$token = $rfl->token->getMethod($finalName);

			$this->assertEquals($internal->$method(), $internal->$method());
			$this->assertTrue($token->$method());
			$this->assertEquals($internal->$oppositeMethod(), $internal->$oppositeMethod());
			$this->assertFalse($token->$oppositeMethod());
			$this->assertEquals($internal->isStatic(), $internal->isStatic());
			$this->assertFalse($token->isStatic());
			$this->assertEquals($internal->isFinal(), $internal->isFinal());
			$this->assertTrue($token->isFinal());
			$this->assertEquals($internal->isAbstract(), $internal->isAbstract());
			$this->assertFalse($token->isAbstract());
			$this->assertEquals($internal->getModifiers(), $token->getModifiers());
			$this->assertGreaterThan(0, $token->getModifiers() & constant('\ReflectionMethod::IS_' . strtoupper($name)));
			$this->assertGreaterThan(0, $token->getModifiers() & \ReflectionMethod::IS_FINAL);

			$internal = $rfl->internal->getMethod($staticName);
			$token = $rfl->token->getMethod($staticName);

			$this->assertEquals($internal->$method(), $internal->$method());
			$this->assertTrue($token->$method());
			$this->assertEquals($internal->$oppositeMethod(), $internal->$oppositeMethod());
			$this->assertFalse($token->$oppositeMethod());
			$this->assertEquals($internal->isStatic(), $internal->isStatic());
			$this->assertTrue($token->isStatic());
			$this->assertEquals($internal->isFinal(), $internal->isFinal());
			$this->assertFalse($token->isFinal());
			$this->assertEquals($internal->isAbstract(), $internal->isAbstract());
			$this->assertFalse($token->isAbstract());
			$this->assertGreaterThan(0, $token->getModifiers() & constant('\ReflectionMethod::IS_' . strtoupper($name)));
			$this->assertGreaterThan(0, $token->getModifiers() & \ReflectionMethod::IS_STATIC);
		}

		// Shadow
		$rfl = $this->getMethodReflection('shadow');
		$this->assertEquals($rfl->internal->getModifiers(), $rfl->token->getModifiers());

		$rfl = new \stdClass();
		$rfl->internal = new \ReflectionMethod('TokenReflection_Test_MethodShadowParent', 'shadow');
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_MethodShadowParent')->getMethod('shadow');
		$this->assertEquals($rfl->internal->getModifiers(), $rfl->token->getModifiers());

		// Access level
		$rfl = $this->getClassReflection('accessLevel');
		foreach(array('private', 'protected') as $method) {
			$extended = $method . 'Extended';
			$noExtended = $method . 'NoExtended';

			$this->assertEquals($rfl->internal->getMethod($extended)->getModifiers(), $rfl->token->getMethod($extended)->getModifiers(), $method);
			$this->assertEquals($rfl->internal->getMethod($noExtended)->getModifiers(), $rfl->token->getMethod($noExtended)->getModifiers(), $method);
		}

		// Abstract implemented
		$rfl = $this->getMethodReflection('abstractImplemented');
		$this->assertEquals($rfl->internal->getModifiers(), $rfl->token->getModifiers());
	}

	public function testUserDefined()
	{
		$rfl = $this->getMethodReflection('userDefined');

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
		$class = new \ReflectionClass('Exception');
		$rfl->internal = $class->getMethod('getMessage');
		$rfl->token = $this->getBroker()->getClass('Exception')->getMethod('getMessage');

		$this->assertEquals($rfl->internal->isUserDefined(), $rfl->token->isUserDefined());
		$this->assertFalse($rfl->token->isUserDefined());
		$this->assertEquals($rfl->internal->getFileName(), $rfl->token->getFileName());
		$this->assertFalse($rfl->token->getFileName());
		$this->assertEquals($rfl->internal->isInternal(), $rfl->token->isInternal());
		$this->assertTrue($rfl->token->isInternal());

		$this->assertEquals($rfl->internal->getExtension(), $rfl->token->getExtension());
		$this->assertEquals($rfl->internal->getExtensionName(), $rfl->token->getExtensionName());
		$this->assertEquals('Core', $rfl->token->getExtensionName());
	}

	public function testInNamespace()
	{
		require_once $this->getFilePath('inNamespace');
		$this->getBroker()->processFile($this->getFilePath('inNamespace'));

		$rfl = new \stdClass();
		$class = new \ReflectionClass('TokenReflection\Test\MethodInNamespace');
		$rfl->internal = $class->getMethod('inNamespace');
		$rfl->token = $this->getBroker()->getClass('TokenReflection\Test\MethodInNamespace')->getMethod('inNamespace');

		$this->assertEquals($rfl->internal->inNamespace(), $rfl->token->inNamespace());
		$this->assertFalse($rfl->token->inNamespace());
		$this->assertEquals($rfl->internal->getNamespaceName(), $rfl->token->getNamespaceName());
		$this->assertEquals('', $rfl->token->getNamespaceName());
		$this->assertEquals($rfl->internal->getName(), $rfl->token->getName());
		$this->assertEquals('inNamespace', $rfl->token->getName());
		$this->assertEquals($rfl->internal->getShortName(), $rfl->token->getShortName());
		$this->assertEquals('inNamespace', $rfl->token->getShortName());

		$rfl = $this->getMethodReflection('noNamespace');
		$this->assertEquals($rfl->internal->inNamespace(), $rfl->token->inNamespace());
		$this->assertFalse($rfl->token->inNamespace());
		$this->assertEquals($rfl->internal->getNamespaceName(), $rfl->token->getNamespaceName());
		$this->assertEquals('', $rfl->token->getNamespaceName());
		$this->assertEquals($rfl->internal->getName(), $rfl->token->getName());
		$this->assertEquals($this->getMethodName('noNamespace'), $rfl->token->getName());
		$this->assertEquals($rfl->internal->getShortName(), $rfl->token->getShortName());
		$this->assertEquals($this->getMethodName('noNamespace'), $rfl->token->getShortName());
	}

	public function testReference()
	{
		$rfl = $this->getMethodReflection('reference');
		$this->assertEquals($rfl->internal->returnsReference(), $rfl->token->returnsReference());
		$this->assertTrue($rfl->token->returnsReference());

		$rfl = $this->getMethodReflection('noReference');
		$this->assertEquals($rfl->internal->returnsReference(), $rfl->token->returnsReference());
		$this->assertFalse($rfl->token->returnsReference());
	}

	public function testParameters()
	{
		$rfl = $this->getMethodReflection('parameters');
		$this->assertEquals($rfl->internal->getNumberOfParameters(), $rfl->token->getNumberOfParameters());
		$this->assertEquals(3, $rfl->token->getNumberOfParameters());
		$this->assertEquals($rfl->internal->getNumberOfRequiredParameters(), $rfl->token->getNumberOfRequiredParameters());
		$this->assertEquals(2, $rfl->token->getNumberOfRequiredParameters());

		$this->assertEquals(count($rfl->internal->getParameters()), count($rfl->token->getParameters()));
		$internalParameters = $rfl->internal->getParameters();
		$tokenParameters = $rfl->token->getParameters();
		for ($i = 0; $i < count($internalParameters); $i++) {
			$this->assertEquals($internalParameters[$i]->getName(), $tokenParameters[$i]->getName());
			$this->assertInstanceOf('TokenReflection\ReflectionParameter', $tokenParameters[$i]);
		}

		$rfl = $this->getMethodReflection('noParameters');
		$this->assertEquals($rfl->internal->getNumberOfParameters(), $rfl->token->getNumberOfParameters());
		$this->assertEquals(0, $rfl->token->getNumberOfParameters());
		$this->assertEquals($rfl->internal->getNumberOfRequiredParameters(), $rfl->token->getNumberOfRequiredParameters());
		$this->assertEquals(0, $rfl->token->getNumberOfRequiredParameters());
		$this->assertEquals($rfl->internal->getParameters(), $rfl->token->getParameters());
		$this->assertEquals(array(), $rfl->token->getParameters());
	}

	public function testInvoke()
	{
		$rfl = $this->getClassReflection('invoke');

		$className = $this->getClassName('invoke');
		$object = new $className();

		$internal = $rfl->internal->getMethod('publicInvoke');
		$token = $rfl->token->getMethod('publicInvoke');

		$this->assertEquals($internal->invoke($object, 1, 2), $token->invoke($object, 1, 2));
		$this->assertEquals(3, $token->invoke($object, 1, 2));
		$this->assertEquals($internal->invokeArgs($object, array(1, 2)), $token->invokeArgs($object, array(1, 2)));
		$this->assertEquals(3, $token->invokeArgs($object, array(1, 2)));

		$this->assertEquals($internal->setAccessible(false), $token->setAccessible(false));
		$this->assertEquals($internal->invoke($object, 1, 2), $token->invoke($object, 1, 2));

		try {
			$token->invoke(new \Exception(), 1, 2);
			$this->fail('Expected exception InvalidArgumentException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('InvalidArgumentException', $e);
		}

		try {
			$token->invokeArgs(new \Exception(), array(1, 2));
			$this->fail('Expected exception InvalidArgumentException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('InvalidArgumentException', $e);
		}

		$internal = $rfl->internal->getMethod('protectedInvoke');
		$token = $rfl->token->getMethod('protectedInvoke');

		try {
			$token->invoke($object, 1, 2);
			$this->fail('Expected exception RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('RuntimeException', $e);
		}

		try {
			$token->invokeArgs($object, array(1, 2));
			$this->fail('Expected exception RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('RuntimeException', $e);
		}

		$this->assertEquals($internal->setAccessible(true), $token->setAccessible(true));
		$this->assertEquals($internal->invoke($object, 1, 2), $token->invoke($object, 1, 2));
		$this->assertEquals(3, $token->invoke($object, 1, 2));
		$this->assertEquals($internal->invokeArgs($object, array(1, 2)), $token->invokeArgs($object, array(1, 2)));
		$this->assertEquals(3, $token->invokeArgs($object, array(1, 2)));
	}

	public function testPrototype()
	{
		$rfl = $this->getMethodReflection('prototype');
		$this->assertEquals($rfl->internal->getPrototype()->getName(), $rfl->internal->getPrototype()->getName());
		$this->assertEquals($rfl->internal->getPrototype()->getDeclaringClass()->getName(), $rfl->internal->getPrototype()->getDeclaringClass()->getName());
		$this->assertInstanceOf('TokenReflection\ReflectionMethod', $rfl->token->getPrototype());

		$rfl = $this->getMethodReflection('noPrototype');

		try {
			$rfl->token->getPrototype();
			$this->fail('Expected exception TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}
	}
}
