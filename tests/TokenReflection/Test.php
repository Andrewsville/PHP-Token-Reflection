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

require_once __DIR__ . '/../bootstrap.php';

/**
 * Abstract test.
 */
abstract class Test extends \PHPUnit_Framework_TestCase
{
	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Return a tokenized file reflection.
	 *
	 * @param mixed $test
	 * @return \TokenReflection\ReflectionFile
	 */
	protected function getFileTokenReflection($test)
	{
		return $this->getBroker()->processFile($this->getFilePath($test), true);
	}

	/**
	 * Returns class reflections.
	 *
	 * @param string $test
	 * @param boolean $fromString
	 * @return \stdClass
	 */
	protected function getClassReflection($test, $fromString = false)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getClassInternalReflection($test);
		$reflection->token = $this->getClassTokenReflection($test, $fromString);
		return $reflection;
	}

	/**
	 * Returns method reflections.
	 *
	 * @param string $test
	 * @param boolean $fromString
	 * @return \stdClass
	 */
	protected function getMethodReflection($test, $fromString = false)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getMethodInternalReflection($test);
		$reflection->token = $this->getMethodTokenReflection($test, $fromString);
		return $reflection;
	}

	/**
	 * Returns property reflections.
	 *
	 * @param string $test
	 * @param boolean $fromString
	 * @return \stdClass
	 */
	protected function getPropertyReflection($test, $fromString = false)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getPropertyInternalReflection($test);
		$reflection->token = $this->getPropertyTokenReflection($test, $fromString);
		return $reflection;
	}

	/**
	 * Returns function reflections.
	 *
	 * @param string $test
	 * @param boolean $fromString
	 * @return \stdClass
	 */
	protected function getFunctionReflection($test, $fromString = false)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getFunctionInternalReflection($test);
		$reflection->token = $this->getFunctionTokenReflection($test, $fromString);
		return $reflection;
	}

	/**
	 * Returns parameter reflections.
	 *
	 * @param string $test
	 * @param boolean $fromString
	 * @return \stdClass
	 */
	protected function getParameterReflection($test, $fromString = false)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getParameterInternalReflection($test);
		$reflection->token = $this->getParameterTokenReflection($test, $fromString);
		return $reflection;
	}

	/**
	 * Returns internal class reflection.
	 *
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
	 * Returns internal property reflection.
	 *
	 * @param string $test
	 * @return \ReflectionProperty
	 */
	protected function getPropertyInternalReflection($test)
	{
		return $this->getClassInternalReflection($test)->getProperty($this->getPropertyName($test));
	}

	/**
	 * Returns internal function reflection.
	 *
	 * @param string $test
	 * @return \ReflectionFunction
	 */
	protected function getFunctionInternalReflection($test)
	{
		require_once $this->getFilePath($test);
		return new \ReflectionFunction($this->getFunctionName($test));
	}

	/**
	 * Returns internal parameter reflection.
	 *
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
	 * Returns tokenized class reflection.
	 *
	 * @param string $test
	 * @param boolean $fromString
	 * @return \TokenReflection\ReflectionClass
	 */
	protected function getClassTokenReflection($test, $fromString = false)
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
	 * Returns tokenized method reflection.
	 *
	 * @param string $test
	 * @param boolean $fromString
	 * @return \TokenReflection\ReflectionMethod
	 */
	protected function getMethodTokenReflection($test, $fromString = false)
	{
		return $this->getClassTokenReflection($test, $fromString)->getMethod($this->getMethodName($test));
	}

	/**
	 * Returns tokenized property reflection.
	 *
	 * @param string $test
	 * @param boolean $fromString
	 * @return \TokenReflection\ReflectionProperty
	 */
	protected function getPropertyTokenReflection($test, $fromString = false)
	{
		return $this->getClassTokenReflection($test, $fromString)->getProperty($this->getPropertyName($test));
	}

	/**
	 * Returns tokenized constant reflection.
	 *
	 * @param string $test
	 * @param boolean $fromString
	 * @return \TokenReflection\ReflectionConstant
	 */
	protected function getConstantTokenReflection($test, $fromString = false)
	{
		return $this->getClassTokenReflection($test, $fromString)->getConstantReflection($this->getConstantName($test));
	}

	/**
	 * Returns tokenized function reflection.
	 *
	 * @param string $test
	 * @param boolean $fromString
	 * @return \TokenReflection\ReflectionFunction
	 */
	protected function getFunctionTokenReflection($test, $fromString = false)
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
	 * Returns tokenized parameter reflection.
	 *
	 * @param string $test
	 * @param boolean $fromString
	 * @return \TokenReflection\ReflectionParameter
	 */
	protected function getParameterTokenReflection($test, $fromString = false)
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
	 * Returns test file path.
	 *
	 * @param string $test
	 * @return string
	 */
	protected function getFilePath($test)
	{
		$file = preg_replace_callback('~[A-Z]~', function($matches) {
			return '-' . strtolower($matches[0]);
		}, $test);
		return realpath(__DIR__ . '/../data/' . $this->type . '/' . $file . '.php');
	}

	/**
	 * Returns test class name.
	 *
	 * @param string $test
	 * @return string
	 */
	protected function getClassName($test)
	{
		return 'TokenReflection_Test_' . ucfirst($this->type) . ucfirst($test);
	}

	/**
	 * Returns test method name.
	 *
	 * @param string $test
	 * @return string
	 */
	protected function getMethodName($test)
	{
		return $test;
	}

	/**
	 * Returns test property name.
	 *
	 * @param string $test
	 * @return string
	 */
	protected function getPropertyName($test)
	{
		return $test;
	}

	/**
	 * Returns test constant name.
	 *
	 * @param string $test
	 * @return string
	 */
	protected function getConstantName($test)
	{
		return strtoupper(preg_replace_callback('~[A-Z]~', function($matches) {
			return '_' . $matches[0];
		}, $test));
	}

	/**
	 * Returns test function name.
	 *
	 * @param string $test
	 * @return string
	 */
	protected function getFunctionName($test)
	{
		return 'tokenReflection' . ucfirst($this->type) . ucfirst($test);
	}

	/**
	 * Returns a new broker instance.
	 *
	 * @return \TokenReflection\Broker
	 */
	public function createBroker()
	{
		return new Broker(new Broker\Backend\Memory());
	}

	/**
	 * Returns broker instance.
	 *
	 * @return \TokenReflection\Broker
	 */
	protected function getBroker()
	{
		static $broker = null;
		if (null === $broker) {
			$broker = $this->createBroker();
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
		$combinations = array();

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
