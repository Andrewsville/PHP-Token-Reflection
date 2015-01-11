<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

use ApiGen;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Stream\StreamBase as Stream;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Stream\StreamBase;


class ReflectionFile extends ReflectionBase
{

	/**
	 * Namespaces list.
	 *
	 * @var array
	 */
	private $namespaces = [];


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
	 * @throws RuntimeException If the method is called, because it's unsupported.
	 */
	public function __toString()
	{
		throw new RuntimeException('Casting to string is not supported.', RuntimeException::UNSUPPORTED, $this);
	}


	/**
	 * @param Broker $broker Broker instance
	 * @param string $argument Reflection object name
	 * @param bool $return Return the export instead of outputting it
	 * @throws RuntimeException If the method is called, because it's unsupported.
	 */
	public static function export(Broker $broker, $argument, $return = FALSE)
	{
		throw new RuntimeException('Export is not supported.', RuntimeException::UNSUPPORTED);
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
	 * @return ApiGen\TokenReflection\ReflectionFile
	 */
	protected function parseStream(StreamBase $tokenStream, IReflection $parent = NULL)
	{
		$this->name = $tokenStream->getFileName();
		if ($tokenStream->count() <= 1) {
			// No PHP content
			$this->docComment = new ReflectionAnnotation($this, NULL);
			return $this;
		}
		$docCommentPosition = NULL;
		if ( ! $tokenStream->is(T_OPEN_TAG)) {
			$this->namespaces[] = new ReflectionFileNamespace($tokenStream, $this->broker, $this);
		} else {
			$tokenStream->skipWhitespaces();
			while (NULL !== ($type = $tokenStream->getType())) {
				switch ($type) {
					case T_DOC_COMMENT:
						if (NULL === $docCommentPosition) {
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
			while (NULL !== ($type = $tokenStream->getType())) {
				if (T_NAMESPACE === $type) {
					$this->namespaces[] = new ReflectionFileNamespace($tokenStream, $this->broker, $this);
				} else {
					$tokenStream->skipWhitespaces();
				}
			}
		}
		if (NULL !== $docCommentPosition && !empty($this->namespaces) && $docCommentPosition === $this->namespaces[0]->getStartPosition()) {
			$docCommentPosition = NULL;
		}
		$this->docComment = new ReflectionAnnotation($this, NULL !== $docCommentPosition ? $tokenStream->getTokenValue($docCommentPosition) : NULL);
		return $this;
	}

}