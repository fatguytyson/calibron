<?php

namespace App\Command;

use App\DependencyInjection\Solver;
use App\Model\Rect;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FindCommand extends Command
{
    protected static $defaultName = 'app:find';
    protected static $defaultDescription = 'Solve Inq\'s Calibron 12';
    /** @var int[][] */
    private $main = [
        [28,14], // 392
        [21,18], // 378
        [21,18], // 378
        [32,11], // 352
        [32,10], // 320
        [21,14], // 294
        [21,14], // 294
        [17,14], // 238
        [28, 7], // 196
        [28, 6], // 168
        [10, 7], //  70
        [14, 4], //  56
    ];
    /** @var int[][] */
    private $extra = [
        [ 7, 6], // 42
        [14, 3], // 42
        [28, 2], // 28
    ];
    /** @var int[][] */
    private $boards = [
        [63,51], // >3178
        [63,51], // >3178
        [63,51], // >3192
        [56,56], // 3136
    ];

    protected function configure(): void
    {
        $this
            ->addArgument('puzzle', InputArgument::OPTIONAL, 'Puzzle Number', 1)
            ->addOption('all', null, InputOption::VALUE_NONE, 'Show all solutions.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $puzzle = $input->getArgument('puzzle');
        if (1 > $puzzle || 4 < $puzzle) {
            $io->error('Puzzle number out of bounds');
            return Command::INVALID;
        }
        $pieces = [];
        foreach ($this->main as $value) {
            $pieces[] = new Rect(...$value);
        }
        if (isset($this->extra[--$puzzle])) {
            $pieces[] = new Rect(...$this->extra[$puzzle]);
        }
        $solver = new Solver();
        $solver->setIo($io);
        $result = $solver->solve($this->boards[$puzzle][0], $this->boards[$puzzle][1], $pieces, !$input->getOption('all'));

        return $result ? Command::SUCCESS : Command::FAILURE;
    }
}
