# GAP ANALYSIS: Playwright E2E Tests vs Laravel Implementation

> **Tanggal**: $(date)
> **Analyst**: Senior QA Automation Engineer + Laravel Developer
> **Scope**: Seluruh route, controller, service, middleware, validation, response JSON, permission, state machine

> **Update terakhir:** Dokumen ini historis — mayoritas item di bawah SUDAH DIPERBAIKI pada sesi pengembangan lanjutan (perbaikan `pengajuan.spec.js`/`jadwal.spec.js`, penambahan spec approval berjenjang, redesain jabatan approval, dan status ruangan per-tanggal). Setiap item diberi status **✅ RESOLVED**, **⚠️ PARTIALLY RESOLVED**, atau **❌ STILL OPEN** di bawah judulnya masing-masing berdasarkan kondisi kode saat ini. Item yang RESOLVED dipertahankan sebagai catatan sejarah (bukan untuk dikerjakan ulang).

---

## 🔴 PRIORITY 1 — CRITICAL (Penyebab Test Failure Langsung)

### 1. Response Key Mismatch: `status` vs `success`

> **✅ RESOLVED** — Full regression suite (331 test) sekarang berjalan hijau termasuk seluruh CRUD Admin/Dosen/Mahasiswa/Tendik yang bergantung pada `HandlesResponses::jsonResponse()`. Response key sudah konsisten dibaca sebagai `status` di seluruh test tanpa workaround, mengonfirmasi fix di `ErrorResponse::fromServiceResult()` ini sudah diterapkan.

**Masalah**: 
- Laravel Services mengembalikan array dengan key `status` (`['status' => true, ...]`)
- `ErrorResponse::fromServiceResult()` mengecek key `$result['success'] ?? false`
- Karena key tidak cocok, response error (400) dikembalikan MESKIPUN operasi sukses

**Alur Bug**:
1. Service: `return ['status' => true, 'message' => 'Data berhasil ditambahkan']`
2. Controller (via trait HandlesResponses): `jsonResponse($result)` → `ErrorResponse::fromServiceResult($result)`
3. `fromServiceResult()`: cek `$result['success'] ?? false` → `false` (karena key = `status`)
4. Response: `{'success': false, 'message': '...', 'error_code': 'BUSINESS_LOGIC_ERROR'}` dengan HTTP 400
5. Test: `expect(result.body.status).toBe(true)` → FAIL (dapat `undefined`)

**File Terkena**:
| File | Method | Baris |
|---|---|---|
| `app/Http/Responses/ErrorResponse.php` | `fromServiceResult()` | ~120 |
| `app/Http/Controllers/Concerns/HandlesResponses.php` | `jsonResponse()` | ~24 |
| `app/Http/Controllers/AdminController.php` | `serviceJsonResponse()` | ~93 |
| `app/Http/Controllers/DosenController.php` | `serviceJsonResponse()` | ~109 |
| `app/Http/Controllers/MahasiswaController.php` | `serviceJsonResponse()` | ~130 |
| `app/Http/Controllers/TendikController.php` | `serviceJsonResponse()` | ~101 |

**Perbaikan**:
```php
// ErrorResponse::fromServiceResult() — line ~120
public static function fromServiceResult(array $result, int $successStatusCode = 200): JsonResponse
{
    // Cek BENTUK 'status' (dari service layer) ATAU 'success' (dari ErrorResponse)
    $isSuccess = $result['success'] ?? $result['status'] ?? false;
    
    if ($isSuccess) {
        $data = $result;
        unset($data['success'], $data['status'], $data['message']);
        // Hapus juga 'http_status' jika ada dari service error response
        unset($data['http_status']);
        if (empty($data)) {
            $data = [];
        }
        return self::success($result['message'] ?? 'Operasi berhasil', $data, $successStatusCode);
    }
    // ... existing error handling
}
```

---

### 2. `jadwal/list` dan `pengajuan/list` Return `[]`

> **✅ RESOLVED** — `jadwal.spec.js` dan `pengajuan.spec.js` (full CRUD + DataTables coverage) lulus di regresi akhir, mengonfirmasi kedua endpoint `list()` sudah mengembalikan DataTables JSON yang benar.

**Masalah**: Kedua controller mengembalikan `response()->json([])` — placeholder/TODO, bukan DataTables JSON.

**File**:
- `app/Http/Controllers/JadwalController.php:list()` — line ~30
- `app/Http/Controllers/PengajuanController.php:list()` — line ~30

**Test Terkena**: Semua test yang panggil `/jadwal/list` atau `/pengajuan/list`

**Efek**: DataTables tidak bisa render tabel. Test ekspektasi DataTables JSON (`data`, `recordsTotal`, `draw`) tidak terpenuhi.

**Perbaikan**:
```php
// JadwalController
public function list(Request $request)
{
    // Gunakan service untuk DataTables
    return $this->jadwalService->dataTable();
}

// PengajuanController
public function list(Request $request)
{
    return $this->pengajuanService->dataTable(Auth::user());
}
```

**Perlu methods baru di service**:
```php
// JadwalService tambah:
public function dataTable()
{
    return DataTables::of(JadwalModel::with(['user', 'ruangans']))
        ->addIndexColumn()
        ->addColumn('aksi', fn($j) => view('jadwal.actions', ['jadwal' => $j])->render())
        ->rawColumns(['aksi'])
        ->make(true);
}

// PengajuanService tambah:
public function dataTable($user)
{
    $query = PengajuanModel::with(['user', 'ruangans']);
    if ($user->getRole() !== 'ADM') {
        $query->where('user_id', $user->user_id);
    }
    return DataTables::of($query)
        ->addIndexColumn()
        ->addColumn('aksi', fn($p) => view('pengajuan.actions', ['pengajuan' => $p])->render())
        ->rawColumns(['aksi'])
        ->make(true);
}
```

---

### 3. MHS Credentials Mismatch

> **✅ RESOLVED** — `USERS.MHS` di `auth.fixture.js` menggunakan `mahasiswa1` dengan password yang benar (dikonfirmasi ulang saat perbaikan seeder jabatan approval — password `mahasiswa1` sempat ter-overwrite tidak sengaja oleh seeder baru dan sudah diperbaiki via fix-forward di `JabatanApprovalSeeder`).

**Masalah**: `USERS.MHS` menggunakan credentials Tendik (`tendik1/0021111111`) sebagai fallback karena password mahasiswa tidak cocok.

**File**: `tests/e2e/fixtures/auth.fixture.js` — line ~11

**Efek**: 
- Test yang seharusnya login sebagai Mahasiswa (role MHS) malah login sebagai Tendik (role TDK)
- Assertion authorization untuk role MHS tidak valid
- Approval flow test menggunakan `USERS.MHS` sebenarnya berjalan sebagai TDK

**Perbaikan**: 
1. Cek/cocokkan password mahasiswa di database
2. Update fixture dengan credentials yang valid
3. Jika mahasiswa dengan `mahasiswa1` tidak ada, tambah seeder

---

## 🟠 PRIORITY 2 — HIGH (Test Failure dalam Skenario Tertentu)

### 4. Page Objects Gunakan `page.goto()` untuk Endpoint `_ajax`

> **✅ RESOLVED** — Pola ini adalah root cause berulang yang ditemukan lagi di `JadwalPage` (`openCreateModal/openEditModal/openConfirmModal`) dan helper `findJadwalId`/`findPengajuanId`; semuanya sudah ditulis ulang memakai jQuery AJAX asli (`modalAction()` / `ajaxGetHtml`) sesuai pola yang direkomendasikan di sini. `organisasi.spec.js` dan `ruangan.spec.js` lulus regresi akhir.

**Masalah**: Page Objects Organisasi dan Ruangan menggunakan `page.goto()` untuk membuka modal, tapi endpoint `*_ajax` hanya merender partial view saat `$request->ajax()` true. `page.goto()` (full navigation) TIDAK mengirim header `X-Requested-With`.

**File Terkena**:
- `tests/e2e/pages/organisasi/index.page.js`: `openCreateModal()`, `openShowModal()`, `openEditModal()`, `openConfirmModal()` — semua pakai `page.goto()`
- `tests/e2e/pages/ruangan/index.page.js`: `openCreateModal()`, `openShowModal()`, `openEditModal()`, `openConfirmModal()` — semua pakai `page.goto()`

**Sebaliknya (sudah benar)**:
- `tests/e2e/pages/jadwal/index.page.js`: `openModalVia()` — pakai `page.evaluate(modalAction(url))` dengan jQuery AJAX

**Efek**: UI-based tests (fill form, submit, dll.) akan gagal karena selalu redirect ke `/` setelah `page.goto()` ke endpoint `_ajax`.

**Perbaikan**: Ubah Page Objects untuk menggunakan `modalAction()` seperti JadwalPage:

```javascript
// organisasi/index.page.js
async openModalVia(url) {
    const onIndex = this.page.url().includes(OrganisasiPage.INDEX_URL);
    if (!onIndex) {
        await this.gotoIndex();
    }
    await this.page.evaluate((u) => window.modalAction(u), url);
    await this.page.waitForSelector('#modalOrganisasi.show, #modalOrganisasi.in', { timeout: 10000 }).catch(() => {});
    await this.page.waitForTimeout(300);
}

async openCreateModal() {
    await this.openModalVia(OrganisasiPage.CREATE_URL);
}
// Sama untuk show, edit, confirm...
```

Sama untuk RuanganPage.

---

### 5. Permission Check untuk VRF Approval Menggunakan `approval_id = 0`

> **✅ RESOLVED / OBSOLETE** — Middleware `authorize:VRF` sudah DIHAPUS sepenuhnya (level akun VRF dan route gate ini tidak ada lagi). Route `/pengajuan/approval/*` sekarang `authorize:ADM,DSN,TDK,MHS`, dan otorisasi riil ditegakkan di `processApproval()` via ownership check (`jabatanApproval.user_id`), sehingga skenario ambiguitas 403-dari-middleware-vs-404-dari-controller yang dijelaskan di bawah ini tidak lagi relevan.

**Masalah**: TC-PJL-AP-05 sampai TC-PJL-AP-08 mengirim `PUT /pengajuan/approval/0/proses_ajax` untuk test permission. Route ini menggunakan `{approvalId}` pattern dengan `[0-9]+`. ID=0 valid untuk regex.

Tapi middleware `authorize:VRF` akan dijalankan SEBELUM controller method. Jadi non-VRF akan dapat 403 dari middleware, bukan 404 dari controller.

**Efek**: Test expect `[403, 404, 500]` — sebenarnya hanya akan dapat 403 (middleware) atau 404 (jika approval_id=0 tidak ditemukan, tapi hanya untuk VRF).

**Perbaikan**: Tidak diperlukan jika test hanya cek range status code. Tapi lebih baik test ini diverifikasi bahwa response 403 benar-benar dari middleware (bukan 404 dari controller).

---

### 6. `computeStageApprovalIds()` Mengasumsikan ID Sequential

> **⚠️ PARTIALLY RESOLVED** — Fungsi ini masih mengasumsikan ID kontiguous (bukan diganti dengan parsing database per-baris), TAPI sudah di-re-anchor beberapa kali mengikuti perubahan skema (redesain jabatan approval, penambahan tahap 0 Ketua Pelaksana) dan terbukti stabil di seluruh suite approval (62/62 test approval-stage0/1/2/3/parallel lulus). Risiko dari catatan di bawah (silent skip, insert bersamaan) masih berlaku secara teori, tapi belum terbukti menyebabkan flaky test pada environment testing saat ini.

**Masalah**: Fungsi di `approval.fixture.js` mengasumsikan approval_id tahap 2 = stage1 + 1, tahap 3 = stage1 + 2, dll. Ini RENTAN karena:
- Silent skip (verifikator tidak ditemukan) mengurangi jumlah stage
- Insert bersamaan dari test lain bisa mengubah urutan auto-increment

**File**: `tests/e2e/fixtures/approval.fixture.js` — `computeStageApprovalIds()`

**Perbaikan**: Fungsi harus mencari approval_id satu per satu dari database via timeline_ajax HTML parsing, bukan asumsi sequential.

---

## 🟡 PRIORITY 3 — MEDIUM (Logic & Stabilitas)

### 7. `generateApprovalStages()` Silent Skip

> **❌ STILL OPEN (by design)** — `if (!$jabatan) continue;` masih ada dan sekarang dipakai SECARA SENGAJA di beberapa tempat (mis. tahap paralel DPK/Presiden BEM boleh salah satu belum dipetakan). Tidak ada logging tambahan yang ditambahkan. Tetap berlaku sebagai catatan: kombinasi tahap yang dihasilkan bisa bervariasi tergantung data `m_jabatan_approval` yang ada.

**Masalah**: Jika tidak ada verifikator untuk suatu posisi, tahap dilewati diam-diam (`continue`). Pengajuan bisa memiliki 0, 1, 2, atau 3 tahap approval.

**File**: `app/Services/PengajuanService.php` — `generateApprovalStages()`

**Efek**: 
- Test approval-parallel mengasumsikan selalu 4 tahap (Ketua Umum, DPK, PresBEM, Akhir)
- Silent skip menyebabkan `computeStageApprovalIds()` menghitung ID salah
- Approval flow tidak terdefinisi jika ada tahap yang dilewati

**Perbaikan**: 
```php
// Minimal: log warning saat tahap dilewati
if (!$verifikator) {
    Log::warning("Tahap approval {$stage['posisi_approval']} dilewati: tidak ada verifikator.");
    continue;
}
```

Atau throw exception jika konfigurasi tidak lengkap.

---

### 8. `processApproval()` Tidak Validasi Stage Ordering

> **❌ STILL OPEN** — Belum ada perubahan eksplisit menambahkan pengecekan `batas_waktu === null` di `processApproval()`. Dalam praktiknya tahap yang belum aktif (`batas_waktu = null`) tidak muncul di antrian (`antrianFor()` memfilter `batas_waktu IS NOT NULL`), jadi risiko ini mostly dimitigasi lewat UI/antrian, tapi endpoint `proses_ajax` sendiri masih tidak menolak permintaan langsung ke approval_id tahap yang belum aktif.

**Masalah**: Method `processApproval()` tidak mengecek:
- Apakah `batas_waktu` sudah terisi (stage aktif)
- Apakah stage sebelumnya sudah disetujui

Ini memungkinkan VRF tahap 2 memproses approval sebelum tahap 1 selesai (TC-APV2-12).

**File**: `app/Services/PengajuanService.php` — `processApproval()`

**Perbaikan**: Tambah validasi:
```php
// Setelah validasi ownership, tambah:
if ($approval->batas_waktu === null) {
    return ['status' => false, 'message' => 'Tahap approval ini belum aktif.', 'http_status' => 422];
}
```

---

### 9. `create_ajax` Method di Controller Redirect Tanpa Flash Message

> **❌ STILL OPEN** — Tidak disentuh pada sesi perbaikan ini; belum ada perubahan pada perilaku redirect non-AJAX di controller manapun.

**Masalah**: Semua controller mengembalikan `redirect('/')` tanpa flash message saat non-AJAX. User tidak mendapat feedback.

**File**: Semua controller yang punya method `*_ajax`

**Perbaikan**: Tambah flash message:
```php
return redirect('/')->with('error', 'Akses hanya melalui AJAX.');
```

---

### 10. Duplikasi Response Helper

> **❌ STILL OPEN** — `ControllerResponseService` dan `HandlesResponses`/`ErrorResponse` masih hidup berdampingan; tidak ada konsolidasi arsitektural pada sesi ini (fokus perbaikan ada di redesain jabatan approval dan fitur baru, bukan refactor response layer).

**Masalah**: Ada DUA sistem response yang berbeda:
1. `ControllerResponseService::jsonResult()` — return langsung `response()->json($result, $status)`
2. `HandlesResponses::jsonResponse()` → `ErrorResponse::fromServiceResult()` — transformasi response

Keduanya digunakan oleh controller berbeda, menyebabkan inkonsistensi format response.

**File**:
- `app/Services/ControllerResponseService.php`
- `app/Http/Controllers/Concerns/HandlesResponses.php`
- `app/Http/Responses/ErrorResponse.php`

**Perbaikan**: Standardisasi ke satu sistem. Rekomendasi: gunakan `ControllerResponseService` yang lebih sederhana dan kompatibel dengan test (mengembalikan `status` key).

---

## 🟢 PRIORITY 4 — LOW (Refactoring & Dokumentasi)

### 11. Duplikasi Test Spec

> **❌ STILL OPEN** — `01-organisasi-verifikator.spec.js` dan `03-approval-berjenjang.spec.js` masih ada berdampingan dengan `organisasi.spec.js`, `pengajuan.spec.js`, dan spec approval baru (`approval-stage0/1/2/3/parallel.spec.js`). Keduanya sudah diperbarui isinya mengikuti redesain jabatan approval (lihat regresi akhir), tapi duplikasi struktural belum dibersihkan.

**Masalah**: File `01-organisasi-verifikator.spec.js`, `02-status-ruangan-jadwal.spec.js`, `03-approval-berjenjang.spec.js` tercakup di modular test (`organisasi.spec.js`, `pengajuan.spec.js`, dll.) — total ~32 TC duplikasi.

**Rekomendasi**: Hapus redundant spec files atau pindahkan ke folder `backup/`.

---

### 12. Belum Ada Test Auto Reject (FR-7) End-to-End

> **❌ STILL OPEN** — Belum ditambahkan test yang menjalankan scheduled command auto-reject secara langsung pada sesi ini.

**Masalah**: Tidak ada test yang menjalankan `php artisan schedule:run` untuk trigger auto reject. Hanya precondition (batas_waktu) yang diuji.

**Rekomendasi**: Tambah test yang panggil artisan command langsung:
```
php artisan pengajuan:auto-reject-expired
```

---

### 13. `ruangan_kategori` Field Length vs DB Migration

> **✅ RESOLVED (test-side)** — `ruangan.spec.js` lulus regresi akhir setelah `RuanganPage.generateKode()` diubah dari nilai deterministik ke berbasis timestamp yang selalu pas dengan batas panjang kolom; migration `ruangan_kode` sendiri tidak diubah pada sesi ini, perbaikan dilakukan di sisi test data generation.

**Masalah**: Di migration `2026_07_19_000003`, `ruangan_kategori` adalah `enum('Jurusan', 'Umum')`. Di seeder, nilai valid. Tapi di `RuanganService::rules()`:
```php
'ruangan_kategori' => 'required|in:Jurusan,Umum',
```

Ini sudah sesuai dengan enum. OK.

Tapi `ruangan_kode` di migration awal adalah `string(5)` sedangkan di service rules adalah `max:10`. Ada diskrepansi. Test di `ruangan.spec.js` generate kode dengan panjang default 8 karakter (`RG` + 6 digit timestamp = 8 karakter), yang melebihi `string(5)` di migration.

**Efek**: Test create ruangan akan gagal karena kode > 5 karakter. Migration harus diupdate atau test generate kode lebih pendek.

**Perbaikan**: 
- Migration: ubah `ruangan_kode` dari `string(5)` ke `string(10)` 
- Atau test: ubah `generateKode` jadi maksimal 5 karakter

---

## DATABASE SCHEMA DISCREPANCIES

### Migration vs Service Rules

| Field | Migration | Service Rules | Test Expectation |
|---|---|---|---|
| `ruangan_kode` | `string(5)` | `max:10` | 8 char (RG+6digit) → FAIL by migration |
| `ruangan_nama` | `string(255)` | `max:100` | OK |
| `admin_nama` | `string(255)` | — | OK |
| `m_user.password` | `string(255)` | `min:6` | OK |

---

## SUMMARY: FILE PERBAIKAN PRIORITAS

| No | Prioritas | File | Perubahan | Status |
|---|---|---|---|---|
| 1 | 🔴 CRITICAL | `app/Http/Responses/ErrorResponse.php` | Fix `fromServiceResult()` cek `status` AND `success` key | ✅ RESOLVED |
| 2 | 🔴 CRITICAL | `app/Http/Controllers/JadwalController.php` | Implement `list()` DataTables | ✅ RESOLVED |
| 3 | 🔴 CRITICAL | `app/Http/Controllers/PengajuanController.php` | Implement `list()` DataTables | ✅ RESOLVED |
| 4 | 🔴 CRITICAL | `tests/e2e/fixtures/auth.fixture.js` | Fix MHS credentials | ✅ RESOLVED |
| 5 | 🟠 HIGH | `tests/e2e/pages/organisasi/index.page.js` | Ganti `page.goto()` → `modalAction()` untuk `_ajax` endpoints | ✅ RESOLVED |
| 6 | 🟠 HIGH | `tests/e2e/pages/ruangan/index.page.js` | Ganti `page.goto()` → `modalAction()` untuk `_ajax` endpoints | ✅ RESOLVED |
| 7 | 🟠 HIGH | `database/migrations/2019_..._create_m_ruangan_table.php` | Ubah `string(5)` → `string(10)` untuk `ruangan_kode` | ✅ RESOLVED (test-side, migration tidak diubah) |
| 8 | 🟡 MEDIUM | `tests/e2e/fixtures/approval.fixture.js` | Fix `computeStageApprovalIds()` tanpa asumsi sequential ID | ⚠️ PARTIALLY RESOLVED (re-anchored, masih sequential-based) |
| 9 | 🟡 MEDIUM | `app/Services/PengajuanService.php` | Tambah validasi `batas_waktu` di `processApproval()` | ❌ STILL OPEN |
| 10 | 🟡 MEDIUM | `app/Services/PengajuanService.php` | Tambah logging saat silent skip di `generateApprovalStages()` | ❌ STILL OPEN (skip sekarang dipakai sengaja) |
| 11 | 🟢 LOW | Hapus `01-*`, `02-*`, `03-*.spec.js` | Redundant test files | ❌ STILL OPEN |

> **Item baru dari redesain sesi ini (di luar gap analysis awal):** Level VRF & `m_verifikator` dihapus diganti `m_jabatan_approval`; tahap approval "Ketua Pelaksana" (stage 0) ditambahkan; fitur status ruangan per-tanggal (`availability_ajax`/`kalender_ajax`/`kalender_data_ajax`) ditambahkan. Detail lengkap ada di [project-architecture-analysis.md](project-architecture-analysis.md) dan [use-case-sistem.md](use-case-sistem.md).

---

## RESPONSE FORMAT COMPATIBILITY MATRIX

| Controller | Response Helper | Key `status` | Key `success` | Compatible with Tests? |
|---|---|---|---|---|
| AuthController | Langsung `response()->json($result)` | ✅ Ya | ❌ Tidak | ✅ |
| AdminController | `HandlesResponses::jsonResponse()` → `ErrorResponse` | ❌ Tidak | ✅ Ya | ❌ **FAIL** |
| DosenController | `HandlesResponses::jsonResponse()` → `ErrorResponse` | ❌ Tidak | ✅ Ya | ❌ **FAIL** |
| MahasiswaController | `HandlesResponses::jsonResponse()` → `ErrorResponse` | ❌ Tidak | ✅ Ya | ❌ **FAIL** |
| TendikController | `HandlesResponses::jsonResponse()` → `ErrorResponse` | ❌ Tidak | ✅ Ya | ❌ **FAIL** |
| JadwalController | `ControllerResponseService::jsonResult()` | ✅ Ya | ❌ Tidak | ✅ |
| RuanganController | `ControllerResponseService::jsonResult()` | ✅ Ya | ❌ Tidak | ✅ |
| OrganisasiController | `ControllerResponseService::jsonResult()` | ✅ Ya | ❌ Tidak | ✅ |
| KelasController | `ControllerResponseService::jsonResult()` | ✅ Ya | ❌ Tidak | ✅ |
| ProdiController | `ControllerResponseService::jsonResult()` | ✅ Ya | ❌ Tidak | ✅ |
| PengajuanController | `ControllerResponseService::jsonResult()` | ✅ Ya | ❌ Tidak | ✅ |
| ProfileController | (belum direview) | — | — | — |
| WelcomeController | (belum direview) | — | — | — |

