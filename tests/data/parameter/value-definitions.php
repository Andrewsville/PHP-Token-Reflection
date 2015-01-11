<?php

class TokenReflection_Test_ParameterValueDefinitions
{

	private function method(
		$parameter1 = TRUE,
		$parameter2 = TRUE,
		$parameter3 = TRUE/** foo */,
		$parameter4 = /** foo */
		TRUE,
		$parameter5 /** foo */ = TRUE,
		$parameter6 /** foo */ = TRUE/** foo */,
		$parameter7 /** foo */ = /** foo */
		TRUE/** foo */,
		/** foo */
		$parameter8 = TRUE
	)
	{

	}
}
