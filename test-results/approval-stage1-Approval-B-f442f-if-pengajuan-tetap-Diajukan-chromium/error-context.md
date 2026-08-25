# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: approval-stage1.spec.js >> Approval Berjenjang — Tahap 1 (Ketua Umum) >> [POSITIF] TC-APV1-02: Ketua Umum setujui tahap 1 -> tahap 2 (Presiden BEM) aktif, pengajuan tetap Diajukan
- Location: modules\approval-stage1.spec.js:73:3

# Error details

```
Error: expect(received).toContain(expected) // indexOf

Expected substring: "Tahap berikutnya telah diaktifkan"
Received string:    "Pengajuan berhasil diterima. Pengajuan telah dikirimkan ke tahap berikutnya."
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
  2   |  * Test Suite: Approval Berjenjang — Tahap 1 (Ketua Umum)
  3   |  *
  4   |  * Module: Pengajuan Approval (FR-6)
  5   |  * Controller: PengajuanController::antrian_ajax / proses_approval_ajax
  6   |  * Service: PengajuanService::processApproval() / generateApprovalStages()
  7   |  *
  8   |  * Sejak poin 1 (tahap "Ketua Pelaksana"), Ketua Umum BUKAN lagi tahap yang aktif
  9   |  * duluan — dia menunggu tahap 0 (Ketua Pelaksana) disetujui dulu (lihat
  10  |  * generateApprovalStages(): batas_waktu Ketua Umum selalu null saat dibuat, baru
  11  |  * diaktifkan oleh processApproval() setelah tahap 0 selesai). Setiap test di sini
  12  |  * yang butuh tahap Ketua Umum aktif memakai helper `createAndConfirmKetuaPelaksana()`
  13  |  * di bawah, bukan `createPengajuanForApproval()` polos.
  14  |  *
  15  |  * Tahap 1 sendiri baru dibuat sama sekali jika ada jabatan dengan posisi_approval=
  16  |  * 'Ketua Umum' DAN organisasi_id sama dengan organisasi_id pengajuan (lihat
  17  |  * generateApprovalStages() di app/Services/PengajuanService.php). Kalau tidak ada
  18  |  * yang cocok, tahap ini dilewati diam-diam.
  19  |  *
  20  |  * Fixture: HMJ (organisasi_id dari resolveApprovalFixtureIds) -> jabatan Ketua Umum
  21  |  * (lihat database/seeders/JabatanApprovalSeeder.php).
  22  |  */
  23  | 
  24  | import { test, expect } from '@playwright/test';
  25  | import { USERS, login, logout } from '../fixtures/auth.fixture.js';
  26  | import {
  27  |   VRF_USERS,
  28  |   resolveApprovalFixtureIds,
  29  |   createPengajuanForApproval,
  30  |   getApprovalIdFromAntrian,
  31  |   approveAsVerifikator,
  32  |   approveKetuaPelaksana,
  33  |   processApprovalAs,
  34  |   getTimelineHtml,
  35  |   getPengajuanStatus,
  36  | } from '../fixtures/approval.fixture.js';
  37  | 
  38  | test.describe('Approval Berjenjang — Tahap 1 (Ketua Umum)', () => {
  39  |   let fx;
  40  | 
  41  |   test.beforeAll(async ({ browser }) => {
  42  |     test.setTimeout(150000);
  43  |     const page = await browser.newPage();
  44  |     fx = await resolveApprovalFixtureIds(page);
  45  |     await page.close();
  46  |   });
  47  | 
  48  |   async function createAndConfirmKetuaPelaksana(page, overrides = {}) {
  49  |     const created = await createPengajuanForApproval(page, fx, overrides);
  50  |     const stage0 = await approveKetuaPelaksana(page, created.data.pengajuan_nama, 'Disetujui');
  51  |     expect(stage0.result.body.status).toBe(true);
  52  |     return created;
  53  |   }
  54  | 
  55  |   test('[POSITIF] TC-APV1-01: Setelah Ketua Pelaksana disetujui, pengajuan muncul di antrian Ketua Umum', async ({ page }) => {
  56  |     test.setTimeout(60000);
  57  |     const { data, id } = await createAndConfirmKetuaPelaksana(page);
  58  |     expect(id).not.toBeNull();
  59  | 
  60  |     const approvalId = await getApprovalIdFromAntrian(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama);
  61  |     expect(approvalId).not.toBeNull();
  62  |   });
  63  | 
  64  |   test('[POSITIF] TC-APV1-01b: Sebelum Ketua Pelaksana disetujui, Ketua Umum belum melihat pengajuan di antriannya', async ({ page }) => {
  65  |     test.setTimeout(60000);
  66  |     const { data, id } = await createPengajuanForApproval(page, fx);
  67  |     expect(id).not.toBeNull();
  68  | 
  69  |     const approvalId = await getApprovalIdFromAntrian(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama);
  70  |     expect(approvalId).toBeNull();
  71  |   });
  72  | 
  73  |   test('[POSITIF] TC-APV1-02: Ketua Umum setujui tahap 1 -> tahap 2 (Presiden BEM) aktif, pengajuan tetap Diajukan', async ({ page }) => {
  74  |     test.setTimeout(60000);
  75  |     const { data, id } = await createAndConfirmKetuaPelaksana(page);
  76  | 
  77  |     const { result } = await approveAsVerifikator(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama, 'Disetujui');
  78  |     expect(result.status).toBe(200);
  79  |     expect(result.body.status).toBe(true);
> 80  |     expect(result.body.message).toContain('Tahap berikutnya telah diaktifkan');
      |                                 ^ Error: expect(received).toContain(expected) // indexOf
  81  | 
  82  |     const detail = await getPengajuanStatus(page, id);
  83  |     expect(detail).toContain('Diajukan');
  84  | 
  85  |     const timeline = await getTimelineHtml(page, id);
  86  |     expect(timeline.body).toContain('Tahap 1');
  87  |     expect(timeline.body).toMatch(/Ketua Umum[\s\S]*?Disetujui/);
  88  |     // Sejak alur jadi sekuensial, tahap 2 HANYA berisi Presiden BEM (bukan lagi
  89  |     // paralel dengan DPK) — DPK baru muncul di tahap 3. Baris tahap 3/4 SUDAH ADA
  90  |     // di timeline sejak awal (generateApprovalStages membuat semua baris di muka),
  91  |     // jadi yang dicek di sini adalah STATUSNYA ('Menunggu'), bukan ketiadaannya.
  92  |     expect(timeline.body).toContain('Tahap 2');
  93  |     expect(timeline.body).toMatch(/Tahap 2[\s\S]*?Presiden BEM[\s\S]*?Menunggu/);
  94  |     expect(timeline.body).toMatch(/Tahap 3[\s\S]*?DPK[\s\S]*?Menunggu/);
  95  |   });
  96  | 
  97  |   test('[POSITIF] TC-APV1-03: Ketua Umum tolak tahap 1 dengan alasan -> pengajuan langsung Ditolak', async ({ page }) => {
  98  |     test.setTimeout(60000);
  99  |     const { data, id } = await createAndConfirmKetuaPelaksana(page);
  100 | 
  101 |     const { result } = await approveAsVerifikator(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama, 'Ditolak', 'Kegiatan tidak sesuai ketentuan organisasi');
  102 |     expect(result.status).toBe(200);
  103 |     expect(result.body.status).toBe(true);
  104 | 
  105 |     const detail = await getPengajuanStatus(page, id);
  106 |     expect(detail).toContain('Ditolak');
  107 | 
  108 |     const timeline = await getTimelineHtml(page, id);
  109 |     expect(timeline.body).toMatch(/Ketua Umum[\s\S]*?Ditolak/);
  110 |     expect(timeline.body).toContain('Kegiatan tidak sesuai ketentuan organisasi');
  111 |   });
  112 | 
  113 |   test('[NEGATIF] TC-APV1-04: Tolak tahap 1 tanpa alasan_penolakan -> 422', async ({ page }) => {
  114 |     test.setTimeout(60000);
  115 |     const { data } = await createAndConfirmKetuaPelaksana(page);
  116 | 
  117 |     const { result } = await approveAsVerifikator(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama, 'Ditolak', '');
  118 |     expect(result.status).toBe(422);
  119 |     expect(result.body.status).toBe(false);
  120 |     expect(result.body.msgField?.alasan_penolakan).toBeDefined();
  121 |   });
  122 | 
  123 |   test('[NEGATIF] TC-APV1-05: Verifikator lain (DPK) coba proses approval_id milik Ketua Umum -> 403', async ({ page }) => {
  124 |     test.setTimeout(60000);
  125 |     const { data } = await createAndConfirmKetuaPelaksana(page);
  126 | 
  127 |     const approvalId = await getApprovalIdFromAntrian(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama);
  128 |     expect(approvalId).not.toBeNull();
  129 | 
  130 |     const result = await processApprovalAs(page, VRF_USERS.DPK, approvalId, 'Disetujui');
  131 |     expect(result.status).toBe(403);
  132 |     expect(result.body.status).toBe(false);
  133 |     expect(result.body.message).toContain('tidak berwenang');
  134 |   });
  135 | 
  136 |   test('[NEGATIF] TC-APV1-06: Double-approve tahap 1 -> percobaan kedua 422', async ({ page }) => {
  137 |     test.setTimeout(60000);
  138 |     const { data } = await createAndConfirmKetuaPelaksana(page);
  139 | 
  140 |     const first = await approveAsVerifikator(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama, 'Disetujui');
  141 |     expect(first.result.body.status).toBe(true);
  142 | 
  143 |     const approvalId = first.approvalId;
  144 |     const second = await processApprovalAs(page, VRF_USERS.KETUA_UMUM, approvalId, 'Disetujui');
  145 |     expect(second.status).toBe(422);
  146 |     expect(second.body.status).toBe(false);
  147 |     expect(second.body.message).toContain('sudah diproses');
  148 |   });
  149 | 
  150 |   test('[NEGATIF] TC-APV1-07: status_approval tidak valid -> 422', async ({ page }) => {
  151 |     test.setTimeout(60000);
  152 |     const { data } = await createAndConfirmKetuaPelaksana(page);
  153 |     const approvalId = await getApprovalIdFromAntrian(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama);
  154 | 
  155 |     const result = await processApprovalAs(page, VRF_USERS.KETUA_UMUM, approvalId, 'StatusAsal');
  156 |     expect(result.status).toBe(422);
  157 |     expect(result.body.status).toBe(false);
  158 |     expect(result.body.message).toContain('tidak valid');
  159 |   });
  160 | 
  161 |   test('[NEGATIF] TC-APV1-08: approval_id tidak ditemukan -> 404', async ({ page }) => {
  162 |     test.setTimeout(45000);
  163 |     const result = await processApprovalAs(page, VRF_USERS.KETUA_UMUM, 9999999, 'Disetujui');
  164 |     expect(result.status).toBe(404);
  165 |     expect(result.body.status).toBe(false);
  166 |   });
  167 | 
  168 |   test('[NEGATIF] TC-APV1-09: User tanpa jabatan approval sama sekali (ADM/MHS/TDK) bisa akses antrian_ajax tapi antriannya kosong', async ({ page }) => {
  169 |     test.setTimeout(60000);
  170 |     // Sejak redesain (m_jabatan_approval menggantikan level VRF terpisah — jabatan
  171 |     // menempel ke dosen/mahasiswa yang sudah ada), route ini tidak lagi digerbangi per
  172 |     // role: authorize:ADM,DSN,TDK,MHS (siapapun yang login boleh masuk). Otorisasi
  173 |     // SEBENARNYA (siapa boleh memproses baris approval mana) ditegakkan lebih dalam di
  174 |     // PengajuanService::processApproval() lewat ownership jabatanApproval->user_id,
  175 |     // dan antrianFor() otomatis balikin collection kosong untuk user tanpa jabatan.
  176 |     // USERS.DSN SENGAJA tidak diikutkan di sini — sejak poin 1, akun itu dipakai sebagai
  177 |     // Ketua Pelaksana default (fixtureIds.ketuaPelaksanaUserId), jadi ia SELALU punya
  178 |     // jabatan approval & antriannya bisa saja tidak kosong (tergantung test lain yang
  179 |     // berjalan sebelumnya) — bukan lagi representasi "user tanpa jabatan".
  180 |     for (const user of [USERS.ADM, USERS.MHS, USERS.TDK]) {
```