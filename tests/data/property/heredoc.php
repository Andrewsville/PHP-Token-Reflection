<?php

class TokenReflection_Test_PropertyHeredoc
{
	public $heredoc = <<<EOT
property value
EOT;

	public $nowdoc = <<<'EOT'
property value
EOT;
}