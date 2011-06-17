<?php

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

class ReflectionConstantTest extends Test
{
	protected $type = 'constant';

	public function testLines()
	{
		$token = $this->getConstantTokenReflection('lines');

		$this->assertSame(5, $token->getStartLine());
		$this->assertSame(5, $token->getEndLine());
	}

	public function testComment()
	{
		$token = $this->getConstantTokenReflection('docComment');
		$this->assertSame("/**\n\t * This is a constant.\n\t */", $token->getDocComment());

		$token = $this->getConstantTokenReflection('noComment');
		$this->assertFalse($token->getDocComment());
	}

	public function testTypes()
	{
		$constants = array('string' => 'string', 'integer' => 1, 'integerNegative' => -1, 'float' => 1.1, 'floatNegative' => -1.1, 'boolean' => true, 'null' => null, 'constant' => E_NOTICE);
		foreach ($constants as $type => $value) {
			$test = 'type' . ucfirst($type);
			$token = $this->getConstantTokenReflection($test);
			$this->assertSame($this->getClassInternalReflection($test)->getConstant($this->getConstantName($test)), $token->getValue());
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
		$this->assertSame('TokenReflection\\Test\\CONSTANT_IN_NAMESPACE', $token->getName());
		$this->assertSame('CONSTANT_IN_NAMESPACE', $token->getShortName());

		$this->assertNull($token->getDeclaringClassName());
		$this->assertNull($token->getDeclaringClass());

		$token = $this->getConstantTokenReflection('noNamespace');

		$this->assertFalse($token->inNamespace());
		$this->assertSame('NO_NAMESPACE', $token->getName());
		$this->assertSame('NO_NAMESPACE', $token->getShortName());

		$this->assertSame('TokenReflection_Test_ConstantNoNamespace', $token->getDeclaringClassName());
		$this->assertSame('TokenReflection_Test_ConstantNoNamespace', $token->getDeclaringClass()->getName());
		$this->assertInstanceOf('TokenReflection\ReflectionClass', $token->getDeclaringClass());
	}

	public function testToString()
	{
		$tests = array(
			'noNamespace' => "Constant [ string NO_NAMESPACE ] { no-namespace }\n",
			'typeString' => "Constant [ string TYPE_STRING ] { string }\n",
			'typeInteger' => "Constant [ integer TYPE_INTEGER ] { 1 }\n",
			'typeIntegerNegative' => "Constant [ integer TYPE_INTEGER_NEGATIVE ] { -1 }\n",
			'typeFloat' => "Constant [ double TYPE_FLOAT ] { 1.1 }\n",
			'typeFloatNegative' => "Constant [ double TYPE_FLOAT_NEGATIVE ] { -1.1 }\n",
			'typeBoolean' => "Constant [ boolean TYPE_BOOLEAN ] { 1 }\n",
			'typeNull' => "Constant [ NULL TYPE_NULL ] {  }\n"
		);
		foreach ($tests as $test => $expected) {
			$this->assertSame($expected, $this->getConstantTokenReflection($test)->__toString());
			$this->assertSame($expected, ReflectionConstant::export($this->getBroker(), $this->getClassName($test), $this->getConstantName($test), true));
		}

		$this->assertSame("Constant [ integer E_NOTICE ] { 8 }\n", ReflectionConstant::export($this->getBroker(), null, 'E_NOTICE', true));
	}
}
