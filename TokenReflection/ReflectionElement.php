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

use TokenReflection\Exception, TokenReflection\Stream\StreamBase as Stream;

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
	private static $methodCache = array();

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
	private $startPosition;

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
	protected $docblockTemplates = array();

	/**
	 * Parses the token substream.
	 *
	 * @param Stream\StreamBase $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @throws \TokenReflection\Exception\Runtime If the token stream is empty
	 * @throws \TokenReflection\Exception\Parse If the token stream could not be parsed
	 */
	final protected function parseStream(Stream $tokenStream, IReflection $parent = null)
	{
		$this->fileName = $tokenStream->getFileName();

		try {
			$this
				->processParent($parent)
				->parseStartLine($tokenStream)
				->parseDocComment($tokenStream, $parent)
				->parse($tokenStream, $parent);
		} catch (Exception $e) {
			$message = 'Could not parse %s.';
			if (null !== $this->name) {
				$message = sprintf($message, get_class($this) . ' ' . $this->getName());
			} else {
				$message = sprintf($message, get_class($this));
			}

			throw new Exception\Parse($message, Exception\Parse::PARSE_ELEMENT_ERROR, $e);
		}

		try {
			$this->parseChildren($tokenStream, $parent);
		} catch (Exception $e) {
			throw new Exception\Parse(sprintf('Could not parse %s %s child elements.', get_class($this), $this->getName()), Exception\Parse::PARSE_CHILDREN_ERROR, $e);
		}

		$this->parseEndLine($tokenStream);
	}

	/**
	 * Returns the name (FQN).
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
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
	 * @return \TokenReflection\ReflectionFile
	 * @throws \TokenReflection\Exception\Runtime If the file is not stored inside the broker
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
		return null;
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
		return false;
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
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	abstract public function getNamespaceAliases();

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
	 * @param \TokenReflection\Reflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionElement
	 */
	protected function processParent(IReflection $parent)
	{
		// To be defined in child classes
		return $this;
	}

	/**
	 * Find the appropriate docblock.
	 *
	 * @param \TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection
	 * @return \TokenReflection\ReflectionElement
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
		} elseif ($tokenStream->is(T_COMMENT, $position - 2) && substr_count($tokenStream->getTokenValue($position - 1), "\n") < 2 && preg_match('~^' . preg_quote(self::DOCBLOCK_TEMPLATE_START, '~') . '~', $tokenStream->getTokenValue($position - 2))) {
			$this->docComment = new ReflectionAnnotation($this, $tokenStream->getTokenValue($position - 2));
			$this->startPosition -= 2;
		}

		if (null === $this->docComment) {
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
	 * @param \TokenReflection\Stream\StreamBase $tokenStream Token susbtream
	 * @return \TokenReflection\ReflectionElement
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
	 * @param \TokenReflection\Stream\StreamBase $tokenStream Token susbtream
	 * @return \TokenReflection\ReflectionElement
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
	 * @param \TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionElement
	 */
	abstract protected function parse(Stream $tokenStream, IReflection $parent);

	/**
	 * Parses the reflection object name.
	 *
	 * @param \TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @return \TokenReflection\ReflectionElement
	 */
	abstract protected function parseName(Stream $tokenStream);

	/**
	 * Parses child reflection objects from the token stream.
	 *
	 * @param \TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param \TokenReflection\Reflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionElement
	 */
	protected function parseChildren(Stream $tokenStream, IReflection $parent)
	{
		// To be defined in child classes
		return $this;
	}
}
