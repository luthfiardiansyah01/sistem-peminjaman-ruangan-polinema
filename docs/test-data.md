# Data Testing — SPR JTI (Sistem Peminjaman Ruangan JTI)

> **Proyek:** Sistem Peminjaman Ruangan: PLP JTI  
> **Role:** QA Engineer  
> **Tujuan:** Menyediakan data testing untuk setiap modul berdasarkan source code  
> **Kelompok:** Valid, Invalid, Boundary, Duplicate, Empty, Null  

> **Update terakhir:** Level akun VRF terpisah dihapus (login sebagai VRF sudah tidak berlaku — pemegang jabatan approval login sebagai dosen/mahasiswa biasa). `m_verifikator` diganti `m_jabatan_approval`. Ditambahkan data test untuk tahap Ketua Pelaksana dan status ruangan per-tanggal.

---

## 1. MODUL AUTHENTICATION

### 1.1 Login

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Login sukses Admin | `username: "admin01"`, `password: "password123"` | `{status: true, message: "Selamat datang, [admin_nama]."}` |
| **Valid** | Login sukses Dosen | `username: "dosen01"`, `password: "pass123"` | `{status: true, message: "Selamat datang, [dosen_nama]."}` |
| **Valid** | Login sukses Tendik | `username: "tendik01"`, `password: "pass123"` | `{status: true, message: "Selamat datang, [tendik_nama]."}` |
| **Valid** | Login sukses Mahasiswa | `username: "mhs01"`, `password: "pass123"` | `{status: true, message: "Selamat datang, [mahasiswa_nama]."}` |
| **Valid** | Login sukses Dosen pemegang jabatan approval | `username: "2241760003"`, `password: "123456"` | Login sukses sebagai DSN biasa; jabatan approval (Ketua Umum) hanya atribut tambahan, bukan level akun berbeda |
| **Invalid** | Username tidak terdaftar | `username: "tidakada"`, `password: "password123"` | `{status: false, message: "Login gagal, username atau password salah."}` |
| **Invalid** | Password salah | `username: "admin01"`, `password: "salahpassword"` | `{status: false, message: "Login gagal, username atau password salah."}` |
| **Invalid** | Username & password salah | `username: "fiktif"`, `password: "fiktif123"` | `{status: false, message: "Login gagal, username atau password salah."}` |
| **Boundary** | Username 3 karakter (min) | `username: "abc"`, `password: "password123"` | Sukses jika user abc ada di database |
| **Boundary** | Username 255 karakter (max) | `username: "a" x 255`, `password: "password123"` | Tergantung database, jika tidak ada → gagal |
| **Duplicate** | N/A (login read-only, tidak insert) | — | — |
| **Empty** | Username kosong | `username: ""`, `password: "password123"` | `{status: false, message: "Login gagal, username atau password salah."}` |
| **Empty** | Password kosong | `username: "admin01"`, `password: ""` | `{status: false, message: "Login gagal, username atau password salah."}` |
| **Empty** | Keduanya kosong | `username: ""`, `password: ""` | `{status: false, message: "Login gagal, username atau password salah."}` |
| **Null** | Username null | `username: null`, `password: "password123"` | `{status: false, message: "Login gagal, username atau password salah."}` |
| **Null** | Password null | `username: "admin01"`, `password: null` | `{status: false, message: "Login gagal, username atau password salah."}` |

### 1.2 Profile Update

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Update username valid | `username: "newname"` | `{status: true, msg: "Profil berhasil diperbarui!"}` |
| **Valid** | Update password valid | `password: "newpass123"` | `{status: true, msg: "Profil berhasil diperbarui!"}` |
| **Valid** | Update no_hp valid | `no_hp: "08123456789"` | `{status: true, msg: "Profil berhasil diperbarui!"}` |
| **Valid** | Update semua field | `{username: "newname", password: "newpass123", no_hp: "08123456789"}` | `{status: true, msg: "Profil berhasil diperbarui!"}` |
| **Invalid** | Username sudah dipakai user lain | `username: "existing_user"` | `{status: false, msg: "Validasi gagal", errors: {username: [...]}}` |
| **Invalid** | Username dengan karakter spesial | `username: "admin@user!"` | Validasi gagal (tergantung rule regex) |
| **Boundary** | Username 3 karakter | `username: "abc"` | Sukses (min:3) |
| **Boundary** | Username 2 karakter | `username: "ab"` | Validasi gagal (min:3) |
| **Boundary** | Password 5 karakter | `password: "abcde"` | Sukses (min:5) |
| **Boundary** | Password 4 karakter | `password: "abcd"` | Validasi gagal (min:5) |
| **Duplicate** | Username duplikat (user lain) | `username: "admin01"` (sudah dipakai) | `{status: false, msg: "Validasi gagal", errors: {username: [...]}}` |
| **Empty** | Username string kosong | `username: ""` | Tidak update username (gunakan existing) |
| **Empty** | Password string kosong | `password: ""` | Tidak update password |
| **Empty** | no_hp string kosong | `no_hp: ""` | Update no_hp ke null |
| **Null** | Username null | `username: null` | Tidak update username |
| **Null** | Password null | `password: null` | Tidak update password |

---

## 2. MODUL ADMIN CRUD

### 2.1 Create Admin

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Admin lengkap dengan semua field | `{username: "admin_baru", password: "pass123", admin_nama: "Andi Pratama", admin_nidn: "1234567890", prodi_id: 1, admin_noHp: "08123456789"}` | `{success: true, message: "Data admin berhasil ditambahkan", admin_id: N}` |
| **Valid** | Admin tanpa noHP | `{username: "admin_no_hp", password: "pass123", admin_nama: "Budi", admin_nidn: "0987654321", prodi_id: 1, admin_noHp: ""}` | Sukses, admin_noHp = null |
| **Invalid** | Password kurang dari 6 karakter | `password: "12345"` | Validasi gagal (min:6) |
| **Invalid** | admin_nidn non-numeric | `admin_nidn: "ABC1234567"` | Validasi gagal (numeric 10-16 digit) |
| **Invalid** | prodi_id tidak ada | `prodi_id: 999` | Error FK constraint atau validasi |
| **Boundary** | Username 3 karakter | `username: "abc"` | Sukses |
| **Boundary** | Username 2 karakter | `username: "ab"` | Validasi gagal |
| **Boundary** | Password 6 karakter | `password: "abcde1"` | Sukses |
| **Boundary** | Password 5 karakter | `password: "abcde"` | Validasi gagal |
| **Duplicate** | Username sudah terdaftar | `username: "admin01"` | `{success: false, message: "Username sudah terdaftar"}` |
| **Duplicate** | admin_nidn duplikat | `admin_nidn: "1234567890"` (sudah ada) | Tergantung validasi (mungkin sukses jika NIDN tidak unique constraint) |
| **Empty** | Username kosong | `username: ""` | Validasi gagal (required) |
| **Empty** | Password kosong | `password: ""` | Validasi gagal (required) |
| **Empty** | admin_nama kosong | `admin_nama: ""` | Validasi gagal (required) |
| **Empty** | admin_nidn kosong | `admin_nidn: ""` | Validasi gagal (required) |
| **Null** | Username null | `username: null` | Validasi gagal |
| **Null** | Password null | `password: null` | Validasi gagal |
| **Null** | prodi_id null | `prodi_id: null` | Validasi gagal (required via validatePersonPayload) |

### 2.2 Update Admin

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Update nama saja | `{admin_nama: "Andi Baru"}` | `{success: true, message: "User berhasil diperbarui"}` |
| **Valid** | Update noHP saja | `{admin_noHp: "0811111111"}` | Sukses |
| **Invalid** | admin_id tidak ada | ID = 999 | `{status: false, message: "Data admin tidak ditemukan!"}` 404 |
| **Duplicate** | Username duplikat (ke user lain) | `username: "admin02"` (milik admin lain) | `{success: false, message: "Username sudah terdaftar"}` |
| **Empty** | Semua field kosong | `{}` | Sukses (tidak ada perubahan) |
| **Null** | Field tidak dikirim | Request tanpa body | Tergantung validasi |

### 2.3 Delete Admin

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Hapus admin existing | ID = 1 (valid) | `{success: true, message: "User berhasil dihapus"}` |
| **Invalid** | admin_id tidak ada | ID = 999 | `{status: false, message: "Data admin tidak ditemukan!"}` 404 |
| **Null** | ID null | ID = null | 404 (route pattern [0-9]+ tidak match) |

---

## 3. MODUL DOSEN CRUD

### 3.1 Create Dosen

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Dosen lengkap | `{username: "dosen_baru", password: "pass123", dosen_nama: "Prof. Joko", dosen_nidn: "1234567890", prodi_id: 1, dosen_noHp: "08123456789"}` | `{success: true, message: "Data dosen berhasil ditambahkan", dosen_id: N}` |
| **Valid** | Dosen tanpa noHP | `{username: "dosen2", password: "pass123", dosen_nama: "Dr. Siti", dosen_nidn: "0987654321", prodi_id: 2, dosen_noHp: ""}` | Sukses, dosen_noHp = null |
| **Invalid** | prodi_id tidak ada | `prodi_id: 999` | Error |
| **Invalid** | NIDN non-numeric | `dosen_nidn: "NIDN12345"` | Validasi gagal |
| **Duplicate** | Username duplikat | `username: "existing_dosen"` | `{success: false, message: "Username sudah terdaftar"}` |
| **Empty** | dosen_nama kosong | `dosen_nama: ""` | Validasi gagal (required) |
| **Empty** | dosen_nidn kosong | `dosen_nidn: ""` | Validasi gagal (required) |
| **Null** | Username null | `username: null` | Validasi gagal |

### 3.2 Import Dosen Excel

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | File Excel valid | File dengan 5 baris: `A1:1234567890, B1:Andi, C1:Teknik Informatika, D1:08123456789` | `{success: true, message: "5 data dosen berhasil diimpor"}` |
| **Invalid** | File bukan Excel | File `data.txt` | `{success: false, message: "Validasi Gagal"}` |
| **Invalid** | File terlalu besar | File 3MB | Validasi gagal (max:2048KB) |
| **Invalid** | Nama prodi tidak dikenal | `C1:ProdiFiktif` | Rollback, error |
| **Duplicate** | NIDN sudah terdaftar di database | `A1:1234567890` (sudah ada) | Rollback, error "sudah terdaftar" |
| **Empty** | File hanya header | 1 baris (header) | `{success: false, message: "Tidak ada data yang diimport."}` |
| **Empty** | Baris data tidak lengkap | `A1:12345, B1:, C1:, D1:` | Rollback "Data tidak lengkap" |
| **Null** | File tidak diupload | No file | Validasi gagal (required) |

---

## 4. MODUL TENDIK CRUD

### 4.1 Create Tendik

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Tendik lengkap | `{username: "tendik_baru", password: "pass123", tendik_nama: "Siti Rahayu", tendik_nidn: "1234567890", tendik_noHp: "08123456789"}` | `{success: true, message: "Data tendik berhasil ditambahkan", tendik_id: N}` |
| **Valid** | Tendik tanpa noHP | `{username: "tendik2", password: "pass123", tendik_nama: "Bambang", tendik_nidn: "0987654321", tendik_noHp: ""}` | Sukses, tendik_noHp = null |
| **Invalid** | NIDN non-numeric | `tendik_nidn: "NIP12345"` | Validasi gagal |
| **Duplicate** | Username duplikat | `username: "existing_tendik"` | `{success: false, message: "Username sudah terdaftar"}` |
| **Empty** | tendik_nama kosong | `tendik_nama: ""` | Validasi gagal |
| **Null** | Password null | `password: null` | Validasi gagal |

---

## 5. MODUL MAHASISWA CRUD

### 5.1 Create Mahasiswa

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Mahasiswa lengkap | `{username: "mhs_baru", password: "pass123", mahasiswa_nama: "Budi Santoso", mahasiswa_nim: "12345678", prodi_id: 1, kelas_id: 1, mahasiswa_noHp: "08123456789"}` | `{success: true, message: "Data mahasiswa berhasil ditambahkan", mahasiswa_id: N}` |
| **Valid** | Mahasiswa tanpa noHP | `{username: "mhs2", password: "pass123", mahasiswa_nama: "Ani", mahasiswa_nim: "87654321", prodi_id: 2, kelas_id: 3, mahasiswa_noHp: ""}` | Sukses |
| **Invalid** | NIM non-numeric | `mahasiswa_nim: "NIM12345"` | Validasi gagal |
| **Invalid** | prodi_id tidak ada | `prodi_id: 999` | Error |
| **Invalid** | kelas_id tidak ada | `kelas_id: 999` | Error |
| **Duplicate** | NIM sudah terdaftar (username) | `username: "12345678"` (sudah ada) | `{success: false, message: "Username sudah terdaftar"}` |
| **Boundary** | NIM 8 digit | `mahasiswa_nim: "12345678"` | Sukses |
| **Boundary** | NIM 7 digit | `mahasiswa_nim: "1234567"` | Validasi gagal (min:8) |
| **Boundary** | NIM 12 digit | `mahasiswa_nim: "123456789012"` | Sukses |
| **Boundary** | NIM 13 digit | `mahasiswa_nim: "1234567890123"` | Validasi gagal (max:12) |
| **Empty** | mahasiswa_nama kosong | `mahasiswa_nama: ""` | Validasi gagal |
| **Null** | kelas_id null | `kelas_id: null` | Validasi gagal (required) |

---

## 6. MODUL PRODI CRUD

### 6.1 Create Prodi

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Prodi valid | `{prodi_kode: "TI", prodi_nama: "Teknik Informatika"}` | Sukses |
| **Boundary** | prodi_kode 2 karakter | `prodi_kode: "TI"` | Sukses |
| **Boundary** | prodi_kode 1 karakter | `prodi_kode: "T"` | Tergantung validasi |
| **Duplicate** | prodi_kode duplikat | `prodi_kode: "TI"` (sudah ada) | Error (unique constraint) |
| **Empty** | prodi_kode kosong | `prodi_kode: ""` | Validasi gagal |
| **Empty** | prodi_nama kosong | `prodi_nama: ""` | Validasi gagal |
| **Null** | Semua null | `{prodi_kode: null, prodi_nama: null}` | Validasi gagal |

---

## 7. MODUL KELAS CRUD

### 7.1 Create Kelas

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Kelas valid | `{prodi_id: 1, kelas_nama: "TI-1A"}` | Sukses |
| **Invalid** | prodi_id tidak ada | `prodi_id: 999` | Error |
| **Duplicate** | kelas_nama duplikat per prodi | `{prodi_id: 1, kelas_nama: "TI-1A"}` (sudah ada) | Error (unique constraint) |
| **Empty** | kelas_nama kosong | `kelas_nama: ""` | Validasi gagal |
| **Null** | prodi_id null | `prodi_id: null` | Validasi gagal |

---

## 8. MODUL RUANGAN CRUD

### 8.1 Create Ruangan

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Ruangan Jurusan lengkap | `{ruangan_kode: "R101", ruangan_nama: "Lab Komputer", ruangan_fasilitas: "AC, LCD, Komputer", ruangan_kuota: 30, ruangan_status: "Tersedia", ruangan_kategori: "Jurusan"}` | Sukses |
| **Valid** | Ruangan Umum | `{ruangan_kode: "A101", ruangan_nama: "Aula Besar", ruangan_fasilitas: "AC, Sound System", ruangan_kuota: 200, ruangan_status: "Tersedia", ruangan_kategori: "Umum"}` | Sukses |
| **Valid** | Ruangan Tanpa Fasilitas | `{ruangan_kode: "R102", ruangan_nama: "Ruang Kosong", ruangan_fasilitas: "", ruangan_kuota: 10, ruangan_status: "Tersedia", ruangan_kategori: "Jurusan"}` | Sukses, fasilitas = null |
| **Invalid** | ruangan_kuota negatif | `ruangan_kuota: -1` | Validasi gagal (min:1) |
| **Invalid** | ruangan_kuota 0 | `ruangan_kuota: 0` | Validasi gagal (min:1) |
| **Boundary** | ruangan_kuota = 1 | `ruangan_kuota: 1` | Sukses |
| **Boundary** | ruangan_kode 1 karakter | `ruangan_kode: "R"` | Tergantung validasi |
| **Duplicate** | ruangan_kode duplikat | `ruangan_kode: "R101"` (sudah ada) | Error |
| **Empty** | ruangan_kode kosong | `ruangan_kode: ""` | Validasi gagal |
| **Empty** | ruangan_nama kosong | `ruangan_nama: ""` | Validasi gagal |
| **Null** | ruangan_kategori null | `ruangan_kategori: null` | Tergantung nullable migration |
| **Null** | ruangan_status null | `ruangan_status: null` | Tergantung default value |

### 8.2 Update Ruangan

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Update kategori | `{ruangan_kategori: "Umum"}` | Sukses, kategori berubah |
| **Valid** | Update status | `{ruangan_status: "Tidak Tersedia"}` | Sukses |
| **Invalid** | ID tidak ada | ID = 999 | `{status: false, message: "Data ruangan tidak ditemukan!"}` 404 |

---

## 9. MODUL ORGANISASI CRUD

### 9.1 Create Organisasi

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Organisasi valid | `{organisasi_kode: "HMTI", organisasi_nama: "Himpunan Mahasiswa Teknik Informatika"}` | Sukses |
| **Valid** | Organisasi dengan logo | `{organisasi_kode: "BEM", organisasi_nama: "BEM JTI", organisasi_logo: "logo_bem.png"}` | Sukses |
| **Duplicate** | organisasi_kode duplikat | `organisasi_kode: "HMTI"` (sudah ada) | Error |
| **Empty** | organisasi_kode kosong | `organisasi_kode: ""` | Validasi gagal |
| **Empty** | organisasi_nama kosong | `organisasi_nama: ""` | Validasi gagal |
| **Null** | organisasi_kode null | `organisasi_kode: null` | Validasi gagal |

---

## 10. MODUL JABATAN APPROVAL (dahulu "VERIFIKATOR")

### 10.1 Tambah Jabatan Approval

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Ketua Umum (terikat organisasi) | `{user_id: 5, organisasi_id: 1, posisi_approval: "Ketua Umum", urutan_approval: 1}` | Data tersimpan di `m_jabatan_approval` |
| **Valid** | DPK (lintas organisasi) | `{user_id: 6, organisasi_id: null, posisi_approval: "DPK", urutan_approval: 2}` | Data tersimpan |
| **Valid** | Presiden BEM (lintas, paralel) | `{user_id: 7, organisasi_id: null, posisi_approval: "Presiden BEM", urutan_approval: 2}` | Data tersimpan |
| **Valid** | Ketua Jurusan (lintas) | `{user_id: 8, organisasi_id: null, posisi_approval: "Ketua Jurusan", urutan_approval: 3}` | Data tersimpan |
| **Valid** | Wakil Direktur II (lintas) | `{user_id: 9, organisasi_id: null, posisi_approval: "Wakil Direktur II", urutan_approval: 3}` | Data tersimpan |
| **Valid** | Ketua Pelaksana (dibuat otomatis, bukan diinput manual admin) | `firstOrCreate(['user_id' => X, 'posisi_approval' => 'Ketua Pelaksana'], ['organisasi_id' => null, 'urutan_approval' => 0])` dipanggil saat pengajuan dibuat | Baris baru dibuat, atau reuse jika X sudah pernah jadi Ketua Pelaksana |
| **Invalid** | user_id tidak ada | `user_id: 999` | Error FK |
| **Invalid** | organisasi_id tidak ada (untuk yang required) | `organisasi_id: 999` | Error FK |
| **Duplicate** | Duplikat posisi untuk user yang sama | `{user_id: 5, posisi_approval: "Ketua Umum"}` (sudah ada) | Error unique constraint `(user_id, posisi_approval)` |
| **Null** | posisi_approval null | `posisi_approval: null` | Validasi gagal |
| **Null** | urutan_approval null | `urutan_approval: null` | Validasi gagal |

---

## 11. MODUL PENGAJUAN

### 11.1 Create Pengajuan

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Pengajuan 1 ruangan (termasuk Ketua Pelaksana) | `{pengajuan_nama: "Rapat HMTI", pengajuan_tgl: "2026-01-15", pengajuan_jam_mulai: "09:00", pengajuan_jam_selesai: "12:00", pengajuan_jumPes: 20, pengajuan_keterangan: "Meeting rutin", organisasi_id: 1, ketua_pelaksana_user_id: 2, ruangan_ids: [1]}` | `{status: true, message: "Pengajuan peminjaman berhasil diajukan!"}`, tahap 0 (Ketua Pelaksana) langsung aktif |
| **Valid** | Pengajuan multiple ruangan | `{pengajuan_nama: "Seminar", pengajuan_tgl: "2026-02-01", pengajuan_jam_mulai: "08:00", pengajuan_jam_selesai: "16:00", pengajuan_jumPes: 50, pengajuan_keterangan: "", organisasi_id: 1, ruangan_ids: [1, 2, 3]}` | Sukses |
| **Valid** | Pengajuan tanpa keterangan | `{pengajuan_nama: "Kegiatan", pengajuan_tgl: "2026-03-01", pengajuan_jam_mulai: "10:00", pengajuan_jam_selesai: "11:00", pengajuan_jumPes: 10, organisasi_id: 1, ruangan_ids: [1]}` (tanpa keterangan) | Sukses, keterangan = null |
| **Invalid** | Jam selesai sebelum jam mulai | `pengajuan_jam_mulai: "13:00"`, `pengajuan_jam_selesai: "10:00"` | Validasi gagal (after:pengajuan_jam_mulai) |
| **Invalid** | Jam mulai & selesai sama | `pengajuan_jam_mulai: "10:00"`, `pengajuan_jam_selesai: "10:00"` | Validasi gagal (after, tidak sama) |
| **Invalid** | Format jam salah | `pengajuan_jam_mulai: "25:00"` | Validasi gagal (date_format:H:i) |
| **Invalid** | Tanggal tidak valid | `pengajuan_tgl: "bukan-tanggal"` | Validasi gagal (date) |
| **Invalid** | Jumlah peserta 0 | `pengajuan_jumPes: 0` | Validasi gagal (min:1) |
| **Invalid** | organisasi_id tidak ada | `organisasi_id: 999` | Validasi gagal (exists) |
| **Invalid** | ruangan_ids array kosong | `ruangan_ids: []` | Validasi gagal (required|array) |
| **Invalid** | ruangan_id tidak ada | `ruangan_ids: [999]` | Validasi gagal (exists) |
| **Boundary** | pengajuan_nama 255 karakter | `pengajuan_nama: "a" x 255` | Sukses |
| **Boundary** | pengajuan_nama 256 karakter | `pengajuan_nama: "a" x 256` | Validasi gagal (max:255) |
| **Boundary** | Jumlah peserta 1 | `pengajuan_jumPes: 1` | Sukses |
| **Boundary** | Jam mulai 00:00 | `pengajuan_jam_mulai: "00:00"` | Sukses |
| **Boundary** | Jam selesai 23:59 | `pengajuan_jam_selesai: "23:59"` | Sukses |
| **Duplicate** | Pengajuan duplikat (sama ruangan, sama waktu) | Data sama dengan existing | Tergantung validasi (mungkin sukses, belum ada validasi bentrok) |
| **Empty** | pengajuan_nama kosong | `pengajuan_nama: ""` | Validasi gagal (required) |
| **Empty** | pengajuan_tgl kosong | `pengajuan_tgl: ""` | Validasi gagal (required) |
| **Empty** | pengajuan_jam_mulai kosong | `pengajuan_jam_mulai: ""` | Validasi gagal (required) |
| **Empty** | pengajuan_jam_selesai kosong | `pengajuan_jam_selesai: ""` | Validasi gagal (required) |
| **Empty** | pengajuan_jumPes kosong | `pengajuan_jumPes: ""` | Validasi gagal (required) |
| **Empty** | organisasi_id kosong | `organisasi_id: ""` | Validasi gagal (required) |
| **Empty** | ruangan_ids kosong | `ruangan_ids: ""` | Validasi gagal (required) |
| **Empty** | ketua_pelaksana_user_id kosong | `ketua_pelaksana_user_id: ""` | Validasi gagal (required) |
| **Null** | Semua field null | Semua null | Validasi gagal semua field |
| **Null** | ketua_pelaksana_user_id null | `ketua_pelaksana_user_id: null` | Validasi gagal (required|exists:m_user,user_id) |
| **Invalid** | ketua_pelaksana_user_id tidak ada | `ketua_pelaksana_user_id: 99999` | Validasi gagal (exists) |

### 11.2 Verifikasi Admin (Terima/Tolak)

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Terima pengajuan | ID = 1 (status 'Diajukan') | `{status: true, message: "Pengajuan diterima dan jadwal berhasil dibuat!"}` |
| **Valid** | Tolak dengan catatan | ID = 2, `{catatan_verifikator: "Dokumen tidak lengkap"}` | `{status: true, message: "Pengajuan berhasil ditolak."}` |
| **Valid** | Tolak tanpa catatan | ID = 3, `{catatan_verifikator: null}` | Sukses |
| **Invalid** | Terima pengajuan sudah diproses | ID = 4 (status 'Diterima') | `{status: false, message: "Pengajuan ini sudah diproses sebelumnya."}` |
| **Invalid** | Terima ID tidak ada | ID = 999 | `{status: false, message: "Data pengajuan tidak ditemukan!"}` |
| **Null** | ID null | ID = null | 404 (route tidak match) |

### 11.3 Approve Approval (Pemegang Jabatan Approval)

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Setujui tahap 0 (Ketua Pelaksana) | `approvalId=1` (urutan_tahap=0), `{status_approval: "Disetujui"}` | Sukses, tahap 1 (Ketua Umum) diaktifkan |
| **Valid** | Setujui tahap | `approvalId=2`, `{status_approval: "Disetujui"}` | `{status: true, message: "Tahap approval disetujui. ..."}` |
| **Valid** | Tolak dengan alasan | `approvalId=3`, `{status_approval: "Ditolak", alasan_penolakan: "Bentrok jadwal"}` | `{status: true, message: "Pengajuan ditolak pada tahap ini."}` |
| **Invalid** | Tolak tanpa alasan | `approvalId=4`, `{status_approval: "Ditolak", alasan_penolakan: ""}` | `{status: false, message: "Validasi Gagal", msgField: ...}` |
| **Invalid** | Status tidak valid | `{status_approval: "Mungkin"}` | `{status: false, message: "Status approval tidak valid."}` |
| **Invalid** | Approval sudah diproses | approvalId sudah 'Disetujui' | `{status: false, message: "Tahap approval ini sudah diproses sebelumnya."}` |
| **Invalid** | Bukan pemegang jabatan yang berhak | approvalId milik jabatan user lain | `{status: false, message: "Anda tidak berwenang memproses tahap approval ini."}` |
| **Duplicate** | Setujui 2x approvalId sama | Proses kedua kali | Error "sudah diproses" |
| **Null** | approvalId null | `/pengajuan/approval//proses_ajax` | 404 |
| **Null** | status_approval null | `{status_approval: null}` | Validasi gagal |

---

## 12. MODUL JADWAL

### 12.1 Create Jadwal

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Jadwal valid | `{user_id: 1, jadwal_nama: "Seminar AI", jadwal_tgl: "2026-01-20", jadwal_jam_mulai: "08:00", jadwal_jam_selesai: "17:00", jadwal_jumPes: 100, jadwal_status: "Akan Datang"}` | Sukses |
| **Invalid** | Jam selesai sebelum mulai | `jadwal_jam_mulai: "14:00"`, `jadwal_jam_selesai: "08:00"` | Validasi gagal |
| **Invalid** | Jumlah peserta 0 | `jadwal_jumPes: 0` | Validasi gagal |
| **Boundary** | jadwal_nama 255 karakter | `jadwal_nama: "a" x 255` | Sukses |
| **Empty** | jadwal_nama kosong | `jadwal_nama: ""` | Validasi gagal |
| **Empty** | jadwal_tgl kosong | `jadwal_tgl: ""` | Validasi gagal |
| **Null** | user_id null | `user_id: null` | Validasi gagal |

### 12.2 Update Status Jadwal

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Status 'Berlangsung' | `{jadwal_status: "Berlangsung"}` | Sukses |
| **Valid** | Status 'Selesai' | `{jadwal_status: "Selesai"}` | Sukses |
| **Valid** | Status 'Dokumentasi Tidak Sesuai' | `{jadwal_status: "Dokumentasi Tidak Sesuai"}` | Sukses |
| **Invalid** | Status tidak dikenal | `{jadwal_status: "StatusInvalid"}` | Error |
| **Invalid** | ID jadwal tidak ada | ID = 999 | Error |
| **Empty** | Status string kosong | `{jadwal_status: ""}` | Validasi gagal |
| **Null** | Status null | `{jadwal_status: null}` | Validasi gagal |

---

## 13. MODUL FORMULIR (UPLOAD)

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Upload PDF valid | File: `formulir_peminjaman.pdf`, size: 500KB | `{status: true, message: "..."}` |
| **Invalid** | Upload file DOCX | File: `formulir.docx` | Error (mimes:pdf) |
| **Invalid** | Upload file JPG | File: `foto.jpg` | Error (mimes:pdf) |
| **Invalid** | Upload file > 2MB | File: 3MB | Error (max:2048) |
| **Empty** | Tidak upload file | No file | Error (required) |
| **Null** | File null | `file_formulir: null` | Error (required) |

---

## 14. MODUL DASHBOARD

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Dashboard Admin | Login sebagai ADM | View `admin.welcome` dengan semua statistik + early warning |
| **Valid** | Dashboard Dosen | Login sebagai DSN | View `dosen.welcome` + pengajuanAktif + ruanganHariIni |
| **Valid** | Dashboard Dosen/Mahasiswa pemegang jabatan approval | Login sebagai DSN/MHS yang punya baris `m_jabatan_approval` | Widget `_antrian_approval_widget` disertakan di `dosen.welcome`/`mahasiswa.welcome` |
| **Valid** | Dashboard Mahasiswa | Login sebagai MHS | View `mahasiswa.welcome` + pengajuanAktif |
| **Invalid** | Dashboard tanpa data | Database kosong | Statistik semua 0, chart kosong |
| **Empty** | Tidak ada early warning | Tidak ada pengajuan mendekati batas | Data earlyWarning kosong |
| **Empty** | Tidak ada antrian jabatan approval | User tidak punya jabatan approval / tidak ada tahap aktif | Data antrianApproval kosong |

---

## 15. MODUL STATUS RUANGAN PER-TANGGAL (Baru)

### 15.1 Availability Query

| Kategori | Skenario | Data Input | Expected Output |
|---|---|---|---|
| **Valid** | Tanggal tanpa booking sama sekali | `GET /ruangan/availability_ajax?tanggal=2026-08-01` | Semua ruangan 'Tersedia' |
| **Valid** | Ruangan dengan pengajuan pending di tanggal tsb | Ruangan X punya pengajuan `status='Diajukan'` di tanggal itu | Ruangan X → 'Pending' |
| **Valid** | Ruangan dengan jadwal aktif di tanggal tsb | Ruangan X punya Jadwal `jadwal_status IN ('Akan Datang','Berlangsung','Ditinjau','Dokumentasi Tidak Sesuai')` | Ruangan X → 'Tidak Tersedia' |
| **Valid** | Manual override menang | Ruangan X `ruangan_status='Tidak Tersedia'` manual, tidak ada jadwal/pengajuan | Ruangan X tetap 'Tidak Tersedia' |
| **Valid** | Jadwal 'Selesai' tidak menghalangi | Ruangan X punya Jadwal `jadwal_status='Selesai'` | Ruangan X → 'Tersedia' (jika tidak ada faktor lain) |
| **Invalid** | Tanggal tidak dikirim | `GET /ruangan/availability_ajax` (tanpa `tanggal`) | Validasi gagal (required|date) |
| **Invalid** | Ruangan tidak ditemukan (kalender) | `GET /ruangan/999/kalender_data_ajax` | 404 |
| **Boundary** | Tanggal hari ini persis | `tanggal` = hari ini | Precedence dihitung sama seperti tanggal lain |
| **Null** | tanggal null | `tanggal: null` | Validasi gagal |

---

## LAMPIRAN A: DATA SEEDING UNTUK TESTING

### Data User Testing

```sql
-- Users untuk testing login (level VRF terpisah SUDAH DIHAPUS — pemegang jabatan approval
-- adalah dosen/mahasiswa BIASA, jabatan hanya baris tambahan di m_jabatan_approval, lihat di bawah)
INSERT INTO `m_user` (`user_id`, `level_id`, `username`, `password`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin01', '$2y$10$...', NOW(), NOW()),
(2, 2, 'dosen01', '$2y$10$...', NOW(), NOW()),
(3, 3, 'tendik01', '$2y$10$...', NOW(), NOW()),
(4, 4, 'mhs01', '$2y$10$...', NOW(), NOW());

-- Gunakan password: "password123" untuk semua testing user (Hash::make('password123'))
```

### Data Organisasi & Jabatan Approval Testing

```sql
-- Organisasi
INSERT INTO `m_organisasi` (`organisasi_id`, `organisasi_kode`, `organisasi_nama`) VALUES
(1, 'HMTI', 'Himpunan Mahasiswa Teknik Informatika'),
(2, 'BEM', 'Badan Eksekutif Mahasiswa JTI');

-- Jabatan Approval — user_id di sini menunjuk ke dosen/mahasiswa yang SUDAH ADA (bukan akun terpisah)
INSERT INTO `m_jabatan_approval` (`jabatan_id`, `user_id`, `organisasi_id`, `posisi_approval`, `urutan_approval`) VALUES
(1, 5, 1, 'Ketua Umum', 1),
(2, 6, NULL, 'DPK', 2),
(3, 7, NULL, 'Presiden BEM', 2),
(4, 8, NULL, 'Ketua Jurusan', 3),
(5, 9, NULL, 'Wakil Direktur II', 3);
-- Catatan: baris "Ketua Pelaksana" (urutan_approval=0) TIDAK di-seed di muka —
-- dibuat otomatis (firstOrCreate) saat pengajuan pertama kali dibuat dengan user tsb sebagai Ketua Pelaksana.
-- Unique index (user_id, posisi_approval) mencegah duplikasi jika user yang sama dipilih lagi di pengajuan lain.
```

### Data Ruangan Testing

```sql
INSERT INTO `m_ruangan` (`ruangan_id`, `ruangan_kode`, `ruangan_nama`, `ruangan_fasilitas`, `ruangan_kuota`, `ruangan_status`, `ruangan_kategori`) VALUES
(1, 'R101', 'Lab Komputer 1', 'AC, LCD, Komputer 30 unit', 30, 'Tersedia', 'Jurusan'),
(2, 'R102', 'Lab Komputer 2', 'AC, LCD, Komputer 25 unit', 25, 'Tersedia', 'Jurusan'),
(3, 'A101', 'Aula Serbaguna', 'AC, Sound System, Proyektor', 200, 'Tersedia', 'Umum'),
(4, 'R201', 'Ruang Seminar', 'AC, LCD, Whiteboard', 50, 'Tersedia', 'Jurusan');
```

### Data Pengajuan Testing dengan Status Berbeda

```sql
-- Pengajuan 1: Status Diajukan (untuk test approval flow) — ketua_pelaksana_user_id wajib diisi
INSERT INTO `t_pengajuan` (`pengajuan_id`, `user_id`, `organisasi_id`, `ketua_pelaksana_user_id`, `pengajuan_nama`, `pengajuan_tgl`, `pengajuan_jam_mulai`, `pengajuan_jam_selesai`, `pengajuan_jumPes`, `pengajuan_keterangan`, `pengajuan_status`) VALUES
(1, 4, 1, 2, 'Rapat Rutin HMTI', '2026-01-20', '09:00', '12:00', 20, 'Rapat bulanan', 'Diajukan');

-- Pengajuan 2: Status Diterima (untuk test cetak surat)
INSERT INTO `t_pengajuan` (`pengajuan_id`, `user_id`, `organisasi_id`, `ketua_pelaksana_user_id`, `pengajuan_nama`, `pengajuan_tgl`, `pengajuan_jam_mulai`, `pengajuan_jam_selesai`, `pengajuan_jumPes`, `pengajuan_keterangan`, `pengajuan_status`, `nomor_surat`) VALUES
(2, 2, 2, 2, 'Seminar Nasional TI', '2026-02-10', '08:00', '16:00', 100, 'Seminar tahunan', 'Diterima', '1/SPR-JTI/I/2026');

-- Pengajuan 3: Status Ditolak
INSERT INTO `t_pengajuan` (`pengajuan_id`, `user_id`, `organisasi_id`, `ketua_pelaksana_user_id`, `pengajuan_nama`, `pengajuan_tgl`, `pengajuan_jam_mulai`, `pengajuan_jam_selesai`, `pengajuan_jumPes`, `pengajuan_keterangan`, `pengajuan_status`, `catatan_verifikator`) VALUES
(3, 4, 1, 2, 'Acara Tidak Disetujui', '2026-03-01', '10:00', '12:00', 15, 'Test', 'Ditolak', 'Dokumen tidak lengkap');
```

### Data Approval Testing

```sql
-- Jabatan Ketua Pelaksana untuk user_id=2 (dibuat otomatis oleh generateApprovalStages(), disimulasikan di sini untuk seeding manual)
INSERT INTO `m_jabatan_approval` (`jabatan_id`, `user_id`, `organisasi_id`, `posisi_approval`, `urutan_approval`) VALUES
(6, 2, NULL, 'Ketua Pelaksana', 0);

-- Approval untuk Pengajuan 1 (diawali tahap 0 Ketua Pelaksana yang aktif, sisanya menunggu)
INSERT INTO `t_pengajuan_approval` (`approval_id`, `pengajuan_id`, `jabatan_id`, `urutan_tahap`, `status_approval`, `batas_waktu`, `diproses_pada`) VALUES
(1, 1, 6, 0, 'Menunggu', DATE_ADD(NOW(), INTERVAL 2 DAY), NULL),
(2, 1, 1, 1, 'Menunggu', NULL, NULL),
(3, 1, 2, 2, 'Menunggu', NULL, NULL),
(4, 1, 3, 2, 'Menunggu', NULL, NULL),
(5, 1, 4, 3, 'Menunggu', NULL, NULL);

-- Approval untuk Pengajuan 2 (sudah selesai semua tahap termasuk tahap 0)
INSERT INTO `t_pengajuan_approval` (`approval_id`, `pengajuan_id`, `jabatan_id`, `urutan_tahap`, `status_approval`, `batas_waktu`, `diproses_pada`) VALUES
(6, 2, 6, 0, 'Disetujui', DATE_ADD(NOW(), INTERVAL -2 DAY), NOW()),
(7, 2, 1, 1, 'Disetujui', DATE_ADD(NOW(), INTERVAL -1 DAY), NOW()),
(8, 2, 2, 2, 'Disetujui', DATE_ADD(NOW(), INTERVAL -1 DAY), NOW()),
(9, 2, 3, 2, 'Disetujui', DATE_ADD(NOW(), INTERVAL -1 DAY), NOW()),
(10, 2, 4, 3, 'Disetujui', DATE_ADD(NOW(), INTERVAL -1 DAY), NOW());

-- Approval expired (untuk test auto reject) — tahap 0 sudah disetujui, tahap 1 expired
INSERT INTO `t_pengajuan_approval` (`approval_id`, `pengajuan_id`, `jabatan_id`, `urutan_tahap`, `status_approval`, `batas_waktu`, `diproses_pada`) VALUES
(11, 3, 6, 0, 'Disetujui', DATE_ADD(NOW(), INTERVAL -4 DAY), NOW()),
(12, 3, 1, 1, 'Menunggu', DATE_ADD(NOW(), INTERVAL -3 DAY), NULL);
```

---

## LAMPIRAN B: RINGKASAN JUMLAH DATA TEST

| Modul | Valid | Invalid | Boundary | Duplicate | Empty | Null | Total |
|---|---|---|---|---|---|---|---|
| 1. Auth (Login) | 5 | 3 | 2 | — | 3 | 2 | **15** |
| 2. Auth (Profile) | 4 | 2 | 4 | 1 | 3 | 3 | **17** |
| 3. Admin CRUD | 2 | 3 | 4 | 2 | 5 | 3 | **19** |
| 4. Dosen CRUD | 2 | 2 | — | 1 | 2 | 1 | **8** |
| 5. Dosen Import | 1 | 3 | — | 1 | 2 | 1 | **8** |
| 6. Tendik CRUD | 2 | 1 | — | 1 | 1 | 1 | **6** |
| 7. Mahasiswa CRUD | 2 | 3 | 4 | 1 | 1 | 1 | **12** |
| 8. Prodi CRUD | 1 | — | 2 | 1 | 2 | 1 | **7** |
| 9. Kelas CRUD | 1 | 1 | — | 1 | 1 | 1 | **5** |
| 10. Ruangan CRUD | 3 | 2 | 2 | 1 | 2 | 2 | **12** |
| 11. Organisasi CRUD | 2 | — | — | 1 | 2 | 1 | **6** |
| 12. Jabatan Approval | 6 | 2 | — | 1 | — | 2 | **11** |
| 13. Pengajuan | 4 | 8 | 4 | 1 | 8 | 3 | **28** |
| 14. Verifikasi Admin | 3 | 2 | — | — | — | 1 | **6** |
| 15. Approval (Jabatan Approval, termasuk tahap Ketua Pelaksana) | 3 | 4 | — | 1 | — | 2 | **10** |
| 16. Jadwal CRUD | 1 | 2 | 1 | — | 2 | 1 | **7** |
| 17. Update Status Jadwal | 3 | 2 | — | — | 1 | 1 | **7** |
| 18. Formulir Upload | 1 | 3 | — | — | 1 | 1 | **6** |
| 19. Dashboard | 4 | 1 | — | — | 2 | — | **7** |
| 20. Status Ruangan Per-Tanggal (Baru) | 5 | 2 | 1 | — | — | 1 | **9** |
| **TOTAL** | **56** | **44** | **24** | **14** | **39** | **29** | **206** |

