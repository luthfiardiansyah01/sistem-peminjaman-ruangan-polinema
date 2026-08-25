# Rancangan Struktur Project Playwright — SPR JTI

> **Proyek:** Sistem Peminjaman Ruangan: PLP JTI  
> **Role:** Playwright Automation Engineer  
> **Base:** Existing setup di `tests/e2e/`  
> **Framework:** Playwright 1.x (JavaScript)  
> **Pola:** Page Object Model (POM) + Fixtures + Data-Driven  

> **Update terakhir:** Dokumen ini adalah rancangan/target struktur (sebagian aspirational). Implementasi aktual saat ini berbeda dari rancangan penomoran modul di bawah — spec file nyata ada di `tests/e2e/modules/`: `pengajuan.spec.js`, `jadwal.spec.js`, `approval-stage0.spec.js`, `approval-stage1.spec.js`, `approval-stage2.spec.js`, `approval-stage3.spec.js`, `approval-parallel.spec.js`, `ruangan-availability.spec.js`, `01-organisasi-verifikator.spec.js`, `03-approval-berjenjang.spec.js`, `organisasi.spec.js`, `ruangan.spec.js`, `login.spec.js`, dll. Fixture nyata: `fixtures/auth.fixture.js` (USERS dengan username asli, level VRF terpisah sudah dihapus) dan `fixtures/approval.fixture.js` (helper untuk approval berjenjang + Ketua Pelaksana). Bagian di bawah tetap dipertahankan sebagai referensi desain/konvensi umum, dengan istilah "Verifikator/VRF" dibaca sebagai "pemegang jabatan approval (dosen/mahasiswa dengan baris `m_jabatan_approval`)".

---

## 1. STRUKTUR FOLDER

```
tests/e2e/
├── playwright.config.js              # Konfigurasi utama Playwright (existing)
├── .env                              # Environment variables (baseURL, credentials)
├── .env.example                      # Template env untuk developer lain
│
├── fixtures/                         # Data fixtures dan shared setup
│   ├── auth.fixture.js               # Login/logout + USERS (existing)
│   ├── data.fixture.js               # Data testing untuk semua modul
│   └── index.js                      # Re-export semua fixtures
│
├── helpers/                          # Utility functions
│   ├── api.helper.js                 # AJAX/API calls via jQuery
│   ├── ui.helper.js                  # Interaksi UI (modal, sidebar, dll)
│   ├── assert.helper.js              # Custom assertions
│   ├── wait.helper.js                # Wait strategies
│   └── index.js                      # Re-export semua helpers
│
├── pages/                            # Page Object Models
│   ├── login.page.js                 # Halaman login
│   ├── dashboard.page.js             # Dashboard (role-aware)
│   ├── profile.page.js               # Profile management
│   ├── admin/
│   │   ├── index.page.js             # Daftar admin (DataTables)
│   │   ├── create.page.js            # Create admin modal
│   │   ├── edit.page.js              # Edit admin modal
│   │   └── import.page.js            # Import excel admin
│   ├── dosen/
│   │   ├── index.page.js             # Daftar dosen
│   │   ├── create.page.js            # Create dosen modal
│   │   ├── edit.page.js              # Edit dosen modal
│   │   └── import.page.js            # Import excel dosen
│   ├── tendik/
│   │   ├── index.page.js             # Daftar tendik
│   │   ├── create.page.js            # Create tendik modal
│   │   ├── edit.page.js              # Edit tendik modal
│   │   └── import.page.js            # Import excel tendik
│   ├── mahasiswa/
│   │   ├── index.page.js             # Daftar mahasiswa
│   │   ├── create.page.js            # Create mahasiswa modal
│   │   ├── edit.page.js              # Edit mahasiswa modal
│   │   ├── import.page.js            # Import excel mahasiswa
│   │   └── getkelas.page.js          # Get kelas by prodi (cascading)
│   ├── prodi/
│   │   ├── index.page.js             # Daftar prodi
│   │   ├── create.page.js            # Create prodi modal
│   │   └── edit.page.js              # Edit prodi modal
│   ├── kelas/
│   │   ├── index.page.js             # Daftar kelas
│   │   ├── create.page.js            # Create kelas modal
│   │   └── edit.page.js              # Edit kelas modal
│   ├── ruangan/
│   │   ├── index.page.js             # Daftar ruangan
│   │   ├── create.page.js            # Create ruangan modal
│   │   ├── edit.page.js              # Edit ruangan modal
│   │   └── kategori.page.js          # Kategori Jurusan/Umum
│   ├── organisasi/
│   │   ├── index.page.js             # Daftar organisasi
│   │   ├── create.page.js            # Create organisasi modal
│   │   ├── edit.page.js              # Edit organisasi modal
│   │   ├── verifikator.page.js       # Pemetaan verifikator
│   │   └── show.page.js              # Detail organisasi
│   ├── jadwal/
│   │   ├── index.page.js             # Daftar jadwal
│   │   ├── create.page.js            # Create jadwal modal
│   │   ├── edit.page.js              # Edit jadwal modal
│   │   ├── status.page.js            # Update status jadwal
│   │   └── kelas-by-prodi.page.js    # Get kelas by prodi
│   ├── pengajuan/
│   │   ├── index.page.js             # Daftar pengajuan (role-aware)
│   │   ├── create.page.js            # Create pengajuan modal
│   │   ├── show.page.js              # Detail pengajuan
│   │   ├── timeline.page.js          # Timeline approval
│   │   ├── verifikasi.page.js        # Verifikasi admin (terima/tolak)
│   │   ├── cetak-surat.page.js       # Cetak surat PDF
│   │   └── antrian.page.js           # Antrian approval (VRF)
│   ├── approval/
│   │   ├── antrian.page.js           # Antrian approval verifikator
│   │   └── proses.page.js            # Proses setuju/tolak approval
│   ├── formulir/
│   │   └── upload.page.js            # Upload/download formulir
│   └── components/                   # Shared components
│       ├── datatables.component.js   # Interaksi umum DataTables
│       ├── modal.component.js        # Interaksi umum modal AJAX
│       ├── sweetalert.component.js   # Interaksi SweetAlert2
│       ├── sidebar.component.js      # Navigasi sidebar AdminLTE
│       └── toast.component.js        # Notifikasi toast
│
├── modules/                          # Test suites (per modul bisnis)
│   ├── 01-auth.spec.js               # Login, Logout, Profile
│   ├── 02-master-admin.spec.js       # CRUD + Import Admin
│   ├── 03-master-dosen.spec.js       # CRUD + Import Dosen
│   ├── 04-master-tendik.spec.js      # CRUD + Import Tendik
│   ├── 05-master-mahasiswa.spec.js   # CRUD + Import Mahasiswa
│   ├── 06-master-prodi.spec.js       # CRUD Prodi
│   ├── 07-master-kelas.spec.js       # CRUD Kelas
│   ├── 08-ruangan.spec.js            # CRUD + Kategori + Sinkronisasi
│   ├── 09-organisasi.spec.js         # CRUD + Pemetaan Verifikator
│   ├── 10-verifikator.spec.js        # Antrian + Proses Approval
│   ├── 11-pengajuan.spec.js          # Pengajuan + Verifikasi + Timeline + Surat
│   ├── 12-approval-berjenjang.spec.js# Approval 4 tahap + Paralel
│   ├── 13-auto-reject.spec.js        # Auto reject scheduler
│   ├── 14-jadwal.spec.js             # CRUD + Status + Filter
│   ├── 15-formulir.spec.js           # Upload / Download PDF
│   ├── 16-dashboard.spec.js          # Dashboard role-aware
│   ├── 17-permission-test.spec.js    # Negative: permission matrix
│   ├── 18-boundary-test.spec.js      # Boundary value analysis
│   └── 19-data-integrity.spec.js     # Data integrity + cascade delete
│
├── data/                             # Data files for testing
│   ├── import-dosen-valid.xlsx       # File Excel valid untuk import dosen
│   ├── import-mahasiswa-valid.xlsx   # File Excel valid untuk import mahasiswa
│   ├── import-tendik-valid.xlsx      # File Excel valid untuk import tendik
│   ├── import-duplikat.xlsx          # File Excel dengan data duplikat
│   ├── import-kosong.xlsx            # File Excel tanpa data
│   ├── formulir-test.pdf             # File PDF testing untuk upload
│   ├── formulir-invalid.txt          # File non-PDF untuk testing validasi
│   └── generated/                    # File generated saat runtime
│       └── .gitkeep
│
├── reports/                          # Test reports (auto-generated)
│   └── .gitkeep
│
└── utils/                            # Utility functions
    ├── database.js                   # DB helpers (seeder, cleanup)
    ├── file-generator.js             # Generate file testing
    ├── random-data.js                # Generate random data untuk testing
    └── timestamp.js                  # Timestamp utilities
```

---

## 2. FIXTURE DESIGN

### 2.1 `fixtures/auth.fixture.js` (Existing — Rekomendasi Perbaikan)

```javascript
/**
 * Auth fixture untuk Playwright E2E tests
 * Strategy: Login via UI form submit (jQuery AJAX)
 * Session cookie otomatis tersimpan di browser context Playwright
 */

// Data user testing — dipisahkan dari logic
const USERS = {
  ADM: { 
    username: 'admin1', 
    password: '0000000000', 
    role: 'ADM',
    displayName: 'Admin JTI' 
  },
  DSN: { 
    username: 'dosen', 
    password: '1111111111', 
    role: 'DSN',
    displayName: 'Dr. Andi' 
  },
  TDK: { 
    username: 'tendik1', 
    password: '0021111111', 
    role: 'TDK',
    displayName: 'Siti Rahayu' 
  },
  MHS: { 
    username: 'mahasiswa1', 
    password: '1111111111', 
    role: 'MHS',
    displayName: 'Budi Santoso' 
  },
  // Pemegang jabatan approval — SEMUA login sebagai DSN/MHS biasa (level VRF terpisah sudah dihapus),
  // jabatan approval hanya baris tambahan di m_jabatan_approval, bukan level akun.
  VRF_KETUA_UMUM: {
    username: '2241760003',
    password: '123456',
    role: 'MHS',
    displayName: 'Ketua Umum HMJ'
  },
  VRF_DPK: {
    username: 'dosen2',
    password: '123456',
    role: 'DSN',
    displayName: 'DPK'
  },
  VRF_PRESIDEN_BEM: {
    username: '2241760063',
    password: '123456',
    role: 'MHS',
    displayName: 'Presiden BEM'
  },
  VRF_KETUA_JURUSAN: {
    username: '0013333333',
    password: '123456',
    role: 'DSN',
    displayName: 'Ketua Jurusan TI'
  },
  VRF_WADIR_2: {
    username: '0014444444',
    password: '123456',
    role: 'DSN',
    displayName: 'Wakil Direktur II'
  },
};

const BASE_URL = process.env.BASE_URL || 'http://127.0.0.1:8000';

/**
 * Login via UI form submit
 * @param {import('@playwright/test').Page} page
 * @param {string} username
 * @param {string} password
 */
async function login(page, username, password) {
  await page.goto(BASE_URL + '/login', { waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {});
  await page.waitForSelector('#username', { timeout: 10000 });
  await page.waitForTimeout(500);

  await page.fill('#username', username);
  await page.fill('#password', password);

  const respPromise = page.waitForResponse(r => 
    r.url().includes('/login') && r.request().method() === 'POST' && r.status() === 200,
    { timeout: 15000 }
  );

  await page.click('button[type="submit"]');
  const resp = await respPromise;
  const data = await resp.json();

  if (!data.status) {
    throw new Error(`Login gagal untuk ${username}: ${data.message}`);
  }

  await page.goto(BASE_URL + '/dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {});
  await page.waitForTimeout(1000);
}

/**
 * Logout
 * @param {import('@playwright/test').Page} page
 */
async function logout(page) {
  try {
    await page.goto(BASE_URL + '/logout', { waitUntil: 'domcontentloaded', timeout: 10000 });
    await page.waitForTimeout(500);
  } catch (e) {
    // Logout failure is acceptable in cleanup
  }
}

export { USERS, login, logout, BASE_URL };
```

### 2.2 `fixtures/data.fixture.js` (Baru)

```javascript
/**
 * Data fixtures untuk testing — berisi data valid, invalid, boundary
 * Berdasarkan analisis source code (Controller, Service, Model, Migration)
 */

// Data VALID untuk CRUD
const VALID_DATA = {
  admin: {
    username: 'admin_test_' + Date.now(),
    password: 'password123',
    admin_nama: 'Test Admin ' + Date.now(),
    admin_nidn: '1234567890',
    prodi_id: 1,
    admin_noHp: '08123456789',
  },
  dosen: {
    username: 'dosen_test_' + Date.now(),
    password: 'password123',
    dosen_nama: 'Test Dosen ' + Date.now(),
    dosen_nidn: '1234567890',
    prodi_id: 1,
    dosen_noHp: '08123456789',
  },
  tendik: {
    username: 'tendik_test_' + Date.now(),
    password: 'password123',
    tendik_nama: 'Test Tendik ' + Date.now(),
    tendik_nidn: '1234567890',
    tendik_noHp: '08123456789',
  },
  mahasiswa: {
    username: 'mhs_test_' + Date.now(),
    password: 'password123',
    mahasiswa_nama: 'Test Mahasiswa ' + Date.now(),
    mahasiswa_nim: '12345678',
    prodi_id: 1,
    kelas_id: 1,
    mahasiswa_noHp: '08123456789',
  },
  prodi: {
    prodi_kode: 'TST-' + Date.now().toString().slice(-4),
    prodi_nama: 'Test Prodi ' + Date.now(),
  },
  kelas: {
    prodi_id: 1,
    kelas_nama: 'TEST-' + Date.now().toString().slice(-4),
  },
  ruangan: {
    ruangan_kode: 'TEST-' + Date.now().toString().slice(-6),
    ruangan_nama: 'Test Ruangan ' + Date.now(),
    ruangan_fasilitas: 'Meja, Kursi, AC',
    ruangan_kuota: 30,
    ruangan_status: 'Tersedia',
    ruangan_kategori: 'Jurusan',
  },
  organisasi: {
    organisasi_kode: 'TEST-' + Date.now().toString().slice(-4),
    organisasi_nama: 'Test Organisasi ' + Date.now(),
    organisasi_logo: '',
  },
  pengajuan: {
    pengajuan_nama: 'Test Kegiatan ' + Date.now(),
    pengajuan_tgl: '2026-12-01',
    pengajuan_jam_mulai: '09:00',
    pengajuan_jam_selesai: '12:00',
    pengajuan_jumPes: 20,
    pengajuan_keterangan: 'Test keterangan',
    organisasi_id: 1,
    ketua_pelaksana_user_id: 2, // wajib diisi — tahap 0 (Ketua Pelaksana) approval
    ruangan_ids: [1],
  },
  jadwal: {
    jadwal_nama: 'Test Jadwal ' + Date.now(),
    jadwal_tgl: '2026-12-15',
    jadwal_jam_mulai: '08:00',
    jadwal_jam_selesai: '16:00',
    jadwal_jumPes: 50,
    jadwal_status: 'Akan Datang',
  },
};

// Data INVALID untuk negative testing
const INVALID_DATA = {
  // Invalid karena field required kosong
  emptyFields: {
    username: '',
    password: '',
    nama: '',
    kode: '',
  },
  // Invalid karena format salah
  invalidFormat: {
    email: 'bukan-email',
    tanggal: 'bukan-tanggal',
    jam: '25:00',         // Format H:i, jam > 23
    jamSelesaiSebelumMulai: { jam_mulai: '14:00', jam_selesai: '08:00' },
    jumlahPesertaNol: 0,
    jumlahPesertaNegatif: -1,
  },
  // Invalid karena tidak exist di database
  notExistsId: 99999,
  notExistsProdiId: 999,
  notExistsOrganisasiId: 999,
  notExistsKelasId: 999,
  notExistsRuanganId: 999,
  notExistsApprovalId: 99999,
  // Invalid role untuk permission test
  invalidStatus: {
    ruanganStatus: 'Busy',           // Bukan Tersedia/Diajukan/Tidak Tersedia
    jadwalStatus: 'Tidak Ada',       // Bukan state valid
    kategoriRuangan: 'Invalid',      // Bukan Jurusan/Umum
    statusApproval: 'Mungkin',       // Bukan Disetujui/Ditolak
  },
};

// Data BOUNDARY
const BOUNDARY_DATA = {
  username: {
    minValid: 'abc',                    // 3 karakter
    minInvalid: 'ab',                   // 2 karakter
    max255: 'a'.repeat(255),           // 255 karakter
    max256: 'a'.repeat(256),           // 256 karakter (melebihi)
  },
  password: {
    minValid: 'abcde',                  // 5 karakter
    minInvalid: 'abcd',                 // 4 karakter
  },
  nim: {
    min8: '12345678',                   // 8 digit
    min7: '1234567',                    // 7 digit (invalid)
    max12: '123456789012',             // 12 digit
    max13: '1234567890123',            // 13 digit (invalid)
  },
  nidn: {
    min10: '1234567890',               // 10 digit
    max16: '1234567890123456',         // 16 digit
  },
  jumlahPeserta: {
    min1: 1,
    min0: 0,                           // invalid
  },
  kuota: {
    min1: 1,
    min0: 0,                           // invalid
  },
  namaKegiatan: {
    max255: 'K'.repeat(255),
    max256: 'K'.repeat(256),           // invalid
  },
  batasWaktu: {
    expired: '2020-01-01',             // Sudah lewat untuk auto reject test
    future: '2099-12-31',              // Masih jauh
  },
};

// Data DUPLICATE — menggunakan kode yang sudah ada di seeder
const DUPLICATE_DATA = {
  username: 'admin1',                   // User dari seeder (admin1 / 0000000000)
  prodiKode: 'TI',
  ruanganKode: 'R101',
  organisasiKode: 'HMTI',
  kelasNama: 'TI-1A',
};

export { VALID_DATA, INVALID_DATA, BOUNDARY_DATA, DUPLICATE_DATA };
```

### 2.3 `fixtures/index.js` (Baru)

```javascript
/**
 * Central export untuk semua fixtures
 */
export { USERS, login, logout, BASE_URL } from './auth.fixture.js';
export { VALID_DATA, INVALID_DATA, BOUNDARY_DATA, DUPLICATE_DATA } from './data.fixture.js';
```

---

## 3. HELPER DESIGN

### 3.1 `helpers/ui.helper.js` — Interaksi UI (Refactor dari existing helpers.js)

```javascript
/**
 * UI Helper — Interaksi dengan komponen AdminLTE + AJAX modals
 */

/**
 * Navigasi ke halaman via sidebar atau URL langsung
 * @param {import('@playwright/test').Page} page
 * @param {string} menuText - Teks menu (untuk fallback klik sidebar)
 * @param {string} [directUrl] - URL langsung (optional)
 */
async function navigateTo(page, menuText, directUrl = null) {
  const menuMap = {
    'Dashboard': '/dashboard',
    'Daftar Jadwal': '/jadwal',
    'Daftar Ruangan': '/ruangan',
    'Daftar Organisasi': '/organisasi',
    'Pengajuan Peminjaman': '/pengajuan',
    'Antrian Approval': '/pengajuan/approval/antrian_ajax',
    'Daftar Admin': '/admin',
    'Daftar Dosen': '/dosen',
    'Daftar Tendik': '/tendik',
    'Daftar Mahasiswa': '/mahasiswa',
    'Daftar Program Studi': '/prodi',
    'Daftar Kelas': '/kelas',
    'Profil Saya': '/profile/show_ajax',
  };

  const url = directUrl || menuMap[menuText];
  if (url) {
    await page.goto(url, { waitUntil: 'networkidle', timeout: 15000 });
    await page.waitForTimeout(1000);
  } else {
    // Fallback: klik sidebar link
    const link = page.locator(`.nav-sidebar a:has-text("${menuText}")`);
    if (await link.isVisible()) {
      await link.click();
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(1000);
    }
  }
}

/**
 * Buka modal AJAX
 * @param {import('@playwright/test').Page} page
 * @param {string} buttonSelector - Selector tombol
 */
async function openModal(page, buttonSelector) {
  const btn = page.locator(buttonSelector).first();
  await btn.waitFor({ state: 'visible', timeout: 10000 });
  await btn.click();
  await page.waitForSelector('.modal.fade.show', { timeout: 10000 });
  await page.waitForTimeout(800);
}

/**
 * Isi field dalam modal
 * @param {import('@playwright/test').Page} page
 * @param {string} fieldName - name attribute
 * @param {string} value
 */
async function fillField(page, fieldName, value) {
  const field = page.locator(`.modal.fade.show [name="${fieldName}"]`);
  await field.waitFor({ state: 'visible', timeout: 5000 });
  await field.fill('');
  await field.fill(String(value));
}

/**
 * Pilih option dalam select dalam modal
 * @param {import('@playwright/test').Page} page
 * @param {string} selectName - name attribute
 * @param {string|string[]} value - value atau array untuk multi-select
 */
async function selectOption(page, selectName, value) {
  const select = page.locator(`.modal.fade.show select[name="${selectName}"]`);
  await select.waitFor({ state: 'visible', timeout: 5000 });
  await select.selectOption(value);
}

/**
 * Submit form modal
 * @param {import('@playwright/test').Page} page
 * @param {string} formSelector - Selector form (default: '#form-tambah')
 */
async function submitForm(page, formSelector = '#form-tambah') {
  const form = page.locator(`.modal.fade.show ${formSelector}`);
  const submitBtn = form.locator('button[type="submit"]').last();
  await submitBtn.waitFor({ state: 'visible', timeout: 5000 });
  await submitBtn.click();
  await page.waitForTimeout(1000);
}

/**
 * Handle SweetAlert2 popup
 * @param {import('@playwright/test').Page} page
 * @param {string} buttonSelector - Selector tombol (default: .swal2-confirm)
 */
async function handleSwal(page, buttonSelector = '.swal2-confirm') {
  try {
    await page.waitForSelector('.swal2-popup', { timeout: 8000 });
    await page.waitForTimeout(800);
    const confirmBtn = page.locator(buttonSelector).last();
    await confirmBtn.click();
    await page.waitForTimeout(500);
  } catch (e) {
    // SweetAlert mungkin tidak muncul — skip
  }
}

export { navigateTo, openModal, fillField, selectOption, submitForm, handleSwal };
```

### 3.2 `helpers/api.helper.js` — AJAX/API Calls (Refactor dari auth.fixture.js doAjax)

```javascript
/**
 * API Helper — AJAX calls via jQuery $.ajax dengan CSRF token
 */

/**
 * Navigasi ke halaman yang memiliki jQuery + CSRF token
 * @param {import('@playwright/test').Page} page
 */
async function initJqueryContext(page) {
  await page.goto('/dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {});
  await page.waitForTimeout(1000);
}

/**
 * Check apakah halaman sudah punya jQuery context
 * @param {import('@playwright/test').Page} page
 * @returns {Promise<boolean>}
 */
async function hasJqueryContext(page) {
  return await page.evaluate(() => {
    return typeof $ !== 'undefined' && 
           typeof $.ajax !== 'undefined' && 
           document.querySelector('meta[name="csrf-token"]') !== null;
  }).catch(() => false);
}

/**
 * AJAX POST via jQuery — untuk Laravel CSRF protected endpoints
 * @param {import('@playwright/test').Page} page
 * @param {string} url
 * @param {string} method - POST, PUT, DELETE
 * @param {object} data
 * @returns {Promise<{status: number, body: object}>}
 */
async function ajaxRequest(page, url, method = 'POST', data = {}) {
  // Pastikan jQuery context tersedia
  const hasContext = await hasJqueryContext(page);
  if (!hasContext) {
    await initJqueryContext(page);
  }

  // Ambil CSRF token dari meta tag
  const token = await page.evaluate(() => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  });

  // Build payload dengan _token dan _method jika perlu
  const payload = { ...data, _token: token };
  if (['PUT', 'DELETE'].includes(method)) {
    payload._method = method;
  }

  // Eksekusi jQuery AJAX
  const result = await page.evaluate(async ({ url, payload }) => {
    return new Promise((resolve) => {
      $.ajax({
        url: url,
        type: 'POST',
        data: payload,
        dataType: 'json',
        success: (response) => resolve({ status: 200, body: response }),
        error: (xhr) => {
          try {
            const body = JSON.parse(xhr.responseText);
            resolve({ status: xhr.status, body });
          } catch (e) {
            resolve({ status: xhr.status, body: xhr.responseText });
          }
        }
      });
    });
  }, { url, payload });

  return result;
}

/**
 * GET request — langsung page.goto()
 * @param {import('@playwright/test').Page} page
 * @param {string} url
 * @returns {Promise<import('@playwright/test').Response>}
 */
async function getRequest(page, url) {
  return await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 15000 });
}

export { ajaxRequest, getRequest, initJqueryContext };
```

### 3.3 `helpers/assert.helper.js` — Custom Assertions

```javascript
/**
 * Assert Helper — Custom assertions untuk pattern umum
 */
import { expect } from '@playwright/test';

/**
 * Assert response status termasuk dalam daftar yang diizinkan
 * @param {number} actualStatus
 * @param {number[]} allowedStatuses
 */
function expectStatusIn(actualStatus, allowedStatuses) {
  expect(allowedStatuses).toContain(actualStatus);
}

/**
 * Assert response body sukses (status: true / success: true)
 * @param {object} body
 */
function expectSuccess(body) {
  const isSuccess = body?.status === true || body?.success === true;
  expect(isSuccess).toBeTruthy();
}

/**
 * Assert response body gagal
 * @param {object} body
 */
function expectFailure(body) {
  const isFailure = body?.status === false || body?.success === false;
  expect(isFailure).toBeTruthy();
}

/**
 * Assert error message mengandung teks tertentu
 * @param {object} body
 * @param {string} expectedMessage
 */
function expectErrorMessage(body, expectedMessage) {
  const message = body?.message || body?.msg || '';
  expect(message).toContain(expectedMessage);
}

/**
 * Assert HTTP 403 Forbidden
 * @param {import('@playwright/test').Response} response
 */
function expectForbidden(response) {
  expect(response.status()).toBe(403);
}

/**
 * Assert HTTP 404 Not Found
 * @param {import('@playwright/test').Response|object} responseOrBody
 */
function expectNotFound(responseOrBody) {
  if (responseOrBody.status) {
    expect(responseOrBody.status).toBe(404);
  } else {
    expect(responseOrBody).toBe(404);
  }
}

/**
 * Assert validation error untuk field tertentu
 * @param {object} body
 * @param {string} fieldName
 */
function expectValidationError(body, fieldName) {
  const errors = body?.msgField || body?.errors || {};
  expect(errors).toHaveProperty(fieldName);
}

/**
 * Assert DataTables response memiliki struktur yang benar
 * @param {object} body
 */
function expectDataTablesResponse(body) {
  expect(body).toHaveProperty('draw');
  expect(body).toHaveProperty('recordsTotal');
  expect(body).toHaveProperty('recordsFiltered');
  expect(body).toHaveProperty('data');
  expect(Array.isArray(body.data)).toBeTruthy();
}

/**
 * Assert toast notification muncul dengan pesan tertentu
 * @param {import('@playwright/test').Page} page
 * @param {string} expectedMessage
 */
async function expectToastMessage(page, expectedMessage) {
  const toast = page.locator('.toast, .swal2-popup, .alert-success, .alert-danger').first();
  await expect(toast).toBeVisible({ timeout: 5000 });
  const text = await toast.textContent();
  expect(text).toContain(expectedMessage);
}

export {
  expectStatusIn,
  expectSuccess,
  expectFailure,
  expectErrorMessage,
  expectForbidden,
  expectNotFound,
  expectValidationError,
  expectDataTablesResponse,
  expectToastMessage,
};
```

### 3.4 `helpers/wait.helper.js` — Wait Strategies

```javascript
/**
 * Wait Helper — Strategi wait untuk berbagai kondisi
 */

/**
 * Tunggu hingga modal AJAX selesai loading
 * @param {import('@playwright/test').Page} page
 * @param {number} timeout
 */
async function waitForModal(page, timeout = 10000) {
  await page.waitForSelector('.modal.fade.show', { timeout });
  await page.waitForTimeout(800); // Extra wait untuk form AJAX
}

/**
 * Tunggu hingga SweetAlert2 muncul
 * @param {import('@playwright/test').Page} page
 * @param {number} timeout
 */
async function waitForSwal(page, timeout = 8000) {
  await page.waitForSelector('.swal2-popup', { timeout });
  await page.waitForTimeout(500);
}

/**
 * Tunggu hingga DataTables selesai loading
 * @param {import('@playwright/test').Page} page
 * @param {number} timeout
 */
async function waitForDataTable(page, timeout = 10000) {
  await page.waitForSelector('#table_id_wrapper .dataTables_processing', { state: 'hidden', timeout }).catch(() => {});
  await page.waitForTimeout(500);
}

/**
 * Tunggu hingga AJAX request selesai (network idle)
 * @param {import('@playwright/test').Page} page
 * @param {number} timeout
 */
async function waitForAjaxComplete(page, timeout = 10000) {
  await page.waitForLoadState('networkidle', { timeout });
  await page.waitForTimeout(500);
}

/**
 * Tunggu hingga halaman benar-benar siap dengan jQuery
 * @param {import('@playwright/test').Page} page
 * @param {number} timeout
 */
async function waitForPageReady(page, timeout = 15000) {
  await page.waitForLoadState('domcontentloaded', { timeout });
  await page.waitForTimeout(1000);
}

export { waitForModal, waitForSwal, waitForDataTable, waitForAjaxComplete, waitForPageReady };
```

### 3.5 `helpers/index.js`

```javascript
export { navigateTo, openModal, fillField, selectOption, submitForm, handleSwal } from './ui.helper.js';
export { ajaxRequest, getRequest, initJqueryContext } from './api.helper.js';
export { expectStatusIn, expectSuccess, expectFailure, expectErrorMessage, expectForbidden, expectNotFound, expectValidationError, expectDataTablesResponse, expectToastMessage } from './assert.helper.js';
export { waitForModal, waitForSwal, waitForDataTable, waitForAjaxComplete, waitForPageReady } from './wait.helper.js';
```

---

## 4. PAGE OBJECT DESIGN

### 4.1 `pages/components/modal.component.js` — Shared Modal Component

```javascript
/**
 * Component: Modal AJAX — Interaksi umum untuk semua modal CRUD
 * Semua modul menggunakan pola modal yang sama (AdminLTE + jQuery)
 */
class ModalComponent {
  constructor(page) {
    this.page = page;
  }

  get modal() {
    return this.page.locator('.modal.fade.show');
  }

  get form() {
    return this.modal.locator('form');
  }

  get submitButton() {
    return this.form.locator('button[type="submit"]').last();
  }

  async waitForOpen(timeout = 10000) {
    await this.page.waitForSelector('.modal.fade.show', { timeout });
    await this.page.waitForTimeout(800);
  }

  async waitForClose(timeout = 5000) {
    await this.page.waitForSelector('.modal.fade.show', { state: 'hidden', timeout }).catch(() => {});
  }

  async fillField(name, value) {
    const field = this.modal.locator(`[name="${name}"]`);
    await field.waitFor({ state: 'visible', timeout: 5000 });
    await field.fill('');
    await field.fill(String(value));
  }

  async selectOption(name, value) {
    const select = this.modal.locator(`select[name="${name}"]`);
    await select.waitFor({ state: 'visible', timeout: 5000 });
    await select.selectOption(value);
  }

  async submit() {
    await this.submitButton.waitFor({ state: 'visible', timeout: 5000 });
    await this.submitButton.click();
    await this.page.waitForTimeout(1000);
  }

  async getValidationError(fieldName) {
    const errorEl = this.modal.locator(`#error_${fieldName}`);
    try {
      return await errorEl.textContent({ timeout: 3000 });
    } catch {
      return null;
    }
  }
}

export { ModalComponent };
```

### 4.2 `pages/components/datatables.component.js` — Shared DataTables Component

```javascript
/**
 * Component: DataTables — Interaksi umum dengan Yajra DataTables
 */
class DataTablesComponent {
  constructor(page, tableId = 'table_id') {
    this.page = page;
    this.tableId = tableId;
  }

  get wrapper() {
    return this.page.locator(`#${this.tableId}_wrapper`);
  }

  get table() {
    return this.page.locator(`#${this.tableId}`);
  }

  get searchBox() {
    return this.wrapper.locator('input[type="search"]');
  }

  get rows() {
    return this.table.locator('tbody tr');
  }

  get processingOverlay() {
    return this.page.locator(`#${this.tableId}_wrapper .dataTables_processing`);
  }

  async waitForLoad(timeout = 10000) {
    await this.processingOverlay.waitFor({ state: 'hidden', timeout }).catch(() => {});
    await this.page.waitForTimeout(500);
  }

  async search(keyword) {
    await this.searchBox.fill(keyword);
    await this.waitForLoad();
  }

  async getRowCount() {
    return await this.rows.count();
  }

  async getCellText(rowIndex, columnIndex) {
    const cell = this.rows.nth(rowIndex).locator('td').nth(columnIndex);
    return await cell.textContent();
  }

  async clickAction(rowIndex, actionButtonSelector) {
    const actionCell = this.rows.nth(rowIndex).locator('td').last();
    const btn = actionCell.locator(actionButtonSelector);
    await btn.click();
  }

  async selectPageSize(size) {
    const lengthSelect = this.wrapper.locator('select[name$="_length"]');
    await lengthSelect.selectOption(String(size));
    await this.waitForLoad();
  }

  async paginateTo(pageNumber) {
    const paginationBtn = this.wrapper.locator(`.pagination a[data-dt-idx="${pageNumber}"]`);
    await paginationBtn.click();
    await this.waitForLoad();
  }
}

export { DataTablesComponent };
```

### 4.3 `pages/components/sidebar.component.js` — AdminLTE Sidebar Component

```javascript
/**
 * Component: Sidebar — Navigasi sidebar AdminLTE
 */
class SidebarComponent {
  constructor(page) {
    this.page = page;
  }

  get sidebar() {
    return this.page.locator('.main-sidebar');
  }

  get navItems() {
    return this.sidebar.locator('.nav-item');
  }

  async navigateTo(menuText, submenuText = null) {
    // Map menu ke URL (reliable, tanpa klik)
    const urlMap = this.getUrlMap();
    const url = urlMap[menuText];
    
    if (url) {
      await this.page.goto(url, { waitUntil: 'networkidle', timeout: 15000 });
      await this.page.waitForTimeout(1000);
      return;
    }

    // Fallback: klik sidebar
    if (submenuText) {
      // Expand parent menu
      const parentLink = this.sidebar.locator(`a:has-text("${menuText}")`);
      await parentLink.click();
      await this.page.waitForTimeout(500);
      // Click submenu
      const subLink = this.sidebar.locator(`a:has-text("${submenuText}")`);
      await subLink.click();
    } else {
      const link = this.sidebar.locator(`a:has-text("${menuText}")`);
      await link.click();
    }

    await this.page.waitForLoadState('networkidle');
    await this.page.waitForTimeout(1000);
  }

  getUrlMap() {
    return {
      'Dashboard': '/dashboard',
      'Daftar Jadwal': '/jadwal',
      'Daftar Ruangan': '/ruangan',
      'Daftar Organisasi': '/organisasi',
      'Pengajuan Peminjaman': '/pengajuan',
      'Antrian Approval': '/pengajuan/approval/antrian_ajax',
      'Daftar Admin': '/admin',
      'Daftar Dosen': '/dosen',
      'Daftar Tendik': '/tendik',
      'Daftar Mahasiswa': '/mahasiswa',
      'Daftar Program Studi': '/prodi',
      'Daftar Kelas': '/kelas',
    };
  }
}

export { SidebarComponent };
```

### 4.4 `pages/components/sweetalert.component.js`

```javascript
/**
 * Component: SweetAlert2 — Interaksi dengan SweetAlert popup
 */
class SweetAlertComponent {
  constructor(page) {
    this.page = page;
  }

  get popup() {
    return this.page.locator('.swal2-popup');
  }

  get title() {
    return this.popup.locator('.swal2-title');
  }

  get content() {
    return this.popup.locator('.swal2-html-container');
  }

  get confirmButton() {
    return this.popup.locator('.swal2-confirm');
  }

  get cancelButton() {
    return this.popup.locator('.swal2-cancel');
  }

  async waitForOpen(timeout = 8000) {
    await this.page.waitForSelector('.swal2-popup', { timeout });
    await this.page.waitForTimeout(500);
  }

  async confirm() {
    await this.confirmButton.click();
    await this.page.waitForTimeout(500);
  }

  async cancel() {
    await this.cancelButton.click();
    await this.page.waitForTimeout(500);
  }

  async getMessage() {
    try {
      const titleText = await this.title.textContent();
      const contentText = await this.content.textContent();
      return `${titleText} ${contentText}`.trim();
    } catch {
      return '';
    }
  }
}

export { SweetAlertComponent };
```

### 4.5 Contoh Page Object: `pages/pengajuan/create.page.js`

```javascript
/**
 * Page Object: Create Pengajuan
 * URL: GET /pengajuan/create_ajax (modal)
 * POST /pengajuan/ajax (submit)
 */
import { ModalComponent } from '../components/modal.component.js';

class CreatePengajuanPage {
  constructor(page) {
    this.page = page;
    this.modal = new ModalComponent(page);
  }

  // URL endpoint untuk modal create
  static createUrl = '/pengajuan/create_ajax';
  static submitUrl = '/pengajuan/ajax';

  // Selectors
  get ruanganCheckboxes() {
    return this.modal.modal.locator('input[name="ruangan_ids[]"]');
  }

  /**
   * Buka modal create pengajuan via AJAX
   */
  async open() {
    await this.page.goto(CreatePengajuanPage.createUrl, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await this.page.waitForTimeout(500);
  }

  /**
   * Isi form create pengajuan
   * @param {object} data - Data pengajuan
   */
  async fillForm(data) {
    await this.modal.fillField('pengajuan_nama', data.pengajuan_nama);
    await this.modal.fillField('pengajuan_tgl', data.pengajuan_tgl);
    await this.modal.fillField('pengajuan_jam_mulai', data.pengajuan_jam_mulai);
    await this.modal.fillField('pengajuan_jam_selesai', data.pengajuan_jam_selesai);
    await this.modal.fillField('pengajuan_jumPes', String(data.pengajuan_jumPes));
    if (data.pengajuan_keterangan) {
      await this.modal.fillField('pengajuan_keterangan', data.pengajuan_keterangan);
    }
    await this.modal.selectOption('organisasi_id', String(data.organisasi_id));
    
    // Select ruangan checkboxes
    for (const ruanganId of data.ruangan_ids) {
      await this.ruanganCheckboxes.locator(`[value="${ruanganId}"]`).check();
    }
  }

  /**
   * Submit form via AJAX
   * @param {object} page - Playwright page (untuk doAjax context)
   * @returns {Promise<{status: number, body: object}>}
   */
  async submit(pageContext) {
    await this.modal.submit();
    // Return via doAjax helper (from api.helper.js)
    const { ajaxRequest } = await import('../../helpers/api.helper.js');
    // Note: actual submit might need different handling
    await this.page.waitForTimeout(1000);
  }
}

export { CreatePengajuanPage };
```

### 4.6 Contoh Page Object: `pages/pengajuan/index.page.js`

```javascript
/**
 * Page Object: Daftar Pengajuan (Role-Aware)
 * URL: GET /pengajuan
 * POST /pengajuan/list (DataTables)
 */
import { DataTablesComponent } from '../components/datatables.component.js';

class PengajuanIndexPage {
  constructor(page) {
    this.page = page;
    this.datatables = new DataTablesComponent(page, 'table_pengajuan');
  }

  static url = '/pengajuan';

  // Selectors
  get tambahButton() {
    return this.page.locator('button:has-text("Tambah"), a:has-text("Tambah")');
  }

  get timelineTab() {
    return this.page.locator('a:has-text("Timeline")');
  }

  get cetakSuratButton() {
    return this.page.locator('button:has-text("Cetak Surat"), a:has-text("Cetak Surat")');
  }

  get statusBadge() {
    return this.datatables.table.locator('.badge');
  }

  /**
   * Navigasi ke halaman daftar pengajuan
   */
  async goto() {
    await this.page.goto(PengajuanIndexPage.url, { waitUntil: 'networkidle', timeout: 15000 });
    await this.page.waitForTimeout(1000);
  }

  /**
   * Klik tombol Tambah untuk membuka modal create
   */
  async clickTambah() {
    await this.tambahButton.click();
    await this.page.waitForTimeout(500);
  }

  /**
   * Dapatkan status pengajuan dari baris tertentu
   * @param {number} rowIndex
   * @returns {Promise<string>}
   */
  async getStatus(rowIndex = 0) {
    return await this.datatables.getCellText(rowIndex, 4); // Kolom status
  }

  /**
   * Klik aksi pada baris tertentu
   * @param {number} rowIndex
   * @param {string} action - 'show', 'timeline', 'cetak', 'verifikasi'
   */
  async clickAction(rowIndex, action) {
    const actionMap = {
      show: 'button.btn-outline-info',
      timeline: 'button:has-text("Timeline")',
      cetak: 'button:has-text("Cetak")',
      verifikasi: 'button:has-text("Verifikasi")',
    };
    const selector = actionMap[action];
    if (selector) {
      await this.datatables.clickAction(rowIndex, selector);
      await this.page.waitForTimeout(500);
    }
  }
}

export { PengajuanIndexPage };
```

### 4.7 Contoh Page Object: `pages/approval/proses.page.js`

```javascript
/**
 * Page Object: Proses Approval (Verifikator)
 * URL: PUT /pengajuan/approval/{approvalId}/proses_ajax
 */
class ProsesApprovalPage {
  constructor(page) {
    this.page = page;
  }

  /**
   * Setujui tahap approval
   * @param {number} approvalId
   * @param {object} pageContext - Playwright page (for doAjax)
   * @returns {Promise<{status: number, body: object}>}
   */
  async setujui(approvalId, pageContext) {
    const { ajaxRequest } = await import('../../helpers/api.helper.js');
    return await ajaxRequest(pageContext, `/pengajuan/approval/${approvalId}/proses_ajax`, 'PUT', {
      status_approval: 'Disetujui',
    });
  }

  /**
   * Tolak tahap approval dengan alasan
   * @param {number} approvalId
   * @param {string} alasan
   * @param {object} pageContext
   * @returns {Promise<{status: number, body: object}>}
   */
  async tolak(approvalId, alasan, pageContext) {
    const { ajaxRequest } = await import('../../helpers/api.helper.js');
    return await ajaxRequest(pageContext, `/pengajuan/approval/${approvalId}/proses_ajax`, 'PUT', {
      status_approval: 'Ditolak',
      alasan_penolakan: alasan,
    });
  }

  /**
   * Cek apakah response menunjukkan proses berhasil
   * @param {object} body
   * @returns {boolean}
   */
  isSuccess(body) {
    return body?.status === true;
  }

  /**
   * Cek apakah perlu menunggu approval paralel
   * @param {object} body
   * @returns {boolean}
   */
  needsParallelApproval(body) {
    return body?.message?.includes('menunggu approval paralel');
  }
}

export { ProsesApprovalPage };
```

---

## 5. CONFIG DESIGN

### 5.1 `tests/e2e/playwright.config.js` (Improved)

```javascript
// @ts-check
import { defineConfig, devices } from '@playwright/test';
import dotenv from 'dotenv';
import path from 'path';

// Load environment variables
dotenv.config({ path: path.resolve(__dirname, '.env') });

export default defineConfig({
  // Test directory — per modul
  testDir: './modules',

  // Pattern untuk test files
  testMatch: ['**/*.spec.js'],

  // Non-parallel untuk menjaga session state
  fullyParallel: false,

  // Fail fast di CI
  forbidOnly: !!process.env.CI,

  // Retry — 1x di CI
  retries: process.env.CI ? 1 : 0,

  // Single worker untuk sequential test execution
  workers: 1,

  // Reporter
  reporter: [
    ['list'],
    ['html', { outputFolder: '../playwright-report', open: 'never' }],
    ['json', { outputFile: '../playwright-report/test-results.json' }],
  ],

  // Global timeout per test
  timeout: 60000,

  // Expect timeout
  expect: {
    timeout: 10000,
  },

  // Shared settings for all projects
  use: {
    // Base URL dari environment variable
    baseURL: process.env.BASE_URL || 'http://127.0.0.1:8000',

    // Trace: simpan trace on retry
    trace: process.env.CI ? 'on-first-retry' : 'retain-on-failure',

    // Screenshot: hanya saat gagal
    screenshot: 'only-on-failure',

    // Video: rekam saat gagal
    video: process.env.CI ? 'retain-on-failure' : 'off',

    // Headless: default true
    headless: process.env.HEADLESS !== 'false',

    // Timeouts
    actionTimeout: 10000,
    navigationTimeout: 15000,
  },

  // Projects
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    // Firefox — optional, bisa ditambahkan nanti
    // {
    //   name: 'firefox',
    //   use: { ...devices['Desktop Firefox'] },
    // },
  ],

  // Global setup — before all tests
  globalSetup: './global-setup.js',

  // Global teardown — after all tests
  globalTeardown: './global-teardown.js',
});
```

### 5.2 `tests/e2e/global-setup.js`

```javascript
/**
 * Global Setup — dijalankan sekali sebelum semua test
 * Berguna untuk: seeding database, memastikan server running, dll.
 */
import { execSync } from 'child_process';

async function globalSetup() {
  console.log('[Global Setup] Memulai...');

  // Cek apakah server Laravel sudah running
  try {
    const response = await fetch(process.env.BASE_URL || 'http://127.0.0.1:8000');
    if (response.ok) {
      console.log('[Global Setup] Server sudah running.');
    }
  } catch (e) {
    console.log('[Global Setup] Server belum running. Jalankan: php artisan serve');
    console.log('[Global Setup] atau set BASE_URL ke server yang sudah running.');
    // Tidak throw error — test akan gagal sendiri dengan pesan jelas
  }

  // Run database seeder untuk memastikan data testing ada
  try {
    execSync('php artisan db:seed --class=DatabaseSeeder', { 
      cwd: process.cwd(),
      stdio: 'pipe'
    });
    console.log('[Global Setup] Database seeding selesai.');
  } catch (e) {
    console.warn('[Global Setup] Warning: Database seeding gagal:', e.message);
  }

  console.log('[Global Setup] Selesai.');
}

export default globalSetup;
```

### 5.3 `tests/e2e/global-teardown.js`

```javascript
/**
 * Global Teardown — dijalankan sekali setelah semua test
 * Berguna untuk: cleanup data testing, laporan akhir
 */
async function globalTeardown() {
  console.log('[Global Teardown] Membersihkan...');
  // Optional: Hapus data testing yang dibuat
  // Optional: Generate summary report
  console.log('[Global Teardown] Selesai.');
}

export default globalTeardown;
```

---

## 6. ENVIRONMENT CONFIGURATION

### 6.1 `tests/e2e/.env`

```env
# === SPR JTI — Playwright Environment ===

# Base URL aplikasi Laravel
BASE_URL=http://127.0.0.1:8000

# Browser mode
HEADLESS=true

# Credentials — sesuaikan dengan data seeder
ADMIN_USERNAME=admin1
ADMIN_PASSWORD=0000000000
DOSEN_USERNAME=dosen
DOSEN_PASSWORD=1111111111
TENDIK_USERNAME=tendik1
TENDIK_PASSWORD=0021111111
MAHASISWA_USERNAME=mahasiswa1
MAHASISWA_PASSWORD=1111111111
# Pemegang jabatan approval (login sebagai DSN/MHS biasa, bukan level akun terpisah)
KETUA_UMUM_USERNAME=2241760003
KETUA_UMUM_PASSWORD=123456
DPK_USERNAME=dosen2
DPK_PASSWORD=123456
PRESIDEN_BEM_USERNAME=2241760063
PRESIDEN_BEM_PASSWORD=123456
KETUA_JURUSAN_USERNAME=0013333333
KETUA_JURUSAN_PASSWORD=123456
WADIR_2_USERNAME=0014444444
WADIR_2_PASSWORD=123456

# CI Mode
CI=false

# Test data paths
TEST_DATA_DIR=./data
```

### 6.2 `tests/e2e/.env.example`

```env
# === SPR JTI — Playwright Environment (Template) ===
# Copy file ini ke .env dan sesuaikan dengan environment lokal

BASE_URL=http://127.0.0.1:8000
HEADLESS=true
ADMIN_USERNAME=admin1
ADMIN_PASSWORD=0000000000
CI=false
```

---

## 7. TEST SUITE ORGANIZATION

### 7.1 Mapping Use Case → Test Suite

| Test Suite File | Use Case | Jumlah Skenario | Prioritas |
|---|---|---|---|
| `01-auth.spec.js` | UC-AUTH-01, UC-AUTH-02, UC-AUTH-03 | 10-15 | Critical |
| `02-master-admin.spec.js` | UC-MD-01, UC-MD-02 | 15-20 | High |
| `03-master-dosen.spec.js` | UC-MD-03, UC-MD-04 | 12-18 | High |
| `04-master-tendik.spec.js` | UC-MD-05, UC-MD-06 | 10-15 | High |
| `05-master-mahasiswa.spec.js` | UC-MD-07, UC-MD-08 | 15-20 | High |
| `06-master-prodi.spec.js` | UC-MD-09 | 8-12 | Medium |
| `07-master-kelas.spec.js` | UC-MD-10 | 8-12 | Medium |
| `08-ruangan.spec.js` (aktual: `ruangan.spec.js` + `ruangan-availability.spec.js`) | UC-RG-01, UC-RG-02, UC-RG-03, **UC-RG-04 (Baru)** | 12-18 + 10 | High |
| `09-organisasi.spec.js` (aktual: `organisasi.spec.js`, `01-organisasi-verifikator.spec.js`) | UC-ORG-01, UC-ORG-02 | 10-15 | High |
| `10-jabatan-approval.spec.js` (aktual: dicakup lintas `approval-stage*.spec.js`) | UC-JAB-00, UC-JAB-01, UC-JAB-02, UC-JAB-03 | 15-20 | High |
| `11-pengajuan.spec.js` (aktual: `pengajuan.spec.js`) | UC-PGN-01 (termasuk Ketua Pelaksana), UC-PGN-02, UC-PGN-03, UC-PGN-04 | 18-25 | Critical |
| `12-approval-berjenjang.spec.js` (aktual: `approval-stage0.spec.js`, `approval-stage1.spec.js`, `approval-stage2.spec.js`, `approval-stage3.spec.js`, `approval-parallel.spec.js`, `03-approval-berjenjang.spec.js`) | UC-JAB-00, UC-JAB-02, UC-JAB-03, UC-PGN-01, UC-PGN-05 | 62 (aktual) | Critical |
| `13-auto-reject.spec.js` | UC-PGN-05 | 5-8 | High |
| `14-jadwal.spec.js` | UC-JDW-01, UC-JDW-02, UC-JDW-03, UC-JDW-04 | 12-16 | High |
| `15-formulir.spec.js` | UC-MD-11 | 6-10 | Medium |
| `16-dashboard.spec.js` | UC-DSH-01 | 8-12 | Medium |
| `17-permission-test.spec.js` | Semua UC (permission matrix) | 20-30 | Critical |
| `18-boundary-test.spec.js` | Semua UC (boundary values) | 15-20 | Medium |
| `19-data-integrity.spec.js` | UC-MD-01 s/d 10, UC-ORG-01 (cascade) | 10-15 | Medium |

### 7.2 Priority Test Suites untuk Execution

```
Priority 1 (Critical — Run First):
  01-auth.spec.js
  11-pengajuan.spec.js
  12-approval-berjenjang.spec.js
  17-permission-test.spec.js

Priority 2 (High — Core Features):
  02-master-admin.spec.js
  03-master-dosen.spec.js
  04-master-tendik.spec.js
  05-master-mahasiswa.spec.js
  08-ruangan.spec.js
  09-organisasi.spec.js
  10-jabatan-approval.spec.js (aktual: cakupan lintas approval-stage*.spec.js)
  13-auto-reject.spec.js
  14-jadwal.spec.js

Priority 3 (Medium — Supporting Features):
  06-master-prodi.spec.js
  07-master-kelas.spec.js
  15-formulir.spec.js
  16-dashboard.spec.js
  18-boundary-test.spec.js
  19-data-integrity.spec.js
```

### 7.3 Test Lifecycle (per Suite)

```javascript
// Template struktur per test suite
import { test, expect } from '@playwright/test';
import { USERS, login, logout } from '../fixtures/auth.fixture.js';
import { VALID_DATA, INVALID_DATA, BOUNDARY_DATA, DUPLICATE_DATA } from '../fixtures/data.fixture.js';
import { ajaxRequest, getRequest } from '../helpers/api.helper.js';
import { navigateTo, openModal, handleSwal } from '../helpers/ui.helper.js';
import { expectStatusIn, expectSuccess, expectForbidden } from '../helpers/assert.helper.js';

test.describe('Nama Modul', () => {

  // Setup: login before each test
  test.beforeEach(async ({ page }) => {
    await login(page, USERS.ADM.username, USERS.ADM.password);
  });

  // Cleanup: logout after each test
  test.afterEach(async ({ page }) => {
    await logout(page);
  });

  // Positive Test
  test('TC-XX-POS-01: Deskripsi', async ({ page }) => {
    // Arrange
    await navigateTo(page, 'Nama Menu');
    
    // Act
    const result = await ajaxRequest(page, '/url/action', 'POST', VALID_DATA.moduleName);
    
    // Assert
    expectSuccess(result.body);
  });

  // Negative Test
  test('TC-XX-NEG-01: Deskripsi', async ({ page }) => {
    await navigateTo(page, 'Nama Menu');
    const result = await ajaxRequest(page, '/url/action', 'POST', INVALID_DATA.emptyFields);
    expect(result.body?.status).toBe(false);
  });

  // Permission Test
  test('TC-XX-PER-01: Deskripsi', async ({ page }) => {
    // Login sebagai role yang tidak punya akses
    await logout(page);
    await login(page, USERS.DSN.username, USERS.DSN.password);
    
    const resp = await getRequest(page, '/admin-only-url');
    expectForbidden(resp);
  });
});
```

---

## 8. MIGRATION PLAN

### 8.1 Dari Existing → Target Structure

| File Existing | Target Location | Action |
|---|---|---|
| `tests/e2e/playwright.config.js` | `tests/e2e/playwright.config.js` | **Enhance** — tambah reporter, env, global setup |
| `tests/e2e/fixtures/auth.fixture.js` | `tests/e2e/fixtures/auth.fixture.js` | **Enhance** — tambah user VRF lengkap |
| `tests/e2e/modules/helpers.js` | `tests/e2e/helpers/ui.helper.js` | **Refactor** — pisahkan UI vs API vs Assert |
| — (new) | `tests/e2e/helpers/api.helper.js` | **Create** — extract doAjax dari auth fixture |
| — (new) | `tests/e2e/helpers/assert.helper.js` | **Create** — custom assertions |
| — (new) | `tests/e2e/helpers/wait.helper.js` | **Create** — wait strategies |
| — (new) | `tests/e2e/fixtures/data.fixture.js` | **Create** — all test data |
| — (new) | `tests/e2e/pages/components/*` | **Create** — component POM |
| — (new) | `tests/e2e/pages/*/*.page.js` | **Create** — page objects per modul |
| `tests/e2e/modules/01-organisasi-verifikator.spec.js` | `tests/e2e/modules/09-organisasi.spec.js` | **Refactor** — gunakan POM |
| `tests/e2e/modules/02-status-ruangan-jadwal.spec.js` | `tests/e2e/modules/08-ruangan.spec.js` + `14-jadwal.spec.js` | **Split** — per modul |
| `tests/e2e/modules/03-approval-berjenjang.spec.js` | `tests/e2e/modules/12-approval-berjenjang.spec.js` | **Refactor** — gunakan POM + data fixture |
| — (new) | `tests/e2e/modules/01-auth.spec.js` | **Create** — login/logout/profile |
| — (new) | `tests/e2e/modules/02-master-admin.spec.js` | **Create** — CRUD admin |
| — (new) | `tests/e2e/modules/11-pengajuan.spec.js` | **Create** — full pengajuan flow |
| — (new) | `tests/e2e/modules/13-auto-reject.spec.js` | **Create** — auto reject |
| — (new) | `tests/e2e/modules/17-permission-test.spec.js` | **Create** — permission matrix |
| — (new) | `tests/e2e/.env` | **Create** — environment file |
| — (new) | `tests/e2e/global-setup.js` | **Create** — global setup |
| — (new) | `tests/e2e/data/*` | **Create** — test data files |

### 8.2 Recommended Execution Order

```
Step 1: Buat .env dan global-setup.js
Step 2: Buat helpers (api, assert, wait)
Step 3: Buat fixtures/data.fixture.js
Step 4: Buat components POM (modal, datatables, sidebar, swal)
Step 5: Buat page objects per modul (prioritas: auth, pengajuan, approval)
Step 6: Refactor existing spec ke POM + data fixtures
Step 7: Buat spec baru (auth, permission-test, auto-reject, dll.)
Step 8: Enhance playwright.config.js dengan reporter dan env
Step 9: Run full regression test
```

---

## 9. NOTES

### 9.1 Testing Strategy Summary

| Aspek | Approach |
|---|---|
| **Authentication** | Login via UI (form submit) — session otomatis tersimpan di browser context |
| **AJAX Calls** | `page.evaluate()` + jQuery `$.ajax()` — karena Laravel + CSRF token di meta tag |
| **CRUD Operations** | Page Object Model — setiap modul punya create/edit/delete page object |
| **DataTables** | Shared DataTables component — reusable untuk semua modul |
| **Modals** | Shared Modal component — handle form fields, select, submit |
| **SweetAlert2** | Shared SweetAlert component — handle popup confirm/cancel |
| **Permission Tests** | Login sebagai role berbeda, akses URL, assert 403 |
| **File Upload** | Playwright `fileChooser` event + `page.setInputFiles()` |
| **PDF Download** | Playwright `page.waitForEvent('download')` |
| **Data Cleanup** | Seeder di `global-setup.js`, data testing pakai timestamp unik |
| **Environment** | `.env` file + `dotenv` — baseURL, credentials, config |

### 9.2 Key Design Decisions

1. **Direct URL navigation over sidebar clicks** — lebih reliable, menghindari flakiness dari menu animasi
2. **jQuery AJAX over fetch/axios** — aplikasi existing menggunakan jQuery untuk semua AJAX
3. **Timestamp-based unique data** — setiap test run menghasilkan data unik, menghindari conflict
4. **Sequential execution (workers:1)** — menjaga session state antar test
5. **POM per modul** — setiap modul bisnis punya page object sendiri, bukan 1 POM besar
6. **Component reuse** — modal, datatables, sidebar, swal sebagai shared components
7. **Fixture separation** — auth (user data) terpisah dari data fixture (test data)
8. **No hardcoded values** — semua nilai dari fixtures atau environment variables

