<?php

class TokenReflection_Test_MethodDeclaringClassParent
{
	public function parent()
	{
	}

	protected function parentOverlay()
	{
	}
}

class TokenReflection_Test_MethodDeclaringClass extends TokenReflection_Test_MethodDeclaringClassParent
{
	protected function parentOverlay()
	{
	}

	public function child()
	{
	}
}