<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.0 RC 2
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

use TokenReflection\Stream\StreamBase as Stream;

/**
 * Processed file class.
 */
class ReflectionFile implements IReflection
{
	/**
	 * Namespaces list.
	 *
	 * @var array
	 */
	private $namespaces = array();

	/**
	 * File name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Reflection broker.
	 *
	 * @var \TokenReflection\Broker
	 */
	private $broker;

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
	 * Constructor.
	 *
	 * @param \TokenReflection\Stream\StreamBase $tokenStream Token stream
	 * @param \TokenReflection\Broker $broker Reflection broker
	 */
	public function __construct(Stream $tokenStream, Broker $broker)
	{
		$this->broker = $broker;
		$this->name = $tokenStream->getFileName();

		$this->parse($tokenStream);
	}

	/**
	 * Returns the file name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns if the file is internal.
	 *
	 * Always false.
	 *
	 * @return boolean
	 */
	public function isInternal()
	{
		return false;
	}

	/**
	 * Returns if the file is user defined.
	 *
	 * Always true.
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
	 * Returns an array of namespaces in the current file.
	 *
	 * @return array
	 */
	public function getNamespaces()
	{
		return $this->namespaces;
	}

	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @throws \TokenReflection\Exception\Runtime If the method is called, because it's unsupported.
	 */
	public function __toString()
	{
		throw new Exception\Runtime('__toString is not supported.', Exception\Runtime::UNSUPPORTED);
	}

	/**
	 * Exports a reflected object.
	 *
	 * @param \TokenReflection\Broker $broker Broker instance
	 * @param string $argument Reflection object name
	 * @param boolean $return Return the export instead of outputting it
	 * @throws \TokenReflection\Exception\Runtime If the method is called, because it's unsupported.
	 */
	public static function export(Broker $broker, $argument, $return = false)
	{
		throw new Exception\Runtime('Export is not supported.', Exception\Runtime::UNSUPPORTED);
	}

	/**
	 * Outputs the file source code.
	 *
	 * @return string
	 */
	public function getSource()
	{
		return (string) $this->broker->getFileTokens($this->getName());
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
	 * Returns the reflection broker used by this reflection object.
	 *
	 * @return \TokenReflection\Broker
	 */
	public function getBroker()
	{
		return $this->broker;
	}

	/**
	 * Magic __get method.
	 *
	 * @param string $key Variable name
	 * @return mixed
	 */
	final public function __get($key)
	{
		return ReflectionBase::get($this, $key);
	}

	/**
	 * Magic __isset method.
	 *
	 * @param string $key Variable name
	 * @return boolean
	 */
	final public function __isset($key)
	{
		return ReflectionBase::exists($this, $key);
	}

	/**
	 * Prepares namespace reflections from the file.
	 *
	 * @param \TokenReflection\Stream\StreamBase $tokenStream Token stream
	 * @return \TokenReflection\ReflectionFile
	 * @throws \TokenReflection\Exception\Parse If the file could not be parsed.
	 */
	private function parse(Stream $tokenStream)
	{
		if ($tokenStream->count() <= 1) {
			// No PHP content
			return $this;
		}

		$docCommentPosition = null;

		try {
			if (!$tokenStream->is(T_OPEN_TAG)) {
				$this->namespaces[] = new ReflectionFileNamespace($tokenStream, $this->broker, $this);
			} else {
				$tokenStream->skipWhitespaces();

				while (null !== ($type = $tokenStream->getType())) {
					switch ($type) {
						case T_DOC_COMMENT:
							if (null === $docCommentPosition) {
								$docCommentPosition = $tokenStream->key();
							}
						case T_WHITESPACE:
						case T_COMMENT:
							break;
						case T_DECLARE:
							// Intentionally twice call of skipWhitespaces()
							$tokenStream
								->skipWhitespaces()
								->findMatchingBracket()
								->skipWhitespaces()
								->skipWhitespaces();
							break;
						case T_NAMESPACE:
							$docCommentPosition = $docCommentPosition ?: -1;
							break 2;
						default:
							$docCommentPosition = $docCommentPosition ?: -1;
							$this->namespaces[] = new ReflectionFileNamespace($tokenStream, $this->broker, $this);
							break 2;
					}

					$tokenStream->skipWhitespaces();
				}

				while (null !== ($type = $tokenStream->getType())) {
					if (T_NAMESPACE === $type) {
						$this->namespaces[] = new ReflectionFileNamespace($tokenStream, $this->broker, $this);
					} else {
						$tokenStream->skipWhitespaces();
					}
				}
			}

			if (null !== $docCommentPosition && !empty($this->namespaces) && $docCommentPosition === $this->namespaces[0]->getStartPosition()) {
				$docCommentPosition = null;
			}
			$this->docComment = new ReflectionAnnotation($this, null !== $docCommentPosition ? $tokenStream->getTokenValue($docCommentPosition) : null);

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse('Could not parse file contents.', Exception\Parse::PARSE_CHILDREN_ERROR, $e);
		}
	}
}
