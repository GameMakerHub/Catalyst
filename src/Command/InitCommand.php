<?php

namespace Catalyst\Command;

use Assert\Assertion;
use Catalyst\Entity\CatalystEntity;
use Catalyst\Service\CatalystService;
use Catalyst\Service\StorageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class InitCommand extends Command
{
    protected static $defaultName = 'init';

    /** @var CatalystService */
    private $catalystService;

    public function __construct(CatalystService $catalystService)
    {
        $this->catalystService = $catalystService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Initialize a project.')
            ->setHelp('Interactive wizard to setup a project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->catalystService->existsHere()) {
            $output->writeln('<info>A Catalyst file is already present in this directory.</info>');
            return;
        }

        // Ask for package name
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new Question('Please enter the name of your package, in "vendor/package" format: ', '');
        $question->setValidator(function ($answer) {
            try {
                Assertion::regex($answer, '~^[a-z0-9\-]+\/[a-z0-9\-]+$~');
            } catch (\InvalidArgumentException $e) {
                throw new \RuntimeException(
                    'Package name must be "vendor/package" in lowercase, and only allows a-z, 0-9 and -'
                );
            }
            return $answer;
        });
        $name = $helper->ask($input, $output, $question);

        // Description
        $question = new Question('Optionally enter the description for your package (max 255): ', '');
        $question->setValidator(function ($answer) {
            try {
                Assertion::maxLength($answer, 255);
            } catch (\InvalidArgumentException $e) {
                throw new \RuntimeException(
                    'Description must be under 255 characters.'
                );
            }
            return $answer;
        });
        $description = $helper->ask($input, $output, $question);

        // License
        $question = new ChoiceQuestion(
            'Please select the license',
            CatalystEntity::ALLOWED_LICENSES
        );
        $question->setErrorMessage('License %s is invalid.');

        $license = $helper->ask($input, $output, $question);

        // Homepage
        try {
            Assertion::file('./.git/config');
            preg_match('~\[remote[.\s\S]*url = git@(.*)\.git~', file_get_contents('./.git/config'), $matches);
            \Assert\Assertion::count($matches, 2);
            $madeUrl = 'https://' . str_replace(':', '/', $matches[1]);
            \Assert\Assertion::url($madeUrl);
        } catch (\Exception $e) {
            $madeUrl = null;
        }
        $question = new Question('Please enter URL of your repository: ['.$madeUrl.'] ', $madeUrl);
        $question->setValidator(function ($answer) {
            Assertion::url($answer);
            return $answer;
        });

        $homepage = $helper->ask($input, $output, $question);

        // YYP File
        try {
            $filename = glob("*.yyp")[0];
            \Assert\Assertion::file($filename);
        } catch (\Exception $e) {
            $filename = null;
        }
        $question = new Question('Please enter YYP file name: ['.$filename.']', $filename);
        $question->setValidator(function ($answer) {
            Assertion::file($answer);
            return $answer;
        });

        $yyp = $helper->ask($input, $output, $question);

        $this->catalystService->createNew(
            '.',
            $name,
            $description,
            $license,
            $homepage,
            $yyp
        );

        $this->catalystService->persist();

        $output->writeln('Catalyst file initialized!');
    }
}
