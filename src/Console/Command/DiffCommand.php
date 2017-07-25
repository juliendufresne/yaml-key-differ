<?php

declare(strict_types=1);

namespace JulienDufresne\YAMLKeyDiffer\Console\Command;

use JulienDufresne\YAMLKeyDiffer\Runner\DiffRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DiffCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('diff')
            ->addArgument('source-file', InputArgument::REQUIRED, 'The source file, which we can trust.')
            ->addArgument('tested-file', InputArgument::REQUIRED, 'The tested file, which we hope has the same keys as the source file.')
            ->addOption('max-depth', null, InputOption::VALUE_REQUIRED, 'The maximum level depth to search for differences.')
            ->addOption('count', 'c', InputOption::VALUE_NONE, 'Display a message with the number of differences.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $runner = new DiffRunner($input->getArgument('source-file'), $input->getArgument('tested-file'), (int) $input->getOption('max-depth'));
        $result = $runner();
        $result = $this->flattenDiffResult($result);
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('count')) {
            $this->output($result, $io);
        }

        if ($output->isVerbose()) {
            $this->outputMissingKeys($result, $io);
        }

        return empty($result) ? 0 : 1;
    }

    private function output(array $result, SymfonyStyle $output)
    {
        $nbDifference = count($result);
        if (0 === $nbDifference) {
            $output->success('There is no difference between source and tested files.');

            return;
        }

        if (1 === $nbDifference) {
            $output->error('There is 1 difference between source and tested files.');
        } else {
            $output->error(sprintf('There are %d differences between source and tested files.', $nbDifference));
        }
    }

    private function outputMissingKeys(array $result, SymfonyStyle $output)
    {
        foreach ($result as $key) {
            $output->writeln(sprintf('<info>Missing key <comment>%s</comment> in the tested file.</info>', $key));
        }
    }

    private function flattenDiffResult(array $result, $currentPath = '')
    {
        if ($currentPath) {
            $currentPath .= '.';
        }

        $keys = [];
        foreach ($result as $key => $item) {
            if (!is_array($item)) {
                $keys[] = $currentPath.$item;
                continue;
            }

            $keys = array_merge($keys, $this->flattenDiffResult($item, $currentPath.$key));
        }

        return $keys;
    }
}
