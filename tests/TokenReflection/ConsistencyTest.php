<?php
/**
 * PHP Token Reflection
 *
 * Version 1.2.4
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
 * Consistency tests.
 *
 * Tests of consistency between:
 * - the internal reflection and TR
 * - each internal reflection type
 */
class ConsistencyTest extends Test
{
	/**
	 * Tests consistency with the internal reflection.
	 */
	public function testInternalConstantReflectionConsistency()
	{
		$broker = $this->createBroker();
		$broker->processFile(__DIR__ . '/../data/constant/in-namespace.php');
		try {
			$broker->processFile(__DIR__ . '/../data/duplicities/otherfile.php');
		} catch (Exception\FileProcessingException $e) {
			// Expected
		}

		$this->assertNotSame(null, @constant('PHP_INT_MAX'));
		$constants = array(
			'tokenized' => $broker->getConstant('TokenReflection\\Test\\CONSTANT_IN_NAMESPACE'),
			'internal' => $broker->getConstant('PHP_INT_MAX'),
			'invalid' => $broker->getConstant('DUPLICITIES_CONSTANTS_1')
		);

		// Test cross-consistency
		foreach ($constants as $referenceType => $referenceConstant) {
			foreach ($constants as $type => $constant) {
				if ($referenceType !== $type) {
					$this->crossConsistencyTest($referenceConstant, $constant);
				}
			}
		}
	}

	/**
	 * Tests consistency with the internal reflection.
	 */
	public function testInternalClassReflectionConsistency()
	{
		$broker = $this->createBroker();
		$broker->processFile(__FILE__);
		try {
			$broker->processFile(__DIR__ . '/../data/duplicities/otherfile.php');
		} catch (Exception\FileProcessingException $e) {
			// Expected
		}

		$this->assertFalse(class_exists('Foo\\Bar', true));
		$classes = array(
			'tokenized' => $broker->getClass('TokenReflection\\ConsistencyTest'),
			'internal' => $broker->getClass('Exception'),
			'dummy' => $broker->getClass('Foo\\Bar'),
			'invalid' => $broker->getClass('duplicitiesClasses1')
		);

		// Test consistency with the internal reflection
		foreach ($classes as $class) {
			$this->classConsistencyTest(new \ReflectionClass(new \stdClass()), new \ReflectionClass($class));
		}

		// Test cross-consistency
		foreach ($classes as $referenceType => $referenceClass) {
			foreach ($classes as $type => $class) {
				if ($referenceType !== $type) {
					$this->crossConsistencyTest($referenceClass, $class);
				}
			}
		}
	}

	/**
	 * Tests consistency with the internal reflection.
	 */
	public function testInternalFunctionReflectionConsistency()
	{
		$broker = $this->createBroker();
		$broker->processFile(__DIR__ . '/../data/function/in-namespace.php');
		try {
			$broker->processFile(__DIR__ . '/../data/duplicities/otherfile.php');
		} catch (Exception\FileProcessingException $e) {
			// Expected
		}

		$this->assertTrue(function_exists('constant'));
		$functions = array(
			'tokenized' => $broker->getFunction('TokenReflection\\Test\\functionInNamespace'),
			'internal' => $broker->getFunction('constant'),
			'invalid' => $broker->getFunction('duplicitiesFunctions1')
		);

		// Test cross-consistency
		foreach ($functions as $referenceType => $referenceFunction) {
			foreach ($functions as $type => $function) {
				if ($referenceType !== $type) {
					$this->crossConsistencyTest($referenceFunction, $function);
				}
			}
		}
	}

	/**
	 * Tests API consistency of two TR reflections.
	 *
	 * @param \TokenReflection\IReflection $reference Reference reflection
	 * @param \TokenReflection\IReflection $token Tested reflection
	 */
	private function crossConsistencyTest(IReflection $reference, IReflection $token)
	{
		$this->classConsistencyTest(new \ReflectionClass($reference), new \ReflectionClass($token));
	}

	/**
	 * Tests API consistency of two classes.
	 *
	 * @param \ReflectionClass $reference Reference class reflection
	 * @param \ReflectionClass $test Tested class reflection
	 */
	private function classConsistencyTest(\ReflectionClass $reference, \ReflectionClass $test)
	{
		static $skip = array('addReason', 'getReasons', 'hasReasons', 'getClosureThis');

		$methods = $reference->getMethods(\ReflectionMethod::IS_PUBLIC);
		foreach ($methods as $method) {
			if (!$method->isStatic() && !in_array($method->getName(), $skip)) {
				$this->assertTrue($test->hasMethod($method->getName()), sprintf('%s::%s()', $test->getName(), $method->getName()));
			}
		}
	}
}
