# Black Box Testing Scenarios — SPR JTI

> **Proyek:** Sistem Peminjaman Ruangan: PLP JTI  
> **Role:** QA Engineer  
> **Sumber:** Use Case Sistem (docs/use-case-sistem.md), Source Code Analysis  
> **Total Skenario:** ~168+  
> **Update terakhir:** Redesain Jabatan Approval (VRF dihapus), tahap Ketua Pelaksana (UC-JAB-00), status ruangan per-tanggal (UC-RG-04) — lihat bagian ringkasan di akhir dokumen.

---

## BAGIAN A — AUTHENTICATION

### UC-AUTH-01: Login Pengguna

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **AUTH-01-POS-01** | Positive | Login sukses dengan kredensial valid | username: "admin01", password: "password123" | Response JSON `{status: true, message: "Selamat datang, Admin.", redirect: "/dashboard"}`, redirect ke dashboard | `AuthService::login()` → `Auth::attempt()` sukses, `getUserDisplayName()` return admin_nama |
| **AUTH-01-POS-02** | Positive | Login sukses sebagai Dosen | username: "dosen01", password: "password123" | `{status: true, message: "Selamat datang, Dr. Andi.", redirect: "/dashboard"}` | Level_id=2, dosen_nama diambil dari m_dosen |
| **AUTH-01-POS-03** | Positive | Login sukses sebagai Mahasiswa | username: "mhs01", password: "password123" | `{status: true, message: "Selamat datang, Budi.", redirect: "/dashboard"}` | Level_id=4, mahasiswa_nama diambil dari m_mahasiswa |
| **AUTH-01-POS-04** | Positive | Login AJAX request | Request dengan header X-Requested-With: XMLHttpRequest | Response JSON `{status: true, ...}` tanpa redirect | `$request->ajax()` → return JSON |
| **AUTH-01-NEG-01** | Negative | Login dengan username salah | username: "tidakada", password: "password123" | `{status: false, message: "Login gagal, username atau password salah."}` | `Auth::attempt()` return false |
| **AUTH-01-NEG-02** | Negative | Login dengan password salah | username: "admin01", password: "salahpassword" | `{status: false, message: "Login gagal, username atau password salah."}` | `Auth::attempt()` return false |
| **AUTH-01-NEG-03** | Negative | Login dengan username dan password kosong | username: "", password: "" | `{status: false, message: "Login gagal, username atau password salah."}` | Trim menghasilkan string kosong, return early |
| **AUTH-01-NEG-04** | Negative | Login dengan spasi saja | username: "   ", password: "   " | `{status: false, message: "Login gagal, username atau password salah."}` | Trim jadi string kosong |
| **AUTH-01-VAL-01** | Validation | Login dengan username null | username: null, password: "password123" | `{status: false, message: "Login gagal, username atau password salah."}` | `$username = trim((string)($credentials['username'] ?? ''))` jadi '' |
| **AUTH-01-VAL-02** | Validation | Login dengan password null | username: "admin01", password: null | `{status: false, message: "Login gagal, username atau password salah."}` | `$password = (string)($credentials['password'] ?? '')` jadi '' |
| **AUTH-01-BOU-01** | Boundary | Login dengan username panjang (255 karakter) | username: "a" x 255, password: "password123" | Tergantung database, jika tidak ada → `{status: false, message: "Login gagal..."}` | Tidak ada validasi panjang di service |
| **AUTH-01-EXC-01** | Exception | Exception saat Auth::attempt() | Database down, koneksi terputus | `{status: false, message: "Terjadi kesalahan sistem. Silakan coba lagi."}`, error tercatat di log | Catch `\Exception`, `Log::error()` |
| **AUTH-01-PER-01** | Permission | Akses halaman login saat sudah login | User sudah memiliki session valid, akses GET /login | Redirect ke `/dashboard` | `AuthController@login`: `if ($this->authService->isAuthenticated()) return redirect('/dashboard')` |
| **AUTH-01-EDG-01** | Edge Case | Login dengan username yang terdaftar tetapi level_id tidak punya role-related data | username: "user_no_data", tidak punya admin/dosen/tendik/mahasiswa | `{status: true, message: "Selamat datang, User.", redirect: "/dashboard"}` | `getUserDisplayName()` fallback ke username jika role tidak match |

---

### UC-AUTH-02: Logout Pengguna

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **AUTH-02-POS-01** | Positive | Logout sukses | User terautentikasi, akses GET /logout | Session dihapus, redirect ke `/` | `Auth::logout()`, `$request->session()->invalidate()`, `$request->session()->regenerateToken()` |
| **AUTH-02-EXC-01** | Exception | Exception saat Auth::logout() | Throw exception pada logout | Sistem tetap lanjut ke invalidate session dan regenerate token, error tercatat di log | `Log::error()`, try-catch dengan fallback |
| **AUTH-02-PER-01** | Permission | Akses logout tanpa login | User tidak terautentikasi, akses GET /logout | Redirect ke halaman login | Middleware `auth` redirect ke route('login') |
| **AUTH-02-EDG-01** | Edge Case | Logout double (2x berturut-turut) | User logout, lalu akses /logout lagi | Redirect ke halaman login (karena session sudah hilang) | Middleware `auth` redirect |

---

### UC-AUTH-03: Manajemen Profil Pengguna

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **AUTH-03-POS-01** | Positive | Lihat profil sukses | GET /profile/show_ajax (AJAX) | View `profile.show_ajax` dengan data profil sesuai role | `ProfileService::showData(Auth::user())` |
| **AUTH-03-POS-02** | Positive | Edit profil sukses (username saja) | PUT /profile/update_ajax, username: "newname" | `{status: true, msg: "Profil berhasil diperbarui!"}` | `$user->username = $data['username']; $user->save()` |
| **AUTH-03-POS-03** | Positive | Edit profil sukses (password & no_hp) | PUT /profile/update_ajax, password: "newpass123", no_hp: "08123456789" | `{status: true, msg: "Profil berhasil diperbarui!"}` | `$user->password = Hash::make($data['password']); updatePhone()` |
| **AUTH-03-NEG-01** | Negative | Update dengan username sudah dipakai user lain | username: "admin01" (sudah ada) | `{status: false, msg: "Validasi gagal", errors: {username: ["..."}}}` | Rule unique:m_user,username,{user_id},user_id |
| **AUTH-03-NEG-02** | Negative | Update dengan password terlalu pendek | password: "ab" (2 karakter) | `{status: false, msg: "Validasi gagal", errors: {...}}` | Rule password nullable|min:5 |
| **AUTH-03-VAL-01** | Validation | Update dengan username null | username: null | Validasi gagal jika null | Rule string|min:3 |
| **AUTH-03-VAL-02** | Validation | Update dengan semua field kosong | username: "", password: "", no_hp: "" | Berhasil (username tetap, password tidak diubah, no_hp null) | `$data['username'] ?? $user->username`, `!empty($data['password'])` |
| **AUTH-03-BOU-01** | Boundary | Update dengan username 3 karakter | username: "abc" | Berhasil | Rule min:3 |
| **AUTH-03-BOU-02** | Boundary | Update dengan username 2 karakter | username: "ab" | Gagal | Rule min:3 |
| **AUTH-03-BOU-03** | Boundary | Update dengan password 5 karakter | password: "abcde" | Berhasil | Rule min:5 |
| **AUTH-03-BOU-04** | Boundary | Update dengan password 4 karakter | password: "abcd" | Gagal | Rule min:5 |
| **AUTH-03-PER-01** | Permission | Akses profil tanpa login | Tidak terautentikasi, akses GET /profile/show_ajax | Redirect ke halaman login | Middleware `auth` |
| **AUTH-03-EDG-01** | Edge Case | Request non-AJAX ke endpoint profil | Request biasa (bukan AJAX) ke /profile/show_ajax | Redirect ke `/` | `$request->ajax() ? view(...) : redirect('/')` (khusus show & edit) |
| **AUTH-03-EDG-02** | Edge Case | Update no_hp untuk role yang tidak punya field noHP | Role tanpa implementasi updatePhone match | Tidak error, profile di-update tanpa noHP | `$column = [...][$user->getRole()] ?? null` → jika null, skip updatePhone |

---

## BAGIAN B — MASTER DATA

### UC-MD-01: CRUD Admin

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **MD-01-POS-01** | Positive | Lihat daftar admin sukses | GET /admin | View `admin.index` dengan data admin + DataTables | `AdminController@index` |
| **MD-01-POS-02** | Positive | DataTables list admin | POST /admin/list (DataTables params) | JSON DataTables dengan data admin | `AdminController@list` → `adminDataTable()` |
| **MD-01-POS-03** | Positive | Tambah admin sukses (semua field valid) | POST /admin/ajax, {username: "admin_baru", password: "pass123", admin_nama: "Andi", admin_nidn: "1234567890", prodi_id: 1, admin_noHp: "08123456789"} | JSON `{success: true, message: "Data admin berhasil ditambahkan", ...}` status 201 | `UserService::createAdmin()` → DB transaction create user + admin |
| **MD-01-POS-04** | Positive | Lihat detail admin | GET /admin/1/show_ajax (AJAX) | View `admin.show_ajax` dengan data admin + relasi user & prodi | `AdminModel::with(['user','prodi'])->find($id)` |
| **MD-01-POS-05** | Positive | Edit admin sukses | PUT /admin/1/update_ajax, {username: "admin_update", admin_noHp: "0811111111"} | JSON `{success: true, message: "User berhasil diperbarui"}` | `UserService::updateUser()` |
| **MD-01-POS-06** | Positive | Hapus admin sukses | DELETE /admin/1/delete_ajax | JSON `{success: true, message: "User berhasil dihapus"}` | `UserService::deleteUser()` |
| **MD-01-NEG-01** | Negative | Tambah admin dengan username duplikat | username: "existing_admin" | JSON `{success: false, message: "Username sudah terdaftar", errors: {...}}` | `validateUniqueUsername()` |
| **MD-01-NEG-02** | Negative | Edit admin yang tidak ada | PUT /admin/999/update_ajax | JSON `{status: false, message: "Data admin tidak ditemukan!", ...}` status 404 | `AdminModel::find($id)` null → `notFoundErrorResponse()` |
| **MD-01-NEG-03** | Negative | Hapus admin yang tidak ada | DELETE /admin/999/delete_ajax | JSON error 404 | `AdminModel::find($id)` null |
| **MD-01-VAL-01** | Validation | Tambah admin dengan field kosong | POST /admin/ajax, {username: "", password: "", admin_nama: "", admin_nidn: "", prodi_id: ""} | JSON validasi gagal, msgField berisi error per field | `UserService::createAdmin()` → `validatePersonPayload()` |
| **MD-01-VAL-02** | Validation | Tambah admin dengan password kurang dari 6 karakter | password: "12345" | JSON validasi gagal | Validasi di AdminController (min:6) |
| **MD-01-PER-01** | Permission | Akses CRUD admin tanpa login | Request tanpa autentikasi | Redirect ke halaman login | Middleware `auth` |
| **MD-01-PER-02** | Permission | Akses CRUD admin sebagai Dosen | Login sebagai DSN, akses GET /admin | HTTP 403 Forbidden | Middleware `authorize:ADM` |
| **MD-01-PER-03** | Permission | Akses CRUD admin sebagai Mahasiswa | Login sebagai MHS, akses GET /admin/create_ajax | HTTP 403 Forbidden | Middleware `authorize:ADM` |
| **MD-01-PER-04** | Permission | Akses CRUD admin sebagai pemegang jabatan approval (bukan level ADM) | Login sebagai DSN/MHS pemegang jabatan approval, akses POST /admin/ajax | HTTP 403 Forbidden | Middleware `authorize:ADM` — jabatan approval tidak memberi akses level ADM |
| **MD-01-EDG-01** | Edge Case | Non-AJAX request ke endpoint AJAX | Request biasa ke /admin/create_ajax | Redirect ke `/` | `isAjaxRequest()` → `redirect('/')` |
| **MD-01-EDG-02** | Edge Case | DataTables dengan filter prodi | POST /admin/list, filter prodi_id = 5 | Hanya admin dengan prodi_id=5 yang tampil | `buildAdminQuery()` → `where('prodi_id', $request->prodi_id)` |
| **MD-01-EDG-03** | Edge Case | DataTables error handling | Database error saat query list | JSON error response dengan draw, recordsTotal=0 | `dataTableErrorResponse()` |

---

### UC-MD-02: Import Admin via Excel

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **MD-02-POS-01** | Positive | Import admin sukses | File Excel valid dengan 10 baris data admin | `{status: true, message: "Data berhasil diimport!"}` | Validasi mimes:xlsx, proses baris per baris |
| **MD-02-NEG-01** | Negative | Upload file bukan Excel | File .txt atau .jpg | `{status: false, message: "Validasi Gagal", msgField: {...}}` | Validasi mimes:xlsx |
| **MD-02-NEG-02** | Negative | Upload file > 1MB | File 2MB | `{status: false, message: "Validasi Gagal", msgField: {...}}` | Validasi max:1024 |
| **MD-02-VAL-01** | Validation | Upload tanpa file | No file uploaded | `{status: false, message: "Validasi Gagal", msgField: {...}}` | Validasi required |
| **MD-02-PER-01** | Permission | Import sebagai Dosen | Login DSN, akses GET /admin/import | HTTP 403 | Middleware `authorize:ADM` |

---

### UC-MD-03: CRUD Dosen

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **MD-03-POS-01** | Positive | Tambah dosen sukses | POST /dosen/ajax, {username: "dosen_baru", password: "pass123", dosen_nama: "Prof. Joko", dosen_nidn: "1234567890", prodi_id: 1, dosen_noHp: "08123456789"} | JSON `{success: true, message: "Data dosen berhasil ditambahkan", ...}` status 201 | `UserService::createDosen()` → resolve level DSN |
| **MD-03-POS-02** | Positive | DataTables list dosen dengan filter prodi | POST /dosen/list, prodi_id = 2 | DataTables hanya menampilkan dosen prodi_id=2 | `buildDosenQuery()` |
| **MD-03-POS-03** | Positive | Edit dosen sukses (hanya noHP) | PUT /dosen/1/update_ajax, {dosen_noHp: "0811111111"} | JSON sukses | `UserService::updateUser()` → `updateRoleData()` update dosen_noHp |
| **MD-03-NEG-01** | Negative | Tambah dosen dengan NIDN duplikat sebagai username | username (NIDN): "1234567890" sudah terdaftar | JSON `{success: false, message: "Username sudah terdaftar"}` | Validasi unique username |
| **MD-03-NEG-02** | Negative | Hapus dosen yang tidak ada | DELETE /dosen/999/delete_ajax | JSON error 404 | `DosenModel::find($id)` null |
| **MD-03-PER-01** | Permission | Tambah dosen sebagai Mahasiswa | Login MHS, POST /dosen/ajax | HTTP 403 | Middleware `authorize:ADM` |

---

### UC-MD-04: Import Dosen via Excel

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **MD-04-POS-01** | Positive | Import dosen sukses | File Excel valid: kolom A=NIDN, B=Nama, C=Prodi, D=NoHP | `{success: true, message: "5 data dosen berhasil diimpor"}` | `ImportService::importDosenFromExcel()` |
| **MD-04-NEG-01** | Negative | Import dengan NIDN duplikat di file | 2 baris dengan NIDN sama | Rollback, return error "NIDN sudah terdaftar" per baris | Validasi `$this->userRepository->usernameExists($nidn)` |
| **MD-04-NEG-02** | Negative | Import dengan nama prodi tidak ditemukan | Kolom C = "ProdiFiktif" | Rollback, return error "Program Studi ... tidak ditemukan" | `$prodiMap[$prodiName] ?? null` |
| **MD-04-NEG-03** | Negative | Import dengan baris tidak lengkap | Baris dengan NIDN kosong | Rollback, return error "Baris X: Data tidak lengkap" | Validasi `$nidn === '' || $nama === '' || $prodiName === ''` |
| **MD-04-VAL-01** | Validation | Upload file format salah | File .csv bukan .xlsx | JSON validasi gagal | Validasi `mimes:xlsx` |
| **MD-04-EXC-01** | Exception | File rusak (corrupt) | File Excel corrupt | `{success: false, message: "Gagal membaca file: ..."}` | Catch `\Throwable` pada IOFactory |
| **MD-04-EDG-01** | Edge Case | Import file dengan header saja (1 baris, tanpa data) | File hanya berisi header | `{success: false, message: "Tidak ada data yang diimport."}` | `$dataRowCount === 0` → rollback |

---

### UC-MD-05: CRUD Tendik

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **MD-05-POS-01** | Positive | Tambah tendik sukses | POST /tendik/ajax, {username: "tendik_baru", password: "pass123", tendik_nama: "Siti", tendik_nidn: "1234567890", tendik_noHp: "08123456789"} | JSON sukses status 201 | `UserService::createTendik()` → resolve level TDK |
| **MD-05-NEG-01** | Negative | DataTables tendik error | Database error saat query | JSON DataTables error dengan draw & recordsTotal=0 | `dataTableErrorResponse()` |
| **MD-05-PER-01** | Permission | Show tendik sebagai Tendik (role sendiri) | Login TDK, GET /tendik/1/show_ajax | HTTP 403 | Middleware `authorize:ADM` |

---

### UC-MD-06: Import Tendik via Excel

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **MD-06-POS-01** | Positive | Import tendik sukses | File Excel valid: kolom A=NIDN, B=Nama, C=NoHP | `{success: true, message: "3 data tendik berhasil diimpor"}` | `ImportService::importTendikFromExcel()` |
| **MD-06-NEG-01** | Negative | File terlalu besar | File > 2MB | JSON validasi gagal | Validasi `max:2048` (KB) |
| **MD-06-EDG-01** | Edge Case | Import 1 baris data saja | File dengan 1 baris data valid | Sukses, count=1 | Proses normal |

---

### UC-MD-07: CRUD Mahasiswa

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **MD-07-POS-01** | Positive | Tambah mahasiswa sukses | POST /mahasiswa/ajax, {username: "mhs_baru", password: "pass123", mahasiswa_nama: "Budi", mahasiswa_nim: "12345678", prodi_id: 1, kelas_id: 1, mahasiswa_noHp: "08123456789"} | JSON sukses status 201 | `UserService::createMahasiswa()` |
| **MD-07-POS-02** | Positive | Get kelas by prodi (cascading dropdown) | GET /mahasiswa/get_kelas_by_prodi/1 | JSON array kelas untuk prodi_id=1 | `KelasModel::where('prodi_id', $prodi_id)->get()` |
| **MD-07-NEG-01** | Negative | Tambah mahasiswa dengan NIM duplikat | mahasiswa_nim: "12345678" sudah terdaftar | JSON error unique username | Validasi username (NIM) unique |
| **MD-07-NEG-02** | Negative | Get kelas by prodi dengan prodi_id tidak ada | GET /mahasiswa/get_kelas_by_prodi/999 | JSON array kosong [] | Query return empty collection |
| **MD-07-VAL-01** | Validation | Tambah mahasiswa tanpa prodi_id | prodi_id: null/kosong | Validasi gagal | Required field di `validatePersonPayload()` |

---

### UC-MD-08: Import Mahasiswa via Excel

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **MD-08-POS-01** | Positive | Import mahasiswa sukses | File Excel: A=NIM, B=Nama, C=Prodi, D=Kelas, E=NoHP | `{success: true, message: "10 data mahasiswa berhasil diimpor"}` | `ImportService::importMahasiswaFromExcel()` |
| **MD-08-NEG-01** | Negative | Nama kelas tidak ditemukan di database | Kolom D = "KelasFiktif" | Rollback, error "Kelas ... tidak ditemukan" | `$kelasMap[$kelasName] ?? null` |
| **MD-08-NEG-02** | Negative | NIM duplikat di database | NIM sudah terdaftar sebagai username | Rollback, error per baris | `$this->userRepository->usernameExists($nim)` |

---

### UC-MD-09: CRUD Program Studi

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **MD-09-POS-01** | Positive | Tambah prodi sukses | POST /prodi/ajax, {prodi_kode: "TI", prodi_nama: "Teknik Informatika"} | JSON sukses status 201 | `ProdiCrudService::create()` |
| **MD-09-POS-02** | Positive | Hapus prodi sukses | DELETE /prodi/1/delete_ajax | JSON sukses | `ProdiCrudService::delete()` |
| **MD-09-NEG-01** | Negative | Hapus prodi yang masih memiliki relasi (dosen/mahasiswa) | DELETE /prodi/1 (prodi masih dipakai) | Error database (FK constraint) | Tidak ada soft delete, cascade tergantung DB |
| **MD-09-EDG-01** | Edge Case | Prodi tidak punya method show_ajax | Akses GET /prodi/1/show_ajax | 404 Not Found (route tidak terdaftar) | Route tidak ada |

---

### UC-MD-10: CRUD Kelas

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **MD-10-POS-01** | Positive | Tambah kelas sukses | POST /kelas/ajax, {prodi_id: 1, kelas_nama: "TI-1A"} | JSON sukses | `KelasService::create()` |
| **MD-10-NEG-01** | Negative | Edit kelas tidak ditemukan | GET /kelas/999/edit_ajax | HTTP 404 | `abort_if(!$data['kelas'], 404, ...)` |

---

### UC-MD-11: Upload dan Download Formulir

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **MD-11-POS-01** | Positive | Upload formulir sukses (Admin) | POST /formulir/upload, file PDF valid | JSON `{status: true, message: ...}` | `FileService::uploadFormulir()` |
| **MD-11-POS-02** | Positive | Download formulir sukses (Dosen) | GET /formulir/download (sebagai DSN) | File PDF terdownload | `FileService::downloadFormulir()` |
| **MD-11-NEG-01** | Negative | Upload file bukan PDF | File .docx | JSON error `{status: false, message: ...}` | Validasi `mimes:pdf` |
| **MD-11-NEG-02** | Negative | Upload file > 2MB | File 3MB | JSON error | Validasi `max:2048` |
| **MD-11-PER-01** | Permission | Upload sebagai Dosen (bukan Admin) | Login DSN, POST /formulir/upload | HTTP 403 | Middleware `authorize:ADM` |
| **MD-11-PER-02** | Permission | Download sebagai Admin | Login ADM, GET /formulir/download | HTTP 403 | Middleware `authorize:DSN,TDK,MHS` |
| **MD-11-PER-03** | Permission | Download sebagai Admin (dicek ulang, bukan role turunan jabatan) | Login ADM (yang juga kebetulan pemegang jabatan approval), GET /formulir/download | HTTP 403 | Middleware `authorize:DSN,TDK,MHS` — level akun ADM tetap yang menentukan, bukan jabatan approval |
| **MD-11-EDG-01** | Edge Case | Download formulir saat belum ada file diupload | Belum ada formulir di database | Redirect back with error `"..."` | `$result['success']` false → `back()->with('error', ...)` |

---

## BAGIAN C — RUANGAN

### UC-RG-01: CRUD Ruangan

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **RG-01-POS-01** | Positive | Lihat daftar ruangan sebagai Admin | GET /ruangan (ADM) | View `ruangan.index` dengan semua data ruangan | `RuanganService::indexData()` |
| **RG-01-POS-02** | Positive | Lihat daftar ruangan sebagai Mahasiswa | GET /ruangan (MHS) | View `ruangan.index` (read-only, tanpa tombol aksi CRUD) | Middleware `authorize:ADM,DSN,TDK,MHS` |
| **RG-01-POS-03** | Positive | Tambah ruangan sukses (Admin) | POST /ruangan/ajax, {ruangan_kode: "R101", ruangan_nama: "Lab Komputer", ruangan_fasilitas: "AC, LCD", ruangan_kuota: 30, ruangan_status: "Tersedia", ruangan_kategori: "Jurusan"} | JSON sukses status 201 | `RuanganService::create()` |
| **RG-01-POS-04** | Positive | Show ruangan detail | GET /ruangan/1/show_ajax (AJAX) | View `ruangan.show_ajax` | `RuanganService::find()` |
| **RG-01-POS-05** | Positive | Edit ruangan sukses | PUT /ruangan/1/update_ajax, {ruangan_nama: "Lab Baru"} | JSON sukses | `RuanganService::update()` |
| **RG-01-POS-06** | Positive | Hapus ruangan sukses | DELETE /ruangan/1/delete_ajax | JSON sukses | `RuanganService::delete()` |
| **RG-01-NEG-01** | Negative | Tambah ruangan dengan kode duplikat | ruangan_kode: "R101" sudah ada | Error database (unique constraint) atau validasi | Tergantung implementasi validasi |
| **RG-01-NEG-02** | Negative | Hapus ruangan yang tidak ada | DELETE /ruangan/999/delete_ajax | JSON error 404 | `$this->ruanganService->find((int) $id)` null |
| **RG-01-PER-01** | Permission | Tambah ruangan sebagai Dosen | Login DSN, POST /ruangan/ajax | HTTP 403 | Middleware `authorize:ADM` (hanya untuk create/edit/delete route) |
| **RG-01-PER-02** | Permission | Hapus ruangan sebagai Mahasiswa | Login MHS, DELETE /ruangan/1/delete_ajax | HTTP 403 | Middleware `authorize:ADM` |
| **RG-01-EDG-01** | Edge Case | DataTables ruangan dengan role berbeda | POST /ruangan/list (sebagai MHS) | DataTables dengan data ruangan (read-only) | `RuanganService::dataTable(Auth::user()->getRole())` |

---

### UC-RG-04: Status Ruangan Per-Tanggal (Baru)

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **AV-01-POS-01** | Positive | Availability semua ruangan untuk tanggal tanpa booking | GET /ruangan/availability_ajax?tanggal=2026-08-01 (belum ada jadwal/pengajuan pending) | JSON list ruangan semua berstatus 'Tersedia' | `RuanganService::availabilityForAllRoomsOnDate()` |
| **AV-01-POS-02** | Positive | Ruangan berstatus Pending karena ada pengajuan menunggu | Ruangan X punya pengajuan `status='Diajukan'` di tanggal tsb | Ruangan X → status 'Pending' | Precedence: pending pengajuan > Tersedia |
| **AV-01-POS-03** | Positive | Ruangan berstatus Tidak Tersedia karena jadwal aktif | Ruangan X punya Jadwal dengan `jadwal_status != 'Selesai'` di tanggal tsb | Ruangan X → 'Tidak Tersedia' | `JADWAL_STATUS_MENEMPATI` const dicek |
| **AV-01-POS-04** | Positive | Manual override `ruangan_status='Tidak Tersedia'` menang atas semua | Ruangan X manual status 'Tidak Tersedia', tidak ada jadwal/pengajuan | Ruangan X tetap 'Tidak Tersedia' | Precedence tertinggi: manual override |
| **AV-01-POS-05** | Positive | Jadwal 'Selesai' tidak menghalangi availability | Ruangan X punya Jadwal dengan `jadwal_status='Selesai'` | Ruangan X → 'Tersedia' (jika tidak ada faktor lain) | `'Selesai'` dikecualikan dari `JADWAL_STATUS_MENEMPATI` |
| **AV-01-VAL-01** | Validation | Tanggal tidak dikirim | GET /ruangan/availability_ajax (tanpa param tanggal) | Validasi gagal / 422 | Rule `required|date` |
| **AV-01-PER-01** | Permission | Akses availability_ajax tanpa login | Tidak terautentikasi | Redirect ke login | Middleware `auth` |
| **AV-02-POS-01** | Positive | Kalender bulanan ruangan tertentu | GET /ruangan/1/kalender_ajax | View kalender bulan berjalan, grid tanggal dengan warna sesuai status | `RuanganController::kalender_ajax()` |
| **AV-02-POS-02** | Positive | Navigasi bulan sebelumnya/berikutnya | GET /ruangan/1/kalender_ajax?year=2026&month=9 | Kalender menampilkan bulan September 2026 | Parameter `year`/`month` di-parse |
| **AV-03-POS-01** | Positive | Data kalender bulanan (JSON, dipakai AJAX kalender) | GET /ruangan/1/kalender_data_ajax?year=2026&month=8 | JSON per-tanggal: status (Tersedia/Pending/Tidak Tersedia) | `RuanganService::monthlyAvailability()` — bulk query, tanpa N+1 |
| **AV-03-EDG-01** | Edge Case | Konsistensi data antara availability_ajax dan kalender_data_ajax | Query tanggal yang sama dari kedua endpoint | Status yang dikembalikan identik | Kedua endpoint pakai precedence logic yang sama |
| **AV-03-NEG-01** | Negative | Ruangan tidak ditemukan | GET /ruangan/999/kalender_data_ajax | JSON error 404 | `RuanganModel::find($id)` null |

---

### UC-RG-02: Sinkronisasi Status Ruangan

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **RG-02-POS-01** | Positive | Status berubah menjadi Diajukan saat pengajuan dibuat | Pengajuan baru dibuat dengan ruangan_id=[1,2] | `ruangan_status` untuk id 1 & 2 menjadi 'Diajukan' | `PengajuanService::syncRuanganStatus([1,2], 'Diajukan')` |
| **RG-02-POS-02** | Positive | Status berubah menjadi Tersedia saat pengajuan ditolak | Admin menolak pengajuan dengan ruangan_id=[1] | `ruangan_status` id 1 menjadi 'Tersedia' | `PengajuanService::reject()` → `syncRuanganStatus([1], 'Tersedia')` |
| **RG-02-POS-03** | Positive | Status berubah menjadi Tersedia saat auto reject | Auto reject terjadi pada pengajuan dengan ruangan_id=[1] | `ruangan_status` id 1 menjadi 'Tersedia' | `autoRejectExpiredApprovals()` → `syncRuanganStatus(...)` |
| **RG-02-EDG-01** | Edge Case | Sync dengan array kosong | `syncRuanganStatus([], 'Tersedia')` | Tidak ada perubahan, return void | `if (empty($ruanganIds)) return;` |
| **RG-02-EDG-02** | Edge Case | Sync ruangan yang sudah berstatus sama | Ruangan sudah 'Diajukan', sync 'Diajukan' lagi | Tidak ada error, query update tetap jalan | `RuanganModel::whereIn()->update()` — idempotent |

---

### UC-RG-03: Penetapan Kategori Ruangan

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **RG-03-POS-01** | Positive | Set kategori Jurusan saat create | POST /ruangan/ajax, ruangan_kategori: "Jurusan" | Kategori tersimpan 'Jurusan' | Field `ruangan_kategori` di fillable |
| **RG-03-POS-02** | Positive | Set kategori Umum saat edit | PUT /ruangan/1/update_ajax, ruangan_kategori: "Umum" | Kategori berubah 'Umum' | `RuanganService::update()` |
| **RG-03-VAL-01** | Validation | Kategori null saat create | ruangan_kategori: null atau tidak dikirim | Tergantung validasi — jika nullable, tersimpan null | Migration: kolom nullable atau default value |
| **RG-03-EDG-01** | Edge Case | Kategori menentukan tahap akhir approval | Pengajuan dengan ruangan kategori 'Umum' | Tahap akhir verifikator = 'Wakil Direktur II' | `PengajuanService::generateApprovalStages()` → `$ruanganKategori === 'Umum' ? 'Wakil Direktur II' : 'Ketua Jurusan'` |

---

## BAGIAN D — ORGANISASI

### UC-ORG-01: CRUD Organisasi Mahasiswa

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **ORG-01-POS-01** | Positive | Tambah organisasi sukses | POST /organisasi/ajax, {organisasi_kode: "HMTI", organisasi_nama: "Himpunan Mahasiswa TI", organisasi_logo: null} | JSON sukses status 201 | `OrganisasiService::create()` |
| **ORG-01-POS-02** | Positive | Lihat detail organisasi | GET /organisasi/1/show_ajax (AJAX) | View `organisasi.show_ajax` | `OrganisasiService::find()` |
| **ORG-01-POS-03** | Positive | Edit organisasi sukses | PUT /organisasi/1/update_ajax, {organisasi_nama: "HMTI Update"} | JSON sukses | `OrganisasiService::update()` |
| **ORG-01-POS-04** | Positive | Hapus organisasi sukses | DELETE /organisasi/1/delete_ajax | JSON sukses | `OrganisasiService::delete()` |
| **ORG-01-NEG-01** | Negative | Hapus organisasi yang masih memiliki verifikator | organisasi_id=1, masih ada verifikator terkait | Error FK constraint atau ditolak | Tergantung implementasi |
| **ORG-01-PER-01** | Permission | Akses CRUD organisasi sebagai Dosen | Login DSN, GET /organisasi | HTTP 403 | Middleware `authorize:ADM` |

---

### UC-ORG-02: Pemetaan Jabatan Approval ke Organisasi

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **ORG-02-POS-01** | Positive | Tambah jabatan Ketua Umum untuk organisasi | Insert m_jabatan_approval: user_id=5 (dosen/mhs existing), organisasi_id=1, posisi_approval="Ketua Umum", urutan_approval=1 | Data tersimpan, siap dipakai approval | Model `JabatanApprovalModel` |
| **ORG-02-POS-02** | Positive | Tambah jabatan lintas organisasi (Kajur) | Insert m_jabatan_approval: user_id=6, organisasi_id=null, posisi_approval="Ketua Jurusan", urutan_approval=3 | Data tersimpan tanpa organisasi | `organisasi_id` nullable |
| **ORG-02-POS-03** | Positive | Tambah jabatan DPK & PresBEM (paralel) | 2 baris: urutan_tahap=2, posisi_approval="DPK" dan "Presiden BEM" | Kedua posisi dengan urutan_tahap sama | `urutan_approval` tidak menentukan urutan_tahap |
| **ORG-02-POS-04** | Positive | Satu user memegang lebih dari satu jabatan | user_id=5 dipetakan sebagai Ketua Umum organisasi A, lalu juga Ketua Pelaksana kegiatan lain | Kedua baris tersimpan (relasi `hasMany`), antrian menggabungkan keduanya | `UserModel::jabatanApprovals()` hasMany |
| **ORG-02-NEG-01** | Negative | Jabatan dengan posisi yang sama untuk user yang sama (duplikat) | Insert duplikat user_id=5 + posisi_approval="Ketua Umum" | Error unique constraint (`user_id`+`posisi_approval`) | Migration `add_unique_index_to_m_jabatan_approval_table` |
| **ORG-02-EDG-01** | Edge Case | Jabatan tidak ditemukan untuk posisi tertentu saat generate approval | Belum ada jabatan "DPK" di database | Tahap dilewati (skip), pengajuan tidak macet | `PengajuanService::generateApprovalStages()` → `if (!$jabatan) continue;` |

---

## BAGIAN E — JABATAN APPROVAL (dahulu "VERIFIKATOR")

> **Catatan redesain:** Level akun `VRF` dan tabel `m_verifikator` sudah dihapus. Pemegang jabatan approval login sebagai dosen/mahasiswa biasa (level DSN/MHS); jabatan hanya atribut tambahan di `m_jabatan_approval`. Route `/pengajuan/approval/*` sekarang digerbangi `authorize:ADM,DSN,TDK,MHS` (bukan `authorize:VRF`) — otorisasi riil ditegakkan di level ownership check dalam `processApproval()`, bukan di middleware.

### UC-JAB-00: Tahap Approval Ketua Pelaksana (Baru)

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **APV0-POS-01** | Positive | Ketua Pelaksana muncul di antrian segera setelah pengajuan dibuat | Pengajuan baru dibuat dengan `ketua_pelaksana_user_id`=X | Antrian user X berisi tahap 0 "Ketua Pelaksana", Ketua Umum masih kosong (belum aktif) | `generateApprovalStages()` → `findOrCreateJabatanKetuaPelaksana()` |
| **APV0-POS-02** | Positive | Approve tahap 0 mengaktifkan tahap 1 (Ketua Umum) | PUT proses_ajax tahap 0, status_approval=Disetujui | Tahap 1 (Ketua Umum) `batas_waktu` terisi, muncul di antrian Ketua Umum | Mekanisme "aktifkan tahap berikutnya" generic, tidak ada logic khusus tahap 0 |
| **APV0-POS-03** | Positive | User yang sama dipilih sebagai Ketua Pelaksana di 2 pengajuan berbeda | Buat pengajuan A dan B, keduanya `ketua_pelaksana_user_id`=X | Hanya SATU baris `m_jabatan_approval` untuk X+"Ketua Pelaksana" (reused), tidak dobel | `firstOrCreate` + unique index `(user_id, posisi_approval)` |
| **APV0-NEG-01** | Negative | Tolak tahap 0 | PUT proses_ajax tahap 0, status_approval=Ditolak, alasan diisi | Pengajuan langsung 'Ditolak', ruangan kembali 'Tersedia' | Sama seperti alur reject tahap lain |
| **APV0-NEG-02** | Negative | `ketua_pelaksana_user_id` kosong saat create pengajuan | POST /pengajuan/ajax tanpa ketua_pelaksana_user_id | Validasi gagal 422 | Rule `required|exists:m_user,user_id` |
| **APV0-NEG-03** | Negative | `ketua_pelaksana_user_id` menunjuk user yang tidak ada | ketua_pelaksana_user_id: 99999 | Validasi gagal 422 | Rule `exists:m_user,user_id` |
| **APV0-PER-01** | Permission | User lain (bukan Ketua Pelaksana terpilih) mencoba proses tahap 0 | Login user Y ≠ X, PUT proses_ajax tahap 0 milik X | `{status:false, http_status:403}` | Ownership check `jabatanApproval.user_id == Auth::id()` |
| **APV0-EDG-01** | Edge Case | Race condition dua submission memilih Ketua Pelaksana yang sama nyaris bersamaan | 2 request paralel create pengajuan dgn `ketua_pelaksana_user_id` sama, belum ada baris jabatan | Tidak terjadi duplicate-row error tak tertangani; salah satu insert menang, yang lain fallback fetch | `firstOrCreate` dibungkus try/catch `QueryException` → retry fetch |

---

### UC-JAB-01: Melihat Antrian Approval

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **APV1-POS-01** | Positive | Pemegang jabatan melihat antrian dengan data | GET /pengajuan/approval/antrian_ajax (login sebagai DSN/MHS pemegang jabatan) | View `pengajuan.antrian` dengan list approval menunggu | `PengajuanService::antrianFor($user)` |
| **APV1-POS-02** | Positive | Antrian kosong (tidak ada yang menunggu) | Login pemegang jabatan, GET antrian_ajax | View dengan collection kosong (tampilkan "Tidak ada antrian"), HTTP 200 | `PengajuanApprovalModel::where(...)->get()` return empty |
| **APV1-POS-03** | Positive | User dengan lebih dari satu jabatan melihat gabungan antrian keduanya | User X = Ketua Umum organisasi A + Ketua Pelaksana kegiatan B | Antrian menampilkan tahap dari kedua jabatan | `pluck('jabatan_id')` dari semua baris `m_jabatan_approval` milik user |
| **APV1-PER-01** | Permission | Akses antrian approval sebagai Admin/Mahasiswa yang tidak punya jabatan | Login ADM/MHS tanpa jabatan, GET antrian_ajax | HTTP 200, antrian kosong (BUKAN 403 — route accessible ke semua level ADM,DSN,TDK,MHS) | Middleware `authorize:ADM,DSN,TDK,MHS`; fungsional dibatasi di service |
| **APV1-EDG-01** | Edge Case | User tidak punya data jabatan sama sekali | User belum pernah dipetakan ke `m_jabatan_approval` | Antrian kosong (collection kosong) | `JabatanApprovalModel::where('user_id')` kosong → `return collect()` |

---

### UC-JAB-02: Proses Persetujuan / Penolakan Tahap Approval

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **APV2-POS-01** | Positive | Setujui tahap approval sukses | PUT /pengajuan/approval/1/proses_ajax, {status_approval: "Disetujui"} | `{status: true, message: "Tahap approval disetujui. Tahap berikutnya telah diaktifkan."}` | `processApproval()` → update status, cek stageFullyApproved, aktivasi next |
| **APV2-POS-02** | Positive | Setujui tahap paralel (tunggu pasangan) | PUT dengan status_approval=Disetujui, pasangan masih 'Menunggu' | `{status: true, message: "Menunggu approval paralel lainnya."}` | `$stageFullyApproved` = false |
| **APV2-POS-03** | Positive | Setujui tahap terakhir (langsung selesai) | PUT, ini tahap terakhir, semua approval sudah Disetujui | `{status: true, message: "Seluruh tahap approval selesai. Pengajuan diterima dan jadwal dibuat."}` | Tidak ada nextStage → pengajuan Diterima |
| **APV2-POS-04** | Positive | Tolak pengajuan dengan alasan | PUT, {status_approval: "Ditolak", alasan_penolakan: "Dokumen tidak lengkap"} | `{status: true, message: "Pengajuan ditolak pada tahap ini."}` | Update approval + pengajuan jadi Ditolak |
| **APV2-NEG-01** | Negative | Tolak tanpa alasan | PUT, {status_approval: "Ditolak", alasan_penolakan: ""} | `{status: false, message: "Validasi Gagal", msgField: {alasan_penolakan: ["Alasan penolakan wajib diisi."]}}` | `if (empty($alasanPenolakan))` return error |
| **APV2-NEG-02** | Negative | Proses approval yang sudah diproses sebelumnya | PUT approval_id dengan status sudah 'Disetujui' | `{status: false, message: "Tahap approval ini sudah diproses sebelumnya.", http_status: 422}` | `if ($approval->status_approval !== 'Menunggu')` return error |
| **APV2-PER-01** | Permission | Pemegang jabatan A memproses tahap milik pemegang jabatan B | Login user A, proses approval_id milik jabatan B | `{status: false, message: "Anda tidak berwenang memproses tahap approval ini.", http_status: 403}` | `if ($approval->jabatanApproval->user_id !== $loggedInUserId)` return error |
| **APV2-PER-02** | Permission | User tanpa jabatan approval terkait memproses approval | Login ADM/MHS tanpa jabatan, PUT proses_ajax | HTTP 403 (ownership check gagal, bukan level check) | Ownership check di `processApproval()` |
| **APV2-VAL-01** | Validation | Status approval tidak valid | PUT, {status_approval: "Mungkin"} | `{status: false, message: "Status approval tidak valid.", http_status: 422}` | `!in_array($statusApproval, ['Disetujui', 'Ditolak'])` |
| **APV2-EXC-01** | Exception | Approval ID tidak ditemukan | PUT /pengajuan/approval/999/proses_ajax | `{status: false, message: "Data tahap approval tidak ditemukan!", http_status: 404}` | `PengajuanApprovalModel::find($approvalId)` null |

---

### UC-JAB-03: Approval Paralel DPK & Presiden BEM

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **APV3-POS-01** | Positive | DPK setuju, lalu PresBEM setuju | 1. DPK setujui, 2. PresBEM setujui | Tahap 2 selesai, tahap 3 aktif | `$stageFullyApproved` cek semua approval di urutan_tahap=2 |
| **APV3-POS-02** | Positive | PresBEM setuju, lalu DPK setuju | 1. PresBEM setujui, 2. DPK setujui | Sama (urutan terbalik) | Kedua approval independen |
| **APV3-NEG-01** | Negative | DPK tolak, PresBEM setuju | 1. DPK tolak → pengajuan Ditolak, 2. PresBEM tidak bisa proses | Pengajuan Ditolak di tahap DPK, PresBEM mendapat error "sudah diproses" | `if ($statusApproval === 'Ditolak')` → langsung akhiri |
| **APV3-EDG-01** | Edge Case | Kedua pemegang jabatan setuju di saat bersamaan | Request simultan DPK & PresBEM | Kedua status 'Disetujui', tidak konflik | Database transaction per request |
| **APV3-EDG-02** | Edge Case | Salah satu posisi jabatan tidak ada yang ditunjuk | Tidak ada DPK di m_jabatan_approval | Tahap DPK di-skip saat generate, hanya PresBEM yang perlu setuju | `if (!$jabatan) continue;` |

---

## BAGIAN F — PENGAJUAN

### UC-PGN-01: Pengajuan Peminjaman Ruangan

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **PGN-01-POS-01** | Positive | Pengajuan sukses (semua field valid) | POST /pengajuan/ajax, {pengajuan_nama: "Rapat HMTI", pengajuan_tgl: "2026-01-15", pengajuan_jam_mulai: "09:00", pengajuan_jam_selesai: "12:00", pengajuan_jumPes: 20, pengajuan_keterangan: "Meeting rutin", organisasi_id: 1, ketua_pelaksana_user_id: 5, ruangan_ids: [1]} | `{status: true, message: "Pengajuan peminjaman berhasil diajukan!"}` | `PengajuanService::create()` → create pengajuan + attach ruangan + sync status + generate approval (diawali tahap 0 Ketua Pelaksana) |
| **PGN-01-POS-06** | Positive | Widget availability form menampilkan status per ruangan saat tanggal diisi | Form create_ajax, isi `pengajuan_tgl` | Widget `#ruangan-availability` menampilkan status (Tersedia/Pending/Tidak Tersedia) tiap ruangan | JS `change` handler → `GET /ruangan/availability_ajax` |
| **PGN-01-NEG-06** | Negative | `ketua_pelaksana_user_id` kosong | ketua_pelaksana_user_id: null | Validasi gagal 422 | Rule `required|exists:m_user,user_id` |
| **PGN-01-POS-02** | Positive | Pengajuan tanpa keterangan (opsional) | pengajuan_keterangan: null | Sukses, keterangan null | `$data['pengajuan_keterangan'] ?? null` |
| **PGN-01-POS-03** | Positive | Pengajuan dengan multiple ruangan | ruangan_ids: [1, 2, 3] | Sukses, semua ruangan terattach | `$pengajuan->ruangans()->attach($data['ruangan_ids'])` |
| **PGN-01-POS-04** | Positive | Admin melihat semua pengajuan | GET /pengajuan (ADM) | Semua pengajuan dari semua user | `visibleFor($user)` → role ADM: `$query->get()` |
| **PGN-01-POS-05** | Positive | Dosen hanya melihat pengajuannya sendiri | GET /pengajuan (DSN) | Hanya pengajuan miliknya | `visibleFor($user)` → non-ADM: `where('user_id', $user->user_id)` |
| **PGN-01-NEG-01** | Negative | Jam selesai sebelum jam mulai | jam_mulai: "13:00", jam_selesai: "10:00" | `{status: false, message: "Validasi Gagal", msgField: {pengajuan_jam_selesai: ["..."]}}` | Rule `after:pengajuan_jam_mulai` |
| **PGN-01-NEG-02** | Negative | Tanggal tidak valid | pengajuan_tgl: "bukan-tanggal" | Validasi gagal | Rule `date` |
| **PGN-01-NEG-03** | Negative | Jumlah peserta 0 | pengajuan_jumPes: 0 | Validasi gagal | Rule `min:1` |
| **PGN-01-NEG-04** | Negative | Organisasi tidak ada | organisasi_id: 999 | Validasi gagal | Rule `exists:m_organisasi,organisasi_id` |
| **PGN-01-NEG-05** | Negative | Ruangan tidak ada | ruangan_ids: [999] | Validasi gagal | Rule `exists:m_ruangan,ruangan_id` |
| **PGN-01-VAL-01** | Validation | Field required kosong | pengajuan_nama: "" | Validasi gagal | Rule `required` untuk semua field utama |
| **PGN-01-VAL-02** | Validation | Format jam salah | pengajuan_jam_mulai: "25:00" | Validasi gagal | Rule `date_format:H:i` |
| **PGN-01-PER-01** | Permission | Pengajuan tanpa login | Tidak terautentikasi, GET /pengajuan | Redirect ke login | Middleware `auth` |
| **PGN-01-EDG-01** | Edge Case | Generate approval stages saat jabatan untuk posisi tertentu belum ada | Jabatan "Ketua Umum" untuk organisasi_id tersebut belum ditunjuk | Tahap di-skip, approval tetap jalan | `if (!$jabatan) continue;` |
| **PGN-01-EDG-02** | Edge Case | Pengajuan berstatus Diajukan, status operasional null | Pengajuan baru, belum diproses | `status_operasional = null` | `operationalStatusFor()` return null jika status bukan 'Diterima' |

---

### UC-PGN-02: Verifikasi Pengajuan oleh Admin

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **PGN-02-POS-01** | Positive | Admin terima pengajuan | PUT /pengajuan/1/terima_ajax | `{status: true, message: "Pengajuan diterima dan jadwal berhasil dibuat!"}` | `PengajuanService::accept()` → update status, generate nomor surat, create jadwal |
| **PGN-02-POS-02** | Positive | Admin tolak pengajuan dengan catatan | PUT /pengajuan/1/tolak_ajax, {catatan_verifikator: "Dokumen tidak lengkap"} | `{status: true, message: "Pengajuan berhasil ditolak."}` | `PengajuanService::reject()` → update status Ditolak + sync ruangan |
| **PGN-02-POS-03** | Positive | Admin tolak tanpa catatan | PUT /pengajuan/1/tolak_ajax, {catatan_verifikator: null} | Sukses, catatan null | `$data['catatan_verifikator'] ?? null` — nullable field |
| **PGN-02-NEG-01** | Negative | Terima pengajuan yang sudah diproses | PUT /pengajuan/1/terima_ajax (status sudah 'Diterima') | `{status: false, message: "Pengajuan ini sudah diproses sebelumnya.", http_status: 422}` | `guardProcessable()` → `$pengajuan->pengajuan_status === 'Diajukan'` |
| **PGN-02-NEG-02** | Negative | Terima pengajuan tidak ada | PUT /pengajuan/999/terima_ajax | `{status: false, message: "Data pengajuan tidak ditemukan!", http_status: 404}` | `PengajuanModel::find($id)` null |
| **PGN-02-PER-01** | Permission | Dosen melakukan verifikasi | Login DSN, PUT /pengajuan/1/terima_ajax | HTTP 403 | Middleware `authorize:ADM` |

---

### UC-PGN-03: Timeline Approval

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **PGN-03-POS-01** | Positive | Lihat timeline dengan 4 tahap approval | GET /pengajuan/1/timeline_ajax | View `pengajuan.timeline_ajax` dengan 4 baris tahap | `PengajuanService::timelineFor()` → orderBy urutan_tahap |
| **PGN-03-POS-02** | Positive | Timeline dengan status campuran (Setuju & Menunggu) | Pengajuan dengan 2 tahap sudah diproses, 2 menunggu | Timeline menampilkan semua status sesuai kondisi | Data mentah dari query |
| **PGN-03-PER-01** | Permission | Non-AJAX request | GET /pengajuan/1/timeline_ajax (bukan AJAX) | Redirect ke `/` | `$request->ajax() ? view(...) : redirect('/')` |

---

### UC-PGN-04: Cetak Surat Peminjaman (PDF)

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **PGN-04-POS-01** | Positive | Cetak surat untuk pengajuan Diterima | GET /pengajuan/1/cetak_surat_ajax (status Diterima) | File PDF terdownload: "Surat_Peminjaman_1.pdf" | `DomPDF::loadView('pdf.surat_peminjaman', ...)` → `$pdf->download()` |
| **PGN-04-POS-02** | Positive | Nomor surat tergenerate otomatis | Pengajuan Diterima pertama di tahun 2026 | Nomor surat: "1/SPR-JTI/I/2026" | `generateNomorSurat()` → count + 1 |
| **PGN-04-NEG-01** | Negative | Cetak surat untuk pengajuan belum Diterima | GET /pengajuan/1/cetak_surat_ajax (status Diajukan) | HTTP 422 | `if ($pengajuan->pengajuan_status !== 'Diterima')` abort |
| **PGN-04-NEG-02** | Negative | Cetak surat untuk pengajuan tidak ada | GET /pengajuan/999/cetak_surat_ajax | HTTP 404 | `PengajuanModel::find($id)` null |
| **PGN-04-PER-01** | Permission | Cetak surat tanpa login | Request tanpa autentikasi | Redirect login | Middleware `auth` |

---

### UC-PGN-05: Auto Reject Tahap Approval

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **PGN-05-POS-01** | Positive | Auto reject 1 tahap expired | 1 approval expired > 2 hari | Tahap jadi 'Auto Reject', pengajuan 'Ditolak', ruangan 'Tersedia', return count=1 | `autoRejectExpiredApprovals()` |
| **PGN-05-POS-02** | Positive | Auto reject multiple tahap expired | 3 approval expired | 3 tahap diproses, count=3 | Loop foreach, update masing-masing |
| **PGN-05-POS-03** | Positive | Tidak ada expired (normal) | Semua approval masih dalam batas waktu | Count=0, tidak ada perubahan | `$expired->count()` = 0 |
| **PGN-05-NEG-01** | Negative | Pengajuan sudah diproses sebelum auto reject | Approval expired, tapi pengajuan sudah 'Ditolak' oleh Admin | Skip (idempotent), count tidak bertambah | `if ($pengajuan->pengajuan_status !== 'Diajukan') continue;` |
| **PGN-05-EDG-01** | Edge Case | Run auto reject 2x berturut-turut | Run command, lalu run lagi | Run kedua: count=0 (idempotent) | Status sudah 'Auto Reject' tidak akan terjaring lagi (where status='Menunggu') |
| **PGN-05-EDG-02** | Edge Case | batas_waktu persis sama dengan now | batas_waktu = now(), dieksekusi | Tidak diproses (belum < now) | Condition: `where('batas_waktu', '<', now())` |

---

## BAGIAN G — JADWAL

### UC-JDW-01: CRUD Jadwal

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **JDW-01-POS-01** | Positive | Lihat daftar jadwal sebagai Admin | GET /jadwal (ADM) | View `jadwal.index` | `JadwalService::indexData()` |
| **JDW-01-POS-02** | Positive | Lihat daftar jadwal sebagai Mahasiswa | GET /jadwal (MHS) | View `jadwal.index` (read-only) | Middleware `authorize:ADM,DSN,TDK,MHS` |
| **JDW-01-POS-03** | Positive | Tambah jadwal sukses | POST /jadwal/ajax, {user_id: 1, jadwal_nama: "Seminar", jadwal_tgl: "2026-01-15", jadwal_jam_mulai: "08:00", jadwal_jam_selesai: "16:00", jadwal_jumPes: 100} | JSON sukses | `JadwalService::create()` |
| **JDW-01-POS-04** | Positive | Show jadwal detail | GET /jadwal/1/show_ajax (AJAX) | View `jadwal.show_ajax` | `JadwalService::findForShow()` |
| **JDW-01-POS-05** | Positive | Hapus jadwal sukses | DELETE /jadwal/1/delete_ajax | JSON sukses | `JadwalService::delete()` |
| **JDW-01-NEG-01** | Negative | Show jadwal tidak ditemukan | GET /jadwal/999/show_ajax | JSON `{status: false, message: "Data jadwal tidak ditemukan!"}` 404 | `JadwalService::findForShow()` null |
| **JDW-01-NEG-02** | Negative | Edit jadwal tidak ditemukan | GET /jadwal/999/edit_ajax | JSON 404 | `JadwalService::formData((int) $id)` jadwal null |
| **JDW-01-PER-01** | Permission | Tambah jadwal sebagai Dosen | Login DSN, POST /jadwal/ajax | HTTP 403 | Middleware `authorize:ADM` |
| **JDW-01-PER-02** | Permission | Hapus jadwal sebagai Mahasiswa | Login MHS, DELETE /jadwal/1/delete_ajax | HTTP 403 | Middleware `authorize:ADM` |

---

### UC-JDW-02: Update Status Jadwal

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **JDW-02-POS-01** | Positive | Update status ke 'Berlangsung' | PUT /jadwal/1/update_status_ajax, {jadwal_status: "Berlangsung"} | JSON sukses | `JadwalService::updateStatus()` |
| **JDW-02-POS-02** | Positive | Update status ke 'Selesai' | PUT /jadwal/1/update_status_ajax, {jadwal_status: "Selesai"} | JSON sukses | `JadwalService::updateStatus()` |
| **JDW-02-NEG-01** | Negative | Update status tidak valid | PUT /jadwal/1/update_status_ajax, {jadwal_status: "InvalidStatus"} | JSON error | Validasi status di `JadwalService::updateStatus()` |
| **JDW-02-PER-01** | Permission | Update status sebagai Dosen | Login DSN, PUT /jadwal/1/update_status_ajax | HTTP 403 | Middleware `authorize:ADM` |

---

### UC-JDW-03: Filter Jadwal per Semester

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **JDW-03-POS-01** | Positive | Filter jadwal semester ganjil | POST /jadwal/list, filter semester = "2025/2026 Ganjil" | Data jadwal sesuai semester | Filter logic di JadwalService |
| **JDW-03-EDG-01** | Edge Case | Filter menghasilkan data kosong | POST /jadwal/list, filter semester tanpa data | DataTables dengan data kosong | Tergantung implementasi filter |

---

### UC-JDW-04: Get Kelas by Prodi

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **JDW-04-POS-01** | Positive | Get kelas untuk prodi valid | GET /jadwal/get_kelas_by_prodi/1 | JSON array kelas untuk prodi_id=1 | `JadwalService::kelasByProdi()` |
| **JDW-04-EDG-01** | Edge Case | Get kelas untuk prodi tanpa kelas | GET /jadwal/get_kelas_by_prodi/999 | JSON array kosong [] | Query return empty |

---

## BAGIAN H — DASHBOARD

### UC-DSH-01: Dashboard Role-Aware

| No | Tipe Skenario | Nama Skenario | Input | Expected Output | Implementasi Acuan |
|---|---|---|---|---|---|
| **DSH-01-POS-01** | Positive | Dashboard Admin dengan statistik lengkap | GET /dashboard (ADM) | View `admin.welcome` dengan: stat cards, bar chart, pie chart, line chart, early warning | `DashboardService::getWelcomeDashboardData()` → role ADM |
| **DSH-01-POS-02** | Positive | Dashboard Dosen dengan pengajuan aktif | GET /dashboard (DSN) | View `dosen.welcome` dengan: stat cards, pengajuan aktif miliknya, ruangan hari ini | Role DSN → data tambahan `pengajuanAktif`, `ruanganHariIni` |
| **DSH-01-POS-03** | Positive | Dashboard Mahasiswa dengan pengajuan aktif | GET /dashboard (MHS) | View `mahasiswa.welcome` dengan data yang sama seperti Dosen | `$role === 'MHS'` → sama dengan DSN |
| **DSH-01-POS-04** | Positive | Dashboard pemegang jabatan approval dengan antrian | GET /dashboard (DSN/MHS yang punya `m_jabatan_approval`) | Widget `_antrian_approval_widget` (include di `dosen.welcome`/`mahasiswa.welcome`) menampilkan antrian approval miliknya | `JabatanApprovalModel::where('user_id',...)->exists()` → antrianApproval disertakan |
| **DSH-01-POS-05** | Positive | Top 5 ruangan terfavorit (bar chart) | GET /dashboard | Array topRuangan dengan 5 data, diurutkan desc | `RuanganModel::withCount(['jadwal' => ...])->limit(5)->orderByDesc('jadwal_count')` |
| **DSH-01-POS-06** | Positive | Tren peminjaman 6 bulan (line chart) | GET /dashboard | Array trenPeminjaman 6 bulan terakhir | `DB::table('t_jadwal')->where('jadwal_tgl', '>=', $sixMonthsAgo)->pluck('jadwal_tgl')` |
| **DSH-01-POS-07** | Positive | Distribusi peminjam per role (pie chart) | GET /dashboard | Array distribusiPeminjam {admin: N, dosen: N, tendik: N, mahasiswa: N} | `DB::table('t_jadwal')->join(...)->groupBy('level_kode')` |
| **DSH-01-POS-08** | Positive | Early warning admin (pengajuan mendekati auto reject) | GET /dashboard (ADM) | Data `earlyWarning` dengan approval batas waktu < 12 jam | `getEarlyWarningPengajuan()` → `whereBetween('batas_waktu', [now(), now()->addHours(12)])` |
| **DSH-01-POS-09** | Positive | Dashboard Admin dengan cek total user | GET /dashboard | totalUser, totalMahasiswa, totalDosen, totalTendik ditampilkan | `DashboardService::getDashboardStats()` |
| **DSH-01-NEG-01** | Negative | Dashboard tanpa data jadwal (database kosong) | GET /dashboard saat database baru | Statistik 0 untuk semua metrik, chart kosong | Query return 0, collection kosong |
| **DSH-01-PER-01** | Permission | Akses dashboard tanpa login | GET /dashboard tanpa autentikasi | Redirect ke halaman login | Middleware `auth` |
| **DSH-01-EDG-01** | Edge Case | Dashboard untuk role yang tidak punya view spesifik (level_id tidak dikenal) | User dengan level_id=6 (tidak ada di match) | Fallback ke view `welcome` | `resolveWelcomeViewName()` → `match ((int) $user->level_id)` → default `'welcome'` |
| **DSH-01-EDG-02** | Edge Case | Data ruanganHariIni untuk role peminjam (DSN/TDK/MHS) | GET /dashboard sebagai DSN | `RuanganModel::with(['jadwal' => fn($q) => $q->whereDate('jadwal_tgl', today())])->get()` | `getRuanganHariIni()` |
| **DSH-01-EDG-03** | Edge Case | Early warning kosong (tidak ada yang mendekati) | GET /dashboard, semua approval masih jauh dari batas waktu | Data early warning kosong | Query `whereBetween` return empty |

---

## LAMPIRAN: RINGKASAN JUMLAH SKENARIO

| Kategori | POS | NEG | VAL | BOU | EXC | PER | EDG | TOTAL |
|---|---|---|---|---|---|---|---|---|
| AUTH | 4 | 4 | 2 | 2 | 1 | 1 | 2 | **16** |
| MASTER DATA | 12 | 12 | 4 | 4 | 1 | 5 | 4 | **42** |
| RUANGAN (termasuk UC-RG-04 Baru) | 14 | 3 | 2 | 0 | 0 | 3 | 4 | **26** |
| ORGANISASI | 6 | 1 | 0 | 0 | 0 | 1 | 1 | **9** |
| JABATAN APPROVAL (termasuk UC-JAB-00 Ketua Pelaksana, Baru) | 12 | 6 | 1 | 0 | 1 | 4 | 4 | **28** |
| PENGAJUAN | 9 | 8 | 2 | 0 | 0 | 2 | 2 | **23** |
| JADWAL | 5 | 2 | 0 | 0 | 0 | 2 | 1 | **10** |
| DASHBOARD | 9 | 1 | 0 | 0 | 0 | 1 | 3 | **14** |
| **TOTAL** | **71** | **37** | **11** | **6** | **3** | **19** | **21** | **168** |

**Total seluruh skenario: 168+**

*Catatan: Skenario untuk UC-MD-03 s/d UC-MD-08 hanya mencakup variasi unik. Skenario repetitif (seperti CRUD pattern yang identik) tidak disebutkan ulang untuk menghindari redundansi.*

> **Update terakhir:** Kategori "VERIFIKATOR" diganti menjadi "JABATAN APPROVAL" mengikuti redesain `m_verifikator` → `m_jabatan_approval` (level akun VRF dihapus). Ditambahkan skenario baru untuk UC-JAB-00 (tahap Ketua Pelaksana) dan UC-RG-04 (status ruangan per-tanggal), sesuai spec file Playwright baru: `approval-stage0/1/2/3/parallel.spec.js` dan `ruangan-availability.spec.js`.

