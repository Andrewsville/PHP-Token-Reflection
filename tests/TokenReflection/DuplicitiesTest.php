<?php
/**
 * PHP Token Reflection
 *
 * Version 1.1
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
 * Duplicities handling test.
 */
class DuplicitiesTest extends Test
{
	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type = 'duplicities';

	/**
	 * Tests duplicit constants
	 */
	public function testConstants()
	{
		$broker = $this->getBroker();
		try {
			$broker->processFile($fileName = $this->getFilePath('constants'));

			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (Exception\RuntimeException $e) {
			// Expected
		}

		$this->assertTrue($broker->hasConstant('DUPLICITIES_CONSTANTS_1'));
		$this->assertTrue($broker->hasConstant('DUPLICITIES_CONSTANTS_2'));
		$this->assertTrue($broker->hasFunction('duplicitiesConstants'));
		$this->assertTrue($broker->hasClass('duplicitiesConstants'));

		$constant = $broker->getConstant('DUPLICITIES_CONSTANTS_1');
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionConstant', $constant);
		$this->assertSame($fileName, $constant->getFileName());
	}

	/**
	 * Tests duplicit functions.
	 */
	public function testFunctions()
	{
		$broker = $this->getBroker();
		try {
			$broker->processFile($fileName = $this->getFilePath('functions'));

			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (Exception\RuntimeException $e) {
			// Expected
		}

		$this->assertTrue($broker->hasFunction('duplicitiesFunctions1'));
		$this->assertTrue($broker->hasFunction('duplicitiesFunctions2'));
		$this->assertTrue($broker->hasClass('duplicitiesFunctions'));
		$this->assertTrue($broker->hasConstant('DUPLICITIES_FUNCTIONS'));

		$function = $broker->getFunction('duplicitiesFunctions1');
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionFunction', $function);
		$this->assertSame($fileName, $function->getFileName());
	}

	/**
	 * Tests duplicit classes.
	 */
	public function testClasses()
	{
		$broker = $this->getBroker();
		try {
			$broker->processFile($fileName = $this->getFilePath('classes'));

			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (Exception\RuntimeException $e) {
			// Expected
		}

		$this->assertTrue($broker->hasClass('duplicitiesClasses1'));
		$this->assertTrue($broker->hasClass('duplicitiesClasses2'));
		$this->assertTrue($broker->hasFunction('duplicitiesClasses'));
		$this->assertTrue($broker->hasConstant('DUPLICITIES_CLASSES'));

		$class = $broker->getClass('duplicitiesClasses1');
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionClass', $class);
		$this->assertSame($fileName, $class->getFileName());
	}

	/**
	 * Tests duplicities from an another file.
	 */
	public function testOtherFile()
	{
		$broker = $this->getBroker();
		try {
			$broker->processFile($fileName = $this->getFilePath('otherfile'));

			$this->fail('Expected exception \TokenReflection\Exception\RuntimeException.');
		} catch (Exception\RuntimeException $e) {
			// Expected
		}

		$this->assertTrue($broker->hasConstant('DUPLICITIES_CONSTANTS_1'));
		$this->assertTrue($broker->hasConstant('DUPLICITIES_CONSTANTS_2'));
		$this->assertTrue($broker->hasConstant('DUPLICITIES_FUNCTIONS'));
		$this->assertTrue($broker->hasConstant('DUPLICITIES_CLASSES'));
		$this->assertTrue($broker->hasFunction('duplicitiesConstants'));
		$this->assertTrue($broker->hasFunction('duplicitiesFunctions1'));
		$this->assertTrue($broker->hasFunction('duplicitiesFunctions2'));
		$this->assertTrue($broker->hasFunction('duplicitiesClasses'));
		$this->assertTrue($broker->hasClass('duplicitiesConstants'));
		$this->assertTrue($broker->hasClass('duplicitiesFunctions'));
		$this->assertTrue($broker->hasClass('duplicitiesClasses1'));
		$this->assertTrue($broker->hasClass('duplicitiesClasses2'));

		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionConstant', $broker->getConstant('DUPLICITIES_CONSTANTS_1'));
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionConstant', $broker->getConstant('DUPLICITIES_CONSTANTS_2'));
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionConstant', $broker->getConstant('DUPLICITIES_FUNCTIONS'));
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionConstant', $broker->getConstant('DUPLICITIES_CLASSES'));
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionFunction', $broker->getFunction('duplicitiesConstants'));
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionFunction', $broker->getFunction('duplicitiesFunctions1'));
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionFunction', $broker->getFunction('duplicitiesFunctions2'));
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionFunction', $broker->getFunction('duplicitiesClasses'));
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionClass', $broker->getClass('duplicitiesConstants'));
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionClass', $broker->getClass('duplicitiesFunctions'));
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionClass', $broker->getClass('duplicitiesClasses1'));
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionClass', $broker->getClass('duplicitiesClasses2'));

		$this->assertNotSame($fileName, $broker->getConstant('DUPLICITIES_CONSTANTS_1')->getFileName());
		$this->assertNotSame($fileName, $broker->getConstant('DUPLICITIES_CONSTANTS_2')->getFileName());
		$this->assertNotSame($fileName, $broker->getConstant('DUPLICITIES_FUNCTIONS')->getFileName());
		$this->assertNotSame($fileName, $broker->getConstant('DUPLICITIES_CLASSES')->getFileName());
		$this->assertNotSame($fileName, $broker->getFunction('duplicitiesConstants')->getFileName());
		$this->assertNotSame($fileName, $broker->getFunction('duplicitiesFunctions1')->getFileName());
		$this->assertNotSame($fileName, $broker->getFunction('duplicitiesFunctions2')->getFileName());
		$this->assertNotSame($fileName, $broker->getFunction('duplicitiesClasses')->getFileName());
		$this->assertNotSame($fileName, $broker->getClass('duplicitiesConstants')->getFileName());
		$this->assertNotSame($fileName, $broker->getClass('duplicitiesFunctions')->getFileName());
		$this->assertNotSame($fileName, $broker->getClass('duplicitiesClasses1')->getFileName());
		$this->assertNotSame($fileName, $broker->getClass('duplicitiesClasses2')->getFileName());
	}
}
