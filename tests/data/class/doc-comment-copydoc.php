<?php

/**
 * Short description.
 *
 * Long description.
 *
 * @copyright Copyright (c) 2011
 * @author Author
 * @see http://php.net
 */
class TokenReflection_Test_ClassDocCommentCopydocParent
{
}

/**
 * @copydoc TokenReflection_Test_ClassDocCommentCopydocParent
 */
class TokenReflection_Test_ClassDocCommentCopydocFound
{
}

/**
 * Whatever.
 *
 * @copydoc TokenReflection_Test_ClassDocCommentCopydocParent
 * @author Another author
 * @license None
 */
class TokenReflection_Test_ClassDocCommentCopydocOverwritten
{
}

/**
 * @copydoc TokenReflection_Test_ClassDocCommentCopydocNonexistent
 */
class TokenReflection_Test_ClassDocCommentCopydocNotFound
{
}

/**
 * @copydoc TokenReflection_Test_ClassDocCommentCopydocParent
 * @copydoc TokenReflection_Test_ClassDocCommentCopydocOverwritten
 */
class TokenReflection_Test_ClassDocCommentCopydocDouble
{
}

/**
 * @copydoc TokenReflection_Test_ClassDocCommentCopydocDouble
 */
class TokenReflection_Test_ClassDocCommentCopydocRecursive
{
}

/**
 * @copydoc TokenReflection_Test_ClassDocCommentCopydocCircle12
 */
class TokenReflection_Test_ClassDocCommentCopydocCircle11
{
}

/**
 * @copydoc TokenReflection_Test_ClassDocCommentCopydocCircle11
 */
class TokenReflection_Test_ClassDocCommentCopydocCircle12
{
}

/**
 * @copydoc TokenReflection_Test_ClassDocCommentCopydocCircle22
 */
class TokenReflection_Test_ClassDocCommentCopydocCircle21
{
}

/**
 * @copydoc TokenReflection_Test_ClassDocCommentCopydocCircle23
 */
class TokenReflection_Test_ClassDocCommentCopydocCircle22
{
}

/**
 * @copydoc TokenReflection_Test_ClassDocCommentCopydocCircle21
 */
class TokenReflection_Test_ClassDocCommentCopydocCircle23
{
}

/**
 * @copydoc TokenReflection_Test_ClassDocCommentCopydocCircleSelf
 */
class TokenReflection_Test_ClassDocCommentCopydocCircleSelf
{
}