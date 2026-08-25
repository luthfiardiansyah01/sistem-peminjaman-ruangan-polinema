# Audit Fitur — Sistem Peminjaman Ruangan JTI (SPR JTI)

> **Proyek:** Sistem Peminjaman Ruangan: PLP JTI
> **Role:** System Analyst
> **Sumber:** Source code analysis (Laravel 10.x)
> **Tanggal:** $(date +%Y-%m-%d)
> **Update terakhir:** Redesain jabatan approval (level VRF terpisah dihapus, diganti `m_jabatan_approval` yang menempel ke dosen/mahasiswa yang sudah ada), tahap approval baru "Ketua Pelaksana" (dipilih bebas per-pengajuan), dan fitur status ruangan per-tanggal.

---

## 1. AUTHENTICATION

### 1.1 Login
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Login Pengguna |
| **Deskripsi** | Autentikasi pengguna menggunakan username dan password via session-based Laravel Auth. Validasi dilakukan di AuthService, mengembalikan pesan error jika kredensial salah atau sukses dengan redirect ke dashboard. |
| **Actor** | ADM, DSN, TDK, MHS (level VRF terpisah sudah dihapus — lihat 5.x) |
| **Endpoint** | `GET /login` (form), `POST /login` (proses) |
| **Controller** | `AuthController@login`, `AuthController@postLogin` |
| **Service** | `AuthService::login()` |
| **Priority** | **Wajib (Critical)** |

### 1.2 Logout
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Logout Pengguna |
| **Deskripsi** | Menghapus session autentikasi pengguna dan regenerasi token CSRF, kemudian redirect ke halaman utama. |
| **Actor** | ADM, DSN, TDK, MHS, VRF |
| **Endpoint** | `GET /logout` |
| **Controller** | `AuthController@logout` |
| **Service** | `AuthService::logout()` |
| **Priority** | **Wajib (Critical)** |

### 1.3 Profile Management
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Manajemen Profil Pengguna |
| **Deskripsi** | Menampilkan, mengedit, dan memperbarui profil pengguna (username, password, nomor telepon). Data profil disesuaikan berdasarkan role (Admin → admin_nama, Dosen → dosen_nama, dll.). |
| **Actor** | ADM, DSN, TDK, MHS |
| **Endpoint** | `GET /profile/show_ajax`, `GET /profile/edit_ajax`, `PUT /profile/update_ajax` |
| **Controller** | `ProfileController@show_ajax`, `ProfileController@edit_ajax`, `ProfileController@update_ajax` |
| **Service** | `ProfileService::showData()`, `ProfileService::editData()`, `ProfileService::update()` |
| **Priority** | **Wajib** |

---

## 2. MASTER DATA

### 2.1 CRUD Admin
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Manajemen Data Admin |
| **Deskripsi** | CRUD lengkap data admin (nama, NIDN, noHP, prodi) dengan DataTables server-side processing via AJAX, termasuk modal-based create/edit/delete/confirm. |
| **Actor** | ADM |
| **Endpoint** | `GET /admin`, `POST /admin/list`, `GET /admin/create_ajax`, `POST /admin/ajax`, `GET /admin/{id}/show_ajax`, `GET /admin/{id}/edit_ajax`, `PUT /admin/{id}/update_ajax`, `GET /admin/{id}/confirm_ajax`, `DELETE /admin/{id}/delete_ajax` |
| **Controller** | `AdminController` |
| **Service** | `UserService::createAdmin()`, `UserService::updateUser()`, `UserService::deleteUser()` |
| **Priority** | **Wajib (Existing)** |

### 2.2 Import Admin via Excel
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Import Data Admin dari Excel |
| **Deskripsi** | Mengunggah file Excel untuk mengimpor data admin secara massal dengan validasi data per baris. |
| **Actor** | ADM |
| **Endpoint** | `GET /admin/import`, `POST /admin/import_ajax` |
| **Controller** | `AdminController@import`, `AdminController@import_ajax` |
| **Service** | `UserService::createAdmin()` |
| **Priority** | **Wajib (Existing)** |

### 2.3 CRUD Dosen
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Manajemen Data Dosen |
| **Deskripsi** | CRUD lengkap data dosen (nama, NIDN, noHP, prodi) dengan DataTables server-side + AJAX modal. |
| **Actor** | ADM |
| **Endpoint** | `GET /dosen`, `POST /dosen/list`, `GET /dosen/create_ajax`, `POST /dosen/ajax`, `GET /dosen/{id}/show_ajax`, `GET /dosen/{id}/edit_ajax`, `PUT /dosen/{id}/update_ajax`, `GET /dosen/{id}/confirm_ajax`, `DELETE /dosen/{id}/delete_ajax` |
| **Controller** | `DosenController` |
| **Service** | `UserService::createDosen()`, `UserService::updateUser()`, `UserService::deleteUser()` |
| **Priority** | **Wajib (Existing)** |

### 2.4 Import Dosen via Excel
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Import Data Dosen dari Excel |
| **Deskripsi** | Import massal data dosen dengan mapping kolom: NIDN, Nama, Prodi, NoHP. Validasi duplikasi username per baris. |
| **Actor** | ADM |
| **Endpoint** | `GET /dosen/import`, `POST /dosen/import_ajax` |
| **Controller** | `DosenController@import`, `DosenController@import_ajax` |
| **Service** | `ImportService::importDosenFromExcel()` |
| **Priority** | **Wajib (Existing)** |

### 2.5 CRUD Tendik
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Manajemen Data Tendik |
| **Deskripsi** | CRUD lengkap data tendik (nama, NIDN/NIP, noHP) dengan DataTables server-side + AJAX modal. |
| **Actor** | ADM |
| **Endpoint** | `GET /tendik`, `POST /tendik/list`, `GET /tendik/create_ajax`, `POST /tendik/ajax`, `GET /tendik/{id}/show_ajax`, `GET /tendik/{id}/edit_ajax`, `PUT /tendik/{id}/update_ajax`, `GET /tendik/{id}/confirm_ajax`, `DELETE /tendik/{id}/delete_ajax` |
| **Controller** | `TendikController` |
| **Service** | `UserService::createTendik()`, `UserService::updateUser()`, `UserService::deleteUser()` |
| **Priority** | **Wajib (Existing)** |

### 2.6 Import Tendik via Excel
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Import Data Tendik dari Excel |
| **Deskripsi** | Import massal data tendik dengan mapping kolom: NIDN/NIP, Nama, NoHP. |
| **Actor** | ADM |
| **Endpoint** | `GET /tendik/import`, `POST /tendik/import_ajax` |
| **Controller** | `TendikController@import`, `TendikController@import_ajax` |
| **Service** | `ImportService::importTendikFromExcel()` |
| **Priority** | **Wajib (Existing)** |

### 2.7 CRUD Mahasiswa
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Manajemen Data Mahasiswa |
| **Deskripsi** | CRUD lengkap data mahasiswa (NIM, nama, noHP, prodi, kelas) dengan DataTables server-side + AJAX modal. Termasuk endpoint dependen `get_kelas_by_prodi` untuk cascading dropdown. |
| **Actor** | ADM |
| **Endpoint** | `GET /mahasiswa`, `POST /mahasiswa/list`, `GET /mahasiswa/create_ajax`, `POST /mahasiswa/ajax`, `GET /mahasiswa/{id}/show_ajax`, `GET /mahasiswa/{id}/edit_ajax`, `PUT /mahasiswa/{id}/update_ajax`, `GET /mahasiswa/{id}/confirm_ajax`, `DELETE /mahasiswa/{id}/delete_ajax`, `GET /mahasiswa/get_kelas_by_prodi/{prodi_id}` |
| **Controller** | `MahasiswaController` |
| **Service** | `UserService::createMahasiswa()`, `UserService::updateUser()`, `UserService::deleteUser()` |
| **Priority** | **Wajib (Existing)** |

### 2.8 Import Mahasiswa via Excel
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Import Data Mahasiswa dari Excel |
| **Deskripsi** | Import massal data mahasiswa dengan mapping kolom: NIM, Nama, Prodi, Kelas, NoHP. Validasi Prodi dan Kelas terhadap database. |
| **Actor** | ADM |
| **Endpoint** | `GET /mahasiswa/import`, `POST /mahasiswa/import_ajax` |
| **Controller** | `MahasiswaController@import`, `MahasiswaController@import_ajax` |
| **Service** | `ImportService::importMahasiswaFromExcel()` |
| **Priority** | **Wajib (Existing)** |

### 2.9 CRUD Program Studi
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Manajemen Program Studi |
| **Deskripsi** | CRUD data program studi (kode prodi, nama prodi) dengan DataTables server-side. Tanpa fitur show_ajax dan import. |
| **Actor** | ADM |
| **Endpoint** | `GET /prodi`, `POST /prodi/list`, `GET /prodi/create_ajax`, `POST /prodi/ajax`, `GET /prodi/{id}/edit_ajax`, `PUT /prodi/{id}/update_ajax`, `GET /prodi/{id}/confirm_ajax`, `DELETE /prodi/{id}/delete_ajax` |
| **Controller** | `ProdiController` |
| **Service** | `ProdiCrudService::create()`, `ProdiCrudService::update()`, `ProdiCrudService::delete()` |
| **Priority** | **Wajib (Existing)** |

### 2.10 CRUD Kelas
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Manajemen Kelas |
| **Deskripsi** | CRUD data kelas (nama kelas, prodi) dengan DataTables server-side. Dropdown prodi pada form create/edit. |
| **Actor** | ADM |
| **Endpoint** | `GET /kelas`, `POST /kelas/list`, `GET /kelas/create_ajax`, `POST /kelas/ajax`, `GET /kelas/{id}/edit_ajax`, `PUT /kelas/{id}/update_ajax`, `GET /kelas/{id}/confirm_ajax`, `DELETE /kelas/{id}/delete_ajax` |
| **Controller** | `KelasController` |
| **Service** | `KelasService::create()`, `KelasService::update()`, `KelasService::delete()` |
| **Priority** | **Wajib (Existing)** |

### 2.11 Upload/Download Formulir
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Manajemen File Formulir |
| **Deskripsi** | Admin dapat mengunggah file formulir (PDF, maks 2MB). Dosen, Tendik, dan Mahasiswa dapat mengunduh formulir tersebut. |
| **Actor** | Upload: ADM, Download: DSN, TDK, MHS |
| **Endpoint** | `POST /formulir/upload` (authorize:ADM), `GET /formulir/download` (authorize:DSN,TDK,MHS) |
| **Controller** | `WelcomeController@uploadFormulir`, `WelcomeController@downloadFormulir` |
| **Service** | `FileService::uploadFormulir()`, `FileService::downloadFormulir()` |
| **Priority** | **Wajib** |

---

## 3. RUANGAN

### 3.1 CRUD Ruangan
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Manajemen Data Ruangan |
| **Deskripsi** | CRUD lengkap data ruangan (kode, nama, fasilitas, kuota, status, kategori: Jurusan/Umum, foto). Ditampilkan untuk semua role terautentikasi (list/show), namun edit/delete hanya untuk Admin. |
| **Actor** | List/Show: ADM, DSN, TDK, MHS | Create/Edit/Delete: ADM |
| **Endpoint** | `GET /ruangan`, `POST /ruangan/list`, `GET /ruangan/{id}/show_ajax` (ADM,DSN,TDK,MHS) |
| | `GET /ruangan/create_ajax`, `POST /ruangan/ajax`, `GET /ruangan/{id}/edit_ajax`, `PUT /ruangan/{id}/update_ajax`, `GET /ruangan/{id}/confirm_ajax`, `DELETE /ruangan/{id}/delete_ajax` (ADM only) |
| **Controller** | `RuanganController` |
| **Service** | `RuanganService::indexData()`, `RuanganService::dataTable()`, `RuanganService::create()`, `RuanganService::update()`, `RuanganService::delete()`, `RuanganService::find()` |
| **Priority** | **Wajib (Existing)** |

### 3.2 Sinkronisasi Status Ruangan
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Sinkronisasi Otomatis Status Ruangan |
| **Deskripsi** | Status ruangan (Tersedia/Diajukan/Tidak Tersedia) berubah otomatis mengikuti status pengajuan/jadwal terkait. Saat pengajuan dibuat → Diajukan. Saat ditolak/dibatalkan → Tersedia. |
| **Actor** | System (triggered by PengajuanService) |
| **Endpoint** | N/A (internal service call: `PengajuanService::syncRuanganStatus()`) |
| **Controller** | N/A |
| **Service** | `PengajuanService::syncRuanganStatus()` |
| **Priority** | **Wajib (FR-3.2)** |

### 3.3 Kategori Ruangan
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Penetapan Kategori Ruangan |
| **Deskripsi** | Admin menetapkan kategori ruangan (Jurusan/Umum) saat CRUD. Kategori ini menentukan jalur approval akhir pengajuan: Ketua Jurusan (jika Jurusan) atau Wakil Direktur II (jika Umum). |
| **Actor** | ADM |
| **Endpoint** | (Bagian dari CRUD Ruangan) `POST /ruangan/ajax`, `PUT /ruangan/{id}/update_ajax` |
| **Controller** | `RuanganController@store_ajax`, `RuanganController@update_ajax` |
| **Service** | `RuanganService::create()`, `RuanganService::update()` |
| **Priority** | **Wajib (FR-3.3, FR-3.4)** |

### 3.4 Status Ruangan Per-Tanggal (Baru)
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Status Ketersediaan Ruangan Per-Tanggal |
| **Deskripsi** | `ruangan_status` (FR-3.1) adalah flag global, bukan per-tanggal. Fitur ini menambah lapisan status per-tanggal (dihitung real-time dari `t_pengajuan`+`t_jadwal`, TIDAK disimpan sebagai kolom baru): **Tersedia** (default), **Diajukan** (ada pengajuan lain dengan status 'Diajukan' di tanggal itu), **Tidak Tersedia** (ada Jadwal aktif di tanggal itu, ATAU `ruangan_status` manual di-set 'Tidak Tersedia' — override final). Ditampilkan di 2 tempat: (1) widget tabel status ruangan di form pengajuan saat memilih tanggal, (2) kalender bulanan per ruangan (grid warna hijau/kuning/merah). |
| **Actor** | ADM, DSN, TDK, MHS (baca), sinkron dengan sistem approval & jadwal |
| **Endpoint** | `GET /ruangan/availability_ajax?tanggal=YYYY-MM-DD`, `GET /ruangan/{id}/kalender_ajax` (modal), `GET /ruangan/{id}/kalender_data_ajax?bulan=YYYY-MM` |
| **Controller** | `RuanganController@availability_ajax`, `@kalender_ajax`, `@kalender_data_ajax` |
| **Service** | `RuanganService::availabilityStatusForDate()`, `::availabilityForAllRoomsOnDate()`, `::monthlyAvailability()` |
| **Priority** | **Wajib (Poin 2 — permintaan klien)** |

---

## 4. ORGANISASI

### 4.1 CRUD Organisasi Mahasiswa
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Manajemen Organisasi Mahasiswa |
| **Deskripsi** | CRUD lengkap data organisasi mahasiswa (kode, nama, logo). Organisasi menjadi entitas pengaju peminjaman ruangan. |
| **Actor** | ADM |
| **Endpoint** | `GET /organisasi`, `POST /organisasi/list`, `GET /organisasi/create_ajax`, `POST /organisasi/ajax`, `GET /organisasi/{id}/show_ajax`, `GET /organisasi/{id}/edit_ajax`, `PUT /organisasi/{id}/update_ajax`, `GET /organisasi/{id}/confirm_ajax`, `DELETE /organisasi/{id}/delete_ajax` |
| **Controller** | `OrganisasiController` |
| **Service** | `OrganisasiService::indexData()`, `OrganisasiService::dataTable()`, `OrganisasiService::create()`, `OrganisasiService::update()`, `OrganisasiService::delete()`, `OrganisasiService::find()` |
| **Priority** | **Wajib (FR-1.1)** |

### 4.2 Pemetaan Jabatan Approval ke Organisasi
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Pemetaan Posisi Approval ke Organisasi |
| **Deskripsi** | Admin memetakan user **yang sudah terdaftar sebagai dosen/mahasiswa** (bukan jenis akun terpisah) ke posisi approval tertentu dalam organisasi (Ketua Umum, DPK, Presiden BEM). Posisi lintas-organisasi (Ketua Jurusan, Wakil Direktur II) tidak terikat organisasi tertentu. Tersimpan di tabel `m_jabatan_approval` (menggantikan `m_verifikator` + level VRF terpisah — lihat catatan redesain di bagian 5). |
| **Actor** | ADM |
| **Endpoint** | (Linked to Organisasi CRUD; jabatan Ketua Pelaksana dibuat otomatis — lihat 5.1) |
| **Controller** | `OrganisasiController` |
| **Service** | `OrganisasiService` (via model relasi ke `JabatanApprovalModel`) |
| **Priority** | **Wajib (FR-1.2, FR-1.3, FR-1.4)** |

---

## 5. JABATAN APPROVAL (dahulu "VERIFIKATOR")

> **Catatan redesain:** Level akun `VRF` terpisah dan tabel `m_verifikator` sudah **dihapus**. Pemegang jabatan approval sekarang adalah dosen/mahasiswa yang **sudah terdaftar** (login dengan level DSN/MHS biasa), yang dipasangi jabatan tambahan lewat `m_jabatan_approval` (`user_id`, `organisasi_id` nullable, `posisi_approval` — string bebas, bukan enum DB, `urutan_approval`). Route approval (`/pengajuan/approval/*`) sekarang digerbangi `authorize:ADM,DSN,TDK,MHS` (siapapun yang login boleh akses); otorisasi SEBENARNYA (siapa boleh memproses baris approval mana) ditegakkan di `PengajuanService::processApproval()` lewat pengecekan `jabatanApproval->user_id === Auth::id()`.

### 5.0 Tahap Ketua Pelaksana (Baru — Tahap 0)
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Tahap Approval Ketua Pelaksana |
| **Deskripsi** | Tahap approval PALING AWAL (urutan_tahap=0), sebelum Ketua Umum. Berbeda dari 5 posisi lain (posisi TETAP per organisasi), Ketua Pelaksana **dipilih bebas per-pengajuan** oleh pemohon — dosen/mahasiswa manapun, bisa beda tiap kegiatan. Baris `m_jabatan_approval` untuk orang yang dipilih dibuat on-the-fly (find-or-create by `user_id`+`posisi_approval='Ketua Pelaksana'`, dilindungi unique index) saat pengajuan dibuat. Ketua Umum (tahap 1) baru aktif setelah tahap ini disetujui. |
| **Actor** | Dosen/Mahasiswa manapun (dipilih via dropdown saat pengajuan dibuat) |
| **Endpoint** | Field `ketua_pelaksana_user_id` di `POST /pengajuan/ajax`; diproses lewat endpoint yang sama dengan 5.2 |
| **Controller** | `PengajuanController@store_ajax`, `@proses_approval_ajax` |
| **Service** | `PengajuanService::findOrCreateJabatanKetuaPelaksana()`, `::generateApprovalStages()` |
| **Priority** | **Wajib (Poin 1 — permintaan klien)** |

### 5.1 Antrian Approval
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Antrian Pengajuan untuk Pemegang Jabatan Approval |
| **Deskripsi** | Pemegang jabatan approval melihat daftar pengajuan yang menunggu persetujuannya. Hanya tahap yang sudah AKTIF (batas_waktu terisi = giliran approver) yang ditampilkan, diurutkan berdasarkan batas waktu. User tanpa jabatan approval apapun tetap bisa akses route ini (200) tapi antriannya selalu kosong. |
| **Actor** | Dosen/Mahasiswa yang memegang jabatan approval |
| **Endpoint** | `GET /pengajuan/approval/antrian_ajax` |
| **Controller** | `PengajuanController@antrian_ajax` |
| **Service** | `PengajuanService::antrianFor()` |
| **Priority** | **Wajib (FR-6.5, FR-8.3)** |

### 5.2 Proses Approval (Setuju/Tolak)
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Proses Persetujuan/Penolakan Tahap Approval |
| **Deskripsi** | Pemegang jabatan approval menyetujui atau menolak pengajuan pada tahap yang menjadi tanggung jawabnya. Penolakan mewajibkan alasan. Ditolak pada tahap manapun (termasuk tahap 0 Ketua Pelaksana) → mengakhiri seluruh proses approval. |
| **Actor** | Dosen/Mahasiswa yang memegang jabatan approval |
| **Endpoint** | `PUT /pengajuan/approval/{approvalId}/proses_ajax` |
| **Controller** | `PengajuanController@proses_approval_ajax` |
| **Service** | `PengajuanService::processApproval()` |
| **Priority** | **Wajib (FR-6.6, FR-6.7, FR-6.8)** |

### 5.3 Approval Paralel
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Approval Paralel DPK & Presiden BEM |
| **Deskripsi** | Tahap DPK dan Presiden BEM memiliki urutan_tahap yang sama (2) dan dapat diproses secara independen. Tahap berikutnya baru aktif setelah **SELURUH** baris pada urutan_tahap tersebut berstatus 'Disetujui'. |
| **Actor** | Pemegang jabatan DPK & Presiden BEM |
| **Endpoint** | (Sama dengan 5.2) |
| **Controller** | `PengajuanController@proses_approval_ajax` |
| **Service** | `PengajuanService::processApproval()` — logic `$stageFullyApproved` |
| **Priority** | **Wajib (FR-6.4)** |

---

## 6. PENGAJUAN

### 6.1 Pengajuan Peminjaman Ruangan
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Pengajuan Peminjaman Ruangan |
| **Deskripsi** | Peminjam (Dosen, Tendik, Mahasiswa via organisasi) mengajukan peminjaman ruangan dengan data: nama kegiatan, tanggal, jam mulai-selesai, jumlah peserta, keterangan, organisasi pengaju, **Ketua Pelaksana** (dosen/mahasiswa manapun, wajib dipilih — lihat 5.0), dan ruangan yang dipilih. Status awal: 'Diajukan'. |
| **Actor** | DSN, TDK, MHS, ADM |
| **Endpoint** | `GET /pengajuan`, `POST /pengajuan/list`, `GET /pengajuan/create_ajax`, `POST /pengajuan/ajax`, `GET /pengajuan/{id}/show_ajax` |
| **Controller** | `PengajuanController@index`, `list`, `create_ajax`, `store_ajax`, `show_ajax` |
| **Service** | `PengajuanService::indexData()`, `PengajuanService::createData()`, `PengajuanService::create()` |
| **Priority** | **Wajib (FR-5.1, FR-5.2)** |

### 6.2 Verifikasi Pengajuan oleh Admin
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Verifikasi Manual Pengajuan oleh Admin |
| **Deskripsi** | Admin dapat melihat detail pengajuan, menyetujui (terima) atau menolak dengan catatan. Saat diterima → generate nomor surat + buat jadwal. Saat ditolak → status 'Ditolak' + ruangan kembali 'Tersedia'. |
| **Actor** | ADM |
| **Endpoint** | `GET /pengajuan/{id}/verifikasi_ajax`, `PUT /pengajuan/{id}/terima_ajax`, `PUT /pengajuan/{id}/tolak_ajax` |
| **Controller** | `PengajuanController@verifikasi_ajax`, `PengajuanController@terima_ajax`, `PengajuanController@tolak_ajax` |
| **Service** | `PengajuanService::accept()`, `PengajuanService::reject()` |
| **Priority** | **Wajib (FR-5.1, FR-6.9 — admin override)** |

### 6.3 Timeline Approval
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Timeline Riwayat Approval |
| **Deskripsi** | Menampilkan timeline seluruh tahap approval suatu pengajuan, diurutkan berdasarkan urutan tahap. Menunjukkan status tiap tahap (Menunggu/Disetujui/Ditolak/Auto Reject), verifikator, dan waktu proses. |
| **Actor** | ADM, DSN, TDK, MHS |
| **Endpoint** | `GET /pengajuan/{id}/timeline_ajax` |
| **Controller** | `PengajuanController@timeline_ajax` |
| **Service** | `PengajuanService::timelineFor()` |
| **Priority** | **Wajib (FR-5.3)** |

### 6.4 Cetak Surat Peminjaman (PDF)
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Cetak Surat Peminjaman Digital |
| **Deskripsi** | Generate surat peminjaman dalam format PDF (DomPDF) untuk pengajuan yang sudah berstatus 'Diterima'. Surat mencakup nomor surat (auto-generate), logo organisasi, data peminjam, data ruangan. Hanya dapat dicetak untuk pengajuan Diterima. |
| **Actor** | ADM, DSN, TDK, MHS |
| **Endpoint** | `GET /pengajuan/{id}/cetak_surat_ajax` |
| **Controller** | `PengajuanController@cetak_surat_ajax` |
| **Service** | `PengajuanService::findForCetakSurat()`, `PengajuanService::generateNomorSurat()` |
| **Priority** | **Wajib (FR-9.1, FR-9.3, FR-9.4)** |

### 6.5 Auto Reject (Scheduled)
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Auto Reject Tahap Approval |
| **Deskripsi** | Penolakan otomatis oleh sistem apabila verifikator tidak memproses pengajuan dalam batas waktu 2 hari pada satu tahap. Berjalan via Laravel Task Scheduler setiap 15 menit. Idempotent (tidak memproses ulang yang sudah di-auto-reject). |
| **Actor** | System (Scheduled Command) |
| **Endpoint** | `php artisan pengajuan:auto-reject` (console command) |
| **Controller** | N/A (Console Command: `AutoRejectPengajuan`) |
| **Service** | `PengajuanService::autoRejectExpiredApprovals()` |
| **Priority** | **Wajib (FR-7.1, FR-7.2, FR-7.3)** |

---

## 7. JADWAL

### 7.1 CRUD Jadwal
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Manajemen Jadwal Peminjaman |
| **Deskripsi** | CRUD data jadwal peminjaman ruangan. Admin membuat/mengedit/menghapus jadwal. Semua role terautentikasi dapat melihat daftar dan detail jadwal. Jadwal terkait dengan ruangan via tabel pivot `t_jadwal_ruangan`. |
| **Actor** | List/Show: ADM, DSN, TDK, MHS | Create/Edit/Delete: ADM |
| **Endpoint** | `GET /jadwal`, `POST /jadwal/list`, `GET /jadwal/{id}/show_ajax` (ADM,DSN,TDK,MHS) |
| | `GET /jadwal/create_ajax`, `POST /jadwal/ajax`, `GET /jadwal/{id}/edit_ajax`, `PUT /jadwal/{id}/update_ajax`, `GET /jadwal/{id}/confirm_ajax`, `DELETE /jadwal/{id}/delete_ajax` (ADM only) |
| **Controller** | `JadwalController` |
| **Service** | `JadwalService::indexData()`, `JadwalService::formData()`, `JadwalService::create()`, `JadwalService::update()`, `JadwalService::delete()`, `JadwalService::findForShow()`, `JadwalService::findForConfirm()` |
| **Priority** | **Wajib (Existing)** |

### 7.2 Update Status Jadwal
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Perubahan Status Jadwal |
| **Deskripsi** | Admin mengubah status jadwal secara berurutan: Akan Datang → Berlangsung → Ditinjau → Dokumentasi Tidak Sesuai / Selesai. |
| **Actor** | ADM |
| **Endpoint** | `PUT /jadwal/{id}/update_status_ajax` |
| **Controller** | `JadwalController@update_status_ajax` |
| **Service** | `JadwalService::updateStatus()` |
| **Priority** | **Wajib (FR-4.1, FR-4.2)** |

### 7.3 Filter Jadwal per Semester
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Filter Daftar Jadwal Berdasarkan Semester |
| **Deskripsi** | Sistem menyediakan filter untuk menampilkan daftar jadwal berdasarkan semester tertentu. |
| **Actor** | ADM, DSN, TDK, MHS |
| **Endpoint** | (Bagian dari `POST /jadwal/list`) |
| **Controller** | `JadwalController@list` |
| **Service** | `JadwalService` (filter logic di DataTables) |
| **Priority** | **Wajib (FR-10.1)** |

### 7.4 Get Kelas by Prodi (Dependensi)
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Data Kelas Berdasarkan Program Studi |
| **Deskripsi** | Endpoint AJAX untuk mendapatkan daftar kelas berdasarkan prodi tertentu. Digunakan untuk cascading dropdown pada form create/edit jadwal (dan juga di MahasiswaController). |
| **Actor** | ADM, DSN, TDK, MHS |
| **Endpoint** | `GET /jadwal/get_kelas_by_prodi/{prodi_id}` |
| **Controller** | `JadwalController@get_kelas_by_prodi` |
| **Service** | `JadwalService::kelasByProdi()` |
| **Priority** | **Pendukung** |

---

## 8. DASHBOARD (Lintas Modul)

### 8.1 Dashboard Role-Aware
| Atribut | Detail |
|---|---|
| **Nama Fitur** | Dashboard Berdasarkan Role Pengguna |
| **Deskripsi** | Dashboard menampilkan konten yang berbeda sesuai role: **Admin** → statistik penggunaan ruangan + early warning pengajuan mendekati auto-reject. **Peminjam (DSN/TDK/MHS)** → pengajuan aktif + tabel ruangan hari ini. **Verifikator (VRF)** → antrian approval yang menjadi tanggung jawabnya. |
| **Actor** | ADM, DSN, TDK, MHS, VRF |
| **Endpoint** | `GET /dashboard` |
| **Controller** | `WelcomeController@index` |
| **Service** | `DashboardService::getWelcomeDashboardData()` |
| **Priority** | **Wajib (FR-8.1, FR-8.2, FR-8.3, FR-8.4, FR-8.5)** |

---

## RINGKASAN PRIORITAS

| Kategori | Jumlah Fitur | Critical | Wajib | Pendukung |
|---|---|---|---|---|
| Authentication | 3 | 1 | 2 | 0 |
| Master Data | 11 | 0 | 11 | 0 |
| Ruangan | 4 | 0 | 4 | 0 |
| Organisasi | 2 | 0 | 2 | 0 |
| Jabatan Approval | 4 | 0 | 4 | 0 |
| Pengajuan | 5 | 0 | 5 | 0 |
| Jadwal | 4 | 0 | 3 | 1 |
| Dashboard | 1 | 0 | 1 | 0 |
| **Total** | **34** | **1** | **32** | **1** |

---

## MATRIKS AKTOR vs FITUR

> **Catatan:** Kolom `VRF` (level akun terpisah) sudah dihapus. Baris "Antrian/Proses Approval" sekarang bisa diakses ADM/DSN/TDK/MHS manapun (route level), tapi hanya user yang benar-benar memegang jabatan approval (via `m_jabatan_approval`) yang antriannya berisi data & bisa memproses tahapnya — ditandai `✓*` di bawah.

| Fitur ↓ / Actor → | ADM | DSN | TDK | MHS | System |
|---|---|---|---|---|---|
| Login | ✓ | ✓ | ✓ | ✓ | |
| Logout | ✓ | ✓ | ✓ | ✓ | |
| Profile Management | ✓ | ✓ | ✓ | ✓ | |
| CRUD Admin | ✓ | | | | |
| CRUD Dosen | ✓ | | | | |
| CRUD Tendik | ✓ | | | | |
| CRUD Mahasiswa | ✓ | | | | |
| Import Excel | ✓ | | | | |
| CRUD Prodi | ✓ | | | | |
| CRUD Kelas | ✓ | | | | |
| Upload Formulir | ✓ | | | | |
| Download Formulir | | ✓ | ✓ | ✓ | |
| CRUD Ruangan (full) | ✓ | | | | |
| Lihat Ruangan | ✓ | ✓ | ✓ | ✓ | |
| Status Ruangan Per-Tanggal | ✓ | ✓ | ✓ | ✓ | |
| CRUD Organisasi | ✓ | | | | |
| Pemetaan Jabatan Approval | ✓ | | | | |
| Antrian Approval | ✓* | ✓* | ✓* | ✓* | |
| Proses Approval | ✓* | ✓* | ✓* | ✓* | |
| Pengajuan Peminjaman (+ Ketua Pelaksana) | ✓ | ✓ | ✓ | ✓ | |
| Verifikasi Admin | ✓ | | | | |
| Timeline | ✓ | ✓ | ✓ | ✓ | |
| Cetak Surat PDF | ✓ | ✓ | ✓ | ✓ | |
| Auto Reject | | | | | ✓ |
| CRUD Jadwal (full) | ✓ | | | | |
| Lihat Jadwal | ✓ | ✓ | ✓ | ✓ | |
| Update Status Jadwal | ✓ | | | | |
| Dashboard | ✓ | ✓ | ✓ | ✓ | |

