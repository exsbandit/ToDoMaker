<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\WeeklyPlanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

class TaskController extends AbstractController
{
    private $taskRepository;
    private $weeklyPlanRepository;

    public function __construct(TaskRepository $taskRepository, WeeklyPlanRepository $weeklyPlanRepository)
    {
        $this->taskRepository = $taskRepository;
        $this->weeklyPlanRepository = $weeklyPlanRepository;
    }

    /**
     * @Route("/weekly-plans", name="weekly_plans_list")
     */
    public function weeklyPlans(): Response
    {
        $weeklyPlans = $this->weeklyPlanRepository->findAllWeeklyPlans();

        $earliestStartDate = null;
        $latestEndDate = null;

        $developerTasks = [];

        foreach ($weeklyPlans as $plan) {
            $developerName = $plan->getDeveloper()->getName();
            $taskUnit = $plan->getTask()->getDuration() * $plan->getTask()->getDifficulty();
            $taskHours = ceil(($plan->getTask()->getDuration() * $plan->getTask()->getDifficulty()) / $plan->getDeveloper()->getEfficiency());

            if (!isset($developerTasks[$developerName])) {
                $developerTasks[$developerName] = [
                    'taskUnits' => 0,
                    'totalHours' => 0,
                ];
            }

            $developerTasks[$developerName]['taskUnits'] += $taskUnit;
            $developerTasks[$developerName]['totalHours'] += $taskHours;

            // Calculate earliest start date and latest end date
            if ($earliestStartDate === null || $plan->getStartDate() < $earliestStartDate) {
                $earliestStartDate = $plan->getStartDate();
            }

            if ($latestEndDate === null || $plan->getEndDate() > $latestEndDate) {
                $latestEndDate = $plan->getEndDate();
            }
        }

        if ($earliestStartDate && $latestEndDate) {
            $interval = $earliestStartDate->diff($latestEndDate);
            $weeks = $interval->days / 7;
            $days = $interval->days % 7;
            $planDuration = '';

            if ($weeks > 0) {
                $planDuration .= sprintf("%d hafta", floor($weeks));
            }

            if ($days > 0) {
                $planDuration .= ($planDuration ? ' ' : '') . sprintf("%d gün", $days);
            }

            if (empty($planDuration)) {
                $planDuration = 'N/A';
            }
        } else {
            $planDuration = 'N/A';
        }

        return $this->render('task/todo.html.twig', [
            'weekly_plans' => $weeklyPlans,
            'developer_tasks' => $developerTasks,
            'earliestStartDate' => $earliestStartDate,
            'latestEndDate' => $latestEndDate,
            'planDuration' => $planDuration,
        ]);
    }

    /**
     * @Route("/task/{id}", name="task_show")
     */
    public function show(Task $task): Response
    {
        $weeklyPlans = $this->weeklyPlanRepository->findByTask($task);

        return $this->render('task/show.html.twig', [
            'task' => $task,
            'weeklyPlans' => $weeklyPlans,
        ]);
    }

    /**
     * @Route("/tasks", name="task_list")
     */
    public function taskList(): Response
    {
        $tasks = $this->taskRepository->findAllTasks();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
        ]);
    }
}