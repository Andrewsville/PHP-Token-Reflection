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
		} catch (Exception\FileProcessingException $e) {
			// Expected
		}

		$this->assertTrue($broker->hasConstant('DUPLICITIES_CONSTANTS_1'));
		$this->assertTrue($broker->hasConstant('DUPLICITIES_CONSTANTS_2'));
		$this->assertTrue($broker->hasFunction('duplicitiesConstants'));
		$this->assertTrue($broker->hasClass('duplicitiesConstants'));

		$constant = $broker->getConstant('DUPLICITIES_CONSTANTS_1');
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionConstant', $constant);
		$this->assertSame($fileName, $constant->getFileName());
		$this->assertTrue($constant->hasReasons());
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
		} catch (Exception\FileProcessingException $e) {
			// Expected
		}

		$this->assertTrue($broker->hasFunction('duplicitiesFunctions1'));
		$this->assertTrue($broker->hasFunction('duplicitiesFunctions2'));
		$this->assertTrue($broker->hasClass('duplicitiesFunctions'));
		$this->assertTrue($broker->hasConstant('DUPLICITIES_FUNCTIONS'));

		$function = $broker->getFunction('duplicitiesFunctions1');
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionFunction', $function);
		$this->assertSame($fileName, $function->getFileName());
		$this->assertTrue($function->hasReasons());
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
		} catch (Exception\FileProcessingException $e) {
			// Expected
		}

		$this->assertTrue($broker->hasClass('duplicitiesClasses1'));
		$this->assertTrue($broker->hasClass('duplicitiesClasses2'));
		$this->assertTrue($broker->hasFunction('duplicitiesClasses'));
		$this->assertTrue($broker->hasConstant('DUPLICITIES_CLASSES'));

		$class = $broker->getClass('duplicitiesClasses1');
		$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionClass', $class);
		$this->assertSame($fileName, $class->getFileName());
		$this->assertTrue($class->hasReasons());
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
		} catch (Exception\FileProcessingException $e) {
			// Expected
		}

		static $elements = array(
			'classes' => array(
				'duplicitiesConstants',
				'duplicitiesFunctions',
				'duplicitiesClasses1',
				'duplicitiesClasses2'
			),
			'functions' => array(
				'duplicitiesConstants',
				'duplicitiesFunctions1',
				'duplicitiesFunctions2',
				'duplicitiesClasses'
			),
			'constants' => array(
				'DUPLICITIES_CONSTANTS_1',
				'DUPLICITIES_CONSTANTS_2',
				'DUPLICITIES_FUNCTIONS',
				'DUPLICITIES_CLASSES'
			)
		);

		foreach ($elements as $type => $names) {
			foreach ($names as $name) {
				switch ($type) {
					case 'classes':
						$this->assertTrue($broker->hasClass($name));

						$reflection = $broker->getClass($name);
						$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionClass', $reflection);
						break;
					case 'functions':
						$this->assertTrue($broker->hasFunction($name));

						$reflection = $broker->getFunction($name);
						$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionFunction', $reflection);
						break;
					case 'constants':
						$this->assertTrue($broker->hasConstant($name));

						$reflection = $broker->getConstant($name);
						$this->assertInstanceOf('TokenReflection\\Invalid\\ReflectionConstant', $reflection);
						break;
				}

				$this->assertTrue($reflection->hasReasons());
				$this->assertNotSame($fileName, $reflection->getFileName());
			}
		}
	}
}
