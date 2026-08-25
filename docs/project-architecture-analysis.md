# Project Architecture Analysis — SPR JTI (Sistem Peminjaman Ruangan JTI)

> **Versi Dokumen:** 1.0  
> **Project:** Sistem Peminjaman Ruangan: PLP JTI  
> **Framework:** Laravel 10.x  

> **Update terakhir:** Redesain besar — level akun `VRF` dan tabel `m_verifikator` DIHAPUS, diganti `m_jabatan_approval` (`JabatanApprovalModel`) yang menempel ke user dosen/mahasiswa yang sudah ada (bukan level akun terpisah). Ditambahkan tahap approval baru "Ketua Pelaksana" (urutan_tahap=0, dipilih bebas per-pengajuan) dan fitur status ruangan per-tanggal (`availability_ajax`, `kalender_ajax`, `kalender_data_ajax`). Lihat detail di masing-masing bagian bertanda "(Baru)" di bawah.

---

## 1. Struktur Sistem

### 1.1 Layered Architecture

```
┌──────────────────────────────────────────────────────────────────┐
│                         ROUTES (web.php, api.php)                 │
│   Route::get('/login'), Route::post('/login'), Route::get('/')   │
│   Route::group(['prefix' => ...]) — dengan middleware otorisasi  │
├──────────────────────────────────────────────────────────────────┤
│                      MIDDLEWARE STACK                             │
│   Global: EncryptCookies, StartSession, VerifyCsrfToken          │
│   Alias: auth → Authenticate, authorize → AuthorizeUser          │
├──────────────────────────────────────────────────────────────────┤
│                      CONTROLLERS (12 Controllers)                 │
│   ├── AuthController        │  ──  Login/Logout                  │
│   ├── WelcomeController     │  ──  Dashboard, Formulir, Landing  │
│   ├── AdminController       │  ──  CRUD Admin                    │
│   ├── DosenController       │  ──  CRUD Dosen                    │
│   ├── TendikController      │  ──  CRUD Tendik                   │
│   ├── MahasiswaController   │  ──  CRUD Mahasiswa                │
│   ├── ProdiController       │  ──  CRUD Prodi                    │
│   ├── KelasController       │  ──  CRUD Kelas                    │
│   ├── RuanganController     │  ──  CRUD Ruangan                  │
│   ├── JadwalController      │  ──  CRUD Jadwal                   │
│   ├── OrganisasiController  │  ──  CRUD Organisasi               │
│   ├── PengajuanController   │  ──  Pengajuan + Approval          │
│   └── ProfileController     │  ──  Edit Profil                   │
├──────────────────────────────────────────────────────────────────┤
│                       SERVICES (16 Services)                      │
│   ├── AuthService           │  ──  Login/logout logic            │
│   ├── UserService           │  ──  CRUD user + role-specific     │
│   ├── UserManagementService │  ──  DataTable, import             │
│   ├── DashboardService      │  ──  Statistik dashboard           │
│   ├── FileService           │  ──  Upload/download formulir      │
│   ├── ImportService         │  ──  Excel import                  │
│   ├── JadwalService         │  ──  CRUD jadwal                   │
│   ├── RuanganService        │  ──  CRUD ruangan                  │
│   ├── KelasService          │  ──  CRUD kelas                    │
│   ├── LevelService          │  ──  Level lookup                  │
│   ├── ProdiService          │  ──  Prodi logic                   │
│   ├── ProdiCrudService      │  ──  Prodi CRUD                    │
│   ├── OrganisasiService     │  ──  CRUD organisasi               │
│   ├── PengajuanService      │  ──  Pengajuan + approval logic    │
│   ├── ProfileService        │  ──  Profile update                │
│   └── ControllerResponseService │  ──  Standardized responses    │
├──────────────────────────────────────────────────────────────────┤
│                     REPOSITORIES (2 Repositories)                  │
│   ├── EloquentUserRepository    implements UserRepositoryInterface│
│   └── EloquentJadwalRepository  implements JadwalRepositoryInterface│
├──────────────────────────────────────────────────────────────────┤
│                        MODELS (14 Models)                         │
│   │ UserModel (m_user) — Authenticatable + JWT, hasMany            │
│   │   jabatanApprovals() (Baru — ganti hasOne verifikator())      │
│   │ LevelModel (m_level) — Level kode ADM/DSN/TDK/MHS (VRF dihapus)│
│   │ AdminModel, DosenModel, TendikModel, MahasiswaModel           │
│   │ ProdiModel, KelasModel, FormulirModel                         │
│   │ RuanganModel, JadwalModel                                     │
│   │ OrganisasiModel, JabatanApprovalModel (Baru — ganti           │
│   │   VerifikatorModel, tabel m_jabatan_approval)                 │
│   │ PengajuanModel (+ ketuaPelaksana() BelongsTo, Baru),           │
│   │   PengajuanApprovalModel (jabatanApproval() BelongsTo)         │
├──────────────────────────────────────────────────────────────────┤
│                      DTOS & EVENTS & LISTENERS                    │
│   │ AuthRequestDTO, CreateUserDTO, ImportAdminDTO, ImportDosenDTO │
│   │ ImportTendikDTO, ImportUserDTO, ProfileDTO, UpdateUserDTO     │
│   │ ValidationResult                                              │
│   │ Events: UserCreated, UserUpdated, UserDeleted                 │
│   │ Listeners: LogUserCreation, SendWelcomeEmail                  │
├──────────────────────────────────────────────────────────────────┤
│                     DATABASE & MIGRATIONS                         │
│   ├── 22 migration files (m_*, t_*, pivot)                        │
│   ├── 15 seeder files (Level, User, Admin, Dosen, etc.)          │
│   └── MySQL (production), SQLite (testing)                        │
└──────────────────────────────────────────────────────────────────┘
```

### 1.2 Technology Stack

| Layer | Technology | Keterangan |
|-------|-----------|-----------|
| **Framework** | Laravel 10.x | PHP 8.1+ |
| **Authentication** | Session-based (web guard) | Login via Auth::attempt() |
| **JWT** | tymon/jwt-auth | Untuk API (terdaftar di UserModel) |
| **Database** | MySQL | Production |
| **Testing DB** | SQLite | Environment testing |
| **Frontend CSS** | AdminLTE 3 | Bootstrap 4, Font Awesome |
| **Frontend JS** | jQuery 3.x | AJAX via $.ajax |
| **DataTables** | Yajra DataTables | Server-side processing |
| **Form Validation** | jQuery Validation | Client-side + server-side |
| **PDF** | barryvdh/laravel-dompdf | Generate surat digital |
| **Excel Import** | PhpSpreadsheet | Import data via Excel |
| **Notifications** | SweetAlert2 | Toast/alert UI |

### 1.3 Request Lifecycle

```
Browser → GET /pengajuan
  ↓
web.php (Route matched: /pengajuan, authorize:ADM,DSN,TDK,MHS)
  ↓
Middleware Stack (web group):
  EncryptCookies → StartSession → ShareErrorsFromSession
  → VerifyCsrfToken → SubstituteBindings
  ↓
Authenticate (auth middleware) — redirect ke /login jika belum login
  ↓
AuthorizeUser (authorize middleware) — abort(403) jika role tidak sesuai
  ↓
PengajuanController@index()
  ↓
PengajuanService::indexData(Auth::user())
  ↓
PengajuanModel::with(['user', 'ruangans'])->get()
  → view('pengajuan.index', [...])
  ↓
Response → HTML → Browser
```

---

## 2. Daftar Modul

| No | Modul | Controller | Service | Model | Relasi Kunci | Deskripsi |
|-----|-------|-----------|---------|-------|-------------|-----------|
| 1 | **Authentication** | AuthController | AuthService | UserModel, LevelModel | user → level | Login/logout session-based via Auth::attempt() |
| 2 | **Dashboard** | WelcomeController | DashboardService | RuanganModel, JadwalModel, PengajuanModel, JabatanApprovalModel | multi-table stats | Dashboard role-aware dengan 4 view (admin, dosen, tendik, mahasiswa) — widget antrian approval disertakan untuk DSN/MHS yang punya jabatan approval |
| 3 | **Admin Management** | AdminController | UserService | AdminModel, UserModel | admin → user → prodi | CRUD admin + import Excel via PhpSpreadsheet |
| 4 | **Dosen Management** | DosenController | UserService | DosenModel, UserModel | dosen → user → prodi | CRUD dosen + import Excel |
| 5 | **Tendik Management** | TendikController | UserService | TendikModel, UserModel | tendik → user | CRUD tendik + import Excel |
| 6 | **Mahasiswa Management** | MahasiswaController | UserService | MahasiswaModel, UserModel | mahasiswa → user → prodi → kelas | CRUD mahasiswa + import + get_kelas_by_prodi |
| 7 | **Prodi Management** | ProdiController | ProdiCrudService | ProdiModel | — | CRUD program studi |
| 8 | **Kelas Management** | KelasController | KelasService | KelasModel | kelas → prodi | CRUD kelas |
| 9 | **Ruangan Management** | RuanganController | RuanganService | RuanganModel | ruangan → jadwal (pivot), ruangan → pengajuan (pivot, Baru) | CRUD ruangan + kategori (Jurusan/Umum) + status manual (Tersedia/Diajukan/Tidak Tersedia) **+ status per-tanggal terkomputasi (Baru, lihat 5.11)** |
| 10 | **Jadwal Management** | JadwalController | JadwalService | JadwalModel | jadwal → user, ruangan (pivot) | CRUD jadwal + status + update_status + get_kelas_by_prodi |
| 11 | **Organisasi Mahasiswa** | OrganisasiController | OrganisasiService | OrganisasiModel | organisasi → jabatanApprovals, pengajuan | CRUD organisasi + logo |
| 12 | **Jabatan Approval (dahulu "Verifikator")** | (via Organisasi/Pengajuan) | — | JabatanApprovalModel | jabatan_approval → user → organisasi (nullable) | Mapping user (dosen/mahasiswa existing) ke posisi approval; `hasMany` per user (bisa pegang >1 jabatan) |
| 13 | **Pengajuan Peminjaman** | PengajuanController | PengajuanService | PengajuanModel, PengajuanApprovalModel | pengajuan → user → organisasi → ruangan (pivot) → ketuaPelaksana (user, Baru) → approval | CRUD pengajuan + approval berjenjang **5 tahap** (tahap 0 Ketua Pelaksana + 4 tahap lama) |
| 14 | **Profile** | ProfileController | ProfileService | UserModel | — | Edit profil pengguna (show/edit/update_ajax) |
| 15 | **Formulir** | (via WelcomeController) | FileService | FormulirModel | formulir → pengajuan | Upload (ADM) / Download (DSN,TDK,MHS) formulir PDF |
| 16 | **Surat Digital** | (via PengajuanController) | PengajuanService | PengajuanModel | pengajuan → organisasi (logo) | Generate PDF surat peminjaman via DomPDF |

---

## 3. Daftar Endpoint

### 3.1 Authentication Endpoints

| Method | URI | Controller@Method | Middleware | Input | Response | Kode |
|--------|-----|-------------------|-----------|-------|----------|------|
| GET | `/` | WelcomeController@landing | — | — | View (landing.blade.php) | 200 |
| GET | `/login` | AuthController@login | guest | — | View (auth.login) | 200 |
| POST | `/login` | AuthController@postLogin | — | username, password | JSON {status, message, redirect} | 200 |
| GET | `/logout` | AuthController@logout | auth | — | Redirect to / | 302 |

### 3.2 Dashboard Endpoints

| Method | URI | Controller@Method | Middleware | Response |
|--------|-----|-------------------|-----------|----------|
| GET | `/dashboard` | WelcomeController@index | auth | View (dashboard role-aware) |
| GET | `/profile/show_ajax` | ProfileController@show_ajax | auth | Partial view (modal) |
| GET | `/profile/edit_ajax` | ProfileController@edit_ajax | auth | Partial view (modal) |
| PUT | `/profile/update_ajax` | ProfileController@update_ajax | auth | JSON response |

### 3.3 Jadwal Endpoints

| Method | URI | Middleware | Fungsi |
|--------|-----|-----------|--------|
| GET `/jadwal` | POST `/jadwal/list` | authorize:ADM,DSN,TDK,MHS | Index + DataTable |
| GET `/jadwal/{id}/show_ajax` | GET `/jadwal/get_kelas_by_prodi/{prodi_id}` | authorize:ADM,DSN,TDK,MHS | View + AJAX helper |
| GET `/jadwal/create_ajax` | POST `/jadwal/ajax` | authorize:ADM | Create |
| GET `/jadwal/{id}/edit_ajax` | PUT `/jadwal/{id}/update_ajax` | authorize:ADM | Edit |
| GET `/jadwal/{id}/confirm_ajax` | DELETE `/jadwal/{id}/delete_ajax` | authorize:ADM | Delete |
| PUT `/jadwal/{id}/update_status_ajax` | — | authorize:ADM | Update status |

### 3.4 Ruangan Endpoints

| Method | URI | Middleware | Fungsi |
|--------|-----|-----------|--------|
| GET `/ruangan` | POST `/ruangan/list` | authorize:ADM,DSN,TDK,MHS | Index + DataTable |
| GET `/ruangan/{id}/show_ajax` | — | authorize:ADM,DSN,TDK,MHS | View detail |
| GET `/ruangan/create_ajax` | POST `/ruangan/ajax` | authorize:ADM | Create |
| GET `/ruangan/{id}/edit_ajax` | PUT `/ruangan/{id}/update_ajax` | authorize:ADM | Edit |
| GET `/ruangan/{id}/confirm_ajax` | DELETE `/ruangan/{id}/delete_ajax` | authorize:ADM | Delete |
| GET `/ruangan/availability_ajax?tanggal=` | — | authorize:ADM,DSN,TDK,MHS | **(Baru)** Availability semua ruangan untuk 1 tanggal |
| GET `/ruangan/{id}/kalender_ajax` | — | authorize:ADM,DSN,TDK,MHS | **(Baru)** View kalender bulanan 1 ruangan |
| GET `/ruangan/{id}/kalender_data_ajax?year=&month=` | — | authorize:ADM,DSN,TDK,MHS | **(Baru)** Data JSON kalender bulanan |

### 3.5 Organisasi Endpoints

| Method | URI | Middleware | Fungsi |
|--------|-----|-----------|--------|
| GET `/organisasi` | POST `/organisasi/list` | authorize:ADM | Index + DataTable |
| GET `/organisasi/create_ajax` | POST `/organisasi/ajax` | authorize:ADM | Create |
| GET `/organisasi/{id}/show_ajax` | — | authorize:ADM | View detail |
| GET `/organisasi/{id}/edit_ajax` | PUT `/organisasi/{id}/update_ajax` | authorize:ADM | Edit |
| GET `/organisasi/{id}/confirm_ajax` | DELETE `/organisasi/{id}/delete_ajax` | authorize:ADM | Delete |

### 3.6 Pengajuan Endpoints (3 Route Groups)

#### Group 1: authorize:ADM,DSN,TDK,MHS (Create + View)
| Method | URI | Fungsi |
|--------|-----|--------|
| GET | `/pengajuan` | Index page |
| POST | `/pengajuan/list` | DataTable list |
| GET | `/pengajuan/create_ajax` | Form create |
| POST | `/pengajuan/ajax` | Store AJAX |
| GET | `/pengajuan/{id}/show_ajax` | Show detail |
| GET | `/pengajuan/{id}/timeline_ajax` | Timeline approval |
| GET | `/pengajuan/{id}/cetak_surat_ajax` | Unduh PDF surat |

#### Group 2: authorize:ADM (Verifikasi Admin)
| Method | URI | Fungsi |
|--------|-----|--------|
| GET | `/pengajuan/{id}/verifikasi_ajax` | Modal verifikasi |
| PUT | `/pengajuan/{id}/terima_ajax` | Terima (otomatis buat jadwal) |
| PUT | `/pengajuan/{id}/tolak_ajax` | Tolak (dengan catatan) |

#### Group 3: authorize:ADM,DSN,TDK,MHS (Approval Berjenjang — Baru, dahulu authorize:VRF)
| Method | URI | Fungsi |
|--------|-----|--------|
| GET | `/pengajuan/approval/antrian_ajax` | Antrian persetujuan — hasil kosong jika user tidak punya jabatan approval |
| PUT | `/pengajuan/approval/{approvalId}/proses_ajax` | Proses setuju/tolak — otorisasi riil ditegakkan di `processApproval()` (ownership check `jabatanApproval.user_id`), bukan di middleware |

### 3.7 Master Data Endpoints (Admin only — authorize:ADM)

| Modul | CRUD Patterns | Import |
|-------|--------------|--------|
| **Admin** | `/admin`, /list, /create_ajax, /ajax, /{id}/show_ajax, /{id}/edit_ajax, /{id}/update_ajax, /{id}/confirm_ajax, /{id}/delete_ajax | `/admin/import`, /import_ajax |
| **Dosen** | Same pattern as Admin | `/dosen/import`, /import_ajax |
| **Tendik** | Same pattern as Admin (no prodi relation) | `/tendik/import`, /import_ajax |
| **Mahasiswa** | Same pattern + `/get_kelas_by_prodi/{prodi_id}` | `/mahasiswa/import`, /import_ajax |
| **Prodi** | `/prodi`, /list, /create_ajax, /ajax, /{id}/edit_ajax, /{id}/update_ajax, /{id}/confirm_ajax, /{id}/delete_ajax | — |
| **Kelas** | `/kelas`, /list, /create_ajax, /ajax, /{id}/edit_ajax, /{id}/update_ajax, /{id}/confirm_ajax, /{id}/delete_ajax | — |

### 3.8 API Endpoints

| Method | URI | Middleware | Fungsi |
|--------|-----|-----------|--------|
| GET | `/api/user` | auth:sanctum | Get current authenticated user |

---

## 4. Daftar Aktor (User Roles)

| Kode | Level ID | Role | Login Credentials (Seeder) | Hak Akses Utama |
|------|---------|------|---------------------------|----------------|
| ADM | 1 | **Admin** | admin1 / 0000000000 | CRUD semua master data, verifikasi pengajuan (terima/tolak), lihat early warning |
| DSN | 2 | **Dosen** | dosen / 1111111111 | Peminjam: buat pengajuan, lihat jadwal/ruangan sendiri; bisa juga jadi pemegang jabatan approval |
| TDK | 3 | **Tendik** | tendik1 / 0021111111 | Peminjam: buat pengajuan, lihat jadwal/ruangan sendiri |
| MHS | 4 | **Mahasiswa** | mahasiswa1 / 0011111111 | Peminjam: buat pengajuan, lihat jadwal/ruangan sendiri; bisa juga jadi pemegang jabatan approval |

> **Level `VRF` DIHAPUS.** Pemegang jabatan approval bukan level akun terpisah — mereka login sebagai DSN/MHS biasa dan mendapat akses approval melalui baris tambahan di `m_jabatan_approval` (relasi `hasMany` ke `UserModel`), bukan lewat `level_id`.

### 4.1 Posisi Jabatan Approval (m_jabatan_approval, dahulu "m_verifikator")

| Posisi | Organisasi | Urutan Approval | Username Seeder (dosen/mhs existing) | Password |
|--------|-----------|----------------|----------------|----------|
| Ketua Pelaksana **(Baru)** | — (dipilih bebas per-pengajuan) | 0 | Dipilih user saat create pengajuan; baris dibuat otomatis (`firstOrCreate`) | — |
| Ketua Umum | HMJ (organisasi_id=1) | 1 | 2241760003 (mahasiswa) | 123456 |
| DPK | — (lintas organisasi) | 2 | dosen2 (dosen) | 123456 |
| Presiden BEM | — (lintas organisasi) | 2 | 2241760063 (mahasiswa) | 123456 |
| Ketua Jurusan | — (lintas organisasi) | 3 | 0013333333 (dosen) | 123456 |
| Wakil Direktur II | — (lintas organisasi) | 3 | 0014444444 (dosen) | 123456 |

### 4.2 Matriks Otorisasi

| Fitur / Halaman | ADM | DSN | TDK | MHS |
|----------------|:---:|:---:|:---:|:---:|
| Login / Logout | ✓ | ✓ | ✓ | ✓ |
| Dashboard | ✓ | ✓ | ✓ | ✓ |
| CRUD Admin | ✓ | ✗ | ✗ | ✗ |
| CRUD Dosen | ✓ | ✗ | ✗ | ✗ |
| CRUD Tendik | ✓ | ✗ | ✗ | ✗ |
| CRUD Mahasiswa | ✓ | ✗ | ✗ | ✗ |
| CRUD Prodi | ✓ | ✗ | ✗ | ✗ |
| CRUD Kelas | ✓ | ✗ | ✗ | ✗ |
| CRUD Ruangan | ✓ (full) | ✗ | ✗ | ✗ |
| View Ruangan | ✓ | ✓ | ✓ | ✓ |
| Status Ruangan Per-Tanggal (Baru) | ✓ | ✓ | ✓ | ✓ |
| CRUD Jadwal | ✓ (full) | ✗ | ✗ | ✗ |
| View Jadwal | ✓ | ✓ | ✓ | ✓ |
| CRUD Organisasi | ✓ | ✗ | ✗ | ✗ |
| Buat Pengajuan | ✓ | ✓ | ✓ | ✓ |
| Verifikasi (ADM) | ✓ | ✗ | ✗ | ✗ |
| Approval (jabatan approval, ✓* = fungsional hanya jika benar-benar pegang jabatan) | ✓* | ✓* | ✓* | ✓* |
| Cetak Surat | ✓ | ✓ | ✓ | ✓ |
| Upload Formulir | ✓ | ✗ | ✗ | ✗ |
| Download Formulir | ✗ | ✓ | ✓ | ✓ |

---

## 5. Daftar Fitur

### 5.1 Manajemen Data Master (FR-1)

| ID | Requirement | Modul Terkait | Status |
|----|------------|--------------|--------|
| FR-1.1 | CRUD Organisasi Mahasiswa (nama, kode, logo) | Organisasi | ✅ |
| FR-1.2 | Mapping user ke posisi approval (Ketua Umum, DPK, PresBEM) | Jabatan Approval | ✅ |
| FR-1.3 | Posisi approval lintas-organisasi (Ketua Jurusan, Wakil Direktur II) | Jabatan Approval | ✅ |
| FR-1.4 | Urutan tahap approval per posisi jabatan | Jabatan Approval | ✅ |

### 5.2 Jabatan Approval — dahulu "Role Verifikator" (FR-2, REDESAIN)

| ID | Requirement | Implementasi |
|----|------------|-------------|
| FR-2.1 | ~~Role VRF pada m_level~~ **DIHAPUS** — pemegang jabatan approval login sebagai DSN/MHS biasa | Level VRF dan tabel `m_verifikator` dihapus via migration |
| FR-2.2 | Detail posisi approval di tabel terpisah, menempel ke user existing | `m_jabatan_approval` (`jabatan_id`, `user_id`, `organisasi_id` nullable, `posisi_approval` string bebas, `urutan_approval`), unique index `(user_id, posisi_approval)` |
| FR-2.3 | Satu user bisa memegang lebih dari satu jabatan | `UserModel::jabatanApprovals()` — relasi `hasMany` (dahulu `hasOne verifikator()`) |
| FR-2.4 | Middleware `authorize:VRF` **DIHAPUS** | Route `/pengajuan/approval/*` sekarang `authorize:ADM,DSN,TDK,MHS`; otorisasi riil di `processApproval()` (ownership check) |
| FR-2.5 | Tahap approval "Ketua Pelaksana" **(Baru)** | Tahap 0, dipilih bebas per-pengajuan (bukan posisi tetap); baris `m_jabatan_approval` dibuat otomatis via `findOrCreateJabatanKetuaPelaksana()`, dibungkus try/catch `QueryException` untuk race safety |

### 5.3 Status Ruangan (FR-3)

| ID | Requirement | Implementasi |
|----|------------|-------------|
| FR-3.1 | Status: Tersedia, Diajukan, Tidak Tersedia | Kolom `ruangan_status` enum — kini berfungsi sebagai **manual override/fallback** |
| FR-3.2 | Sinkronisasi otomatis | `PengajuanService::syncRuanganStatus()` dipanggil saat create/accept/reject/autoReject |
| FR-3.3 | Kategori ruangan (Jurusan/Umum) | Kolom `ruangan_kategori` |
| FR-3.4 | Kategori menentukan jalur approval akhir | Jurusan → Ketua Jurusan, Umum → Wakil Direktur II |
| FR-3.5 | Status ruangan per-tanggal, terkomputasi **(Baru)** | `RuanganService::availabilityStatusForDate()` — precedence: manual override 'Tidak Tersedia' > Jadwal aktif (`jadwal_status != 'Selesai'`) > Pengajuan pending (`status='Diajukan'`) > 'Tersedia' |
| FR-3.6 | Widget availability di form pengajuan + kalender per ruangan **(Baru)** | `RuanganController::availability_ajax()` (bulk, untuk form), `kalender_ajax()`/`kalender_data_ajax()` (bulk per bulan, untuk kalender) — keduanya bulk query untuk hindari N+1 |

### 5.4 Status Jadwal (FR-4)

| ID | Requirement | Implementasi |
|----|------------|-------------|
| FR-4.1 | Status: Akan Datang, Berlangsung, Ditinjau, Dokumentasi Tidak Sesuai, Selesai | Kolom `jadwal_status` enum |
| FR-4.2 | State machine | Validasi di `JadwalService::updateStatus()` |

### 5.5 Pengajuan Peminjaman (FR-5)

| ID | Requirement | Implementasi |
|----|------------|-------------|
| FR-5.1 | Ajukan peminjaman dengan organisasi pengaju | `PengajuanService::create()` — validasi form + create record |
| FR-5.2 | Riwayat pengajuan dan jadwal | Tabel `t_pengajuan` + `t_pengajuan_ruangan` + `t_jadwal` |
| FR-5.3 | Timeline status approval | `PengajuanController@timeline_ajax` → `PengajuanService::timelineFor()` |
| FR-5.4 | Pemisahan status administratif vs operasional | `pengajuan_status` (Diajukan/Diterima/Ditolak) vs `jadwal_status` (Akan Datang/Berlangsung/Selesai) |

### 5.6 Workflow Approval Bertingkat (FR-6)

| ID | Requirement | Stage | Deskripsi |
|----|------------|-------|-----------|
| FR-6.0 | Tahap Ketua Pelaksana **(Baru)** | Stage 0 | Dipilih bebas per-pengajuan (`ketua_pelaksana_user_id`), tahap PERTAMA yang aktif (`batas_waktu` diisi saat create); Ketua Umum dst. `batas_waktu = null` sampai tahap 0 disetujui |
| FR-6.1 | Urutan approval | 5 tahap | Ketua Pelaksana (0) → Ketua Umum (1) → DPK (2) + PresBEM (2) paralel → Ketua Jurusan/Wakil Direktur II (3) |
| FR-6.2 | Pencatatan tiap tahap | — | `t_pengajuan_approval` (pengajuan_id, **jabatan_id** [dahulu verifikator_id], urutan_tahap, status_approval, batas_waktu) |
| FR-6.3 | Auto-generate tahap | — | `PengajuanService::generateApprovalStages()` dipanggil setelah create; tahap 0 via `findOrCreateJabatanKetuaPelaksana()`, tahap lain via lookup posisi+organisasi di `m_jabatan_approval` |
| FR-6.4 | Tahap paralel | Stage 2 | DPK + PresBEM memiliki urutan_tahap = 2 yang sama |
| FR-6.5 | Antrian pemegang jabatan | — | `antrianFor()` — mengumpulkan SEMUA `jabatan_id` milik user (`pluck`) karena satu user bisa pegang >1 jabatan, filter status Menunggu + batas_waktu not null |
| FR-6.6 | Setuju/tolak | — | `processApproval()` dengan validasi otorisasi (ownership check `jabatanApproval.user_id`) |
| FR-6.7 | Alasan penolakan wajib | — | Validasi: jika status Ditolak, alasan_penolakan tidak boleh empty |
| FR-6.8 | Penolakan akhiri proses | — | Update pengajuan_status = 'Ditolak', batalkan sisa tahap |
| FR-6.9 | Status akhir otomatis | — | Jika seluruh tahap Disetujui → Diterima, buat Jadwal |

### 5.7 Auto Reject (FR-7)

| ID | Requirement | Implementasi |
|----|------------|-------------|
| FR-7.1 | Batas waktu 2 hari | `batas_waktu = now()->addDays(2)` saat tahap diaktifkan |
| FR-7.2 | Auto reject otomatis | `PengajuanService::autoRejectExpiredApprovals()` — cron tiap menit |
| FR-7.3 | Scheduled command | `Console/Kernel.php` → `$schedule->call()` |
| FR-7.4 | Notifikasi peminjam | Direkomendasikan (belum diimplementasikan) |

### 5.8 Dashboard Role-Aware (FR-8)

| ID | Requirement | Implementasi |
|----|------------|-------------|
| FR-8.1 | Dashboard Peminjam: status pengajuan aktif | `getPengajuanAktifFor()` — filter by user_id + status Diajukan |
| FR-8.2 | Dashboard Peminjam: tabel ruangan hari ini | `getRuanganHariIni()` — with jadwal by today |
| FR-8.3 | Dashboard pemegang jabatan approval (DSN/MHS): widget antrian approval | `getAntrianApprovalFor()` — filter by seluruh `jabatan_id` milik user |
| FR-8.4 | Dashboard Admin: early warning | `getEarlyWarningPengajuan()` — batas_waktu < 12 jam |
| FR-8.5 | Statistik penggunaan ruangan | Total Ruangan, Jadwal Hari Ini, Ruangan Kosong, Total User, Top 5 Ruangan, Distribusi Peminjam, Tren Peminjaman |

### 5.9 Surat Digital (FR-9)

| ID | Requirement | Implementasi |
|----|------------|-------------|
| FR-9.1 | Generate PDF | `PengajuanController@cetak_surat_ajax` → DomPDF |
| FR-9.2 | Template resmi + lampiran | `pdf/surat_peminjaman.blade.php` |
| FR-9.3 | Nomor surat otomatis | `generateNomorSurat()` — format: {urutan}/SPR-JTI/{bulan_romawi}/{tahun} |
| FR-9.4 | Logo organisasi | Dari relasi organisasi → `organisasi_logo` |

### 5.10 Filter Jadwal (FR-10)

| ID | Requirement | Implementasi |
|----|------------|-------------|
| FR-10.1 | Filter berdasarkan semester | Belum diimplementasikan |

---

## 6. Daftar Dependency Antar Modul

### 6.1 Dependency Graph

```
                                ┌───────────────────┐
                                │   Authentication   │
                                │  (AuthController / │
                                │   AuthService)     │
                                └────────┬──────────┘
                                         │
                    ┌────────────────────┼────────────────────┐
                    │                    │                     │
                    ▼                    ▼                     ▼
          ┌─────────────────┐  ┌─────────────────┐  ┌───────────────────┐
          │  Level Service   │  │ Profile Service  │  │  DashboardService  │
          │  (LevelModel)    │  │  (UserModel)     │  │ (Semua Model)      │
          └────────┬────────┘  └─────────────────┘  └───────────────────┘
                   │
          ┌────────┴────────────────────────────────────────────┐
          ▼                                                     ▼
┌─────────────────────────┐                     ┌──────────────────────────┐
│  User Management Module  │                     │  Master Data Module       │
│  ┌─────────────────────┐ │                     │  ┌──────────────────────┐ │
│  │ AdminController      │ │                     │  │ ProdiController       │ │
│  │ DosenController      │ │                     │  │ KelasController       │ │
│  │ TendikController     │ │                     │  │ RuanganController     │ │
│  │ MahasiswaController  │ │                     │  │ JadwalController      │ │
│  └─────────┬───────────┘ │                     │  └──────────┬───────────┘ │
│            ▼             │                     │             ▼             │
│  ┌─────────────────────┐ │                     │  ┌──────────────────────┐ │
│  │  UserService         │ │                     │  │  RuanganService       │ │
│  │  UserManagementSvc   │ │                     │  │  JadwalService        │ │
│  │  ImportService       │ │                     │  │  ProdiCrudService     │ │
│  └─────────────────────┘ │                     │  └──────────────────────┘ │
└─────────────────────────┘                     └──────────────────────────┘
                   │                                         │
                   └──────────────┬──────────────────────────┘
                                  │
                                  ▼
                    ┌─────────────────────────┐
                    │   Organisasi Module      │
                    │  ┌─────────────────────┐ │
                    │  │ OrganisasiController  │ │
                    │  └──────────┬──────────┘ │
                    │             ▼            │
                    │  ┌─────────────────────┐ │
                    │  │ JabatanApprovalModel │ │
                    │  │ (dahulu Verifikator) │ │
                    │  └─────────────────────┘ │
                    └─────────────┬───────────┘
                                  │
                                  ▼
                    ┌─────────────────────────┐
                    │   Pengajuan Module       │
                    │  ┌─────────────────────┐ │
                    │  │ PengajuanController  │ │
                    │  └──────────┬──────────┘ │
                    │             ▼            │
                    │  ┌─────────────────────┐ │
                    │  │ PengajuanService     │ │
                    │  │ ├── create()         │ │
                    │  │ ├── accept()         │ │
                    │  │ ├── reject()         │ │
                    │  │ ├── processApproval() │ │
                    │  │ ├── generateStages() │ │
                    │  │ └── autoReject()     │ │
                    │  └─────────────────────┘ │
                    │             │            │
                    │             ▼            │
                    │  ┌─────────────────────┐ │
                    │  │ PengajuanModel       │ │
                    │  │ PengajuanApproval    │ │
                    │  └─────────────────────┘ │
                    └─────────────────────────┘
                                  │
                    ┌─────────────┴─────────────┐
                    ▼                           ▼
         ┌──────────────────┐      ┌──────────────────┐
         │  Jadwal (t_jadwal)│      │  Ruangan Status   │
         │  Dibuat otomatis  │      │  sync via Service  │
         │  saat Diterima    │      │  Tersedia/Diajukan │
         └──────────────────┘      └──────────────────┘
```

### 6.2 Data Flow Dependencies

#### A. Login Flow
```
Browser → POST /login → AuthController → AuthService::login()
  → Auth::attempt(['username' => $u, 'password' => $p])
  → UserModel (m_user) → cek password hash
  → Session created → AuthService returns {status, message, redirect}
  → Browser redirect to /dashboard
```

#### B. CRUD Master Data Flow (Admin Only)
```
Browser (ajax) → Controller → Middleware authorize:ADM
  → Controller@store_ajax → UserService::createAdmin/Dosen/Tendik/Mahasiswa()
  → EloquentUserRepository (transaction)
  → Event: UserCreated
  → Response JSON {status: true, message: "..."}
```

#### C. Pengajuan Create Flow
```
User (DSN/TDK/MHS) → POST /pengajuan/ajax → PengajuanController@store_ajax
  ├── Validasi: pengajuan_nama, tgl, jam, jumPes, organisasi_id, ketua_pelaksana_user_id (Baru), ruangan_ids
  ├── PengajuanModel::create()
  ├── $pengajuan->ruangans()->attach(ruangan_ids) — pivot t_pengajuan_ruangan
  ├── syncRuanganStatus(ruangan_ids, 'Diajukan') — FR-3.2
  ├── generateApprovalStages($pengajuan) — FR-6.3
  │   ├── Stage 0 (Baru): Ketua Pelaksana — findOrCreateJabatanKetuaPelaksana() — batas_waktu = now+2days (tahap aktif pertama)
  │   ├── Stage 1: Ketua Umum (organisasi pengaju) — batas_waktu = null (menunggu tahap 0)
  │   ├── Stage 2: DPK — batas_waktu = null
  │   ├── Stage 2: Presiden BEM — batas_waktu = null
  │   └── Stage 3: Ketua Jurusan / Wakil Dir II — batas_waktu = null
  └── Response JSON {status: true, message: "berhasil"}
```

#### D. Approval Process Flow
```
Pemegang jabatan approval (login sebagai DSN/MHS) → PUT /pengajuan/approval/{id}/proses_ajax
  → Middleware authorize:ADM,DSN,TDK,MHS (Baru — dahulu authorize:VRF)
  → PengajuanController@proses_approval_ajax
  └── PengajuanService::processApproval(approvalId, userId, status, alasan)
      ├── Cek: approval exists?
      ├── Cek: jabatanApproval.user_id cocok dengan user login? (ownership check, menggantikan middleware role check)
      ├── Cek: status_approval masih 'Menunggu'?
      │
      ├── Jika Ditolak (FR-6.8):
      │   ├── update status_approval = 'Ditolak'
      │   ├── update pengajuan_status = 'Ditolak'
      │   ├── syncRuanganStatus → 'Tersedia'
      │   └── return "Pengajuan ditolak"
      │
      ├── Jika Disetujui:
      │   ├── update status_approval = 'Disetujui'
      │   ├── Cek: semua tahap pada urutan ini sudah Disetujui? (FR-6.4)
      │   │   ├── Belum: return "Menunggu approval paralel"
      │   │   └── Ya:
      │   │       ├── Cek: ada tahap berikutnya?
      │   │       │   ├── Ada: activateNextStage() — isi batas_waktu
      │   │       │   └── Tidak ada (FR-6.9):
      │   │       │       ├── pengajuan_status = 'Diterima'
      │   │       │       ├── generateNomorSurat()
      │   │       │       ├── createJadwalFromPengajuan()
      │   │       │       └── return "Seluruh tahap selesai"
      │   │       └── return "Tahap berikutnya diaktifkan"
```

#### E. Auto Reject Flow (Scheduled)
```
Console/Kernel.php — setiap menit
  └── PengajuanService::autoRejectExpiredApprovals()
      ├── SELECT * FROM t_pengajuan_approval
      │   WHERE status_approval = 'Menunggu'
      │   AND batas_waktu IS NOT NULL
      │   AND batas_waktu < NOW()
      ├── Untuk setiap baris:
      │   ├── Update status_approval = 'Auto Reject'
      │   ├── Update pengajuan_status = 'Ditolak'
      │   └── syncRuanganStatus → 'Tersedia'
      └── Return count (idempotent — NFR-3.3)
```

#### F. Surat Digital Flow
```
User → GET /pengajuan/{id}/cetak_surat_ajax
  → PengajuanService::findForCetakSurat()
  ├── Cek: pengajuan exists?
  ├── Cek: pengajuan_status = 'Diterima'? (FR-9.1)
  ├── Jika tidak: abort(422, "Surat hanya dapat dicetak untuk pengajuan yang sudah Diterima")
  └── Jika ya:
      ├── PDF::loadView('pdf.surat_peminjaman', compact('pengajuan'))
      ├── Template: logo organisasi, nomor surat, lampiran
      └── Download PDF
```

### 6.3 Constraint Dependencies

| ID | Constraint | Source Code | Dependensi |
|----|-----------|------------|-----------|
| C1 | `ruangan_ids.*` harus `exists:m_ruangan` | PengajuanService@rules | Pengajuan → Ruangan |
| C2 | `organisasi_id` harus `exists:m_organisasi` | PengajuanService@rules | Pengajuan → Organisasi |
| C3 | Kategori ruangan → jalur approval akhir | PengajuanService@generateApprovalStages | Approval → Ruangan.kategori |
| C4 | ~~Verifikator harus user dengan role VRF~~ **DIHAPUS** — jabatan approval kini atribut tambahan (`m_jabatan_approval`), bukan level akun | — | Jabatan Approval → User (bukan Level) |
| C5 | Cetak surat hanya jika status Diterima | PengajuanService@findForCetakSurat | Surat → Pengajuan.status |
| C6 | Jadwal dibuat dari pengajuan Diterima | PengajuanService@accept/createJadwalFromPengajuan | Jadwal → Pengajuan |
| C7 | Status ruangan sync otomatis (manual override) | PengajuanService@syncRuanganStatus | Ruangan → Pengajuan/Jadwal |
| C8 | Pemegang jabatan hanya proses approval miliknya | PengajuanService@processApproval (cek `jabatanApproval.user_id`) | Approval → User |
| C9 | Auto reject idempotent — filter status 'Menunggu' | PengajuanService@autoRejectExpiredApprovals | AutoReject → Approval.status |
| C10 | Route approval accessible ke ADM,DSN,TDK,MHS; otorisasi riil di ownership check | Route middleware `authorize:ADM,DSN,TDK,MHS` + `processApproval()` | Route → Ownership (bukan Level) |
| C11 | `ketua_pelaksana_user_id` wajib & harus user valid **(Baru)** | PengajuanService@rules (`required\|exists:m_user,user_id`) | Pengajuan → User |
| C12 | Satu user+posisi hanya satu baris jabatan approval **(Baru)** | Unique index `(user_id, posisi_approval)` di `m_jabatan_approval` | JabatanApproval → User |
| C13 | Status ruangan per-tanggal: manual override > Jadwal aktif > Pengajuan pending > Tersedia **(Baru)** | RuanganService@availabilityStatusForDate | Ruangan → Jadwal/Pengajuan (computed, tidak disimpan) |

---

## 7. Database Schema

### 7.1 Entity Relationship Summary

```
m_level (1) ───< m_user (N) ───< m_admin (1)
                              ├──< m_dosen (1)
                              ├──< m_tendik (1)
                              ├──< m_mahasiswa (1)
                              ├──< t_jadwal (N)
                              ├──< t_pengajuan (N) [sebagai pengaju]
                              ├──< t_pengajuan (N) [sebagai ketua_pelaksana_user_id, Baru]
                              └──< m_jabatan_approval (N) [hasMany, dahulu m_verifikator hasOne]

m_prodi (1) ───< m_admin (N)
            ├──< m_dosen (N)
            ├──< m_mahasiswa (N)
            └──< m_kelas (N)

m_kelas (1) ───< m_mahasiswa (N)

m_ruangan (N) >──< t_jadwal_ruangan >──< t_jadwal (N)
           (N) >──< t_pengajuan_ruangan >──< t_pengajuan (N)
           (Status per-tanggal dihitung dari kedua relasi ini + ruangan_status manual, Baru)

m_organisasi (1) ───< m_jabatan_approval (N) [nullable — Ketua Pelaksana/DPK/Kajur/Wadir2 lintas organisasi]
              (1) ───< t_pengajuan (N)

m_jabatan_approval (1) ───< t_pengajuan_approval (N)  [via jabatan_id, dahulu verifikator_id]

t_pengajuan (1) ───< t_pengajuan_approval (N)  [termasuk tahap 0 Ketua Pelaksana, Baru]

m_formulir (1) ───< t_pengajuan (N)
```

> **m_verifikator DIHAPUS** — digantikan `m_jabatan_approval` dengan relasi `hasMany` ke `m_user` (bukan `hasOne`), dan `organisasi_id` tetap nullable untuk posisi lintas organisasi termasuk Ketua Pelaksana.

### 7.2 Key Tables Detail

| Table | Type | Records Count (Seeder) | Key Fields | Foreign Keys |
|-------|------|----------------------|-----------|-------------|
| m_user | Master | ~15 | user_id, level_id, username, password | level_id → m_level |
| m_level | Master | 4 | level_id, level_kode (ADM/DSN/TDK/MHS — VRF dihapus), level_nama | — |
| m_admin | Master | 2 | admin_id, user_id, admin_nama, prodi_id | user_id → m_user, prodi_id → m_prodi |
| m_dosen | Master | 2 | dosen_id, user_id, dosen_nama, prodi_id | user_id → m_user, prodi_id → m_prodi |
| m_tendik | Master | 1 | tendik_id, user_id, tendik_nama | user_id → m_user |
| m_mahasiswa | Master | 3 | mahasiswa_id, user_id, mahasiswa_nama, prodi_id, kelas_id | user_id, prodi_id, kelas_id |
| m_prodi | Master | 5+ | prodi_id, prodi_kode, prodi_nama | — |
| m_kelas | Master | 10+ | kelas_id, prodi_id, kelas_nama | prodi_id → m_prodi |
| m_ruangan | Master | 5+ | ruangan_id, ruangan_kode, ruangan_nama, ruangan_status, ruangan_kategori | — |
| m_formulir | Master | 1 | formulir_id, formulir_file | — |
| m_organisasi | Master | 2+ | organisasi_id, organisasi_kode, organisasi_nama, organisasi_logo | — |
| m_jabatan_approval (dahulu m_verifikator) | Master | 5+ (+ Ketua Pelaksana on-demand) | jabatan_id, user_id, organisasi_id (nullable), posisi_approval (string bebas), urutan_approval | user_id → m_user, organisasi_id → m_organisasi; **unique(user_id, posisi_approval)** |
| t_jadwal | Transaksi | 5+ | jadwal_id, user_id, jadwal_nama, jadwal_tgl, jadwal_status | user_id → m_user |
| t_jadwal_ruangan | Pivot | — | jadwal_id, ruangan_id | jadwal_id, ruangan_id |
| t_pengajuan | Transaksi | 0+ | pengajuan_id, user_id, organisasi_id, formulir_id, **ketua_pelaksana_user_id (Baru)**, pengajuan_status | user_id, organisasi_id, formulir_id, ketua_pelaksana_user_id → m_user |
| t_pengajuan_ruangan | Pivot | — | pengajuan_id, ruangan_id | pengajuan_id, ruangan_id |
| t_pengajuan_approval | Transaksi | 0+ | approval_id, pengajuan_id, **jabatan_id (dahulu verifikator_id)**, urutan_tahap (0=Ketua Pelaksana Baru, 1-3 lainnya), status_approval, batas_waktu | pengajuan_id, jabatan_id → m_jabatan_approval |

---

## 8. Middleware & Authorization

### 8.1 Middleware Stack

| Priority | Middleware | Group | Fungsi |
|----------|-----------|-------|--------|
| 1 | `TrustProxies` | Global | Proxy trust configuration |
| 2 | `HandleCors` | Global | CORS headers |
| 3 | `PreventRequestsDuringMaintenance` | Global | Maintenance mode |
| 4 | `TrimStrings` | Global | Trim input whitespace |
| 5 | `ConvertEmptyStringsToNull` | Global | Convert empty → null |
| 6 | `EncryptCookies` | web | Cookie encryption |
| 7 | `AddQueuedCookiesToResponse` | web | Cookie queue |
| 8 | `StartSession` | web | Session initialization |
| 9 | `ShareErrorsFromSession` | web | Share errors to views |
| 10 | `VerifyCsrfToken` | web | CSRF protection |
| 11 | `SubstituteBindings` | web/api | Route model binding |
| 12 | `ThrottleRequests` | api | Rate limiting (60/min) |

### 8.2 Custom Middleware

#### `Authenticate` (alias: `auth`)
- Extends Laravel's `Illuminate\Auth\Middleware\Authenticate`
- Redirects unauthenticated users to `/login`
- Supports JSON response via `expectsJson()`

#### `AuthorizeUser` (alias: `authorize`)
- Custom middleware for role-based authorization
- Logic:
  1. Check `Auth::check()` — redirect to login if not authenticated
  2. Get role: `$request->user()->getRole()` → returns `level_kode` string (ADM/DSN/TDK/MHS — VRF dihapus)
  3. Check if role is in the allowed roles array (variadic parameter)
  4. If allowed: `return $next($request)`
  5. If not allowed: `abort(403, 'Forbidden. Insufficient Permissions.')`
- **Catatan redesain:** middleware ini TIDAK LAGI menegakkan otorisasi approval berjenjang (route `/pengajuan/approval/*` sekarang terbuka untuk `ADM,DSN,TDK,MHS`). Otorisasi riil siapa yang boleh memproses tahap tertentu ditegakkan di `PengajuanService::processApproval()` via ownership check (`jabatanApproval.user_id == Auth::id()`), bukan di middleware level.

#### Usage Examples
```php
// Admin only
Route::group(['middleware' => 'authorize:ADM'], function () { ... });

// Multiple roles
Route::group(['middleware' => 'authorize:ADM,DSN,TDK,MHS'], function () { ... });

// Approval berjenjang — accessible ke semua level, otorisasi riil di service layer (dahulu authorize:VRF)
Route::group(['middleware' => 'authorize:ADM,DSN,TDK,MHS'], function () { ... });
```

### 8.3 CSRF Protection
- Global for all web routes (VerifyCsrfToken)
- CSRF token injected via `@csrf` in Blade forms and `meta[name="csrf-token"]`
- AJAX requests must include X-CSRF-TOKEN header or `_token` parameter
- Login form uses jQuery Validate + AJAX submit with serialized form data

---

## 9. Testing Strategy

### 9.1 Test Structure

```
tests/
├── e2e/                              # Playwright E2E tests (331 TC total, single config in tests/e2e/)
│   ├── login.spec.js                 # 23 TC — Authentication
│   ├── security.spec.js
│   ├── modules/                      # testDir target di playwright.config.js — jalankan dari sini
│   │   ├── 01-organisasi-verifikator.spec.js
│   │   ├── 02-status-ruangan-jadwal.spec.js
│   │   ├── 03-approval-berjenjang.spec.js
│   │   ├── pengajuan.spec.js
│   │   ├── jadwal.spec.js
│   │   ├── organisasi.spec.js
│   │   ├── ruangan.spec.js
│   │   ├── ruangan-availability.spec.js          # (Baru) 10 TC — UC-RG-04
│   │   ├── approval-stage0.spec.js               # (Baru) 10 TC — UC-JAB-00 Ketua Pelaksana
│   │   ├── approval-stage1.spec.js
│   │   ├── approval-stage2.spec.js
│   │   ├── approval-stage3.spec.js
│   │   └── approval-parallel.spec.js
│   ├── fixtures/
│   │   ├── auth.fixture.js                    # Login/logout/doAjax helpers, USERS (level VRF terpisah dihapus)
│   │   └── approval.fixture.js                # (Baru) Helper approval berjenjang + Ketua Pelaksana
│   └── playwright.config.js          # testDir:'./modules', baseURL, workers:1 — HANYA terdeteksi jika dijalankan dari tests/e2e/, BUKAN dari root repo
├── Feature/                          # Laravel feature tests
└── Unit/                             # Laravel unit tests
```

> **Catatan penting:** Menjalankan `npx playwright test` dari ROOT repo (bukan dari `tests/e2e/`) TIDAK memuat `tests/e2e/playwright.config.js` — Playwright hanya mencari config di cwd atau direktori ANCESTOR, tidak pernah di direktori child. Akibatnya `baseURL`, `testDir`, dan `workers:1` hilang, dan file legacy di luar `testDir` (`login.spec.js`, `security.spec.js`) ikut ter-include tanpa filter — jalankan selalu dari `tests/e2e/` sebagai working directory.

### 9.2 Test Fixture — `auth.fixture.js` & `approval.fixture.js`
- **USERS**: Object berisi kredensial untuk semua role (ADM, DSN, TDK, MHS) — level VRF terpisah SUDAH DIHAPUS; pemegang jabatan approval (Ketua Umum, DPK, Presiden BEM, Ketua Jurusan, Wakil Direktur II) direpresentasikan sebagai varian `USERS.VRF_KETUA_UMUM` dst. yang login dengan username dosen/mahasiswa ASLI (mis. `2241760003`, `dosen2`)
- **login()**: Navigates to `/login`, fills form, submits via click, waits for AJAX response, then navigates to `/dashboard`
- **logout()**: Navigates to `/logout`
- **doAjax()**: Executes jQuery $.ajax within page context, handles CSRF token, normalizes response body (key: `status` ↔ `success`)
- **Response normalization**: Laravel `ErrorResponse::fromServiceResult()` returns `success` key, while services return `status` key — helper normalizes both so tests can access `result.body.status`
- **`approval.fixture.js` (Baru)**: `resolveApprovalFixtureIds()` (parse dropdown HTML dinamis, termasuk `ketuaPelaksanaUserId`), `createPengajuanForApproval()`, `computeStageApprovalIds()` (anchor di stage 0 Ketua Pelaksana, offset +1 untuk semua tahap lain), `approveKetuaPelaksana()`, `approveAsVerifikator()`, `processApprovalAs()`

### 9.3 Key Test Patterns

| Pattern | Implementation | Benefit |
|---------|---------------|---------|
| Page object via `page.goto()` | Navigate full page | Loads jQuery + CSRF |
| AJAX via `page.evaluate($.ajax)` | Execute jQuery in browser | Sends X-Requested-With header |
| Response wait via `waitForResponse` | Match by URL + method | Accurate timing |
| Selector via CSS | `#id`, `.class`, `button[type="submit"]` | Stable locators |
| Login via UI | Fill form → click → wait | Tests real user flow |
| Data isolation | Timestamp-based unique names | Prevents test collision |
