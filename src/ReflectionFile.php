<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

use ApiGen;
use ApiGen\TokenReflection\Stream\StreamBase as Stream;
use ApiGen\TokenReflection\Exception;


class ReflectionFile extends ReflectionBase
{
	/**
	 * Namespaces list.
	 *
	 * @var array
	 */
	private $namespaces = array();

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
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the method is called, because it's unsupported.
	 */
	public function __toString()
	{
		throw new Exception\RuntimeException('Casting to string is not supported.', Exception\RuntimeException::UNSUPPORTED, $this);
	}

	/**
	 * Exports a reflected object.
	 *
	 * @param ApiGen\TokenReflection\Broker $broker Broker instance
	 * @param string $argument Reflection object name
	 * @param boolean $return Return the export instead of outputting it
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the method is called, because it's unsupported.
	 */
	public static function export(Broker $broker, $argument, $return = false)
	{
		throw new Exception\RuntimeException('Export is not supported.', Exception\RuntimeException::UNSUPPORTED);
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
	 * Parses the token substream and prepares namespace reflections from the file.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param ApiGen\TokenReflection\IReflection $parent Parent reflection object
	 * @return ApiGen\TokenReflection\ReflectionFile
	 */
	protected function parseStream(Stream $tokenStream, IReflection $parent = null)
	{
		$this->name = $tokenStream->getFileName();

		if (1 >= $tokenStream->count()) {
			// No PHP content
			$this->docComment = new ReflectionAnnotation($this, null);
			return $this;
		}

		$docCommentPosition = null;

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
	}
}
