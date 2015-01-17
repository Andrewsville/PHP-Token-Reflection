<?php

namespace ApiGen\TokenReflection\Tests\Reflection;

use ApiGen;
use ApiGen\TokenReflection\Php\ReflectionParameter;
use ApiGen\TokenReflection\Tests\TestCase;
use ReflectionParameter as InternalReflectionParameter;


class ReflectionParameterTest extends TestCase
{

	/**
	 * @var string
	 */
	protected $type = 'parameter';


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


	public function testAllowsNull()
	{
		foreach (['Class', 'Array'] as $type) {
			$rfl = $this->getParameterReflection('null' . $type);
			$this->assertSame($rfl->internal->allowsNull(), $rfl->token->allowsNull());
			$this->assertTrue($rfl->token->allowsNull());

			$rfl = $this->getParameterReflection('noNull' . $type);
			$this->assertSame($rfl->internal->allowsNull(), $rfl->token->allowsNull());
			$this->assertFalse($rfl->token->allowsNull());
		}
	}


	public function testOptional()
	{
		$types = ['null' => NULL, 'true' => TRUE, 'false' => FALSE, 'array' => [], 'string' => 'string', 'integer' => 1, 'float' => 1.1, 'constant' => E_NOTICE];
		$definitions = ['null' => 'NULL', 'true' => 'TRUE', 'false' => 'FALSE', 'array' => '[]', 'string' => "'string'", 'integer' => '1', 'float' => '1.1', 'constant' => 'E_NOTICE'];
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
			$this->fail('Expected exception ApiGen\TokenReflection\Exception\RuntimeException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('ApiGen\TokenReflection\Exception\RuntimeException', $e);
		}
	}


	public function testInvalidOptions()
	{
		$fileName = $this->getFilePath('invalid-optionals');

		$broker = $this->getBroker();
		$broker->processFile($fileName);

		require_once $fileName;

		$token = $broker->getFunction('tokenReflectionParameterInvalidOptionals');
		$internal = new \ReflectionFunction('tokenReflectionParameterInvalidOptionals');

		static $params = [
			['one', FALSE, FALSE, TRUE],
			['two', FALSE, FALSE, FALSE],
			['three', TRUE, TRUE, TRUE]
		];

		$parameters = $internal->getParameters();
		$this->assertSame(count($params), count($parameters));

		foreach ($parameters as $i => $parameter) {
			$tokenParameter = $token->getParameter($i);

			list($paramName, $defaultValueAvailable, $optional, $allowsNull) = $params[$i];

			$this->assertSame($paramName, $parameter->getName(), $parameter->getName());
			$this->assertSame($paramName, $tokenParameter->getName(), $parameter->getName());

			$this->assertSame($defaultValueAvailable, $tokenParameter->isDefaultValueAvailable(), $parameter->getName());

			$this->assertSame($optional, $parameter->isOptional(), $parameter->getName());
			$this->assertSame($optional, $tokenParameter->isOptional(), $parameter->getName());

			$this->assertSame($allowsNull, $parameter->allowsNull(), $parameter->getName());
			$this->assertSame($allowsNull, $tokenParameter->allowsNull(), $parameter->getName());
		}
	}


	public function testArray()
	{
		$rfl = $this->getParameterReflection('array');
		$this->assertSame($rfl->internal->isArray(), $rfl->token->isArray());
		$this->assertTrue($rfl->token->isArray());

		$rfl = $this->getParameterReflection('noArray');
		$this->assertSame($rfl->internal->isArray(), $rfl->token->isArray());
		$this->assertFalse($rfl->token->isArray());
	}


	public function testCallable()
	{
		$rfl = $this->getParameterReflection('callable');
		$this->assertSame($rfl->internal->isCallable(), $rfl->token->isCallable());
		$this->assertTrue($rfl->token->isCallable());

		$rfl = $this->getParameterReflection('noCallable');
		$this->assertSame($rfl->internal->isCallable(), $rfl->token->isCallable());
		$this->assertFalse($rfl->token->isCallable());
	}


	public function testClass()
	{
		$rfl = $this->getParameterReflection('class');
		$this->assertSame($rfl->internal->getClass()->getName(), $rfl->token->getClass()->getName());
		$this->assertSame('Exception', $rfl->token->getClass()->getName());
		$this->assertSame('Exception', $rfl->token->getClassName());
		$this->assertInstanceOf('ApiGen\TokenReflection\ReflectionClassInterface', $rfl->token->getClass());

		$rfl = $this->getParameterReflection('noClass');
		$this->assertSame($rfl->internal->getClass(), $rfl->token->getClass());
		$this->assertNull($rfl->token->getClass());
		$this->assertNull($rfl->token->getClassName());
	}


	public function testReference()
	{
		$rfl = $this->getParameterReflection('reference');
		$this->assertSame($rfl->internal->isPassedByReference(), $rfl->token->isPassedByReference());
		$this->assertTrue($rfl->token->isPassedByReference());

		$rfl = $this->getParameterReflection('noReference');
		$this->assertSame($rfl->internal->isPassedByReference(), $rfl->token->isPassedByReference());
		$this->assertFalse($rfl->token->isPassedByReference());
	}


	public function testDeclaring()
	{
		$rfl = $this->getParameterReflection('declaringFunction');
		$this->assertSame($rfl->internal->getDeclaringFunction()->getName(), $rfl->token->getDeclaringFunction()->getName());
		$this->assertSame($this->getFunctionName('declaringFunction'), $rfl->token->getDeclaringFunction()->getName());
		$this->assertSame($this->getFunctionName('declaringFunction'), $rfl->token->getDeclaringFunctionName());
		$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionFunction', $rfl->token->getDeclaringFunction());

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
		$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionMethod', $token->getDeclaringFunction());

		$this->assertSame($internal->getDeclaringClass()->getName(), $token->getDeclaringClass()->getName());
		$this->assertSame($this->getClassName('declaringMethod'), $token->getDeclaringClass()->getName());
		$this->assertSame($this->getClassName('declaringMethod'), $token->getDeclaringClassName());
		$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionClass', $token->getDeclaringClass());
	}


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


	public function test54features()
	{
		$rfl = $this->getFunctionReflection('54features');

		$this->assertSame(3, $rfl->internal->getNumberOfParameters());
		foreach ($rfl->internal->getParameters() as $internal) {
			$token = $rfl->token->getParameter($internal->getPosition());
			$this->assertSame($internal->getDefaultValue(), $token->getDefaultValue());
		}
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\RuntimeException
	 */
	public function testInternalParameterReflectionCreate()
	{
		ReflectionParameter::create(new \ReflectionClass('Exception'), $this->getBroker());
	}


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


	/**
	 * Tests returning if a parameter has its default value defined by a constant (PHP 5.4.6+ feature).
	 */
	public function testDefaultValuesByConstant()
	{
		static $expected = [
			'one' => [FALSE, NULL, 'foo'],
			'two' => [FALSE, NULL, 'bar'],
			'three' => [TRUE, 'self::VALUE', 'bar'],
			'four' => [TRUE, 'TokenReflection_Test_ParameterConstantValue::VALUE', 'bar'],
			'five' => [TRUE, 'TOKEN_REFLECTION_PARAMETER_CONSTANT_VALUE', 'foo']
		];

		$rfl = $this->getMethodReflection('constantValue');
		$this->assertSame(count($expected), count($rfl->internal->getParameters()));

		foreach ($rfl->internal->getParameters() as $parameter) {
			$tokenParameter = $rfl->token->getParameter($parameter->getName());

			$this->assertTrue(isset($expected[$parameter->getName()]), $parameter->getName());
			list($isConstant, $constantName, $value) = $expected[$parameter->getName()];

			$this->assertSame($isConstant, $tokenParameter->isDefaultValueConstant(), $parameter->getName());
			$this->assertSame($constantName, $tokenParameter->getDefaultValueConstantName(), $parameter->getName());
			$this->assertSame($value, $tokenParameter->getDefaultValue(), $parameter->getName());

			$this->assertSame($parameter->isDefaultValueConstant(), $tokenParameter->isDefaultValueConstant(), $parameter->getName());
			$this->assertSame($parameter->getDefaultValueConstantName(), $tokenParameter->getDefaultValueConstantName(), $parameter->getName());
			$this->assertSame($parameter->getDefaultValue(), $tokenParameter->getDefaultValue(), $parameter->getName());
		}
	}

}
