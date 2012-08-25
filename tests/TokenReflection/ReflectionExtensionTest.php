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
 * Extension test.
 */
class ReflectionExtensionTest extends Test
{
	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type = 'extension';

	/**
	 * Tests an exception thrown when trying to create the reflection from a PHP internal reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalExtensionReflectionCreate()
	{
		Php\ReflectionExtension::create(new \ReflectionFunction('create_function'), $this->getBroker());
	}
}
