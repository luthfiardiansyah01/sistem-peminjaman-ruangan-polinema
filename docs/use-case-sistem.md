# Use Case Sistem — SPR JTI (Sistem Peminjaman Ruangan JTI)

> **Proyek:** Sistem Peminjaman Ruangan: PLP JTI
> **Role:** Business Analyst
> **Sumber:** Source code analysis (Laravel 10.x) — Controller, Service, Middleware, Route
> **Total Use Case:** 35
> **Update terakhir:** Level akun VRF terpisah + `m_verifikator` dihapus, diganti `m_jabatan_approval` (menempel ke dosen/mahasiswa yang sudah ada) — lihat UC-JAB-*. Tahap approval baru "Ketua Pelaksana" (UC-JAB-00). Fitur baru status ruangan per-tanggal (UC-RG-04).

---

## BAGIAN A — AUTHENTICATION (3 Use Case)

---

### UC-AUTH-01: Login Pengguna

| Atribut | Detail |
|---|---|
| **ID** | UC-AUTH-01 |
| **Nama** | Login Pengguna |
| **Aktor** | Admin (ADM), Dosen (DSN), Tendik (TDK), Mahasiswa (MHS) — level VRF terpisah sudah dihapus, pemegang jabatan approval kini login sebagai DSN/MHS biasa |
| **Tujuan** | Aktor terautentikasi ke dalam sistem dan mendapatkan akses sesuai role masing-masing |
| **Precondition** | 1. Aktor belum terautentikasi (tidak memiliki session aktif) |
| | 2. Aktor memiliki akun dengan username dan password yang terdaftar di tabel `m_user` |
| **Main Flow** | 1. Aktor membuka halaman `GET /login` |
| | 2. Sistem menampilkan form login (view: `auth.login`) |
| | 3. Aktor memasukkan username dan password, lalu menekan tombol submit |
| | 4. Sistem menerima request `POST /login` dengan kredensial |
| | 5. `AuthController@postLogin` memanggil `AuthServiceInterface::login()` |
| | 6. `AuthService::login()` melakukan trim username, memanggil `Auth::attempt(['username' => $username, 'password' => $password])` |
| | 7. Sistem mencocokkan kredensial dengan data di tabel `m_user` (password hashed) |
| | 8. Jika cocok, `Auth::attempt()` mengembalikan true, sistem membuat session autentikasi |
| | 9. `AuthService::getUserDisplayName()` mengambil nama sesuai role: admin_nama (ADM), dosen_nama (DSN), tendik_nama (TDK), mahasiswa_nama (MHS) |
| | 10. Sistem mengembalikan response JSON: `{status: true, message: "Selamat datang, {nama}.", redirect: "/dashboard"}` |
| | 11. Frontend/browser me-redirect aktor ke `/dashboard` |
| **Alternative Flow** | **A1: Kredensial salah** |
| | 4a. `Auth::attempt()` mengembalikan false |
| | 5a. Sistem mengembalikan `{status: false, message: "Login gagal, username atau password salah."}` |
| | **A2: Exception sistem** |
| | 4b. `AuthService::login()` menangkap `\Exception` |
| | 5b. Sistem log error ke file log (Log::error) |
| | 6b. Sistem mengembalikan `{status: false, message: "Terjadi kesalahan sistem. Silakan coba lagi."}` |
| | **A3: Request AJAX** |
| | 4c. Jika `$request->ajax() || $request->wantsJson()`, response langsung JSON tanpa redirect |
| **Postcondition** | 1. Session autentikasi aktif untuk aktor |
| | 2. Aktor diarahkan ke halaman dashboard |
| | 3. Cookie session tersimpan di browser |

---

### UC-AUTH-02: Logout Pengguna

| Atribut | Detail |
|---|---|
| **ID** | UC-AUTH-02 |
| **Nama** | Logout Pengguna |
| **Aktor** | Admin (ADM), Dosen (DSN), Tendik (TDK), Mahasiswa (MHS) — level VRF terpisah sudah dihapus, pemegang jabatan approval kini login sebagai DSN/MHS biasa |
| **Tujuan** | Aktor keluar dari sistem dan session autentikasi dihapus |
| **Precondition** | 1. Aktor sudah terautentikasi (memiliki session aktif) |
| **Main Flow** | 1. Aktor mengakses `GET /logout` |
| | 2. `AuthController@logout` memanggil `AuthService::logout($request)` |
| | 3. Sistem memanggil `Auth::logout()` — menghapus session autentikasi |
| | 4. Sistem memanggil `$request->session()->invalidate()` — menghancurkan session data |
| | 5. Sistem memanggil `$request->session()->regenerateToken()` — regenerasi CSRF token |
| | 6. Sistem redirect aktor ke halaman `/` (landing page) |
| **Alternative Flow** | **A1: Exception saat logout** |
| | 3a. `Auth::logout()` throw exception |
| | 4a. Sistem log error, tetap lanjut ke invalidate session dan regenerate token |
| **Postcondition** | 1. Session autentikasi aktor dihapus |
| | 2. Aktor kembali ke halaman landing page |
| | 3. CSRF token baru di-generate |

---

### UC-AUTH-03: Manajemen Profil Pengguna

| Atribut | Detail |
|---|---|
| **ID** | UC-AUTH-03 |
| **Nama** | Manajemen Profil Pengguna |
| **Aktor** | Admin (ADM), Dosen (DSN), Tendik (TDK), Mahasiswa (MHS) |
| **Tujuan** | Aktor melihat, mengedit, dan memperbarui data profil pribadi (username, password, nomor telepon) |
| **Precondition** | 1. Aktor sudah terautentikasi |
| **Main Flow** | **Subflow A — Lihat Profil:** |
| | 1. Aktor mengakses `GET /profile/show_ajax` |
| | 2. `ProfileController@show_ajax` memanggil `ProfileService::showData(Auth::user())` |
| | 3. Sistem menentukan role aktor via `$user->getRole()` |
| | 4. Sistem mengambil data profil dari tabel yang sesuai: |
| | - ADM: `AdminModel::with('prodi')->where('user_id', $user->user_id)` |
| | - DSN: `DosenModel::with('prodi')->where('user_id', $user->user_id)` |
| | - TDK: `TendikModel::where('user_id', $user->user_id)` |
| | - MHS: `MahasiswaModel::with(['prodi','kelas'])->where('user_id', $user->user_id)` |
| | 5. Sistem mengembalikan view `profile.show_ajax` dengan data profil |
| | **Subflow B — Edit Profil:** |
| | 1. Aktor mengakses `GET /profile/edit_ajax` |
| | 2. Sama dengan subflow A, ditambah data `ProdiModel::all()` dan `KelasModel::all()` untuk dropdown |
| | 3. Sistem mengembalikan view `profile.edit_ajax` |
| | **Subflow C — Update Profil:** |
| | 1. Aktor submit form `PUT /profile/update_ajax` dengan data: username, password (opsional), no_hp |
| | 2. `ProfileController@update_ajax` memanggil `ProfileService::update($user, $request->all())` |
| | 3. Validasi: `username` string min 3, unique di m_user (kecuali user_id sendiri), `password` nullable min 5, `no_hp` nullable max 15 |
| | 4. Jika validasi gagal → return `{status: false, msg: "Validasi gagal", errors: {...}}` |
| | 5. Jika validasi lolos: |
| | a. Update username di `m_user` |
| | b. Jika password diisi, hash dengan `Hash::make()` lalu update |
| | c. Simpan perubahan via `$user->save()` |
| | d. Update no_hp di tabel role-specific: `admin_noHp`, `dosen_noHp`, `tendik_noHp`, `mahasiswa_noHp` |
| | 6. Return `{status: true, msg: "Profil berhasil diperbarui!"}` |
| **Alternative Flow** | **A1: Request bukan AJAX** |
| | 1a. Semua method ProfileController mengembalikan redirect('/') jika bukan AJAX |
| **Postcondition** | 1. Data profil aktor diperbarui di database |
| | 2. Session username tetap terupdate |
| | 3. Password baru aktif untuk login berikutnya |

---

## BAGIAN B — MASTER DATA (11 Use Case)

---

### UC-MD-01: CRUD Admin

| Atribut | Detail |
|---|---|
| **ID** | UC-MD-01 |
| **Nama** | Manajemen Data Admin |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Admin mengelola data admin lain (nama, NIDN, noHP, prodi) melalui antarmuka DataTables dengan modal AJAX |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| | 2. Aktor memiliki akses route group dengan middleware `authorize:ADM` |
| **Main Flow** | **Subflow A — Lihat Daftar Admin:** |
| | 1. Aktor mengakses `GET /admin` |
| | 2. `AdminController@index` mengambil data `AdminModel::with(['user','prodi'])->get()` |
| | 3. Sistem menampilkan view `admin.index` dengan DataTables |
| | 4. DataTables memanggil `POST /admin/list` untuk data JSON |
| | 5. `AdminController@list` menjalankan query dengan filter `prodi_id` dan `search.value` |
| | 6. DataTables merender tabel dengan kolom: No, Nama, NIDN, NoHP, Prodi, Aksi (Detail/Edit/Hapus) |
| | **Subflow B — Tambah Admin:** |
| | 1. Aktor klik tombol "Tambah" → modal `GET /admin/create_ajax` |
| | 2. Sistem menampilkan form dengan dropdown Prodi |
| | 3. Aktor mengisi data, submit via `POST /admin/ajax` |
| | 4. `AdminController@store_ajax` memanggil `UserService::createAdmin($request->all())` |
| | 5. `UserService` resolve level ADM via `LevelService::getLevelByKode('ADM')` |
| | 6. Validasi: username unique, password minimal 6, admin_nama required, admin_nidn required, prodi_id required |
| | 7. Transaksi database: create user di `m_user` → create admin di `m_admin` |
| | 8. Dispatch event `UserCreated` |
| | 9. Return JSON sukses |
| | **Subflow C — Edit Admin:** |
| | 1. Aktor klik "Edit" → `GET /admin/{id}/edit_ajax` |
| | 2. Sistem menampilkan form dengan data admin existing + dropdown Prodi |
| | 3. Aktor ubah data, submit via `PUT /admin/{id}/update_ajax` |
| | 4. `AdminController@update_ajax` memanggil `UserService::updateUser($admin->user_id, $data)` |
| | 5. Dispatch event `UserUpdated` |
| | **Subflow D — Hapus Admin:** |
| | 1. Aktor klik "Hapus" → `GET /admin/{id}/confirm_ajax` (tampilkan konfirmasi detail) |
| | 2. Aktor konfirmasi → `DELETE /admin/{id}/delete_ajax` |
| | 3. `AdminController@delete_ajax` memanggil `UserService::deleteUser($admin->user_id)` |
| | 4. Dispatch event `UserDeleted` |
| | 5. Admin dihapus dari database |
| **Alternative Flow** | **A1: Data tidak ditemukan** |
| | 1a. Saat edit/delete, jika `AdminModel::find($id)` null → return error 404 |
| | **A2: Validasi gagal** |
| | 1b. `UserService::createAdmin` mengembalikan `{success: false, message:..., errors:...}` |
| | 2b. Controller mengembalikan JSON error |
| **Postcondition** | 1. Data admin berhasil ditambahkan/diubah/dihapus |
| | 2. DataTables direfresh |
| | 3. Event tercatat (logging) |

---

### UC-MD-02: Import Admin via Excel

| Atribut | Detail |
|---|---|
| **ID** | UC-MD-02 |
| **Nama** | Import Data Admin dari Excel |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Admin mengimpor data admin secara massal dari file Excel |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| | 2. File Excel tersedia dengan format kolom yang sesuai |
| **Main Flow** | 1. Aktor mengakses `GET /admin/import` → view upload form |
| | 2. Aktor memilih file `.xlsx` (maks 1MB) dan submit |
| | 3. `POST /admin/import_ajax` → `AdminController@import_ajax` |
| | 4. Validasi: `required|mimes:xlsx|max:1024` |
| | 5. `AdminController` memanggil `UserService::createAdmin()` per baris data |
| | 6. Setiap baris diproses: validasi unique username, create user + admin |
| | 7. Return JSON `{status: true, message: "Data berhasil diimport!"}` |
| **Alternative Flow** | **A1: File tidak valid** → return validation error |
| | **A2: Tidak ada data** → return `{status: false, message: "Tidak ada data yang diimport."}` |
| **Postcondition** | Data admin baru tersimpan di database |

---

### UC-MD-03: CRUD Dosen

| Atribut | Detail |
|---|---|
| **ID** | UC-MD-03 |
| **Nama** | Manajemen Data Dosen |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Admin mengelola data dosen dengan pola CRUD yang identik dengan Admin |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| **Main Flow** | Identik dengan UC-MD-01, menggunakan: |
| | - Controller: `DosenController` |
| | - Service: `UserService::createDosen()`, `UserService::updateUser()`, `UserService::deleteUser()` |
| | - Model: `DosenModel` dengan relasi `user` dan `prodi` |
| | - View prefix: `dosen.*` |
| | - Route prefix: `/dosen/*` |
| | - Level: 'DSN' (resolve via `LevelService::getLevelByKode('DSN')`) |
| **Postcondition** | Data dosen berhasil ditambahkan/diubah/dihapus |

---

### UC-MD-04: Import Dosen via Excel

| Atribut | Detail |
|---|---|
| **ID** | UC-MD-04 |
| **Nama** | Import Data Dosen dari Excel |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Import massal data dosen dengan mapping: NIDN (kolom A), Nama (B), Prodi (C), NoHP (D) |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| **Main Flow** | 1. Upload file via `POST /dosen/import_ajax` |
| | 2. `ImportService::importDosenFromExcel()` membaca file Excel menggunakan PhpSpreadsheet |
| | 3. Sistem membaca baris per baris (mulai baris 2, lewati header) |
| | 4. Validasi per baris: NIDN tidak kosong, Nama tidak kosong, Prodi tidak kosong, NIDN unique |
| | 5. Map nama Prodi ke `prodi_id` via `ProdiService::getAllProdis()` |
| | 6. Jika valid: create user (username=NIDN, password=Hash::make(NIDN), level_id=DSN) + create dosen |
| | 7. Proses dalam 1 transaksi database |
| | 8. Jika ada error validasi → rollback transaksi → return error list |
| | 9. Jika semua valid → commit → return `{success: true, message: "{count} data dosen berhasil diimpor"}` |
| **Alternative Flow** | **A1: File tidak bisa dibaca** → return `{success: false, message: "Gagal membaca file: ..."}` |
| | **A2: Baris tidak lengkap** → return error per baris, rollback |
| **Postcondition** | Data dosen baru tersimpan di database, user baru terbuat |

---

### UC-MD-05: CRUD Tendik

| Atribut | Detail |
|---|---|
| **ID** | UC-MD-05 |
| **Nama** | Manajemen Data Tendik |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Admin mengelola data tendik dengan pola CRUD yang identik dengan Admin (tanpa relasi prodi) |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| **Main Flow** | Identik dengan UC-MD-01, menggunakan: |
| | - Controller: `TendikController` |
| | - Service: `UserService::createTendik()`, `UserService::updateUser()`, `UserService::deleteUser()` |
| | - Model: `TendikModel` (tanpa relasi prodi) |
| | - Level: 'TDK' (resolve via `LevelService::getLevelByKode('TDK')`) |
| **Postcondition** | Data tendik berhasil ditambahkan/diubah/dihapus |

---

### UC-MD-06: Import Tendik via Excel

| Atribut | Detail |
|---|---|
| **ID** | UC-MD-06 |
| **Nama** | Import Data Tendik dari Excel |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Import massal data tendik dengan mapping: NIDN (kolom A), Nama (B), NoHP (C) |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| **Main Flow** | Identik dengan UC-MD-04, menggunakan `ImportService::importTendikFromExcel()` |
| | - Level: 'TDK' |
| | - Tanpa mapping Prodi |
| **Postcondition** | Data tendik baru tersimpan |

---

### UC-MD-07: CRUD Mahasiswa

| Atribut | Detail |
|---|---|
| **ID** | UC-MD-07 |
| **Nama** | Manajemen Data Mahasiswa |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Admin mengelola data mahasiswa (NIM, nama, noHP, prodi, kelas) |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| **Main Flow** | Identik dengan UC-MD-01, dengan tambahan: |
| | - Controller: `MahasiswaController` |
| | - Service: `UserService::createMahasiswa()`, `UserService::updateUser()`, `UserService::deleteUser()` |
| | - Model: `MahasiswaModel` dengan relasi `user`, `prodi`, `kelas` |
| | - Level: 'MHS' (resolve via `LevelService::getLevelByKode('MHS')`) |
| | - Endpoint tambahan: `GET /mahasiswa/get_kelas_by_prodi/{prodi_id}` untuk cascading dropdown |
| | - Extra data di form: `kelasList` + `get_kelas_by_prodi` untuk dropdown dinamis |
| **Postcondition** | Data mahasiswa berhasil ditambahkan/diubah/dihapus |

---

### UC-MD-08: Import Mahasiswa via Excel

| Atribut | Detail |
|---|---|
| **ID** | UC-MD-08 |
| **Nama** | Import Data Mahasiswa dari Excel |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Import massal data mahasiswa dengan mapping: NIM (A), Nama (B), Prodi (C), Kelas (D), NoHP (E) |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| **Main Flow** | Identik dengan UC-MD-04, menggunakan `ImportService::importMahasiswaFromExcel()` |
| | - Level: 'MHS' |
| | - Mapping Prodi + Kelas via `$prodiMap` dan `$kelasMap` |
| | - Validasi tambahan: Kelas harus ditemukan di database |
| **Postcondition** | Data mahasiswa baru tersimpan |

---

### UC-MD-09: CRUD Program Studi

| Atribut | Detail |
|---|---|
| **ID** | UC-MD-09 |
| **Nama** | Manajemen Program Studi |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Admin mengelola data program studi (kode prodi, nama prodi) |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| **Main Flow** | 1. Lihat daftar: `GET /prodi` → `ProdiController@index` → view `prodi.index` |
| | 2. DataTables: `POST /prodi/list` → `ProdiController@list` → `ProdiCrudService::dataTable()` |
| | 3. Create: `GET /prodi/create_ajax` (form) → `POST /prodi/ajax` (store) |
| | 4. Edit: `GET /prodi/{id}/edit_ajax` → `PUT /prodi/{id}/update_ajax` |
| | 5. Delete: `GET /prodi/{id}/confirm_ajax` → `DELETE /prodi/{id}/delete_ajax` |
| | **Catatan:** Tidak memiliki fitur show_ajax dan import |
| **Postcondition** | Data prodi berhasil ditambahkan/diubah/dihapus |

---

### UC-MD-10: CRUD Kelas

| Atribut | Detail |
|---|---|
| **ID** | UC-MD-10 |
| **Nama** | Manajemen Kelas |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Admin mengelola data kelas per program studi |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| **Main Flow** | 1. Lihat daftar: `GET /kelas` → `KelasController@index` → view `kelas.index` |
| | 2. DataTables: `POST /kelas/list` → `KelasService::dataTable($request)` |
| | 3. Create: `GET /kelas/create_ajax` (form dengan dropdown prodi) → `POST /kelas/ajax` |
| | 4. Edit: `GET /kelas/{id}/edit_ajax` → `PUT /kelas/{id}/update_ajax` |
| | 5. Delete: `GET /kelas/{id}/confirm_ajax` → `DELETE /kelas/{id}/delete_ajax` |
| **Postcondition** | Data kelas berhasil ditambahkan/diubah/dihapus |

---

### UC-MD-11: Upload dan Download Formulir

| Atribut | Detail |
|---|---|
| **ID** | UC-MD-11 |
| **Nama** | Manajemen File Formulir |
| **Aktor** | Upload: Admin (ADM) | Download: Dosen (DSN), Tendik (TDK), Mahasiswa (MHS) |
| **Tujuan** | Admin mengunggah file formulir PDF; Dosen/Tendik/Mahasiswa mengunduh formulir tersebut |
| **Precondition** | 1. Aktor terautentikasi |
| | 2. Untuk upload: role = ADM |
| | 3. Untuk download: role = DSN atau TDK atau MHS |
| **Main Flow** | **Upload:** |
| | 1. Aktor (ADM) submit form dengan file PDF via `POST /formulir/upload` |
| | 2. Validasi: `required|mimes:pdf|max:2048` |
| | 3. `FileService::uploadFormulir()` menyimpan file ke storage |
| | 4. Sistem update path file di `FormulirModel` |
| | 5. Response JSON sukses |
| | **Download:** |
| | 1. Aktor (DSN/TDK/MHS) mengakses `GET /formulir/download` |
| | 2. `FileService::downloadFormulir()` mengambil path dari database |
| | 3. Response download file via `response()->download()` |
| **Alternative Flow** | **A1: File tidak valid** → return error JSON |
| | **A2: File tidak ditemukan** → redirect back with error message |
| **Postcondition** | 1. File formulir tersimpan/terdownload |
| | 2. Path terupdate di database |

---

## BAGIAN C — RUANGAN (3 Use Case)

---

### UC-RG-01: CRUD Ruangan

| Atribut | Detail |
|---|---|
| **ID** | UC-RG-01 |
| **Nama** | Manajemen Data Ruangan |
| **Aktor** | Create/Edit/Delete: Admin (ADM) | List/Show: ADM, DSN, TDK, MHS |
| **Tujuan** | Mengelola data ruangan (kode, nama, fasilitas, kuota, status, kategori, foto) |
| **Precondition** | 1. Aktor terautentikasi |
| | 2. Untuk operasi tulis: role = ADM |
| **Main Flow** | **Lihat daftar ruangan:** |
| | 1. `GET /ruangan` → `RuanganController@index` → `RuanganService::indexData()` → view `ruangan.index` |
| | 2. `POST /ruangan/list` → `RuanganService::dataTable(Auth::user()->getRole())` |
| | 3. DataTables menampilkan data ruangan |
| | **Tambah ruangan:** |
| | 1. `GET /ruangan/create_ajax` → form modal |
| | 2. Submit `POST /ruangan/ajax` → `RuanganService::create($request->all())` → return JSON |
| | **Edit ruangan:** |
| | 1. `GET /ruangan/{id}/edit_ajax` → form dengan data existing |
| | 2. Submit `PUT /ruangan/{id}/update_ajax` → `RuanganService::update()` |
| | **Hapus ruangan:** |
| | 1. `GET /ruangan/{id}/confirm_ajax` → modal konfirmasi |
| | 2. `DELETE /ruangan/{id}/delete_ajax` → `RuanganService::delete()` |
| **Alternative Flow** | **A1: Data tidak ditemukan** → `notFoundErrorResponse` |
| | **A2: Request bukan AJAX** → redirect('/') |
| **Postcondition** | Data ruangan berhasil ditambahkan/diubah/dihapus di tabel `m_ruangan` |

---

### UC-RG-02: Sinkronisasi Status Ruangan

| Atribut | Detail |
|---|---|
| **ID** | UC-RG-02 |
| **Nama** | Sinkronisasi Otomatis Status Ruangan |
| **Aktor** | Sistem (internal — dipicu oleh PengajuanService) |
| **Tujuan** | Status ruangan berubah otomatis mengikuti status pengajuan/jadwal terkait tanpa input manual |
| **Precondition** | 1. Ada perubahan status pada pengajuan atau jadwal yang terkait dengan ruangan |
| **Main Flow** | 1. `PengajuanService::syncRuanganStatus($ruanganIds, $status)` dipanggil |
| | 2. Eksekusi: `RuanganModel::whereIn('ruangan_id', $ruanganIds)->update(['ruangan_status' => $status])` |
| | 3. Status yang mungkin: 'Tersedia', 'Diajukan', 'Tidak Tersedia' |
| | **Trigger points (dari source code):** |
| | - Saat pengajuan dibuat (create): status → 'Diajukan' |
| | - Saat pengajuan diterima (accept): status → 'Diajukan' |
| | - Saat pengajuan ditolak (reject): status → 'Tersedia' |
| | - Saat auto reject: status → 'Tersedia' |
| **Postcondition** | Status kolom `ruangan_status` di tabel `m_ruangan` terupdate |

---

### UC-RG-03: Penetapan Kategori Ruangan

| Atribut | Detail |
|---|---|
| **ID** | UC-RG-03 |
| **Nama** | Penetapan Kategori Ruangan |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Admin menetapkan kategori ruangan (Jurusan/Umum) yang menentukan jalur approval akhir pengajuan |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| | 2. Ruangan sudah dibuat atau sedang dibuat |
| **Main Flow** | 1. Aktor membuka form create/edit ruangan |
| | 2. Pada field "Kategori Ruangan", aktor memilih: 'Jurusan' atau 'Umum' |
| | 3. Submit form → `RuanganService::create()` / `RuanganService::update()` |
| | 4. Nilai `ruangan_kategori` tersimpan di tabel `m_ruangan` |
| | **Dampak downstream:** |
| | - Kategori 'Jurusan' → approval akhir ke **Ketua Jurusan** |
| | - Kategori 'Umum' → approval akhir ke **Wakil Direktur II** |
| **Postcondition** | Kategori ruangan tersimpan dan akan digunakan oleh `PengajuanService::generateApprovalStages()` |

---

### UC-RG-04: Status Ruangan Per-Tanggal (Baru)

| Atribut | Detail |
|---|---|
| **ID** | UC-RG-04 |
| **Nama** | Melihat Status Ketersediaan Ruangan Per-Tanggal |
| **Aktor** | Admin (ADM), Dosen (DSN), Tendik (TDK), Mahasiswa (MHS) |
| **Tujuan** | Calon peminjam melihat apakah ruangan tersedia, ada calon peminjam lain yang mengajukan, atau sudah/sedang dipinjam — untuk tanggal tertentu (bukan status global) |
| **Precondition** | 1. Aktor terautentikasi |
| **Main Flow** | **Widget di form pengajuan:** |
| | 1. Aktor membuka form `GET /pengajuan/create_ajax`, memilih tanggal kegiatan |
| | 2. JS mengirim `GET /ruangan/availability_ajax?tanggal=YYYY-MM-DD` |
| | 3. `RuanganService::availabilityForAllRoomsOnDate()` mengembalikan status tiap ruangan (Tersedia/Diajukan/Tidak Tersedia) untuk tanggal itu |
| | 4. Tabel status ditampilkan di bawah pilihan ruangan (badge warna) — informasi saja, tidak memblokir submit |
| | **Kalender per ruangan:** |
| | 1. Aktor klik tombol "Lihat Kalender" pada daftar ruangan → `GET /ruangan/{id}/kalender_ajax` (modal) |
| | 2. JS memuat `GET /ruangan/{id}/kalender_data_ajax?bulan=YYYY-MM` |
| | 3. `RuanganService::monthlyAvailability()` mengembalikan status tiap tanggal dalam 1 bulan |
| | 4. Grid kalender diwarnai per tanggal, tombol prev/next bulan me-refresh data |
| **Logika status (precedence)** | 1. `ruangan_status = 'Tidak Tersedia'` (override manual admin) → selalu Tidak Tersedia |
| | 2. Ada `t_jadwal` untuk ruangan itu di tanggal itu dengan `jadwal_status != 'Selesai'` → Tidak Tersedia |
| | 3. Ada `t_pengajuan` untuk ruangan itu di tanggal itu dengan `pengajuan_status = 'Diajukan'` → Diajukan |
| | 4. Selain itu → Tersedia |
| **Alternative Flow** | **A1: Parameter tanggal/bulan tidak valid** → 422 |
| **Postcondition** | Aktor melihat status ketersediaan ruangan spesifik untuk tanggal yang relevan, bukan status global |

---

## BAGIAN D — ORGANISASI (2 Use Case)

---

### UC-ORG-01: CRUD Organisasi Mahasiswa

| Atribut | Detail |
|---|---|
| **ID** | UC-ORG-01 |
| **Nama** | Manajemen Organisasi Mahasiswa |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Admin mengelola data organisasi mahasiswa (kode, nama, logo) yang akan menjadi entitas pengaju peminjaman ruangan |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| **Main Flow** | 1. Lihat daftar: `GET /organisasi` → `OrganisasiController@index` → `OrganisasiService::indexData()` |
| | 2. DataTables: `POST /organisasi/list` → `OrganisasiService::dataTable()` |
| | 3. Create: `GET /organisasi/create_ajax` → `POST /organisasi/ajax` |
| | 4. Show: `GET /organisasi/{id}/show_ajax` |
| | 5. Edit: `GET /organisasi/{id}/edit_ajax` → `PUT /organisasi/{id}/update_ajax` |
| | 6. Delete: `GET /organisasi/{id}/confirm_ajax` → `DELETE /organisasi/{id}/delete_ajax` |
| **Alternative Flow** | **A1: Data tidak ditemukan** → `notFoundErrorResponse` |
| **Postcondition** | Data organisasi tersimpan di tabel `m_organisasi` |

---

### UC-ORG-02: Pemetaan Jabatan Approval ke Organisasi

| Atribut | Detail |
|---|---|
| **ID** | UC-ORG-02 |
| **Nama** | Pemetaan Posisi Approval ke Organisasi |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Admin memetakan user **yang sudah terdaftar sebagai dosen/mahasiswa** ke posisi approval dalam organisasi (Ketua Umum, DPK, Presiden BEM) atau posisi lintas-organisasi (Ketua Jurusan, Wakil Direktur II) |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| | 2. Data organisasi sudah ada di `m_organisasi` |
| | 3. User yang akan dipetakan sudah terdaftar sebagai dosen (`m_dosen`) atau mahasiswa (`m_mahasiswa`) — BUKAN jenis akun/level terpisah |
| **Main Flow** | 1. Admin menambahkan data jabatan approval via tabel `m_jabatan_approval` |
| | 2. Data yang disimpan: `user_id` (FK ke m_user — dosen/mahasiswa existing), `organisasi_id` (FK ke m_organisasi, nullable untuk posisi lintas), `posisi_approval` (string bebas — BUKAN enum DB, supaya posisi baru bisa ditambah tanpa migration), `urutan_approval` |
| | 3. Posisi 'Ketua Umum' terikat pada `organisasi_id` tertentu |
| | 4. Posisi 'DPK', 'Presiden BEM', 'Ketua Jurusan', 'Wakil Direktur II' memiliki `organisasi_id = null` (lintas organisasi) |
| | 5. Data ini digunakan oleh `PengajuanService::generateApprovalStages()` saat pengajuan dibuat |
| | 6. **Pengecualian:** posisi 'Ketua Pelaksana' TIDAK dipetakan di sini — baris `m_jabatan_approval`-nya dibuat otomatis (find-or-create) saat pengajuan dibuat, mengikuti siapa yang dipilih pemohon (lihat UC-JAB-00) |
| **Postcondition** | Data jabatan approval tersimpan di tabel `m_jabatan_approval` (unique per `user_id`+`posisi_approval`), siap digunakan oleh modul Approval Berjenjang |

---

## BAGIAN E — JABATAN APPROVAL (4 Use Case, dahulu "VERIFIKATOR")

> **Catatan redesain:** Level akun `VRF` terpisah dan tabel `m_verifikator` sudah dihapus. Pemegang jabatan approval login sebagai dosen/mahasiswa biasa (level DSN/MHS); jabatan approval hanya atribut tambahan di `m_jabatan_approval`, bukan level akun. Route `/pengajuan/approval/*` sekarang digerbangi `authorize:ADM,DSN,TDK,MHS`, bukan `authorize:VRF`.

---

### UC-JAB-00: Tahap Approval Ketua Pelaksana (Baru)

| Atribut | Detail |
|---|---|
| **ID** | UC-JAB-00 |
| **Nama** | Konfirmasi Tahap Ketua Pelaksana |
| **Aktor** | Dosen/Mahasiswa manapun yang dipilih sebagai Ketua Pelaksana |
| **Tujuan** | Ketua Pelaksana kegiatan (dipilih bebas per-pengajuan, bukan posisi tetap) mengonfirmasi/menyetujui pengajuan sebelum masuk ke rantai approval berjenjang standar |
| **Precondition** | 1. Pengajuan sudah dibuat dengan `ketua_pelaksana_user_id` terisi |
| | 2. Baris `m_jabatan_approval` untuk user itu dengan posisi 'Ketua Pelaksana' sudah ada (dibuat otomatis saat create, atau reuse jika user itu sudah pernah jadi Ketua Pelaksana sebelumnya) |
| **Main Flow** | 1. Saat `PengajuanService::create()` dipanggil, `generateApprovalStages()` memanggil `findOrCreateJabatanKetuaPelaksana($pengajuan->ketua_pelaksana_user_id)` |
| | 2. Baris `t_pengajuan_approval` urutan_tahap=0 dibuat dengan `batas_waktu = now()+2 hari` (tahap ini yang aktif duluan, BUKAN Ketua Umum) |
| | 3. Ketua Pelaksana melihat pengajuan di antriannya (`GET /pengajuan/approval/antrian_ajax`) |
| | 4. Ketua Pelaksana menyetujui/menolak via `PUT /pengajuan/approval/{approvalId}/proses_ajax` (endpoint sama dengan UC-JAB-02) |
| | 5. Jika disetujui → tahap 1 (Ketua Umum) diaktifkan (`batas_waktu` diisi) |
| | 6. Jika ditolak → pengajuan langsung 'Ditolak', sama seperti tahap lain |
| **Alternative Flow** | **A1: User yang sama dipilih di 2+ pengajuan berbeda** → baris `m_jabatan_approval` di-reuse (unique index `user_id`+`posisi_approval` mencegah duplikasi), bukan dibuat baru tiap kali |
| | **A2: `ketua_pelaksana_user_id` kosong/tidak valid saat create** → validasi gagal 422 (`required|exists:m_user,user_id`) |
| **Postcondition** | 1. Tahap 0 tercatat di timeline approval sebagai "Ketua Pelaksana" |
| | 2. Ketua Umum (tahap 1) tidak aktif sampai tahap ini disetujui |

---

### UC-JAB-01: Melihat Antrian Approval

| Atribut | Detail |
|---|---|
| **ID** | UC-JAB-01 |
| **Nama** | Antrian Pengajuan untuk Pemegang Jabatan Approval |
| **Aktor** | Dosen/Mahasiswa yang memegang jabatan approval |
| **Tujuan** | Aktor melihat daftar pengajuan yang menunggu persetujuannya |
| **Precondition** | 1. Aktor terautentikasi (level DSN/MHS/TDK/ADM apa saja — route tidak lagi digerbangi per-level) |
| | 2. Terdapat data di `t_pengajuan_approval` dengan `jabatan_id` yang menunjuk ke salah satu jabatan milik aktor |
| **Main Flow** | 1. Aktor mengakses `GET /pengajuan/approval/antrian_ajax` |
| | 2. `PengajuanController@antrian_ajax` memanggil `PengajuanService::antrianFor(Auth::user())` |
| | 3. Sistem mengumpulkan SEMUA `jabatan_id` milik user ini: `JabatanApprovalModel::where('user_id', ...)->pluck('jabatan_id')` (satu user bisa punya lebih dari satu jabatan — relasi `hasMany`) |
| | 4. Jika ada jabatan, query `PengajuanApprovalModel` dengan filter: |
| | - `jabatan_id` IN (daftar jabatan_id aktor) |
| | - `status_approval` = 'Menunggu' |
| | - `batas_waktu` IS NOT NULL (hanya tahap aktif) |
| | - Order by `batas_waktu` ASC (paling mendesak duluan) |
| | 5. Data di-load dengan relasi: `pengajuan.user`, `pengajuan.ruangans`, `pengajuan.organisasi` |
| | 6. Sistem mengembalikan view `pengajuan.antrian` dengan data antrian |
| **Alternative Flow** | **A1: User tidak punya jabatan approval apapun** |
| | 1a. `JabatanApprovalModel::where('user_id', ...)` tidak menemukan data |
| | 2a. Return `collect()` — antrian kosong, TAPI route tetap 200 (bukan 403) |
| **Postcondition** | 1. Aktor melihat daftar pengajuan yang menunggu persetujuannya (bisa kosong) |
| | 2. Data ditampilkan dengan informasi lengkap: nama kegiatan, organisasi, tanggal, ruangan |

---

### UC-JAB-02: Proses Persetujuan / Penolakan Tahap Approval

| Atribut | Detail |
|---|---|
| **ID** | UC-JAB-02 |
| **Nama** | Proses Persetujuan atau Penolakan Tahap Approval |
| **Aktor** | Dosen/Mahasiswa yang memegang jabatan approval |
| **Tujuan** | Aktor menyetujui atau menolak pengajuan pada tahap yang menjadi tanggung jawabnya |
| **Precondition** | 1. Aktor terautentikasi |
| | 2. Data approval (`PengajuanApprovalModel`) dengan `approval_id` yang valid |
| | 3. `status_approval` = 'Menunggu' pada tahap tersebut |
| | 4. Aktor yang login adalah pemilik tahap tersebut (`jabatanApproval.user_id == Auth::id()`) |
| **Main Flow** | **Flow Setuju:** |
| | 1. Aktor mengirim `PUT /pengajuan/approval/{approvalId}/proses_ajax` dengan `status_approval=Disetujui` |
| | 2. `PengajuanController@proses_approval_ajax` memanggil `PengajuanService::processApproval($approvalId, Auth::id(), 'Disetujui', null)` |
| | 3. Validasi: approval exists, pemegang jabatan berhak, status masih 'Menunggu' |
| | 4. Sistem update `status_approval = 'Disetujui'`, `diproses_pada = now()` |
| | 5. Sistem cek apakah semua approval pada `urutan_tahap` yang sama sudah 'Disetujui' (`$stageFullyApproved`) |
| | 6. **Jika belum semua disetujui** (masih ada paralel) → return `{message: "Menunggu approval paralel lainnya."}` |
| | 7. **Jika semua sudah disetujui**: |
| | a. Cari tahap berikutnya (`urutan_tahap` > current) |
| | b. Jika ada → aktifkan tahap berikutnya (isi `batas_waktu = now()+2hari`) |
| | c. Jika tidak ada (tahap terakhir) → update `pengajuan_status = 'Diterima'`, generate nomor surat, buat jadwal |
| | **Flow Tolak:** |
| | 1. Aktor kirim dengan `status_approval=Ditolak` dan `alasan_penolakan` (wajib diisi) |
| | 2. Validasi: alasan penolakan tidak boleh kosong |
| | 3. Update `status_approval = 'Ditolak'`, `alasan_penolakan = ...`, `diproses_pada = now()` |
| | 4. Update `pengajuan_status = 'Ditolak'`, `catatan_verifikator = alasan_penolakan` |
| | 5. `syncRuanganStatus($ruanganIds, 'Tersedia')` — ruangan kembali tersedia |
| | 6. Return `{status: true, message: "Pengajuan ditolak pada tahap ini."}` |
| **Alternative Flow** | **A1: Approval tidak ditemukan** → `{status: false, message: "Data tahap approval tidak ditemukan!", http_status: 404}` |
| | **A2: Verifikator tidak berhak** → `{status: false, message: "Anda tidak berwenang memproses tahap approval ini.", http_status: 403}` |
| | **A3: Status bukan Menunggu** → `{status: false, message: "Tahap approval ini sudah diproses sebelumnya.", http_status: 422}` |
| | **A4: Status approval tidak valid** → `{status: false, message: "Status approval tidak valid.", http_status: 422}` |
| | **A5: Tolak tanpa alasan** → `{status: false, message: "Validasi Gagal", msgField: {alasan_penolakan: ["Alasan penolakan wajib diisi."]}}` |
| **Postcondition** | 1. Tahap approval tercatat (Disetujui/Ditolak) |
| | 2. Jika ditolak → seluruh proses berakhir, status pengajuan 'Ditolak', ruangan 'Tersedia' |
| | 3. Jika disetujui di tahap akhir → pengajuan 'Diterima', nomor surat digenerate, jadwal dibuat |
| | 4. Jika disetujui bukan tahap akhir → tahap berikutnya diaktifkan |

---

### UC-JAB-03: Approval Paralel DPK & Presiden BEM

| Atribut | Detail |
|---|---|
| **ID** | UC-JAB-03 |
| **Nama** | Approval Paralel oleh DPK dan Presiden BEM |
| **Aktor** | Pemegang jabatan DPK, Pemegang jabatan Presiden BEM |
| **Tujuan** | Dua pemegang jabatan berbeda memproses tahap approval yang sama secara independen |
| **Precondition** | 1. Aktor terautentikasi (dosen/mahasiswa dengan jabatan approval terkait) |
| | 2. Tahap sebelumnya (Ketua Pelaksana → Ketua Umum) sudah disetujui |
| | 3. `urutan_tahap = 2` untuk kedua posisi (DPK & Presiden BEM) |
| **Main Flow** | 1. Sistem membuat 2 baris `PengajuanApprovalModel` dengan `urutan_tahap = 2` |
| | - Satu baris untuk `jabatan_id` DPK |
| | - Satu baris untuk `jabatan_id` Presiden BEM |
| | 2. Kedua baris memiliki `status_approval = 'Menunggu'` dan `batas_waktu = now()+2hari` (diisi saat tahap 2 diaktifkan) |
| | 3. **Skenario A: DPK setuju duluan** |
| | a. DPK proses → status 'Disetujui' |
| | b. Sistem cek `$stageFullyApproved` → false (Presiden BEM masih 'Menunggu') |
| | c. Return `"Menunggu approval paralel lainnya."` |
| | d. Presiden BEM proses → status 'Disetujui' |
| | e. Sistem cek → `$stageFullyApproved` = true |
| | f. Lanjut ke tahap 3 |
| | **Skenario B: Presiden BEM setuju duluan** — sama dengan Skenario A (urutan terbalik) |
| | **Skenario C: Salah satu menolak** → pengajuan langsung 'Ditolak' (UC-JAB-02 Alternative Flow) |
| **Postcondition** | 1. Kedua tahap paralel diproses independen |
| | 2. Tahap berikutnya hanya aktif jika KEDUA tahap disetujui |

---

## BAGIAN F — PENGAJUAN (5 Use Case)

---

### UC-PGN-01: Pengajuan Peminjaman Ruangan

| Atribut | Detail |
|---|---|
| **ID** | UC-PGN-01 |
| **Nama** | Pengajuan Peminjaman Ruangan |
| **Aktor** | Dosen (DSN), Tendik (TDK), Mahasiswa (MHS), Admin (ADM) |
| **Tujuan** | Aktor mengajukan peminjaman ruangan dengan melengkapi data kegiatan dan memilih ruangan |
| **Precondition** | 1. Aktor terautentikasi |
| | 2. Middleware `authorize:ADM,DSN,TDK,MHS` lolos |
| | 3. Data organisasi tersedia di `m_organisasi` |
| | 4. Ruangan tersedia (status 'Tersedia') pada tanggal yang dipilih — lihat UC-RG-04 untuk pengecekan availability per-tanggal |
| **Main Flow** | 1. Aktor mengakses `GET /pengajuan` → `PengajuanController@index` → view `pengajuan.index` dengan daftar pengajuan miliknya (atau semua jika ADM) |
| | 2. Aktor klik "Tambah" → `GET /pengajuan/create_ajax` |
| | 3. `PengajuanService::createData()` menyediakan: `ruanganList` (semua ruangan), `organisasiList` (semua organisasi), **`ketuaPelaksanaList`** (gabungan seluruh dosen+mahasiswa terdaftar — field baru) |
| | 4. Aktor mengisi form: nama kegiatan, tanggal, jam mulai, jam selesai, jumlah peserta, keterangan (opsional), organisasi pengaju, **Ketua Pelaksana (wajib pilih salah satu dosen/mahasiswa)**, ruangan yang dipilih (checkbox/select multiple); saat tanggal diisi, widget availability per-ruangan (UC-RG-04) menampilkan status Tersedia/Pending/Tidak Tersedia per ruangan |
| | 5. Submit via `POST /pengajuan/ajax` |
| | 6. `PengajuanController@store_ajax` memanggil `PengajuanService::create($request->all(), Auth::id())` |
| | 7. Validasi: semua field required valid (nama max 255, jam selesai after jam mulai, jumlah peserta min 1, organisasi_id exists, ruangan_ids array exists, **`ketua_pelaksana_user_id` required|exists:m_user,user_id**) |
| | 8. `PengajuanModel::create($payload)` — status awal: 'Diajukan', termasuk `ketua_pelaksana_user_id` |
| | 9. `$pengajuan->ruangans()->attach($data['ruangan_ids'])` — simpan relasi many-to-many |
| | 10. `syncRuanganStatus($ruanganIds, 'Diajukan')` — status ruangan menjadi 'Diajukan' |
| | 11. `generateApprovalStages($pengajuan)` — buat seluruh baris approval otomatis, **diawali tahap 0 (Ketua Pelaksana)** via `findOrCreateJabatanKetuaPelaksana()` (lihat UC-JAB-00), baru diikuti tahap 1 (Ketua Umum) dst. dengan `batas_waktu = null` (belum aktif) |
| | 12. Return `{status: true, message: "Pengajuan peminjaman berhasil diajukan!"}` |
| **Alternative Flow** | **A1: Validasi gagal** → return `{status: false, message: "Validasi Gagal", msgField: {...}}` (termasuk jika `ketua_pelaksana_user_id` kosong/tidak valid) |
| **Postcondition** | 1. Data pengajuan tersimpan di `t_pengajuan`, termasuk `ketua_pelaksana_user_id` |
| | 2. Relasi many-to-many ke ruangan tersimpan di `t_pengajuan_ruangan` |
| | 3. Status ruangan berubah menjadi 'Diajukan' |
| | 4. Baris approval berjenjang dibuat di `t_pengajuan_approval`, dimulai dari tahap 0 |
| | 5. Pengajuan masuk ke antrian Ketua Pelaksana (tahap 0) terlebih dahulu — Ketua Umum (tahap 1) baru aktif setelah tahap 0 disetujui |

---

### UC-PGN-02: Verifikasi Pengajuan oleh Admin

| Atribut | Detail |
|---|---|
| **ID** | UC-PGN-02 |
| **Nama** | Verifikasi Manual Pengajuan oleh Admin |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Admin menyetujui atau menolak pengajuan (override approval berjenjang) — tidak wajib menunggu seluruh tahap approval |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| | 2. Ada pengajuan dengan `pengajuan_status = 'Diajukan'` |
| **Main Flow** | **Terima pengajuan:** |
| | 1. Admin lihat detail: `GET /pengajuan/{id}/verifikasi_ajax` |
| | 2. Admin klik "Terima" → `PUT /pengajuan/{id}/terima_ajax` |
| | 3. `PengajuanService::accept($id)` — guard: pengajuan exists, status masih 'Diajukan' |
| | 4. Update `pengajuan_status = 'Diterima'` |
| | 5. Generate nomor surat via `generateNomorSurat()` — format: "{urutan}/SPR-JTI/{bulan_romawi}/{tahun}" |
| | 6. `createJadwalFromPengajuan()` — buat jadwal baru + attach ruangan |
| | 7. `syncRuanganStatus($ruanganIds, 'Diajukan')` |
| | 8. Return `{status: true, message: "Pengajuan diterima dan jadwal berhasil dibuat!"}` |
| | **Tolak pengajuan:** |
| | 1. Admin klik "Tolak" dengan input catatan → `PUT /pengajuan/{id}/tolak_ajax` |
| | 2. `PengajuanService::reject($id, $data)` — validasi catatan nullable string max 500 |
| | 3. Update `pengajuan_status = 'Ditolak'`, `catatan_verifikator = ...` |
| | 4. `syncRuanganStatus($ruanganIds, 'Tersedia')` |
| | 5. Return `{status: true, message: "Pengajuan berhasil ditolak."}` |
| **Alternative Flow** | **A1: Data tidak ditemukan** → `{status: false, message: "...", http_status: 404}` |
| | **A2: Sudah diproses** → `{status: false, message: "Pengajuan ini sudah diproses sebelumnya.", http_status: 422}` |
| **Postcondition** | 1. Status pengajuan berubah (Diterima/Ditolak) |
| | 2. Jika diterima: jadwal baru terbuat, nomor surat digenerate |
| | 3. Jika ditolak: ruangan kembali 'Tersedia' |

---

### UC-PGN-03: Timeline Approval

| Atribut | Detail |
|---|---|
| **ID** | UC-PGN-03 |
| **Nama** | Lihat Timeline Approval Pengajuan |
| **Aktor** | Admin (ADM), Dosen (DSN), Tendik (TDK), Mahasiswa (MHS) |
| **Tujuan** | Aktor melihat riwayat seluruh tahap approval suatu pengajuan secara kronologis |
| **Precondition** | 1. Aktor terautentikasi |
| | 2. Pengajuan dengan `pengajuan_id` yang valid |
| **Main Flow** | 1. Aktor mengakses `GET /pengajuan/{id}/timeline_ajax` |
| | 2. `PengajuanController@timeline_ajax` memanggil `PengajuanService::timelineFor((int) $id)` |
| | 3. Query: `PengajuanApprovalModel::with('verifikator.user')->where('pengajuan_id', $id)->orderBy('urutan_tahap')->get()` |
| | 4. Data dikembalikan ke view `pengajuan.timeline_ajax` |
| | 5. Timeline menampilkan per tahap: urutan, posisi verifikator, nama verifikator, status (Menunggu/Disetujui/Ditolak/Auto Reject), batas waktu, waktu proses, alasan penolakan |
| **Postcondition** | Aktor melihat timeline approval pengajuan |

---

### UC-PGN-04: Cetak Surat Peminjaman (PDF)

| Atribut | Detail |
|---|---|
| **ID** | UC-PGN-04 |
| **Nama** | Cetak Surat Peminjaman Digital |
| **Aktor** | Admin (ADM), Dosen (DSN), Tendik (TDK), Mahasiswa (MHS) |
| **Tujuan** | Aktor mengunduh surat peminjaman dalam format PDF untuk pengajuan yang sudah Diterima |
| **Precondition** | 1. Aktor terautentikasi |
| | 2. Pengajuan dengan `pengajuan_status = 'Diterima'` |
| **Main Flow** | 1. Aktor mengakses `GET /pengajuan/{id}/cetak_surat_ajax` |
| | 2. `PengajuanController@cetak_surat_ajax` memanggil `PengajuanService::findForCetakSurat((int) $id)` |
| | 3. Validasi: data exists, status = 'Diterima' |
| | 4. Load data dengan relasi: `user`, `organisasi`, `ruangans` |
| | 5. Generate PDF menggunakan `Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.surat_peminjaman', compact('pengajuan'))` |
| | 6. Response download: `$pdf->download('Surat_Peminjaman_' . $id . '.pdf')` |
| **Alternative Flow** | **A1: Tidak ditemukan** → abort 404 |
| | **A2: Status bukan Diterima** → abort 422 |
| **Postcondition** | 1. File PDF terdownload |
| | 2. Surat mencakup: nomor surat, logo organisasi, data peminjam, data ruangan |

---

### UC-PGN-05: Auto Reject Tahap Approval

| Atribut | Detail |
|---|---|
| **ID** | UC-PGN-05 |
| **Nama** | Auto Reject Otomatis oleh Sistem |
| **Aktor** | Sistem (Scheduled Command) |
| **Tujuan** | Sistem secara otomatis menolak tahap approval yang melewati batas waktu 2 hari tanpa respons verifikator |
| **Precondition** | 1. Laravel Task Scheduler aktif (`* * * * * php artisan schedule:run`) |
| | 2. Ada data di `t_pengajuan_approval` dengan: `status_approval = 'Menunggu'`, `batas_waktu IS NOT NULL`, `batas_waktu < now()` |
| **Main Flow** | 1. Scheduler menjalankan `php artisan pengajuan:auto-reject` setiap 15 menit |
| | 2. `AutoRejectPengajuan::handle()` memanggil `PengajuanService::autoRejectExpiredApprovals()` |
| | 3. Query: `PengajuanApprovalModel::with('pengajuan.ruangans')->where('status_approval', 'Menunggu')->whereNotNull('batas_waktu')->where('batas_waktu', '<', now())->get()` |
| | 4. Untuk setiap approval yang expired: |
| | a. Cek jika pengajuan masih 'Diajukan' (jika sudah diproses → skip — idempotent) |
| | b. Update approval: `status_approval = 'Auto Reject'`, `alasan_penolakan = 'Tidak diproses dalam batas waktu 2 hari'`, `diproses_pada = now()` |
| | c. Update pengajuan: `pengajuan_status = 'Ditolak'`, `catatan_verifikator = 'Tidak diproses dalam batas waktu 2 hari'` |
| | d. `syncRuanganStatus($ruanganIds, 'Tersedia')` |
| | 5. Return count: `"{count} tahap approval diproses."` |
| **Alternative Flow** | **A1: Tidak ada expired** → return 0, command selesai |
| **Postcondition** | 1. Tahap approval yang expired menjadi 'Auto Reject' |
| | 2. Pengajuan terkait berstatus 'Ditolak' |
| | 3. Ruangan kembali 'Tersedia' |
| | 4. Proses idempotent — aman dijalankan berulang |

---

## BAGIAN G — JADWAL (4 Use Case)

---

### UC-JDW-01: CRUD Jadwal

| Atribut | Detail |
|---|---|
| **ID** | UC-JDW-01 |
| **Nama** | Manajemen Jadwal Peminjaman |
| **Aktor** | Create/Edit/Delete: Admin (ADM) | List/Show: ADM, DSN, TDK, MHS |
| **Tujuan** | Mengelola data jadwal peminjaman ruangan yang sudah disetujui |
| **Precondition** | 1. Aktor terautentikasi |
| | 2. Untuk operasi tulis: role = ADM |
| **Main Flow** | **Lihat daftar jadwal:** |
| | 1. `GET /jadwal` → `JadwalController@index` → `JadwalService::indexData()` |
| | 2. `POST /jadwal/list` → `JadwalController@list` (return `response()->json([])` — masih placeholder) |
| | **Create jadwal:** |
| | 1. `GET /jadwal/create_ajax` → form dengan data ruangan, kelas |
| | 2. Submit `POST /jadwal/ajax` → `JadwalService::create($request->all())` |
| | **Show/Edit/Delete:** |
| | 1. `GET /jadwal/{id}/show_ajax` → `JadwalService::findForShow()` |
| | 2. `GET /jadwal/{id}/edit_ajax` → `JadwalService::formData($id)` |
| | 3. `PUT /jadwal/{id}/update_ajax` → `JadwalService::update()` |
| | 4. `GET /jadwal/{id}/confirm_ajax` → `JadwalService::findForConfirm()` |
| | 5. `DELETE /jadwal/{id}/delete_ajax` → `JadwalService::delete()` |
| **Postcondition** | Data jadwal berhasil ditambahkan/diubah/dihapus |

---

### UC-JDW-02: Update Status Jadwal

| Atribut | Detail |
|---|---|
| **ID** | UC-JDW-02 |
| **Nama** | Perubahan Status Jadwal |
| **Aktor** | Admin (ADM) |
| **Tujuan** | Admin mengubah status jadwal secara berurutan sesuai state machine |
| **Precondition** | 1. Aktor terautentikasi sebagai ADM |
| | 2. Jadwal dengan `jadwal_id` yang valid |
| **Main Flow** | 1. Aktor mengakses `PUT /jadwal/{id}/update_status_ajax` dengan parameter `jadwal_status` |
| | 2. `JadwalController@update_status_ajax` memanggil `JadwalService::updateStatus((int) $id, $request->input('jadwal_status'))` |
| | 3. Status flow: 'Akan Datang' → 'Berlangsung' → 'Ditinjau' → 'Selesai' / 'Dokumentasi Tidak Sesuai' |
| | 4. Return JSON hasil operasi |
| **Postcondition** | Status kolom `jadwal_status` di `t_jadwal` terupdate |

---

### UC-JDW-03: Filter Jadwal per Semester

| Atribut | Detail |
|---|---|
| **ID** | UC-JDW-03 |
| **Nama** | Filter Daftar Jadwal Berdasarkan Semester |
| **Aktor** | Admin (ADM), Dosen (DSN), Tendik (TDK), Mahasiswa (MHS) |
| **Tujuan** | Aktor menyaring daftar jadwal berdasarkan semester tertentu |
| **Precondition** | 1. Aktor terautentikasi |
| **Main Flow** | 1. Pada halaman `/jadwal`, aktor memilih filter semester |
| | 2. DataTables mengirim parameter filter ke `POST /jadwal/list` |
| | 3. `JadwalService` memproses filter dan mengembalikan data yang difilter |
| **Postcondition** | DataTables menampilkan jadwal sesuai filter semester |

---

### UC-JDW-04: Get Kelas by Prodi

| Atribut | Detail |
|---|---|
| **ID** | UC-JDW-04 |
| **Nama** | Data Kelas Berdasarkan Program Studi |
| **Aktor** | Admin (ADM), Dosen (DSN), Tendik (TDK), Mahasiswa (MHS) |
| **Tujuan** | Mendapatkan daftar kelas untuk program studi tertentu (cascading dropdown) |
| **Precondition** | 1. Aktor terautentikasi |
| **Main Flow** | 1. Aktor memilih Prodi pada form |
| | 2. AJAX call ke `GET /jadwal/get_kelas_by_prodi/{prodi_id}` |
| | 3. `JadwalService::kelasByProdi((int) $prodi_id)` mengambil data kelas dari database |
| | 4. Return JSON array kelas untuk prodi tersebut |
| **Postcondition** | Dropdown kelas terisi dengan data yang sesuai |

---

## BAGIAN H — DASHBOARD (1 Use Case)

---

### UC-DSH-01: Dashboard Role-Aware

| Atribut | Detail |
|---|---|
| **ID** | UC-DSH-01 |
| **Nama** | Dashboard Berdasarkan Role Pengguna |
| **Aktor** | Admin (ADM), Dosen (DSN), Tendik (TDK), Mahasiswa (MHS) — level VRF terpisah sudah dihapus, pemegang jabatan approval kini login sebagai DSN/MHS biasa |
| **Tujuan** | Aktor melihat dashboard yang menampilkan informasi relevan sesuai role-nya |
| **Precondition** | 1. Aktor terautentikasi, melewati middleware `auth` |
| **Main Flow** | 1. Aktor mengakses `GET /dashboard` |
| | 2. `WelcomeController@index` memanggil `DashboardService::getWelcomeDashboardData(Auth::user())` |
| | 3. Sistem mengambil statistik umum: `totalRuangan`, `jadwalHariIni`, `totalRuanganKosong`, `totalUser`, `totalMahasiswa`, `totalDosen`, `totalTendik` |
| | 4. Sistem mengambil data chart: top 5 ruangan terfavorit (bar chart), distribusi peminjam per role (pie chart), tren peminjaman 6 bulan (line chart) |
| | 5. Sistem menentukan view berdasarkan role: |
| | - ADM → `admin.welcome` + `earlyWarning` (pengajuan mendekati auto-reject, batas 12 jam) |
| | - DSN/TDK/MHS → `dosen.welcome` / `tendik.welcome` / `mahasiswa.welcome` + `pengajuanAktif` (pengajuan miliknya yang masih 'Diajukan') + `ruanganHariIni` (tabel ruangan dengan jadwal hari ini) |
| | - VRF → `verifikator.welcome` + `antrianApproval` (antrian approval miliknya yang 'Menunggu' dan aktif) |
| | 6. Sistem merender view yang sesuai dengan data |
| **Alternative Flow** | **A1: User tidak terautentikasi** → redirect ke landing page |
| **Postcondition** | 1. Aktor melihat dashboard yang disesuaikan dengan role |
| | 2. Data statistik dan chart ditampilkan |

---

## LAMPIRAN: MATRIKS USE CASE vs AKTOR

| ID Use Case | Nama | ADM | DSN | TDK | MHS | System |
|---|---|---|---|---|---|---|
| UC-AUTH-01 | Login Pengguna | ✓ | ✓ | ✓ | ✓ | |
| UC-AUTH-02 | Logout Pengguna | ✓ | ✓ | ✓ | ✓ | |
| UC-AUTH-03 | Manajemen Profil | ✓ | ✓ | ✓ | ✓ | |
| UC-MD-01 | CRUD Admin | ✓ | | | | |
| UC-MD-02 | Import Admin Excel | ✓ | | | | |
| UC-MD-03 | CRUD Dosen | ✓ | | | | |
| UC-MD-04 | Import Dosen Excel | ✓ | | | | |
| UC-MD-05 | CRUD Tendik | ✓ | | | | |
| UC-MD-06 | Import Tendik Excel | ✓ | | | | |
| UC-MD-07 | CRUD Mahasiswa | ✓ | | | | |
| UC-MD-08 | Import Mahasiswa Excel | ✓ | | | | |
| UC-MD-09 | CRUD Prodi | ✓ | | | | |
| UC-MD-10 | CRUD Kelas | ✓ | | | | |
| UC-MD-11 | Upload/Download Formulir | ✓(U) | ✓(D) | ✓(D) | ✓(D) | |
| UC-RG-01 | CRUD Ruangan | ✓(W) | ✓(R) | ✓(R) | ✓(R) | |
| UC-RG-02 | Sinkronisasi Status Ruangan | | | | | ✓ |
| UC-RG-03 | Kategori Ruangan | ✓ | | | | |
| UC-RG-04 | Status Ruangan Per-Tanggal (Baru) | ✓ | ✓ | ✓ | ✓ | |
| UC-ORG-01 | CRUD Organisasi | ✓ | | | | |
| UC-ORG-02 | Pemetaan Jabatan Approval | ✓ | | | | |
| UC-JAB-00 | Konfirmasi Ketua Pelaksana (Baru) | ✓* | ✓* | ✓* | ✓* | |
| UC-JAB-01 | Antrian Approval | ✓* | ✓* | ✓* | ✓* | |
| UC-JAB-02 | Proses Approval | ✓* | ✓* | ✓* | ✓* | |
| UC-JAB-03 | Approval Paralel | ✓* | ✓* | ✓* | ✓* | |
| UC-PGN-01 | Pengajuan Peminjaman | ✓ | ✓ | ✓ | ✓ | |
| UC-PGN-02 | Verifikasi Admin | ✓ | | | | |
| UC-PGN-03 | Timeline Approval | ✓ | ✓ | ✓ | ✓ | |
| UC-PGN-04 | Cetak Surat PDF | ✓ | ✓ | ✓ | ✓ | |
| UC-PGN-05 | Auto Reject | | | | | ✓ |
| UC-JDW-01 | CRUD Jadwal | ✓(W) | ✓(R) | ✓(R) | ✓(R) | |
| UC-JDW-02 | Update Status Jadwal | ✓ | | | | |
| UC-JDW-03 | Filter Jadwal | ✓ | ✓ | ✓ | ✓ | |
| UC-JDW-04 | Get Kelas by Prodi | ✓ | ✓ | ✓ | ✓ | |
| UC-DSH-01 | Dashboard Role-Aware | ✓ | ✓ | ✓ | ✓ | |

**Keterangan:** ✓ = Akses penuh, (W) = Write, (R) = Read only, (U) = Upload only, (D) = Download only. Kolom **VRF dihapus** (level akun tsb sudah tidak ada). ✓* = route accessible ke semua level yang lolos middleware `authorize:ADM,DSN,TDK,MHS`, tetapi FUNGSIONAL hanya untuk user yang benar-benar memegang jabatan approval (`m_jabatan_approval`) terkait pengajuan tersebut — user lain akan melihat antrian kosong atau menerima 403 saat mencoba memproses tahap yang bukan miliknya.

