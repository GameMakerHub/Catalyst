<?php

namespace Catalyst\Command;

use Composer\Semver\Semver;
use Catalyst\Entity\CatalystEntity;
use Catalyst\Exception\UnresolveableDependenciesException;
use Catalyst\Service\PackageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class RunCommand extends Command
{
    protected static $defaultName = 'run';

    protected function configure()
    {
        $this
            ->setDescription('Run')
            ->setHelp('run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        shell_exec('subst Z: "%APPDATA%\GameMakerStudio2\Cache\GMS2CACHE"');
        shell_exec('subst Y: "%LOCALAPPDATA%\GameMakerStudio2\GMS2TEMP"');
        shell_exec('subst X: "%PROGRAMDATA%\GameMakerStudio2\Cache\runtimes\runtime-2.2.2.325"');

        $process = new Process([
            'C:\ProgramData/GameMakerStudio2/Cache/runtimes\runtime-2.2.2.325/bin/Igor.exe',
            '-j=8',
            '-options' => '%LOCALAPPDATA%\GameMakerStudio2\GMS2TEMP\build.bff',
            '-- Windows Run'
        ]);

        try {
            $process->mustRun();

            echo $process->getOutput();
        } catch (ProcessFailedException $exception) {
            echo $exception->getMessage();
        }

        shell_exec('subst Z: /D');
        shell_exec('subst Y: /D');
        shell_exec('subst X: /D');

        return 0;
    }
}