<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen\TokenReflection\Behaviors\AnnotationsInterface;
use ApiGen\TokenReflection\Broker\StorageInterface;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\Reflection\ReflectionNamespace;
use Reflector;


class ReflectionConstant implements ReflectionInterface, ReflectionConstantInterface, AnnotationsInterface
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $declaringClassName;

	/**
	 * @var string
	 */
	private $namespaceName;

	/**
	 * @var mixed
	 */
	private $value;

	/**
	 * @var bool
	 */
	private $userDefined;

	/**
	 * @var StorageInterface
	 */
	private $storage;


	/**
	 * @param string $name
	 * @param mixed $value
	 * @param StorageInterface $storage
	 * @param ReflectionClass $parent
	 * @throws RuntimeException If real parent class could not be determined.
	 */
	public function __construct($name, $value, StorageInterface $storage, ReflectionClass $parent = NULL)
	{
		$this->name = $name;
		$this->value = $value;
		$this->storage = $storage;
		if ($parent !== NULL) {
			$realParent = NULL;
			if (array_key_exists($name, $parent->getOwnConstants())) {
				$realParent = $parent;
			}
			if ($realParent === NULL) {
				foreach ($parent->getParentClasses() as $grandParent) {
					if (array_key_exists($name, $grandParent->getOwnConstants())) {
						$realParent = $grandParent;
						break;
					}
				}
			}
			if ($realParent === NULL) {
				foreach ($parent->getInterfaces() as $interface) {
					if (array_key_exists($name, $interface->getOwnConstants())) {
						$realParent = $interface;
						break;
					}
				}
			}
			if ($realParent === NULL) {
				throw new RuntimeException('Could not determine constant real parent class.');
			}
			$this->declaringClassName = $realParent->getName();
			$this->userDefined = $realParent->isUserDefined();

		} else {
			if ( ! array_key_exists($name, get_defined_constants(FALSE))) {
				$this->userDefined = TRUE;

			} else {
				$declared = get_defined_constants(TRUE);
				$this->userDefined = array_key_exists($name, $declared['user']);
			}
		}
	}


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
	public function getShortName()
	{
		$name = $this->getName();
		if ($this->namespaceName !== NULL && $this->namespaceName !== ReflectionNamespace::NO_NAMESPACE_NAME) {
			$name = substr($name, strlen($this->namespaceName) + 1);
		}
		return $name;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClass()
	{
		if ($this->declaringClassName === NULL) {
			return NULL;
		}
		return $this->storage->getClass($this->declaringClassName);
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
	public function getNamespaceName()
	{
		return $this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME ? '' : (string) $this->namespaceName;
	}


	/**
	 * {@inheritdoc}
	 */
	public function inNamespace()
	{
		return $this->getNamespaceName() !== '';
	}


	/**
	 * {@inheritdoc}
	 */
	public function getExtension()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getExtensionName()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFileName()
	{
		return NULL;
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
		return $this->value;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getValueDefinition()
	{
		return var_export($this->value, TRUE);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isInternal()
	{
		return !$this->userDefined;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isUserDefined()
	{
		return $this->userDefined;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isTokenized()
	{
		return FALSE;
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
	public function getPrettyName()
	{
		return $this->declaringClassName === NULL ? $this->name : sprintf('%s::%s', $this->declaringClassName, $this->name);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getStorage()
	{
		return $this->storage;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceAliases()
	{
		return [];
	}

}
