<?php

declare(strict_types=1);

namespace App\Models;

class Drama extends Media
{
    public function __construct(
        string $title,
        int $year,
        float $score,
        array $genres,
        string $status,
        private int $episodes,
        private string $country,
    ) {
        parent::__construct($title, $year, $score, $genres, $status);
    }

    public function getType(): string
    {
        return 'Drama';
    }

    public function getUnitName(): string
    {
        return 'episode';
    }

    public function getTotalUnits(): int
    {
        return $this->episodes;
    }

    public function getAction(): string
    {
        return 'Tonton';
    }

    protected function getExtraInfo(): array
    {
        return ['Negara' => $this->country];
    }
}
