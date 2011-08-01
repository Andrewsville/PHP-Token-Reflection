<?php

class TokenReflection_Test_ParameterDocCommentInheritanceGrandParent
{
	/**
	 * Private method.
	 *
	 * @param mixed $one First
	 * @param boolean $two Second
	 * @param string $three Third
	 */
	private function m($one, $two, $three)
	{

	}
}

class TokenReflection_Test_ParameterDocCommentInheritanceParent extends TokenReflection_Test_ParameterDocCommentInheritanceGrandParent
{
	/**
	 * Protected method.
	 */
	protected function m($one, $two, $three)
	{

	}
}

class TokenReflection_Test_ParameterDocCommentInheritance extends TokenReflection_Test_ParameterDocCommentInheritanceParent
{
	/**
	 * Public method.
	 *
	 * @param mixed $one First of the public method.
	 */
	protected function m($one, $two, $three)
	{

	}
}