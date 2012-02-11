<?php

class TokenReflection_Test_ConstantDocCommentCopydoc
{
	/**
	 * This is a constant.
	 */
	const DOC_COMMENT = 'doc-comment';

	/**
	 * @copydoc DOC_COMMENT
	 */
	const DOC_COMMENT_COPY = 'doc-comment-copy';

	/**
	 * @copydoc TokenReflection_Test_ConstantDocCommentCopydoc::DOC_COMMENT
	 */
	const DOC_COMMENT_COPY2 = 'doc-comment-copy2';

	/**
	 * @copydoc TokenReflection_Test_ConstantDocCommentCopydoc2::DOC_COMMENT
	 */
	const DOC_COMMENT_COPY_CLASS = 'doc-comment-copy-class';

	/**
	 * @copydoc TokenReflection_Test_ConstantDocCommentCopydoc::NON_EXISTENT
	 */
	const DOC_COMMENT_COPY_NO = 'doc-comment-copy-no';
}

class TokenReflection_Test_ConstantDocCommentCopydoc2
{
	/**
	 * This is another constant.
	 */
	const DOC_COMMENT = 'doc-comment';
}

/**
 * Comment.
 */
const CONSTANT_DOC_COMMENT_COPYDOC = 1;

/**
 * @copydoc CONSTANT_DOC_COMMENT_COPYDOC
 */
const CONSTANT_DOC_COMMENT_COPYDOC2 = 2;

/**
 * @copydoc NONEXISTENT_CONSTANT
 */
const CONSTANT_DOC_COMMENT_COPYDOC3 = 2;