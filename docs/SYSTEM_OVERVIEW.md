# IT Knowledge Base — System Overview

Dokumen ini merangkum arsitektur, struktur data, alur fitur, dan konvensi proyek
**kbase** (IT Knowledge Base UPT TIK). Tujuannya agar pengembang berikutnya —
manusia maupun agen AI — dapat melanjutkan pengembangan tanpa menebak-nebak.

> Terakhir diperbarui: September 2026.

---

## 1. Ringkasan

Portal dokumentasi internal IT. Terdiri dari empat area utama:

1. **Beranda** (`/`) — landing page dengan ringkasan domain, tim, dan Arsip TA.
2. **Domain** (`/domain`) — dokumen **manajerial** tata kelola TI, 3 tingkat:
   `Domain → Kategori → Dokumen`.
3. **Tim** (`/tim`) — dokumen **teknis** per tim, struktur 3 tingkat yang sama.
4. **Arsip TA** (`/arsip-ta`) — arsip Tugas Akhir (tabel terpisah `theses`).
5. **SOP** (`/sop`) — modul pembuatan dokumen SOP terstruktur (format SOP AP),
   hanya terlihat setelah login.

Fitur lintas halaman: **pencarian global** (Ctrl/Cmd + K), **autentikasi admin**
(login-only, tanpa registrasi), dan **export PDF** untuk SOP.

---

## 2. Stack Teknis

| Komponen        | Versi / Pilihan                                    |
|-----------------|----------------------------------------------------|
| PHP             | 8.4                                                |
| Laravel         | 13.x                                               |
| Database        | MySQL (default). SQLite in-memory untuk test.      |
| Frontend build  | Vite 8 + Tailwind CSS 4 (`@import 'tailwindcss'`)  |
| CSS utama       | `resources/css/app.css` (banyak CSS kustom + Tailwind) |
| JS utama        | `resources/js/app.js` (vanilla JS, tanpa framework) |
| Testing         | Pest 5 (Feature test, `RefreshDatabase` aktif)     |
| PDF             | `barryvdh/laravel-dompdf` ^3.1                     |
| Lokal serving   | Laravel Herd (`https://kbase.test`), PHP via Herd  |

> Catatan lingkungan: pada mesin dev, binary PHP berada di
> `~/Library/Application Support/Herd/bin/php84`. Jalankan build/test dengan
> `npm run build` dan `php artisan test` / `vendor/bin/pest`.

---

## 3. Model Data

### 3.1 Hirarki dokumen (manajerial & teknis)

```
Domain (type: domain | team | archive)
  └─ Category (domain_id)
        └─ Document (category_id)
```

**`domains`** — `name`, `slug`, `type` (`domain`/`team`/`archive`), `description`,
`icon`, `accent` (primary|success|warning|danger), `sort_order`.
Relasi: `hasMany Category`, `hasManyThrough Document`.
Scope: `ofType($type)`, `ordered()`. Route key: `slug`.

**`categories`** — `domain_id`, `name`, `slug`, `description`, `icon`, `sort_order`.
Relasi: `belongsTo Domain`, `hasMany Document`. Route key: `slug`.

**`documents`** — `category_id`, `title`, `slug`, `description`, `doc_category`
(label mis. "SOP"), `owner`, `icon`, `url` (tautan eksternal), `file_path`
(PDF privat), `is_active` (visibilitas publik).
Helper: `scopeActive()`, `hasUploadedFile()`, `sourceMode()` → `pdf|url|null`.
Relasi: `belongsTo Category`, `hasOne Sop`.

**Aturan sumber dokumen**: setiap dokumen punya **URL** (dibuka tab baru) **atau**
**PDF** (di-preview via iframe). PDF disimpan di disk privat `local`
(`storage/app/private/documents`), diakses lewat route terkontrol.

### 3.2 Arsip Tugas Akhir

**`theses`** — `title`, `author`, `university`, `study_program`, `year`,
`drive_url` (opsional), `file_path` (PDF, opsional).
Salah satu dari `drive_url` / file PDF wajib diisi.
Scope: `search()`, `forYear()`, `forStudyProgram()`.
Catatan: file Arsip TA saat ini disimpan di disk **public** (`storage/app/public`,
folder `theses/`) — berbeda dari dokumen SOP/Document yang privat.

### 3.3 SOP (format SOP AP / PermenPAN-RB 35/2012)

```
Sop
 ├─ SopApproval  (role: penyusun | pemeriksa | pengesahan) — unik per role
 ├─ SopItem      (type: tujuan | ruang_lingkup | istilah | dasar_hukum |
 │                       kualifikasi | keterkaitan | peralatan | peringatan | pencatatan)
 └─ SopFlowStep  (langkah flowchart + gambar opsional)
```

**`sops`** — `document_id` (tautan opsional ke Document), `nomor_sop`, `nama_sop`,
`unit_pembuat`, `kementerian`, `institusi`, `tgl_pembuatan/revisi/efektif`,
`final_document_path` (PDF final ber-TTD & stempel), `status`
(`draft`/`reviewed`/`published`), `created_by`.
Const: `Sop::ITEM_TYPES`, `Sop::APPROVAL_ROLES`.
Helper: `items()`, `itemsOfType($type)`, `approvalFor($role)`, `flowSteps()`,
`hasFinalDocument()`.

**`sop_approvals`** — `sop_id`, `role`, `user_id`, `nama`, `jabatan`, `tanggal`,
`signature_path` (snapshot gambar TTD), `is_signed`.

**`sop_items`** — `sop_id`, `type`, `content`, `url`, `file_path`, `sort_order`.
Semua bagian naratif (tujuan/ruang lingkup/istilah) DAN daftar (dasar hukum dst)
adalah item **repeatable** di tabel ini.

**`sop_flow_steps`** — `sop_id`, `step_no`, `kegiatan`, `pelaksana`, `is_decision`,
`kelengkapan`, `waktu`, `output`, `keterangan`, `image_path` (gambar flowchart),
`sort_order`.

### 3.4 User & autentikasi

**`users`** — bawaan Laravel + `is_admin` (bool), `jabatan`, `signature_path`
(gambar TTD di disk privat). Helper: `isAdmin()`, `hasSignature()`.
Model memakai atribut PHP: `#[Fillable([...])]`, `#[Hidden([...])]`.

Akun admin default (seeder): **`admin@kbase.test` / `password`** (`is_admin = true`).
> Ganti password ini sebelum produksi.

---

## 4. Otorisasi (penting)

| Aksi                                   | Siapa yang boleh                          |
|----------------------------------------|-------------------------------------------|
| Melihat halaman publik (domain/tim/arsip/SOP list & detail) | Semua orang |
| Menambah Arsip TA (`/arsip-ta` POST)   | Publik (rate-limited `throttle:10,1`)     |
| CRUD Dokumen (store/update/toggle/destroy) | **Administrator** (`admin` middleware) |
| CRUD SOP (create/store/edit/update/destroy) | **Semua user login** (`auth` middleware) |
| Approve SOP (pemeriksa)                | Pemeriksa yang ditunjuk **atau** admin, wajib punya TTD |
| Upload dokumen final SOP               | Semua user login                          |
| Login publik                           | `throttle:6,1`, tanpa registrasi          |

Middleware alias: `admin` → `App\Http\Middleware\EnsureUserIsAdmin` (abort 403).
Terdaftar di `bootstrap/app.php`.

**Visibilitas dokumen**: publik hanya melihat dokumen `is_active = true`; admin
melihat semua. Index pencarian global juga menyembunyikan dokumen nonaktif untuk
non-admin.

---

## 5. Routing

Semua di `routes/web.php`. Nama route penting:

**Knowledge base**
- `knowledge-base` — `GET /`
- `knowledge-base.domains` — `GET /domain`
- `knowledge-base.domain` — `GET /domain/{domain}` (slug)
- `knowledge-base.teams` — `GET /tim`
- `knowledge-base.team` — `GET /tim/{domain}` (slug)
- `knowledge-base.archive` — `GET /arsip-ta`
- `knowledge-base.archive.store` — `POST /arsip-ta`

**Dokumen (admin)**
- `documents.store` — `POST /kategori/{category}/dokumen`
- `documents.update` — `PUT /dokumen/{document}`
- `documents.toggle` — `PATCH /dokumen/{document}/toggle`
- `documents.destroy` — `DELETE /dokumen/{document}`
- `documents.file` — `GET /dokumen/{document}/berkas` (stream PDF privat, akses dicek)

**Auth**
- `login` — `GET /login`
- `login.store` — `POST /login`
- `logout` — `POST /logout`

**SOP**
- `sop.index` — `GET /sop`
- `sop.show` — `GET /sop/{sop}`
- `sop.pdf` — `GET /sop/{sop}/pdf` (export dompdf)
- `sop.file` — `GET /sop/{sop}/berkas/{kind}/{id?}` (kind: final|item|step|signature)
- `sop.create` — `GET /sop-baru/create` (auth)
- `sop.store` — `POST /sop` (auth)
- `sop.edit` / `sop.update` / `sop.destroy` (auth)
- `sop.approve` — `POST /sop/{sop}/approve` (auth, cek pemeriksa)
- `sop.final` — `POST /sop/{sop}/final` (auth, upload PDF final)

---

## 6. Struktur Berkas Kunci

```
app/
  Http/Controllers/
    KnowledgeBaseController.php   # beranda, domain/tim index+detail, arsip, store thesis
    DocumentController.php        # CRUD dokumen + stream file privat
    SopController.php             # CRUD SOP, approve, upload final, PDF, stream file
    Auth/LoginController.php      # login/logout
  Http/Middleware/EnsureUserIsAdmin.php
  Http/Requests/
    StoreThesisRequest.php
    StoreDocumentRequest.php      # wajib URL atau PDF (pada store)
    StoreSopRequest.php
  Models/  Domain, Category, Document, Thesis, Sop, SopApproval, SopItem, SopFlowStep, User
  Support/SearchIndex.php         # membangun index pencarian global (domain+dokumen+thesis)

resources/
  views/
    components/knowledge-base/layout.blade.php   # layout + nav + footer + search overlay
    knowledge-base/ home, domain-index, domain-show, archive .blade.php
    sop/ index, show, form, pdf .blade.php       # pdf = template dompdf
    auth/login.blade.php
    components/icon.blade.php                     # ikon Lucide inline (x-icon)
  css/app.css
  js/app.js                                       # search global, filter, modal, form dinamis SOP

database/
  migrations/  (domains, categories, documents, theses, users tweaks, sop*)
  factories/   (Domain, Category, Document, Thesis, Sop)
  seeders/     DatabaseSeeder → KnowledgeBaseSeeder, SopSeeder
```

---

## 7. Frontend

- **Palette warna** didefinisikan sebagai CSS variables di `:root` (`--primary-*`,
  `--success-*`, `--warning-*`, `--danger-*`) + alias `--brand*` (primary sebagai
  warna utama = primary-600). Lihat bagian atas `app.css`.
- **Ikon**: komponen `<x-icon name="..." :size="..." />` (Lucide inline SVG).
  Nama ikon yang tersedia terdaftar di `resources/views/components/icon.blade.php`.
- **Responsif**: mobile (< 761px) sidebar jadi drawer; tabel Arsip TA jadi kartu.
  Top bar sticky; footer sticky di dasar (flex column `#app`).
- **Pencarian global**: overlay dipicu tombol nav atau Ctrl/Cmd + K (deteksi OS
  untuk label). Sumber data: `SearchIndex::build()` di-share ke layout via
  View Composer (`AppServiceProvider`).
- **Form SOP dinamis**: baris item & langkah flowchart repeatable (tambah/hapus)
  memakai `<template>` (`tpl-item-row`, `tpl-step-row`) + logika di `app.js`.

---

## 8. Penyimpanan Berkas

- Disk **`local`** = `storage/app/private` (PRIVAT). Dipakai untuk: PDF dokumen
  (`documents/`), berkas & gambar SOP (`sops/final`, `sops/flow`), snapshot TTD.
  Akses hanya lewat route streaming yang mengecek izin.
- Disk **`public`** = `storage/app/public` (via `php artisan storage:link`).
  Dipakai untuk file Arsip TA (`theses/`).
- Jangan tautkan file privat langsung ke publik; selalu lewat controller.

---

## 9. Seeder & Data Contoh

`php artisan migrate:fresh --seed` mengisi:
- 6 domain manajerial (Tata Kelola TI, Perencanaan UPT TIK, Manajemen Layanan TI,
  Manajemen Risiko TI, Dokumentasi Aplikasi, Materi) + kategori + dokumen mock.
- Kategori **Tata Kelola TI → Dokumen SOP** diisi **10 file SOP asli** dari
  `storage/app/private/tata_kelola_ti/dokumen_sop/*.pdf` (judul diturunkan dari
  nama file, akronim SOP/VPS/TIK dipertahankan).
- 4 tim (Web Developer, Networking & Infrastructure, Administration, Lainnya).
- 24 arsip TA acak (lintas prodi & tahun).
- 1 SOP terstruktur lengkap ("Penyusunan Program Kerja", nomor
  `243/IT10.IV/OT.07/2021`) dari data PDF asli: 3 approval, item (tujuan 3,
  ruang lingkup 2, istilah 3, dasar hukum 5, dll), 8 langkah flowchart.
- 1 user admin.

---

## 10. Testing

- Framework: Pest 5. `RefreshDatabase` aktif untuk `tests/Feature`.
- File: `KnowledgeBasePageTest`, `DocumentManagementTest`, `SopTest`.
- Jalankan: `php artisan test --compact` atau `vendor/bin/pest`.
- Saat ini: **27 test lolos**.
- Catatan: karena index pencarian global memuat semua judul, hindari
  `assertDontSee` pada seluruh halaman untuk konten yang tersembunyi dari daftar —
  assert pada markup elemen yang terlihat (mis. `>Judul<`).

---

## 11. Konvensi Proyek (wajib diikuti)

- Ikuti Laravel Boost guidelines (`AGENTS.md`) & aturan di `.ai/rules` bila ada.
- Buat berkas via `php artisan make:*`. Model baru + factory + (jika perlu) seeder.
- Format PHP: **jalankan `vendor/bin/pint --dirty`** sebelum menyelesaikan perubahan.
- Setiap perubahan disertai test yang relevan; jalankan test terkait sampai hijau.
- Nama deskriptif; PHP 8 constructor promotion; return type & type hint eksplisit;
  kurung kurawal pada semua control structure.
- Untuk API/link internal, pakai named route (`route()`).
- Simpan durable rule via Boost `record-rule` (bukan catatan pribadi).

---

## 12. Ide Pengembangan Lanjutan

- Halaman **profil**: petugas mengunggah tanda tangan sendiri (`users.signature_path`)
  agar tombol "Setujui" SOP langsung bisa dipakai.
- CRUD **Domain & Kategori** untuk admin (saat ini hanya via seeder).
- Proteksi/moderasi form **Arsip TA** (kini publik).
- Peran (role) lebih granular selain `is_admin`.
- Editor **flowchart visual** (kini berupa tabel langkah + gambar).
- Pindahkan file Arsip TA ke disk privat bila perlu kontrol akses.
```
