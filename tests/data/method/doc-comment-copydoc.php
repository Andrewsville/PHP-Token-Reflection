<?php

class TokenReflection_Test_MethodDocCommentCopydoc
{
	/**
	 * This is a method.
	 */
	public function method()
	{

	}

	/**
	 * @copydoc method
	 */
	public function method2()
	{

	}

	/**
	 * @copydoc method()
	 */
	public function method3()
	{

	}

	/**
	 * @copydoc TokenReflection_Test_MethodDocCommentCopydoc::method
	 */
	public function method4()
	{

	}

	/**
	 * @copydoc TokenReflection_Test_MethodDocCommentCopydoc::method()
	 */
	public function method5()
	{

	}

	/**
	 * @copydoc nonexistentMethod()
	 */
	public function method6()
	{

	}

	/**
	 * @copydoc nonexistentClass::nonexistentMethod()
	 */
	public function method7()
	{

	}
}
