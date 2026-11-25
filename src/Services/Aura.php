<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use InvalidArgumentException;

/**
 * Aura-Based Discovery, aturannya sama dengan The Vibe Grid (Modul 2).
 * Isinya hanya konstanta dan static method, jadi dipanggil lewat Aura::...
 * tanpa perlu membuat objek.
 */
final class Aura
{
    public const ALL         = 'all';
    public const HIGH_ENERGY = 'high-energy';
    public const MELANCHOLY  = 'melancholy';
    public const BINGE_MODE  = 'binge-mode';

    // Constructor private: class ini tidak boleh dibuat objeknya (new Aura() akan error)
    private function __construct()
    {
    }

    public static function matches(string $aura, Media $media): bool
    {
        return match ($aura) {
            self::ALL         => true,
            self::HIGH_ENERGY => $media->hasGenre(['action', 'adventure', 'thriller', 'science fiction']),
            self::MELANCHOLY  => $media->hasGenre(['drama', 'romance']) && !$media->hasGenre(['comedy']),
            self::BINGE_MODE  => $media->isFinished(),
            default           => throw new InvalidArgumentException("Aura '{$aura}' tidak dikenal."),
        };
    }

    public static function describe(string $aura): string
    {
        return match ($aura) {
            self::ALL         => 'Semua: menampilkan seluruh media.',
            self::HIGH_ENERGY => 'High Energy: genre Action, Adventure, Thriller, atau Science Fiction.',
            self::MELANCHOLY  => 'Melancholy: genre Drama atau Romance yang bukan komedi.',
            self::BINGE_MODE  => 'Binge Mode: sudah tamat, aman dimaraton.',
            default           => throw new InvalidArgumentException("Aura '{$aura}' tidak dikenal."),
        };
    }
}
