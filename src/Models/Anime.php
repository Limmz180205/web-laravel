<?php

declare(strict_types=1);

namespace App\Models;

class Anime extends Media
{
    // Constructor Property Promotion: $episodes dan $studio langsung jadi property private
    public function __construct(
        string $title,
        int $year,
        float $score,
        array $genres,
        string $status,
        private int $episodes,
        private string $studio,
    ) {
        // Data umum diteruskan ke constructor parent (Media)
        parent::__construct($title, $year, $score, $genres, $status);
    }

    public function getType(): string
    {
        return 'Anime';
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
        return ['Studio' => $this->studio];
    }
}
