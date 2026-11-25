<?php

declare(strict_types=1);

/*
 * FlipPlay - Prototype Backend Engine (Modul 3 PHP OOP)
 * Jalankan dari terminal:  php index.php
 */

// ---------- Autoloader ----------
// Memuat file class secara otomatis berdasarkan namespace-nya,
// jadi tidak perlu menulis require_once satu per satu.
// Contoh: App\Models\Anime  ->  src/Models/Anime.php
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $relativePath . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

use App\Models\Anime;
use App\Models\Drama;
use App\Models\LightNovel;
use App\Models\Manga;
use App\Models\User;
use App\Services\Console;
use App\Services\FlipPlayApp;
use App\Services\MediaLibrary;

// ---------- Katalog awal ----------
// Data contoh supaya library tidak kosong saat simulasi dimulai.
$library = new MediaLibrary();

$aotAnime     = new Anime('Attack on Titan', 2013, 8.5, ['Action', 'Drama', 'Fantasy'], 'finished', 25, 'Wit Studio');
$frieren      = new Anime("Frieren: Beyond Journey's End", 2023, 9.1, ['Adventure', 'Drama', 'Fantasy'], 'finished', 28, 'Madhouse');
$violet       = new Anime('Violet Evergarden', 2018, 8.7, ['Drama', 'Fantasy'], 'finished', 13, 'Kyoto Animation');
$spyFamily    = new Anime('Spy x Family', 2022, 8.4, ['Action', 'Comedy'], 'current', 25, 'Wit Studio & CloverWorks');
$cloy         = new Drama('Crash Landing on You', 2019, 8.7, ['Romance', 'Comedy'], 'finished', 16, 'Korea Selatan');
$alice        = new Drama('Alice in Borderland', 2020, 7.8, ['Thriller', 'Science Fiction'], 'finished', 8, 'Jepang');
$aotManga     = new Manga('Attack on Titan', 2009, 8.6, ['Action', 'Drama', 'Fantasy'], 'finished', 139, 'Hajime Isayama');
$frierenManga = new Manga("Frieren: Beyond Journey's End", 2020, 9.0, ['Adventure', 'Fantasy'], 'current', 140, 'Kanehito Yamada');
$violetNovel  = new LightNovel('Violet Evergarden', 2015, 8.2, ['Drama', 'Fantasy'], 'finished', 4, 'Kana Akatsuki');
$sao          = new LightNovel('Sword Art Online', 2009, 7.5, ['Action', 'Adventure', 'Romance'], 'current', 27, 'Reki Kawahara');

foreach ([$aotAnime, $frieren, $violet, $spyFamily, $cloy, $alice, $aotManga, $frierenManga, $violetNovel, $sao] as $media) {
    $library->addMedia($media);
}

// Fitur "Tonton lalu baca": hubungkan versi tonton dengan versi bacanya
$aotAnime->linkAdaptation($aotManga);
$frieren->linkAdaptation($frierenManga);
$violet->linkAdaptation($violetNovel);

// ---------- Pengguna ----------
$name = Console::ask('Masukkan namamu: ');
$user = new User($name !== null && $name !== '' ? $name : 'Tamu');

// ---------- Jalankan aplikasi ----------
$app = new FlipPlayApp($library, $user);
$app->run();
