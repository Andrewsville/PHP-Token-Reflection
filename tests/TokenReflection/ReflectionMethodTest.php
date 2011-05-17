<?php

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

class ReflectionMethodTest extends Test
{
	protected $type = 'method';

	public function testLines()
	{
		$rfl = $this->getMethodReflection('lines');
		$this->assertSame($rfl->internal->getStartLine(), $rfl->token->getStartLine());
		$this->assertSame(5, $rfl->token->getStartLine());
		$this->assertSame($rfl->internal->getEndLine(), $rfl->token->getEndLine());
		$this->assertSame(7, $rfl->token->getEndLine());
	}

	public function testComment()
	{
		$rfl = $this->getMethodReflection('docComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame("/**\n\t * This is a method.\n\t */", $rfl->token->getDocComment());

		$rfl = $this->getMethodReflection('noComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertFalse($rfl->token->getDocComment());
	}

	public function testDocCommentInheritance()
	{
		require_once $this->getFilePath('docCommentInheritance');
		$this->getBroker()->processFile($this->getFilePath('docCommentInheritance'));

		$grandParent = new \stdClass();
		$grandParent->token = $this->getBroker()->getClass('TokenReflection_Test_MethodDocCommentInheritanceGrandParent');

		$parent = new \stdClass();
		$parent->token = $this->getBroker()->getClass('TokenReflection_Test_MethodDocCommentInheritanceParent');

		$rfl = new \stdClass();
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_MethodDocCommentInheritance');

		$this->assertSame($parent->token->getMethod('method1')->getAnnotations(), $rfl->token->getMethod('method1')->getAnnotations());
		$this->assertSame('Private1 short. Protected1 short.', $rfl->token->getMethod('method1')->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));
		$this->assertSame('Protected1 long. Private1 long.', $rfl->token->getMethod('method1')->getAnnotation(ReflectionAnnotation::LONG_DESCRIPTION));

		$this->assertSame($parent->token->getMethod('method2')->getAnnotations(), $rfl->token->getMethod('method2')->getAnnotations());
		$this->assertSame($grandParent->token->getMethod('method2')->getAnnotations(), $rfl->token->getMethod('method2')->getAnnotations());

		$this->assertSame('Public3 Protected3  short.', $rfl->token->getMethod('method3')->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));
		$this->assertNull($rfl->token->getMethod('method3')->getAnnotation(ReflectionAnnotation::LONG_DESCRIPTION));

		$this->assertSame(array(), $rfl->token->getMethod('method4')->getAnnotations());
		$this->assertNull($rfl->token->getMethod('method4')->getAnnotation(ReflectionAnnotation::LONG_DESCRIPTION));
	}

	public function testStaticVariables()
	{
		/**
		 * @todo
		 */
		return;

		$rfl = $this->getMethodReflection('staticVariables');

		$this->assertSame($rfl->internal->getStaticVariables(), $rfl->token->getStaticVariables());
		$this->assertSame(array('string' => 'string', 'integer' => 1, 'float' => 1.1, 'boolean' => true, 'null' => null, 'array' => array(1 => 1)), $rfl->token->getStaticVariables());
	}

	public function testClosure()
	{
		$rfl = $this->getMethodReflection('noClosure');
		$this->assertSame($rfl->internal->isClosure(), $rfl->token->isClosure());
		$this->assertFalse($rfl->token->isClosure());
	}

	public function testDeprecated()
	{
		$rfl = $this->getMethodReflection('noDeprecated');
		$this->assertSame($rfl->internal->isDeprecated(), $rfl->token->isDeprecated());
		$this->assertFalse($rfl->token->isDeprecated());
	}

	public function testConstructorDestructor()
	{
		$rfl = $this->getClassReflection('constructorDestructor');

		$internal = $rfl->internal->getMethod('__construct');
		$token = $rfl->token->getMethod('__construct');

		$this->assertSame($internal->isConstructor(), $token->isConstructor());
		$this->assertTrue($token->isConstructor());
		$this->assertSame($internal->isDestructor(), $token->isDestructor());
		$this->assertFalse($token->isDestructor());

		$internal = $rfl->internal->getMethod('__destruct');
		$token = $rfl->token->getMethod('__destruct');

		$this->assertSame($internal->isConstructor(), $token->isConstructor());
		$this->assertFalse($token->isConstructor());
		$this->assertSame($internal->isDestructor(), $token->isDestructor());
		$this->assertTrue($token->isDestructor());

		$rfl = $this->getClassReflection('namedConstructor');

		$internal = $rfl->internal->getMethod($this->getClassName('namedConstructor'));
		$token = $rfl->token->getMethod($this->getClassName('namedConstructor'));

		$this->assertSame($internal->isConstructor(), $token->isConstructor());
		$this->assertTrue($token->isConstructor());

		require_once $this->getFilePath('namedConstructorInNamespace');
		$this->getBroker()->processFile($this->getFilePath('namedConstructorInNamespace'));

		$class = new \ReflectionClass('TokenReflection\Test\MethodNamedConstructor');
		$internal = $class->getMethod('MethodNamedConstructor');
		$token = $this->getBroker()->getClass('TokenReflection\Test\MethodNamedConstructor')->getMethod('MethodNamedConstructor');

		$this->assertSame($internal->isConstructor(), $token->isConstructor());
		$this->assertFalse($token->isConstructor());
	}

	public function testClone()
	{
		$rfl = $this->getClassReflection('clone');

		$this->assertSame($rfl->internal->getMethod('__clone')->getModifiers(), $rfl->token->getMethod('__clone')->getModifiers());
		$this->assertSame($rfl->internal->getMethod('noClone')->getModifiers(), $rfl->token->getMethod('noClone')->getModifiers());
	}

	public function testDeclaringClass()
	{
		$rfl = $this->getClassReflection('declaringClass');

		foreach (array('parent' => 'Parent', 'child' => '', 'parentOverlay' => '') as $method => $class) {
			$internal = $rfl->internal->getMethod($method);
			$token = $rfl->token->getMethod($method);

			$this->assertSame($internal->getDeclaringClass()->getName(), $token->getDeclaringClass()->getName());
			$this->assertSame('TokenReflection_Test_MethodDeclaringClass' .  $class, $token->getDeclaringClass()->getName());
			$this->assertSame('TokenReflection_Test_MethodDeclaringClass' .  $class, $token->getDeclaringClassName());
			$this->assertSame('TokenReflection_Test_MethodDeclaringClass' .  $class, $token->getClass());
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

			$this->assertSame($internal->$method(), $internal->$method());
			$this->assertTrue($token->$method());
			$this->assertSame($internal->$oppositeMethod(), $internal->$oppositeMethod());
			$this->assertFalse($token->$oppositeMethod());
			$this->assertSame($internal->isStatic(), $internal->isStatic());
			$this->assertFalse($token->isStatic());
			$this->assertSame($internal->isFinal(), $internal->isFinal());
			$this->assertFalse($token->isFinal());
			$this->assertSame($internal->isAbstract(), $internal->isAbstract());
			$this->assertFalse($token->isAbstract());
			$this->assertSame($internal->getModifiers(), $token->getModifiers());
			$this->assertGreaterThan(0, $token->getModifiers() & constant('\ReflectionMethod::IS_' . strtoupper($name)));

			if ('private' !== $name) {
				$internal = $rfl->internal->getMethod($abstractName);
				$token = $rfl->token->getMethod($abstractName);

				$this->assertSame($internal->$method(), $internal->$method());
				$this->assertTrue($token->$method());
				$this->assertSame($internal->$oppositeMethod(), $internal->$oppositeMethod());
				$this->assertFalse($token->$oppositeMethod());
				$this->assertSame($internal->isStatic(), $internal->isStatic());
				$this->assertFalse($token->isStatic());
				$this->assertSame($internal->isFinal(), $internal->isFinal());
				$this->assertFalse($token->isFinal());
				$this->assertSame($internal->isAbstract(), $internal->isAbstract());
				$this->assertTrue($token->isAbstract());
				$this->assertSame($internal->getModifiers(), $token->getModifiers());
				$this->assertGreaterThan(0, $token->getModifiers() & constant('\ReflectionMethod::IS_' . strtoupper($name)));
				$this->assertGreaterThan(0, $token->getModifiers() & \ReflectionMethod::IS_ABSTRACT);
			}

			$internal = $rfl->internal->getMethod($finalName);
			$token = $rfl->token->getMethod($finalName);

			$this->assertSame($internal->$method(), $internal->$method());
			$this->assertTrue($token->$method());
			$this->assertSame($internal->$oppositeMethod(), $internal->$oppositeMethod());
			$this->assertFalse($token->$oppositeMethod());
			$this->assertSame($internal->isStatic(), $internal->isStatic());
			$this->assertFalse($token->isStatic());
			$this->assertSame($internal->isFinal(), $internal->isFinal());
			$this->assertTrue($token->isFinal());
			$this->assertSame($internal->isAbstract(), $internal->isAbstract());
			$this->assertFalse($token->isAbstract());
			$this->assertSame($internal->getModifiers(), $token->getModifiers());
			$this->assertGreaterThan(0, $token->getModifiers() & constant('\ReflectionMethod::IS_' . strtoupper($name)));
			$this->assertGreaterThan(0, $token->getModifiers() & \ReflectionMethod::IS_FINAL);

			$internal = $rfl->internal->getMethod($staticName);
			$token = $rfl->token->getMethod($staticName);

			$this->assertSame($internal->$method(), $internal->$method());
			$this->assertTrue($token->$method());
			$this->assertSame($internal->$oppositeMethod(), $internal->$oppositeMethod());
			$this->assertFalse($token->$oppositeMethod());
			$this->assertSame($internal->isStatic(), $internal->isStatic());
			$this->assertTrue($token->isStatic());
			$this->assertSame($internal->isFinal(), $internal->isFinal());
			$this->assertFalse($token->isFinal());
			$this->assertSame($internal->isAbstract(), $internal->isAbstract());
			$this->assertFalse($token->isAbstract());
			$this->assertGreaterThan(0, $token->getModifiers() & constant('\ReflectionMethod::IS_' . strtoupper($name)));
			$this->assertGreaterThan(0, $token->getModifiers() & \ReflectionMethod::IS_STATIC);
		}

		// Shadow
		$rfl = $this->getMethodReflection('shadow');
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());

		$rfl = new \stdClass();
		$rfl->internal = new \ReflectionMethod('TokenReflection_Test_MethodShadowParent', 'shadow');
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_MethodShadowParent')->getMethod('shadow');
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());

		// Access level
		$rfl = $this->getClassReflection('accessLevel');
		foreach(array('private', 'protected') as $method) {
			$extended = $method . 'Extended';
			$noExtended = $method . 'NoExtended';

			$this->assertSame($rfl->internal->getMethod($extended)->getModifiers(), $rfl->token->getMethod($extended)->getModifiers(), $method);
			$this->assertSame($rfl->internal->getMethod($noExtended)->getModifiers(), $rfl->token->getMethod($noExtended)->getModifiers(), $method);
		}

		// Abstract implemented
		$rfl = $this->getMethodReflection('abstractImplemented');
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
	}

	public function testUserDefined()
	{
		$rfl = $this->getMethodReflection('userDefined');

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
		$class = new \ReflectionClass('Exception');
		$rfl->internal = $class->getMethod('getMessage');
		$rfl->token = $this->getBroker()->getClass('Exception')->getMethod('getMessage');

		$this->assertSame($rfl->internal->isUserDefined(), $rfl->token->isUserDefined());
		$this->assertFalse($rfl->token->isUserDefined());
		$this->assertSame($rfl->internal->getFileName(), $rfl->token->getFileName());
		$this->assertFalse($rfl->token->getFileName());
		$this->assertSame($rfl->internal->isInternal(), $rfl->token->isInternal());
		$this->assertTrue($rfl->token->isInternal());

		$this->assertEquals($rfl->internal->getExtension(), $rfl->token->getExtension());
		$this->assertSame($rfl->internal->getExtensionName(), $rfl->token->getExtensionName());
		$this->assertSame('Core', $rfl->token->getExtensionName());
	}

	public function testInNamespace()
	{
		require_once $this->getFilePath('inNamespace');
		$this->getBroker()->processFile($this->getFilePath('inNamespace'));

		$rfl = new \stdClass();
		$class = new \ReflectionClass('TokenReflection\Test\MethodInNamespace');
		$rfl->internal = $class->getMethod('inNamespace');
		$rfl->token = $this->getBroker()->getClass('TokenReflection\Test\MethodInNamespace')->getMethod('inNamespace');

		$this->assertSame($rfl->internal->inNamespace(), $rfl->token->inNamespace());
		$this->assertFalse($rfl->token->inNamespace());
		$this->assertSame($rfl->internal->getNamespaceName(), $rfl->token->getNamespaceName());
		$this->assertSame('', $rfl->token->getNamespaceName());
		$this->assertSame($rfl->internal->getName(), $rfl->token->getName());
		$this->assertSame('inNamespace', $rfl->token->getName());
		$this->assertSame($rfl->internal->getShortName(), $rfl->token->getShortName());
		$this->assertSame('inNamespace', $rfl->token->getShortName());

		$rfl = $this->getMethodReflection('noNamespace');
		$this->assertSame($rfl->internal->inNamespace(), $rfl->token->inNamespace());
		$this->assertFalse($rfl->token->inNamespace());
		$this->assertSame($rfl->internal->getNamespaceName(), $rfl->token->getNamespaceName());
		$this->assertSame('', $rfl->token->getNamespaceName());
		$this->assertSame($rfl->internal->getName(), $rfl->token->getName());
		$this->assertSame($this->getMethodName('noNamespace'), $rfl->token->getName());
		$this->assertSame($rfl->internal->getShortName(), $rfl->token->getShortName());
		$this->assertSame($this->getMethodName('noNamespace'), $rfl->token->getShortName());
	}

	public function testReference()
	{
		$rfl = $this->getMethodReflection('reference');
		$this->assertSame($rfl->internal->returnsReference(), $rfl->token->returnsReference());
		$this->assertTrue($rfl->token->returnsReference());

		$rfl = $this->getMethodReflection('noReference');
		$this->assertSame($rfl->internal->returnsReference(), $rfl->token->returnsReference());
		$this->assertFalse($rfl->token->returnsReference());
	}

	public function testParameters()
	{
		$rfl = $this->getMethodReflection('parameters');
		$this->assertSame($rfl->internal->getNumberOfParameters(), $rfl->token->getNumberOfParameters());
		$this->assertSame(3, $rfl->token->getNumberOfParameters());
		$this->assertSame($rfl->internal->getNumberOfRequiredParameters(), $rfl->token->getNumberOfRequiredParameters());
		$this->assertSame(2, $rfl->token->getNumberOfRequiredParameters());

		$this->assertSame(count($rfl->internal->getParameters()), count($rfl->token->getParameters()));
		$internalParameters = $rfl->internal->getParameters();
		$tokenParameters = $rfl->token->getParameters();
		for ($i = 0; $i < count($internalParameters); $i++) {
			$this->assertSame($internalParameters[$i]->getName(), $tokenParameters[$i]->getName());
			$this->assertInstanceOf('TokenReflection\ReflectionParameter', $tokenParameters[$i]);
		}

		$rfl = $this->getMethodReflection('noParameters');
		$this->assertSame($rfl->internal->getNumberOfParameters(), $rfl->token->getNumberOfParameters());
		$this->assertSame(0, $rfl->token->getNumberOfParameters());
		$this->assertSame($rfl->internal->getNumberOfRequiredParameters(), $rfl->token->getNumberOfRequiredParameters());
		$this->assertSame(0, $rfl->token->getNumberOfRequiredParameters());
		$this->assertSame($rfl->internal->getParameters(), $rfl->token->getParameters());
		$this->assertSame(array(), $rfl->token->getParameters());
	}

	public function testInvoke()
	{
		$rfl = $this->getClassReflection('invoke');

		$className = $this->getClassName('invoke');
		$object = new $className();

		$internal = $rfl->internal->getMethod('publicInvoke');
		$token = $rfl->token->getMethod('publicInvoke');

		$this->assertSame($internal->invoke($object, 1, 2), $token->invoke($object, 1, 2));
		$this->assertSame(3, $token->invoke($object, 1, 2));
		$this->assertSame($internal->invokeArgs($object, array(1, 2)), $token->invokeArgs($object, array(1, 2)));
		$this->assertSame(3, $token->invokeArgs($object, array(1, 2)));

		$this->assertSame($internal->setAccessible(false), $token->setAccessible(false));
		$this->assertSame($internal->invoke($object, 1, 2), $token->invoke($object, 1, 2));

		try {
			$token->invoke(new \Exception(), 1, 2);
			$this->fail('Expected exception TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		try {
			$token->invokeArgs(new \Exception(), array(1, 2));
			$this->fail('Expected exception TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		$internal = $rfl->internal->getMethod('protectedInvoke');
		$token = $rfl->token->getMethod('protectedInvoke');

		try {
			$token->invoke($object, 1, 2);
			$this->fail('Expected exception TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		try {
			$token->invokeArgs($object, array(1, 2));
			$this->fail('Expected exception TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		$this->assertSame($internal->setAccessible(true), $token->setAccessible(true));
		$this->assertSame($internal->invoke($object, 1, 2), $token->invoke($object, 1, 2));
		$this->assertSame(3, $token->invoke($object, 1, 2));
		$this->assertSame($internal->invokeArgs($object, array(1, 2)), $token->invokeArgs($object, array(1, 2)));
		$this->assertSame(3, $token->invokeArgs($object, array(1, 2)));
	}

	public function testPrototype()
	{
		$rfl = $this->getMethodReflection('prototype');
		$this->assertSame($rfl->internal->getPrototype()->getName(), $rfl->internal->getPrototype()->getName());
		$this->assertSame($rfl->internal->getPrototype()->getDeclaringClass()->getName(), $rfl->internal->getPrototype()->getDeclaringClass()->getName());
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
