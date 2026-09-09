<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Domain;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seed domain dan kategori untuk lingkungan production.
 * Tidak membuat dokumen — konten diisi manual oleh petugas setelah deploy.
 *
 * Dijalankan via: php artisan db:seed --class=ProductionSeeder
 */
class ProductionKnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $created = 0;
        $skipped = 0;

        foreach ($this->structure() as $order => $domainData) {
            $domain = Domain::updateOrCreate(
                ['slug' => Str::slug($domainData['name'])],
                [
                    'name' => $domainData['name'],
                    'type' => $domainData['type'],
                    'description' => $domainData['description'],
                    'icon' => $domainData['icon'],
                    'accent' => $domainData['accent'],
                    'sort_order' => $order,
                ],
            );

            foreach ($domainData['categories'] as $catOrder => $cat) {
                $slug = Str::slug($domain->slug.'-'.$cat['name']);
                $existing = Category::where('slug', $slug)->exists();

                Category::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'domain_id' => $domain->id,
                        'name' => $cat['name'],
                        'description' => $cat['description'],
                        'icon' => 'folder',
                        'sort_order' => $catOrder,
                    ],
                );

                $existing ? $skipped++ : $created++;
            }
        }

        $this->command->info("✓ Domains & categories seeded ({$created} baru, {$skipped} sudah ada).");
    }

    /**
     * Struktur domain dan kategori untuk production.
     *
     * @return list<array{name: string, type: string, description: string, icon: string, accent: string, categories: list<array{name: string, description: string}>}>
     */
    private function structure(): array
    {
        return [
            [
                'name' => 'Tata Kelola TI',
                'type' => Domain::TYPE_DOMAIN,
                'description' => 'Kebijakan, struktur, dan tata kelola teknologi informasi.',
                'icon' => 'shield-check',
                'accent' => 'primary',
                'categories' => [
                    ['name' => 'Manajemen Aset TI',                    'description' => 'Dokumen pengelolaan dan inventarisasi aset TI.'],
                    ['name' => 'Dokumen SOP',                           'description' => 'Standar Operasional Prosedur UPT TIK.'],
                    ['name' => 'Sistem Manajemen Keamanan Informasi',   'description' => 'Dokumen SMKI dan kebijakan keamanan informasi.'],
                    ['name' => 'Dokumen KAK',                           'description' => 'Kerangka Acuan Kerja kegiatan TI.'],
                    ['name' => 'Dokumen Standar Sistem Informasi',      'description' => 'Standar dan panduan pengembangan sistem informasi.'],
                ],
            ],
            [
                'name' => 'Perencanaan UPT TIK',
                'type' => Domain::TYPE_DOMAIN,
                'description' => 'Rencana strategis dan roadmap pengembangan TIK.',
                'icon' => 'trending-up',
                'accent' => 'primary',
                'categories' => [
                    ['name' => 'Rencana Strategis',    'description' => 'Dokumen renstra pengembangan TIK.'],
                    ['name' => 'Roadmap Pengembangan', 'description' => 'Peta jalan pengembangan sistem informasi.'],
                    ['name' => 'Rencana Anggaran',     'description' => 'Dokumen perencanaan anggaran belanja TI.'],
                ],
            ],
            [
                'name' => 'Manajemen Layanan TI',
                'type' => Domain::TYPE_DOMAIN,
                'description' => 'SOP dan katalog layanan teknologi informasi.',
                'icon' => 'layers',
                'accent' => 'primary',
                'categories' => [
                    ['name' => 'Dokumen SPBE',            'description' => 'Dokumen Sistem Pemerintahan Berbasis Elektronik.'],
                    ['name' => 'Katalog Layanan',         'description' => 'Daftar layanan TI beserta tingkat layanannya.'],
                    ['name' => 'Service Level Agreement', 'description' => 'Perjanjian tingkat layanan TI.'],
                    ['name' => 'Prosedur Layanan',        'description' => 'Prosedur operasional layanan TI.'],
                ],
            ],
            [
                'name' => 'Manajemen Risiko TI',
                'type' => Domain::TYPE_DOMAIN,
                'description' => 'Register risiko dan rencana mitigasi teknologi informasi.',
                'icon' => 'shield-check',
                'accent' => 'warning',
                'categories' => [
                    ['name' => 'Register Risiko',         'description' => 'Daftar risiko TI beserta dampak dan kemungkinannya.'],
                    ['name' => 'Rencana Mitigasi',        'description' => 'Langkah mitigasi dan penanganan risiko TI.'],
                    ['name' => 'Business Continuity Plan', 'description' => 'Rencana keberlangsungan bisnis dan pemulihan bencana.'],
                ],
            ],
            [
                'name' => 'Dokumentasi Aplikasi',
                'type' => Domain::TYPE_DOMAIN,
                'description' => 'Dokumentasi teknis dan panduan penggunaan aplikasi.',
                'icon' => 'terminal',
                'accent' => 'primary',
                'categories' => [
                    ['name' => 'Panduan Pengguna',  'description' => 'Panduan penggunaan aplikasi untuk pengguna akhir.'],
                    ['name' => 'Dokumentasi API',   'description' => 'Referensi endpoint dan integrasi API aplikasi internal.'],
                    ['name' => 'Arsitektur Sistem', 'description' => 'Diagram dan penjelasan arsitektur sistem informasi.'],
                    ['name' => 'Basis Data',        'description' => 'Skema, kamus data, dan dokumentasi basis data.'],
                ],
            ],
            [
                'name' => 'Materi',
                'type' => Domain::TYPE_DOMAIN,
                'description' => 'Materi pendukung dan dokumen lainnya.',
                'icon' => 'folder',
                'accent' => 'success',
                'categories' => [
                    ['name' => 'Materi Pelatihan',  'description' => 'Bahan pelatihan dan pengembangan kompetensi TI.'],
                    ['name' => 'Materi Sosialisasi', 'description' => 'Bahan sosialisasi pemanfaatan layanan TIK.'],
                    ['name' => 'Dokumen Lainnya',   'description' => 'Dokumen pendukung lainnya.'],
                ],
            ],
            // ===== Tim — dokumen teknis =====
            [
                'name' => 'Web Developer',
                'type' => Domain::TYPE_TEAM,
                'description' => 'Panduan teknis pengembangan web dan aplikasi.',
                'icon' => 'terminal',
                'accent' => 'success',
                'categories' => [
                    ['name' => 'Deployment',       'description' => 'Prosedur dan checklist deployment aplikasi.'],
                    ['name' => 'Coding Standard',  'description' => 'Standar penulisan kode dan konvensi pengembangan.'],
                    ['name' => 'Dokumentasi API',  'description' => 'Dokumentasi API internal tim web.'],
                ],
            ],
            [
                'name' => 'Networking & Infrastructure',
                'type' => Domain::TYPE_TEAM,
                'description' => 'Panduan teknis jaringan dan infrastruktur.',
                'icon' => 'network',
                'accent' => 'primary',
                'categories' => [
                    ['name' => 'Konfigurasi Jaringan', 'description' => 'Dokumentasi konfigurasi perangkat jaringan.'],
                    ['name' => 'Runbook Insiden',     'description' => 'Panduan penanganan insiden jaringan dan infrastruktur.'],
                    ['name' => 'Keamanan Jaringan',   'description' => 'Kebijakan dan prosedur keamanan jaringan.'],
                ],
            ],
            [
                'name' => 'Administration',
                'type' => Domain::TYPE_TEAM,
                'description' => 'Panduan teknis administrasi umum.',
                'icon' => 'shield-check',
                'accent' => 'warning',
                'categories' => [
                    ['name' => 'Onboarding',              'description' => 'Prosedur penerimaan dan orientasi anggota baru.'],
                    ['name' => 'Pengadaan',               'description' => 'Prosedur pengadaan perangkat dan lisensi.'],
                    ['name' => 'Prosedur Administrasi',   'description' => 'Prosedur administrasi umum UPT TIK.'],
                ],
            ],
            [
                'name' => 'Lainnya',
                'type' => Domain::TYPE_TEAM,
                'description' => 'Dokumentasi teknis lintas tim lainnya.',
                'icon' => 'folder',
                'accent' => 'success',
                'categories' => [
                    ['name' => 'Umum', 'description' => 'Dokumen teknis umum lintas tim.'],
                ],
            ],
            // ===== Arsip TA =====
            [
                'name' => 'Arsip TA',
                'type' => Domain::TYPE_ARCHIVE,
                'description' => 'Arsip dokumentasi Tugas Akhir mahasiswa.',
                'icon' => 'folder',
                'accent' => 'success',
                'categories' => [],
            ],
        ];
    }
}
