<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Parser;

use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionPropertyInterface;
use ApiGen\TokenReflection\Reflection\ReflectionNamespace;
use ApiGen\TokenReflection\Stream\StreamBase;


class ConstantParser
{

	/**
	 * @var StreamBase
	 */
	private $tokenStream;

	/**
	 * @var ReflectionConstantInterface
	 */
	private $reflectionConstant;

	/**
	 * @var ReflectionInterface|ReflectionClassInterface
	 */
	private $parent;


	public function __construct(StreamBase $tokenStream, ReflectionConstantInterface $reflectionConstant, ReflectionInterface $parent = NULL)
	{
		$this->tokenStream = $tokenStream;
		$this->reflectionConstant = $reflectionConstant;
		$this->parent = $parent;
	}


	/**
	 * @param string $namespace
	 * @return string
	 */
	public function parseName($namespace)
	{
		if ( ! $this->tokenStream->is(T_STRING)) {
			throw new ParseException('The constant name could not be determined.', ParseException::LOGICAL_ERROR);
		}

		if ($namespace === NULL || $namespace === ReflectionNamespace::NO_NAMESPACE_NAME) {
			$name = $this->tokenStream->getTokenValue();
		} else {
			$name = $namespace . '\\' . $this->tokenStream->getTokenValue();
		}
		$this->tokenStream->skipWhitespaces(TRUE);
		return $name;
	}


	/**
	 * @return array
	 */
	public function parseValue()
	{
		$valueDefinition = [];

		if ( ! $this->tokenStream->is('=')) {
			throw new ParseException('Could not find the definition start.', ParseException::UNEXPECTED_TOKEN);
		}

		$this->tokenStream->skipWhitespaces(TRUE);

		static $acceptedTokens = [
			'-' => TRUE,
			'+' => TRUE,
			T_STRING => TRUE,
			T_NS_SEPARATOR => TRUE,
			T_CONSTANT_ENCAPSED_STRING => TRUE,
			T_DNUMBER => TRUE,
			T_LNUMBER => TRUE,
			T_DOUBLE_COLON => TRUE,
			T_CLASS_C => TRUE,
			T_DIR => TRUE,
			T_FILE => TRUE,
			T_FUNC_C => TRUE,
			T_LINE => TRUE,
			T_METHOD_C => TRUE,
			T_NS_C => TRUE,
			T_TRAIT_C => TRUE
		];

		while (NULL !== ($type = $this->tokenStream->getType())) {
			if (T_START_HEREDOC === $type) {
				$valueDefinition[] = $this->tokenStream->current();
				while (NULL !== $type && T_END_HEREDOC !== $type) {
					$this->tokenStream->next();
					$valueDefinition[] = $this->tokenStream->current();
					$type = $this->tokenStream->getType();
				};
				$this->tokenStream->next();

			} elseif (isset($acceptedTokens[$type])) {
				$valueDefinition[] = $this->tokenStream->current();
				$this->tokenStream->next();

			} elseif ($this->tokenStream->isWhitespace(TRUE)) {
				$this->tokenStream->skipWhitespaces(TRUE);

			} else {
				break;
			}
		}
		if (empty($valueDefinition)) {
			throw new ParseException('Value definition is empty.', ParseException::LOGICAL_ERROR);
		}
		$value = $this->tokenStream->getTokenValue();
		if (NULL === $type || (',' !== $value && ';' !== $value)) {
			throw new ParseException('Invalid value definition.', ParseException::LOGICAL_ERROR);
		}

		return $valueDefinition;
	}

}
