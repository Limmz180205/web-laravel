<?php

declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;

/**
 * Parent class untuk semua jenis media di FlipPlay.
 * Dibuat abstract karena tidak ada media yang "hanya Media":
 * setiap media pasti Anime, Drama, Manga, atau Light Novel.
 */
abstract class Media
{
    // Konstanta class, diakses lewat self::STATUS_LABELS
    private const STATUS_LABELS = [
        'finished' => 'Tamat',
        'current'  => 'Berjalan',
        'upcoming' => 'Akan rilis',
    ];

    private string $title;
    private int $year;
    private float $score;
    private array $genres;
    private string $status;

    // Fitur "Tonton lalu baca": anime/drama ditautkan ke versi manga/novelnya
    private ?Media $adaptation = null;

    public function __construct(string $title, int $year, float $score, array $genres, string $status)
    {
        $this->title  = $title;
        $this->year   = $year;
        $this->genres = $genres;
        $this->setScore($score);
        $this->setStatus($status);
    }

    // ---------- Method abstract: wajib diisi oleh setiap child class ----------

    abstract public function getType(): string;       // "Anime", "Manga", ...
    abstract public function getUnitName(): string;   // "episode", "bab", "volume"
    abstract public function getTotalUnits(): int;    // jumlah episode/bab/volume
    abstract public function getAction(): string;     // "Tonton" atau "Baca"
    abstract protected function getExtraInfo(): array; // info khusus tiap jenis media

    // ---------- Setter dengan validasi (encapsulation) ----------

    private function setScore(float $score): void
    {
        if ($score < 0 || $score > 10) {
            throw new InvalidArgumentException("Skor harus di antara 0 dan 10, diberikan: {$score}");
        }
        $this->score = $score;
    }

    private function setStatus(string $status): void
    {
        if (!array_key_exists($status, self::STATUS_LABELS)) {
            throw new InvalidArgumentException("Status '{$status}' tidak dikenal.");
        }
        $this->status = $status;
    }

    // ---------- Getter ----------

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function getGenres(): array
    {
        return $this->genres;
    }

    public function getStatusLabel(): string
    {
        return self::STATUS_LABELS[$this->status];
    }

    public function isFinished(): bool
    {
        return $this->status === 'finished';
    }

    // Mengecek apakah media punya salah satu genre (tidak peka huruf besar/kecil)
    public function hasGenre(array $names): bool
    {
        foreach ($this->genres as $genre) {
            if (in_array(strtolower($genre), $names, true)) {
                return true;
            }
        }
        return false;
    }

    // Kunci unik: jenis + judul, karena anime dan manga bisa berjudul sama
    public function getKey(): string
    {
        return strtolower($this->getType() . ':' . $this->title);
    }

    // ---------- Fitur "Tonton lalu baca" ----------

    public function linkAdaptation(Media $other): void
    {
        $this->adaptation  = $other;
        $other->adaptation = $this;
    }

    public function getAdaptation(): ?Media
    {
        return $this->adaptation;
    }

    // ---------- Tampilan ----------

    // Ringkasan satu baris untuk daftar
    public function getSummary(): string
    {
        return sprintf(
            '[%s] %s (%d) - Skor %.1f - %s',
            $this->getType(),
            $this->title,
            $this->year,
            $this->score,
            $this->getStatusLabel()
        );
    }

    // Detail lengkap. Hasilnya berbeda untuk tiap jenis media (polymorphism),
    // karena getType(), getUnitName(), dan getExtraInfo() diisi oleh child class.
    public function getDetail(): string
    {
        $rows = [
            'Judul'   => $this->title,
            'Jenis'   => $this->getType(),
            'Tahun'   => (string) $this->year,
            'Skor'    => sprintf('%.1f / 10', $this->score),
            'Genre'   => implode(', ', $this->genres),
            'Status'  => $this->getStatusLabel(),
            'Panjang' => $this->getTotalUnits() . ' ' . $this->getUnitName(),
        ];

        $rows = array_merge($rows, $this->getExtraInfo());

        if ($this->adaptation !== null) {
            $rows['Lanjutan'] = sprintf(
                '%s versi %s-nya -> %s',
                $this->adaptation->getAction(),
                $this->adaptation->getType(),
                $this->adaptation->getTitle()
            );
        }

        $lines = [];
        foreach ($rows as $label => $value) {
            $lines[] = sprintf('%-9s: %s', $label, $value);
        }

        return implode(PHP_EOL, $lines);
    }
}
