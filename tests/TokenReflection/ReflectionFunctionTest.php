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

use ReflectionFunction as InternalReflectionFunction;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Function test.
 */
class ReflectionFunctionTest extends Test
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
	 * Tests getting of copydoc documentation comment.
	 */
	public function testCommentCopydoc()
	{
		static $functions = array(
			'tokenReflectionFunctionDocCommentCopydoc' => 'This is a function.',
			'tokenReflectionFunctionDocCommentCopydoc2' => 'This is a function.',
			'tokenReflectionFunctionDocCommentCopydoc3' => 'This is a function.',
			'tokenReflectionFunctionDocCommentCopydoc4' => null,
			'tokenReflectionFunctionDocCommentCopydoc5' => null,
		);

		$broker = $this->getBroker();
		$broker->processFile($this->getFilePath('docCommentCopydoc'));

		foreach ($functions as $functionName => $shortDescription) {
			$this->assertTrue($broker->hasFunction($functionName), $functionName);
			$this->assertSame($shortDescription, $broker->getFunction($functionName)->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION), $functionName);
		}
	}

	/**
	 * Tests getting of static variables.
	 */
	public function testStaticVariables()
	{
		$rfl = $this->getFunctionReflection('staticVariables');

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
				'constant' => 'constant value'
			),
			$rfl->token->getStaticVariables()
		);
	}

	/**
	 * Tests if function is a closure.
	 */
	public function testClosure()
	{
		$rfl = $this->getFunctionReflection('noClosure');
		$this->assertSame($rfl->internal->isClosure(), $rfl->token->isClosure());
		$this->assertFalse($rfl->token->isClosure());
	}

	/**
	 * Tests if function is a closure.
	 */
	public function testGetClosure()
	{
		$broker = $this->getBroker();
		$broker->processFile($this->getFilePath('getClosure'));
		require_once $this->getFilePath('getClosure');

		$function = $broker->getFunction('tokenReflectionFunctionGetClosure1');
		$this->assertNull($function->getClosureScopeClass());
		$closure = $function->getClosure();
		$this->assertInstanceOf('Closure', $closure);

		static $data1 = array(1 => 1, 4 => 2, 9 => 3);
		foreach ($data1 as $result => $input) {
			$this->assertSame($result, $closure($input));
		}

		$function = $broker->getFunction('tokenReflectionFunctionGetClosure2');
		$this->assertNull($function->getClosureScopeClass());
		$closure = $function->getClosure();
		$this->assertInstanceOf('Closure', $closure);

		static $data2 = array(-1 => 1, -2 => 2, -3 => 3);
		foreach ($data2 as $result => $input) {
			$this->assertSame($result, $closure($input));
		}

		static $data3 = array(-1 => array(2, -.5), 1 => array(-100, -.01), 8 => array(2, 4));
		foreach ($data3 as $result => $input) {
			list($a, $b) = $input;
			$this->assertEquals($result, $closure($a, $b));
		}
	}

	/**
	 * Tests if function is deprecated.
	 */
	public function testDeprecated()
	{
		$rfl = $this->getFunctionReflection('noDeprecated');
		$this->assertSame($rfl->internal->isDeprecated(), $rfl->token->isDeprecated());
		$this->assertFalse($rfl->token->isDeprecated());
	}

	/**
	 * Tests if function is disabled.
	 */
	public function testDisabled()
	{
		$rfl = $this->getFunctionReflection('noDisabled');
		$this->assertSame($rfl->internal->isDisabled(), $rfl->token->isDisabled());
		$this->assertFalse($rfl->token->isDisabled());
	}

	/**
	 * Tests if function is user defined or internal.
	 */
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

	/**
	 * Tests if function is defined in namespace.
	 */
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

	/**
	 * Tests function invoking.
	 */
	public function testInvoke()
	{
		$rfl = $this->getFunctionReflection('invoke');
		$this->assertSame($rfl->internal->invoke(1, 2), $rfl->token->invoke(1, 2));
		$this->assertSame(3, $rfl->token->invoke(1, 2));
		$this->assertSame($rfl->internal->invokeArgs(array(1, 2)), $rfl->token->invokeArgs(array(1, 2)));
		$this->assertSame(3, $rfl->token->invokeArgs(array(1, 2)));
	}

	/**
	 * Tests export.
	 */
	public function testToString()
	{
		$tests = array(
			'lines', 'docComment', 'noComment',
			'invoke', 'noParameters', 'parameters', 'reference', 'noReference', 'noNamespace', 'userDefined', 'noClosure'
		);
		foreach ($tests as $test) {
			$rfl = $this->getFunctionReflection($test);
			$this->assertSame($rfl->internal->__toString(), $rfl->token->__toString());
			$this->assertSame(InternalReflectionFunction::export($this->getFunctionName($test), true), ReflectionFunction::export($this->getBroker(), $this->getFunctionName($test), true));

			// Test loading from a string
			$rfl = $this->getFunctionReflection($test, true);
			$this->assertSame($rfl->internal->__toString(), $rfl->token->__toString());
		}

		$this->assertSame(InternalReflectionFunction::export('strpos', true), ReflectionFunction::export($this->getBroker(), 'strpos', true));
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
	public function testInternalFunctionReflectionCreate()
	{
		Php\ReflectionExtension::create(new \ReflectionClass('Exception'), $this->getBroker());
	}

	/**
	 * Tests an exception thrown when trying to get a non-existent parameter.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalFunctionGetParameter1()
	{
		$this->getInternalFunctionReflection()->getParameter('~non-existent~');
	}

	/**
	 * Tests an exception thrown when trying to get a non-existent parameter.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalFunctionGetParameter2()
	{
		$this->getInternalFunctionReflection()->getParameter(999);
	}

	/**
	 * Returns an internal function reflection.
	 *
	 * @return \TokenReflection\Php\ReflectionFunction
	 */
	private function getInternalFunctionReflection()
	{
		return $this->getBroker()->getFunction('create_function');
	}
}
