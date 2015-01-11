<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

use ApiGen;
use ApiGen\TokenReflection\Behaviors\Annotations;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Stream\StreamBase;


abstract class ReflectionBase implements IReflection, Annotations
{

	/**
	 * Class method cache.
	 *
	 * @var array
	 */
	private static $methodCache = [];

	/**
	 * Object name (FQN).
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Docblock definition.
	 *
	 * @var ApiGen\TokenReflection\ReflectionAnnotation|bool
	 */
	protected $docComment;

	/**
	 * @var Broker
	 */
	private $broker;


	public function __construct(StreamBase $tokenStream, Broker $broker, IReflection $parent = NULL)
	{
		$this->broker = $broker;
		$this->parseStream($tokenStream, $parent);
	}


	/**
	 * Parses the token substream.
	 */
	abstract protected function parseStream(StreamBase $tokenStream, IReflection $parent = NULL);


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
	public function getDocComment()
	{
		return $this->docComment->getDocComment();
	}


	/**
	 * Checks if there is a particular annotation.
	 *
	 * @param string $name Annotation name
	 * @return bool
	 */
	final public function hasAnnotation($name)
	{
		return $this->docComment->hasAnnotation($name);
	}


	/**
	 * Returns a particular annotation value.
	 *
	 * @param string $name Annotation name
	 * @return string|array|null
	 */
	final public function getAnnotation($name)
	{
		return $this->docComment->getAnnotation($name);
	}


	/**
	 * Returns all annotations.
	 *
	 * @return array
	 */
	final public function getAnnotations()
	{
		return $this->docComment->getAnnotations();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getBroker()
	{
		return $this->broker;
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
		return $this->hasAnnotation('deprecated');
	}


	/**
	 * @return string
	 */
	abstract public function getSource();


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return $this->name;
	}


	/**
	 * {@inheritdoc}
	 */
	final public function __get($key)
	{
		return self::get($this, $key);
	}


	/**
	 * {@inheritdoc}
	 */
	final public function __isset($key)
	{
		return self::exists($this, $key);
	}


	/**
	 * @param IReflection $object Reflection object
	 * @param string $key Variable name
	 * @return mixed
	 * @throws RuntimeException If the requested parameter does not exist.
	 */
	final public static function get(IReflection $object, $key)
	{
		if ( ! empty($key)) {
			$className = get_class($object);
			if ( ! isset(self::$methodCache[$className])) {
				self::$methodCache[$className] = array_flip(get_class_methods($className));
			}
			$methods = self::$methodCache[$className];
			$key2 = ucfirst($key);
			if (isset($methods['get' . $key2])) {
				return $object->{'get' . $key2}();
			} elseif (isset($methods['is' . $key2])) {
				return $object->{'is' . $key2}();
			}
		}
		throw new RuntimeException(sprintf('Cannot read property "%s".', $key), RuntimeException::DOES_NOT_EXIST);
	}


	/**
	 * @param IReflection $object Reflection object
	 * @param string $key Variable name
	 * @return bool
	 */
	final public static function exists(IReflection $object, $key)
	{
		try {
			self::get($object, $key);
			return TRUE;
		} catch (RuntimeException $e) {
			return FALSE;
		}
	}

}
