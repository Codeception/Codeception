<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\InitTemplate;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function class_exists;
use function ucfirst;

class Init extends Command
{
    protected function configure(): void
    {
        $this->setDefinition(
            [
                new InputArgument('template', InputArgument::REQUIRED, 'Init template for the setup'),
                new InputOption('path', null, InputOption::VALUE_REQUIRED, 'Change current directory', null),
                new InputOption('namespace', null, InputOption::VALUE_OPTIONAL, 'Namespace to add for actor classes and helpers', null),

            ]
        );
    }

    public function getDescription(): string
    {
        return "Creates test suites by a template";
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $template = (string)$input->getArgument('template');

        if (class_exists($template)) {
            $className = $template;
        } else {
            $className = 'Codeception\Template\\' . ucfirst($template);

            if (!class_exists($className)) {
                throw new Exception("Template from a {$className} can't be loaded; Init can't be executed");
            }
        }

        $initProcess = new $className($input, $output);
        if (!$initProcess instanceof InitTemplate) {
            throw new Exception("{$className} is not a valid template");
        }
        if ($path = $input->getOption('path')) {
            $initProcess->initDir($path);
        }
        $initProcess->setup();
        return 0;
    }
}
