<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 2
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
 */

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

abstract class Test extends \PHPUnit_Framework_TestCase
{
	protected $type;

	protected function getClassReflection($test)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getClassInternalReflection($test);
		$reflection->token = $this->getClassTokenReflection($test);
		return $reflection;
	}

	protected function getMethodReflection($test)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getMethodInternalReflection($test);
		$reflection->token = $this->getMethodTokenReflection($test);
		return $reflection;
	}

	protected function getPropertyReflection($test)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getPropertyInternalReflection($test);
		$reflection->token = $this->getPropertyTokenReflection($test);
		return $reflection;
	}

	protected function getFunctionReflection($test)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getFunctionInternalReflection($test);
		$reflection->token = $this->getFunctionTokenReflection($test);
		return $reflection;
	}

	protected function getParameterReflection($test)
	{
		$reflection = new \stdClass();
		$reflection->internal = $this->getParameterInternalReflection($test);
		$reflection->token = $this->getParameterTokenReflection($test);
		return $reflection;
	}

	protected function getClassInternalReflection($test)
	{
		require_once $this->getFilePath($test);
		return new \ReflectionClass($this->getClassName($test));
	}

	protected function getMethodInternalReflection($test)
	{
		return $this->getClassInternalReflection($test)->getMethod($this->getMethodName($test));
	}

	protected function getPropertyInternalReflection($test)
	{
		return $this->getClassInternalReflection($test)->getProperty($this->getPropertyName($test));
	}

	protected function getFunctionInternalReflection($test)
	{
		require_once $this->getFilePath($test);
		return new \ReflectionFunction($this->getFunctionName($test));
	}

	protected function getParameterInternalReflection($test)
	{
		require_once $this->getFilePath($test);
		$function = new \ReflectionFunction($this->getFunctionName($test));
		$parameters = $function->getParameters();
		return $parameters[0];
	}

	protected function getClassTokenReflection($test)
	{
		$broker = $this->getBroker();
		$broker->processFile($this->getFilePath($test));
		return $broker->getClass($this->getClassName($test));
	}

	protected function getMethodTokenReflection($test)
	{
		return $this->getClassTokenReflection($test)->getMethod($this->getMethodName($test));
	}

	protected function getPropertyTokenReflection($test)
	{
		return $this->getClassTokenReflection($test)->getProperty($this->getPropertyName($test));
	}

	protected function getConstantTokenReflection($test)
	{
		return $this->getClassTokenReflection($test)->getConstantReflection($this->getConstantName($test));
	}

	protected function getFunctionTokenReflection($test)
	{
		$broker = $this->getBroker();
		$broker->processFile($this->getFilePath($test));
		return $broker->getFunction($this->getFunctionName($test));
	}

	protected function getParameterTokenReflection($test)
	{
		$broker = $this->getBroker();
		$broker->processFile($this->getFilePath($test));
		$parameters = $broker->getFunction($this->getFunctionName($test))->getParameters();
		return $parameters[0];
	}

	protected function getFilePath($test)
	{
		$file = preg_replace_callback('~[A-Z]~', function($matches) {
			return '-' . strtolower($matches[0]);
		}, $test);
		return realpath(__DIR__ . '/../data/' . $this->type . '/' . $file . '.php');
	}

	protected function getClassName($test)
	{
		return 'TokenReflection_Test_' . ucfirst($this->type) . ucfirst($test);
	}

	protected function getMethodName($test)
	{
		return $test;
	}

	protected function getPropertyName($test)
	{
		return $test;
	}

	protected function getConstantName($test)
	{
		return strtoupper(preg_replace_callback('~[A-Z]~', function($matches) {
			return '_' . $matches[0];
		}, $test));
	}

	protected function getFunctionName($test)
	{
		return 'tokenReflection' . ucfirst($this->type) . ucfirst($test);
	}

	protected function getBroker()
	{
		static $broker = null;
		if (null === $broker) {
			$broker = new Broker(new Broker\Backend\Memory());
		}
		return $broker;
	}

	protected function getFilterCombinations($filters)
	{
		$combinations = array();

		for ($i = 0; $i < pow(2, count($filters)); $i++)
		{
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
