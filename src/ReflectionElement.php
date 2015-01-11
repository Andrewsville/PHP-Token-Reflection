<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */
namespace ApiGen\TokenReflection;

use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Stream\StreamBase as Stream;


/**
 * Basic class for reflection elements.
 *
 * Defines a variety of common methods. All reflections are descendants of this class.
 */
abstract class ReflectionElement extends ReflectionBase
{

	/**
	 * Docblock template start.
	 *
	 * @var string
	 */
	const DOCBLOCK_TEMPLATE_START = '/**#@+';

	/**
	 * Docblock template end.
	 *
	 * @var string
	 */
	const DOCBLOCK_TEMPLATE_END = '/**#@-*/';

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
	 * @var integer
	 */
	private $startLine;

	/**
	 * End line in the file.
	 *
	 * @var integer
	 */
	private $endLine;

	/**
	 * Start position in the file token stream.
	 *
	 * @var integer
	 */
	protected $startPosition;

	/**
	 * End position in the file token stream.
	 *
	 * @var integer
	 */
	private $endPosition;

	/**
	 * Stack of actual docblock templates.
	 *
	 * @var array
	 */
	protected $docblockTemplates = [];


	/**
	 * Constructor.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker
	 * @param ApiGen\TokenReflection\IReflection $parent Parent reflection object
	 * @throws ApiGen\TokenReflection\Exception\ParseException If an empty token stream was provided
	 */
	final public function __construct(Stream $tokenStream, Broker $broker, IReflection $parent = NULL)
	{
		if (0 === $tokenStream->count()) {
			throw new Exception\ParseException($this, $tokenStream, 'Reflection token stream must not be empty.', Exception\ParseException::INVALID_ARGUMENT);
		}
		parent::__construct($tokenStream, $broker, $parent);
	}


	/**
	 * Parses the token substream.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param ApiGen\TokenReflection\IReflection $parent Parent reflection object
	 */
	final protected function parseStream(Stream $tokenStream, IReflection $parent = NULL)
	{
		$this->fileName = $tokenStream->getFileName();
		$this
			->processParent($parent, $tokenStream)
			->parseStartLine($tokenStream)
			->parseDocComment($tokenStream, $parent)
			->parse($tokenStream, $parent)
			->parseChildren($tokenStream, $parent)
			->parseEndLine($tokenStream);
	}


	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return string
	 */
	public function getFileName()
	{
		return $this->fileName;
	}


	/**
	 * Returns a file reflection.
	 *
	 * @return ApiGen\TokenReflection\ReflectionFile
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the file is not stored inside the broker
	 */
	public function getFileReflection()
	{
		return $this->getBroker()->getFile($this->fileName);
	}


	/**
	 * Returns the definition start line number in the file.
	 *
	 * @return integer
	 */
	public function getStartLine()
	{
		return $this->startLine;
	}


	/**
	 * Returns the definition end line number in the file.
	 *
	 * @return integer
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
	 * @return boolean
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
		return $this->broker->getFileTokens($this->getFileName())->getSourcePart($this->startPosition, $this->endPosition);
	}


	/**
	 * Returns the start position in the file token stream.
	 *
	 * @return integer
	 */
	public function getStartPosition()
	{
		return $this->startPosition;
	}


	/**
	 * Returns the end position in the file token stream.
	 *
	 * @return integer
	 */
	public function getEndPosition()
	{
		return $this->endPosition;
	}


	/**
	 * Returns the stack of docblock templates.
	 *
	 * @return array
	 */
	protected function getDocblockTemplates()
	{
		return $this->docblockTemplates;
	}


	/**
	 * Processes the parent reflection object.
	 *
	 * @param ApiGen\TokenReflection\Reflection $parent Parent reflection object
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @return ApiGen\TokenReflection\ReflectionElement
	 */
	protected function processParent(IReflection $parent, Stream $tokenStream)
	{
		// To be defined in child classes
		return $this;
	}


	/**
	 * Find the appropriate docblock.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param ApiGen\TokenReflection\IReflection $parent Parent reflection
	 * @return ApiGen\TokenReflection\ReflectionElement
	 */
	protected function parseDocComment(Stream $tokenStream, IReflection $parent)
	{
		if ($this instanceof ReflectionParameter) {
			$this->docComment = new ReflectionAnnotation($this);
			return $this;
		}
		$position = $tokenStream->key();
		if ($tokenStream->is(T_DOC_COMMENT, $position - 1)) {
			$value = $tokenStream->getTokenValue($position - 1);
			if (self::DOCBLOCK_TEMPLATE_END !== $value) {
				$this->docComment = new ReflectionAnnotation($this, $value);
				$this->startPosition--;
			}
		} elseif ($tokenStream->is(T_DOC_COMMENT, $position - 2)) {
			$value = $tokenStream->getTokenValue($position - 2);
			if (self::DOCBLOCK_TEMPLATE_END !== $value) {
				$this->docComment = new ReflectionAnnotation($this, $value);
				$this->startPosition -= 2;
			}
		} elseif ($tokenStream->is(T_COMMENT, $position - 1) && preg_match('~^' . preg_quote(self::DOCBLOCK_TEMPLATE_START, '~') . '~', $tokenStream->getTokenValue($position - 1))) {
			$this->docComment = new ReflectionAnnotation($this, $tokenStream->getTokenValue($position - 1));
			$this->startPosition--;
		} elseif ($tokenStream->is(T_COMMENT, $position - 2) && preg_match('~^' . preg_quote(self::DOCBLOCK_TEMPLATE_START, '~') . '~', $tokenStream->getTokenValue($position - 2))) {
			$this->docComment = new ReflectionAnnotation($this, $tokenStream->getTokenValue($position - 2));
			$this->startPosition -= 2;
		}
		if (NULL === $this->docComment) {
			$this->docComment = new ReflectionAnnotation($this);
		}
		if ($parent instanceof ReflectionElement) {
			$this->docComment->setTemplates($parent->getDocblockTemplates());
		}
		return $this;
	}


	/**
	 * Saves the start line number.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token susbtream
	 * @return ApiGen\TokenReflection\ReflectionElement
	 */
	private final function parseStartLine(Stream $tokenStream)
	{
		$token = $tokenStream->current();
		$this->startLine = $token[2];
		$this->startPosition = $tokenStream->key();
		return $this;
	}


	/**
	 * Saves the end line number.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token susbtream
	 * @return ApiGen\TokenReflection\ReflectionElement
	 */
	private final function parseEndLine(Stream $tokenStream)
	{
		$token = $tokenStream->current();
		$this->endLine = $token[2];
		$this->endPosition = $tokenStream->key();
		return $this;
	}


	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param ApiGen\TokenReflection\IReflection $parent Parent reflection object
	 * @return ApiGen\TokenReflection\ReflectionElement
	 */
	abstract protected function parse(Stream $tokenStream, IReflection $parent);


	/**
	 * Parses the reflection object name.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @return ApiGen\TokenReflection\ReflectionElement
	 */
	abstract protected function parseName(Stream $tokenStream);


	/**
	 * Parses child reflection objects from the token stream.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param ApiGen\TokenReflection\Reflection $parent Parent reflection object
	 * @return ApiGen\TokenReflection\ReflectionElement
	 */
	protected function parseChildren(Stream $tokenStream, IReflection $parent)
	{
		// To be defined in child classes
		return $this;
	}
}
