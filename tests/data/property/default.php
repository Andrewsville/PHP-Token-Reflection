<?php

class TokenReflection_Test_PropertyDefault
{
	const DEFAULT_VALUE = 'default';

	public $default = 'default';

	public $default2 = self::DEFAULT_VALUE;

	public $default3 = TokenReflection_Test_PropertyDefault::DEFAULT_VALUE;
}

class TokenReflection_Test_PropertyDefault2 extends TokenReflection_Test_PropertyDefault
{
	const DEFAULT_VALUE = 'not default';

	const PARENT_DEFAULT_VALUE = parent::DEFAULT_VALUE;

	public $default4 = self::DEFAULT_VALUE;

	public $default5 = TokenReflection_Test_PropertyDefault2::DEFAULT_VALUE;

	public $default6 = parent::DEFAULT_VALUE;

	public $default7 = TokenReflection_Test_PropertyDefault::DEFAULT_VALUE;

	public $default8 = self::PARENT_DEFAULT_VALUE;

	public $default9 = array(self::DEFAULT_VALUE, parent::DEFAULT_VALUE, self::PARENT_DEFAULT_VALUE);
}
