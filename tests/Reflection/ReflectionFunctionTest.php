<?php

namespace ApiGen\TokenReflection\Tests\Reflection;

use ApiGen;
use ApiGen\TokenReflection\Php\ReflectionExtension;
use ApiGen\TokenReflection\Tests\TestCase;
use ReflectionFunction as InternalReflectionFunction;


class ReflectionFunctionTest extends TestCase
{

	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type = 'function';


	/**
	 * Tests getting of start and end line.
	 */
	public function testLines()
	{
		$rfl = $this->getFunctionReflection('lines');
		$this->assertSame($rfl->internal->getStartLine(), $rfl->token->getStartLine());
		$this->assertSame(3, $rfl->token->getStartLine());
		$this->assertSame($rfl->internal->getEndLine(), $rfl->token->getEndLine());
		$this->assertSame(5, $rfl->token->getEndLine());
	}


	/**
	 * Tests getting of documentation comment.
	 */
	public function testComment()
	{
		$rfl = $this->getFunctionReflection('docComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame("/**\n * This is a function.\n */", $rfl->token->getDocComment());

		$rfl = $this->getFunctionReflection('noComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertFalse($rfl->token->getDocComment());
	}


	/**
	 * Tests getting of static variables.
	 */
	public function testStaticVariables()
	{
		$rfl = $this->getFunctionReflection('staticVariables');

		$this->assertSame($rfl->internal->getStaticVariables(), $rfl->token->getStaticVariables());
		$this->assertSame(
			[
				'string' => 'string',
				'int' => 1,
				'float' => 1.1,
				'bool' => TRUE,
				'null' => NULL,
				'array' => [1 => 1],
				'array2' => [1 => 1, 2 => 2],
				'constant' => 'constant value'
			],
			$rfl->token->getStaticVariables()
		);
	}


	public function testDeprecated()
	{
		$rfl = $this->getFunctionReflection('noDeprecated');
		$this->assertSame($rfl->internal->isDeprecated(), $rfl->token->isDeprecated());
		$this->assertFalse($rfl->token->isDeprecated());
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

		$rfl = new \stdClass();
		$rfl->token = $this->getStorage()->getFunction('get_class');
		$this->assertFalse($rfl->token->isUserDefined());
		$this->assertFalse($rfl->token->getFileName());
		$this->assertTrue($rfl->token->isInternal());

		$this->assertInstanceOf('ApiGen\TokenReflection\Php\ReflectionExtension', $rfl->token->getExtension());
		$this->assertSame('Core', $rfl->token->getExtensionName());
	}


	/**
	 * Tests if function is defined in namespace.
	 */
	public function testInNamespace()
	{
		require_once $this->getFilePath('inNamespace');
		$this->getBroker()->processFile($this->getFilePath('inNamespace'));

		$rfl = new \stdClass();
		$rfl->internal = new InternalReflectionFunction('TokenReflection\Test\functionInNamespace');
		$rfl->token = $this->getStorage()->getFunction('TokenReflection\Test\functionInNamespace');

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


	/**
	 * Tests if function returns reference.
	 */
	public function testReference()
	{
		$rfl = $this->getFunctionReflection('reference');
		$this->assertSame($rfl->internal->returnsReference(), $rfl->token->returnsReference());
		$this->assertTrue($rfl->token->returnsReference());

		$rfl = $this->getFunctionReflection('noReference');
		$this->assertSame($rfl->internal->returnsReference(), $rfl->token->returnsReference());
		$this->assertFalse($rfl->token->returnsReference());
	}


	/**
	 * Tests getting of function parameters.
	 */
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
			$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionParameter', $tokenParameters[$i]);
		}

		$rfl = $this->getFunctionReflection('noParameters');
		$this->assertSame($rfl->internal->getNumberOfParameters(), $rfl->token->getNumberOfParameters());
		$this->assertSame(0, $rfl->token->getNumberOfParameters());
		$this->assertSame($rfl->internal->getNumberOfRequiredParameters(), $rfl->token->getNumberOfRequiredParameters());
		$this->assertSame(0, $rfl->token->getNumberOfRequiredParameters());
		$this->assertSame($rfl->internal->getParameters(), $rfl->token->getParameters());
		$this->assertSame([], $rfl->token->getParameters());
	}


	/**
	 * Tests new PHP 5.4 features.
	 */
	public function test54features()
	{
		$rfl = $this->getFunctionReflection('54features');

		$this->assertSame($rfl->internal->getStaticVariables(), $rfl->token->getStaticVariables());
		$this->assertSame(
			[
				'one' => [],
				'two' => [[1], '2', [[[[TRUE]]]]],
				'three' => 21
			],
			$rfl->token->getStaticVariables()
		);
	}



}
