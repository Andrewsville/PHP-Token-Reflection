<?php

namespace ApiGen\TokenReflection\Tests;

use ApiGen;
use ApiGen\TokenReflection\Exception\FileProcessingException;
use ApiGen\TokenReflection\ReflectionInterface;


/**
 * Tests of consistency between:
 * - the internal reflection and TR
 * - each internal reflection type
 */
class ConsistencyTest extends TestCase
{

	public function testConstantReflectionConsistency()
	{
		$this->parser->parseFile(__DIR__ . '/data/constant/in-namespace.php');
		try {
			$this->parser->parseFile(__DIR__ . '/data/duplicities/otherfile.php');
		} catch (FileProcessingException $e) {
			// Expected
		}

		$storage = $this->parser->getStorage();
		$this->assertNotSame(NULL, @constant('PHP_INT_MAX'));
		$constants = [
			'tokenized' => $storage->getConstant('TokenReflection\\Test\\CONSTANT_IN_NAMESPACE'),
			'internal' => $storage->getConstant('PHP_INT_MAX'),
			'invalid' => $storage->getConstant('DUPLICITIES_CONSTANTS_1')
		];

		// TestCase cross-consistency
		foreach ($constants as $referenceType => $referenceConstant) {
			foreach ($constants as $type => $constant) {
				if ($referenceType !== $type) {
					$this->crossConsistencyTest($referenceConstant, $constant);
				}
			}
		}
	}


	public function testClassReflectionConsistency()
	{
		$this->parser->parseFile(__FILE__);
		try {
			$this->parser->parseFile(__DIR__ . '/data/duplicities/otherfile.php');
		} catch (FileProcessingException $e) {
			// Expected
		}

		$storage = $this->parser->getStorage();
		$this->assertFalse(class_exists('Foo\\Bar', TRUE));
		$classes = [
			'tokenized' => $storage->getClass('ApiGen\\TokenReflection\\Tests\\ConsistencyTest'),
			'internal' => $storage->getClass('Exception'),
			'invalid' => $storage->getClass('duplicitiesClasses1')
		];

		// TestCase consistency with the internal reflection
		foreach ($classes as $class) {
			$this->internalConsistencyTest(new \ReflectionClass(new \stdClass()), $class);
		}

		// TestCase cross-consistency
		foreach ($classes as $referenceType => $referenceClass) {
			foreach ($classes as $type => $class) {
				if ($referenceType !== $type) {
					$this->crossConsistencyTest($referenceClass, $class);
				}
			}
		}
	}


	public function testGetNonExistingClass()
	{
		$this->assertNull($this->parser->getStorage()->getClass('Foo\\Bar'));
	}


	public function testFunctionReflectionConsistency()
	{
		$this->parser->parseFile(__DIR__ . '/data/function/in-namespace.php');
		try {
			$this->parser->parseFile(__DIR__ . '/data/duplicities/otherfile.php');
		} catch (FileProcessingException $e) {
			// Expected
		}

		$storage = $this->parser->getStorage();
		$this->assertTrue(function_exists('constant'));
		$functions = [
			'tokenized' => $storage->getFunction('TokenReflection\\Test\\functionInNamespace'),
			'internal' => $storage->getFunction('constant'),
			'invalid' => $storage->getFunction('duplicitiesFunctions1')
		];

		// TestCase consistency with the internal reflection
		foreach ($functions as $function) {
			$this->internalConsistencyTest(new \ReflectionFunction('constant'), $function);
		}

		// TestCase cross-consistency
		foreach ($functions as $referenceType => $referenceFunction) {
			foreach ($functions as $type => $function) {
				if ($referenceType !== $type) {
					$this->crossConsistencyTest($referenceFunction, $function);
				}
			}
		}
	}


	public function testPropertyReflectionConsistency()
	{
		$this->parser->parseFile(__DIR__ . '/data/property/lines.php');

		$this->assertTrue(function_exists('constant'));
		$storage = $this->parser->getStorage();
		$properties = [
			'tokenized' => $storage->getClass('TokenReflection_Test_PropertyLines')->getProperty('lines'),
			'internal' => $storage->getClass('Exception')->getProperty('message')
		];

		// TestCase consistency with the internal reflection
		foreach ($properties as $property) {
			$this->internalConsistencyTest(new \ReflectionProperty('Exception', 'message'), $property);
		}

		// TestCase cross-consistency
		foreach ($properties as $referenceType => $referenceProperty) {
			foreach ($properties as $type => $property) {
				if ($referenceType !== $type) {
					$this->crossConsistencyTest($referenceProperty, $property);
				}
			}
		}
	}


	public function testMethodReflectionConsistency()
	{
		$this->parser->parseFile(__DIR__ . '/data/method/access-level.php');

		$storage = $this->parser->getStorage();
		$methods = [
			'tokenized' => $storage->getClass('TokenReflection_Test_MethodAccessLevelParent')->getMethod('privateNoExtended'),
			'internal' => $storage->getClass('Exception')->getMethod('getMessage')
		];

		// TestCase consistency with the internal reflection
		foreach ($methods as $method) {
			$this->internalConsistencyTest(new \ReflectionMethod('Exception', 'getMessage'), $method);
		}

		// TestCase cross-consistency
		foreach ($methods as $referenceType => $referenceMethod) {
			foreach ($methods as $type => $method) {
				if ($referenceType !== $type) {
					$this->crossConsistencyTest($referenceMethod, $method);
				}
			}
		}
	}


	public function testParameterReflectionConsistency()
	{
		$this->parser->parseFile(__DIR__ . '/data/parameter/optional-false.php');

		$storage = $this->parser->getStorage();
		$parameters = [
			'tokenized' => $storage->getFunction('tokenReflectionParameterOptionalFalse')->getParameter('one'),
			'internal' => $storage->getFunction('constant')->getParameter('const_name')
		];

		// TestCase consistency with the internal reflection
		foreach ($parameters as $parameter) {
			$this->internalConsistencyTest(new \ReflectionParameter('constant', 'const_name'), $parameter);
		}

		// TestCase cross-consistency
		foreach ($parameters as $referenceType => $referenceParameter) {
			foreach ($parameters as $type => $parameter) {
				if ($referenceType !== $type) {
					$this->crossConsistencyTest($referenceParameter, $parameter);
				}
			}
		}
	}


	/**
	 * Tests API consistency of two TR reflections.
	 *
	 * @param ReflectionInterface $reference Reference reflection
	 * @param ReflectionInterface $token Tested reflection
	 */
	private function crossConsistencyTest(ReflectionInterface $reference, ReflectionInterface $token)
	{
		$this->performConsistencyTest(new \ReflectionClass($reference), new \ReflectionClass($token));
	}


	/**
	 * Tests API consistency of an internal reflection and TR.
	 *
	 * @param \Reflector $reference Reference reflection
	 * @param ReflectionInterface $token Tested reflection
	 */
	private function internalConsistencyTest(\Reflector $reference, ReflectionInterface $token)
	{
		$this->performConsistencyTest(new \ReflectionClass($reference), new \ReflectionClass($token));
	}


	/**
	 * Tests API consistency of two classes.
	 *
	 * @param \ReflectionClass $reference Reference class reflection
	 * @param \ReflectionClass $test Tested class reflection
	 */
	private function performConsistencyTest(\ReflectionClass $reference, \ReflectionClass $test)
	{
		static $skip = [
			'*' => ['addReason' => TRUE, 'getReasons' => TRUE, 'hasReasons' => TRUE],
			'ApiGen\\TokenReflection\\Php\\ReflectionProperty' => ['setDefaultValue' => TRUE]
		];

		$methods = $reference->getMethods(\ReflectionMethod::IS_PUBLIC);
		foreach ($methods as $method) {
			if ($method->isStatic()) {
				continue;
			}

			if (isset($skip['*'][$method->getName()])) {
				continue;
			}

			foreach ($skip as $className => $skipping) {
				if (isset($skipping[$method->getName()]) && ($className === $test->getName() || $test->isSubclassOf($className))) {
					continue 2;
				}
			}

			// $this->assertTrue($test->hasMethod($method->getName()), sprintf('%s::%s() (defined in %s)', $test->getName(), $method->getName(), $reference->getName()));
		}
	}

}
