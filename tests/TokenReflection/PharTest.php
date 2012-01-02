<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.1
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

use Phar;

require_once __DIR__ . '/../bootstrap.php';

/**
 * PHAR support test.
 *
 * It basically takes all tests of a test type (class, ...), packs into all PHAR
 * archive types, lets the broker parse every archive and compares if the same
 * classes, constants and functions were parsed like when parsing a directory
 * of files.
 *
 * @author Jaroslav Hanslík
 * @author Ondřej Nešpor
 */
class PharTest extends Test
{
	/**
	 * Perform the pre-test check.
	 */
	protected function assertPreConditions()
	{
		if (!extension_loaded('phar')) {
			$this->markTestSkipped('The phar extension is required');
		}
	}

	/**
	 * Compares items parsed from filesystem and from a PHAR archive.
	 *
	 * @param \TokenReflection\Broker $filesystem Filesystem TR broker
	 * @param \TokenReflection\Broker $phar PHAR TR broker
	 * @param integer $format PHAR archive format
	 * @param integer $compression PHAR archive compression
	 * @param integer $wholeArchive Whole archive compressed
	 */
	private function archiveTest(Broker $filesystem, Broker $phar, $format, $compression, $wholeArchive)
	{
		$fsConstants = $filesystem->getConstants();
		$pharConstants = $phar->getConstants();
		$this->assertSame(count($fsConstants), count($pharConstants));
		foreach (array_keys($fsConstants) as $name) {
			$this->assertArrayHasKey($name, $pharConstants);
		}

		$fsClasses = $filesystem->getClasses();
		$pharClasses = $phar->getClasses();
		$this->assertSame(count($fsClasses), count($pharClasses));
		foreach (array_keys($fsClasses) as $name) {
			$this->assertArrayHasKey($name, $pharClasses);
		}

		$fsFunctions = $filesystem->getFunctions();
		$pharFunctions = $phar->getFunctions();
		$this->assertSame(count($fsFunctions), count($pharFunctions));
		foreach (array_keys($fsFunctions) as $name) {
			$this->assertArrayHasKey($name, $pharFunctions);
		}
	}

	/**
	 * Tests the PHAR file format.
	 *
	 * @dataProvider pharProvider
	 * @param array $metadata Comparison metadata
	 * @param \TokenReflection\Broker $filesystem Filesystem TR broker
	 * @param \TokenReflection\Broker $phar PHAR TR broker
	 */
	public function testPharArchive(array $metadata, Broker $filesystem, Broker $phar)
	{
		$this->archiveTest($filesystem, $phar, $metadata['format'], $metadata['compression'], $metadata['wholeArchive']);
	}

	/**
	 * Tests the zipped PHAR file format.
	 *
	 * @dataProvider zippedPharProvider
	 * @param array $metadata Comparison metadata
	 * @param \TokenReflection\Broker $filesystem Filesystem TR broker
	 * @param \TokenReflection\Broker $phar PHAR TR broker
	 */
	public function testZippedPharArchive(array $metadata, Broker $filesystem, Broker $phar)
	{
		$this->archiveTest($filesystem, $phar, $metadata['format'], $metadata['compression'], $metadata['wholeArchive']);
	}

	/**
	 * Tests the gzipped PHAR file format.
	 *
	 * @dataProvider gzippedPharProvider
	 * @param array $metadata Comparison metadata
	 * @param \TokenReflection\Broker $filesystem Filesystem TR broker
	 * @param \TokenReflection\Broker $phar PHAR TR broker
	 */
	public function testGZippedPharArchive(array $metadata, Broker $filesystem, Broker $phar)
	{
		$this->archiveTest($filesystem, $phar, $metadata['format'], $metadata['compression'], $metadata['wholeArchive']);
	}

	/**
	 * Tests the bzipped PHAR file format.
	 *
	 * @dataProvider bzippedPharProvider
	 * @param array $metadata Comparison metadata
	 * @param \TokenReflection\Broker $filesystem Filesystem TR broker
	 * @param \TokenReflection\Broker $phar PHAR TR broker
	 */
	public function testBZippedPharArchive(array $metadata, Broker $filesystem, Broker $phar)
	{
		$this->archiveTest($filesystem, $phar, $metadata['format'], $metadata['compression'], $metadata['wholeArchive']);
	}

	/**
	 * Prepares the temporary storage and returns its path.
	 *
	 * @return string
	 */
	private function prepareTemporaryStorage()
	{
		$dirName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('tr_phar_test');
		if (!mkdir($dirName)) {
			$this->fail('Could not create the temporary storage.');
		}

		return $dirName;
	}

	/**
	 * Cleans up the temporary storage.
	 *
	 * @param string $path Storage path
	 */
	private function cleanUpTemporaryStorage($path)
	{
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
		foreach ($iterator as $item) {
			if ($item->isFile()) {
				unlink($item->getPathName());
			} elseif ($item->isDir() && !$item->isDot()) {
				rmdir($item->getPathName());
			}
		}

		rmdir($path);
	}

	/**
	 * Data preparer.
	 *
	 * Returns pairs of TokenReflection\Broker where one parses a directory of given type
	 * and the second one parses a PHAR archive that was created from the same directory.
	 *
	 * @param integer $format Archive format
	 * @param integer $compression Archive compression
	 * @param boolean $wholeArchive Use compression for the whole archive
	 * @return array
	 */
	private function prepareData($format = Phar::PHAR, $compression = Phar::NONE, $wholeArchive = true)
	{
		$dirName = $this->prepareTemporaryStorage();

		$directory = realpath(__DIR__ . '/../data/');
		$iterator = new \DirectoryIterator($directory);

		$data = array();
		foreach ($iterator as $item) {
			if ($item->isDir() && !$item->isDot()) {
				$ext = '.phar';
				$fileName = $dirName . DIRECTORY_SEPARATOR . uniqid($format . $compression);

				$phar = new Phar($fileName . $ext);
				$phar->buildFromDirectory($item->getPathName());

				if ($format !== Phar::PHAR) {
					if ($format === Phar::TAR) {
						$ext .= '.tar';
					} elseif ($format === Phar::ZIP) {
						$ext .= '.zip';
					}

					$phar->convertToExecutable($format, $wholeArchive ? $compression : Phar::NONE, $ext);
				}
				if ($compression !== Phar::NONE && !$wholeArchive) {
					$phar->compressFiles($compression);
				}

				unset($phar);

				$dataItem = array(
					'metadata' => array(
						'format' => $format,
						'compression' => $compression,
						'wholeArchive' => $wholeArchive,
					),
				);

				$broker = new Broker(new Broker\Backend\Memory(), 0);
				$broker->processDirectory($item->getPathName());
				$dataItem['fsBroker'] = $broker;

				$broker2 = new Broker(new Broker\Backend\Memory(), 0);
				$broker2->process($fileName . $ext);
				$dataItem['pharBroker'] = $broker2;

				$data[] = $dataItem;
			}
		}

		$this->cleanUpTemporaryStorage($dirName);

		return $data;
	}

	/**
	 * Plain PHAR data provider.
	 *
	 * @return array
	 */
	public function pharProvider()
	{
		return $this->prepareData();
	}

	/**
	 * Zipped PHAR data provider.
	 *
	 * @return array
	 */
	public function zippedPharProvider()
	{
		if (!extension_loaded('zip')) {
			$this->markTestSkipped('The zip extension is required to run this test.');
		}

		return $this->prepareData(Phar::ZIP);
	}

	/**
	 * Gzipped PHAR data provider.
	 *
	 * @return array
	 */
	public function gzippedPharProvider()
	{
		if (!extension_loaded('zlib')) {
			$this->markTestSkipped('The zlib extension is required to run this test.');
		}

		return array_merge(
			$this->prepareData(Phar::PHAR, Phar::GZ, false),
			$this->prepareData(Phar::TAR, Phar::GZ, false),
			$this->prepareData(Phar::PHAR, Phar::GZ, true),
			$this->prepareData(Phar::TAR, Phar::GZ, true)
		);
	}

	/**
	 * Bzipped PHAR data provider.
	 *
	 * @return array
	 */
	public function bzippedPharProvider()
	{
		if (!extension_loaded('bz2')) {
			$this->markTestSkipped('The zlib extension is required to run this test.');
		}

		return array_merge(
			$this->prepareData(Phar::PHAR, Phar::BZ2, false),
			$this->prepareData(Phar::TAR, Phar::BZ2, false),
			$this->prepareData(Phar::PHAR, Phar::BZ2, true),
			$this->prepareData(Phar::TAR, Phar::BZ2, true)
		);
	}
}
