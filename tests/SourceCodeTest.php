<?php

namespace ApiGen\TokenReflection\Tests;

/**
 * TR library source code test.
 */
class SourceCodeTest extends TestCase
{

	/**
	 * Tests if all methods have annotations.
	 */
	public function testAnnotationsPresence()
	{
		$broker = $this->createBroker();
		$broker->processDirectory(__DIR__ . '/../src');

		$classes = $broker->getClasses();
		$this->assertGreaterThan(0, count($classes));

		foreach ($classes as $class) {
			foreach ($class->getMethods() as $method) {
				if (!$method->isInternal()) {
					$this->assertNotSame(FALSE, $method->getDocComment(), $method->getPrettyName());
				}
			}
			foreach ($class->getProperties() as $property) {
				if (!$property->isInternal()) {
					$this->assertNotSame(FALSE, $property->getDocComment(), $property->getPrettyName());
				}
			}
			foreach ($class->getConstantReflections() as $constant) {
				if (!$constant->isInternal()) {
					$this->assertNotSame(FALSE, $constant->getDocComment(), $constant->getPrettyName());
				}
			}
		}
	}

}
