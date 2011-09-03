<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.0 RC 1
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

use TokenReflection\Exception;

/**
 * Basic abstract TokenReflection class.
 *
 * Defines a variety of common methods. All reflection are descendants of this class.
 */
abstract class ReflectionBase implements IReflection
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
	 * Object name (FQN).
	 *
	 * @var string
	 */
	protected $name;

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
	 * Docblock definition.
	 *
	 * @var \TokenReflection\ReflectionAnnotation|boolean
	 */
	protected $docComment;

	/**
	 * Parsed docblock definition.
	 *
	 * @var array
	 */
	private $parsedDocComment;

	/**
	 * Reflection broker.
	 *
	 * @var \TokenReflection\Broker
	 */
	private $broker;

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
	 * Constructor.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\Broker $broker Reflection broker
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @throws \TokenReflection\Exception\Parse If the token stream is empty
	 * @throws \TokenReflection\Exception\Parse If the token stream could not be parsed
	 */
	public final function __construct(Stream $tokenStream, Broker $broker, IReflection $parent)
	{
		if (0 === $tokenStream->count()) {
			throw new Exception\Runtime('Reflection token stream must not be empty.', Exception\Runtime::INVALID_ARGUMENT);
		}

		$this->broker = $broker;
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
	 * Returns the appropriate docblock definition.
	 *
	 * @return string|boolean
	 */
	public function getDocComment()
	{
		return $this->docComment->getDocComment();
	}

	/**
	 * Checks if there is a particular annotation.
	 *
	 * @param string $name Annotation name
	 * @return boolean
	 */
	final public function hasAnnotation($name)
	{
		return $this->docComment->hasAnnotation($name);
	}

	/**
	 * Returns a particular annotation value.
	 *
	 * @param string $name Annotation name
	 * @return string|array|null
	 */
	final public function getAnnotation($name)
	{
		return $this->docComment->getAnnotation($name);
	}

	/**
	 * Returns all annotations.
	 *
	 * @return array
	 */
	final public function getAnnotations()
	{
		return $this->docComment->getAnnotations();
	}

	/**
	 * Returns if the reflection object is internal.
	 *
	 * Always returns false - everything is user defined.
	 *
	 * @return boolean
	 */
	public function isInternal()
	{
		return false;
	}

	/**
	 * Returns if the reflection object is user defined.
	 *
	 * Always returns true - everything is user defined.
	 *
	 * @return boolean
	 */
	public function isUserDefined()
	{
		return true;
	}

	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return boolean
	 */
	public function isTokenized()
	{
		return true;
	}

	/**
	 * Returns if the reflection subject is deprecated.
	 *
	 * @return boolean
	 */
	public function isDeprecated()
	{
		return $this->hasAnnotation('deprecated');
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
	 * Returns the reflection broker used by this reflection object.
	 *
	 * @return \TokenReflection\Broker
	 */
	public function getBroker()
	{
		return $this->broker;
	}

	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	abstract public function getNamespaceAliases();

	/**
	 * Magic __get method.
	 *
	 * @param string $key Variable name
	 * @return mixed
	 */
	final public function __get($key)
	{
		return self::get($this, $key);
	}

	/**
	 * Magic __isset method.
	 *
	 * @param string $key Variable name
	 * @return boolean
	 */
	final public function __isset($key)
	{
		return self::exists($this, $key);
	}

	/**
	 * Magic __get method helper.
	 *
	 * @param \TokenReflection\IReflection $object Reflection object
	 * @param string $key Variable name
	 * @return mixed
	 * @throws \TokenReflection\Exception\Runtime If the requested parameter does not exist
	 */
	final public static function get(IReflection $object, $key)
	{
		if (!empty($key)) {
			$className = get_class($object);
			if (!isset(self::$methodCache[$className])) {
				self::$methodCache[$className] = array_flip(get_class_methods($className));
			}

			$methods = self::$methodCache[$className];
			$key2 = ucfirst($key);
			if (isset($methods['get' . $key2])) {
				return $object->{'get' . $key2}();
			} elseif (isset($methods['is' . $key2])) {
				return $object->{'is' . $key2}();
			}
		}

		throw new Exception\Runtime(sprintf('Cannot read %s "%s" property "%s".', get_class($object), $object->getName(), $key), Exception\Runtime::DOES_NOT_EXIST);
	}

	/**
	 * Magic __isset method helper.
	 *
	 * @param \TokenReflection\IReflection $object Reflection object
	 * @param string $key Variable name
	 * @return boolean
	 */
	final public static function exists(IReflection $object, $key)
	{
		try {
			self::get($object, $key);
			return true;
		} catch (RuntimeException $e) {
			return false;
		}
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
	 * @param \TokenReflection\Reflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 */
	protected function processParent(IReflection $parent)
	{
		// To be defined in child classes
		return $this;
	}

	/**
	 * Find the appropriate docblock.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection
	 * @return \TokenReflection\ReflectionBase
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

		if (null === $this->docComment) {
			$this->docComment = new ReflectionAnnotation($this);
		}

		if ($parent instanceof ReflectionBase) {
			$this->docComment->setTemplates($parent->getDocblockTemplates());
		}

		return $this;
	}

	/**
	 * Saves the start line number.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token susbtream
	 * @return \TokenReflection\ReflectionBase
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
	 * @param \TokenReflection\Stream $tokenStream Token susbtream
	 * @return \TokenReflection\ReflectionBase
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
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 */
	abstract protected function parse(Stream $tokenStream, IReflection $parent);

	/**
	 * Parses the reflection object name.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionBase
	 */
	abstract protected function parseName(Stream $tokenStream);

	/**
	 * Parses child reflection objects from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\Reflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 */
	protected function parseChildren(Stream $tokenStream, IReflection $parent)
	{
		// To be defined in child classes
		return $this;
	}
}
