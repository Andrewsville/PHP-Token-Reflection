<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection;

use ApiGen\TokenReflection\Storage\StorageInterface;
use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\Parser\ElementParser;
use ApiGen\TokenReflection\Stream\StreamBase;


abstract class ReflectionElement extends ReflectionBase
{

	/**
	 * Filename with reflection subject definition.
	 *
	 * @var string
	 */
	protected $fileName;

	/**
	 * @var ElementParser
	 */
	protected $elementParser;

	/**
	 * Start line in the file.
	 *
	 * @var int
	 */
	protected $startLine;

	/**
	 * End line in the file.
	 *
	 * @var int
	 */
	protected $endLine;

	/**
	 * Start position in the file token stream.
	 *
	 * @var int
	 */
	protected $startPosition;

	/**
	 * End position in the file token stream.
	 *
	 * @var int
	 */
	protected $endPosition;


	public function __construct(StreamBase $tokenStream, StorageInterface $storage, ReflectionInterface $parent = NULL)
	{
		if ($tokenStream->count() === 0) {
			throw new ParseException('Reflection token stream must not be empty.');
		}

		$this->elementParser = new ElementParser($tokenStream, $this, $parent);
		$this->storage = $storage;
		if (method_exists($this, 'parseStream')) {
			$this->parseStream($tokenStream, $parent);
		}
	}


	protected function parseStream(StreamBase $tokenStream, ReflectionInterface $parent = NULL)
	{
		$this->fileName = $tokenStream->getFileName();

		if (method_exists($this, 'processParent')) {
			$this->processParent($parent, $tokenStream);
		}

		$this->parseStartLine($tokenStream);
		$this->parseDocComment($tokenStream, $parent);

		if (method_exists($this, 'parse')) {
			$this->parse($tokenStream, $parent);
		}

		if (method_exists($this, 'parseChildren')) {
			$this->parseChildren($tokenStream, $parent);
		}

		$this->parseEndLine($tokenStream);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFileName()
	{
		return $this->fileName;
	}


	/**
	 * Returns a file reflection.
	 *
	 * @return ReflectionFile
	 * @throws RuntimeException If the file is not stored inside the broker
	 */
	public function getFileReflection()
	{
		return $this->storage->getFile($this->fileName);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getStartLine()
	{
		return $this->startLine;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getEndLine()
	{
		return $this->endLine;
	}


	/**
	 * Returns the start position in the file token stream.
	 *
	 * @return int
	 */
	public function getStartPosition()
	{
		return $this->startPosition;
	}


	/**
	 * Returns the end position in the file token stream.
	 *
	 * @return int
	 */
	public function getEndPosition()
	{
		return $this->endPosition;
	}


	protected function parseDocComment(StreamBase $tokenStream, ReflectionInterface $parent)
	{
		list($this->docComment, $this->startPosition) = $this->elementParser->parseDocComment($this->startPosition);
	}


	protected function parseStartLine(StreamBase $tokenStream)
	{
		$this->startLine = $tokenStream->current()[2];
		$this->startPosition = $tokenStream->key();
	}


	protected function parseEndLine(StreamBase $tokenStream)
	{
		$this->endLine = $tokenStream->current()[2];
		$this->endPosition = $tokenStream->key();
	}

}
