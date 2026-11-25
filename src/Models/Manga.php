<?php

declare(strict_types=1);

namespace App\Models;

class Manga extends Media
{
    public function __construct(
        string $title,
        int $year,
        float $score,
        array $genres,
        string $status,
        private int $chapters,
        private string $author,
    ) {
        parent::__construct($title, $year, $score, $genres, $status);
    }

    public function getType(): string
    {
        return 'Manga';
    }

    public function getUnitName(): string
    {
        return 'bab';
    }

    public function getTotalUnits(): int
    {
        return $this->chapters;
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
