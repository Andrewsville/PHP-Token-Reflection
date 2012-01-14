<?php

class TokenReflection_Test_ConstantDocComment
{
	/**
	 * This is a constant.
	 */
	const DOC_COMMENT = 'doc-comment';

	const
		/** This is the first constant */
		FIRST_CONSTANT = 'value1',

		/** And this is the second constant. */
		SECOND_CONSTANT = 'value2';
}