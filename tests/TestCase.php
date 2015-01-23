<?php

namespace ApiGen\TokenReflection\Tests;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Broker\MemoryStorage;
use ApiGen\TokenReflection\Reflection\ReflectionClass;
use ApiGen\TokenReflection\Reflection\ReflectionConstant;
use ApiGen\TokenReflection\Reflection\ReflectionFile;
use ApiGen\TokenReflection\Reflection\ReflectionFunction;
use ApiGen\TokenReflection\Reflection\ReflectionMethod;
use ApiGen\TokenReflection\Reflection\ReflectionParameter;
use ApiGen\TokenReflection\Reflection\ReflectionProperty;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\ReflectionMethodInterface;
use ApiGen\TokenReflection\ReflectionParameterInterface;
use ApiGen\TokenReflection\ReflectionPropertyInterface;
use PHPUnit_Framework_TestCase;


abstract class TestCase extends PHPUnit_Framework_TestCase
{

	/**
	 * @var string
	 */
	protected $type;


	/**
	 * @param mixed $test
	 * @return ReflectionFile
	 */
	protected function getFileTokenReflection($test)
	{
		return $this->getBroker()->processFile($this->getFilePath($test));
	}


	/**
	 * @param string $test
	 * @param bool $fromString
	 * @return \stdClass
	 */
	protected function getClassReflection($test, $fromString = FALSE)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getClassInternalReflection($test);
		$reflection->token = $this->getClassTokenReflection($test, $fromString);
		return $reflection;
	}


	/**
	 * @param string $test
	 * @param bool $fromString
	 * @return \stdClass
	 */
	protected function getMethodReflection($test, $fromString = FALSE)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getMethodInternalReflection($test);
		$reflection->token = $this->getMethodTokenReflection($test, $fromString);
		return $reflection;
	}


	/**
	 * @param string $test
	 * @param bool $fromString
	 * @return \stdClass
	 */
	protected function getPropertyReflection($test, $fromString = FALSE)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getPropertyInternalReflection($test);
		$reflection->token = $this->getPropertyTokenReflection($test, $fromString);
		return $reflection;
	}


	/**
	 * @param string $test
	 * @param bool $fromString
	 * @return \stdClass
	 */
	protected function getFunctionReflection($test, $fromString = FALSE)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getFunctionInternalReflection($test);
		$reflection->token = $this->getFunctionTokenReflection($test, $fromString);
		return $reflection;
	}


	/**
	 * @param string $test
	 * @param bool $fromString
	 * @return \stdClass
	 */
	protected function getParameterReflection($test, $fromString = FALSE)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getParameterInternalReflection($test);
		$reflection->token = $this->getParameterTokenReflection($test, $fromString);
		return $reflection;
	}


	/**
	 * @param string $test
	 * @return \ReflectionClass
	 */
	protected function getClassInternalReflection($test)
	{
		require_once $this->getFilePath($test);
		return new \ReflectionClass($this->getClassName($test));
	}


	/**
	 * Returns internal method reflection.
	 *
	 * @param string $test
	 * @return \ReflectionMethod
	 */
	protected function getMethodInternalReflection($test)
	{
		return $this->getClassInternalReflection($test)->getMethod($this->getMethodName($test));
	}


	/**
	 * @param string $test
	 * @return \ReflectionProperty
	 */
	protected function getPropertyInternalReflection($test)
	{
		return $this->getClassInternalReflection($test)->getProperty($this->getPropertyName($test));
	}


	/**
	 * @param string $test
	 * @return \ReflectionFunction
	 */
	protected function getFunctionInternalReflection($test)
	{
		require_once $this->getFilePath($test);
		return new \ReflectionFunction($this->getFunctionName($test));
	}


	/**
	 * @param string $test
	 * @return \ReflectionParameter
	 */
	protected function getParameterInternalReflection($test)
	{
		require_once $this->getFilePath($test);
		$function = new \ReflectionFunction($this->getFunctionName($test));
		$parameters = $function->getParameters();
		return $parameters[0];
	}


	/**
	 * @param string $test
	 * @param bool $fromString
	 * @return ReflectionClass
	 */
	protected function getClassTokenReflection($test, $fromString = FALSE)
	{
		$broker = $this->getBroker();
		if ($fromString) {
			$source = file_get_contents($fileName = $this->getFilePath($test));
			$broker->processString($source, $fileName);
		} else {
			$broker->processFile($this->getFilePath($test));
		}
		return $broker->getClass($this->getClassName($test));
	}


	/**
	 * @param string $test
	 * @param bool $fromString
	 * @return ReflectionMethod
	 */
	protected function getMethodTokenReflection($test, $fromString = FALSE)
	{
		return $this->getClassTokenReflection($test, $fromString)
			->getMethod($this->getMethodName($test));
	}


	/**
	 * @param string $test
	 * @param bool $fromString
	 * @return ReflectionProperty
	 */
	protected function getPropertyTokenReflection($test, $fromString = FALSE)
	{
		return $this->getClassTokenReflection($test, $fromString)
			->getProperty($this->getPropertyName($test));
	}


	/**
	 * @param string $test
	 * @param bool $fromString
	 * @return ReflectionConstant
	 */
	protected function getConstantTokenReflection($test, $fromString = FALSE)
	{
		return $this->getClassTokenReflection($test, $fromString)
			->getConstantReflection($this->getConstantName($test));
	}


	/**
	 * @param string $test
	 * @param bool $fromString
	 * @return ReflectionFunction
	 */
	protected function getFunctionTokenReflection($test, $fromString = FALSE)
	{
		$broker = $this->getBroker();
		if ($fromString) {
			$source = file_get_contents($fileName = $this->getFilePath($test));
			$broker->processString($source, $fileName);
		} else {
			$broker->processFile($this->getFilePath($test));
		}
		return $broker->getFunction($this->getFunctionName($test));
	}


	/**
	 * @param string $test
	 * @param bool $fromString
	 * @return ReflectionParameter
	 */
	protected function getParameterTokenReflection($test, $fromString = FALSE)
	{
		$broker = $this->getBroker();
		if ($fromString) {
			$source = file_get_contents($fileName = $this->getFilePath($test));
			$broker->processString($source, $fileName);
		} else {
			$broker->processFile($this->getFilePath($test));
		}
		$parameters = $broker->getFunction($this->getFunctionName($test))->getParameters();
		return $parameters[0];
	}


	/**
	 * @param string $test
	 * @return string
	 */
	protected function getFilePath($test)
	{
		$file = preg_replace_callback('~[A-Z]~', function ($matches) {
			return '-' . strtolower($matches[0]);
		}, $test);
		return realpath(__DIR__ . '/data/' . $this->type . '/' . $file . '.php');
	}


	/**
	 * @param string $test
	 * @return string
	 */
	protected function getClassName($test)
	{
		return 'TokenReflection_Test_' . ucfirst($this->type) . ucfirst($test);
	}


	/**
	 * @param string $test
	 * @return string
	 */
	protected function getMethodName($test)
	{
		return $test;
	}


	/**
	 * @param string $test
	 * @return string
	 */
	protected function getPropertyName($test)
	{
		return $test;
	}


	/**
	 * @param string $test
	 * @return string
	 */
	protected function getConstantName($test)
	{
		return strtoupper(preg_replace_callback('~[A-Z]~', function ($matches) {
			return '_' . $matches[0];
		}, $test));
	}


	/**
	 * @param string $test
	 * @return string
	 */
	protected function getFunctionName($test)
	{
		return 'tokenReflection' . ucfirst($this->type) . ucfirst($test);
	}


	/**
	 * @return Broker
	 */
	protected function getBroker()
	{
		static $broker = NULL;
		if (NULL === $broker) {
			$broker = new Broker(new MemoryStorage);
		}
		return $broker;
	}


	/**
	 * Returns all filters combinations.
	 *
	 * @param array $filters
	 * @return array
	 */
	protected function getFilterCombinations(array $filters)
	{
		$combinations = [];

		for ($i = 0; $i < pow(2, count($filters)); $i++) {
			$combination = 0;
			for ($j = 0; $j < count($filters); $j++) {
				if ($i % pow(2, $j + 1) < pow(2, $j)) {
					$combination |= $filters[$j];
				}
			}

			$combinations[] = $combination;
		}

		return $combinations;
	}

}
