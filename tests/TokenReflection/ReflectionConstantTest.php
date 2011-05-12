<?php

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

class ReflectionConstantTest extends Test
{
	protected $type = 'constant';

	public function testLines()
	{
		$token = $this->getConstantTokenReflection('lines');

		$this->assertEquals(5, $token->getStartLine());
		$this->assertEquals(5, $token->getEndLine());
	}

	public function testComment()
	{
		$token = $this->getConstantTokenReflection('docComment');
		$this->assertEquals("/**\n\t * This is a constant.\n\t */", $token->getDocComment());

		$token = $this->getConstantTokenReflection('noComment');
		$this->assertFalse($token->getDocComment());
	}

	public function testTypes()
	{
		$constants = array('string' => 'string', 'integer' => 1, 'integerNegative' => -1, 'float' => 1.1, 'floatNegative' => -1.1, 'boolean' => true, 'null' => null/*, 'constant' => E_NOTICE*/);
		foreach ($constants as $type => $value) {
			$test = 'type' . ucfirst($type);
			$token = $this->getConstantTokenReflection($test);
			$this->assertEquals($this->getClassInternalReflection($test)->getConstant($this->getConstantName($test)), $token->getValue());
			$this->assertSame($value, $token->getValue());
		}
	}

	public function testInNamespace()
	{
		$this->getBroker()->processFile($this->getFilePath('inNamespace'));
		$token = $this->getBroker()->getConstant('TokenReflection\Test\CONSTANT_IN_NAMESPACE');

		$this->assertInstanceOf('TokenReflection\ReflectionConstant', $token);
		$this->assertSame('constant-in-namespace', $token->getValue());

		$this->assertTrue($token->inNamespace());
		$this->assertEquals('TokenReflection\\Test\\CONSTANT_IN_NAMESPACE', $token->getName());
		$this->assertEquals('CONSTANT_IN_NAMESPACE', $token->getShortName());

		$this->assertNull($token->getDeclaringClassName());
		$this->assertNull($token->getClass());
		$this->assertNull($token->getDeclaringClass());

		$token = $this->getConstantTokenReflection('noNamespace');

		$this->assertFalse($token->inNamespace());
		$this->assertEquals('NO_NAMESPACE', $token->getName());
		$this->assertEquals('NO_NAMESPACE', $token->getShortName());

		$this->assertEquals('TokenReflection_Test_ConstantNoNamespace', $token->getDeclaringClassName());
		$this->assertEquals('TokenReflection_Test_ConstantNoNamespace', $token->getClass());
		$this->assertEquals('TokenReflection_Test_ConstantNoNamespace', $token->getDeclaringClass()->getName());
		$this->assertInstanceOf('TokenReflection\ReflectionClass', $token->getDeclaringClass());
	}
}
