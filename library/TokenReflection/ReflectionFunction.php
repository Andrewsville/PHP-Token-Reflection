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

use RuntimeException;

/**
 * Tokenized function reflection.
 */
class ReflectionFunction extends ReflectionFunctionBase implements IReflectionFunction
{
	/**
	 * Imported namespace/class aliases.
	 *
	 * @var array
	 */
	private $aliases = array();

	/**
	 * Returns if the method is is disabled via the disable_functions directive.
	 *
	 * @return boolean
	 */
	public function isDisabled()
	{
		return $this->hasAnnotation('disabled');
	}

	/**
	 * Returns the docblock definition of the function.
	 *
	 * @return string|false
	 */
	public function getInheritedDocComment()
	{
		return $this->getDocComment();
	}

	/**
	 * Calls the function.
	 *
	 * @return mixed
	 */
	public function invoke()
	{
		return $this->invokeArgs(func_get_args());
	}

	/**
	 * Calls the function.
	 *
	 * @param mixed $args Function parameter values
	 * @return mixed
	 */
	public function invokeArgs(array $args = array())
	{
		if (!function_exists($this->getName())) {
			throw new RuntimeException('Function %s is not defined in the current scope.', $this->getName());
		}

		return call_user_func_array($this->getName(), $args);
	}

	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionFunction
	 */
	protected function parse(Stream $tokenStream, IReflection $parent)
	{
		return $this
			->parseReturnsReference($tokenStream)
			->parseName($tokenStream);
	}

	/**
	 * Processes the parent reflection object.
	 *
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 */
	protected function processParent(IReflection $parent)
	{
		if (!$parent instanceof ReflectionFileNamespace) {
			throw new RuntimeException(sprintf('The parent object has to be an instance of TokenReflection\ReflectionFileNamespace, %s given.', get_class($parent)));
		}

		$this->namespaceName = $parent->getName();
		$this->aliases = $parent->getAliases();
		return parent::processParent($parent);
	}

	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return $this->aliases;
	}
}
