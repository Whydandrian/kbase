<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Sop;
use Illuminate\Database\Seeder;

class SopSeeder extends Seeder
{
    /**
     * Seed one complete structured SOP based on the real
     * "SOP Penyusunan Program Kerja" document (SOP AP format).
     */
    public function run(): void
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
