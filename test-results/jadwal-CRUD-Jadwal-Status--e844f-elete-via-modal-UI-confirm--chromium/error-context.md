# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: jadwal.spec.js >> CRUD Jadwal & Status State Machine (FR-4) >> Delete Jadwal >> [VALID] TC-JDW-DL-04: Delete via modal UI (confirm)
- Location: modules\jadwal.spec.js:476:5

# Error details

```
Error: expect(received).toBe(expected) // Object.is equality

Expected: true
Received: false
```

# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - navigation [ref=e2]:
    - generic [ref=e3]:
      - img "Logo JTI" [ref=e4]
      - text: Sistem Peminjaman Ruangan JTI
    - link " Login" [ref=e5] [cursor=pointer]:
      - /url: http://127.0.0.1:8000/login
      - generic [ref=e6]: 
      - text: Login
  - generic [ref=e7]:
    - heading "Statistik Publik" [level=3] [ref=e8]
    - generic [ref=e9]:
      - generic [ref=e12]:
        - heading "8" [level=3] [ref=e13]
        - paragraph [ref=e14]: Total Ruangan
        - generic [ref=e16]: 
      - generic [ref=e19]:
        - heading "8" [level=3] [ref=e20]
        - paragraph [ref=e21]: Total Ruangan Kosong
        - generic [ref=e23]: 
      - generic [ref=e26]:
        - heading "0" [level=3] [ref=e27]
        - paragraph [ref=e28]: Jadwal Hari Ini
        - generic [ref=e30]: 
    - generic [ref=e31]:
      - heading "Top 5 Ruangan Terfavorit" [level=3] [ref=e35]
      - heading "Distribusi Peminjam" [level=3] [ref=e42]
    - heading "Tren Peminjaman (6 Bulan)" [level=3] [ref=e49]
```

# Test source

```ts
  1   | /**
  2   |  * Test Suite: CRUD Jadwal + Status State Machine
  3   |  *
  4   |  * Module: Jadwal (Schedule Management)
  5   |  * Controller: JadwalController -> JadwalService -> JadwalModel (t_jadwal)
  6   |  * Pivot: t_jadwal_ruangan (many-to-many with RuanganModel)
  7   |  *
  8   |  * Route Groups:
  9   |  *   authorize:ADM — create_ajax, store_ajax, edit_ajax, update_ajax, confirm_ajax, delete_ajax, update_status_ajax
  10  |  *   authorize:ADM,DSN,TDK,MHS — index, list, show_ajax, get_kelas_by_prodi
  11  |  *
  12  |  * Status State Machine (FR-4.2):
  13  |  *   Akan Datang -> Berlangsung -> Ditinjau -> Selesai
  14  |  *                                          -> Dokumentasi Tidak Sesuai -> Selesai
  15  |  *
  16  |  * Coverage: 75+ test cases — CRUD + Status Transitions + Permission + Edge Cases
  17  |  * Strategy: Login via UI, AJAX via jQuery $.ajax, Page Object Model
  18  |  */
  19  | 
  20  | import { test, expect } from '@playwright/test';
  21  | import { USERS, login, logout } from '../fixtures/auth.fixture.js';
  22  | import { JadwalPage } from '../pages/jadwal/index.page.js';
  23  | 
  24  | // ──────────────────────────────────────────────────
  25  | // Helpers
  26  | // ──────────────────────────────────────────────────
  27  | 
  28  | async function createJadwal(page) {
  29  |   const jPage = new JadwalPage(page);
  30  |   const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL') });
  31  |   const result = await jPage.create(data);
> 32  |   expect(result.body.status).toBe(true);
      |                              ^ Error: expect(received).toBe(expected) // Object.is equality
  33  |   return { jPage, data, id: result.body.jadwal_id };
  34  | }
  35  | 
  36  | 
  37  | // ──────────────────────────────────────────────────
  38  | // Lifecycle
  39  | // ──────────────────────────────────────────────────
  40  | 
  41  | test.describe('CRUD Jadwal & Status State Machine (FR-4)', () => {
  42  | 
  43  |   test.beforeEach(async ({ page }) => {
  44  |     await login(page, USERS.ADM.username, USERS.ADM.password);
  45  |   });
  46  | 
  47  |   test.afterEach(async ({ page }) => {
  48  |     await logout(page);
  49  |   });
  50  | 
  51  |   // ══════════════════════════════════════════════════
  52  |   // 1. CREATE (29 TC)
  53  |   // ══════════════════════════════════════════════════
  54  | 
  55  |   test.describe('Create Jadwal', () => {
  56  | 
  57  |     test('[POSITIF] TC-JDW-CR-01: Admin buat jadwal baru data lengkap', async ({ page }) => {
  58  |       const jPage = new JadwalPage(page);
  59  |       const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL') });
  60  |       const result = await jPage.create(data);
  61  |       expect(result.status).toBe(200);
  62  |       expect(result.body.status).toBe(true);
  63  |       expect(result.body.message).toContain('berhasil disimpan');
  64  |     });
  65  | 
  66  |     test('[POSITIF] TC-JDW-CR-02: Jam mulai 00:00 (boundary)', async ({ page }) => {
  67  |       const jPage = new JadwalPage(page);
  68  |       const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '00:00', jadwal_jam_selesai: '01:00' });
  69  |       expect((await jPage.create(data)).body.status).toBe(true);
  70  |     });
  71  | 
  72  |     test('[POSITIF] TC-JDW-CR-03: Jam selesai 23:59 (boundary)', async ({ page }) => {
  73  |       const jPage = new JadwalPage(page);
  74  |       const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '22:00', jadwal_jam_selesai: '23:59' });
  75  |       expect((await jPage.create(data)).body.status).toBe(true);
  76  |     });
  77  | 
  78  |     test('[POSITIF] TC-JDW-CR-04: Jumlah peserta 1 (minimum)', async ({ page }) => {
  79  |       const jPage = new JadwalPage(page);
  80  |       const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jumPes: 1 });
  81  |       expect((await jPage.create(data)).body.status).toBe(true);
  82  |     });
  83  | 
  84  |     test('[POSITIF] TC-JDW-CR-05: Jumlah peserta besar (99999)', async ({ page }) => {
  85  |       const jPage = new JadwalPage(page);
  86  |       const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jumPes: 99999 });
  87  |       expect((await jPage.create(data)).body.status).toBe(true);
  88  |     });
  89  | 
  90  |     test('[POSITIF] TC-JDW-CR-06: Tanggal masa depan (2028)', async ({ page }) => {
  91  |       const jPage = new JadwalPage(page);
  92  |       const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_tgl: '2028-06-15' });
  93  |       expect((await jPage.create(data)).body.status).toBe(true);
  94  |     });
  95  | 
  96  |     test('[POSITIF] TC-JDW-CR-07: Tanggal masa lalu (2024)', async ({ page }) => {
  97  |       const jPage = new JadwalPage(page);
  98  |       const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_tgl: '2024-01-10' });
  99  |       expect((await jPage.create(data)).body.status).toBe(true);
  100 |     });
  101 | 
  102 |     test('[POSITIF] TC-JDW-CR-08: Multiple ruangan_ids [1,2]', async ({ page }) => {
  103 |       const jPage = new JadwalPage(page);
  104 |       const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), ruangan_ids: [1, 2] });
  105 |       expect((await jPage.create(data)).body.status).toBe(true);
  106 |     });
  107 | 
  108 |     test('[NEGATIF] TC-JDW-CR-09: Nama kosong', async ({ page }) => {
  109 |       const jPage = new JadwalPage(page);
  110 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: '' }));
  111 |       expect([422, 500]).toContain(result.status);
  112 |       expect(result.body.status).toBe(false);
  113 |       expect(result.body.msgField?.jadwal_nama || result.body.errors?.jadwal_nama).toBeDefined();
  114 |     });
  115 | 
  116 |     test('[NEGATIF] TC-JDW-CR-10: Tanggal kosong', async ({ page }) => {
  117 |       const jPage = new JadwalPage(page);
  118 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_tgl: '' }));
  119 |       expect([422, 500]).toContain(result.status);
  120 |       expect(result.body.status).toBe(false);
  121 |     });
  122 | 
  123 |     test('[NEGATIF] TC-JDW-CR-11: Jam mulai kosong', async ({ page }) => {
  124 |       const jPage = new JadwalPage(page);
  125 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_jam_mulai: '' }));
  126 |       expect([422, 500]).toContain(result.status);
  127 |       expect(result.body.status).toBe(false);
  128 |     });
  129 | 
  130 |     test('[NEGATIF] TC-JDW-CR-12: Jam selesai kosong', async ({ page }) => {
  131 |       const jPage = new JadwalPage(page);
  132 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_jam_selesai: '' }));
```