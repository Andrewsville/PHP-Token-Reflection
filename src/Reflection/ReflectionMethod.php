<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection;

use ApiGen\TokenReflection\Storage\StorageInterface;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\ReflectionMethodInterface;
use ApiGen\TokenReflection\Parser\MethodParser;
use ApiGen\TokenReflection\Stream\StreamBase;
use ReflectionMethod as InternalReflectionMethod;


class ReflectionMethod extends ReflectionFunctionBase implements ReflectionMethodInterface
{

	/**
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l114
	 * ZEND_ACC_IMPLICIT_PUBLIC
	 *
	 * @var int
	 */
	const IS_IMPLEMENTED_ABSTRACT = 0x08;

	/**
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l134
	 * ZEND_ACC_CHANGED
	 *
	 * @var int
	 */
	const ACCESS_LEVEL_CHANGED = 0x800;

	/**
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l138
	 * ZEND_ACC_CTOR
	 *
	 * @var int
	 */
	const IS_CONSTRUCTOR = 0x2000;

	/**
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l139
	 * ZEND_ACC_DTOR
	 *
	 * @var int
	 */
	const IS_DESTRUCTOR = 0x4000;

	/**
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l140
	 * ZEND_ACC_CLONE
	 *
	 * @var int
	 */
	const IS_CLONE = 0x8000;

	/**
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l143
	 * ZEND_ACC_ALLOW_STATIC
	 *
	 * @var int
	 */
	const IS_ALLOWED_STATIC = 0x10000;

	/**
	 * @var string
	 */
	private $declaringClassName;

	/**
	 * @var int
	 */
	protected $modifiers = 0;

	/**
	 * @var bool
	 */
	private $accessible = FALSE;

	/**
	 * @var bool
	 */
	private $modifiersComplete = FALSE;

	/**
	 * The original name when importing from a trait.
	 *
	 * @var string|NULL
	 */
	private $originalName = NULL;

	/**
	 * The original method when importing from a trait.
	 *
	 * @var ReflectionMethodInterface|NULL
	 */
	private $original = NULL;

	/**
	 * The original modifiers value when importing from a trait.
	 *
	 * @var int|NULL
	 */
	private $originalModifiers = NULL;

	/**
	 * @var string
	 */
	private $declaringTraitName;


	public function __construct(StreamBase $tokenStream, StorageInterface $storage, ReflectionInterface $parent = NULL)
	{
		$this->methodParser = new MethodParser($tokenStream, $this, $parent);
		parent::__construct($tokenStream, $storage, $parent);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClass()
	{
		return $this->declaringClassName === NULL ? NULL : $this->storage->getClass($this->declaringClassName);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClassName()
	{
		return $this->declaringClassName;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getModifiers()
	{
		if ( ! $this->modifiersComplete && !($this->modifiers & (self::ACCESS_LEVEL_CHANGED | self::IS_IMPLEMENTED_ABSTRACT))) {
			$declaringClass = $this->getDeclaringClass();
			$parentClass = $declaringClass->getParentClass();
			if ($parentClass !== FALSE && $parentClass->hasMethod($this->name)) {
				$parentClassMethod = $parentClass->getMethod($this->name);
				// Access level changed
				if (($this->isPublic() || $this->isProtected()) && $parentClassMethod->is(self::ACCESS_LEVEL_CHANGED | InternalReflectionMethod::IS_PRIVATE)) {
					$this->modifiers |= self::ACCESS_LEVEL_CHANGED;
				}
				// Implemented abstract
				if ($parentClassMethod->isAbstract() && !$this->isAbstract()) {
					$this->modifiers |= self::IS_IMPLEMENTED_ABSTRACT;
				}

			} else {
				// Check if it is an implementation of an interface method
				foreach ($declaringClass->getInterfaces() as $interface) {
					if ($interface->hasOwnMethod($this->name)) {
						$this->modifiers |= self::IS_IMPLEMENTED_ABSTRACT;
						break;
					}
				}
			}
			// Set if modifiers definition is complete
			$this->modifiersComplete = $this->isComplete() || (($this->modifiers & self::IS_IMPLEMENTED_ABSTRACT) && ($this->modifiers & self::ACCESS_LEVEL_CHANGED));
		}
		return $this->modifiers;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isAbstract()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_ABSTRACT);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isFinal()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_FINAL);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isPrivate()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_PRIVATE);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isProtected()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_PROTECTED);
	}


	/**
	 * Returns if the method is public.
	 *
	 * @return bool
	 */
	public function isPublic()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_PUBLIC);
	}


	/**
	 * Returns if the method is static.
	 *
	 * @return bool
	 */
	public function isStatic()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_STATIC);
	}


	/**
	 * Shortcut for isPublic(), ... methods that allows or-ed modifiers.
	 *
	 * The {@see getModifiers()} method is called only when really necessary making this
	 * a more efficient way of doing
	 * <code>
	 *     if ($method->getModifiers() & $filter) {
	 *        ...
	 *     }
	 * </code>
	 *
	 * @param int $filter Filter
	 * @return bool
	 */
	public function is($filter = NULL)
	{
		// See self::ACCESS_LEVEL_CHANGED | self::IS_IMPLEMENTED_ABSTRACT
		static $computedModifiers = 0x808;
		if (NULL === $filter || ($this->modifiers & $filter)) {
			return TRUE;
		} elseif (($filter & $computedModifiers) && !$this->modifiersComplete) {
			return (bool) ($this->getModifiers() & $filter);
		}
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isConstructor()
	{
		return (bool) ($this->modifiers & self::IS_CONSTRUCTOR);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isDestructor()
	{
		return (bool) ($this->modifiers & self::IS_DESTRUCTOR);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return sprintf('%s::%s', $this->declaringClassName ?: $this->declaringTraitName, parent::getPrettyName());
	}


	/**
	 * {@inheritdoc}
	 */
	public function isAccessible()
	{
		return $this->accessible;
	}


	/**
	 * {@inheritdoc}
	 */
	public function setAccessible($accessible)
	{
		$this->accessible = (bool) $accessible;
	}


	/**
	 * Returns if the definition is complete.
	 *
	 * Technically returns if the declaring class definition is complete.
	 *
	 * @return bool
	 */
	private function isComplete()
	{
		return $this->getDeclaringClass()->isComplete();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceAliases()
	{
		return $this->getDeclaringClass()->getNamespaceAliases();
	}


	/**
	 * Creates a method alias of the given name and access level for the given class.
	 *
	 * @param ReflectionClass $parent New parent class
	 * @param string $name New method name
	 * @param int $accessLevel New access level
	 * @return ReflectionMethod
	 * @throws RuntimeException If an invalid method access level was found.
	 */
	public function alias(ReflectionClass $parent, $name = NULL, $accessLevel = NULL)
	{
		static $possibleLevels = [InternalReflectionMethod::IS_PUBLIC => TRUE, InternalReflectionMethod::IS_PROTECTED => TRUE, InternalReflectionMethod::IS_PRIVATE => TRUE];
		$method = clone $this;
		$method->declaringClassName = $parent->getName();
		if ($name !== NULL) {
			$method->originalName = $this->name;
			$method->name = $name;
		}

		if ($accessLevel !== NULL) {
			if ( ! isset($possibleLevels[$accessLevel])) {
				throw new RuntimeException(sprintf('Invalid method access level: "%s".', $accessLevel), RuntimeException::INVALID_ARGUMENT, $this);
			}
			$method->modifiers &= ~(InternalReflectionMethod::IS_PUBLIC | InternalReflectionMethod::IS_PROTECTED | InternalReflectionMethod::IS_PRIVATE);
			$method->modifiers |= $accessLevel;
			$method->originalModifiers = $this->getModifiers();
		}
		foreach ($this->parameters as $parameterName => $parameter) {
			$method->parameters[$parameterName] = $parameter->alias($method);
		}
		return $method;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOriginalName()
	{
		return $this->originalName;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOriginal()
	{
		return $this->original;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOriginalModifiers()
	{
		return $this->originalModifiers;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringTrait()
	{
		return $this->declaringTraitName === NULL ? NULL : $this->storage->getClass($this->declaringTraitName);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringTraitName()
	{
		return $this->declaringTraitName;
	}


	protected function parse(StreamBase $tokenStream, ReflectionClass $parent)
	{
		$this->declaringClassName = $parent->getName();
		if ($parent->isTrait()) {
			$this->declaringTraitName = $parent->getName();
		}

		$this->modifiers = $this->methodParser->parseBaseModifiers();
		$this->returnsReference = $this->methodParser->parseReturnReference();
		$this->name = $this->methodParser->parseName();
		$this->modifiers = $this->methodParser->parseInternalModifiers($this->modifiers);

		$this->parseParameters($tokenStream);
		$this->parseStaticVariables($tokenStream);
	}

}
