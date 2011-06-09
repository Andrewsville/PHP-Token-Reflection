<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 2
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

/**
 * Tokenized constant reflection.
 */
class ReflectionConstant extends ReflectionBase implements IReflectionConstant
{
	/**
	 * Name of the declaring class.
	 *
	 * @var String
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
	 * Constant value definition.
	 *
	 * @var string
	 */
	private $valueDefinition = '';

	/**
	 * Constant value is another constant name.
	 *
	 * @var boolean
	 */
	private $valueAsLink = false;

	/**
	 * Imported namespace/class aliases.
	 *
	 * @var array
	 */
	private $aliases = array();

	/**
	 * Processes the parent reflection object.
	 *
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 * @throws \TokenReflection\Exception\Parse If an invalid parent reflection object was provided
	 */
	protected function processParent(IReflection $parent)
	{
		if ($parent instanceof ReflectionFileNamespace) {
			$this->namespaceName = $parent->getName();
			$this->aliases = $parent->getNamespaceAliases();
		} elseif ($parent instanceof ReflectionClass) {
			$this->declaringClassName = $parent->getName();
		} else {
			throw new Exception\Parse(sprintf('The parent object has to be an instance of TokenReflection\ReflectionFileNamespace or TokenReflection\ReflectionClass, "%s" given.', get_class($parent)), Exception\Parse::INVALID_PARENT);
		}

		return parent::processParent($parent);
	}

	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionConstant
	 */
	protected function parse(Stream $tokenStream, IReflection $parent)
	{
		return $this
			->parseName($tokenStream)
			->parseValue($tokenStream, $parent);
	}

	/**
	 * Parses the constant name.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionConstant
	 * @throws \TokenReflection\Exception\Parse If the constant name could not be determined
	 */
	protected function parseName(Stream $tokenStream)
	{
		try {
			if ($tokenStream->is(T_CONST)) {
				$tokenStream->skipWhitespaces();
			}

			if (!$tokenStream->is(T_STRING)) {
				throw new Exception\Parse('The constant name could not be determined.', Exception\Parse::PARSE_ELEMENT_ERROR);
			}

			if (null === $this->namespaceName || $this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME) {
				$this->name = $tokenStream->getTokenValue();
			} else {
				$this->name = $this->namespaceName . '\\' . $tokenStream->getTokenValue();
			}

			$tokenStream->skipWhitespaces();

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse('Could not parse constant name.', Exception\Parse::PARSE_ELEMENT_ERROR, $e);
		}
	}

	/**
	 * Find the appropriate docblock.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection
	 * @return \TokenReflection\ReflectionConstant
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
	 * Parses the constant value.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionConstant
	 * @throws \TokenReflection\Exception\Parse If the constant value could not be determined
	 */
	private function parseValue(Stream $tokenStream, IReflection $parent)
	{
		try {
			if (!$tokenStream->is('=')) {
				throw new Exception\Parse('Could not find the definition start.', Exception\Parse::PARSE_ELEMENT_ERROR);
			}

			$tokenStream->skipWhitespaces();

			static $acceptedStrings = array('true' => true, 'false' => true, 'null' => true);
			static $acceptedTokens = array('-' => true, '+' => true, T_STRING => true, T_NS_SEPARATOR => true, T_CONSTANT_ENCAPSED_STRING => true, T_DNUMBER => true, T_LNUMBER => true, T_DOUBLE_COLON => true);

			$evalValue = true;
			while (null !== ($type = $tokenStream->getType())) {
				$value = $tokenStream->getTokenValue();

				if (!isset($acceptedTokens[$type])) {
					break;
				} elseif ($tokenStream->is(T_STRING) && !isset($acceptedStrings[strtolower($value)])) {
					$evalValue = false;
				}

				$this->valueDefinition .= $value;
				$tokenStream->next();
			}

			if (null === $type || (',' !== $value && ';' !== $value)) {
				throw new Exception\Parse(sprintf('Invalid value definition: "%s".', $this->valueDefinition), Exception\Parse::PARSE_ELEMENT_ERROR);
			}

			$this->valueDefinition = trim($this->valueDefinition);

			$this->valueAsLink = !$evalValue;
			if ($evalValue) {
				$this->value = eval(sprintf('return %s;', $this->valueDefinition));
			}

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse('Could not parse constant value.', Exception\Parse::PARSE_ELEMENT_ERROR, $e);
		}
	}

	/**
	 * Returns the constant value definition.
	 *
	 * @return string
	 */
	public function getValueDefinition()
	{
		if (!$this->valueAsLink) {
			return $this->valueDefinition;
		}

		if ($pos = strpos($this->valueDefinition, '::')) {
			$className = substr($this->valueDefinition, 0, $pos);
			if ('self' === strtolower($className)) {
				return $this->valueDefinition;
			}

			$nsClassName = self::resolveClassFQN($className, $this->getNamespaceAliases(), $this->namespaceName ?: $this->getDeclaringClass()->getNamespaceName());
			$nsValue = $nsClassName . substr($this->valueDefinition, $pos);
		} else {
			$nsValue = $this->valueDefinition;
			if ('\\' !== $nsValue{0}) {
				$nsValue = ltrim(($this->namespaceName ? $this->getNamespaceName() : $this->getDeclaringClass()->getNamespaceName()) . '\\' . $nsValue, '\\');
			}
		}

		return $this->getBroker()->hasConstant($nsValue) ? $nsValue : $this->valueDefinition;
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
	 * Returns the constant value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		if (null === $this->value && 'null' !== strtolower($this->valueDefinition)) {
			if ($position = strpos($this->valueDefinition, '::')) {
				$className = substr($this->valueDefinition, 0, $position);
				$constantName = substr($this->valueDefinition, $position + 2);

				$class = $this->getBroker()->getClass($className);
				$this->value = $class->hasConstant($constantName) ? $class->getConstantReflection($constantName)->getValue() : null;
			} else {
				$constant = $this->getBroker()->getConstant($this->valueDefinition);
				$this->value = $constant ? $constant->getValue() : null;
			}
		}

		return $this->value;
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
	 * @return \TokenReflection\ReflectionClass|null
	 */
	public function getDeclaringClass()
	{
		if (null === $this->declaringClassName) {
			return null;
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
		return null === $this->namespaceName || $this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME ? '' : $this->namespaceName;
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
	 * Returns the unqualified name (UQN).
	 *
	 * @return string
	 */
	public function getShortName()
	{
		$name = $this->getName();
		if (null !== $this->namespaceName && $this->namespaceName !== ReflectionNamespace::NO_NAMESPACE_NAME) {
			$name = substr($name, strlen($this->namespaceName) + 1);
		}

		return $name;
	}

	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return null === $this->declaringClassName ? $this->aliases : $this->getDeclaringClass()->getNamespaceAliases();
	}
}
