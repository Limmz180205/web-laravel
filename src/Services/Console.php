<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;

/**
 * Pembantu input/output di terminal.
 * Hanya memakai karakter ASCII agar tampil benar di PowerShell/CMD Windows.
 */
final class Console
{
    private const WIDTH = 64;

    private function __construct()
    {
    }

    // ---------- Output ----------

    public static function banner(string $text): void
    {
        echo str_repeat('=', self::WIDTH) . PHP_EOL;
        echo str_pad($text, self::WIDTH, ' ', STR_PAD_BOTH) . PHP_EOL;
        echo str_repeat('=', self::WIDTH) . PHP_EOL;
    }

    public static function section(string $title): void
    {
        echo PHP_EOL . strtoupper($title) . PHP_EOL;
        echo str_repeat('-', self::WIDTH) . PHP_EOL;
    }

    public static function line(string $text = ''): void
    {
        echo $text . PHP_EOL;
    }

    // Menampilkan daftar media bernomor (mulai dari 1)
    public static function mediaList(array $list): void
    {
        if (count($list) === 0) {
            self::line('   (tidak ada media)');
            return;
        }

        foreach ($list as $index => $media) {
            /** @var Media $media */
            self::line(sprintf('%3d. %s', $index + 1, $media->getSummary()));
        }
    }

    // ---------- Input ----------

    // Membaca satu baris dari keyboard. Mengembalikan null jika input sudah habis.
    public static function ask(string $prompt): ?string
    {
        echo $prompt;
        $input = fgets(STDIN);

        if ($input === false) {
            return null;
        }

        return trim($input);
    }

    // Membaca angka bulat di rentang $min..$max. Mengulang terus sampai input valid.
    public static function askInt(string $prompt, int $min, int $max): ?int
    {
        while (true) {
            $input = self::ask($prompt);

            if ($input === null) {
                return null;
            }

            if (preg_match('/^-?\d+$/', $input) === 1) {
                $number = (int) $input;
                if ($number >= $min && $number <= $max) {
                    return $number;
                }
            }

            self::line("   Masukkan angka {$min} sampai {$max}.");
        }
    }

    // Membaca angka desimal, koma juga diterima ("8,5" sama dengan "8.5")
    public static function askFloat(string $prompt): ?float
    {
        while (true) {
            $input = self::ask($prompt);

            if ($input === null) {
                return null;
            }

            $input = str_replace(',', '.', $input);
            if (is_numeric($input)) {
                return (float) $input;
            }

            self::line('   Masukkan angka, contoh: 8.5');
        }
    }

    public static function confirm(string $prompt): bool
    {
        $answer = strtolower(self::ask($prompt . ' (y/n): ') ?? 'n');

        return $answer === 'y' || $answer === 'ya';
    }

    public static function pause(): void
    {
        self::ask(PHP_EOL . 'Tekan Enter untuk kembali ke menu...');
    }
}
