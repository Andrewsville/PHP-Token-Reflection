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
		$skip = array('invoke' => true, '__clone' => true);

		$internalReflection = $this->getReflectionReflection($internal);
		$tokenReflection = $this->getReflectionReflection($token);

		foreach ($internalReflection->getMethods() as $method) {
			if (isset($skip[$method->getName()])) {
				continue;
			}

			if (0 === $method->getNumberOfParameters()) {
				// Internal reflection value
				$internalValue = $method->invoke($internal);

				// Check if the method exists in the token reflection
				$this->assertTrue($tokenReflection->hasMethod($method->getName()), sprintf('%s does not contain method %s.', get_class($token), $method->getName()));

				// Token reflection value
				$tokenValue = $tokenReflection->getMethod($method->getName())->invoke($token);

				if (is_scalar($internalValue)) {
					// Return value is a scalar
					$this->assertTrue(is_scalar($tokenValue), sprintf('Return value of %s::%s() has to be scalar.', get_class($token), $method->getName()));
					$this->assertSame($internalValue, $tokenValue, sprintf('Returns values of %s::%s() do not match.', get_class($token), $method->getName()));
				} elseif (is_array($internalValue)) {
					// Return value is an array
					$this->arrayTest($internalValue, $tokenValue, $method);
				} elseif (is_object($internalValue)) {
					// Return value is an object
					if ($internalValue instanceof \Reflector) {
						// Return value is a reflection -> run the same test recursively on them
						$this->assertInstanceOf($tokenValue, '\\TokenReflection\\IReflection', sprintf('Return value of %s::%s() has to be an instance of \TokenReflection\IReflection.', get_class($token), $method->getName()));
						$this->reflectionTest($internalValue, $tokenValue);
					} else {
						// Otherwise return values have to be equal
						$this->assertEquals($internalValue, $tokenValue, sprintf('Returns values of %s::%s() do not match.', get_class($token), $method->getName()));
					}
				}
			}
		}

		switch (true) {
			case $internal instanceof \ReflectionClass:
				$this->reflectionClassTest($token, $internal);
				break;
			case $internal instanceof \ReflectionFunction:
				$this->reflectionFunctionTest($token, $internal);
				break;
			case $internal instanceof \ReflectionMethod:
				$this->reflectionMethodTest($token, $internal);
				break;
			case $internal instanceof \ReflectionParameter:
				$this->reflectionParameterTest($token, $internal);
				break;
			case $internal instanceof \ReflectionProperty:
				$this->reflectionPropertyTest($token, $internal);
				break;
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
					$this->assertInstanceOf($tokenValue, '\\TokenReflection\\IReflection', sprintf('%s result index %s has to be an instance of \\TokenReflection\\IReflection.', $parent->getName(), $key));
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
	 * @param \TokenReflection\ReflectionClass $token TokenReflection object
	 * @param \ReflectionClass $internal Internal reflection object
	 */
	protected function reflectionClassTest(ReflectionClass $token, \ReflectionClass $internal)
	{

	}

	/**
	 * Tests ReflectionFunction specific features.
	 *
	 * @param \TokenReflection\ReflectionFunction $token TokenReflection object
	 * @param \ReflectionFunction $internal Internal reflection object
	 */
	protected function reflectionFunctionTest(ReflectionFunction $token, \ReflectionFunction $internal)
	{

	}

	/**
	 * Tests ReflectionMethod specific features.
	 *
	 * @param \TokenReflection\ReflectionMethod $token TokenReflection object
	 * @param \ReflectionMethod $internal Internal reflection object
	 */
	protected function reflectionMethodTest(ReflectionMethod $token, \ReflectionMethod $internal)
	{

	}

	/**
	 * Tests ReflectionProperty specific features.
	 *
	 * @param \TokenReflection\ReflectionProperty $token TokenReflection object
	 * @param \ReflectionProperty $internal Internal reflection object
	 */
	protected function reflectionPropertyTest(ReflectionProperty $token, \ReflectionProperty $internal)
	{

	}

	/**
	 * Tests ReflectionParameter specific features.
	 *
	 * @param \TokenReflection\ReflectionParameter $token TokenReflection object
	 * @param \ReflectionParameter $internal Internal reflection object
	 */
	protected function reflectionParameterTest(ReflectionParameter $token, \ReflectionParameter $internal)
	{

	}

	/**
	 * Returns a reflection reflection.
	 *
	 * @param \Reflector $reflection Internal reflection object
	 * @return \ReflectionCLass
	 * @throws \InvalidArgumentException If an invalid argument was provided
	 */
	protected function getReflectionReflection($reflection)
	{
		static $internal = array(), $token = array();

		$type = get_class($reflection);
		if ($reflection instanceof IReflection) {
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
