<?php

class TokenReflection_Test_ConstantHeredoc
{
	const HEREDOC = <<<EOT
constant value
EOT;

	const NOWDOC = <<<'EOT'
constant value
EOT;
}