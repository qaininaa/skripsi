# Product Backlog & Sprint Backlog

**Aplikasi Manajemen Laporan Laboratorium**
**Total: 4 Sprint × 4 Minggu = 16 Minggu**

---

## PRODUCT BACKLOG

Product Backlog diurutkan berdasarkan prioritas dan ketergantungan antar fitur.

| ID    | Fitur / User Story                                                               | Prioritas | Story Points | Sprint |
| ----- | -------------------------------------------------------------------------------- | --------- | ------------ | ------ |
| PB-01 | Setup project: instalasi Laravel, konfigurasi database, migrasi semua tabel      | Tinggi    | 5            | 1      |
| PB-02 | Seeder data awal (akun, frekuensi, ruangan, lokasi, jenis laporan)               | Tinggi    | 3            | 1      |
| PB-03 | Halaman login & logout                                                           | Tinggi    | 2            | 1      |
| PB-04 | Middleware role-based access control (RoleMiddleware)                            | Tinggi    | 3            | 1      |
| PB-05 | Middleware cek password expired (CheckPasswordExpired)                           | Tinggi    | 2            | 1      |
| PB-06 | Halaman ganti password (paksa & sukarela)                                        | Tinggi    | 3            | 1      |
| PB-07 | Validasi kompleksitas password (PasswordComplexity rule)                         | Tinggi    | 2            | 1      |
| PB-08 | Riwayat password — cegah reuse password lama                                     | Tinggi    | 2            | 1      |
| PB-09 | Layout utama aplikasi (sidebar, header, app layout)                              | Tinggi    | 3            | 1      |
| PB-10 | Komponen Blade reusable (button, input, modal, dropdown, dll)                    | Tinggi    | 3            | 1      |
| PB-11 | Manajemen user: lihat daftar, tambah, edit, hapus (Super Admin)                  | Tinggi    | 5            | 1      |
| PB-12 | Edit profil & ganti password sendiri (semua role)                                | Sedang    | 2            | 1      |
| PB-13 | Audit log: catat semua aktivitas pengguna                                        | Tinggi    | 3            | 1      |
| PB-14 | Halaman daftar audit log (Super Admin)                                           | Sedang    | 2            | 1      |
| PB-15 | Pengaturan aturan password (min length, complexity, expiry, reuse) (Super Admin) | Sedang    | 3            | 2      |
| PB-16 | Dashboard Super Admin                                                            | Sedang    | 2            | 2      |
| PB-17 | Dashboard Admin QC                                                               | Sedang    | 2            | 2      |
| PB-18 | Master Ruangan: lihat, tambah, edit, hapus (Admin QC)                            | Tinggi    | 3            | 2      |
| PB-19 | Master Lokasi: lihat, tambah, edit, hapus (Admin QC)                             | Tinggi    | 3            | 2      |
| PB-20 | Manajemen Jenis Laporan: lihat, tambah, edit, hapus (Admin QC)                   | Tinggi    | 5            | 2      |
| PB-21 | Manajemen Seksi per Jenis Laporan: tambah, edit, hapus                           | Tinggi    | 4            | 2      |
| PB-22 | Manajemen Lokasi per Seksi: tambah, hapus                                        | Tinggi    | 3            | 2      |
| PB-23 | Tugas Pelaporan: Admin QC assign laporan ke Analis                               | Tinggi    | 5            | 3      |
| PB-24 | Fitur duplikasi & hapus seksi pada tugas pelaporan                               | Sedang    | 3            | 3      |
| PB-25 | Dashboard Analis                                                                 | Tinggi    | 2            | 3      |
| PB-26 | Analis: lihat daftar laporan yang ditugaskan                                     | Tinggi    | 3            | 3      |
| PB-27 | Analis: form pengisian laporan (semua tipe seksi)                                | Tinggi    | 13           | 3      |
| PB-28 | Analis: simpan draft laporan                                                     | Tinggi    | 3            | 3      |
| PB-29 | Analis: submit laporan dengan verifikasi password                                | Tinggi    | 3            | 3      |
| PB-30 | Preview laporan sebelum submit (Analis & Admin QC)                               | Sedang    | 2            | 3      |
| PB-31 | Dashboard Supervisor                                                             | Tinggi    | 2            | 4      |
| PB-32 | Supervisor: lihat laporan masuk                                                  | Tinggi    | 3            | 4      |
| PB-33 | Supervisor: review detail laporan                                                | Tinggi    | 3            | 4      |
| PB-34 | Supervisor: tanda tangan & setujui laporan                                       | Tinggi    | 4            | 4      |
| PB-35 | Supervisor: kembalikan laporan ke analis (return/revisi)                         | Tinggi    | 3            | 4      |
| PB-36 | Supervisor: cetak laporan (print layout)                                         | Sedang    | 3            | 4      |
| PB-37 | Dashboard Manajer                                                                | Tinggi    | 2            | 4      |
| PB-38 | Manajer: lihat laporan masuk                                                     | Tinggi    | 3            | 4      |
| PB-39 | Manajer: review & setujui laporan final                                          | Tinggi    | 4            | 4      |
| PB-40 | Manajer: kembalikan laporan ke analis                                            | Sedang    | 2            | 4      |
| PB-41 | Manajer: cetak laporan                                                           | Sedang    | 2            | 4      |
| PB-42 | Arsip laporan: lihat daftar laporan selesai (semua role)                         | Sedang    | 3            | 4      |
| PB-43 | Arsip laporan: lihat detail laporan arsip                                        | Sedang    | 2            | 4      |
| PB-44 | Komponen tanda tangan grid & print layout                                        | Sedang    | 3            | 4      |
| PB-45 | Testing, bug fixing, UI polish keseluruhan                                       | Sedang    | 5            | 4      |

---

## SPRINT BACKLOG

---

### SPRINT 1 — Fondasi, Autentikasi & Manajemen Pengguna

**Periode:** Minggu 1 – 4
**Goal:** Aplikasi bisa diakses, sistem login & role berjalan, Super Admin bisa kelola user dan lihat log aktivitas.

| ID Task | PBI Ref | Task                                                                                                                                                                                                   | PIC | Estimasi | Status |
| ------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | --- | -------- | ------ |
| S1-T01  | PB-01   | Instalasi Laravel, konfigurasi `.env`, koneksi database                                                                                                                                                | Dev | 4 jam    | -      |
| S1-T02  | PB-01   | Buat semua file migration (users, audit_logs, report_types, sections, rooms, frequencies, locations, report_section, reports, report_entries, report_approvals, password_histories, password_settings) | Dev | 6 jam    | -      |
| S1-T03  | PB-01   | Jalankan migrasi & verifikasi struktur tabel                                                                                                                                                           | Dev | 1 jam    | -      |
| S1-T04  | PB-02   | Buat seeder: AccountSeeder, FrequencySeeder, RoomSeeder, LocationSeeder, ReportTypeSectionSeeder, ReportSectionLocationSeeder, PasswordSettingSeeder                                                   | Dev | 5 jam    | -      |
| S1-T05  | PB-03   | Halaman login (view: `auth/login.blade.php`)                                                                                                                                                           | Dev | 3 jam    | -      |
| S1-T06  | PB-03   | Logic login/logout (Auth controller)                                                                                                                                                                   | Dev | 2 jam    | -      |
| S1-T07  | PB-04   | Buat `RoleMiddleware` & daftarkan di `bootstrap/app.php`                                                                                                                                               | Dev | 3 jam    | -      |
| S1-T08  | PB-04   | Terapkan middleware role ke semua route group di `web.php`                                                                                                                                             | Dev | 2 jam    | -      |
| S1-T09  | PB-05   | Buat `CheckPasswordExpired` middleware                                                                                                                                                                 | Dev | 2 jam    | -      |
| S1-T10  | PB-06   | Halaman ganti password paksa (`auth/change-password.blade.php`)                                                                                                                                        | Dev | 3 jam    | -      |
| S1-T11  | PB-06   | Logic ganti password paksa di Auth controller                                                                                                                                                          | Dev | 2 jam    | -      |
| S1-T12  | PB-07   | Buat custom rule `PasswordComplexity.php`                                                                                                                                                              | Dev | 3 jam    | -      |
| S1-T13  | PB-08   | Cek riwayat password saat ganti password (model `PasswordHistory`)                                                                                                                                     | Dev | 3 jam    | -      |
| S1-T14  | PB-09   | Buat layout utama: `layouts/app.blade.php`, `header.blade.php`, `sidebar.blade.php`                                                                                                                    | Dev | 5 jam    | -      |
| S1-T15  | PB-09   | Buat `AppLayout.php` & `GuestLayout.php` (View Components)                                                                                                                                             | Dev | 2 jam    | -      |
| S1-T16  | PB-10   | Buat komponen Blade: button (primary, secondary, danger), text-input, input-label, input-error                                                                                                         | Dev | 3 jam    | -      |
| S1-T17  | PB-10   | Buat komponen Blade: modal, delete-modal, dropdown, nav-link, welcome-banner                                                                                                                           | Dev | 3 jam    | -      |
| S1-T18  | PB-10   | Konfigurasi Tailwind CSS & Vite                                                                                                                                                                        | Dev | 2 jam    | -      |
| S1-T19  | PB-11   | Buat `UserManagementController`: index, create, store, edit, update, destroy                                                                                                                           | Dev | 6 jam    | -      |
| S1-T20  | PB-11   | Buat views manajemen user: `users/index.blade.php`, `create.blade.php`, `edit.blade.php`                                                                                                               | Dev | 5 jam    | -      |
| S1-T21  | PB-12   | Buat `ProfileController`: edit, update, destroy                                                                                                                                                        | Dev | 3 jam    | -      |
| S1-T22  | PB-12   | Buat view profil & `ProfileUpdateRequest`                                                                                                                                                              | Dev | 2 jam    | -      |
| S1-T23  | PB-13   | Buat model `AuditLog.php` & helper/observer untuk catat aktivitas                                                                                                                                      | Dev | 4 jam    | -      |
| S1-T24  | PB-14   | Buat `AuditLogController` & view `audit-logs/index.blade.php`                                                                                                                                          | Dev | 3 jam    | -      |

**Total Estimasi Sprint 1:** ~77 jam
**Story Points Sprint 1:** 35 SP

---

### SPRINT 2 — Master Data & Konfigurasi Sistem

**Periode:** Minggu 5 – 8
**Goal:** Admin QC bisa kelola data master (ruangan, lokasi) dan konfigurasi jenis laporan beserta struktur seksi & lokasinya. Super Admin bisa atur rules password.

| ID Task | PBI Ref | Task                                                                                                             | PIC | Estimasi | Status |
| ------- | ------- | ---------------------------------------------------------------------------------------------------------------- | --- | -------- | ------ |
| S2-T01  | PB-15   | Buat `PasswordSettingController`: index, update                                                                  | Dev | 3 jam    | -      |
| S2-T02  | PB-15   | Buat view `settings/index.blade.php` (form pengaturan password)                                                  | Dev | 3 jam    | -      |
| S2-T03  | PB-15   | Integrasikan password settings ke validasi PasswordComplexity & expired check                                    | Dev | 3 jam    | -      |
| S2-T04  | PB-16   | Buat view `dashboard/super-admin.blade.php` (ringkasan statistik user & log)                                     | Dev | 3 jam    | -      |
| S2-T05  | PB-17   | Buat view `dashboard/admin-qc.blade.php` (ringkasan jenis laporan & tugas)                                       | Dev | 3 jam    | -      |
| S2-T06  | PB-18   | Buat `RuanganController`: index, create, store, edit, update, destroy                                            | Dev | 4 jam    | -      |
| S2-T07  | PB-18   | Buat views ruangan: `master/ruangan/index.blade.php`, `create.blade.php`, `edit.blade.php`                       | Dev | 4 jam    | -      |
| S2-T08  | PB-19   | Buat `LokasiController`: index, create, store, edit, update, destroy                                             | Dev | 4 jam    | -      |
| S2-T09  | PB-19   | Buat views lokasi: `master/lokasi/index.blade.php`, `create.blade.php`, `edit.blade.php`                         | Dev | 4 jam    | -      |
| S2-T10  | PB-20   | Buat `ReportTypeManagementController`: index, create, store, edit, update, destroy                               | Dev | 5 jam    | -      |
| S2-T11  | PB-20   | Buat views jenis laporan: `report-types/index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php` | Dev | 6 jam    | -      |
| S2-T12  | PB-20   | Buat model `ReportType.php` dengan relasi ke sections                                                            | Dev | 2 jam    | -      |
| S2-T13  | PB-21   | Tambahkan method `storeSection`, `updateSection`, `destroySection` ke controller                                 | Dev | 4 jam    | -      |
| S2-T14  | PB-21   | Buat model `ReportSection.php` dengan relasi ke ReportType & ReportLocation                                      | Dev | 2 jam    | -      |
| S2-T15  | PB-21   | Tampilkan form kelola seksi di `report-types/show.blade.php`                                                     | Dev | 4 jam    | -      |
| S2-T16  | PB-22   | Tambahkan method `storeLocation`, `destroyLocation` ke controller                                                | Dev | 3 jam    | -      |
| S2-T17  | PB-22   | Buat model `ReportLocation.php` dengan relasi many-to-many ke sections                                           | Dev | 2 jam    | -      |
| S2-T18  | PB-22   | Tampilkan form kelola lokasi per seksi di `show.blade.php`                                                       | Dev | 3 jam    | -      |
| S2-T19  | -       | Buat model `Room.php`, `Frequency.php`                                                                           | Dev | 1 jam    | -      |
| S2-T20  | -       | Testing integrasi: validasi route & middleware semua fitur sprint 2                                              | Dev | 4 jam    | -      |

**Total Estimasi Sprint 2:** ~67 jam
**Story Points Sprint 2:** 35 SP

---

### SPRINT 3 — Tugas Pelaporan & Pengisian Laporan (Analis)

**Periode:** Minggu 9 – 12
**Goal:** Admin QC bisa assign tugas laporan ke Analis. Analis bisa mengisi, menyimpan draft, dan submit laporan.

| ID Task | PBI Ref | Task                                                                                                | PIC | Estimasi | Status |
| ------- | ------- | --------------------------------------------------------------------------------------------------- | --- | -------- | ------ |
| S3-T01  | PB-23   | Buat `TugasPelaporanController`: index, create, store, edit, update, destroy                        | Dev | 6 jam    | -      |
| S3-T02  | PB-23   | Buat model `Report.php` dengan relasi ke ReportType, User, Frequency, ReportEntry, ReportApproval   | Dev | 3 jam    | -      |
| S3-T03  | PB-23   | Buat views tugas pelaporan: `tugas-pelaporan/index.blade.php`, `create.blade.php`, `edit.blade.php` | Dev | 6 jam    | -      |
| S3-T04  | PB-23   | Logic assign laporan ke analis: pilih jenis laporan, analis, periode, frekuensi                     | Dev | 4 jam    | -      |
| S3-T05  | PB-24   | Implement `duplicateSection` & `removeSection` di TugasPelaporanController                          | Dev | 4 jam    | -      |
| S3-T06  | PB-25   | Buat view `dashboard/analis.blade.php` (daftar laporan pending & status)                            | Dev | 3 jam    | -      |
| S3-T07  | PB-26   | Implement `AnalisLaporanController::index` — daftar laporan milik analis                            | Dev | 3 jam    | -      |
| S3-T08  | PB-26   | Buat view `laporan/index.blade.php` dengan filter status                                            | Dev | 4 jam    | -      |
| S3-T09  | PB-27   | Implement `AnalisLaporanController::isi` — load laporan dengan seksi & lokasi                       | Dev | 5 jam    | -      |
| S3-T10  | PB-27   | Buat partial `section-tabel.blade.php` (seksi berbentuk tabel data)                                 | Dev | 4 jam    | -      |
| S3-T11  | PB-27   | Buat partial `section-alat.blade.php` (data kondisi alat)                                           | Dev | 3 jam    | -      |
| S3-T12  | PB-27   | Buat partial `section-medium.blade.php` (data medium)                                               | Dev | 3 jam    | -      |
| S3-T13  | PB-27   | Buat partial `section-inkubator.blade.php` (data inkubator)                                         | Dev | 3 jam    | -      |
| S3-T14  | PB-27   | Buat partial `section-catatan.blade.php` (catatan teks bebas)                                       | Dev | 2 jam    | -      |
| S3-T15  | PB-27   | Buat partial `section-info.blade.php` (informasi umum laporan)                                      | Dev | 2 jam    | -      |
| S3-T16  | PB-27   | Buat partial `section-ttd.blade.php` (area tanda tangan di form isi)                                | Dev | 2 jam    | -      |
| S3-T17  | PB-27   | Buat partial `action-bar.blade.php` & `bottom-bar.blade.php`                                        | Dev | 2 jam    | -      |
| S3-T18  | PB-27   | Buat model `ReportEntry.php` dengan relasi ke Report & ReportSection                                | Dev | 2 jam    | -      |
| S3-T19  | PB-28   | Implement `AnalisLaporanController::save` — simpan semua entry ke `report_entries`                  | Dev | 5 jam    | -      |
| S3-T20  | PB-29   | Implement submit laporan (ubah status → `submitted`) dengan verifikasi password                     | Dev | 4 jam    | -      |
| S3-T21  | PB-29   | Implement `verifyPassword` endpoint & modal konfirmasi submit                                       | Dev | 3 jam    | -      |
| S3-T22  | PB-29   | Buat partial `modals.blade.php` (modal submit & konfirmasi)                                         | Dev | 2 jam    | -      |
| S3-T23  | PB-29   | Buat partial `scripts.blade.php` (JS: AJAX save, validasi form)                                     | Dev | 4 jam    | -      |
| S3-T24  | PB-30   | Implement `AnalisLaporanController::lihat` — view-only laporan                                      | Dev | 2 jam    | -      |
| S3-T25  | -       | Testing alur lengkap: assign → isi → simpan → submit                                                | Dev | 5 jam    | -      |

**Total Estimasi Sprint 3:** ~86 jam
**Story Points Sprint 3:** 33 SP

---

### SPRINT 4 — Alur Persetujuan, Arsip & Finalisasi

**Periode:** Minggu 13 – 16
**Goal:** Supervisor dan Manajer bisa review, approve/return laporan. Arsip laporan tersedia untuk semua role. Aplikasi siap produksi.

| ID Task | PBI Ref | Task                                                                                                             | PIC | Estimasi | Status |
| ------- | ------- | ---------------------------------------------------------------------------------------------------------------- | --- | -------- | ------ |
| S4-T01  | PB-31   | Buat `SupervisorLaporanController::dashboard` & view `dashboard/supervisor.blade.php` (belum ada — perlu dibuat) | Dev | 3 jam    | -      |
| S4-T02  | PB-32   | Implement `laporanMasuk` — daftar laporan dengan status `submitted`                                              | Dev | 3 jam    | -      |
| S4-T03  | PB-32   | Buat view `supervisor/index.blade.php` & `laporan-masuk.blade.php`                                               | Dev | 4 jam    | -      |
| S4-T04  | PB-33   | Implement `SupervisorLaporanController::show` — detail laporan untuk review                                      | Dev | 4 jam    | -      |
| S4-T05  | PB-33   | Buat view `supervisor/laporan-show.blade.php` (tampilan lengkap semua seksi)                                     | Dev | 5 jam    | -      |
| S4-T06  | PB-34   | Implement `approve` — supervisor tanda tangan, simpan ke `report_approvals`, ubah status                         | Dev | 4 jam    | -      |
| S4-T07  | PB-34   | Buat model `ReportApproval.php` & logika simpan tanda tangan                                                     | Dev | 2 jam    | -      |
| S4-T08  | PB-35   | Implement `returnReport` — kembalikan laporan ke analis beserta catatan                                          | Dev | 3 jam    | -      |
| S4-T09  | PB-36   | Implement `cetak` — laporan siap cetak (print-friendly layout)                                                   | Dev | 3 jam    | -      |
| S4-T10  | PB-36   | Buat view `supervisor/laporan-cetak.blade.php`                                                                   | Dev | 4 jam    | -      |
| S4-T11  | PB-36   | Buat partial `report-signature-grid.blade.php` & `report-signature-print.blade.php`                              | Dev | 3 jam    | -      |
| S4-T12  | PB-37   | Buat `ManajerLaporanController::dashboard` & view `dashboard/manajer.blade.php` (belum ada — perlu dibuat)       | Dev | 3 jam    | -      |
| S4-T13  | PB-38   | Implement `laporanMasuk` manajer — daftar laporan dengan status `supervisor_approved`                            | Dev | 3 jam    | -      |
| S4-T14  | PB-38   | Buat view `manajer/index.blade.php` & `laporan-masuk.blade.php`                                                  | Dev | 4 jam    | -      |
| S4-T15  | PB-39   | Implement `ManajerLaporanController::show` & approve — setujui laporan final                                     | Dev | 4 jam    | -      |
| S4-T16  | PB-39   | Buat view `manajer/laporan-show.blade.php`                                                                       | Dev | 4 jam    | -      |
| S4-T17  | PB-40   | Implement `ManajerLaporanController::returnReport`                                                               | Dev | 2 jam    | -      |
| S4-T18  | PB-41   | Implement `ManajerLaporanController::cetak` & view `manajer/laporan-cetak.blade.php`                             | Dev | 3 jam    | -      |
| S4-T19  | PB-42   | Buat `ArsipLaporanController::index` — daftar laporan status `approved` (semua role)                             | Dev | 3 jam    | -      |
| S4-T20  | PB-42   | Buat view `arsip/index.blade.php` dengan filter jenis, periode, analis                                           | Dev | 4 jam    | -      |
| S4-T21  | PB-43   | Implement `ArsipLaporanController::show` & view `arsip/show.blade.php`                                           | Dev | 3 jam    | -      |
| S4-T22  | PB-44   | Finalisasi komponen tanda tangan (grid & print)                                                                  | Dev | 2 jam    | -      |
| S4-T23  | PB-45   | Testing end-to-end: alur submit → approve supervisor → approve manajer → arsip                                   | Dev | 5 jam    | -      |
| S4-T24  | PB-45   | Testing fitur return/revisi di level supervisor & manajer                                                        | Dev | 3 jam    | -      |
| S4-T25  | PB-45   | Bug fixing keseluruhan dari hasil testing                                                                        | Dev | 6 jam    | -      |
| S4-T26  | PB-45   | UI polish: konsistensi warna, spacing, responsif mobile                                                          | Dev | 4 jam    | -      |
| S4-T27  | PB-45   | Finalisasi dokumentasi & review kode akhir                                                                       | Dev | 3 jam    | -      |

**Total Estimasi Sprint 4:** ~92 jam
**Story Points Sprint 4:** 36 SP

---

## RINGKASAN SPRINT

| Sprint    | Periode       | Focus                               | Story Points |
| --------- | ------------- | ----------------------------------- | ------------ |
| Sprint 1  | Minggu 1–4    | Fondasi, Auth, Manajemen User       | 35 SP        |
| Sprint 2  | Minggu 5–8    | Master Data & Konfigurasi Sistem    | 35 SP        |
| Sprint 3  | Minggu 9–12   | Tugas Pelaporan & Pengisian Laporan | 33 SP        |
| Sprint 4  | Minggu 13–16  | Persetujuan, Arsip & Finalisasi     | 36 SP        |
| **Total** | **16 Minggu** |                                     | **139 SP**   |

---

## ALUR STATUS LAPORAN

```
[DRAFT] → (submit oleh Analis) → [SUBMITTED]
[SUBMITTED] → (approve Supervisor) → [SUPERVISOR_APPROVED]
[SUBMITTED] → (return Supervisor) → [RETURNED] → (revisi Analis) → [SUBMITTED]
[SUPERVISOR_APPROVED] → (approve Manajer) → [APPROVED] → masuk ARSIP
[SUPERVISOR_APPROVED] → (return Manajer) → [RETURNED] → (revisi Analis) → [SUBMITTED]
```

---

## DEFINISI OF DONE (DoD)

Sebuah task dianggap **Done** jika:

- [ ] Fitur berjalan sesuai requirement
- [ ] Validasi input sudah diterapkan
- [ ] Middleware/role protection sudah aktif
- [ ] Aktivitas dicatat ke audit log (jika relevan)
- [ ] Tampilan konsisten dengan layout aplikasi
- [ ] Tidak ada error di console/log saat dijalankan
