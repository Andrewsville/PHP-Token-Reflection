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
 * @author Jaroslav Hanslík <kontakt@kukulich.cz>
 */

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Complete test.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 */
class CompleteTest extends Test
{
	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type = 'complete';

	/**
	 * Run a test on an example file.
	 */
	public function testParser()
	{
		$this->fileTest('parser');
	}

	/**
	 * Test a particular file.
	 *
	 * @param String $test Filename
	 */
	protected function fileTest($test)
	{
		$broker = new Broker(new Broker\Backend\Memory(), false);
		$filename = $this->getFilePath($test);

		$broker->processFile($filename);
		require_once $filename;

		foreach ($broker->getClasses() as $classReflection) {
			$this->reflectionTest(new \ReflectionClass($classReflection->getName()), $classReflection);
		}
	}

	/**
	 * Test a particular reflection object by comparison with an appropriate internal reflection object.
	 *
	 * @param \Reflector $internal Internal reflection object
	 * @param \TokenReflection\ReflectionBase $token TokenReflection object
	 */
	protected function reflectionTest(\Reflector $internal, ReflectionBase $token)
	{
		$skip = array(
			'invoke' => true, '__clone' => true, // Not possible to test
			'getDeclaringClass' => true, 'getParentClass' => true, 'getParentClasses' => true, 'getDeclaringFunction' => true, 'getClass' => true, 'getPrototype' => true // Recursion prevention (will be tested individually later)
		);

		$internalReflection = $this->getReflectionReflection($internal);
		$tokenReflection = $this->getReflectionReflection($token);

		foreach ($internalReflection->getMethods() as $method) {
			// Skip certain methods
			if (isset($skip[$method->getName()])) {
				continue;
			}

			// Only methods without parameters will be automatically checked
			if (0 === $method->getNumberOfParameters()) {
				// Check if TokenReflection contains the checked method
				$this->assertTrue($tokenReflection->hasMethod($method->getName()), sprintf('%s does not contain method %s.', $tokenReflection->getName(), $method->getName()));

				// Run return value comparison
				$this->compareResults($internal, $token, $method, $tokenReflection->getMethod($method->getName()));
			}
		}

		// Individual tests for individual reflection types
		switch (true) {
			case $internal instanceof \ReflectionClass:
				$this->reflectionClassTest($internal, $token);
				break;
			case $internal instanceof \ReflectionFunction:
				$this->reflectionFunctionTest($internal, $token);
				break;
			case $internal instanceof \ReflectionMethod:
				$this->reflectionMethodTest($internal, $token);
				break;
			case $internal instanceof \ReflectionParameter:
				$this->reflectionParameterTest($internal, $token);
				break;
			case $internal instanceof \ReflectionProperty:
				$this->reflectionPropertyTest($internal, $token);
				break;
		}
	}

	/**
	 * Compare results of an internal reflection method and its TokenReflection counterpart.
	 *
	 * @param \Reflector $internal Internal reflection object
	 * @param \TokenReflection\ReflectionBase $token TokenReflection object
	 * @param \ReflectionMethod $internalMethod Tested method reflection (internal)
	 * @param \ReflectionMethod $tokenMethod Tested method reflection (TokenReflection)
	 */
	protected function compareResults(\Reflector $internal, ReflectionBase $token, \ReflectionMethod $internalMethod, \ReflectionMethod $tokenMethod)
	{
		// Internal reflection value
		try {
			$internalValue = $internalMethod->invoke($internal);
		} catch (\ReflectionException $e) {
			try {
				$tokenValue = $tokenMethod->invoke($token);
				$this->fail(sprintf('%s::%s() for %s is supposed to throw a \\TokenReflection\\Exception descendant.', get_class($token), $internalMethod->getName(), $token->getName()));
			} catch (\Exception $e) {
				$this->assertInstanceOf('\\TokenReflection\\Exception', $e, sprintf('%s::%s() for %s is supposed to throw a \\TokenReflection\\Exception descendant.', get_class($token), $internalMethod->getName(), $token->getName()));
				return;
			}
		}

		// Token reflection value
		$tokenValue = $tokenMethod->invoke($token);

		if (is_scalar($internalValue)) {
			// Return value is a scalar
			$this->assertTrue(is_scalar($tokenValue), sprintf('Return value of %s::%s() for %s has to be scalar.', get_class($token), $internalMethod->getName(), $token->getName()));
			$this->assertSame($internalValue, $tokenValue, sprintf('Return values of %s::%s() for %s do not match.', get_class($token), $internalMethod->getName(), $token->getName()));
		} elseif (is_array($internalValue)) {
			// Return value is an array
			$this->assertTrue(is_array($tokenValue), sprintf('Return value of %s::%s() for %s has to be an array.', get_class($token), $internalMethod->getName(), $token->getName()));
			$this->arrayTest($internalValue, $tokenValue, $internalMethod);
		} elseif (is_object($internalValue)) {
			// Return value is an object
			if ($internalValue instanceof \Reflector) {
				// Return value is a reflection -> run the same test recursively on them
				$this->assertTrue(is_object($tokenValue), sprintf('Return value of %s::%s() for %s has to an object.', get_class($token), $internalMethod->getName(), $token->getName()));
				$this->assertInstanceOf('\\TokenReflection\\ReflectionBase', $tokenValue, sprintf('Return value of %s::%s() for %s has to be an instance of \\TokenReflection\\ReflectionBase.', get_class($token), $internalMethod->getName(), $token->getName()));
				$this->reflectionTest($internalValue, $tokenValue);
			} else {
				// Otherwise return values have to be equal
				$this->assertEquals($internalValue, $tokenValue, sprintf('Returns values of %s::%s() for %s do not match.', get_class($token), $internalMethod->getName(), $token->getName()));
			}
		}
	}

	/**
	 * Recursively compare arrays.
	 *
	 * @param array $internal Internal reflection return value
	 * @param array $token TokenReflection return value
	 * @param \Reflector $parent Parent internal reflection object
	 */
	protected function arrayTest(array $internal, array $token, \Reflector $parent)
	{
		$this->assertSame(array_keys($internal), array_keys($token), sprintf('Keys of return values of %s do not match.', $parent->getName()));

		foreach ($internal as $key => $value) {
			if (is_scalar($value)) {
				$this->assertTrue(is_scalar($token[$key]), sprintf('%s result index %s has to be scalar.', $parent->getName(), $key));
				$this->assertSame($value, $token[$key], sprintf('%s result values of index %s do not match.', $parent->getName(), $key));
			} elseif (is_object($value)) {
				if ($value instanceof \Reflector) {
					$this->assertInstanceOf('\\TokenReflection\\ReflectionBase', $token[$key], sprintf('%s result index %s has to be an instance of \\TokenReflection\\ReflectionBase.', $parent->getName(), $key));
					$this->reflectionTest($value, $token[$key]);
				} else {
					$this->assertEquals($value, $token[$key], sprintf('%s result values of index %s do not match.', $parent->getName(), $key));
				}
			} elseif (is_array($value)) {
				$this->assertTrue(is_array($token[$key]), sprintf('%s result index %s has to be an array.', $parent->getName(), $key));
			}
		}
	}

	/**
	 * Tests ReflectionClass specific features.
	 *
	 * @param \ReflectionClass $internal Internal reflection object
	 * @param \TokenReflection\ReflectionClass $token TokenReflection object
	 */
	protected function reflectionClassTest(\ReflectionClass $internal, ReflectionClass $token)
	{

	}

	/**
	 * Tests ReflectionFunction specific features.
	 *
	 * @param \ReflectionFunction $internal Internal reflection object
	 * @param \TokenReflection\ReflectionFunction $token TokenReflection object
	 */
	protected function reflectionFunctionTest(\ReflectionFunction $internal, ReflectionFunction $token)
	{

	}

	/**
	 * Tests ReflectionMethod specific features.
	 *
	 * @param \ReflectionMethod $internal Internal reflection object
	 * @param \TokenReflection\ReflectionMethod $token TokenReflection object
	 */
	protected function reflectionMethodTest(\ReflectionMethod $internal, ReflectionMethod $token)
	{

	}

	/**
	 * Tests ReflectionProperty specific features.
	 *
	 * @param \ReflectionProperty $internal Internal reflection object
	 * @param \TokenReflection\ReflectionProperty $token TokenReflection object
	 */
	protected function reflectionPropertyTest(\ReflectionProperty $internal, ReflectionProperty $token)
	{

	}

	/**
	 * Tests ReflectionParameter specific features.
	 *
	 * @param \ReflectionParameter $internal Internal reflection object
	 * @param \TokenReflection\ReflectionParameter $token TokenReflection object
	 */
	protected function reflectionParameterTest(\ReflectionParameter $internal, ReflectionParameter $token)
	{

	}

	/**
	 * Returns a reflection reflection.
	 *
	 * @param \Reflector|\TokenReflection\ReflectionBase $reflection Reflection object
	 * @return \ReflectionCLass
	 * @throws \InvalidArgumentException If an invalid argument was provided
	 */
	protected function getReflectionReflection($reflection)
	{
		static $internal = array(), $token = array();

		$type = get_class($reflection);
		if ($reflection instanceof ReflectionBase) {
			if (!isset($token[$type])) {
				$token[$type] = new \ReflectionClass($reflection);
			}

			return $token[$type];
		} elseif ($reflection instanceof \Reflector) {
			if (!isset($internal[$type])) {
				$internal[$type] = new \ReflectionClass($reflection);
			}

			return $internal[$type];
		} else {
			throw new \InvalidArgumentException('Invalid reflection provided');
		}
	}
}
