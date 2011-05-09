<?php
/**
 * PHP Token Reflection
 *
 * Development version
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

use ReflectionMethod as InternalReflectionMethod, ReflectionClass as InternalReflectionClass;
use RuntimeException, InvalidArgumentException;

/**
 * Tokenized class method reflection.
 */
class ReflectionMethod extends ReflectionFunctionBase implements IReflectionMethod
{
	/**
	 * An implemented abstract method.
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l114
	 * ZEND_ACC_IMPLICIT_PUBLIC
	 *
	 * @var integer
	 */
	const IS_IMPLEMENTED_ABSTRACT = 0x08;

	/**
	 * Access level of this method has changed from the original implementation.
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l134
	 * ZEND_ACC_CHANGED
	 *
	 * @var integer
	 */
	const ACCESS_LEVEL_CHANGED = 0x800;

	/**
	 * Method is constructor.
	 *
	 * Legacy constructors are not supported.
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l138
	 * ZEND_ACC_CTOR
	 *
	 * @var integer
	 */
	const IS_CONSTRUCTOR = 0x2000;

	/**
	 * Method is destructor.
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l139
	 * ZEND_ACC_DTOR
	 *
	 * @var integer
	 */
	const IS_DESTRUCTOR = 0x4000;

	/**
	 * Method is __clone().
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l140
	 * ZEND_ACC_CLONE
	 *
	 * @var integer
	 */
	const IS_CLONE = 0x8000;

	/**
	 * Method can be called statically (although not defined static).
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l143
	 * ZEND_ACC_ALLOW_STATIC
	 *
	 * @var integer
	 */
	const IS_ALLOWED_STATIC = 0x10000;

	/**
	 * Declaring class name.
	 *
	 * @var string
	 */
	private $declaringClassName;

	/**
	 * Determined if the method is accessible.
	 *
	 * @var boolean
	 */
	private $accessible = false;

	/**
	 * Returns the declaring class reflection.
	 *
	 * @return \TokenReflection\ReflectionClass
	 */
	public function getDeclaringClass()
	{
		return null === $this->declaringClassName ? null : $this->getBroker()->getClass($this->declaringClassName);
	}

	/**
	 * Returns the declaring class name.
	 *
	 * @return string
	 */
	public function getDeclaringClassName()
	{
		return $this->declaringClassName;
	}

	/**
	 * Returns the name of the declaring class.
	 *
	 * @return string
	 */
	public function getClass()
	{
		return $this->getDeclaringClassName();
	}

	/**
	 * Returns the docblock definition of the method or its parent.
	 *
	 * @return string|false
	 */
	public function getInheritedDocComment()
	{
		if (false !== ($docComment = $this->getDocComment()) && false === strpos($docComment, '@inheritdoc')) {
			return $docComment;
		}

		$parent = $this->getDeclaringClass()->getParentClass();
		if (null !== $parent && $parent->hasMethod($this->getName())) {
			return $parent->getMethod($this->getName())->getInheritedDocComment();
		}

		return false;
	}

	/**
	 * Returns method modifiers.
	 *
	 * @return integer
	 */
	public function getModifiers()
	{
		if (!($this->modifiers & (self::ACCESS_LEVEL_CHANGED | self::IS_IMPLEMENTED_ABSTRACT))) {
			$declaringClass = $this->getDeclaringClass();
			if (null === $declaringClass) {
				throw new RuntimeException('No declaring class defined.');
			}

			$parentClass = $declaringClass->getParentClass();
			if (null !== $parentClass) {
				$parentClassMethods = $parentClass->getMethods();
				// Access level changed
				if ($this->modifiers & InternalReflectionMethod::IS_PUBLIC) {
					if (isset($parentClassMethods[$this->name]) && ($parentClassMethods[$this->name]->getModifiers() & (self::ACCESS_LEVEL_CHANGED | InternalReflectionMethod::IS_PRIVATE))) {
						$this->modifiers |= self::ACCESS_LEVEL_CHANGED;
					}
				}

				// Implemented abstract
				if (isset($parentClassMethods[$this->name]) && ($parentClassMethods[$this->name]->getModifiers() & (self::IS_IMPLEMENTED_ABSTRACT | InternalReflectionMethod::IS_ABSTRACT))) {
					$this->modifiers |= self::IS_IMPLEMENTED_ABSTRACT;
				}
			}
		}

		return $this->modifiers;
	}

	/**
	 * Calls the method on an given instance.
	 *
	 * @param object $object Class instance
	 * @return mixed
	 */
	public function invoke($object, $args)
	{
		$params = func_get_args();
		return $this->invokeArgs(array_shift($params), $params);
	}

	/**
	 * Calls the method on an given object.
	 *
	 * @param object $object Class instance
	 * @param array $args Method parameter values
	 * @return mixed
	 */
	public function invokeArgs($object, array $args = array())
	{
		$declaringClass = $this->getDeclaringClass();
		if (!$declaringClass->isInstance($object)) {
			throw new InvalidArgumentException(sprintf('Invalid class, %s expected %s given', $declaringClass->getName(), get_class($object)));
		}

		if ($this->isPublic()) {
			return call_user_func_array(array($object, $this->getName()), $args);
		} elseif ($this->isAccessible()) {
			$refClass = new InternalReflectionClass($object);
			$refMethod = $refClass->getMethod($this->name);

			$refMethod->setAccessible(true);
			$value = $refMethod->invokeArgs($object, $args);
			$refMethod->setAccessible(false);

			return $value;
		}

		throw new RuntimeException('Only public methods can be invoked.');
	}

	/**
	 * Returns if the method is abstract.
	 *
	 * @return boolean
	 */
	public function isAbstract()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_ABSTRACT);
	}

	/**
	 * Returns if the method is a constructor.
	 *
	 * @return boolean
	 */
	public function isConstructor()
	{
		return (bool) ($this->modifiers & self::IS_CONSTRUCTOR);
	}

	/**
	 * Returns if the method is a destructor.
	 *
	 * @return boolean
	 */
	public function isDestructor()
	{
		return (bool) ($this->modifiers & self::IS_DESTRUCTOR);
	}

	/**
	 * Returns if the method is final.
	 *
	 * @return boolean
	 */
	public function isFinal()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_FINAL);
	}

	/**
	 * Returns if the method is private.
	 *
	 * @return boolean
	 */
	public function isPrivate()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_PRIVATE);
	}

	/**
	 * Returns if the method is protected.
	 *
	 * @return boolean
	 */
	public function isProtected()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_PROTECTED);
	}

	/**
	 * Returns if the method is public.
	 *
	 * @return boolean
	 */
	public function isPublic()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_PUBLIC);
	}

	/**
	 * Returns if the method is static.
	 *
	 * @return boolean
	 */
	public function isStatic()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_STATIC);
	}

	/**
	 * Sets a method to be accessible or not.
	 *
	 * @return boolean
	 */
	public function setAccessible($accessible)
	{
		$this->accessible = $accessible;
	}

	/**
	 * Returns if the property is set accessible.
	 *
	 * @return boolean
	 */
	public function isAccessible()
	{
		return $this->accessible;
	}

	/**
	 * Returns the method prototype.
	 *
	 * @return \TokenReflection\ReflectionMethod
	 */
	public function getPrototype()
	{
		$declaring = $this->getDeclaringClass();
		if ($declaring->getParentClassName()) {
			foreach ($declaring->getParentClass()->getMethods() as $method) {
				if ($method->getName() === $this->name && !$method->isPrivate()) {
					return $method;
				}
			}
		}

		foreach ($declaring->getInterfaces() as $interface) {
			if ($interface->hasMethod($this->name)) {
				return $interface->getMethod($this->name);
			}
		}

		throw new Exception('Method has no prototype', Exception::DOES_NOT_EXIST);
	}

	/**
	 * Processes the parent reflection object.
	 *
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 */
	protected function processParent(IReflection $parent)
	{
		if (!$parent instanceof ReflectionClass) {
			throw new RuntimeException('The parent object has to be either an instance of TokenReflection\ReflectionClass or NULL, %s given.', get_class($parent));
		}

		$this->declaringClassName = $parent->getName();
		return parent::processParent($parent);
	}

	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionMethod
	 */
	protected function parse(Stream $tokenStream, IReflection $parent)
	{
		return $this
			->parseBaseModifiers($tokenStream)
			->parseReturnsReference($tokenStream)
			->parseName($tokenStream)
			->parseInternalModifiers($parent);
	}

	/**
	 * Parses base method modifiers (abstract, final, public, ...).
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionMethod
	 */
	private function parseBaseModifiers(Stream $tokenStream)
	{
		while (true) {
			switch ($tokenStream->getType()) {
				case T_ABSTRACT:
					$this->modifiers |= InternalReflectionMethod::IS_ABSTRACT;
					break;
				case T_FINAL:
					$this->modifiers |= InternalReflectionMethod::IS_FINAL;
					break;
				case T_PUBLIC:
					$this->modifiers |= InternalReflectionMethod::IS_PUBLIC;
					break;
				case T_PRIVATE:
					$this->modifiers |= InternalReflectionMethod::IS_PRIVATE;
					break;
				case T_PROTECTED:
					$this->modifiers |= InternalReflectionMethod::IS_PROTECTED;
					break;
				case T_STATIC:
					$this->modifiers |= InternalReflectionMethod::IS_STATIC;
					break;
				case T_FUNCTION:
				case null:
					break 2;
			}

			$tokenStream->skipWhitespaces();
		}

		if (!($this->modifiers & (InternalReflectionMethod::IS_PRIVATE | InternalReflectionMethod::IS_PROTECTED))) {
			$this->modifiers |= InternalReflectionMethod::IS_PUBLIC;
		}

		return $this;
	}

	/**
	 * Parses internal PHP method modifiers (abstract, final, public, ...).
	 *
	 * @param \TokenReflection\ReflectionClass $class Parent class
	 * @return \TokenReflection\ReflectionMethod
	 */
	private function parseInternalModifiers(ReflectionClass $class)
	{
		$name = strtolower($this->name);
		if ('__construct' === $name || ($class && !$class->inNamespace() && strtolower($class->getShortName()) === $name)) {
			$this->modifiers |= self::IS_CONSTRUCTOR;
		} elseif ('__destruct' === $name) {
			$this->modifiers |= self::IS_DESTRUCTOR;
		} elseif ('__clone' === $name) {
			$this->modifiers |= self::IS_CLONE;
		}


		/**
		 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_API.c?revision=309853&view=markup#l1795
		 */
		static $notAllowed;
		if (null === $notAllowed) {
			$notAllowed = array_flip(array('__clone', '__tostring', '__get', '__set', '__isset', '__unset'));
		}
		if (!$this->isConstructor() && !$this->isDestructor() && !isset($notAllowed[$name])) {
			$this->modifiers |= self::IS_ALLOWED_STATIC;
		}

		return $this;
	}
}
