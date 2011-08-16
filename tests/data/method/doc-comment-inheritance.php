<?php

class TokenReflection_Test_MethodDocCommentInheritanceGrandParent
{
	/**
	 * Private1 short.
	 *
	 * Private1 long.
	 *
	 * @return integer
	 * @throws Exception
	 */
	private function method1()
	{
	}

	/**
	 * Private2 short.
	 *
	 * Private2 long.
	 *
	 * @return mixed
	 */
	private function method2()
	{
	}

	private function method3()
	{
	}

	private function method4()
	{
	}
}

class TokenReflection_Test_MethodDocCommentInheritanceParent extends TokenReflection_Test_MethodDocCommentInheritanceGrandParent
{
	/**
	 * {@inheritdoc} Protected1 short.
	 *
	 * Protected1 long. {@inheritdoc}
	 *
	 * @return string
	 */
	protected function method1()
	{
	}

	protected function method2()
	{
	}

	/**
	 * Protected3 {@inheritdoc} short.
	 *
	 * Protected3 long.
	 *
	 * @return boolean
	 */
	protected function method3()
	{
	}

	protected function method4()
	{
	}
}

class TokenReflection_Test_MethodDocCommentInheritance extends TokenReflection_Test_MethodDocCommentInheritanceParent
{
	public function method1()
	{
	}

	public function method2()
	{
	}

	/**
	 * Public3 {@inheritdoc}
	 */
	public function method3()
	{
	}

	public function method4()
	{
	}
}