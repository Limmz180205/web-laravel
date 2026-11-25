<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Pengguna FlipPlay beserta koleksi dan progres tonton/bacanya.
 */
class User
{
    private array $collection = []; // kunci media => objek Media
    private array $progress = [];   // kunci media => episode/bab/volume terakhir

    public function __construct(private string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    // Menambah media ke koleksi. Mengembalikan false jika sudah ada (tidak boleh dobel).
    public function addToCollection(Media $media): bool
    {
        if ($this->hasInCollection($media)) {
            return false;
        }

        $this->collection[$media->getKey()] = $media;
        $this->progress[$media->getKey()]   = 0;

        return true;
    }

    public function hasInCollection(Media $media): bool
    {
        return isset($this->collection[$media->getKey()]);
    }

    // Progres hanya bisa diubah lewat method ini, dan divalidasi dulu (encapsulation).
    // Contoh: anime 28 episode tidak boleh diisi progres 30 atau -1.
    public function updateProgress(Media $media, int $value): bool
    {
        if (!$this->hasInCollection($media)) {
            return false;
        }

        if ($value < 0 || $value > $media->getTotalUnits()) {
            return false;
        }

        $this->progress[$media->getKey()] = $value;

        return true;
    }

    public function getProgress(Media $media): int
    {
        return $this->progress[$media->getKey()] ?? 0;
    }

    public function getCollection(): array
    {
        return array_values($this->collection);
    }
}
