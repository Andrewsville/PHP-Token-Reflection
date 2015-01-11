<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Invalid;

use ApiGen\TokenReflection\Behaviors\Annotations;
use ApiGen\TokenReflection\Exception\BrokerException;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\IReflectionConstant;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\ReflectionBase;
use ApiGen\TokenReflection\ReflectionFile;


class ReflectionConstant extends ReflectionElement implements IReflectionConstant, Annotations
{

	/**
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
	 * @var Broker
	 */
	private $broker;


	/**
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getDeclaringClass()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClassName()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceName()
	{
		$pos = strrpos($this->name, '\\');
		return FALSE === $pos ? '' : substr($this->name, 0, $pos);
	}


	/**
	 * {@inheritdoc}
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
	 * @return ReflectionFile
	 * @throws RuntimeException If the file is not stored inside the broker
	 */
	public function getFileReflection()
	{
		throw new BrokerException($this->getBroker(), sprintf('Constant %s was not parsed from a file', $this->getPrettyName()), BrokerException::UNSUPPORTED);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getStartLine()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getEndLine()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDocComment()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasAnnotation($name)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getAnnotation($name)
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getAnnotations()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getValue()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function isInternal()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isUserDefined()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isTokenized()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	final public function __get($key)
	{
		return ReflectionBase::get($this, $key);
	}


	/**
	 * {@inheritdoc}
	 */
	final public function __isset($key)
	{
		return ReflectionBase::exists($this, $key);
	}

}
