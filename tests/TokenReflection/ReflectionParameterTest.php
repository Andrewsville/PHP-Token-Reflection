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

use ReflectionParameter as InternalReflectionParameter;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Parameter test.
 */
class ReflectionParameterTest extends Test
{
	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type = 'parameter';

	/**
	 * Tests getting of parameter position.
	 */
	public function testPosition()
	{
		$rfl = $this->getFunctionReflection('position');
		$internalParameters = $rfl->internal->getParameters();
		$tokenParameters = $rfl->token->getParameters();
		for ($i = 0; $i < 3; $i++) {
			$internal = $internalParameters[$i];
			$token = $tokenParameters[$i];

			$this->assertSame($internal->getPosition(), $token->getPosition());
			$this->assertSame($i, $token->getPosition());
		}
	}

	/**
	 * Tests if parameter allows null.
	 */
	public function testAllowsNull()
	{
		foreach (array('Class', 'Array') as $type) {
			$rfl = $this->getParameterReflection('null' . $type);
			$this->assertSame($rfl->internal->allowsNull(), $rfl->token->allowsNull());
			$this->assertTrue($rfl->token->allowsNull());

			$rfl = $this->getParameterReflection('noNull' . $type);
			$this->assertSame($rfl->internal->allowsNull(), $rfl->token->allowsNull());
			$this->assertFalse($rfl->token->allowsNull());
		}
	}

	/**
	 * Tests if parameters is optional.
	 */
	public function testOptional()
	{
		$types = array('null' => null, 'true' => true, 'false' => false, 'array' => array(), 'string' => 'string', 'integer' => 1, 'float' => 1.1, 'constant' => E_NOTICE);
		$definitions = array('null' => 'null', 'true' => 'true', 'false' => 'false', 'array' => 'array()', 'string' => "'string'", 'integer' => '1', 'float' => '1.1', 'constant' => 'E_NOTICE');
		foreach ($types as $type => $value) {
			$rfl = $this->getParameterReflection('optional' . ucfirst($type));
			$this->assertSame($rfl->internal->isOptional(), $rfl->token->isOptional());
			$this->assertTrue($rfl->token->isOptional());
			$this->assertSame($rfl->internal->isDefaultValueAvailable(), $rfl->token->isDefaultValueAvailable());
			$this->assertTrue($rfl->token->isDefaultValueAvailable());
			$this->assertSame($rfl->internal->getDefaultValue(), $rfl->token->getDefaultValue());
			$this->assertSame($value, $rfl->token->getDefaultValue());
			$this->assertSame($definitions[$type], $rfl->token->getDefaultValueDefinition());
		}

		$rfl = $this->getParameterReflection('noOptional');
		$this->assertSame($rfl->internal->isOptional(), $rfl->token->isOptional());
		$this->assertFalse($rfl->token->isOptional());
		$this->assertSame($rfl->internal->isDefaultValueAvailable(), $rfl->token->isDefaultValueAvailable());
		$this->assertFalse($rfl->token->isDefaultValueAvailable());

		try {
			$rfl->token->getDefaultValue();
			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception\RuntimeException', $e);
		}
	}

	/**
	 * Tests if parameter has array type hint.
	 */
	public function testArray()
	{
		$rfl = $this->getParameterReflection('array');
		$this->assertSame($rfl->internal->isArray(), $rfl->token->isArray());
		$this->assertTrue($rfl->token->isArray());

		$rfl = $this->getParameterReflection('noArray');
		$this->assertSame($rfl->internal->isArray(), $rfl->token->isArray());
		$this->assertFalse($rfl->token->isArray());
	}

	/**
	 * Tests if parameter has callback type hint.
	 */
	public function testCallable()
	{
		if (PHP_VERSION_ID < 50400) {
			$this->markTestSkipped('Requires PHP 5.4 or higher.');
		}

		$rfl = $this->getParameterReflection('callable');
		$this->assertSame($rfl->internal->isCallable(), $rfl->token->isCallable());
		$this->assertTrue($rfl->token->isCallable());

		$rfl = $this->getParameterReflection('noCallable');
		$this->assertSame($rfl->internal->isCallable(), $rfl->token->isCallable());
		$this->assertFalse($rfl->token->isCallable());
	}

	/**
	 * Tests if parameter has class type hint.
	 */
	public function testClass()
	{
		$rfl = $this->getParameterReflection('class');
		$this->assertSame($rfl->internal->getClass()->getName(), $rfl->token->getClass()->getName());
		$this->assertSame('Exception', $rfl->token->getClass()->getName());
		$this->assertSame('Exception', $rfl->token->getClassName());
		$this->assertInstanceOf('TokenReflection\IReflectionClass', $rfl->token->getClass());

		$rfl = $this->getParameterReflection('noClass');
		$this->assertSame($rfl->internal->getClass(), $rfl->token->getClass());
		$this->assertNull($rfl->token->getClass());
		$this->assertNull($rfl->token->getClassName());
	}

	/**
	 * Tests if parameter is passed by reference.
	 */
	public function testReference()
	{
		$rfl = $this->getParameterReflection('reference');
		$this->assertSame($rfl->internal->isPassedByReference(), $rfl->token->isPassedByReference());
		$this->assertTrue($rfl->token->isPassedByReference());

		$rfl = $this->getParameterReflection('noReference');
		$this->assertSame($rfl->internal->isPassedByReference(), $rfl->token->isPassedByReference());
		$this->assertFalse($rfl->token->isPassedByReference());
	}

	/**
	 * Tests getting of declaring method or function.
	 */
	public function testDeclaring()
	{
		$rfl = $this->getParameterReflection('declaringFunction');
		$this->assertSame($rfl->internal->getDeclaringFunction()->getName(), $rfl->token->getDeclaringFunction()->getName());
		$this->assertSame($this->getFunctionName('declaringFunction'), $rfl->token->getDeclaringFunction()->getName());
		$this->assertSame($this->getFunctionName('declaringFunction'), $rfl->token->getDeclaringFunctionName());
		$this->assertInstanceOf('TokenReflection\ReflectionFunction', $rfl->token->getDeclaringFunction());

		$this->assertSame($rfl->internal->getDeclaringClass(), $rfl->token->getDeclaringClass());
		$this->assertNull($rfl->token->getDeclaringClass());
		$this->assertNull($rfl->token->getDeclaringClassName());

		$rfl = $this->getMethodReflection('declaringMethod');
		$internalParameters = $rfl->internal->getParameters();
		$internal = $internalParameters[0];
		$tokenParameters = $rfl->token->getParameters();
		$token = $tokenParameters[0];

		$this->assertSame($internal->getDeclaringFunction()->getName(), $token->getDeclaringFunction()->getName());
		$this->assertSame($this->getMethodName('declaringMethod'), $token->getDeclaringFunction()->getName());
		$this->assertSame($this->getMethodName('declaringMethod'), $token->getDeclaringFunctionName());
		$this->assertInstanceOf('TokenReflection\ReflectionMethod', $token->getDeclaringFunction());

		$this->assertSame($internal->getDeclaringClass()->getName(), $token->getDeclaringClass()->getName());
		$this->assertSame($this->getClassName('declaringMethod'), $token->getDeclaringClass()->getName());
		$this->assertSame($this->getClassName('declaringMethod'), $token->getDeclaringClassName());
		$this->assertInstanceOf('TokenReflection\ReflectionClass', $token->getDeclaringClass());
	}

	/**
	 * Tests export.
	 */
	public function testToString()
	{
		$tests = array(
			'declaringFunction', 'reference', 'noReference', 'class', 'noClass', 'array', 'noArray',
			'nullClass', 'noNullClass', 'nullArray', 'noNullArray', 'noOptional',
			'optionalNull', 'optionalTrue', 'optionalFalse', 'optionalArray', 'optionalString', 'optionalInteger', 'optionalFloat', 'optionalConstant'
		);
		foreach ($tests as $test) {
			$rfl = $this->getParameterReflection($test);
			$this->assertSame($rfl->internal->__toString(), $rfl->token->__toString());
			$this->assertSame(InternalReflectionParameter::export($this->getFunctionName($test), 0, true), ReflectionParameter::export($this->getBroker(), $this->getFunctionName($test), 0, true));

			// Test loading from a string
			$rfl = $this->getParameterReflection($test, true);
			$this->assertSame($rfl->internal->__toString(), $rfl->token->__toString());
		}

		$this->assertSame(InternalReflectionParameter::export('strpos', 0, true), ReflectionParameter::export($this->getBroker(), 'strpos', 0, true));
	}

	/**
	 * Tests getting of inherited documentation comment.
	 */
	public function testDocCommentInheritance()
	{
		$this->getBroker()->processFile($this->getFilePath('docCommentInheritance'));

		$grandParent = new \stdClass();
		$grandParent->token = $this->getBroker()->getClass('TokenReflection_Test_ParameterDocCommentInheritanceGrandParent')->getMethod('m');

		$parent = new \stdClass();
		$parent->token = $this->getBroker()->getClass('TokenReflection_Test_ParameterDocCommentInheritanceParent')->getMethod('m');

		$rfl = new \stdClass();
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_ParameterDocCommentInheritance')->getMethod('m');

		$this->assertNotNull($grandParent->token);
		$this->assertNotNull($parent->token);
		$this->assertNotNull($rfl->token);

		$this->assertSame($grandParent->token->getAnnotation('param'), $parent->token->getAnnotation('param'));
		$this->assertSame(count($grandParent->token->getAnnotation('param')), count($rfl->token->getAnnotation('param')));
	}

	/**
	 * Tests new PHP 5.4 features.
	 */
	public function test54features()
	{
		if (PHP_VERSION_ID < 50400) {
			$this->markTestSkipped('Tested only on PHP 5.4+');
		}

		$rfl = $this->getFunctionReflection('54features');

		$this->assertSame(3, $rfl->internal->getNumberOfParameters());
		foreach ($rfl->internal->getParameters() as $internal){
			$token = $rfl->token->getParameter($internal->getPosition());
			$this->assertSame($internal->getDefaultValue(), $token->getDefaultValue());
		}
	}

	/**
	 * Tests an exception thrown when trying to create the reflection from a PHP internal reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalParameterReflectionCreate()
	{
		Php\ReflectionParameter::create(new \ReflectionClass('Exception'), $this->getBroker());
	}

	/**
	 * Tests various constant (mis)definitions.
	 */
	public function testValueDefinitions()
	{
		$rfl = $this->getClassReflection('valueDefinitions');

		$this->assertTrue($rfl->internal->hasMethod('method'));
		$internalMethod = $rfl->internal->getMethod('method');

		$this->assertTrue($rfl->token->hasMethod('method'));
		$tokenMethod = $rfl->token->getMethod('method');

		foreach ($internalMethod->getParameters() as $parameter) {
			$this->assertSame($parameter->getDefaultValue(), $tokenMethod->getParameter($parameter->getName())->getDefaultValue());
		}
	}
}
