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

use ReflectionMethod as InternalReflectionMethod;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Method test.
 */
class ReflectionMethodTest extends Test
{
	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type = 'method';

	/**
	 * Tests getting of start and end line.
	 */
	public function testLines()
	{
		$rfl = $this->getMethodReflection('lines');
		$this->assertSame($rfl->internal->getStartLine(), $rfl->token->getStartLine());
		$this->assertSame(5, $rfl->token->getStartLine());
		$this->assertSame($rfl->internal->getEndLine(), $rfl->token->getEndLine());
		$this->assertSame(7, $rfl->token->getEndLine());
	}

	/**
	 * Tests getting of documentation comment.
	 */
	public function testComment()
	{
		$rfl = $this->getMethodReflection('docComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame("/**\n\t * This is a method.\n\t */", $rfl->token->getDocComment());

		$rfl = $this->getMethodReflection('noComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertFalse($rfl->token->getDocComment());
	}

	/**
	 * Tests getting of copydoc documentation comment.
	 */
	public function testCommentCopydoc()
	{
		static $methods = array(
			'method' => 'This is a method.',
			'method2' => 'This is a method.',
			'method3' => 'This is a method.',
			'method4' => 'This is a method.',
			'method5' => 'This is a method.',
			'method6' => null,
			'method7' => null
		);

		$class = $this->getClassTokenReflection('docCommentCopydoc');
		foreach ($methods as $methodName => $shortDescription) {
			$this->assertTrue($class->hasMethod($methodName), $methodName);
			$this->assertSame($shortDescription, $class->getMethod($methodName)->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION), $methodName);
		}
	}

	/**
	 * Tests getting of inherited documentation comment.
	 */
	public function testDocCommentInheritance()
	{
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

		$this->assertSame($grandParent->token->getMethod('method1')->getAnnotation('throws'), $parent->token->getMethod('method1')->getAnnotation('throws'));
		$this->assertSame($grandParent->token->getMethod('method1')->getAnnotation('throws'), $rfl->token->getMethod('method1')->getAnnotation('throws'));
		$this->assertSame(array('Exception'), $grandParent->token->getMethod('method1')->getAnnotation('throws'));
		$this->assertSame(array('string'), $parent->token->getMethod('method1')->getAnnotation('return'));

		$this->assertSame($grandParent->token->getMethod('method2')->getAnnotation('return'), $parent->token->getMethod('method2')->getAnnotation('return'));
		$this->assertSame($parent->token->getMethod('method2')->getAnnotation('return'), $rfl->token->getMethod('method2')->getAnnotation('return'));
		$this->assertSame(array('mixed'), $parent->token->getMethod('method2')->getAnnotation('return'));

		$this->assertSame($parent->token->getMethod('method3')->getAnnotation('return'), $rfl->token->getMethod('method3')->getAnnotation('return'));
		$this->assertSame(array('boolean'), $rfl->token->getMethod('method3')->getAnnotation('return'));
	}

	/**
	 * Tests getting of static variables.
	 */
	public function testStaticVariables()
	{
		static $testName = 'staticVariables';

		$rfl = $this->getMethodReflection($testName);

		$this->assertSame($rfl->internal->getStaticVariables(), $rfl->token->getStaticVariables());
		$this->assertSame(
			array(
				'string' => 'string',
				'integer' => 1,
				'float' => 1.1,
				'boolean' => true,
				'null' => null,
				'array' => array(1 => 1),
				'array2' => array(1 => 1, 2 => 2),
				'constants' => array('self constant', 'parent constant')
			),
			$rfl->token->getStaticVariables()
		);

		// The same test with parsing method bodies turned off
		$broker = new Broker(new Broker\Backend\Memory(), Broker::OPTION_DEFAULT & ~Broker::OPTION_PARSE_FUNCTION_BODY);
		$broker->processFile($this->getFilePath($testName));
		$reflection = $broker->getClass($this->getClassName($testName))->getMethod($this->getMethodName($testName));
		$this->assertSame(array(), $reflection->getStaticVariables());
	}

	/**
	 * Tests if method is a closure.
	 */
	public function testClosure()
	{
		$rfl = $this->getMethodReflection('noClosure');
		$this->assertSame($rfl->internal->isClosure(), $rfl->token->isClosure());
		$this->assertFalse($rfl->token->isClosure());
	}

	/**
	 * Tests if method is deprecated.
	 */
	public function testDeprecated()
	{
		$rfl = $this->getMethodReflection('noDeprecated');
		$this->assertSame($rfl->internal->isDeprecated(), $rfl->token->isDeprecated());
		$this->assertFalse($rfl->token->isDeprecated());
	}

	/**
	 * Tests if method is constructor or destructor.
	 */
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
		if (PHP_VERSION_ID >= 50303) {
			$this->assertFalse($token->isConstructor());
		} else {
			$this->assertTrue($token->isConstructor());
		}
	}

	/**
	 * Tests if method can clone.
	 */
	public function testClone()
	{
		if (PHP_VERSION_ID >= 50400) {
			// @todo investigate http://svn.php.net/viewvc/php/php-src/trunk/Zend/zend_compile.h?revision=306938&view=markup#l199
			$this->markTestSkipped();
		}

		$rfl = $this->getClassReflection('clone');

		$this->assertSame($rfl->internal->getMethod('__clone')->getModifiers(), $rfl->token->getMethod('__clone')->getModifiers());
		$this->assertSame($rfl->internal->getMethod('noClone')->getModifiers(), $rfl->token->getMethod('noClone')->getModifiers());
	}

	/**
	 * Tests getting of declaring class.
	 */
	public function testDeclaringClass()
	{
		$rfl = $this->getClassReflection('declaringClass');

		foreach (array('parent' => 'Parent', 'child' => '', 'parentOverlay' => '') as $method => $class) {
			$internal = $rfl->internal->getMethod($method);
			$token = $rfl->token->getMethod($method);

			$this->assertSame($internal->getDeclaringClass()->getName(), $token->getDeclaringClass()->getName());
			$this->assertSame('TokenReflection_Test_MethodDeclaringClass' .  $class, $token->getDeclaringClass()->getName());
			$this->assertSame('TokenReflection_Test_MethodDeclaringClass' .  $class, $token->getDeclaringClassName());
			$this->assertInstanceOf('TokenReflection\ReflectionClass', $token->getDeclaringClass());
		}
	}

	/**
	 * Tests all method modifiers.
	 */
	public function testModifiers()
	{
		static $classes = array(
			'TokenReflection_Test_MethodModifiersIface',
			'TokenReflection_Test_MethodModifiersParent',
			'TokenReflection_Test_MethodModifiers',
			'TokenReflection_Test_MethodModifiersChild',
			'TokenReflection_Test_MethodModifiersChild2',
			'TokenReflection_Test_MethodModifiersChild3',
			'TokenReflection_Test_MethodModifiersChild4'
		);

		require_once $this->getFilePath('modifiers');
		$this->getBroker()->process($this->getFilePath('modifiers'));

		foreach ($classes as $className) {
			$token = $this->getBroker()->getClass($className);
			$internal = new \ReflectionClass($className);

			foreach ($internal->getMethods() as $method) {
				$this->assertTrue($token->hasMethod($method->getName()), sprintf('%s::%s()', $className, $method->getName()));
				if (PHP_VERSION_ID >= 50400) {
					// @todo investigate http://svn.php.net/viewvc/php/php-src/trunk/Zend/zend_compile.h?revision=306938&view=markup#l199
					continue;
				}
				$this->assertSame($method->getModifiers(), $token->getMethod($method->getName())->getModifiers(), sprintf('%s::%s()', $className, $method->getName()));
			}
		}
	}

	/**
	 * Tests if method is user defined or internal.
	 */
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

	/**
	 * Tests if method is defined in class in namespace.
	 */
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

	/**
	 * Tests if method returns reference.
	 */
	public function testReference()
	{
		$rfl = $this->getMethodReflection('reference');
		$this->assertSame($rfl->internal->returnsReference(), $rfl->token->returnsReference());
		$this->assertTrue($rfl->token->returnsReference());

		$rfl = $this->getMethodReflection('noReference');
		$this->assertSame($rfl->internal->returnsReference(), $rfl->token->returnsReference());
		$this->assertFalse($rfl->token->returnsReference());
	}

	/**
	 * Tests getting of method parameters.
	 */
	public function testParameters()
	{
		$rfl = $this->getMethodReflection('parameters');
		$this->assertSame($rfl->internal->getNumberOfParameters(), $rfl->token->getNumberOfParameters());
		$this->assertSame(3, $rfl->token->getNumberOfParameters());
		$this->assertSame($rfl->internal->getNumberOfRequiredParameters(), $rfl->token->getNumberOfRequiredParameters());
		$this->assertSame(2, $rfl->token->getNumberOfRequiredParameters());

		$this->assertSame(array_keys($rfl->internal->getParameters()), array_keys($rfl->token->getParameters()));
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

	/**
	 * Tests method invoking.
	 */
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

		if (PHP_VERSION_ID >= 50302) {
			$this->assertSame($internal->setAccessible(false), $token->setAccessible(false));
			$this->assertSame($internal->invoke($object, 1, 2), $token->invoke($object, 1, 2));
		}

		try {
			$token->invoke(new \Exception(), 1, 2);
			$this->fail('Expected exception TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}

		try {
			$token->invokeArgs(new \Exception(), array(1, 2));
			$this->fail('Expected exception TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}

		$internal = $rfl->internal->getMethod('protectedInvoke');
		$token = $rfl->token->getMethod('protectedInvoke');

		try {
			$token->invoke($object, 1, 2);
			$this->fail('Expected exception TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}

		try {
			$token->invokeArgs($object, array(1, 2));
			$this->fail('Expected exception TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}

		if (PHP_VERSION_ID >= 50302) {
			$this->assertSame($internal->setAccessible(true), $token->setAccessible(true));
			$this->assertSame($internal->invoke($object, 1, 2), $token->invoke($object, 1, 2));
			$this->assertSame(3, $token->invoke($object, 1, 2));
			$this->assertSame($internal->invokeArgs($object, array(1, 2)), $token->invokeArgs($object, array(1, 2)));
			$this->assertSame(3, $token->invokeArgs($object, array(1, 2)));
		}
	}

	/**
	 * Tests if method has a prototype.
	 */
	public function testPrototype()
	{
		$rfl = $this->getMethodReflection('prototype');
		$this->assertSame($rfl->internal->getPrototype()->getName(), $rfl->internal->getPrototype()->getName());
		$this->assertSame($rfl->internal->getPrototype()->getDeclaringClass()->getName(), $rfl->internal->getPrototype()->getDeclaringClass()->getName());
		$this->assertInstanceOf('TokenReflection\ReflectionMethod', $rfl->token->getPrototype());

		$rfl = $this->getMethodReflection('noPrototype');

		try {
			$rfl->token->getPrototype();
			$this->fail('Expected exception TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}
	}

	/**
	 * Tests export.
	 */
	public function testToString()
	{
		$tests = array(
			'lines', 'docComment', 'noComment',
			'prototype', 'noPrototype', 'parameters', 'reference', 'noReference', 'noClosure', 'noNamespace', 'userDefined', 'shadow'
		);
		foreach ($tests as $test) {
			$rfl = $this->getMethodReflection($test);
			$this->assertSame($rfl->internal->__toString(), $rfl->token->__toString());
			$this->assertSame(InternalReflectionMethod::export($this->getClassName($test), $test, true), ReflectionMethod::export($this->getBroker(), $this->getClassName($test), $test, true));

			$rfl = $this->getMethodReflection($test, true);
			$this->assertSame($rfl->internal->__toString(), $rfl->token->__toString());
			$this->assertSame(InternalReflectionMethod::export($this->getClassName($test), $test, true), ReflectionMethod::export($this->getBroker(), $this->getClassName($test), $test, true));
		}

		$tests = array(
			'constructorDestructor' => array('__construct', '__destruct'),
			'clone' => array('__clone', 'noClone'),
			'declaringClass' => array('parent', 'child', 'parentOverlay'),
			'invoke' => array('publicInvoke', 'protectedInvoke'),
			'accessLevel' => array('privateExtended', 'privateNoExtended', 'protectedExtended', 'protectedNoExtended'),
			'modifiers' => array('publicAbstract', 'publicFinal', 'publicStatic', 'publicNoStatic', 'protectedAbstract', 'protectedFinal', 'protectedStatic', 'protectedNoStatic', 'privateFinal', 'privateStatic', 'privateNoStatic')
		);
		foreach ($tests as $class => $classTests) {
			$rfl = $this->getClassReflection($class);
			$rfl_fromString = $this->getClassReflection($class, true);
			foreach ($classTests as $method) {
				// @todo inherits not supported yet
				$this->assertSame(preg_replace('~, inherits [\w]+~', '', $rfl->internal->getMethod($method)->__toString()), $rfl->token->getMethod($method)->__toString());
				$this->assertSame(preg_replace('~, inherits [\w]+~', '', InternalReflectionMethod::export($this->getClassName($class), $method, true)), ReflectionMethod::export($this->getBroker(), $this->getClassName($class), $method, true));
				$this->assertSame(preg_replace('~, inherits [\w]+~', '', $rfl_fromString->internal->getMethod($method)->__toString()), $rfl_fromString->token->getMethod($method)->__toString());
			}
		}

		$this->assertSame(InternalReflectionMethod::export('ReflectionMethod', 'isFinal', true), ReflectionMethod::export($this->getBroker(), 'ReflectionMethod', 'isFinal', true));
		$this->assertSame(InternalReflectionMethod::export(new InternalReflectionMethod('ReflectionMethod', 'isFinal'), 'isFinal', true), ReflectionMethod::export($this->getBroker(), new InternalReflectionMethod('ReflectionMethod', 'isFinal'), 'isFinal', true));
	}

	/**
	 * Tests new PHP 5.4 features.
	 */
	public function test54features()
	{
		if (PHP_VERSION_ID < 50400) {
			$this->markTestSkipped('Tested only on PHP 5.4+');
		}

		$rfl = $this->getMethodReflection('features54');

		$this->assertSame($rfl->internal->getStaticVariables(), $rfl->token->getStaticVariables());
		$this->assertSame(
			array(
				'one' => array(),
				'two' => array(array(1), '2', array(array(array(array(true))))),
				'three' => 21
			),
			$rfl->token->getStaticVariables()
		);
	}

	/**
	 * Tests an exception thrown when trying to create the reflection from a PHP internal reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalMethodReflectionCreate()
	{
		Php\ReflectionExtension::create(new \ReflectionClass('Exception'), $this->getBroker());
	}

	/**
	 * Tests an exception thrown when trying to get a non-existent parameter.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalMethodGetParameter1()
	{
		$this->getInternalMethodReflection()->getParameter('~non-existent~');
	}

	/**
	 * Tests an exception thrown when trying to get a non-existent parameter.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalMethodGetParameter2()
	{
		$this->getInternalMethodReflection()->getParameter(999);
	}

	/**
	 * Returns an internal method reflection.
	 *
	 * @return \TokenReflection\Php\ReflectionMethod
	 */
	private function getInternalMethodReflection()
	{
		return $this->getBroker()->getClass('Exception')->getConstructor();
	}
}
