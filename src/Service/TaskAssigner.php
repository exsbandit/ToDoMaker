<?php

namespace App\Service;

use App\Entity\Developer;
use App\Entity\Task;
use App\Entity\WeeklyPlan;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use DateInterval;
use DatePeriod;

class TaskAssigner
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function assignTasks(): void
    {
        $developers = $this->entityManager->getRepository(Developer::class)->findAll();
        $taskRepository = $this->entityManager->getRepository(Task::class);
        $weeklyPlanRepository = $this->entityManager->getRepository(WeeklyPlan::class);

        $now = new DateTime();

        // Atanmamış görevleri al
        $tasks = $taskRepository->findBy(['assigned' => false]);

        // Geliştirici kapasitesini elindeki işe göre çek
        $developerCapacities = array_map(function (Developer $developer) use ($weeklyPlanRepository, $now) {
            $assignedPlans = $weeklyPlanRepository->createQueryBuilder('wp')
                ->where('wp.developer = :developer')
                ->andWhere('wp.endDate > :now')
                ->setParameter('developer', $developer)
                ->setParameter('now', $now)
                ->getQuery()
                ->getResult();

            $totalAssignedHours = 0;

            // Görevlerin zorluk ve süre bilgilerini kullanarak toplam iş süresini hesaplar
            foreach ($assignedPlans as $plan) {
                $task = $plan->getTask();
                $workUnits = $task->getDuration() * $task->getDifficulty();
                $totalAssignedHours += $workUnits / $developer->getEfficiency();
            }

            // Geliştiricinin son işinin bitiş saatini bul (yeni işin başlangıcı buna göre ayarlanacak)
            $lastEndDate = !empty($assignedPlans) ? end($assignedPlans)->getEndDate() : null;

            return [
                'developer' => $developer,
                'efficiency' => $developer->getEfficiency(),
                'totalAssignedHours' => $totalAssignedHours, // Geliştiriciye atanmış toplam iş süresi
                'lastEndDate' => $lastEndDate // Geliştiricinin son işinin bitiş saati
            ];
        }, $developers);

        // Görevleri zorluk ve süreye göre sırala
        usort($tasks, function (Task $a, Task $b) {
            $workUnitsA = $a->getDuration() * $a->getDifficulty();
            $workUnitsB = $b->getDuration() * $b->getDifficulty();
            return $workUnitsB <=> $workUnitsA; // Büyükten küçüğe sırala
        });

        // Geliştiriciler için çalışma saatleri ve tatil günleri
        $workStartHour = 9;
        $workEndHour = 18;
        $workDaysPerWeek = [1, 2, 3, 4, 5]; // Pazartesi = 1, Cuma = 5

        $taskIndex = 0;

        while ($taskIndex < count($tasks)) {
            foreach ($tasks as $task) {
                if ($taskIndex >= count($tasks)) {
                    break; // Tüm görevler atanmışsa çık
                }

                // Geliştirici listesi (atanmamış işleri 0 olanlar, atanmışları da artan saatlere göre sırala)
                $zeroAssignedHours = array_filter($developerCapacities, function ($developerData) {
                    return !empty($developerData) && $developerData['totalAssignedHours'] == 0;
                });

                $nonZeroAssignedHours = array_filter($developerCapacities, function ($developerData) {
                    return !empty($developerData) && $developerData['totalAssignedHours'] > 0;
                });

                usort($zeroAssignedHours, function ($a, $b) {
                    return $b['efficiency'] <=> $a['efficiency'];
                });

                usort($nonZeroAssignedHours, function ($a, $b) {
                    return $a['totalAssignedHours'] <=> $b['totalAssignedHours'];
                });

                $sortedDeveloperCapacities = array_merge($zeroAssignedHours, $nonZeroAssignedHours);
                $selectedDeveloper = $sortedDeveloperCapacities[0];
                $index = array_search($selectedDeveloper, $developerCapacities, true);
                $developerData = &$developerCapacities[$index];

                $workUnits = $task->getDuration() * $task->getDifficulty();
                $requiredHours = $workUnits / $developerData['efficiency'];

                // Yeni işin başlangıç tarihini bul
                $startDate = $developerData['lastEndDate'] ? clone $developerData['lastEndDate'] : $this->getNextWorkingDay(new DateTime('next monday 9am'));
                $endDate = $this->calculateEndDate($startDate, $requiredHours, $workStartHour, $workEndHour, $workDaysPerWeek);

                $weeklyPlan = new WeeklyPlan();
                $weeklyPlan->setDeveloper($developerData['developer']);
                $weeklyPlan->setTask($task);
                $weeklyPlan->setStartDate($startDate);
                $weeklyPlan->setEndDate($endDate);

                $this->entityManager->persist($weeklyPlan);
                $developerData['totalAssignedHours'] += $requiredHours;
                $developerData['lastEndDate'] = $endDate;

                $task->setAssigned(true);
                $this->entityManager->persist($task);
                $taskIndex++;
            }
        }

        // Dbye kaydet
        $this->entityManager->flush();
    }

    private function getNextWorkingDay(DateTime $date): DateTime
    {
        // Cumartesi veya Pazar ise, bir sonraki Pazartesi'ye ayarla
        if ((int)$date->format('N') >= 6) {
            $date->modify('next monday');
        }
        return $date->setTime(9, 0);
    }

    private function calculateEndDate(DateTime $startDate, float $requiredHours, int $workStartHour, int $workEndHour, array $workDaysPerWeek): DateTime
    {
        $currentDate = clone $startDate;
        $hoursRemaining = $requiredHours;

        while ($hoursRemaining > 0) {
            $dayOfWeek = (int)$currentDate->format('N');

            // Eğer çalışma günü ise
            if (in_array($dayOfWeek, $workDaysPerWeek)) {
                $currentHour = (int)$currentDate->format('H');

                // Bugün çalışılabilecek maksimum saat
                $workableHoursToday = min($hoursRemaining, $workEndHour - $currentHour);
                $currentDate->add(new DateInterval("PT" . (int)$workableHoursToday . "H"));
                $hoursRemaining -= $workableHoursToday;

                // Eğer bugün saatler biterse yarına geç
                if ($currentHour + $workableHoursToday >= $workEndHour) {
                    $currentDate = $this->getNextWorkingDay($currentDate->modify('+1 day'));
                }
            } else {
                $currentDate = $this->getNextWorkingDay($currentDate->modify('+1 day'));
            }
        }

        return $currentDate;
    }
}