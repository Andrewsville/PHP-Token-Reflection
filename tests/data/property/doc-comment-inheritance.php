<?php

class TokenReflection_Test_PropertyDocCommentInheritanceGrandParent
{
	/**
	 * Private1 short.
	 *
	 * Private1 long.
	 *
	 * @var mixed
	 */
	private $param1;

	/**
	 * Private2 short.
	 *
	 * Private2 long.
	 *
	 * @var mixed
	 */
	private $param2;

	private $param3;

	/**
	 * Private4 short.
	 *
	 * Private4 long.
	 *
	 * @var boolean
	 */
	private $param4 = false;
}

class TokenReflection_Test_PropertyDocCommentInheritanceParent extends TokenReflection_Test_PropertyDocCommentInheritanceGrandParent
{
	/**
	 * {@inheritdoc} Protected1 short.
	 *
	 * Protected1 long. {@inheritdoc}
	 *
	 * @var mixed
	 */
	protected $param1;

	protected $param2;

	/**
	 * Protected3 {@inheritdoc} short.
	 *
	 * Protected3 long.
	 *
	 * @var mixed
	 */
	protected $param3;

	/**
	 * Protected4 short.
	 */
	protected $param4;
}

class TokenReflection_Test_PropertyDocCommentInheritance extends TokenReflection_Test_PropertyDocCommentInheritanceParent
{
	public $param1;

	public $param2;

	/**
	 * Public3 {@inheritdoc}
	 *
	 * @var mixed
	 */
	public $param3;

	public $param4;
}