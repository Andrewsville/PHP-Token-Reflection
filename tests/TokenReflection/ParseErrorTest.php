<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.2
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
 * Parse errors test.
 */
class ParseErrorTest extends Test
{
	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type = 'parseerror';

	/**
	 * Tests an exception thrown when trying to pass an empty token stream.
	 *
	 * @expectedException \TokenReflection\Exception\ParseException
	 */
	public function testEmptyTokenStream()
	{
		$stream = new Stream\StringStream('', 'foo.php');
		$reflection = new ReflectionClass($stream, $this->getBroker());
	}

	/**
	 * Tests invalid source code handling.
	 *
	 * @param string $testName Test name
	 * @dataProvider invalidClassSourceCodeProvider
	 */
	public function testClasses($testName)
	{
		$this->performTest($testName);
	}

	/**
	 * Tests invalid source code handling.
	 *
	 * @param string $testName Test name
	 * @dataProvider invalidConstantSourceCodeProvider
	 */
	public function testConstants($testName)
	{
		$this->performTest($testName);
	}

	/**
	 * Tests invalid source code handling.
	 *
	 * @param string $testName Test name
	 * @dataProvider invalidFileSourceCodeProvider
	 */
	public function testFiles($testName)
	{
		$this->performTest($testName);
	}

	/**
	 * Tests invalid source code handling.
	 *
	 * @param string $testName Test name
	 * @dataProvider invalidFunctionBaseSourceCodeProvider
	 */
	public function testFunctionBases($testName)
	{
		$this->performTest($testName);
	}

	/**
	 * Tests invalid source code handling.
	 *
	 * @param string $testName Test name
	 * @dataProvider invalidParameterSourceCodeProvider
	 */
	public function testParameter($testName)
	{
		$this->performTest($testName);
	}

	/**
	 * Tests invalid source code handling.
	 *
	 * @param string $testName Test name
	 * @dataProvider invalidPropertySourceCodeProvider
	 */
	public function testProperty($testName)
	{
		$this->performTest($testName);
	}

	/**
	 * Performs a test.
	 *
	 * @param string $testName Test name
	 */
	private function performTest($testName)
	{
		try {
			$broker = $this->getBroker();
			$broker->processFile($this->getFilePath($testName));

			$this->fail(sprintf('A parse exception was expected for test %s.', $testName));
		} catch (Exception\BrokerException $e) {
			$parse = $e->getPrevious();
			$this->assertInstanceOf('TokenReflection\Exception\ParseException', $parse);
		}
	}

	/**
	 * Provider for invalid class source code handling tests.
	 *
	 * @return array
	 */
	public function invalidClassSourceCodeProvider()
	{
		return $this->prepareTests('invalid-class', 10);
	}

	/**
	 * Provider for invalid constant source code handling tests.
	 *
	 * @return array
	 */
	public function invalidConstantSourceCodeProvider()
	{
		return $this->prepareTests('invalid-constant', 4);
	}

	/**
	 * Provider for invalid file source code handling tests.
	 *
	 * @return array
	 */
	public function invalidFileSourceCodeProvider()
	{
		return $this->prepareTests('invalid-file', 7);
	}

	/**
	 * Provider for invalid function/method source code handling tests.
	 *
	 * @return array
	 */
	public function invalidFunctionBaseSourceCodeProvider()
	{
		return $this->prepareTests('invalid-functionbase', 3);
	}

	/**
	 * Provider for invalid function/method source code handling tests.
	 *
	 * @return array
	 */
	public function invalidParameterSourceCodeProvider()
	{
		return $this->prepareTests('invalid-parameter', 3);
	}

	/**
	 * Provider for invalid function/method source code handling tests.
	 *
	 * @return array
	 */
	public function invalidPropertySourceCodeProvider()
	{
		return $this->prepareTests('invalid-property', 3);
	}

	/**
	 * Prepares test names.
	 *
	 * @param string $prefix Test name prefix
	 * @param integer $count Test count
	 * @return array
	 */
	private function prepareTests($prefix, $count)
	{
		return array_map(function($i) use ($prefix) {
			return array($prefix . '-' . $i);
		}, range(1, $count));
	}
}
