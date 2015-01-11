<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Stream\StreamBase;


class ReflectionParameter extends ReflectionElement implements IReflectionParameter
{

	/**
	 * The parameter requires an array as its value.
	 *
	 * @var string
	 */
	const ARRAY_TYPE_HINT = 'array';

	/**
	 * The parameter requires a callback definition as its value.
	 *
	 * @var string
	 */
	const CALLABLE_TYPE_HINT = 'callable';

	/**
	 * Declaring class name.
	 *
	 * @var string
	 */
	private $declaringClassName;

	/**
	 * Declaring function name.
	 *
	 * @var string
	 */
	private $declaringFunctionName;

	/**
	 * Parameter default value.
	 *
	 * @var mixed
	 */
	private $defaultValue;

	/**
	 * Parameter default value definition (part of the source code).
	 *
	 * @var array|string
	 */
	private $defaultValueDefinition = [];

	/**
	 * Defines a type hint (class name or array) of parameter values.
	 *
	 * @var string
	 */
	private $typeHint;

	/**
	 * Defines a type hint (class name, array or callable) of parameter values as it was defined.
	 *
	 * @var string
	 */
	private $originalTypeHint;

	/**
	 * Position of the parameter in the function/method.
	 *
	 * @var int
	 */
	private $position;

	/**
	 * Determines if the parameter is optional.
	 *
	 * @var bool
	 */
	private $isOptional;

	/**
	 * @var bool
	 */
	private $isVariadic = FALSE;

	/**
	 * Determines if the value is passed by reference.
	 *
	 * @var bool
	 */
	private $passedByReference = FALSE;


	/**
	 * Returns the declaring class.
	 *
	 * @return ReflectionClass|NULL
	 */
	public function getDeclaringClass()
	{
		return NULL === $this->declaringClassName ? NULL : $this->getBroker()->getClass($this->declaringClassName);
	}


	/**
	 * Returns the declaring class name.
	 *
	 * @return string|null
	 */
	public function getDeclaringClassName()
	{
		return $this->declaringClassName;
	}


	/**
	 * Returns the declaring function.
	 *
	 * @return ReflectionFunctionBase
	 */
	public function getDeclaringFunction()
	{
		if (NULL !== $this->declaringClassName) {
			// Method parameter
			$class = $this->getBroker()->getClass($this->declaringClassName);
			if (NULL !== $class) {
				return $class->getMethod($this->declaringFunctionName);
			}
		} else {
			// Function parameter
			return $this->getBroker()->getFunction($this->declaringFunctionName);
		}
	}


	/**
	 * Returns the declaring function name.
	 *
	 * @return string
	 */
	public function getDeclaringFunctionName()
	{
		return $this->declaringFunctionName;
	}


	/**
	 * Returns the default value.
	 *
	 * @return mixed
	 * @throws RuntimeException If the property is not optional.
	 * @throws RuntimeException If the property has no default value.
	 */
	public function getDefaultValue()
	{
		if ( ! $this->isOptional()) {
			throw new RuntimeException('Property is not optional.', RuntimeException::UNSUPPORTED, $this);
		}
		if (NULL === $this->defaultValue) {
			if (0 === count($this->defaultValueDefinition)) {
				throw new RuntimeException('Property has no default value.', RuntimeException::DOES_NOT_EXIST, $this);
			}
			$this->defaultValue = Resolver::getValueDefinition($this->defaultValueDefinition, $this);
		}
		return $this->defaultValue;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDefaultValueDefinition()
	{
		return Resolver::getSourceCode($this->defaultValueDefinition);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isDefaultValueConstant()
	{
		if ( ! $this->isDefaultValueAvailable() || empty($this->defaultValueDefinition)) {
			return FALSE;
		}
		static $expected = [T_STRING => TRUE, T_NS_SEPARATOR => TRUE, T_DOUBLE_COLON => TRUE];
		foreach ($this->defaultValueDefinition as $token) {
			if ( ! isset($expected[$token[0]])) {
				return FALSE;
			}
		}
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDefaultValueConstantName()
	{
		if ( ! $this->isOptional()) {
			throw new RuntimeException('Property is not optional.', RuntimeException::UNSUPPORTED, $this);
		}
		return $this->isDefaultValueConstant() ? $this->getDefaultValueDefinition() : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isDefaultValueAvailable()
	{
		return $this->isOptional();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPosition()
	{
		return $this->position;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isArray()
	{
		return $this->typeHint === self::ARRAY_TYPE_HINT;
	}


	/**
	 * Returns if the parameter expects a callback.
	 *
	 * @return bool
	 */
	public function isCallable()
	{
		return $this->typeHint === self::CALLABLE_TYPE_HINT;
	}


	/**
	 * Returns the original type hint as defined in the source code.
	 *
	 * @return string|null
	 */
	public function getOriginalTypeHint()
	{
		return !$this->isArray() && !$this->isCallable() ? ltrim($this->originalTypeHint, '\\') : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClass()
	{
		$name = $this->getClassName();
		if (NULL === $name) {
			return NULL;
		}
		return $this->getBroker()->getClass($name);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClassName()
	{
		if ($this->isArray() || $this->isCallable()) {
			return NULL;
		}
		if (NULL === $this->typeHint && NULL !== $this->originalTypeHint) {
			if (NULL !== $this->declaringClassName) {
				$parent = $this->getDeclaringClass();
				if (NULL === $parent) {
					throw new RuntimeException('Could not load class reflection.', RuntimeException::DOES_NOT_EXIST, $this);
				}
			} else {
				$parent = $this->getDeclaringFunction();
				if (NULL === $parent || !$parent->isTokenized()) {
					throw new RuntimeException('Could not load function reflection.', RuntimeException::DOES_NOT_EXIST, $this);
				}
			}
			$lTypeHint = strtolower($this->originalTypeHint);
			if ('parent' === $lTypeHint || 'self' === $lTypeHint) {
				if (NULL === $this->declaringClassName) {
					throw new RuntimeException('Parameter type hint cannot be "self" nor "parent" when not a method.', RuntimeException::UNSUPPORTED, $this);
				}
				if ('parent' === $lTypeHint) {
					if ($parent->isInterface() || NULL === $parent->getParentClassName()) {
						throw new RuntimeException('Class has no parent.', RuntimeException::DOES_NOT_EXIST, $this);
					}
					$this->typeHint = $parent->getParentClassName();
				} else {
					$this->typeHint = $this->declaringClassName;
				}
			} else {
				$this->typeHint = ltrim(Resolver::resolveClassFQN($this->originalTypeHint, $parent->getNamespaceAliases(), $parent->getNamespaceName()), '\\');
			}
		}
		return $this->typeHint;
	}


	/**
	 * {@inheritdoc}
	 */
	public function allowsNull()
	{
		if ($this->isArray() || $this->isCallable()) {
			return strtolower($this->getDefaultValueDefinition()) === 'null';
		}
		return $this->originalTypeHint === NULL || !empty($this->defaultValueDefinition);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isOptional()
	{
		if (NULL === $this->isOptional) {
			$this->isOptional = !empty($this->defaultValueDefinition) && $this->haveSiblingsDefaultValues();
		}
		return $this->isOptional;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isVariadic()
	{
		return $this->isVariadic;
	}


	/**
	 * Returns if all following parameters have a default value definition.
	 *
	 * @return bool
	 */
	protected function haveSiblingsDefaultValues()
	{
		$function = $this->getDeclaringFunction();
		if (NULL === $function) {
			throw new RuntimeException('Could not get the declaring function reflection.', RuntimeException::DOES_NOT_EXIST, $this);
		}
		foreach (array_slice($function->getParameters(), $this->position + 1) as $reflectionParameter) {
			if (NULL === $reflectionParameter->getDefaultValueDefinition()) {
				return FALSE;
			}
		}
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isPassedByReference()
	{
		return $this->passedByReference;
	}


	/**
	 * {@inheritdoc}
	 */
	public function canBePassedByValue()
	{
		return !$this->isPassedByReference();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return str_replace('()', '($' . $this->name . ')', $this->getDeclaringFunction()->getPrettyName());
	}


	/**
	 * {@inheritdoc}
	 */
	public function __toString()
	{
		if ($this->getClass()) {
			$hint = $this->getClassName();
		} elseif ($this->isArray()) {
			$hint = self::ARRAY_TYPE_HINT;
		} elseif ($this->isCallable()) {
			$hint = self::CALLABLE_TYPE_HINT;
		} else {
			$hint = '';
		}
		if ( ! empty($hint) && $this->allowsNull()) {
			$hint .= ' or NULL';
		}
		if ($this->isDefaultValueAvailable()) {
			$default = ' = ';
			if (NULL === $this->getDefaultValue()) {
				$default .= 'NULL';
			} elseif (is_array($this->getDefaultValue())) {
				$default .= 'Array';
			} elseif (is_bool($this->getDefaultValue())) {
				$default .= $this->getDefaultValue() ? 'true' : 'false';
			} elseif (is_string($this->getDefaultValue())) {
				$default .= sprintf("'%s'", str_replace("'", "\\'", $this->getDefaultValue()));
			} else {
				$default .= $this->getDefaultValue();
			}
		} else {
			$default = '';
		}
		return sprintf(
			'Parameter #%d [ <%s> %s%s%s$%s%s ]',
			$this->getPosition(),
			$this->isOptional() ? 'optional' : 'required',
			$hint ? $hint . ' ' : '',
			$this->isPassedByReference() ? '&' : '',
			$this->isVariadic() ? '...' : '',
			$this->getName(),
			$default
		);
	}


	/**
	 * Exports a reflected object.
	 *
	 * @param Broker $broker
	 * @param string $function Function name
	 * @param string $parameter Parameter name
	 * @param bool $return Return the export instead of outputting it
	 * @return string|null
	 * @throws RuntimeException If requested parameter doesn't exist.
	 */
	public static function export(Broker $broker, $function, $parameter, $return = FALSE)
	{
		$functionName = $function;
		$parameterName = $parameter;
		$function = $broker->getFunction($functionName);
		if ($function === NULL) {
			throw new RuntimeException(sprintf('Function %s() does not exist.', $functionName), RuntimeException::DOES_NOT_EXIST);
		}
		$parameter = $function->getParameter($parameterName);
		if ($return) {
			return $parameter->__toString();
		}
		echo $parameter->__toString();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceAliases()
	{
		return $this->getDeclaringFunction()->getNamespaceAliases();
	}


	/**
	 * Creates a parameter alias for the given method.
	 *
	 * @return ReflectionParameter
	 */
	public function alias(ReflectionMethod $parent)
	{
		$parameter = clone $this;
		$parameter->declaringClassName = $parent->getDeclaringClassName();
		$parameter->declaringFunctionName = $parent->getName();
		return $parameter;
	}


	/**
	 * @return ReflectionElement
	 * @throws ParseException If an invalid parent reflection object was provided.
	 */
	protected function processParent(IReflection $parent, StreamBase $tokenStream)
	{
		if ( ! $parent instanceof ReflectionFunctionBase) {
			throw new ParseException($this, $tokenStream, 'The parent object has to be an instance of TokenReflection\ReflectionFunctionBase.', ParseException::INVALID_PARENT);
		}
		// Declaring function name
		$this->declaringFunctionName = $parent->getName();
		// Position
		$this->position = count($parent->getParameters());
		// Declaring class name
		if ($parent instanceof ReflectionMethod) {
			$this->declaringClassName = $parent->getDeclaringClassName();
		}
		return parent::processParent($parent, $tokenStream);
	}


	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @return ReflectionParameter
	 */
	protected function parse(StreamBase $tokenStream, IReflection $parent)
	{
		return $this->parseTypeHint($tokenStream)
			->parsePassedByReference($tokenStream)
			->parseIsVariadic($tokenStream)
			->parseName($tokenStream)
			->parseDefaultValue($tokenStream);
	}


	/**
	 * @return ReflectionParameter
	 * @throws ParseException If the type hint class name could not be determined.
	 */
	private function parseTypeHint(StreamBase $tokenStream)
	{
		$type = $tokenStream->getType();
		if (T_ARRAY === $type) {
			$this->typeHint = self::ARRAY_TYPE_HINT;
			$this->originalTypeHint = self::ARRAY_TYPE_HINT;
			$tokenStream->skipWhitespaces(TRUE);
		} elseif (T_CALLABLE === $type) {
			$this->typeHint = self::CALLABLE_TYPE_HINT;
			$this->originalTypeHint = self::CALLABLE_TYPE_HINT;
			$tokenStream->skipWhitespaces(TRUE);
		} elseif (T_STRING === $type || T_NS_SEPARATOR === $type) {
			$className = '';
			do {
				$className .= $tokenStream->getTokenValue();
				$tokenStream->skipWhitespaces(TRUE);
				$type = $tokenStream->getType();
			} while (T_STRING === $type || T_NS_SEPARATOR === $type);
			if ('' === ltrim($className, '\\')) {
				throw new ParseException($this, $tokenStream, sprintf('Invalid class name definition: "%s".', $className), ParseException::LOGICAL_ERROR);
			}
			$this->originalTypeHint = $className;
		}
		return $this;
	}


	/**
	 * @return ReflectionParameter
	 */
	private function parsePassedByReference(StreamBase $tokenStream)
	{
		if ($tokenStream->is('&')) {
			$this->passedByReference = TRUE;
			$tokenStream->skipWhitespaces(TRUE);
		}
		return $this;
	}


	/**
	 * @return ReflectionParameter
	 */
	private function parseIsVariadic(StreamBase $tokenStream)
	{
		if (PHP_VERSION_ID >= 50600 && $tokenStream->is(T_ELLIPSIS)) {
			$this->isVariadic = TRUE;
			$tokenStream->skipWhitespaces(TRUE);
		}
		return $this;
	}


	/**
	 * Parses the constant name.
	 *
	 * @return ReflectionParameter
	 * @throws ParseException If the parameter name could not be determined.
	 */
	protected function parseName(StreamBase $tokenStream)
	{
		if ( ! $tokenStream->is(T_VARIABLE)) {
			throw new ParseException($this, $tokenStream, 'The parameter name could not be determined.', ParseException::UNEXPECTED_TOKEN);
		}
		$this->name = substr($tokenStream->getTokenValue(), 1);
		$tokenStream->skipWhitespaces(TRUE);
		return $this;
	}


	/**
	 * Parses the parameter default value.
	 *
	 * @return ReflectionParameter
	 * @throws ParseException If the default value could not be determined.
	 */
	private function parseDefaultValue(StreamBase $tokenStream)
	{
		if ($tokenStream->is('=')) {
			$tokenStream->skipWhitespaces(TRUE);
			$level = 0;
			while (NULL !== ($type = $tokenStream->getType())) {
				switch ($type) {
					case ')':
						if (0 === $level) {
							break 2;
						}
					case '}':
					case ']':
						$level--;
						break;
					case '(':
					case '{':
					case '[':
						$level++;
						break;
					case ',':
						if (0 === $level) {
							break 2;
						}
						break;
					default:
						break;
				}
				$this->defaultValueDefinition[] = $tokenStream->current();
				$tokenStream->next();
			}
			if (')' !== $type && ',' !== $type) {
				throw new ParseException($this, $tokenStream, 'The property default value is not terminated properly. Expected "," or ")".', ParseException::UNEXPECTED_TOKEN);
			}
		}
		return $this;
	}

}
