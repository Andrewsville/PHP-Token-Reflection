<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.0
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
 *
 * @author Ondřej Nešpor
 */
class ReflectionBrokerTest extends Test
{
	/**
	 * Tests filenames filtering.
	 *
	 * @dataProvider filenameFilterProvider
	 * @param string|array $filters Filename filter(s)
	 * @param array $fileNames Filtered filenames
	 */
	public function testFilenameFiltering($filters, array $fileNames)
	{
		$broker = new Broker(new Broker\Backend\Memory());
		$files = $broker->processDirectory(realpath(__DIR__ . '/../data/class'), $filters, true);

		$brokerFileNames = array();
		foreach ($files as $file) {
			$brokerFileNames[] = basename($file->getName());
		}

		$this->compareFileLists($fileNames, $brokerFileNames);
	}

	/**
	 * Tests directory and filename filtering.
	 *
	 * @dataProvider directoryFilterProvider
	 * @param string|array $filters Filename filter(s)
	 * @param array $fileNames Filtered filenames
	 */
	public function testDirectoryFiltering($filters, array $fileNames)
	{
		$broker = new Broker(new Broker\Backend\Memory());
		$files = $broker->processDirectory(realpath(__DIR__ . '/../data'), $filters, true);

		$brokerFileNames = array();
		foreach ($files as $file) {
			$brokerFileNames[] = basename($file->getName());
		}

		$this->compareFileLists($fileNames, $brokerFileNames);
	}

	/**
	 * Compares a filename list to an expected one.
	 *
	 * PhpUnit does not seem to let one compare two arrays without having to
	 * have elements in the same order (which is not important at all here).
	 *
	 * @param array $expected Expected filenames list
	 * @param array $actual Actual filenames list
	 */
	private function compareFileLists(array $expected, array $actual)
	{
		$this->assertSame(count($expected), count($actual));
		foreach ($expected as $fileName) {
			$this->assertTrue(in_array($fileName, $actual));
		}
	}

	/**
	 * Filename filters provider.
	 *
	 * @return array
	 */
	public function filenameFilterProvider()
	{
		return array(
			array(
				'*.php',
				array(
					'abstract.php',
					'abstract-implicit.php',
					'constants.php',
					'doc-comment.php',
					'doc-comment-copydoc.php',
					'doc-comment-inheritance.php',
					'double-properties.php',
					'final.php',
					'in-namespace.php',
					'instances.php',
					'interface.php',
					'interfaces.php',
					'iterator.php',
					'lines.php',
					'methods.php',
					'modifiers.php',
					'new-instance-without-constructor.php',
					'no-abstract.php',
					'no-constants.php',
					'no-doc-comment.php',
					'no-final.php',
					'no-interface.php',
					'no-interfaces.php',
					'no-iterator.php',
					'no-methods.php',
					'no-namespace.php',
					'no-parent.php',
					'no-properties.php',
					'parent.php',
					'private-clone.php',
					'private-constructor.php',
					'properties.php',
					'public-clone.php',
					'public-constructor.php',
					'traits.php',
					'user-defined.php'
				)
			),
			array(
				'*no-*.php',
				array(
					'no-abstract.php',
					'no-constants.php',
					'no-doc-comment.php',
					'no-final.php',
					'no-interface.php',
					'no-interfaces.php',
					'no-iterator.php',
					'no-methods.php',
					'no-namespace.php',
					'no-parent.php',
					'no-properties.php'
				)
			),
			array(
				'*-constructor.php',
				array(
					'new-instance-without-constructor.php',
					'private-constructor.php',
					'public-constructor.php'
				)
			),
		);
	}

	/**
	 * Filename filters provider.
	 *
	 * @return array
	 */
	public function directoryFilterProvider()
	{
		return array(
			array(
				'*constant' . DIRECTORY_SEPARATOR . '*.php',
				array(
					'doc-comment.php',
					'doc-comment-copydoc.php',
					'in-namespace.php',
					'lines.php',
					'magic.php',
					'magic54.php',
					'no-comment.php',
					'no-namespace.php',
					'type-boolean.php',
					'type-constant.php',
					'type-float.php',
					'type-float-negative.php',
					'type-integer.php',
					'type-integer-negative.php',
					'type-null.php',
					'type-string.php'
				)
			),
			array(
				'*doc-comment.php',
				array(
					'doc-comment.php',
					'doc-comment.php',
					'doc-comment.php',
					'doc-comment.php',
					'doc-comment.php',
					'doc-comment.php',
					'doc-comment.php',
					'no-doc-comment.php'
				)
			),
			array(
				'foo.php',
				array()
			)
		);
	}
}
