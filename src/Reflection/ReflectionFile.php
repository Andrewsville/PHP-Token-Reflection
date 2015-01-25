<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection;

use ApiGen\TokenReflection\Reflection\Factory\ReflectionAnnotationFactory;
use ApiGen\TokenReflection\Reflection\Factory\ReflectionFileNamespaceFactory;
use ApiGen\TokenReflection\Stream\StreamBase;


class ReflectionFile extends ReflectionBase
{

	/**
	 * @var ReflectionFileNamespace[]
	 */
	private $namespaces = [];

	/**
	 * @var ReflectionAnnotationFactory
	 */
	private $reflectionAnnotationFactory;

	/**
	 * @var ReflectionFileNamespaceFactory
	 */
	private $reflectionFileNamespaceFactory;


	/**
	 * @return ReflectionFileNamespace[]
	 */
	public function getNamespaces()
	{
		return $this->namespaces;
	}


	public function setReflectionAnnotationFactory(ReflectionAnnotationFactory $reflectionAnnotationFactory)
	{
		$this->reflectionAnnotationFactory = $reflectionAnnotationFactory;
	}


	public function setReflectionFileNamespaceFactory(ReflectionFileNamespaceFactory $reflectionFileNamespaceFactory)
	{
		$this->reflectionFileNamespaceFactory = $reflectionFileNamespaceFactory;
	}


	/**
	 * Parses the token substream and prepares namespace reflections from the file.
	 */
	protected function parseStream(StreamBase $tokenStream)
	{
		$this->name = $tokenStream->getFileName();
		if ($tokenStream->count() <= 1) {
			if ($this->reflectionAnnotationFactory) {
				$this->docComment = $this->reflectionAnnotationFactory->create($this, NULL);

			} else {
				$this->docComment = new ReflectionAnnotation($this, NULL);
			}

		} else {
			$docCommentPosition = NULL;
			if ( ! $tokenStream->is(T_OPEN_TAG)) {
				if ($this->reflectionFileNamespaceFactory) {
					$this->namespaces[] = $this->reflectionFileNamespaceFactory->create($tokenStream, $this);

				} else {
					$this->namespaces[] = new ReflectionFileNamespace($tokenStream, $this->storage, $this);
				}

			} else {
				$tokenStream->skipWhitespaces();
				while (($type = $tokenStream->getType()) !== NULL) {
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
							$tokenStream->skipWhitespaces()
								->findMatchingBracket()
								->skipWhitespaces();
							break;
						case T_NAMESPACE:
							$docCommentPosition = $docCommentPosition ?: -1;
							break 2;
						default:
							$docCommentPosition = $docCommentPosition ?: -1;
							if ($this->reflectionFileNamespaceFactory) {
								$this->namespaces[] = $this->reflectionFileNamespaceFactory->create($tokenStream, $this);

							} else {
								$this->namespaces[] = new ReflectionFileNamespace($tokenStream, $this->storage, $this);
							}

							break 2;
					}
					$tokenStream->skipWhitespaces();
				}

				while (($type = $tokenStream->getType()) !== NULL) {
					if (T_NAMESPACE === $type) {
						if ($this->reflectionFileNamespaceFactory) {
							$this->namespaces[] = $this->reflectionFileNamespaceFactory->create($tokenStream, $this);

						} else {
							$this->namespaces[] = new ReflectionFileNamespace($tokenStream, $this->storage, $this);
						}

					} else {
						$tokenStream->skipWhitespaces();
					}
				}
			}

			if ($docCommentPosition !== NULL && ! empty($this->namespaces) && $docCommentPosition === $this->namespaces[0]->getStartPosition()) {
				$docCommentPosition = NULL;
			}

			$docComment = $docCommentPosition !== NULL ? $tokenStream->getTokenValue($docCommentPosition) : NULL;
			if ($this->reflectionAnnotationFactory) {
				$this->docComment = $this->reflectionAnnotationFactory->create($this, $docComment);

			} else {
				$this->docComment = new ReflectionAnnotation($this, $docComment);
			}
		}
	}

}
