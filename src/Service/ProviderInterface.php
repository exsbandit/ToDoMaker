<?php

namespace App\Service;

interface ProviderInterface
{
    public function fetchTasks(): array;
}