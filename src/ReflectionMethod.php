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
use ReflectionClass as InternalReflectionClass;
use ReflectionMethod as InternalReflectionMethod;


class ReflectionMethod extends ReflectionFunctionBase implements IReflectionMethod
{

	/**
	 * An implemented abstract method.
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l114
	 * ZEND_ACC_IMPLICIT_PUBLIC
	 *
	 * @var int
	 */
	const IS_IMPLEMENTED_ABSTRACT = 0x08;

	/**
	 * Access level of this method has changed from the original implementation.
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l134
	 * ZEND_ACC_CHANGED
	 *
	 * @var int
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
	 * @var int
	 */
	const IS_CONSTRUCTOR = 0x2000;

	/**
	 * Method is destructor.
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l139
	 * ZEND_ACC_DTOR
	 *
	 * @var int
	 */
	const IS_DESTRUCTOR = 0x4000;

	/**
	 * Method is __clone().
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l140
	 * ZEND_ACC_CLONE
	 *
	 * @var int
	 */
	const IS_CLONE = 0x8000;

	/**
	 * Method can be called statically (although not defined static).
	 *
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
	 * @var IReflectionMethod
	 */
	private $prototype;

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
	 * @var IReflectionMethod|NULL
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


	/**
	 * Returns the declaring class reflection.
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
	 * Returns method modifiers.
	 *
	 * @return int
	 */
	public function getModifiers()
	{
		if ( ! $this->modifiersComplete && !($this->modifiers & (self::ACCESS_LEVEL_CHANGED | self::IS_IMPLEMENTED_ABSTRACT))) {
			$declaringClass = $this->getDeclaringClass();
			$parentClass = $declaringClass->getParentClass();
			if (FALSE !== $parentClass && $parentClass->hasMethod($this->name)) {
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
	 * Returns if the method is abstract.
	 *
	 * @return bool
	 */
	public function isAbstract()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_ABSTRACT);
	}


	/**
	 * Returns if the method is final.
	 *
	 * @return bool
	 */
	public function isFinal()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_FINAL);
	}


	/**
	 * Returns if the method is private.
	 *
	 * @return bool
	 */
	public function isPrivate()
	{
		return (bool) ($this->modifiers & InternalReflectionMethod::IS_PRIVATE);
	}


	/**
	 * Returns if the method is protected.
	 *
	 * @return bool
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
	 * Returns if the method is a constructor.
	 *
	 * @return bool
	 */
	public function isConstructor()
	{
		return (bool) ($this->modifiers & self::IS_CONSTRUCTOR);
	}


	/**
	 * Returns if the method is a destructor.
	 *
	 * @return bool
	 */
	public function isDestructor()
	{
		return (bool) ($this->modifiers & self::IS_DESTRUCTOR);
	}


	/**
	 * Returns the method prototype.
	 *
	 * @return ReflectionMethod
	 * @throws RuntimeException If the method has no prototype.
	 */
	public function getPrototype()
	{
		if (NULL === $this->prototype) {
			$prototype = NULL;
			$declaring = $this->getDeclaringClass();
			if (($parent = $declaring->getParentClass()) && $parent->hasMethod($this->name)) {
				$method = $parent->getMethod($this->name);
				if ( ! $method->isPrivate()) {
					try {
						$prototype = $method->getPrototype();
					} catch (RuntimeException $e) {
						$prototype = $method;
					}
				}
			}
			if (NULL === $prototype) {
				foreach ($declaring->getOwnInterfaces() as $interface) {
					if ($interface->hasMethod($this->name)) {
						$prototype = $interface->getMethod($this->name);
						break;
					}
				}
			}
			$this->prototype = $prototype ?: ($this->isComplete() ? FALSE : NULL);
		}
		if (empty($this->prototype)) {
			throw new RuntimeException('Method has no prototype.', RuntimeException::DOES_NOT_EXIST, $this);
		}
		return $this->prototype;
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
	public function __toString()
	{
		$internal = '';
		$overwrite = '';
		$prototype = '';
		$declaringClassParent = $this->getDeclaringClass()->getParentClass();
		try {
			$prototype = ', prototype ' . $this->getPrototype()->getDeclaringClassName();
		} catch (RuntimeException $e) {
			if ($declaringClassParent && $declaringClassParent->isInternal()) {
				$internal = 'internal:' . $declaringClassParent->getExtensionName();
			}
		}
		if ($declaringClassParent && $declaringClassParent->hasMethod($this->name)) {
			$parentMethod = $declaringClassParent->getMethod($this->name);
			$overwrite = ', overwrites ' . $parentMethod->getDeclaringClassName();
		}
		if ($this->isConstructor()) {
			$cdtor = ', ctor';
		} elseif ($this->isDestructor()) {
			$cdtor = ', dtor';
		} else {
			$cdtor = '';
		}
		$parameters = '';
		if ($this->getNumberOfParameters() > 0) {
			$buffer = '';
			foreach ($this->getParameters() as $parameter) {
				$buffer .= "\n    " . $parameter->__toString();
			}
			$parameters = sprintf(
				"\n\n  - Parameters [%d] {%s\n  }",
				$this->getNumberOfParameters(),
				$buffer
			);
		}
		// @todo support inherits
		return sprintf(
			"%sMethod [ <%s%s%s%s> %s%s%s%s%s%s method %s%s ] {\n  @@ %s %d - %d%s\n}\n",
			$this->getDocComment() ? $this->getDocComment() . "\n" : '',
			!empty($internal) ? $internal : 'user',
			$overwrite,
			$prototype,
			$cdtor,
			$this->isAbstract() ? 'abstract ' : '',
			$this->isFinal() ? 'final ' : '',
			$this->isStatic() ? 'static ' : '',
			$this->isPublic() ? 'public' : '',
			$this->isPrivate() ? 'private' : '',
			$this->isProtected() ? 'protected' : '',
			$this->returnsReference() ? '&' : '',
			$this->getName(),
			$this->getFileName(),
			$this->getStartLine(),
			$this->getEndLine(),
			$parameters
		);
	}


	/**
	 * Exports a reflected object.
	 *
	 * @param Broker $broker
	 * @param string|object $class Class name or class instance
	 * @param string $method Method name
	 * @param bool $return Return the export instead of outputting it
	 * @return string|NULL
	 * @throws RuntimeException If requested parameter doesn't exist.
	 */
	public static function export(Broker $broker, $class, $method, $return = FALSE)
	{
		$className = is_object($class) ? get_class($class) : $class;
		$methodName = $method;
		$class = $broker->getClass($className);
		if ($class instanceof Invalid\ReflectionClass) {
			throw new RuntimeException('Class is invalid.', RuntimeException::UNSUPPORTED);

		} elseif ($class instanceof Dummy\ReflectionClass) {
			throw new RuntimeException(sprintf('Class %s does not exist.', $className), RuntimeException::DOES_NOT_EXIST);
		}
		$method = $class->getMethod($methodName);
		if ($return) {
			return $method->__toString();
		}
		echo $method->__toString();
	}


	/**
	 * {@inheritdoc}
	 */
	public function invoke($object, $args)
	{
		$params = func_get_args();
		return $this->invokeArgs(array_shift($params), $params);
	}


	/**
	 * {@inheritdoc}
	 */
	public function invokeArgs($object, array $args = [])
	{
		$declaringClass = $this->getDeclaringClass();
		if ( ! $declaringClass->isInstance($object)) {
			throw new RuntimeException(sprintf('Expected instance of or subclass of "%s".', $this->declaringClassName), RuntimeException::INVALID_ARGUMENT, $this);
		}
		if ($this->isPublic()) {
			return call_user_func_array([$object, $this->getName()], $args);
		} elseif ($this->isAccessible()) {
			$refClass = new InternalReflectionClass($object);
			$refMethod = $refClass->getMethod($this->name);
			$refMethod->setAccessible(TRUE);
			$value = $refMethod->invokeArgs($object, $args);
			$refMethod->setAccessible(FALSE);
			return $value;
		}
		throw new RuntimeException('Only public methods can be invoked.', RuntimeException::NOT_ACCESSBILE, $this);
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
	 * {@inheritdoc}
	 */
	public function getClosure($object)
	{
		$declaringClass = $this->getDeclaringClass();
		if ( ! $declaringClass->isInstance($object)) {
			throw new RuntimeException(sprintf('Expected instance of or subclass of "%s".', $this->declaringClassName), RuntimeException::INVALID_ARGUMENT, $this);
		}
		$that = $this;
		return function () use ($object, $that) {
			return $that->invokeArgs($object, func_get_args());
		};
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
		if (NULL !== $name) {
			$method->originalName = $this->name;
			$method->name = $name;
		}
		if (NULL !== $accessLevel) {
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
		return NULL === $this->declaringTraitName ? NULL : $this->getBroker()->getClass($this->declaringTraitName);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringTraitName()
	{
		return $this->declaringTraitName;
	}


	/**
	 * Processes the parent reflection object.
	 *
	 * @return ReflectionElement
	 * @throws ParseException If an invalid parent reflection object was provided.
	 */
	protected function processParent(IReflection $parent, StreamBase $tokenStream)
	{
		if ( ! $parent instanceof ReflectionClass) {
			throw new ParseException($this, $tokenStream, 'The parent object has to be an instance of TokenReflection\ReflectionClass.', ParseException::INVALID_PARENT);
		}
		$this->declaringClassName = $parent->getName();
		if ($parent->isTrait()) {
			$this->declaringTraitName = $parent->getName();
		}
		return parent::processParent($parent, $tokenStream);
	}


	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @return ReflectionMethod
	 * @throws ParseException If the class could not be parsed.
	 */
	protected function parse(StreamBase $tokenStream, IReflection $parent)
	{
		return $this->parseBaseModifiers($tokenStream)
			->parseReturnsReference($tokenStream)
			->parseName($tokenStream)
			->parseInternalModifiers($parent);
	}


	/**
	 * Parses base method modifiers (abstract, final, public, ...).
	 *
	 * @return ReflectionMethod
	 */
	private function parseBaseModifiers(StreamBase $tokenStream)
	{
		while (TRUE) {
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
				case NULL:
					break 2;
				default:
					break;
			}
			$tokenStream->skipWhitespaces();
		}
		if ( ! ($this->modifiers & (InternalReflectionMethod::IS_PRIVATE | InternalReflectionMethod::IS_PROTECTED))) {
			$this->modifiers |= InternalReflectionMethod::IS_PUBLIC;
		}
		return $this;
	}


	/**
	 * Parses internal PHP method modifiers (abstract, final, public, ...).
	 *
	 * @return ReflectionMethod
	 */
	private function parseInternalModifiers(ReflectionClass $class)
	{
		$name = strtolower($this->name);
		if ('__construct' === $name || ( ! $class->inNamespace() && strtolower($class->getShortName()) === $name)) {
			$this->modifiers |= self::IS_CONSTRUCTOR;
		} elseif ('__destruct' === $name) {
			$this->modifiers |= self::IS_DESTRUCTOR;
		} elseif ('__clone' === $name) {
			$this->modifiers |= self::IS_CLONE;
		}
		if ($class->isInterface()) {
			$this->modifiers |= InternalReflectionMethod::IS_ABSTRACT;
		} else {
			// Can be called statically, see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_API.c?revision=309853&view=markup#l1795
			static $notAllowed = ['__clone' => TRUE, '__tostring' => TRUE, '__get' => TRUE, '__set' => TRUE, '__isset' => TRUE, '__unset' => TRUE];
			if ( ! $this->isStatic() && !$this->isConstructor() && !$this->isDestructor() && !isset($notAllowed[$name])) {
				$this->modifiers |= self::IS_ALLOWED_STATIC;
			}
		}
		return $this;
	}

}
