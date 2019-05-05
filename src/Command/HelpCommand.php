<?php

namespace Catalyst\Command;

class HelpCommand extends \Symfony\Component\Console\Command\HelpCommand
{
    public function configure()
    {
        parent::configure();

        $this
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command displays help for a given command:

  <info>%command.full_name% list</info>

You can also output the help in other formats by using the <comment>--format</comment> option:

  <info>%command.full_name% --format=xml list</info>

To display the list of available commands, please use the <info>list</info> command.
EOF
            )
        ;
    }
}
