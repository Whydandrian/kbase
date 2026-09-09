<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Document;
use App\Models\Domain;
use App\Models\Thesis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->domains() as $order => $domain) {
            $domainModel = Domain::updateOrCreate(
                ['slug' => Str::slug($domain['name'])],
                [
                    'name' => $domain['name'],
                    'type' => $domain['type'],
                    'description' => $domain['description'],
                    'icon' => $domain['icon'],
                    'accent' => $domain['accent'],
                    'sort_order' => $order,
                ],
            );

            foreach ($domain['categories'] as $catOrder => $categoryName) {
                $categoryModel = Category::updateOrCreate(
                    ['slug' => Str::slug($domainModel->slug.'-'.$categoryName)],
                    [
                        'domain_id' => $domainModel->id,
                        'name' => $categoryName,
                        'description' => 'Kumpulan dokumen '.Str::lower($categoryName).'.',
                        'icon' => 'folder',
                        'sort_order' => $catOrder,
                    ],
                );

                // The Tata Kelola TI > Dokumen SOP category is filled with real
                // SOP PDF files instead of mock data.
                if ($categoryModel->slug === 'tata-kelola-ti-dokumen-sop') {
                    $this->seedSopDocuments($categoryModel, $domain['owner']);

                    continue;
                }

                // Two to four mock documents per category; ~1 in 5 inactive.
                Document::factory()
                    ->count(fake()->numberBetween(2, 4))
                    ->for($categoryModel)
                    ->create([
                        'owner' => $domain['owner'],
                        'is_active' => fake()->boolean(80),
                    ]);
            }
        }

        $this->seedTheses();
    }

    /**
     * Seed the 10 real SOP PDF documents that live on the private disk under
     * tata_kelola_ti/dokumen_sop.
     */
    private function seedSopDocuments(Category $category, string $owner): void
    {
        $directory = 'tata_kelola_ti/dokumen_sop';
        $disk = Storage::disk('local');

        foreach ($disk->files($directory) as $path) {
            if (! Str::endsWith(Str::lower($path), '.pdf')) {
                continue;
            }

            // "(01) SOP PENYUSUNAN PROGRAM KERJA.pdf" -> "SOP Penyusunan Program Kerja"
            $name = pathinfo($path, PATHINFO_FILENAME);
            $name = trim(preg_replace('/^\(\d+\)\s*/', '', $name));
            $title = $this->titleCasePreservingAcronyms($name);

            Document::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'category_id' => $category->id,
                    'title' => $title,
                    'description' => 'Standar Operasional Prosedur: '.$title.'.',
                    'doc_category' => 'SOP',
                    'owner' => $owner,
                    'icon' => 'file-text',
                    'url' => null,
                    'file_path' => $path,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * Title-case a phrase while keeping known acronyms upper-cased.
     */
    private function titleCasePreservingAcronyms(string $value): string
    {
        $acronyms = ['SOP', 'VPS', 'TIK', 'TI'];

        $words = array_map(function (string $word) use ($acronyms): string {
            $bare = trim($word, ',');
            $suffix = str_ends_with($word, ',') ? ',' : '';

            if (in_array(Str::upper($bare), $acronyms, true)) {
                return Str::upper($bare).$suffix;
            }

            return Str::title(Str::lower($bare)).$suffix;
        }, explode(' ', $value));

        return implode(' ', $words);
    }

    /**
     * @return list<array{name: string, type: string, description: string, icon: string, accent: string, owner: string, categories: list<string>}>
     */
    private function domains(): array
    {
        return [
            // ===== 7 domain — dokumen manajerial =====
            [
                'name' => 'Tata Kelola TI',
                'type' => Domain::TYPE_DOMAIN,
                'description' => 'Kebijakan, struktur, dan tata kelola teknologi informasi.',
                'icon' => 'shield-check',
                'accent' => 'primary',
                'owner' => 'UPT TIK',
                'categories' => [
                    'Manajemen Aset TI',
                    'Dokumen SOP',
                    'Sistem Manajemen Keamanan Informasi',
                    'Dokumen KAK',
                    'Dokumen Standar Sistem Informasi',
                ],
            ],
            [
                'name' => 'Perencanaan UPT TIK',
                'type' => Domain::TYPE_DOMAIN,
                'description' => 'Rencana strategis dan roadmap pengembangan TIK.',
                'icon' => 'trending-up',
                'accent' => 'primary',
                'owner' => 'UPT TIK',
                'categories' => [
                    'Rencana Strategis',
                    'Roadmap Pengembangan',
                    'Rencana Anggaran',
                ],
            ],
            [
                'name' => 'Manajemen Layanan TI',
                'type' => Domain::TYPE_DOMAIN,
                'description' => 'SOP dan katalog layanan teknologi informasi.',
                'icon' => 'layers',
                'accent' => 'primary',
                'owner' => 'UPT TIK',
                'categories' => [
                    'Dokumen SPBE',
                    'Katalog Layanan',
                    'Service Level Agreement',
                    'Prosedur Layanan',
                ],
            ],
            [
                'name' => 'Manajemen Risiko TI',
                'type' => Domain::TYPE_DOMAIN,
                'description' => 'Register risiko dan rencana mitigasi teknologi informasi.',
                'icon' => 'shield-check',
                'accent' => 'warning',
                'owner' => 'UPT TIK',
                'categories' => [
                    'Register Risiko',
                    'Rencana Mitigasi',
                    'Business Continuity Plan',
                ],
            ],
            [
                'name' => 'Dokumentasi Aplikasi',
                'type' => Domain::TYPE_DOMAIN,
                'description' => 'Dokumentasi teknis dan panduan penggunaan aplikasi.',
                'icon' => 'terminal',
                'accent' => 'primary',
                'owner' => 'UPT TIK',
                'categories' => [
                    'Panduan Pengguna',
                    'Dokumentasi API',
                    'Arsitektur Sistem',
                    'Basis Data',
                ],
            ],
            [
                'name' => 'Materi',
                'type' => Domain::TYPE_DOMAIN,
                'description' => 'Materi pendukung dan dokumen lainnya.',
                'icon' => 'folder',
                'accent' => 'success',
                'owner' => 'UPT TIK',
                'categories' => [
                    'Materi Pelatihan',
                    'Materi Sosialisasi',
                    'Dokumen Lainnya',
                ],
            ],

            // ===== Tim — dokumen teknis =====
            [
                'name' => 'Web Developer',
                'type' => Domain::TYPE_TEAM,
                'description' => 'Panduan teknis pengembangan web dan aplikasi.',
                'icon' => 'terminal',
                'accent' => 'success',
                'owner' => 'Web Development',
                'categories' => [
                    'Deployment',
                    'Coding Standard',
                    'Dokumentasi API',
                ],
            ],
            [
                'name' => 'Networking & Infrastructure',
                'type' => Domain::TYPE_TEAM,
                'description' => 'Panduan teknis jaringan dan infrastruktur.',
                'icon' => 'network',
                'accent' => 'primary',
                'owner' => 'Network Team',
                'categories' => [
                    'Konfigurasi Jaringan',
                    'Runbook Insiden',
                    'Keamanan Jaringan',
                ],
            ],
            [
                'name' => 'Administration',
                'type' => Domain::TYPE_TEAM,
                'description' => 'Panduan teknis administrasi umum.',
                'icon' => 'shield-check',
                'accent' => 'warning',
                'owner' => 'Administration',
                'categories' => [
                    'Onboarding',
                    'Pengadaan',
                    'Prosedur Administrasi',
                ],
            ],
            [
                'name' => 'Lainnya',
                'type' => Domain::TYPE_TEAM,
                'description' => 'Dokumentasi teknis lintas tim lainnya.',
                'icon' => 'folder',
                'accent' => 'success',
                'owner' => 'UPT TIK',
                'categories' => [
                    'Umum',
                ],
            ],

            // ===== Arsip TA =====
            [
                'name' => 'Arsip TA',
                'type' => Domain::TYPE_ARCHIVE,
                'description' => 'Arsip dokumentasi Tugas Akhir mahasiswa.',
                'icon' => 'folder',
                'accent' => 'success',
                'owner' => 'UPT TIK',
                'categories' => [],
            ],
        ];
    }

    private function seedTheses(): void
    {
        if (Thesis::query()->exists()) {
            return;
        }

        Thesis::factory()->count(24)->create();
    }
}
