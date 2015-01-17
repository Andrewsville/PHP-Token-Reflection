<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Invalid;

use ApiGen\TokenReflection\Behaviors\Annotations;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception\BaseException;
use ApiGen\TokenReflection\Exception\BrokerException;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Reflection\ReflectionFile;


abstract class ReflectionElement implements Annotations
{

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * Original definition file name.
	 *
	 * @var string
	 */
	protected $fileName;

	/**
	 * @var Broker
	 */
	protected $broker;

	/**
	 * Reasons why this element's reflection is invalid.
	 *
	 * @var BaseException[]
	 */
	private $reasons = [];


	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceName()
	{
		$pos = strrpos($this->name, '\\');
		return $pos === FALSE ? '' : substr($this->name, 0, $pos);
	}


	/**
	 * {@inheritdoc}
	 */
	public function inNamespace()
	{
		return strrpos($this->name, '\\') !== FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getShortName()
	{
		$pos = strrpos($this->name, '\\');
		return $pos === FALSE ? $this->name : substr($this->name, $pos + 1);
	}


	/**
	 * @return $this
	 */
	public function addReason(BaseException $reason)
	{
		$this->reasons[] = $reason;
		return $this;
	}


	/**
	 * @return array
	 */
	public function getReasons()
	{
		return $this->reasons;
	}


	/**
	 * @return bool
	 */
	public function hasReasons()
	{
		return ! empty($this->reasons);
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
	 * @return ReflectionFile
	 * @throws RuntimeException If the file is not stored inside the broker
	 */
	public function getFileReflection()
	{
		throw new BrokerException(
			$this->getBroker(),
			sprintf('Constant %s was not parsed from a file', $this->getName()),
			BrokerException::UNSUPPORTED
		);
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
	public function isDeprecated()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isValid()
	{
		return FALSE;
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
	public function getFileName()
	{
		return $this->fileName;
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
	 * {@inheritdoc}
	 */
	public function getBroker()
	{
		return $this->broker;
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

}
