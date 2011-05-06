<?php
/**
 * PHP Token Reflection
 *
 * Development version
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

use ArrayIterator;

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
	 * Assigns a filename and the token stream.
	 *
	 * @param string $filename File name
	 * @param array $tokenStream Token stream
	 * @param \TokenReflection\Broker $broker Reflection broker
	 */
	public function __construct($filename, array $tokenStream, Broker $broker)
	{
		$this->tokenStream = new Stream($tokenStream, $filename);
		$this->broker = $broker;
		$this->parse();
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
	 * Prepares namespace reflections from the file.
	 */
	private function parse()
	{
		if (count($this->tokenStream) <= 1) {
			// No PHP content
			return;
		}

		static $breakers = array(T_CLASS, T_INTERFACE, T_FUNCTION, T_CONST, T_IF);

		while (null !== ($type = $this->tokenStream->getType())) {
			if (in_array($type, $breakers)) {
				break;
			}

			if (T_NAMESPACE === $type) {
				$namespace = new ReflectionFileNamespace($this->tokenStream->getNamespaceStream(), $this->broker, $this);
				$this->namespaces[] = $namespace;
			}

			$this->tokenStream->skipWhitespaces();
		}

		if (empty($this->namespaces)) {
			// No namespaces at all -> assume the "none" pseudo-namespace
			// Find the right beginning of the namespace (considering file level docblocks)
			for ($nsStart = 0; $nsStart <= $this->tokenStream->key(); $nsStart++) {
				if ($this->tokenStream->is(T_OPEN_TAG, $nsStart)) {
					break;
				}
			}

			while (null !== ($this->tokenStream->getType($nsStart + 1)) && T_DOC_COMMENT === $this->tokenStream->getType($nsStart) && T_WHITESPACE === $type) {
				$nsStart += 2;
			}

			$tokens = $this->tokenStream->getArrayCopy();
			$namespace = new ReflectionFileNamespace(new Stream(array_slice($tokens, $nsStart), $this->tokenStream->getFileName()), $this->broker, $this);
			$this->namespaces[] = $namespace;
		}
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
	 * Returns the file name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->tokenStream->getFileName();
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
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return '';
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
	 * Exports a reflected object.
	 *
	 * @param string $argument Reflection object name
	 * @param boolean $return Return the export instead of outputting it
	 * @return string|null
	 */
	public static function export($argument, $return = false)
	{
		return ReflectionBase::export($argument, $return);
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
	final public function __isset($key) {
		return ReflectionBase::exists($this, $key);
	}
}
