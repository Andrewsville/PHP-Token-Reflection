<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.0 beta 6
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

require_once __DIR__ . '/../bootstrap.php';

/**
 * Constant test.
 *
 * @author Jaroslav Hanslík
 * @author Ondřej Nešpor
 */
class ReflectionConstantTest extends Test
{
	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type = 'constant';

	/**
	 * Tests getting of start and end line.
	 */
	public function testLines()
	{
		$token = $this->getConstantTokenReflection('lines');

		$this->assertSame(5, $token->getStartLine());
		$this->assertSame(5, $token->getEndLine());
	}

	/**
	 * Tests getting of documentation comment.
	 */
	public function testComment()
	{
		$token = $this->getConstantTokenReflection('docComment');
		$this->assertSame("/**\n\t * This is a constant.\n\t */", $token->getDocComment());

		$token = $this->getConstantTokenReflection('noComment');
		$this->assertFalse($token->getDocComment());
	}

	/**
	 * Tests different types of constant value.
	 */
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

	/**
	 * Tests if constant is defined in namespace or in class.
	 */
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

	/**
	 * Tests export.
	 */
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
			'typeNull' => "Constant [ null TYPE_NULL ] {  }\n"
		);
		foreach ($tests as $test => $expected) {
			$this->assertSame($expected, $this->getConstantTokenReflection($test)->__toString());
			$this->assertSame($expected, ReflectionConstant::export($this->getBroker(), $this->getClassName($test), $this->getConstantName($test), true));
		}

		$this->assertSame("Constant [ integer E_NOTICE ] { 8 }\n", ReflectionConstant::export($this->getBroker(), null, 'E_NOTICE', true));
	}
}
