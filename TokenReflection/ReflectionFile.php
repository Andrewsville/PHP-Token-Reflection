<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 3
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
	 * File token stream.
	 *
	 * @var \TokenReflection\Stream
	 */
	private $tokenStream = null;

	/**
	 * Reflection broker.
	 *
	 * @var \TokenReflection\Broker
	 */
	private $broker;

	/**
	 * Constructor.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token stream
	 * @param \TokenReflection\Broker $broker Reflection broker
	 */
	public function __construct(Stream $tokenStream, Broker $broker)
	{
		$this->tokenStream = $tokenStream;
		$this->broker = $broker;
		$this->parse();
	}

	/**
	 * Returns the file name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->tokenStream->getFileName();
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
	 * @return string
	 * @throws \TokenReflection\Exception\Runtime
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
	 * @return string|null
	 * @throws \TokenReflection\Exception\Runtime
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
		return (string) $this->tokenStream;
	}

	/**
	 * Returns the file token stream.
	 *
	 * @return \TokenReflection\Stream
	 */
	public function getTokenStream()
	{
		return $this->tokenStream;
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
	 * @return \TokenReflection\ReflectionFile
	 * @throws \TokenReflection\Exception\Parse If the file could not be parsed
	 */
	private function parse()
	{
		if ($this->tokenStream->count() <= 1) {
			// No PHP content
			return $this;
		}

		try {
			if (!$this->tokenStream->is(T_OPEN_TAG)) {
				$this->namespaces[] = new ReflectionFileNamespace($this->tokenStream, $this->broker, $this);
			} else {
				$this->tokenStream->skipWhitespaces();

				while (null !== ($type = $this->tokenStream->getType())) {
					switch ($type) {
						case T_WHITESPACE:
						case T_DOC_COMMENT:
						case T_COMMENT:
							break;
						case T_DECLARE:
							$this->tokenStream
								->skipWhitespaces()
								->findMatchingBracket()
								->skipWhitespaces()
								->skipWhitespaces(); // Intentionally twice
							break;
						case T_NAMESPACE:
							break 2;
						default:
							$this->namespaces[] = new ReflectionFileNamespace($this->tokenStream, $this->broker, $this);
							return $this;
					}

					$this->tokenStream->skipWhitespaces();
				}

				while (null !== ($type = $this->tokenStream->getType())) {
					if (T_NAMESPACE === $type) {
						$this->namespaces[] = new ReflectionFileNamespace($this->tokenStream, $this->broker, $this);
					} else {
						$this->tokenStream->skipWhitespaces();
					}
				}
			}


			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse('Could not parse file contents.', Exception\Parse::PARSE_CHILDREN_ERROR, $e);
		}
	}
}
