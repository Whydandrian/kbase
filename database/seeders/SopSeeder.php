<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Sop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SopSeeder extends Seeder
{
    private const SOP_DIRECTORY = 'tata_kelola_ti/dokumen_sop';

    private const ACRONYMS = ['SOP', 'VPS', 'TIK', 'TI', 'ITK', 'UPT'];

    public function run(): void
    {
        // 1) Satu SOP lengkap (data nyata) sebagai contoh terisi penuh.
        $this->seedDetailedSop();

        // 2) SOP terstruktur untuk SEMUA file PDF di storage private dokumen_sop.
        $this->seedSopsFromFiles();
    }

    /**
     * Buat satu entri SOP terstruktur untuk setiap berkas PDF pada
     * storage/app/private/tata_kelola_ti/dokumen_sop.
     *
     * Tiap SOP dibuat dengan identitas dasar + 3 baris pengesahan kosong,
     * tertaut ke dokumen PDF-nya, dan siap dilengkapi lewat form edit.
     */
    private function seedSopsFromFiles(): void
    {
        $disk = Storage::disk('local');

        if (! $disk->exists(self::SOP_DIRECTORY)) {
            $this->command?->warn('Direktori SOP tidak ditemukan: '.self::SOP_DIRECTORY);

            return;
        }

        $count = 0;

        foreach ($disk->files(self::SOP_DIRECTORY) as $path) {
            if (! Str::endsWith(Str::lower($path), '.pdf')) {
                continue;
            }

            // "(01) SOP PENYUSUNAN PROGRAM KERJA.pdf" -> "Penyusunan Program Kerja"
            $rawName = pathinfo($path, PATHINFO_FILENAME);
            $number = $this->extractNumber($rawName);
            $namaSop = $this->deriveName($rawName);

            // SOP contoh yang sudah lengkap di-skip agar tidak tertimpa.
            if (Str::lower($namaSop) === 'penyusunan program kerja') {
                continue;
            }

            $document = Document::query()
                ->where('slug', Str::slug('SOP '.$namaSop))
                ->first();

            $sop = Sop::updateOrCreate(
                ['nomor_sop' => $this->placeholderNumber($number)],
                [
                    'document_id' => $document?->id,
                    'nama_sop' => $namaSop,
                    'unit_pembuat' => 'UPT Teknologi Informasi dan Komunikasi',
                    'kementerian' => 'Kementerian Riset, Teknologi dan Pendidikan Tinggi',
                    'institusi' => 'Institut Teknologi Kalimantan',
                    'status' => 'draft',
                ],
            );

            // Isi baris pengesahan default hanya jika belum ada.
            if ($sop->approvals()->count() === 0) {
                foreach ($this->defaultApprovals() as $approval) {
                    $sop->approvals()->create($approval);
                }
            }

            $count++;
        }

        $this->command?->info("✓ {$count} SOP terstruktur dibuat dari berkas PDF.");
    }

    private function seedDetailedSop(): void
    {
        $document = Document::query()->where('slug', 'sop-penyusunan-program-kerja')->first();

        $sop = Sop::updateOrCreate(
            ['nomor_sop' => '243/IT10.IV/OT.07/2021'],
            [
                'document_id' => $document?->id,
                'nama_sop' => 'Penyusunan Program Kerja',
                'unit_pembuat' => 'UPT Teknologi Informasi dan Komunikasi',
                'kementerian' => 'Kementerian Riset, Teknologi dan Pendidikan Tinggi',
                'institusi' => 'Institut Teknologi Kalimantan',
                'tgl_pembuatan' => '2021-03-19',
                'tgl_revisi' => null,
                'tgl_efektif' => null,
                'status' => 'draft',
            ],
        );

        $sop->approvals()->delete();
        $sop->items()->delete();
        $sop->flowSteps()->delete();

        foreach ($this->approvals() as $approval) {
            $sop->approvals()->create($approval);
        }

        foreach ($this->items() as $order => $item) {
            $sop->items()->create($item + ['sort_order' => $order]);
        }

        foreach ($this->flowSteps() as $order => $step) {
            $sop->flowSteps()->create($step + ['sort_order' => $order]);
        }
    }

    /**
     * Ambil nomor urut dari nama berkas "(NN) SOP ...".
     */
    private function extractNumber(string $rawName): ?string
    {
        if (preg_match('/^\((\d+)\)/', $rawName, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * "(03) SOP PEMBUATAN EMAIL" -> "Pembuatan Email"
     */
    private function deriveName(string $rawName): string
    {
        // Buang prefiks "(NN)".
        $name = preg_replace('/^\(\d+\)\s*/', '', $rawName);
        // Buang kata "SOP" di depan.
        $name = preg_replace('/^SOP\s+/i', '', trim($name));

        return $this->titleCasePreservingAcronyms(trim($name));
    }

    /**
     * Nomor SOP sementara (placeholder) — dilengkapi manual lewat form.
     */
    private function placeholderNumber(?string $number): string
    {
        $seq = str_pad($number ?? '0', 3, '0', STR_PAD_LEFT);

        return "SOP-{$seq}/IT10.IV/OT.07/DRAFT";
    }

    /**
     * Baris pengesahan default (kosong, belum ditandatangani).
     *
     * @return list<array<string, mixed>>
     */
    private function defaultApprovals(): array
    {
        return [
            ['role' => 'penyusun', 'nama' => null, 'jabatan' => null, 'tanggal' => null, 'is_signed' => false],
            ['role' => 'pemeriksa', 'nama' => null, 'jabatan' => null, 'tanggal' => null, 'is_signed' => false],
            ['role' => 'pengesahan', 'nama' => null, 'jabatan' => null, 'tanggal' => null, 'is_signed' => false],
        ];
    }

    /**
     * Title-case dengan mempertahankan akronim tertentu.
     */
    private function titleCasePreservingAcronyms(string $value): string
    {
        $words = array_map(function (string $word): string {
            $bare = trim($word, ',');
            $suffix = str_ends_with($word, ',') ? ',' : '';

            if (in_array(Str::upper($bare), self::ACRONYMS, true)) {
                return Str::upper($bare).$suffix;
            }

            return Str::title(Str::lower($bare)).$suffix;
        }, explode(' ', $value));

        return implode(' ', $words);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function approvals(): array
    {
        return [
            ['role' => 'penyusun', 'nama' => 'Yudhi Trianto, S.Kom.', 'jabatan' => 'Pengadministrasi Umum', 'tanggal' => '2021-07-12', 'is_signed' => false],
            ['role' => 'pemeriksa', 'nama' => 'Tegar Palyus Fiqar S.T., M.Kom.', 'jabatan' => 'Kepala UPT Teknologi Informasi dan Komunikasi', 'tanggal' => '2021-07-12', 'is_signed' => false],
            ['role' => 'pengesahan', 'nama' => 'Dr. Muhammad Mashuri, M.T.', 'jabatan' => 'Wakil Rektor Bidang Akademik', 'tanggal' => null, 'is_signed' => false],
        ];
    }

    /**
     * @return list<array{type: string, content: string, url?: string}>
     */
    private function items(): array
    {
        $tujuan = [
            'Adanya prosedur baku yang dapat dijadikan acuan dalam menyusun/mengembangkan SOP.',
            'Terkoordinasinya unit kerja/tim yang terlibat dalam kegiatan penyusunan/pengembangan SOP.',
            'Terkendalinya proses penyusunan/pengembangan SOP sesuai dengan peraturan yang berlaku di lingkungan Institut Teknologi Kalimantan.',
        ];

        $ruangLingkup = [
            'Pelaksanaan kegiatan penyusunan/pengembangan SOP dilakukan di setiap unit kerja yang ada di lingkungan Institut Teknologi Kalimantan.',
            'Prosedur ini mengatur kegiatan penyusunan SOP baru dan pengembangan atau perbaikan SOP yang telah ada sebelumnya.',
        ];

        $istilah = [
            'Rektor adalah pimpinan tertinggi yang menjalankan fungsi pengelolaan ITK.',
            'UPT TIK adalah unit pelaksana teknis di bawah Rektor yang melaksanakan kegiatan di bidang pengembangan dan pengelolaan teknologi informasi dan komunikasi.',
            'Tim Penyusun/Pengembang SOP adalah dosen atau tendik yang diberikan wewenang oleh pimpinan untuk menyusun atau mengembangkan SOP.',
        ];

        $dasarHukum = [
            'Peraturan Menteri Pendayagunaan Aparatur Negara dan Reformasi Birokrasi Nomor 35 Tahun 2012 tentang pedoman penyusunan Standar Operasional Prosedur Administrasi Pemerintahan',
            'Peraturan Menteri Pendidikan dan Kebudayaan No. 49 Tahun 2014 tentang Standar Nasional Pendidikan Tinggi',
            'Peraturan Menteri Riset, Teknologi dan Pendidikan Tinggi RI Nomor 71 Tahun 2017 tentang pedoman penyusunan dan evaluasi proses bisnis dan SOP',
            'Peraturan Menteri Riset, Teknologi, dan Pendidikan Tinggi RI Nomor 40 Tahun 2015 tentang Organisasi dan Tata Kerja Institut Teknologi Kalimantan',
            'Peraturan Rektor Institut Teknologi Kalimantan Nomor 1 Tahun 2020 tentang Tata Naskah Dinas di Lingkungan Institut Teknologi Kalimantan',
        ];

        $kualifikasi = [
            'Menguasai Tata Cara Penyusunan Program Kerja',
            'Menguasai Visi dan Misi ITK',
            'Menguasai Grand Design UPT TIK ITK',
            'Menguasai Arah Kebijakan dan Program Strategis ITK',
        ];

        $peralatan = ['Peraturan Perundang-undangan', 'Perangkat Komputer', 'Jaringan Internet', 'Alat Tulis Kantor'];
        $peringatan = ['Apabila SOP ini tidak dijalankan, maka Program Kerja tidak dapat disusun dan dilaksanakan oleh seluruh anggota UPT TIK.'];
        $pencatatan = ['Disimpan sebagai data elektronik dan manual.'];
        $keterkaitan = ['SOP Penyusunan Rencana Anggaran Belanja'];

        $rows = [];
        foreach ($tujuan as $c) {
            $rows[] = ['type' => 'tujuan', 'content' => $c];
        }
        foreach ($ruangLingkup as $c) {
            $rows[] = ['type' => 'ruang_lingkup', 'content' => $c];
        }
        foreach ($istilah as $c) {
            $rows[] = ['type' => 'istilah', 'content' => $c];
        }
        foreach ($dasarHukum as $c) {
            $rows[] = ['type' => 'dasar_hukum', 'content' => $c];
        }
        foreach ($kualifikasi as $c) {
            $rows[] = ['type' => 'kualifikasi', 'content' => $c];
        }
        foreach ($keterkaitan as $c) {
            $rows[] = ['type' => 'keterkaitan', 'content' => $c];
        }
        foreach ($peralatan as $c) {
            $rows[] = ['type' => 'peralatan', 'content' => $c];
        }
        foreach ($peringatan as $c) {
            $rows[] = ['type' => 'peringatan', 'content' => $c];
        }
        foreach ($pencatatan as $c) {
            $rows[] = ['type' => 'pencatatan', 'content' => $c];
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function flowSteps(): array
    {
        return [
            ['step_no' => 1, 'kegiatan' => 'Menyusun draf program kerja', 'pelaksana' => 'Staff', 'kelengkapan' => 'Perjanjian kinerja & Indikator Kinerja Utama', 'waktu' => '1 Minggu', 'output' => 'Draf program kerja'],
            ['step_no' => 2, 'kegiatan' => 'Mengundang dan mengadakan rapat koordinasi untuk membahas draf program kerja', 'pelaksana' => 'Kepala UPT', 'kelengkapan' => 'Draf program kerja, Surat undangan rapat', 'waktu' => '20 menit', 'output' => 'Surat undangan rapat & arsip'],
            ['step_no' => 3, 'kegiatan' => 'Membahas, meninjau, dan/atau mengoreksi draf program kerja dalam rapat bersama Kepala UPT', 'pelaksana' => 'Kepala UPT', 'kelengkapan' => 'Draf program kerja, Perjanjian kinerja', 'waktu' => '120 menit', 'output' => 'Notulensi rapat & draf susunan program kerja'],
            ['step_no' => 4, 'kegiatan' => 'Menyusun dan mengajukan susunan program kerja kepada Wakil Rektor II', 'pelaksana' => 'Staff', 'kelengkapan' => 'Draf susunan program kerja', 'waktu' => '10 menit', 'output' => 'Susunan program kerja diterima Wakil Rektor II'],
            ['step_no' => 5, 'kegiatan' => 'Meninjau dan/atau mengoreksi susunan program kerja', 'pelaksana' => 'Wakil Rektor II', 'kelengkapan' => 'Susunan program kerja', 'waktu' => '120 menit', 'output' => 'Hasil tinjauan / koreksi'],
            ['step_no' => 6, 'kegiatan' => 'Memberikan persetujuan dari susunan program kerja yang telah ditinjau / dikoreksi', 'pelaksana' => 'Wakil Rektor II', 'is_decision' => true, 'kelengkapan' => 'Hasil tinjauan / koreksi', 'waktu' => '15 menit', 'output' => 'Persetujuan susunan program kerja'],
            ['step_no' => 7, 'kegiatan' => 'Menetapkan susunan program kerja sesuai dengan hasil peninjauan', 'pelaksana' => 'Wakil Rektor II', 'kelengkapan' => 'Hasil tinjauan & persetujuan', 'waktu' => '10 menit', 'output' => 'Penetapan susunan program kerja'],
            ['step_no' => 8, 'kegiatan' => 'Menerima hasil penetapan dari susunan program kerja', 'pelaksana' => 'Kepala UPT', 'kelengkapan' => 'Berkas program kerja unit', 'waktu' => '10 menit', 'output' => 'Berkas program kerja unit diterima Kepala UPT'],
        ];
    }
}
