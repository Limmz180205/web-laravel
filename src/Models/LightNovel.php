<?php

declare(strict_types=1);

namespace App\Models;

class LightNovel extends Media
{
    public function __construct(
        string $title,
        int $year,
        float $score,
        array $genres,
        string $status,
        private int $volumes,
        private string $author,
    ) {
        parent::__construct($title, $year, $score, $genres, $status);
    }

    public function getType(): string
    {
        return 'Light Novel';
    }

    public function getUnitName(): string
    {
        return 'volume';
    }

    public function getTotalUnits(): int
    {
        return $this->volumes;
    }

    public function getAction(): string
    {
        return 'Baca';
    }

    protected function getExtraInfo(): array
    {
        return ['Penulis' => $this->author];
    }
}
