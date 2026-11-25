<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Anime;
use App\Models\Drama;
use App\Models\LightNovel;
use App\Models\Manga;
use App\Models\Media;
use App\Models\User;
use InvalidArgumentException;

/**
 * Menu interaktif FlipPlay di terminal.
 * Class ini hanya mengatur alur menu; data dan aturannya tetap ada di
 * MediaLibrary, Media, dan User.
 */
class FlipPlayApp
{
    private bool $running = true;

    public function __construct(
        private MediaLibrary $library,
        private User $user,
    ) {
    }

    public function run(): void
    {
        Console::banner('FlipPlay - Tonton ceritanya, lalu baca kelanjutannya');
        Console::line("Halo, {$this->user->getName()}! Ada {$this->library->count()} media di katalog.");

        while ($this->running) {
            $this->showMainMenu();

            $choice = Console::askInt('Pilih menu: ', 0, 6);
            if ($choice === null) {
                break; // input habis
            }

            match ($choice) {
                1 => $this->addMedia(),
                2 => $this->showAllMedia(),
                3 => $this->searchMedia(),
                4 => $this->showMediaDetail(),
                5 => $this->filterByAura(),
                6 => $this->manageCollection(),
                0 => $this->running = false,
            };
        }

        Console::line();
        Console::banner('Sampai jumpa di FlipPlay!');
    }

    private function showMainMenu(): void
    {
        Console::section('Menu utama');
        Console::line('1. Tambah media ke library');
        Console::line('2. Lihat daftar media');
        Console::line('3. Cari media berdasarkan judul');
        Console::line('4. Lihat detail media');
        Console::line('5. Temukan media berdasarkan Aura');
        Console::line('6. Koleksi saya');
        Console::line('0. Keluar');
    }

    // ---------- 1. Tambah media ----------

    private function addMedia(): void
    {
        Console::section('Tambah media');
        Console::line('Jenis media: 1. Anime   2. Drama   3. Manga   4. Light Novel');

        $type = Console::askInt('Pilih jenis (0 untuk batal): ', 0, 4);
        if ($type === null || $type === 0) {
            return;
        }

        $title = Console::ask('Judul           : ') ?? '';
        if ($title === '') {
            Console::line('   Judul tidak boleh kosong. Penambahan dibatalkan.');
            Console::pause();
            return;
        }

        $year   = Console::askInt('Tahun rilis     : ', 1900, 2100) ?? 2026;
        $score  = Console::askFloat('Skor (0-10)     : ') ?? 0.0;
        $genres = $this->parseGenres(Console::ask('Genre (pisahkan dengan koma): ') ?? '');
        $status = Console::askInt('Status (1. Tamat  2. Berjalan  3. Akan rilis): ', 1, 3) ?? 1;
        $status = ['finished', 'current', 'upcoming'][$status - 1];

        try {
            $media = match ($type) {
                1 => new Anime($title, $year, $score, $genres, $status,
                    Console::askInt('Jumlah episode  : ', 1, 10000) ?? 1,
                    Console::ask('Studio          : ') ?? '-'),
                2 => new Drama($title, $year, $score, $genres, $status,
                    Console::askInt('Jumlah episode  : ', 1, 10000) ?? 1,
                    Console::ask('Negara asal     : ') ?? '-'),
                3 => new Manga($title, $year, $score, $genres, $status,
                    Console::askInt('Jumlah bab      : ', 1, 10000) ?? 1,
                    Console::ask('Penulis         : ') ?? '-'),
                4 => new LightNovel($title, $year, $score, $genres, $status,
                    Console::askInt('Jumlah volume   : ', 1, 1000) ?? 1,
                    Console::ask('Penulis         : ') ?? '-'),
            };
        } catch (InvalidArgumentException $e) {
            // Contoh: skor 12 ditolak oleh validasi di class Media
            Console::line('   Gagal: ' . $e->getMessage());
            Console::pause();
            return;
        }

        if (!$this->library->addMedia($media)) {
            Console::line("   [{$media->getType()}] {$media->getTitle()} sudah ada di library.");
            Console::pause();
            return;
        }

        Console::line("   Berhasil! [{$media->getType()}] {$media->getTitle()} ditambahkan.");

        // Fitur "Tonton lalu baca": tawarkan menautkan ke judul yang sama dengan jenis berbeda
        $counterpart = $this->library->findCounterpart($media);
        if ($counterpart !== null
            && Console::confirm("   Ditemukan [{$counterpart->getType()}] {$counterpart->getTitle()}. Tautkan sebagai Tonton lalu baca?")) {
            $media->linkAdaptation($counterpart);
            Console::line('   Tertaut!');
        }

        Console::pause();
    }

    // "action, Drama ,  fantasy" -> ['Action', 'Drama', 'Fantasy']
    private function parseGenres(string $input): array
    {
        $genres = array_map(fn (string $genre): string => ucwords(strtolower(trim($genre))), explode(',', $input));

        return array_values(array_filter($genres, fn (string $genre): bool => $genre !== ''));
    }

    // ---------- 2. Daftar media ----------

    private function showAllMedia(): void
    {
        Console::section('Daftar media (skor tertinggi)');
        Console::mediaList($this->library->getTopRated());

        $perType = [];
        foreach ($this->library->countByType() as $type => $total) {
            $perType[] = "{$type}: {$total}";
        }
        Console::line();
        Console::line("Total {$this->library->count()} media (" . implode(', ', $perType) . ')');
        Console::pause();
    }

    // ---------- 3. Cari ----------

    private function searchMedia(): void
    {
        Console::section('Cari media');
        $keyword = Console::ask('Masukkan judul atau sebagian judul: ') ?? '';

        if ($keyword === '') {
            Console::line('   Kata kunci kosong.');
            Console::pause();
            return;
        }

        $results = $this->library->searchByTitle($keyword);
        Console::line("Hasil untuk \"{$keyword}\": " . count($results) . ' media');
        Console::mediaList($results);

        // Langsung tawarkan lihat detail dari hasil pencarian
        if (count($results) > 0) {
            $this->pickAndShowDetail($results);
            return;
        }

        Console::pause();
    }

    // ---------- 4. Detail ----------

    private function showMediaDetail(): void
    {
        Console::section('Detail media');
        $list = $this->library->getTopRated();
        Console::mediaList($list);
        $this->pickAndShowDetail($list);
    }

    private function pickAndShowDetail(array $list): void
    {
        $number = Console::askInt(PHP_EOL . 'Nomor media untuk lihat detail (0 untuk kembali): ', 0, count($list));
        if ($number === null || $number === 0) {
            return;
        }

        /** @var Media $media */
        $media = $list[$number - 1];

        Console::line();
        Console::line($media->getDetail());

        if (!$this->user->hasInCollection($media)
            && Console::confirm(PHP_EOL . 'Tambahkan ke koleksimu?')) {
            $this->user->addToCollection($media);
            Console::line('   Ditambahkan ke koleksi.');
        }

        Console::pause();
    }

    // ---------- 5. Aura ----------

    private function filterByAura(): void
    {
        Console::section('Aura-Based Discovery');

        $auras = [Aura::HIGH_ENERGY, Aura::MELANCHOLY, Aura::BINGE_MODE];
        foreach ($auras as $index => $aura) {
            Console::line(($index + 1) . '. ' . Aura::describe($aura));
        }

        $choice = Console::askInt('Pilih Aura (0 untuk kembali): ', 0, count($auras));
        if ($choice === null || $choice === 0) {
            return;
        }

        $results = $this->library->filterByAura($auras[$choice - 1]);
        Console::line();
        Console::line(count($results) . ' media cocok:');
        Console::mediaList($results);

        if (count($results) > 0) {
            $this->pickAndShowDetail($results);
            return;
        }

        Console::pause();
    }

    // ---------- 6. Koleksi ----------

    private function manageCollection(): void
    {
        while (true) {
            Console::section("Koleksi milik {$this->user->getName()}");
            $this->printCollection();

            Console::line();
            Console::line('1. Tambah media ke koleksi');
            Console::line('2. Perbarui progres tonton/baca');
            Console::line('0. Kembali');

            $choice = Console::askInt('Pilih: ', 0, 2);
            if ($choice === null || $choice === 0) {
                return;
            }

            if ($choice === 1) {
                $this->addToCollection();
            } else {
                $this->updateProgress();
            }
        }
    }

    private function printCollection(): void
    {
        $collection = $this->user->getCollection();

        if (count($collection) === 0) {
            Console::line('   Koleksimu masih kosong.');
            return;
        }

        foreach ($collection as $index => $media) {
            $progress = $this->user->getProgress($media);
            $total    = $media->getTotalUnits();
            $next     = $progress === $total
                ? 'Selesai'
                : "{$media->getAction()} {$media->getUnitName()} " . ($progress + 1);

            Console::line(sprintf(
                '%3d. [%s] %s - %d/%d %s (%s)',
                $index + 1,
                $media->getType(),
                $media->getTitle(),
                $progress,
                $total,
                $media->getUnitName(),
                $next
            ));

            // Sudah selesai dan ada versi lain -> sarankan Tonton lalu baca
            $adaptation = $media->getAdaptation();
            if ($progress === $total && $adaptation !== null) {
                Console::line("      -> Lanjutkan: {$adaptation->getAction()} versi {$adaptation->getType()}-nya, \"{$adaptation->getTitle()}\"");
            }
        }
    }

    private function addToCollection(): void
    {
        $list = $this->library->getTopRated();
        Console::line();
        Console::mediaList($list);

        $number = Console::askInt('Nomor media yang ingin ditambahkan (0 untuk batal): ', 0, count($list));
        if ($number === null || $number === 0) {
            return;
        }

        $media = $list[$number - 1];
        $added = $this->user->addToCollection($media);

        Console::line($added
            ? "   [{$media->getType()}] {$media->getTitle()} ditambahkan ke koleksi."
            : '   Media itu sudah ada di koleksimu.');
    }

    private function updateProgress(): void
    {
        $collection = $this->user->getCollection();
        if (count($collection) === 0) {
            Console::line('   Koleksi masih kosong, tambahkan media dulu.');
            return;
        }

        $number = Console::askInt('Nomor media di koleksi (0 untuk batal): ', 0, count($collection));
        if ($number === null || $number === 0) {
            return;
        }

        /** @var Media $media */
        $media = $collection[$number - 1];
        $unit  = $media->getUnitName();

        $value = Console::askInt("Sudah sampai {$unit} berapa? (0-{$media->getTotalUnits()}): ", -1000, 100000);
        if ($value === null) {
            return;
        }

        // Validasi tetap dilakukan oleh class User (encapsulation)
        Console::line($this->user->updateProgress($media, $value)
            ? '   Progres disimpan.'
            : "   Ditolak: {$media->getTitle()} hanya punya {$media->getTotalUnits()} {$unit}.");
    }
}
