<?php

namespace App\Command;

use App\Service\ProviderFactory;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class FetchTasksCommand extends Command
{
    private $providerFactory;
    private $entityManager;

    public function __construct(ProviderFactory $providerFactory, EntityManagerInterface $entityManager)
    {
        $this->providerFactory = $providerFactory;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:fetch-tasks')
            ->setDescription('Fetch tasks from providers and save to database.')
            ->addOption('provider', null, InputOption::VALUE_REQUIRED, 'Provider name (provider1, provider2, or provider3)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $providerName = $input->getOption('provider');
        $provider = $this->providerFactory->getProvider($providerName);
        $tasks = $provider->fetchTasks();

        $lastTaskId = $this->entityManager->getRepository(Task::class)->createQueryBuilder('t')
            ->select('MAX(t.id)')
            ->getQuery()
            ->getSingleScalarResult();

        foreach ($tasks as $taskData) {
            $task = new Task();
            $task->setDifficulty($taskData['difficulty']);
            $task->setDuration($taskData['duration']);
            $task->setName('Task ' . ($lastTaskId + 1));

            $this->entityManager->persist($task);
            $lastTaskId++;
        }

        $this->entityManager->flush();

        $output->writeln('Tasks fetched and saved to the database.');

        return Command::SUCCESS;
    }
}