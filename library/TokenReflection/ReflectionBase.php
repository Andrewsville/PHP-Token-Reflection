<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0beta1
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file license.txt.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
 */

namespace TokenReflection;

use RuntimeException;

/**
 * Basic abstract TokenReflection class.
 *
 * Defines a variety of common methods. All reflection are descendants of this class.
 */
abstract class ReflectionBase implements IReflection
{
	/**
	 * Class method cache.
	 *
	 * @var array
	 */
	private static $methodCache = array();

	/**
	 * Reflection broker.
	 *
	 * @var \TokenReflection\Broker
	 */
	private $broker;

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
	 * @var string|false
	 */
	protected $docComment;

	/**
	 * Parsed docblock definition.
	 *
	 * @var array
	 */
	private $parsedDocComment;

	/**
	 * Object name (UQN).
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Filename with reflection subject definition.
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * Constructor.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\Broker $broker Reflection broker
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 */
	public final function __construct(Stream $tokenStream, Broker $broker, IReflection $parent)
	{
		if (0 === $tokenStream->count()) {
			throw new Exception('Reflection token stream must not be empty');
		}

		$this->broker = $broker;
		$this->filename = $tokenStream->getFileName();

		if (!$this instanceof ReflectionParameter) {
			$this->parseDocComment($tokenStream);
		}

		return $this
			->processParent($parent)
			->parseStartLine($tokenStream)
			->parse($tokenStream, $parent)
			->parseChildren($tokenStream, $parent)
			->parseEndLine($tokenStream);
	}

	/**
	 * Processes the parent reflection object.
	 *
	 * @param \TokenReflection\Reflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 */
	protected function processParent(IReflection $parent)
	{
		// to be defined in child classes
		return $this;
	}

	/**
	 * Parses child reflection objects from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\Reflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 */
	protected function parseChildren(Stream $tokenStream, IReflection $parent)
	{
		// to be defined in child classes
		return $this;
	}

	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 */
	protected abstract function parse(Stream $tokenStream, IReflection $parent);

	/**
	 * Find the appropriate docblock.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionBase
	 */
	protected function parseDocComment(Stream $tokenStream)
	{
		$position = $tokenStream->key();
		if ($tokenStream->is(T_DOC_COMMENT, $position - 2)) {
			$this->docComment = $tokenStream->getTokenValue($position - 2);
		} elseif ($tokenStream->is(T_DOC_COMMENT, $position - 1)) {
			$this->docComment = $tokenStream->getTokenValue($position - 1);
		} else {
			$this->docComment = false;
		}

		return $this;
	}

	/**
	 * Saves the first line number.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token susbtream
	 * @return \TokenReflection\ReflectionBase
	 */
	private final function parseStartLine(Stream $tokenStream)
	{
		$token = $tokenStream->current();
		$this->startLine = $token[2];

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

		return $this;
	}

	/**
	 * Parses the reflection object name.
	 *
	 * @param \TokenReflection\Stream Token substream
	 * @return \TokenReflection\ReflectionBase
	 */
	abstract protected function parseName(Stream $tokenStream);

	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return string
	 */
	public function getFileName()
	{
		return $this->filename;
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
	 * Returns null - everything is user defined.
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
	 * Returns null - everything is user defined.
	 *
	 * @return null
	 */
	public function getExtensionName()
	{
		return null;
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
	 * Returns if the reflection subject is deprecated.
	 *
	 * @return boolean
	 */
	public function isDeprecated()
	{
		return $this->hasAnnotation('deprecated');
	}

	/**
	 * Returns the reflection object name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Outputs the reflection subject source code.
	 *
	 * @return string
	 */
	public function getSource()
	{
		$tokens = $this->broker->getFileTokens($this->filename);
		if (null !== $tokens && $tokens instanceof Stream) {
			$tokens = iterator_to_array($tokens);
		} else {
			return '';
		}

		return array_reduce($tokens, function($output, $token) {
			return $output . $token[1];
		}, '');
	}

	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return string|false
	 */
	public function getDocComment()
	{
		return $this->docComment;
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
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return boolean
	 */
	public function isTokenized()
	{
		return true;
	}

	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString()
	{
		// @todo
		return '';
	}

	/**
	 * Returns the package name.
	 *
	 * @return string
	 */
	public function getPackageName()
	{
		if ($package = $this->getAnnotation('package')) {
			return $package[0];
		}

		return ReflectionClass::PACKAGE_NONE;
	}

	/**
	 * Returns a particular annotation value.
	 *
	 * @param string $name Annotation name
	 * @return string|array|null
	 */
	final public function getAnnotation($name)
	{
		$params = $this->getAnnotations();
		if (isset($params[$name])) {
			return $params[$name];
		}

		return null;
	}

	/**
	 * Checks if there is a particular annotation.
	 *
	 * @param string $name Annotation name
	 * @return boolean
	 */
	final public function hasAnnotation($name)
	{
		$params = $this->getAnnotations();
		return isset($params[$name]);
	}

	/**
	 * Returns all annotations.
	 *
	 * @return array
	 */
	final public function getAnnotations()
	{
		if (null === $this->parsedDocComment) {
			$this->parsedDocComment = ReflectionAnnotation::parse($this);
		}

		return $this->parsedDocComment;
	}

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
	final public function __isset($key) {
		return self::exists($this, $key);
	}

	/**
	 * Exports a reflected object.
	 *
	 * @param string $argument Reflection object name
	 * @param boolean $return Return the export instead of outputting it
	 * @return string|null
	 */
	public static function export($argument, $return = false)
	{
		// @todo
	}

	/**
	 * Magic __get method helper.
	 *
	 * @param \TokenReflection\IReflection $object Reflection object
	 * @param string $key Variable name
	 * @return mixed
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

		throw new RuntimeException(sprintf('Cannot read property %s', $key));
	}

	/**
	 * Magic __isset method helper.
	 *
	 * @param \TokenReflection\IReflection $object Reflection object
	 * @param string $key Variable name
	 * @return boolean
	 */
	final public static function exists(IReflection $object, $key) {
		try {
			self::get($object, $key);
			return true;
		} catch (RuntimeException $e) {
			return false;
		}
	}

	/**
	 * Returns a fully qualified name of a class using imported/aliased namespaces.
	 *
	 * @param string $className Input class name
	 * @param array $aliases Namespace import aliases
	 * @param string $namespaceName Context namespace name
	 * @return string
	 */
	final public static function resolveClassFQN($className, array $aliases, $namespaceName = null)
	{
		if ($className{0} == '\\') {
			// FQN
			return ltrim($className, '\\');
		}

		if (false === ($position = strpos($className, '\\'))) {
			// Plain class name
			if (isset($aliases[$className])) {
				return $aliases[$className];
			}
		} else {
			// Namespaced class name
			$alias = substr($className, 0, $position);
			if (isset($aliases[$alias])) {
				return $aliases[$alias] . '\\' . substr($className, $position + 1);
			}
		}

		return null === $namespaceName || $namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME ? $className : $namespaceName . '\\' . $className;
	}
}
