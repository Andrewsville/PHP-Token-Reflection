<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Invalid;

use ApiGen\TokenReflection;
use ApiGen\TokenReflection\IReflectionConstant;
use ApiGen\TokenReflection\Broker;
use ApiGen\TokenReflection\ReflectionBase;


/**
 * Invalid constant reflection.
 *
 * The reflected constant is not unique.
 */
class ReflectionConstant extends ReflectionElement implements IReflectionConstant
{

	/**
	 * Constant name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Original definition file name.
	 *
	 * @var string
	 */
	private $fileName;

	/**
	 * Reflection broker.
	 *
	 * @var ApiGen\TokenReflection\Broker
	 */
	private $broker;


	/**
	 * Constructor.
	 *
	 * @param string $name Constant name
	 * @param string $fileName Original definiton file name
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker
	 */
	public function __construct($name, $fileName, Broker $broker)
	{
		$this->name = $name;
		$this->broker = $broker;
		$this->fileName = $fileName;
	}


	/**
	 * Returns the name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Returns the unqualified name (UQN).
	 *
	 * @return string
	 */
	public function getShortName()
	{
		$pos = strrpos($this->name, '\\');
		return FALSE === $pos ? $this->name : substr($this->name, $pos + 1);
	}


	/**
	 * Returns the declaring class reflection.
	 *
	 * @return null
	 */
	public function getDeclaringClass()
	{
		return NULL;
	}


	/**
	 * Returns the declaring class name.
	 *
	 * @return null
	 */
	public function getDeclaringClassName()
	{
		return NULL;
	}


	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	public function getNamespaceName()
	{
		$pos = strrpos($this->name, '\\');
		return FALSE === $pos ? '' : substr($this->name, 0, $pos);
	}


	/**
	 * Returns if the function/method is defined within a namespace.
	 *
	 * @return bool
	 */
	public function inNamespace()
	{
		return FALSE !== strpos($this->name, '\\');
	}


	/**
	 * Returns the PHP extension reflection.
	 *
	 * @return null
	 */
	public function getExtension()
	{
		return NULL;
	}


	/**
	 * Returns the PHP extension name.
	 *
	 * @return bool
	 */
	public function getExtensionName()
	{
		return FALSE;
	}


	/**
	 * Returns the appropriate source code part.
	 *
	 * @return string
	 */
	public function getSource()
	{
		return '';
	}


	/**
	 * Returns the start position in the file token stream.
	 *
	 * @return int
	 */
	public function getStartPosition()
	{
		return -1;
	}


	/**
	 * Returns the end position in the file token stream.
	 *
	 * @return int
	 */
	public function getEndPosition()
	{
		return -1;
	}


	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return null
	 */
	public function getFileName()
	{
		return $this->fileName;
	}


	/**
	 * Returns a file reflection.
	 *
	 * @return ApiGen\TokenReflection\ReflectionFile
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the file is not stored inside the broker
	 */
	public function getFileReflection()
	{
		throw new Exception\BrokerException($this->getBroker(), sprintf('Constant %s was not parsed from a file', $this->getPrettyName()), Exception\BrokerException::UNSUPPORTED);
	}


	/**
	 * Returns the definition start line number in the file.
	 *
	 * @return null
	 */
	public function getStartLine()
	{
		return NULL;
	}


	/**
	 * Returns the definition end line number in the file.
	 *
	 * @return null
	 */
	public function getEndLine()
	{
		return NULL;
	}


	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return bool
	 */
	public function getDocComment()
	{
		return FALSE;
	}


	/**
	 * Checks if there is a particular annotation.
	 *
	 * @param string $name Annotation name
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		return FALSE;
	}


	/**
	 * Returns a particular annotation value.
	 *
	 * @param string $name Annotation name
	 * @return null
	 */
	public function getAnnotation($name)
	{
		return NULL;
	}


	/**
	 * Returns parsed docblock.
	 *
	 * @return array
	 */
	public function getAnnotations()
	{
		return [];
	}


	/**
	 * Returns the constant value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return NULL;
	}


	/**
	 * Returns the part of the source code defining the constant value.
	 *
	 * @return string
	 */
	public function getValueDefinition()
	{
		return NULL;
	}


	/**
	 * Returns the originaly provided value definition.
	 *
	 * @return string
	 */
	public function getOriginalValueDefinition()
	{
		return NULL;
	}


	/**
	 * Returns if the constant is internal.
	 *
	 * @return bool
	 */
	public function isInternal()
	{
		return FALSE;
	}


	/**
	 * Returns if the constant is user defined.
	 *
	 * @return bool
	 */
	public function isUserDefined()
	{
		return TRUE;
	}


	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return bool
	 */
	public function isTokenized()
	{
		return TRUE;
	}


	/**
	 * Returns if the reflection subject is deprecated.
	 *
	 * @return bool
	 */
	public function isDeprecated()
	{
		return FALSE;
	}


	/**
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	public function getPrettyName()
	{
		return $this->name;
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
			gettype(NULL),
			$this->getName(),
			NULL
		);
	}


	/**
	 * Returns the reflection broker used by this reflection object.
	 *
	 * @return ApiGen\TokenReflection\Broker
	 */
	public function getBroker()
	{
		return $this->broker;
	}


	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return [];
	}


	/**
	 * Returns if the constant definition is valid.
	 *
	 * @return bool
	 */
	public function isValid()
	{
		return FALSE;
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
	 * @return bool
	 */
	final public function __isset($key)
	{
		return ReflectionBase::exists($this, $key);
	}

}
