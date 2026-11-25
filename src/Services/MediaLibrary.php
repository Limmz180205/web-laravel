<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;

/**
 * Library FlipPlay: tempat menyimpan, mencari, dan memfilter semua media.
 */
class MediaLibrary
{
    private array $items = []; // kunci media => objek Media

    // Menambah media. Mengembalikan false jika media yang sama sudah ada.
    public function addMedia(Media $media): bool
    {
        if (isset($this->items[$media->getKey()])) {
            return false;
        }

        $this->items[$media->getKey()] = $media;

        return true;
    }

    public function getAll(): array
    {
        return array_values($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    // Jumlah media per jenis, contoh: ['Anime' => 4, 'Manga' => 2, ...]
    public function countByType(): array
    {
        $result = [];
        foreach ($this->items as $media) {
            $type = $media->getType();
            $result[$type] = ($result[$type] ?? 0) + 1;
        }

        return $result;
    }

    // Diurutkan dari skor tertinggi, seperti di The Vibe Grid
    public function getTopRated(): array
    {
        $list = $this->getAll();
        usort($list, fn (Media $a, Media $b): int => $b->getScore() <=> $a->getScore());

        return $list;
    }

    // Pencarian sebagian judul, tidak peka huruf besar/kecil ("titan" cocok dengan "Attack on Titan")
    public function searchByTitle(string $keyword): array
    {
        $keyword = strtolower(trim($keyword));

        return array_values(array_filter(
            $this->items,
            fn (Media $media): bool => str_contains(strtolower($media->getTitle()), $keyword)
        ));
    }

    // Mencari satu media dengan judul persis. $type opsional untuk membedakan anime dan manga berjudul sama.
    public function findByTitle(string $title, ?string $type = null): ?Media
    {
        foreach ($this->items as $media) {
            $sameTitle = strtolower($media->getTitle()) === strtolower($title);
            $sameType  = $type === null || $media->getType() === $type;

            if ($sameTitle && $sameType) {
                return $media;
            }
        }

        return null;
    }

    // Mencari pasangan "Tonton lalu baca": judul sama, tapi yang satu ditonton dan yang lain dibaca
    public function findCounterpart(Media $media): ?Media
    {
        foreach ($this->items as $other) {
            $sameTitle      = strtolower($other->getTitle()) === strtolower($media->getTitle());
            $differentKind  = $other->getAction() !== $media->getAction();
            $notLinkedYet   = $other->getAdaptation() === null;

            if ($sameTitle && $differentKind && $notLinkedYet) {
                return $other;
            }
        }

        return null;
    }

    public function filterByAura(string $aura): array
    {
        return array_values(array_filter(
            $this->items,
            fn (Media $media): bool => Aura::matches($aura, $media)
        ));
    }
}
