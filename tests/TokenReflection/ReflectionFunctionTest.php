<?php

namespace TokenReflection;

use ReflectionFunction as InternalReflectionFunction;

require_once __DIR__ . '/../bootstrap.php';

class ReflectionFunctionTest extends Test
{
	protected $type = 'function';

	public function testLines()
	{
		$rfl = $this->getFunctionReflection('lines');
		$this->assertSame($rfl->internal->getStartLine(), $rfl->token->getStartLine());
		$this->assertSame(3, $rfl->token->getStartLine());
		$this->assertSame($rfl->internal->getEndLine(), $rfl->token->getEndLine());
		$this->assertSame(5, $rfl->token->getEndLine());
	}

	public function testComment()
	{
		$rfl = $this->getFunctionReflection('docComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame("/**\n * This is a function.\n */", $rfl->token->getDocComment());

		$rfl = $this->getFunctionReflection('noComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertFalse($rfl->token->getDocComment());
	}

	public function testStaticVariables()
	{
		/**
		 * @todo
		 */
		return;

		$rfl = $this->getFunctionReflection('staticVariables');

		$this->assertSame($rfl->internal->getStaticVariables(), $rfl->token->getStaticVariables());
		$this->assertSame(array('string' => 'string', 'integer' => 1, 'float' => 1.1, 'boolean' => true, 'null' => null, 'array' => array(1 => 1)), $rfl->token->getStaticVariables());
	}

	public function testClosure()
	{
		$rfl = $this->getFunctionReflection('noClosure');
		$this->assertSame($rfl->internal->isClosure(), $rfl->token->isClosure());
		$this->assertFalse($rfl->token->isClosure());
	}

	public function testDeprecated()
	{
		$rfl = $this->getFunctionReflection('noDeprecated');
		$this->assertSame($rfl->internal->isDeprecated(), $rfl->token->isDeprecated());
		$this->assertFalse($rfl->token->isDeprecated());
	}

	public function testDisabled()
	{
		$rfl = $this->getFunctionReflection('noDisabled');
		$this->assertSame($rfl->internal->isDisabled(), $rfl->token->isDisabled());
		$this->assertFalse($rfl->token->isDisabled());
	}

	public function testUserDefined()
	{
		$rfl = $this->getFunctionReflection('userDefined');

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
		$rfl->internal = new InternalReflectionFunction('get_class');
		$rfl->token = $this->getBroker()->getFunction('get_class');

		$this->assertSame($rfl->internal->isUserDefined(), $rfl->token->isUserDefined());
		$this->assertFalse($rfl->token->isUserDefined());
		$this->assertSame($rfl->internal->getFileName(), $rfl->token->getFileName());
		$this->assertFalse($rfl->token->getFileName());
		$this->assertSame($rfl->internal->isInternal(), $rfl->token->isInternal());
		$this->assertTrue($rfl->token->isInternal());

		$this->assertInstanceOf('TokenReflection\Php\ReflectionExtension', $rfl->token->getExtension());
		$this->assertSame($rfl->internal->getExtensionName(), $rfl->token->getExtensionName());
		$this->assertSame('Core', $rfl->token->getExtensionName());
	}

	public function testInNamespace()
	{
		require_once $this->getFilePath('inNamespace');
		$this->getBroker()->processFile($this->getFilePath('inNamespace'));

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionFunction('TokenReflection\Test\functionInNamespace');
		$rfl->token = $this->getBroker()->getFunction('TokenReflection\Test\functionInNamespace');

		$this->assertSame($rfl->internal->inNamespace(), $rfl->token->inNamespace());
		$this->assertTrue($rfl->token->inNamespace());
		$this->assertSame($rfl->internal->getNamespaceName(), $rfl->token->getNamespaceName());
		$this->assertSame('TokenReflection\Test', $rfl->token->getNamespaceName());
		$this->assertSame($rfl->internal->getName(), $rfl->token->getName());
		$this->assertSame('TokenReflection\Test\functionInNamespace', $rfl->token->getName());
		$this->assertSame($rfl->internal->getShortName(), $rfl->token->getShortName());
		$this->assertSame('functionInNamespace', $rfl->token->getShortName());

		$rfl = $this->getFunctionReflection('noNamespace');
		$this->assertSame($rfl->internal->inNamespace(), $rfl->token->inNamespace());
		$this->assertFalse($rfl->token->inNamespace());
		$this->assertSame($rfl->internal->getNamespaceName(), $rfl->token->getNamespaceName());
		$this->assertSame('', $rfl->token->getNamespaceName());
		$this->assertSame($rfl->internal->getName(), $rfl->token->getName());
		$this->assertSame($this->getFunctionName('noNamespace'), $rfl->token->getName());
		$this->assertSame($rfl->internal->getShortName(), $rfl->token->getShortName());
		$this->assertSame($this->getFunctionName('noNamespace'), $rfl->token->getShortName());
	}

	public function testReference()
	{
		$rfl = $this->getFunctionReflection('reference');
		$this->assertSame($rfl->internal->returnsReference(), $rfl->token->returnsReference());
		$this->assertTrue($rfl->token->returnsReference());

		$rfl = $this->getFunctionReflection('noReference');
		$this->assertSame($rfl->internal->returnsReference(), $rfl->token->returnsReference());
		$this->assertFalse($rfl->token->returnsReference());
	}

	public function testParameters()
	{
		$rfl = $this->getFunctionReflection('parameters');
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

		$rfl = $this->getFunctionReflection('noParameters');
		$this->assertSame($rfl->internal->getNumberOfParameters(), $rfl->token->getNumberOfParameters());
		$this->assertSame(0, $rfl->token->getNumberOfParameters());
		$this->assertSame($rfl->internal->getNumberOfRequiredParameters(), $rfl->token->getNumberOfRequiredParameters());
		$this->assertSame(0, $rfl->token->getNumberOfRequiredParameters());
		$this->assertSame($rfl->internal->getParameters(), $rfl->token->getParameters());
		$this->assertSame(array(), $rfl->token->getParameters());
	}

	public function testInvoke()
	{
		$rfl = $this->getFunctionReflection('invoke');
		$this->assertSame($rfl->internal->invoke(1, 2), $rfl->token->invoke(1, 2));
		$this->assertSame(3, $rfl->token->invoke(1, 2));
		$this->assertSame($rfl->internal->invokeArgs(array(1, 2)), $rfl->token->invokeArgs(array(1, 2)));
		$this->assertSame(3, $rfl->token->invokeArgs(array(1, 2)));
	}

	public function testToString()
	{
		$tests = array(
			'invoke', 'noParameters', 'parameters', 'reference', 'noReference', 'noNamespace', 'userDefined', 'noClosure'
		);
		foreach ($tests as $test) {
			$rfl = $this->getFunctionReflection($test);
			$this->assertSame($rfl->internal->__toString(), $rfl->token->__toString());
			$this->assertSame(InternalReflectionFunction::export($this->getFunctionName($test), true), ReflectionFunction::export($this->getBroker(), $this->getFunctionName($test), true));
		}

		$this->assertSame(InternalReflectionFunction::export('strpos', true), ReflectionFunction::export($this->getBroker(), 'strpos', true));
	}
}
