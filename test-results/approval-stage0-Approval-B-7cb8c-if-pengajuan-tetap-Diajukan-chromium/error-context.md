# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: approval-stage0.spec.js >> Approval Berjenjang — Tahap 0 (Ketua Pelaksana) >> [POSITIF] TC-APV0-02: Ketua Pelaksana setujui tahap 0 -> tahap 1 (Ketua Umum) aktif, pengajuan tetap Diajukan
- Location: modules\approval-stage0.spec.js:57:3

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
  2   |  * Test Suite: Approval Berjenjang — Tahap 0 (Ketua Pelaksana)
  3   |  *
  4   |  * Module: Pengajuan Approval (FR-6, Poin 1)
  5   |  * Service: PengajuanService::generateApprovalStages() / processApproval()
  6   |  *
  7   |  * Berbeda dari Ketua Umum/DPK/Presiden BEM/Ketua Jurusan/Wakil Direktur II
  8   |  * (posisi TETAP per organisasi, di-seed di muka lewat JabatanApprovalSeeder),
  9   |  * Ketua Pelaksana DIPILIH BEBAS per-pengajuan — siapapun dosen/mahasiswa
  10  |  * terdaftar, bisa beda orang tiap kegiatan. Baris m_jabatan_approval-nya dibuat
  11  |  * on-the-fly (find-or-create by user_id+posisi_approval) saat pengajuan dibuat
  12  |  * (lihat findOrCreateJabatanKetuaPelaksana()), BUKAN dicari dari data yang sudah
  13  |  * ada seperti 4 tahap lain — makanya tahap ini SELALU ada & SELALU aktif sejak
  14  |  * pengajuan dibuat (tidak pernah silent-skip seperti Ketua Umum saat organisasi
  15  |  * tidak match).
  16  |  *
  17  |  * Setelah poin 1: Ketua Umum (tahap 1) BARU aktif setelah tahap 0 ini disetujui —
  18  |  * lihat approval-stage1.spec.js untuk pengujian pergeseran itu.
  19  |  */
  20  | 
  21  | import { test, expect } from '@playwright/test';
  22  | import { USERS, login, logout } from '../fixtures/auth.fixture.js';
  23  | import {
  24  |   resolveApprovalFixtureIds,
  25  |   createPengajuanForApproval,
  26  |   getApprovalIdFromAntrian,
  27  |   approveKetuaPelaksana,
  28  |   processApprovalAs,
  29  |   ajaxSend,
  30  |   getTimelineHtml,
  31  |   getPengajuanStatus,
  32  | } from '../fixtures/approval.fixture.js';
  33  | 
  34  | test.describe('Approval Berjenjang — Tahap 0 (Ketua Pelaksana)', () => {
  35  |   let fx;
  36  | 
  37  |   test.beforeAll(async ({ browser }) => {
  38  |     test.setTimeout(150000);
  39  |     const page = await browser.newPage();
  40  |     fx = await resolveApprovalFixtureIds(page);
  41  |     await page.close();
  42  |   });
  43  | 
  44  |   test('[POSITIF] TC-APV0-01: Pengajuan baru langsung muncul di antrian Ketua Pelaksana yang dipilih', async ({ page }) => {
  45  |     test.setTimeout(60000);
  46  |     const { data, id } = await createPengajuanForApproval(page, fx);
  47  |     expect(id).not.toBeNull();
  48  | 
  49  |     const approvalId = await getApprovalIdFromAntrian(page, USERS.DSN, data.pengajuan_nama);
  50  |     expect(approvalId).not.toBeNull();
  51  | 
  52  |     const timeline = await getTimelineHtml(page, id);
  53  |     expect(timeline.body).toContain('Tahap 0');
  54  |     expect(timeline.body).toMatch(/Ketua Pelaksana[\s\S]*?Menunggu/);
  55  |   });
  56  | 
  57  |   test('[POSITIF] TC-APV0-02: Ketua Pelaksana setujui tahap 0 -> tahap 1 (Ketua Umum) aktif, pengajuan tetap Diajukan', async ({ page }) => {
  58  |     test.setTimeout(60000);
  59  |     const { data, id } = await createPengajuanForApproval(page, fx);
  60  | 
  61  |     const { result } = await approveKetuaPelaksana(page, data.pengajuan_nama, 'Disetujui');
  62  |     expect(result.status).toBe(200);
  63  |     expect(result.body.status).toBe(true);
> 64  |     expect(result.body.message).toContain('Tahap berikutnya telah diaktifkan');
      |                                 ^ Error: expect(received).toContain(expected) // indexOf
  65  | 
  66  |     const detail = await getPengajuanStatus(page, id);
  67  |     expect(detail).toContain('Diajukan');
  68  | 
  69  |     const timeline = await getTimelineHtml(page, id);
  70  |     expect(timeline.body).toMatch(/Ketua Pelaksana[\s\S]*?Disetujui/);
  71  |     expect(timeline.body).toContain('Tahap 1');
  72  |   });
  73  | 
  74  |   test('[POSITIF] TC-APV0-03: Ketua Pelaksana tolak tahap 0 dengan alasan -> pengajuan langsung Ditolak', async ({ page }) => {
  75  |     test.setTimeout(60000);
  76  |     const { data, id } = await createPengajuanForApproval(page, fx);
  77  | 
  78  |     const { result } = await approveKetuaPelaksana(page, data.pengajuan_nama, 'Ditolak', 'Kegiatan dibatalkan oleh Ketua Pelaksana');
  79  |     expect(result.body.status).toBe(true);
  80  | 
  81  |     const detail = await getPengajuanStatus(page, id);
  82  |     expect(detail).toContain('Ditolak');
  83  | 
  84  |     const timeline = await getTimelineHtml(page, id);
  85  |     expect(timeline.body).toMatch(/Ketua Pelaksana[\s\S]*?Ditolak/);
  86  |     expect(timeline.body).toContain('Kegiatan dibatalkan oleh Ketua Pelaksana');
  87  |   });
  88  | 
  89  |   test('[NEGATIF] TC-APV0-04: Tolak tahap 0 tanpa alasan_penolakan -> 422', async ({ page }) => {
  90  |     test.setTimeout(60000);
  91  |     const { data } = await createPengajuanForApproval(page, fx);
  92  | 
  93  |     const { result } = await approveKetuaPelaksana(page, data.pengajuan_nama, 'Ditolak', '');
  94  |     expect(result.status).toBe(422);
  95  |     expect(result.body.msgField?.alasan_penolakan).toBeDefined();
  96  |   });
  97  | 
  98  |   test('[NEGATIF] TC-APV0-05: User lain (bukan yang dipilih) coba proses tahap 0 -> 403', async ({ page }) => {
  99  |     test.setTimeout(60000);
  100 |     const { data } = await createPengajuanForApproval(page, fx);
  101 | 
  102 |     const approvalId = await getApprovalIdFromAntrian(page, USERS.DSN, data.pengajuan_nama);
  103 |     expect(approvalId).not.toBeNull();
  104 | 
  105 |     const result = await processApprovalAs(page, USERS.TDK, approvalId, 'Disetujui');
  106 |     expect(result.status).toBe(403);
  107 |     expect(result.body.message).toContain('tidak berwenang');
  108 |   });
  109 | 
  110 |   test('[NEGATIF] TC-APV0-06: Double-approve tahap 0 -> percobaan kedua 422', async ({ page }) => {
  111 |     test.setTimeout(60000);
  112 |     const { data } = await createPengajuanForApproval(page, fx);
  113 | 
  114 |     const first = await approveKetuaPelaksana(page, data.pengajuan_nama, 'Disetujui');
  115 |     expect(first.result.body.status).toBe(true);
  116 | 
  117 |     const second = await processApprovalAs(page, USERS.DSN, first.approvalId, 'Disetujui');
  118 |     expect(second.status).toBe(422);
  119 |     expect(second.body.message).toContain('sudah diproses');
  120 |   });
  121 | 
  122 |   test('[POSITIF] TC-APV0-07: Pilih orang yang sama sebagai Ketua Pelaksana di 2 pengajuan berbeda -> tetap satu baris m_jabatan_approval (tidak dobel)', async ({ page }) => {
  123 |     test.setTimeout(90000);
  124 |     const first = await createPengajuanForApproval(page, fx);
  125 |     const second = await createPengajuanForApproval(page, fx);
  126 |     expect(first.id).not.toBeNull();
  127 |     expect(second.id).not.toBeNull();
  128 | 
  129 |     // Keduanya pakai fixtureIds.ketuaPelaksanaUserId yang sama (default createPengajuanForApproval).
  130 |     // Kalau baris m_jabatan_approval dobel, approval_id tahap 0 kedua pengajuan akan berbeda
  131 |     // JAUH (tidak bisa diprediksi dari 1 baris jabatan yang sama) — cukup pastikan keduanya
  132 |     // tetap bisa ditemukan & diproses lewat identitas login yang sama (USERS.DSN), yang hanya
  133 |     // mungkin kalau keduanya reuse baris jabatan yang sama (unique index user_id+posisi_approval
  134 |     // mencegah duplikasi di findOrCreateJabatanKetuaPelaksana()).
  135 |     const approvalIdFirst = await getApprovalIdFromAntrian(page, USERS.DSN, first.data.pengajuan_nama);
  136 |     const approvalIdSecond = await getApprovalIdFromAntrian(page, USERS.DSN, second.data.pengajuan_nama);
  137 |     expect(approvalIdFirst).not.toBeNull();
  138 |     expect(approvalIdSecond).not.toBeNull();
  139 |     expect(approvalIdFirst).not.toBe(approvalIdSecond); // beda baris t_pengajuan_approval...
  140 | 
  141 |     // ...tapi keduanya menunjuk ke jabatan_id yang SAMA (reuse) — dibuktikan lewat approve
  142 |     // keduanya sebagai user yang sama tanpa perlu resolve jabatan_id berbeda.
  143 |     const approveFirst = await processApprovalAs(page, USERS.DSN, approvalIdFirst, 'Disetujui');
  144 |     const approveSecond = await processApprovalAs(page, USERS.DSN, approvalIdSecond, 'Disetujui');
  145 |     expect(approveFirst.body.status).toBe(true);
  146 |     expect(approveSecond.body.status).toBe(true);
  147 |   });
  148 | 
  149 |   test('[NEGATIF] TC-APV0-08: ketua_pelaksana_user_id kosong saat create pengajuan -> 422', async ({ page }) => {
  150 |     test.setTimeout(60000);
  151 |     await login(page, USERS.MHS.username, USERS.MHS.password);
  152 |     const result = await ajaxSend(page, '/pengajuan/ajax', 'POST', {
  153 |       pengajuan_nama: 'APV0-NoKetuaPelaksana',
  154 |       pengajuan_tgl: '2026-09-01',
  155 |       pengajuan_jam_mulai: '08:00',
  156 |       pengajuan_jam_selesai: '10:00',
  157 |       pengajuan_jumPes: 10,
  158 |       organisasi_id: fx.hmjOrgId,
  159 |       ketua_pelaksana_user_id: '',
  160 |       ruangan_ids: [fx.ruanganJurusanId],
  161 |     });
  162 |     expect(result.status).toBe(422);
  163 |     expect(result.body.status).toBe(false);
  164 |     expect(result.body.msgField?.ketua_pelaksana_user_id).toBeDefined();
```