<?php

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

class ReflectionFunctionTest extends Test
{
	protected $type = 'function';

	public function testLines()
	{
		$rfl = $this->getFunctionReflection('lines');
		$this->assertEquals($rfl->internal->getStartLine(), $rfl->token->getStartLine());
		$this->assertEquals(3, $rfl->token->getStartLine());
		$this->assertEquals($rfl->internal->getEndLine(), $rfl->token->getEndLine());
		$this->assertEquals(5, $rfl->token->getEndLine());
	}

	public function testComment()
	{
		$rfl = $this->getFunctionReflection('docComment');
		$this->assertEquals($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertEquals("/**\n * This is a function.\n */", $rfl->token->getDocComment());

		$rfl = $this->getFunctionReflection('noComment');
		$this->assertEquals($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertFalse($rfl->token->getDocComment());
	}

	public function testStaticVariables()
	{
		/**
		 * @todo
		 */
		return;

		$rfl = $this->getFunctionReflection('staticVariables');

		$this->assertEquals($rfl->internal->getStaticVariables(), $rfl->token->getStaticVariables());
		$this->assertEquals(array('string' => 'string', 'integer' => 1, 'float' => 1.1, 'boolean' => true, 'null' => null, 'array' => array(1 => 1)), $rfl->token->getStaticVariables());
	}

	public function testClosure()
	{
		$rfl = $this->getFunctionReflection('noClosure');
		$this->assertEquals($rfl->internal->isClosure(), $rfl->token->isClosure());
		$this->assertFalse($rfl->token->isClosure());
	}

	public function testDeprecated()
	{
		$rfl = $this->getFunctionReflection('noDeprecated');
		$this->assertEquals($rfl->internal->isDeprecated(), $rfl->token->isDeprecated());
		$this->assertFalse($rfl->token->isDeprecated());
	}

	public function testDisabled()
	{
		$rfl = $this->getFunctionReflection('noDisabled');
		$this->assertEquals($rfl->internal->isDisabled(), $rfl->token->isDisabled());
		$this->assertFalse($rfl->token->isDisabled());
	}

	public function testUserDefined()
	{
		$rfl = $this->getFunctionReflection('userDefined');

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
		$rfl->internal = new \ReflectionFunction('get_class');
		$rfl->token = $this->getBroker()->getFunction('get_class');

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

	public function testInNamespace()
	{
		require_once $this->getFilePath('inNamespace');
		$this->getBroker()->processFile($this->getFilePath('inNamespace'));

		$rfl = new \stdClass();
		$rfl->internal = new \ReflectionFunction('TokenReflection\Test\functionInNamespace');
		$rfl->token = $this->getBroker()->getFunction('TokenReflection\Test\functionInNamespace');

		$this->assertEquals($rfl->internal->inNamespace(), $rfl->token->inNamespace());
		$this->assertTrue($rfl->token->inNamespace());
		$this->assertEquals($rfl->internal->getNamespaceName(), $rfl->token->getNamespaceName());
		$this->assertEquals('TokenReflection\Test', $rfl->token->getNamespaceName());
		$this->assertEquals($rfl->internal->getName(), $rfl->token->getName());
		$this->assertEquals('TokenReflection\Test\functionInNamespace', $rfl->token->getName());
		$this->assertEquals($rfl->internal->getShortName(), $rfl->token->getShortName());
		$this->assertEquals('functionInNamespace', $rfl->token->getShortName());

		$rfl = $this->getFunctionReflection('noNamespace');
		$this->assertEquals($rfl->internal->inNamespace(), $rfl->token->inNamespace());
		$this->assertFalse($rfl->token->inNamespace());
		$this->assertEquals($rfl->internal->getNamespaceName(), $rfl->token->getNamespaceName());
		$this->assertEquals('', $rfl->token->getNamespaceName());
		$this->assertEquals($rfl->internal->getName(), $rfl->token->getName());
		$this->assertEquals($this->getFunctionName('noNamespace'), $rfl->token->getName());
		$this->assertEquals($rfl->internal->getShortName(), $rfl->token->getShortName());
		$this->assertEquals($this->getFunctionName('noNamespace'), $rfl->token->getShortName());
	}

	public function testReference()
	{
		$rfl = $this->getFunctionReflection('reference');
		$this->assertEquals($rfl->internal->returnsReference(), $rfl->token->returnsReference());
		$this->assertTrue($rfl->token->returnsReference());

		$rfl = $this->getFunctionReflection('noReference');
		$this->assertEquals($rfl->internal->returnsReference(), $rfl->token->returnsReference());
		$this->assertFalse($rfl->token->returnsReference());
	}

	public function testParameters()
	{
		$rfl = $this->getFunctionReflection('parameters');
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

		$rfl = $this->getFunctionReflection('noParameters');
		$this->assertEquals($rfl->internal->getNumberOfParameters(), $rfl->token->getNumberOfParameters());
		$this->assertEquals(0, $rfl->token->getNumberOfParameters());
		$this->assertEquals($rfl->internal->getNumberOfRequiredParameters(), $rfl->token->getNumberOfRequiredParameters());
		$this->assertEquals(0, $rfl->token->getNumberOfRequiredParameters());
		$this->assertEquals($rfl->internal->getParameters(), $rfl->token->getParameters());
		$this->assertEquals(array(), $rfl->token->getParameters());
	}

	public function testInvoke()
	{
		$rfl = $this->getFunctionReflection('invoke');
		$this->assertEquals($rfl->internal->invoke(1, 2), $rfl->token->invoke(1, 2));
		$this->assertEquals(3, $rfl->token->invoke(1, 2));
		$this->assertEquals($rfl->internal->invokeArgs(array(1, 2)), $rfl->token->invokeArgs(array(1, 2)));
		$this->assertEquals(3, $rfl->token->invokeArgs(array(1, 2)));
	}
}
