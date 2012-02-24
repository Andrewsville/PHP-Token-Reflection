<?php

class TokenReflection_Test_ParameterValueDefinitions
{
	private function method(
		$parameter1 = true,
		$parameter2 = true ,
		$parameter3 = true /** foo */ ,
		$parameter4 = /** foo */ true,
		$parameter5 /** foo */ = true,
		$parameter6 /** foo */ = true /** foo */ ,
		$parameter7 /** foo */ = /** foo */ true /** foo */ ,
		/** foo */ $parameter8 = true
	) {

	}
}