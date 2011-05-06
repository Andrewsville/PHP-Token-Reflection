<?php

class TokenReflection_Test_MethodInvoke
{
	public function publicInvoke($param, $param2)
	{
		return $param + $param2;
	}

	protected function protectedInvoke($param, $param2)
	{
		return $param + $param2;
	}
}