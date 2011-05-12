<?php

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

class ReflectionParameterTest extends Test
{
	protected $type = 'parameter';

	public function testPosition()
	{
		$rfl = $this->getFunctionReflection('position');
		$internalParameters = $rfl->internal->getParameters();
		$tokenParameters = $rfl->token->getParameters();
		for ($i = 0; $i < 3; $i++) {
			$internal = $internalParameters[$i];
			$token = $tokenParameters[$i];

			$this->assertEquals($internal->getPosition(), $token->getPosition());
			$this->assertEquals($i, $token->getPosition());
		}
	}

	public function testNull()
	{
		$rfl = $this->getParameterReflection('null');
		$this->assertEquals($rfl->internal->allowsNull(), $rfl->token->allowsNull());
		$this->assertTrue($rfl->token->allowsNull());

		$rfl = $this->getParameterReflection('noNull');
		$this->assertEquals($rfl->internal->allowsNull(), $rfl->token->allowsNull());
		$this->assertTrue($rfl->token->allowsNull());
	}

	public function testOptional()
	{
		ReflectionParameter::setParseValueDefinitions(true);

		$types = array('null' => null, 'true' => true, 'false' => false, 'array' => array(), 'string' => 'string', 'integer' => 1, 'float' => 1.1, 'constant' => E_NOTICE);
		$definitions = array('null' => 'null', 'true' => 'true', 'false' => 'false', 'array' => 'array()', 'string' => "'string'", 'integer' => '1', 'float' => '1.1', 'constant' => 'E_NOTICE');
		foreach ($types as $type => $value) {
			$rfl = $this->getParameterReflection('optional' . ucfirst($type));
			$this->assertEquals($rfl->internal->isOptional(), $rfl->token->isOptional());
			$this->assertTrue($rfl->token->isOptional());
			$this->assertEquals($rfl->internal->isDefaultValueAvailable(), $rfl->token->isDefaultValueAvailable());
			$this->assertTrue($rfl->token->isDefaultValueAvailable());
			$this->assertEquals($rfl->internal->getDefaultValue(), $rfl->token->getDefaultValue());
			$this->assertSame($value, $rfl->token->getDefaultValue());
			$this->assertEquals($definitions[$type], $rfl->token->getDefaultValueDefinition());
		}

		$rfl = $this->getParameterReflection('noOptional');
		$this->assertEquals($rfl->internal->isOptional(), $rfl->token->isOptional());
		$this->assertFalse($rfl->token->isOptional());
		$this->assertEquals($rfl->internal->isDefaultValueAvailable(), $rfl->token->isDefaultValueAvailable());
		$this->assertFalse($rfl->token->isDefaultValueAvailable());

		try {
			$rfl->token->getDefaultValue();
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		ReflectionParameter::setParseValueDefinitions(false);
	}

	public function testArray()
	{
		$rfl = $this->getParameterReflection('array');
		$this->assertEquals($rfl->internal->isArray(), $rfl->token->isArray());
		$this->assertTrue($rfl->token->isArray());

		$rfl = $this->getParameterReflection('noArray');
		$this->assertEquals($rfl->internal->isArray(), $rfl->token->isArray());
		$this->assertFalse($rfl->token->isArray());
	}

	public function testClass()
	{
		$rfl = $this->getParameterReflection('class');
		$this->assertEquals($rfl->internal->getClass()->getName(), $rfl->token->getClass()->getName());
		$this->assertEquals('Exception', $rfl->token->getClass()->getName());
		$this->assertEquals('Exception', $rfl->token->getClassName());
		$this->assertInstanceOf('TokenReflection\IReflectionClass', $rfl->token->getClass());

		$rfl = $this->getParameterReflection('noClass');
		$this->assertEquals($rfl->internal->getClass(), $rfl->token->getClass());
		$this->assertNull($rfl->token->getClass());
		$this->assertNull($rfl->token->getClassName());
	}

	public function testReference()
	{
		$rfl = $this->getParameterReflection('reference');
		$this->assertEquals($rfl->internal->isPassedByReference(), $rfl->token->isPassedByReference());
		$this->assertTrue($rfl->token->isPassedByReference());

		$rfl = $this->getParameterReflection('noReference');
		$this->assertEquals($rfl->internal->isPassedByReference(), $rfl->token->isPassedByReference());
		$this->assertFalse($rfl->token->isPassedByReference());
	}

	public function testDeclaring()
	{
		$rfl = $this->getParameterReflection('declaringFunction');
		$this->assertEquals($rfl->internal->getDeclaringFunction()->getName(), $rfl->token->getDeclaringFunction()->getName());
		$this->assertEquals($this->getFunctionName('declaringFunction'), $rfl->token->getDeclaringFunction()->getName());
		$this->assertEquals($this->getFunctionName('declaringFunction'), $rfl->token->getDeclaringFunctionName());
		$this->assertInstanceOf('TokenReflection\ReflectionFunction', $rfl->token->getDeclaringFunction());

		$this->assertEquals($rfl->internal->getDeclaringClass(), $rfl->token->getDeclaringClass());
		$this->assertNull($rfl->token->getDeclaringClass());
		$this->assertNull($rfl->token->getDeclaringClassName());

		$rfl = $this->getMethodReflection('declaringMethod');
		$internalParameters = $rfl->internal->getParameters();
		$internal = $internalParameters[0];
		$tokenParameters = $rfl->token->getParameters();
		$token = $tokenParameters[0];

		$this->assertEquals($internal->getDeclaringFunction()->getName(), $token->getDeclaringFunction()->getName());
		$this->assertEquals($this->getMethodName('declaringMethod'), $token->getDeclaringFunction()->getName());
		$this->assertEquals($this->getMethodName('declaringMethod'), $token->getDeclaringFunctionName());
		$this->assertInstanceOf('TokenReflection\ReflectionMethod', $token->getDeclaringFunction());

		$this->assertEquals($internal->getDeclaringClass()->getName(), $token->getDeclaringClass()->getName());
		$this->assertEquals($this->getClassName('declaringMethod'), $token->getDeclaringClass()->getName());
		$this->assertEquals($this->getClassName('declaringMethod'), $token->getDeclaringClassName());
		$this->assertInstanceOf('TokenReflection\ReflectionClass', $token->getDeclaringClass());
	}
}
