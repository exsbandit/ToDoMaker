<?php

namespace App\Repository;

use App\Entity\WeeklyPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WeeklyPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeeklyPlan::class);
    }

    public function findAllWeeklyPlans()
    {
        return $this->findAll();
    }

    public function findByTask($task)
    {
        return $this->findBy(['task' => $task]);
    }
}