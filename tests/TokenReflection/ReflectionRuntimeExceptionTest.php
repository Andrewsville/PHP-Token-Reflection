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
 * Broker test.
 */
class ReflectionRuntimeExceptionTest extends Test
{
	/**
	 * Test type.
	 *
	 * @var string
	 */
	protected $type = 'exception';

	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassImplementsInterface1()
	{
		$this->getDummyClassReflection()->implementsInterface(new \Exception());
	}

	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassImplementsInterface2()
	{
		$this->getDummyClassReflection()->implementsInterface($this->getBroker()->getClass('Exception'));
	}

	/**
	 * Tests an exception thrown when getting a method from a dummy class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassGetMethod()
	{
		$this->getDummyClassReflection()->getMethod('any');
	}

	/**
	 * Tests an exception thrown when getting a property from a dummy class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassGetProperty()
	{
		$this->getDummyClassReflection()->getProperty('any');
	}

	/**
	 * Tests an exception thrown when getting a static property from a dummy class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassGetStaticProperty()
	{
		$this->getDummyClassReflection()->getStaticPropertyValue('any', null);
	}

	/**
	 * Tests an exception thrown when setting a static property from a dummy class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassSetStaticProperty()
	{
		$this->getDummyClassReflection()->setStaticPropertyValue('foo', 'bar');
	}

	/**
	 * Tests an exception thrown when getting a constant value from a dummy class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassGetConstantValue()
	{
		$this->getDummyClassReflection()->getConstant('any');
	}

	/**
	 * Tests an exception thrown when getting a constant reflection from a dummy class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassGetConstantReflection()
	{
		$this->getDummyClassReflection()->getConstantReflection('any');
	}

	/**
	 * Tests an exception thrown when providing an invalid argument to isInstance() method.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyClassIsInstance()
	{
		$this->getDummyClassReflection()->isInstance(true);
	}

	/**
	 * Tests an exception thrown when trying to instantiate a non existent class.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyNewInstanceWithoutConstructor()
	{
		$this->getDummyClassReflection()->newInstanceWithoutConstructor();
	}

	/**
	 * Tests an exception thrown when trying to instantiate a non existent class.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyNewInstance()
	{
		$this->getDummyClassReflection()->newInstance(null);
	}

	/**
	 * Tests an exception thrown when trying to instantiate a non existent class.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testDummyNewInstanceArgs()
	{
		$this->getDummyClassReflection()->newInstanceArgs();
	}

	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassIsSubclassOf()
	{
		$this->getInternalClassReflection()->isSubclassOf(new \Exception());
	}

	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassImplementsInterface1()
	{
		$this->getInternalClassReflection()->implementsInterface(new \Exception());
	}

	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassImplementsInterface2()
	{
		$this->getInternalClassReflection()->implementsInterface($this->getBroker()->getClass('Exception'));
	}

	/**
	 * Tests an exception thrown when providing an invalid class name.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassImplementsInterface3()
	{
		$this->getInternalClassReflection()->implementsInterface('Exception');
	}

	/**
	 * Tests an exception thrown when getting a method from an internal class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassGetMethod()
	{
		$this->getDummyClassReflection()->getMethod('~non-existent~');
	}

	/**
	 * Tests an exception thrown when getting a property from an internal class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassGetProperty()
	{
		$this->getDummyClassReflection()->getProperty('~non-existent~');
	}

	/**
	 * Tests an exception thrown when getting a static property from an internal class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassGetStaticProperty()
	{
		$this->getDummyClassReflection()->getStaticPropertyValue('~non-existent~', null);
	}

	/**
	 * Tests an exception thrown when setting a static property from an internal class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassSetStaticProperty()
	{
		$this->getDummyClassReflection()->setStaticPropertyValue('~non', 'existent~');
	}

	/**
	 * Tests an exception thrown when getting a constant value from an internal class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassGetConstantValue()
	{
		$this->getDummyClassReflection()->getConstant('~non-existent~');
	}

	/**
	 * Tests an exception thrown when getting a constant reflection from an internal class reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassGetConstantReflection()
	{
		$this->getDummyClassReflection()->getConstantReflection('~non-existent~');
	}

	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassUsesTrait1()
	{
		$this->getInternalClassReflection()->usesTrait(new \Exception());
	}

	/**
	 * Tests an exception thrown when providing an invalid object.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassUsesTrait2()
	{
		$this->getInternalClassReflection()->usesTrait($this->getBroker()->getClass('Exception'));
	}

	/**
	 * Tests an exception thrown when providing an invalid class name.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalClassUsesTrait3()
	{
		$this->getInternalClassReflection()->usesTrait('Exception');
	}

	/**
	 * Returns an internal class reflection.
	 *
	 * @return \TokenReflection\Php\ReflectionClass
	 */
	private function getInternalClassReflection()
	{
		return $this->getBroker()->getClass(current(get_declared_classes()));
	}

	/**
	 * Returns a non existent class reflection.
	 *
	 * @return \TokenReflection\Dummy\ReflectionClass
	 */
	private function getDummyClassReflection()
	{
		static $className = 'foo_bar';

		if (class_exists($className, false)) {
			$this->markTestSkipped(sprintf('Class %s exists.', $className));
		}

		return $this->getBroker()->getClass($className);
	}
}
