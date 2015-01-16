<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Parser\ElementParser;
use ApiGen\TokenReflection\Stream\StreamBase;


abstract class ReflectionElement extends ReflectionBase
{

	/**
	 * Class method cache.
	 *
	 * @var array
	 */
	private static $methodCache = [];

	/**
	 * Filename with reflection subject definition.
	 *
	 * @var string
	 */
	private $fileName;

	/**
	 * Start line in the file.
	 *
	 * @var int
	 */
	private $startLine;

	/**
	 * End line in the file.
	 *
	 * @var int
	 */
	private $endLine;

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
	private $endPosition;

	/**
	 * @var ElementParser
	 */
	private $elementParser;


	/**
	 * @throws ParseException If an empty token stream was provided
	 */
	public function __construct(StreamBase $tokenStream, Broker $broker, IReflection $parent = NULL)
	{
		if ($tokenStream->count() === 0) {
			throw new ParseException($this, $tokenStream, 'Reflection token stream must not be empty.', ParseException::INVALID_ARGUMENT);
		}
		$this->elementParser = new ElementParser($tokenStream, $this, $parent);

		$this->broker = $broker;
		$this->parseStream($tokenStream, $parent);
	}


	protected function parseStream(StreamBase $tokenStream, IReflection $parent = NULL)
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
		return $this->getBroker()->getFile($this->fileName);
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
	 * Returns the PHP extension reflection.
	 *
	 * Alwyas returns null - everything is user defined.
	 *
	 * @return null
	 */
	public function getExtension()
	{
		return NULL;
	}


	/**
	 * Returns the PHP extension name.
	 *
	 * Alwyas returns false - everything is user defined.
	 *
	 * @return bool
	 */
	public function getExtensionName()
	{
		return FALSE;
	}


	/**
	 * Returns the appropriate source code part.
	 *
	 * @return string
	 */
	public function getSource()
	{
		return $this->getBroker()->getFileTokens($this->getFileName())->getSourcePart($this->startPosition, $this->endPosition);
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


	/**
	 * Find the appropriate docblock.
	 *
	 * @return ReflectionElement
	 */
	protected function parseDocComment(StreamBase $tokenStream, IReflection $parent)
	{
		list($this->docComment, $this->startPosition) = $this->elementParser->parseDocComment($this->startPosition);
	}


	/**
	 * Saves the start line number.
	 *
	 * @return ReflectionElement
	 */
	private function parseStartLine(StreamBase $tokenStream)
	{
		$token = $tokenStream->current();
		$this->startLine = $token[2];
		$this->startPosition = $tokenStream->key();
		return $this;
	}


	/**
	 * Saves the end line number.
	 *
	 * @return ReflectionElement
	 */
	private function parseEndLine(StreamBase $tokenStream)
	{
		$token = $tokenStream->current();
		$this->endLine = $token[2];
		$this->endPosition = $tokenStream->key();
		return $this;
	}

}
