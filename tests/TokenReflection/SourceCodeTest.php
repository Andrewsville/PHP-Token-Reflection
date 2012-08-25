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
 * TR library source code test.
 */
class SourceCodeTest extends Test
{
	/**
	 * Tests if all methods have annotations.
	 */
	public function testAnnotationsPresence()
	{
		$broker = $this->createBroker();
		$broker->processDirectory(__DIR__ . '/../../TokenReflection');

		$classes = $broker->getClasses();
		$this->assertGreaterThan(0, count($classes));

		foreach ($classes as $class) {
			$this->assertNotSame(false, $class->getDocComment(), $class->getPrettyName());

			foreach ($class->getMethods() as $method) {
				if (!$method->isInternal()) {
					$this->assertNotSame(false, $method->getDocComment(), $method->getPrettyName());
				}
			}
			foreach ($class->getProperties() as $property) {
				if (!$property->isInternal()) {
					$this->assertNotSame(false, $property->getDocComment(), $property->getPrettyName());
				}
			}
			foreach ($class->getConstantReflections() as $constant) {
				if (!$constant->isInternal()) {
					$this->assertNotSame(false, $constant->getDocComment(), $constant->getPrettyName());
				}
			}
		}
	}
}
