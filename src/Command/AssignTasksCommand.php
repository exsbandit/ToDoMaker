<?php

namespace App\Command;

use App\Service\TaskAssigner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignTasksCommand extends Command
{
    private $taskAssigner;
    private $entityManager;

    public function __construct(TaskAssigner $taskAssigner, EntityManagerInterface $entityManager)
    {
        $this->taskAssigner = $taskAssigner;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:assign-tasks')
            ->setDescription('Assign tasks to developers based on efficiency and difficulty.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->taskAssigner->assignTasks();

        $output->writeln('Tasks have been successfully assigned to developers.');

        return Command::SUCCESS;
    }
}