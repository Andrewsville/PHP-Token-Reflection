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
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kontakt@kukulich.cz>
 */

namespace TokenReflection;

use TokenReflection\Exception;
use ReflectionParameter as InternalReflectionParameter;

/**
 * Tokenized function/method parameter reflection.
 */
class ReflectionParameter extends ReflectionBase implements IReflectionParameter
{
	/**
	 * The parameter requires an array as its value.
	 *
	 * @var string
	 */
	CONST ARRAY_TYPE_HINT = 'array';

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
	private $defaultValueDefinition = array();

	/**
	 * Defines a type hint (class name or array) of parameter values.
	 *
	 * @var string
	 */
	private $typeHint;

	/**
	 * Defines a type hint (class name or array) of parameter values as it was defined.
	 *
	 * @var string
	 */
	private $originalTypeHint;

	/**
	 * Position of the parameter in the function/method.
	 *
	 * @var integer
	 */
	private $position;

	/**
	 * Determines if the parameter is optional.
	 *
	 * @var boolean
	 */
	private $isOptional;

	/**
	 * Determines if the value is passed by reference.
	 *
	 * @var boolean
	 */
	private $passedByReference = false;

	/**
	 * Returns the declaring class.
	 *
	 * @return \TokenReflection\ReflectionClass|null
	 */
	public function getDeclaringClass()
	{
		return null === $this->declaringClassName ? null : $this->getBroker()->getClass($this->declaringClassName);
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
	 * @return \TokenReflection\ReflectionFunctionBase
	 */
	public function getDeclaringFunction()
	{
		if (null !== $this->declaringClassName) {
			// Method parameter
			$class = $this->getBroker()->getClass($this->declaringClassName);
			if (null !== $class) {
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
	 * @throws \TokenReflection\Exception\Runtime If the property is not optional
	 * @throws \TokenReflection\Exception\Runtime If the property has no default value
	 */
	public function getDefaultValue()
	{
		if (!$this->isOptional()) {
			throw new Exception\Runtime(sprintf('Property "%s" is not optional.', $this->name), Exception\Runtime::UNSUPPORTED);
		}

		if (is_array($this->defaultValueDefinition)) {
			if (0 === count($this->defaultValueDefinition)) {
				throw new Exception\Runtime(sprintf('Property "%s" has no default value.', $this->name), Exception\Runtime::DOES_NOT_EXIST);
			}

			$this->defaultValue = Resolver::getValueDefinition($this->defaultValueDefinition, $this);
			$this->defaultValueDefinition = Resolver::getSourceCode($this->defaultValueDefinition);
		}

		return $this->defaultValue;
	}

	/**
	 * Returns the part of the source code defining the parameter default value.
	 *
	 * @return string
	 */
	public function getDefaultValueDefinition()
	{
		return is_array($this->defaultValueDefinition) ? Resolver::getSourceCode($this->defaultValueDefinition) : $this->defaultValueDefinition;
	}

	/**
	 * Retutns if a default value for the parameter is available.
	 *
	 * @return boolean
	 */
	public function isDefaultValueAvailable()
	{
		return null !== $this->getDefaultValueDefinition();
	}

	/**
	 * Returns the position within all parameters.
	 *
	 * @return integer
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 * Returns if the parameter expects an array.
	 *
	 * @return boolean
	 */
	public function isArray()
	{
		return $this->typeHint === self::ARRAY_TYPE_HINT;
	}

	/**
	 * Returns the original type hint as defined in the source code.
	 *
	 * @return string|null
	 */
	public function getOriginalTypeHint()
	{
		return !$this->isArray() ? ltrim($this->originalTypeHint, '\\') : null;
	}

	/**
	 * Returns reflection of the required class of the value.
	 *
	 * @return \TokenReflection\IReflectionClass|null
	 */
	public function getClass()
	{
		$name = $this->getClassName();
		if (null === $name) {
			return null;
		}

		return $this->getBroker()->getClass($name);
	}

	/**
	 * Returns the required class name of the value.
	 *
	 * @return string|null
	 * @throws \TokenReflection\Exception\Runtime If the type hint class FQN could not be determined
	 */
	public function getClassName()
	{
		if ($this->isArray()) {
			return null;
		}

		try {
			if (null === $this->typeHint && null !== $this->originalTypeHint) {
				if (null !== $this->declaringClassName) {
					$parent = $this->getDeclaringClass();
					if (null === $parent) {
						throw new Exception\Runtime(sprintf('Could not load class "%s" reflection.', $this->declaringClassName), Exception\Runtime::DOES_NOT_EXIST);
					}
				} else {
					$parent = $this->getDeclaringFunction();
					if (null === $parent || !$parent->isTokenized()) {
						throw new Exception\Runtime(sprintf('Could not load function "%s" reflection.', $this->declaringFunctionName), Exception\Runtime::DOES_NOT_EXIST);
					}
				}

				$lTypeHint = strtolower($this->originalTypeHint);
				if ('parent' === $lTypeHint || 'self' === $lTypeHint) {
					if (null === $this->declaringClassName) {
						throw new Exception\Runtime('Parameter type hint cannot be "self" nor "parent" when not a method.', Exception::UNSUPPORTED);
					}

					if ('parent' === $lTypeHint) {
						if ($parent->isInterface() || null === $parent->getParentClassName()) {
							throw new Exception\Runtime(sprintf('Class "%s" has no parent.', $this->declaringClassName), Exception::DOES_NOT_EXIST);
						}

						$this->typeHint = $parent->getParentClassName();
					} else {
						$this->typeHint = $this->declaringClassName;
					}
				} else {
					$this->typeHint= ltrim(Resolver::resolveClassFQN($this->originalTypeHint, $parent->getNamespaceAliases(), $parent->getNamespaceName()), '\\');
				}
			}

			return $this->typeHint;
		} catch (Exception\Runtime $e) {
			throw new Exception\Runtime('Could not determine the class type hint FQN.', 0, $e);
		}
	}

	/**
	 * Returns if the the parameter allows NULL.
	 *
	 * @return boolean
	 */
	public function allowsNull()
	{
		return !$this->isArray();
	}

	/**
	 * Returns if the parameter is optional.
	 *
	 * @return boolean
	 * @throws \TokenReflection\Exception\Runtime If it is not possible to determine if the parameter is optional
	 */
	public function isOptional()
	{
		try {
			if (null === $this->isOptional) {
				$function = $this->getDeclaringFunction();
				if (null === $function) {
					throw new Exception\Runtime(sprintf('Could not get the declaring function "%s" reflection.', $this->declaringFunctionName), Exception\Runtime::DOES_NOT_EXIST);
				}

				$this->isOptional = true;
				foreach (array_slice($function->getParameters(), $this->position) as $reflectionParameter) {
					if (!$reflectionParameter->isDefaultValueAvailable()) {
						$this->isOptional = false;
						break;
					}
				}
			}

			return $this->isOptional;
		} catch (Exception\Runtime $e) {
			throw new Exception\Runtime(sprintf('Could not determine if parameter "%s" is optional.', $this->name), 0, $e);
		}
	}

	/**
	 * Returns if the parameter value is passed by reference.
	 *
	 * @return boolean
	 */
	public function isPassedByReference()
	{
		return $this->passedByReference;
	}

	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString()
	{
		if ($this->getClass()) {
			$hint = $this->getClassName();
			if ($this->allowsNull()) {
				$hint .= ' or NULL';
			}
		} elseif ($this->isArray()) {
			$hint = 'array';
			if ($this->allowsNull()) {
				$hint .= ' or NULL';
			}
		} else {
			$hint = '';
		}

		if ($this->isDefaultValueAvailable()) {
			$default = ' = ';
			if (is_null($this->getDefaultValue())) {
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
			'Parameter #%d [ <%s> %s%s$%s%s ]',
			$this->getPosition(),
			$this->isOptional() ? 'optional' : 'required',
			$hint ? $hint . ' ' : '',
			$this->isPassedByReference() ? '&' : '',
			$this->getName(),
			$default
		);
	}

	/**
	 * Exports a reflected object.
	 *
	 * @param \TokenReflection\Broker $broker Broker instance
	 * @param string $function Function name
	 * @param string $parameter Parameter name
	 * @param boolean $return Return the export instead of outputting it
	 * @return string|null
	 * @throws \TokenReflection\Exception\Runtime If requested parameter doesn't exist
	 */
	public static function export(Broker $broker, $function, $parameter, $return = false)
	{
		$functionName = $function;
		$parameterName = $parameter;

		$function = $broker->getFunction($functionName);
		if (null === $function) {
			throw new Exception\Runtime(sprintf('Function %s() does not exist.', $functionName), Exception\Runtime::DOES_NOT_EXIST);
		}
		$parameter = $function->getParameter($parameterName);

		if ($return) {
			return $parameter->__toString();
		}

		echo $parameter->__toString();
	}

	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return $this->getDeclaringFunction()->getNamespaceAliases();
	}

	/**
	 * Processes the parent reflection object.
	 *
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 * @throws \TokenReflection\Exception\Parse If an invalid parent reflection object was provided
	 */
	protected function processParent(IReflection $parent)
	{
		if (!$parent instanceof ReflectionFunctionBase) {
			throw new Exception\Parse(sprintf('The parent object has to be an instance of TokenReflection\ReflectionFunctionBase, "%s" given.', get_class($parent)), Exception\Parse::INVALID_PARENT);
		}

		// Declaring function name
		$this->declaringFunctionName = $parent->getName();

		// Position
		$this->position = count($parent->getParameters());

		// Declaring class name
		if ($parent instanceof ReflectionMethod) {
			$this->declaringClassName = $parent->getDeclaringClassName();
		}

		return parent::processParent($parent);
	}

	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionParameter
	 */
	protected function parse(Stream $tokenStream, IReflection $parent)
	{
		return $this
			->parseTypeHint($tokenStream)
			->parsePassedByReference($tokenStream)
			->parseName($tokenStream)
			->parseDefaultValue($tokenStream);
	}

	/**
	 * Parses the type hint.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionParameter
	 * @throws \TokenReflection\Exception\Parse If the type hint class name could not be determined
	 */
	private function parseTypeHint(Stream $tokenStream)
	{
		try {
			$type = $tokenStream->getType();

			if (T_ARRAY === $type) {
				$this->typeHint = self::ARRAY_TYPE_HINT;
				$this->originalTypeHint = self::ARRAY_TYPE_HINT;
				$tokenStream->skipWhitespaces();
			} elseif (T_STRING === $type || T_NS_SEPARATOR === $type) {
				$className = '';
				do {
					$className .= $tokenStream->getTokenValue();

					$tokenStream->skipWhitespaces();
					$type = $tokenStream->getType();
				} while (T_STRING === $type || T_NS_SEPARATOR === $type);

				if ('' === ltrim($className, '\\')) {
					throw new Exception\Parse(sprintf('Invalid class name definition: "%s".', $className), Exception\Parse::PARSE_ELEMENT_ERROR);
				}

				$this->originalTypeHint = $className;
			}

			return $this;
		} catch (Exception\Parse $e) {
			throw new Exception\Parse('Could not parse the value constaint class name.', 0, $e);
		}
	}

	/**
	 * Parses if parameter value is passed by reference.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionParameter
	 */
	private function parsePassedByReference(Stream $tokenStream)
	{
		if ($tokenStream->is('&')) {
			$this->passedByReference = true;
			$tokenStream->skipWhitespaces();
		}

		return $this;
	}

	/**
	 * Parses the constant name.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionParameter
	 * @throws \TokenReflection\Exception\Parse If the parameter name could not be determined
	 * @throws \TokenReflection\Exception\Parse If the parameter name could not be determined
	 */
	protected function parseName(Stream $tokenStream)
	{
		try {
			if (!$tokenStream->is(T_VARIABLE)) {
				throw new Exception\Parse('The parameter name could not be determined.', Exception\Parse::PARSE_ELEMENT_ERROR);
			}

			$this->name = substr($tokenStream->getTokenValue(), 1);

			$tokenStream->skipWhitespaces();

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse('Could not parse parameter name.', Exception\Parse::PARSE_ELEMENT_ERROR, $e);
		}
	}

	/**
	 * Parses the parameter default value.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionParameter
	 * @throws \TokenReflection\Exception\Parse If the default value could not be determined
	 */
	private function parseDefaultValue(Stream $tokenStream)
	{
		try {
			if ($tokenStream->is('=')) {
				$tokenStream->skipWhitespaces();

				$level = 0;
				while (null !== ($type = $tokenStream->getType())) {
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
					throw new Exception\Parse(sprintf('The property default value is not terminated properly. Expected "," or ")", "%s" found.', $tokenStream->getTokenName()), Exception\Parse::PARSE_ELEMENT_ERROR);
				}
			}

			return $this;
		} catch (Exception\Parse $e) {
			throw new Exception\Parse('Could not parse the default value.', 0, $e);
		}
	}
}
