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


/**
 * Tokenized constant reflection.
 */
class ReflectionConstant extends ReflectionElement implements IReflectionConstant
{

	/**
	 * Name of the declaring class.
	 *
	 * @var string
	 */
	private $declaringClassName;

	/**
	 * Constant namespace name.
	 *
	 * @var string
	 */
	private $namespaceName;

	/**
	 * Constant value.
	 *
	 * @var mixed
	 */
	private $value;

	/**
	 * Constant value definition in tokens.
	 *
	 * @var array|string
	 */
	private $valueDefinition = [];

	/**
	 * Imported namespace/class aliases.
	 *
	 * @var array
	 */
	private $aliases = [];


	/**
	 * Returns the unqualified name (UQN).
	 *
	 * @return string
	 */
	public function getShortName()
	{
		$name = $this->getName();
		if (NULL !== $this->namespaceName && $this->namespaceName !== ReflectionNamespace::NO_NAMESPACE_NAME) {
			$name = substr($name, strlen($this->namespaceName) + 1);
		}
		return $name;
	}


	/**
	 * Returns the name of the declaring class.
	 *
	 * @return string|null
	 */
	public function getDeclaringClassName()
	{
		return $this->declaringClassName;
	}


	/**
	 * Returns a reflection of the declaring class.
	 *
	 * @return ApiGen\TokenReflection\ReflectionClass|null
	 */
	public function getDeclaringClass()
	{
		if (NULL === $this->declaringClassName) {
			return NULL;
		}
		return $this->getBroker()->getClass($this->declaringClassName);
	}


	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	public function getNamespaceName()
	{
		return NULL === $this->namespaceName || $this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME ? '' : $this->namespaceName;
	}


	/**
	 * Returns if the class is defined within a namespace.
	 *
	 * @return boolean
	 */
	public function inNamespace()
	{
		return '' !== $this->getNamespaceName();
	}


	/**
	 * Returns the constant value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		if (is_array($this->valueDefinition)) {
			$this->value = Resolver::getValueDefinition($this->valueDefinition, $this);
			$this->valueDefinition = Resolver::getSourceCode($this->valueDefinition);
		}
		return $this->value;
	}


	/**
	 * Returns the constant value definition.
	 *
	 * @return string
	 */
	public function getValueDefinition()
	{
		return is_array($this->valueDefinition) ? Resolver::getSourceCode($this->valueDefinition) : $this->valueDefinition;
	}


	/**
	 * Returns the originaly provided value definition.
	 *
	 * @return string
	 */
	public function getOriginalValueDefinition()
	{
		return $this->valueDefinition;
	}


	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return sprintf(
			"Constant [ %s %s ] { %s }\n",
			strtolower(gettype($this->getValue())),
			$this->getName(),
			$this->getValue()
		);
	}


	/**
	 * Exports a reflected object.
	 *
	 * @param ApiGen\TokenReflection\Broker $broker Broker instance
	 * @param string|object|null $class Class name, class instance or null
	 * @param string $constant Constant name
	 * @param boolean $return Return the export instead of outputting it
	 * @return string|null
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If requested parameter doesn't exist.
	 */
	public static function export(Broker $broker, $class, $constant, $return = FALSE)
	{
		$className = is_object($class) ? get_class($class) : $class;
		$constantName = $constant;
		if (NULL === $className) {
			$constant = $broker->getConstant($constantName);
			if (NULL === $constant) {
				throw new Exception\RuntimeException('Constant does not exist.', Exception\RuntimeException::DOES_NOT_EXIST);
			}
		} else {
			$class = $broker->getClass($className);
			if ($class instanceof Invalid\ReflectionClass) {
				throw new Exception\RuntimeException('Class is invalid.', Exception\RuntimeException::UNSUPPORTED);
			} elseif ($class instanceof Dummy\ReflectionClass) {
				throw new Exception\RuntimeException('Class does not exist.', Exception\RuntimeException::DOES_NOT_EXIST, $class);
			}
			$constant = $class->getConstantReflection($constantName);
		}
		if ($return) {
			return $constant->__toString();
		}
		echo $constant->__toString();
	}


	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return NULL === $this->declaringClassName ? $this->aliases : $this->getDeclaringClass()->getNamespaceAliases();
	}


	/**
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	public function getPrettyName()
	{
		return NULL === $this->declaringClassName ? parent::getPrettyName() : sprintf('%s::%s', $this->declaringClassName, $this->name);
	}


	/**
	 * Returns if the constant definition is valid.
	 *
	 * @return boolean
	 */
	public function isValid()
	{
		return TRUE;
	}


	/**
	 * Processes the parent reflection object.
	 *
	 * @param ApiGen\TokenReflection\IReflection $parent Parent reflection object
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @return ApiGen\TokenReflection\ReflectionElement
	 * @throws ApiGen\TokenReflection\Exception\ParseException If an invalid parent reflection object was provided.
	 */
	protected function processParent(IReflection $parent, Stream $tokenStream)
	{
		if ($parent instanceof ReflectionFileNamespace) {
			$this->namespaceName = $parent->getName();
			$this->aliases = $parent->getNamespaceAliases();
		} elseif ($parent instanceof ReflectionClass) {
			$this->declaringClassName = $parent->getName();
		} else {
			throw new Exception\ParseException($this, $tokenStream, sprintf('Invalid parent reflection provided: "%s".', get_class($parent)), Exception\ParseException::INVALID_PARENT);
		}
		return parent::processParent($parent, $tokenStream);
	}


	/**
	 * Find the appropriate docblock.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param ApiGen\TokenReflection\IReflection $parent Parent reflection
	 * @return ApiGen\TokenReflection\ReflectionConstant
	 */
	protected function parseDocComment(Stream $tokenStream, IReflection $parent)
	{
		$position = $tokenStream->key() - 1;
		while ($position > 0 && !$tokenStream->is(T_CONST, $position)) {
			$position--;
		}
		$actual = $tokenStream->key();
		parent::parseDocComment($tokenStream->seek($position), $parent);
		$tokenStream->seek($actual);
		return $this;
	}


	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param ApiGen\TokenReflection\IReflection $parent Parent reflection object
	 * @return ApiGen\TokenReflection\ReflectionConstant
	 */
	protected function parse(Stream $tokenStream, IReflection $parent)
	{
		if ($tokenStream->is(T_CONST)) {
			$tokenStream->skipWhitespaces(TRUE);
		}
		if (FALSE === $this->docComment->getDocComment()) {
			parent::parseDocComment($tokenStream, $parent);
		}
		return $this
			->parseName($tokenStream)
			->parseValue($tokenStream, $parent);
	}


	/**
	 * Parses the constant name.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @return ApiGen\TokenReflection\ReflectionConstant
	 * @throws ApiGen\TokenReflection\Exception\ParseReflection If the constant name could not be determined.
	 */
	protected function parseName(Stream $tokenStream)
	{
		if (!$tokenStream->is(T_STRING)) {
			throw new Exception\ParseException($this, $tokenStream, 'The constant name could not be determined.', Exception\ParseException::LOGICAL_ERROR);
		}
		if (NULL === $this->namespaceName || $this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME) {
			$this->name = $tokenStream->getTokenValue();
		} else {
			$this->name = $this->namespaceName . '\\' . $tokenStream->getTokenValue();
		}
		$tokenStream->skipWhitespaces(TRUE);
		return $this;
	}


	/**
	 * Parses the constant value.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param ApiGen\TokenReflection\IReflection $parent Parent reflection object
	 * @return ApiGen\TokenReflection\ReflectionConstant
	 * @throws ApiGen\TokenReflection\Exception\ParseException If the constant value could not be determined.
	 */
	private function parseValue(Stream $tokenStream, IReflection $parent)
	{
		if (!$tokenStream->is('=')) {
			throw new Exception\ParseException($this, $tokenStream, 'Could not find the definition start.', Exception\ParseException::UNEXPECTED_TOKEN);
		}
		$tokenStream->skipWhitespaces(TRUE);
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
		while (NULL !== ($type = $tokenStream->getType())) {
			if (T_START_HEREDOC === $type) {
				$this->valueDefinition[] = $tokenStream->current();
				while (NULL !== $type && T_END_HEREDOC !== $type) {
					$tokenStream->next();
					$this->valueDefinition[] = $tokenStream->current();
					$type = $tokenStream->getType();
				};
				$tokenStream->next();
			} elseif (isset($acceptedTokens[$type])) {
				$this->valueDefinition[] = $tokenStream->current();
				$tokenStream->next();
			} elseif ($tokenStream->isWhitespace(TRUE)) {
				$tokenStream->skipWhitespaces(TRUE);
			} else {
				break;
			}
		}
		if (empty($this->valueDefinition)) {
			throw new Exception\ParseException($this, $tokenStream, 'Value definition is empty.', Exception\ParseException::LOGICAL_ERROR);
		}
		$value = $tokenStream->getTokenValue();
		if (NULL === $type || (',' !== $value && ';' !== $value)) {
			throw new Exception\ParseException($this, $tokenStream, 'Invalid value definition.', Exception\ParseException::LOGICAL_ERROR);
		}
		return $this;
	}
}
