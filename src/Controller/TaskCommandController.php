<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TaskCommandController extends AbstractController
{
    /**
     * @Route("/run-task-command/{provider}", name="run_task_command")
     */
    public function runTaskCommand(string $provider): Response
    {
        $command = sprintf('bin/console app:fetch-tasks --provider=%s', escapeshellarg($provider));

        $process = new Process([$command]);
        $process->setWorkingDirectory($this->getParameter('kernel.project_dir'));

        try {
            $process->run();
            // check if the command is successful
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();
            return new Response("Command executed successfully: " . $output);
        } catch (ProcessFailedException $e) {
            return new Response("Command failed: " . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/assign-tasks", name="assign_tasks")
     */
    public function assignTasks(): Response
    {
        $command = 'docker-compose exec app php bin/console app:assign-tasks';
        $command = 'php -v';

        $process = new Process([$command]);
        $process->setWorkingDirectory($this->getParameter('kernel.project_dir'));

        try {
            $process->run();
            // check if the command is successful
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();
            return new Response("Tasks assigned successfully: " . $output);
        } catch (ProcessFailedException $e) {
            return new Response("Command failed: " . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/php-version", name="php_version")
     */
    public function phpVersion(): Response
    {
        $command = ['docker-compose', 'exec', '-T', 'app', 'php', '-v'];

        $process = new Process($command);
        $process->setTimeout(600); // 10 dakikalık zaman aşımı

        try {
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();
            return new Response("PHP Version: " . $output);
        } catch (ProcessFailedException $e) {
            return new Response("Command failed: " . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}