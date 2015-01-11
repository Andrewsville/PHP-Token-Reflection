<?php

namespace ApiGen\TokenReflection\Tests;

use ApiGen;
use ApiGen\TokenReflection\ReflectionClass;
use ApiGen\TokenReflection\Stream\StringStream;


class ParseErrorTest extends TestCase
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
	 * @expectedException ApiGen\TokenReflection\Exception\ParseException
	 */
	public function testEmptyTokenStream()
	{
		$stream = new StringStream('', 'foo.php');
		new ReflectionClass($stream, $this->getBroker());
	}


	/**
	 * Tests invalid source code handling.
	 *
	 * @param string $testName TestCase name
	 * @dataProvider invalidClassSourceCodeProvider
	 * @expectedException ApiGen\TokenReflection\Exception\ParseException
	 */
	public function testClasses($testName)
	{
		$this->performTest($testName);
	}


	/**
	 * Tests invalid source code handling.
	 *
	 * @param string $testName TestCase name
	 * @dataProvider invalidConstantSourceCodeProvider
	 * @expectedException ApiGen\TokenReflection\Exception\ParseException
	 */
	public function testConstants($testName)
	{
		$this->performTest($testName);
	}


	/**
	 * Tests invalid source code handling.
	 *
	 * @param string $testName TestCase name
	 * @dataProvider invalidFileSourceCodeProvider
	 * @expectedException ApiGen\TokenReflection\Exception\ParseException
	 */
	public function testFiles($testName)
	{
		$this->performTest($testName);
	}


	/**
	 * Tests invalid source code handling.
	 *
	 * @param string $testName TestCase name
	 * @dataProvider invalidFunctionBaseSourceCodeProvider
	 * @expectedException ApiGen\TokenReflection\Exception\ParseException
	 */
	public function testFunctionBases($testName)
	{
		$this->performTest($testName);
	}


	/**
	 * Tests invalid source code handling.
	 *
	 * @param string $testName TestCase name
	 * @dataProvider invalidParameterSourceCodeProvider
	 * @expectedException ApiGen\TokenReflection\Exception\ParseException
	 */
	public function testParameter($testName)
	{
		$this->performTest($testName);
	}


	/**
	 * Tests invalid source code handling.
	 *
	 * @param string $testName TestCase name
	 * @dataProvider invalidPropertySourceCodeProvider
	 * @expectedException ApiGen\TokenReflection\Exception\ParseException
	 */
	public function testProperty($testName)
	{
		$this->performTest($testName);
	}


	/**
	 * Performs a test.
	 *
	 * @param string $testName TestCase name
	 */
	private function performTest($testName)
	{
		$broker = $this->getBroker();
		$broker->processFile($this->getFilePath($testName));
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
	 * @param string $prefix TestCase name prefix
	 * @param integer $count TestCase count
	 * @return array
	 */
	private function prepareTests($prefix, $count)
	{
		return array_map(function ($i) use ($prefix) {
			return [$prefix . '-' . $i];
		}, range(1, $count));
	}
}
