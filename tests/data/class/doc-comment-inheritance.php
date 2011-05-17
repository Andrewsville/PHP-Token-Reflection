<?php

/**
 * Short description.
 *
 * Long description.
 *
 * @copyright Copyright (c) 2011
 * @author author
 * @see http://php.net
 */
class TokenReflection_Test_ClassDocCommentInheritanceParent
{
}

/**
 * My {@inheritdoc}
 *
 * {@inheritdoc} Phew, that was long.
 *
 * @author anotherauthor
 */
class TokenReflection_Test_ClassDocCommentInheritanceExplicit extends TokenReflection_Test_ClassDocCommentInheritanceParent
{
}

class TokenReflection_Test_ClassDocCommentInheritanceImplicit extends TokenReflection_Test_ClassDocCommentInheritanceParent
{
}