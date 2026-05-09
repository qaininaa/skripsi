# Struktur Proyek Aplikasi Skripsi

Aplikasi Laravel untuk manajemen laporan laboratorium dengan sistem peran (role-based).

---

## Peran (Role) Pengguna

| Role          | Akses                                          |
| ------------- | ---------------------------------------------- |
| `super-admin` | Manajemen user, master data, pengaturan sistem |
| `admin-qc`    | Manajemen jenis laporan, tugas pelaporan       |
| `analis`      | Mengisi & submit laporan                       |
| `supervisor`  | Review & tanda tangan laporan                  |
| `manajer`     | Melihat & menyetujui laporan final             |

---

## `app/`

### `Models/`

| File                  | Tabel                | Keterangan                                          |
| --------------------- | -------------------- | --------------------------------------------------- |
| `User.php`            | `users`              | Pengguna aplikasi, memiliki role & password history |
| `ReportType.php`      | `report_types`       | Jenis laporan (misal: Laporan PCR, dll)             |
| `ReportSection.php`   | `sections`           | Bagian/seksi dalam jenis laporan                    |
| `ReportLocation.php`  | `locations`          | Lokasi/ruang dalam setiap seksi                     |
| `Room.php`            | `rooms`              | Data ruangan laboratorium                           |
| `Frequency.php`       | `frequencies`        | Frekuensi pelaporan (harian, mingguan, dll)         |
| `Report.php`          | `reports`            | Header laporan yang dibuat analis                   |
| `ReportEntry.php`     | `report_entries`     | Isi/data tiap baris laporan                         |
| `ReportApproval.php`  | `report_approvals`   | Rekam jejak persetujuan/tanda tangan laporan        |
| `AuditLog.php`        | `audit_logs`         | Log aktivitas seluruh pengguna                      |
| `PasswordHistory.php` | `password_histories` | Riwayat password untuk cegah reuse                  |
| `PasswordSetting.php` | `password_settings`  | Konfigurasi aturan password                         |

### `Http/Controllers/`

| File                                 | Keterangan                                      |
| ------------------------------------ | ----------------------------------------------- |
| `AnalisLaporanController.php`        | Analis: buat, isi, edit, submit laporan         |
| `ArsipLaporanController.php`         | Arsip laporan yang sudah selesai                |
| `AuditLogController.php`             | Tampilkan log aktivitas (super-admin)           |
| `LokasiController.php`               | CRUD master data lokasi                         |
| `ManajerLaporanController.php`       | Manajer: lihat & setujui laporan                |
| `PasswordSettingController.php`      | Pengaturan aturan password                      |
| `ProfileController.php`              | Edit profil & ganti password                    |
| `ReportTypeManagementController.php` | CRUD jenis laporan, seksi, dan lokasi per seksi |
| `RuanganController.php`              | CRUD master data ruangan                        |
| `SupervisorLaporanController.php`    | Supervisor: review & tanda tangan laporan       |
| `TugasPelaporanController.php`       | Penugasan analis ke jenis laporan tertentu      |
| `UserManagementController.php`       | CRUD manajemen user (super-admin)               |
| `Auth/`                              | Controller login, logout, ganti password paksa  |

### `Http/Middleware/`

| File                       | Keterangan                                            |
| -------------------------- | ----------------------------------------------------- |
| `RoleMiddleware.php`       | Cek role pengguna sebelum akses route                 |
| `CheckPasswordExpired.php` | Redirect ke halaman ganti password jika sudah expired |

### `Http/Requests/`

| File                       | Keterangan                             |
| -------------------------- | -------------------------------------- |
| `ProfileUpdateRequest.php` | Validasi update profil                 |
| `Auth/`                    | Request untuk autentikasi (login, dll) |

### `Rules/`

| File                     | Keterangan                                 |
| ------------------------ | ------------------------------------------ |
| `PasswordComplexity.php` | Custom rule validasi kompleksitas password |

### `View/Components/`

| File              | Keterangan                          |
| ----------------- | ----------------------------------- |
| `AppLayout.php`   | Layout utama aplikasi (sudah login) |
| `GuestLayout.php` | Layout halaman tamu (login, dll)    |

### `Providers/`

| File                     | Keterangan                                |
| ------------------------ | ----------------------------------------- |
| `AppServiceProvider.php` | Boot & register service provider aplikasi |

---

## `database/`

### `migrations/` — Urutan pembuatan tabel

| File                                            | Tabel yang dibuat                            |
| ----------------------------------------------- | -------------------------------------------- |
| `0001_01_01_000000_create_users_table.php`      | `users`, `password_reset_tokens`, `sessions` |
| `0001_01_01_000001_create_cache_table.php`      | `cache`, `cache_locks`                       |
| `2026_03_13_create_audit_logs.php`              | `audit_logs`                                 |
| `2026_03_28_000001_create_report_types.php`     | `report_types`                               |
| `2026_03_28_000002_create_sections.php`         | `sections`                                   |
| `2026_03_28_000003_create_rooms.php`            | `rooms`                                      |
| `2026_03_28_000004_create_frequencies.php`      | `frequencies`                                |
| `2026_03_28_000005_create_locations.php`        | `locations`                                  |
| `2026_03_28_000006_create_report_section.php`   | `report_section` (pivot: section ↔ location) |
| `2026_03_28_000007_create_reports.php`          | `reports`                                    |
| `2026_03_28_000008_create_report_entries.php`   | `report_entries`                             |
| `2026_03_28_000009_create_report_approvals.php` | `report_approvals`                           |
| `2026_03_31_create_password_histories.php`      | `password_histories`                         |
| `2026_04_06_create_password_settings.php`       | `password_settings`                          |

### `seeders/`

| File                              | Keterangan                             |
| --------------------------------- | -------------------------------------- |
| `DatabaseSeeder.php`              | Entry point, memanggil semua seeder    |
| `AccountSeeder.php`               | Data akun user awal (super-admin, dll) |
| `FrequencySeeder.php`             | Data frekuensi pelaporan               |
| `RoomSeeder.php`                  | Data ruangan laboratorium              |
| `LocationSeeder.php`              | Data lokasi per seksi                  |
| `ReportTypeSectionSeeder.php`     | Data jenis laporan beserta seksinya    |
| `ReportSectionLocationSeeder.php` | Relasi seksi ↔ lokasi                  |
| `PasswordSettingSeeder.php`       | Pengaturan password default            |

---

## `resources/views/`

### `layouts/`

| File                | Keterangan                              |
| ------------------- | --------------------------------------- |
| `app.blade.php`     | Layout utama: sidebar + header + konten |
| `header.blade.php`  | Bagian header (navbar atas)             |
| `sidebar.blade.php` | Sidebar navigasi kiri                   |

### `auth/`

| File                        | Keterangan                              |
| --------------------------- | --------------------------------------- |
| `login.blade.php`           | Halaman login                           |
| `change-password.blade.php` | Halaman ganti password (paksa/sukarela) |

### `components/`

Komponen Blade yang bisa dipakai ulang di seluruh view:
`primary-button`, `secondary-button`, `danger-button`, `text-input`, `input-label`, `input-error`, `modal`, `delete-modal`, `dropdown`, `nav-link`, `welcome-banner`, dll.

### `pages/`

#### `dashboard/`

| File                    | Keterangan                  |
| ----------------------- | --------------------------- |
| `analis.blade.php`      | Dashboard untuk analis      |
| `admin-qc.blade.php`    | Dashboard untuk admin QC    |
| `super-admin.blade.php` | Dashboard untuk super admin |

#### `laporan/`

| File                                   | Keterangan                       |
| -------------------------------------- | -------------------------------- |
| `index.blade.php`                      | Daftar laporan milik analis      |
| `isi.blade.php`                        | Form pengisian isi laporan       |
| `partials/action-bar.blade.php`        | Tombol aksi (simpan, submit)     |
| `partials/bottom-bar.blade.php`        | Bar bagian bawah form            |
| `partials/modals.blade.php`            | Modal konfirmasi (submit, dll)   |
| `partials/scripts.blade.php`           | Script JS khusus halaman laporan |
| `partials/section-tabel.blade.php`     | Tampilan seksi berbentuk tabel   |
| `partials/section-alat.blade.php`      | Tampilan seksi data alat         |
| `partials/section-medium.blade.php`    | Tampilan seksi data medium       |
| `partials/section-inkubator.blade.php` | Tampilan seksi data inkubator    |
| `partials/section-catatan.blade.php`   | Tampilan seksi catatan           |
| `partials/section-info.blade.php`      | Tampilan seksi informasi umum    |
| `partials/section-ttd.blade.php`       | Tampilan seksi tanda tangan      |

#### `supervisor/`

| File                      | Keterangan                          |
| ------------------------- | ----------------------------------- |
| `index.blade.php`         | Daftar laporan yang perlu di-review |
| `laporan-masuk.blade.php` | Laporan masuk menunggu review       |
| `laporan-show.blade.php`  | Detail laporan untuk ditandatangani |
| `laporan-cetak.blade.php` | Tampilan cetak laporan              |

#### `manajer/`

| File                      | Keterangan                         |
| ------------------------- | ---------------------------------- |
| `index.blade.php`         | Daftar laporan untuk manajer       |
| `laporan-masuk.blade.php` | Laporan masuk menunggu persetujuan |
| `laporan-show.blade.php`  | Detail laporan untuk disetujui     |

#### `arsip/`

| File              | Keterangan                   |
| ----------------- | ---------------------------- |
| `index.blade.php` | Daftar arsip laporan selesai |
| `show.blade.php`  | Detail laporan arsip         |

#### `report-types/`

| File               | Keterangan                                   |
| ------------------ | -------------------------------------------- |
| `index.blade.php`  | Daftar semua jenis laporan                   |
| `create.blade.php` | Form buat jenis laporan baru                 |
| `edit.blade.php`   | Form edit jenis laporan                      |
| `show.blade.php`   | Detail jenis laporan + kelola seksi & lokasi |

#### `master/lokasi/`

| File               | Keterangan           |
| ------------------ | -------------------- |
| `index.blade.php`  | Daftar master lokasi |
| `create.blade.php` | Form tambah lokasi   |
| `edit.blade.php`   | Form edit lokasi     |

#### `master/ruangan/`

| File               | Keterangan            |
| ------------------ | --------------------- |
| `index.blade.php`  | Daftar master ruangan |
| `create.blade.php` | Form tambah ruangan   |
| `edit.blade.php`   | Form edit ruangan     |

#### `users/`

| File               | Keterangan            |
| ------------------ | --------------------- |
| `index.blade.php`  | Daftar semua user     |
| `create.blade.php` | Form tambah user baru |
| `edit.blade.php`   | Form edit user        |

#### `tugas-pelaporan/`

| File               | Keterangan                 |
| ------------------ | -------------------------- |
| `index.blade.php`  | Daftar penugasan pelaporan |
| `create.blade.php` | Form buat tugas baru       |
| `edit.blade.php`   | Form edit tugas            |

#### `settings/`

| File              | Keterangan                  |
| ----------------- | --------------------------- |
| `index.blade.php` | Halaman pengaturan password |

#### `audit-logs/`

| File              | Keterangan                      |
| ----------------- | ------------------------------- |
| `index.blade.php` | Tampilan log aktivitas pengguna |

### `partials/`

| File                               | Keterangan                      |
| ---------------------------------- | ------------------------------- |
| `report-signature-grid.blade.php`  | Grid tanda tangan laporan       |
| `report-signature-print.blade.php` | Layout tanda tangan untuk cetak |

---

## `routes/`

| File          | Keterangan                                                        |
| ------------- | ----------------------------------------------------------------- |
| `web.php`     | Semua route utama aplikasi (dashboard, laporan, master data, dll) |
| `auth.php`    | Route autentikasi (login, logout, ganti password)                 |
| `console.php` | Route artisan console commands                                    |

---

## Relasi Antar Tabel (Ringkasan)

```
report_types
  └── sections (hasMany)
        └── report_section (pivot: section ↔ locations)
              └── locations

reports
  ├── dibuat oleh: users (analis)
  ├── jenis: report_types
  ├── frekuensi: frequencies
  ├── report_entries (isi data per seksi/lokasi)
  └── report_approvals (tanda tangan supervisor & manajer)
```
