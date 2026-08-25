# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: approval-stage3.spec.js >> Approval Berjenjang — Tahap 2-4 (Mahasiswa: Presiden BEM -> DPK -> Ketua Jurusan) >> [EDGE] TC-APV3-08: Tahap 3 (DPK) diproses sebelum tahap 2 (Presiden BEM) disetujui -> tetap diproses (tidak ada guard urutan eksplisit)
- Location: modules\approval-stage3.spec.js:168:3

# Error details

```
Error: expect(received).toBe(expected) // Object.is equality

Expected: 200
Received: 422
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
  82  |     test.setTimeout(120000);
  83  |     const { data, id } = await createAndApproveThroughStage3(page, fx);
  84  | 
  85  |     const { result } = await approveAsVerifikator(page, VRF_USERS.KETUA_JURUSAN, data.pengajuan_nama, 'Disetujui');
  86  |     expect(result.status).toBe(200);
  87  |     expect(result.body.status).toBe(true);
  88  |     expect(result.body.message).toContain('diterima');
  89  | 
  90  |     const detail = await getPengajuanStatus(page, id);
  91  |     expect(detail).toContain('Diterima');
  92  |     expect(detail).toContain('cetak_surat_ajax');
  93  | 
  94  |     const timeline = await getTimelineHtml(page, id);
  95  |     expect(timeline.body).toMatch(/Tahap 4[\s\S]*?Ketua Jurusan[\s\S]*?Disetujui/);
  96  |   });
  97  | 
  98  |   test('[POSITIF] TC-APV3-03: Presiden BEM tolak tahap 2 -> pengajuan langsung Ditolak, DPK tidak pernah aktif', async ({ page }) => {
  99  |     test.setTimeout(90000);
  100 |     const { data, id } = await createPengajuanForApproval(page, fx);
  101 |     const ids = await computeStageApprovalIds(page, data.pengajuan_nama);
  102 |     expect(ids).not.toBeNull();
  103 | 
  104 |     const stage0 = await processApprovalAs(page, USERS.DSN, ids.stage0KetuaPelaksana, 'Disetujui');
  105 |     expect(stage0.body.status).toBe(true);
  106 |     const stage1 = await processApprovalAs(page, VRF_USERS.KETUA_UMUM, ids.stage1KetuaUmum, 'Disetujui');
  107 |     expect(stage1.body.status).toBe(true);
  108 | 
  109 |     const { result } = await approveAsVerifikator(page, VRF_USERS.PRESIDEN_BEM, data.pengajuan_nama, 'Ditolak', 'Bertentangan dengan agenda BEM');
  110 |     expect(result.body.status).toBe(true);
  111 | 
  112 |     const detail = await getPengajuanStatus(page, id);
  113 |     expect(detail).toContain('Ditolak');
  114 | 
  115 |     const timeline = await getTimelineHtml(page, id);
  116 |     expect(timeline.body).toMatch(/Presiden BEM[\s\S]*?Ditolak/);
  117 |     // Baris tahap 3 (DPK) SUDAH ADA di timeline sejak awal (semua baris dibuat di
  118 |     // muka oleh generateApprovalStages) — yang dipastikan di sini adalah statusnya
  119 |     // TETAP 'Menunggu' (tidak pernah diaktifkan/diproses), bukan ketiadaan teksnya.
  120 |     expect(timeline.body).toMatch(/Tahap 3[\s\S]*?DPK[\s\S]*?Menunggu/);
  121 |   });
  122 | 
  123 |   test('[POSITIF] TC-APV3-04: DPK tolak tahap 3 -> pengajuan Ditolak, tidak ada Cetak Surat', async ({ page }) => {
  124 |     test.setTimeout(120000);
  125 |     const { data, id } = await createPengajuanForApproval(page, fx);
  126 |     const ids = await computeStageApprovalIds(page, data.pengajuan_nama);
  127 |     expect(ids).not.toBeNull();
  128 | 
  129 |     await processApprovalAs(page, USERS.DSN, ids.stage0KetuaPelaksana, 'Disetujui');
  130 |     await processApprovalAs(page, VRF_USERS.KETUA_UMUM, ids.stage1KetuaUmum, 'Disetujui');
  131 |     await processApprovalAs(page, VRF_USERS.PRESIDEN_BEM, ids.stage2PresidenBem, 'Disetujui');
  132 | 
  133 |     const { result } = await approveAsVerifikator(page, VRF_USERS.DPK, data.pengajuan_nama, 'Ditolak', 'Jadwal bentrok agenda internal DPK');
  134 |     expect(result.body.status).toBe(true);
  135 | 
  136 |     const detail = await getPengajuanStatus(page, id);
  137 |     expect(detail).toContain('Ditolak');
  138 |     expect(detail).not.toContain('cetak_surat_ajax');
  139 |   });
  140 | 
  141 |   test('[POSITIF] TC-APV3-05: Ketua Jurusan tolak tahap akhir -> pengajuan Ditolak, tidak ada Cetak Surat', async ({ page }) => {
  142 |     test.setTimeout(120000);
  143 |     const { data, id } = await createAndApproveThroughStage3(page, fx);
  144 | 
  145 |     const { result } = await approveAsVerifikator(page, VRF_USERS.KETUA_JURUSAN, data.pengajuan_nama, 'Ditolak', 'Ruangan sedang dijadwalkan untuk sidang jurusan');
  146 |     expect(result.body.status).toBe(true);
  147 | 
  148 |     const detail = await getPengajuanStatus(page, id);
  149 |     expect(detail).toContain('Ditolak');
  150 |     expect(detail).not.toContain('cetak_surat_ajax');
  151 |   });
  152 | 
  153 |   test('[NEGATIF] TC-APV3-06: Presiden BEM coba proses approval_id tahap 3 (milik DPK) -> 403', async ({ page }) => {
  154 |     test.setTimeout(90000);
  155 |     const { ids } = await createAndApproveThroughStage3(page, fx); // sudah lunas s/d DPK, dipakai hanya utk ids
  156 |     const result = await processApprovalAs(page, VRF_USERS.PRESIDEN_BEM, ids.stage3Dpk, 'Disetujui');
  157 |     expect(result.status).toBe(403);
  158 |     expect(result.body.message).toContain('tidak berwenang');
  159 |   });
  160 | 
  161 |   test('[NEGATIF] TC-APV3-07: DPK coba proses approval_id tahap 4 (milik Ketua Jurusan) -> 403', async ({ page }) => {
  162 |     test.setTimeout(90000);
  163 |     const { ids } = await createAndApproveThroughStage3(page, fx);
  164 |     const result = await processApprovalAs(page, VRF_USERS.DPK, ids.stage4KetuaJurusan, 'Disetujui');
  165 |     expect(result.status).toBe(403);
  166 |   });
  167 | 
  168 |   test('[EDGE] TC-APV3-08: Tahap 3 (DPK) diproses sebelum tahap 2 (Presiden BEM) disetujui -> tetap diproses (tidak ada guard urutan eksplisit)', async ({ page }) => {
  169 |     test.setTimeout(90000);
  170 |     const { data } = await createPengajuanForApproval(page, fx);
  171 |     const ids = await computeStageApprovalIds(page, data.pengajuan_nama);
  172 |     expect(ids).not.toBeNull();
  173 | 
  174 |     await processApprovalAs(page, USERS.DSN, ids.stage0KetuaPelaksana, 'Disetujui');
  175 |     await processApprovalAs(page, VRF_USERS.KETUA_UMUM, ids.stage1KetuaUmum, 'Disetujui');
  176 |     // Sengaja TIDAK memproses tahap 2 (Presiden BEM) sama sekali.
  177 | 
  178 |     // Baris tahap 3 sudah dibuat sejak awal (hanya batas_waktu-nya null / belum "aktif"
  179 |     // di UI antrian), dan processApproval() tidak memvalidasi urutan_tahap sebelumnya —
  180 |     // hanya ownership + status_approval == 'Menunggu'.
  181 |     const result = await processApprovalAs(page, VRF_USERS.DPK, ids.stage3Dpk, 'Disetujui');
> 182 |     expect(result.status).toBe(200);
      |                           ^ Error: expect(received).toBe(expected) // Object.is equality
  183 |     expect(result.body.status).toBe(true);
  184 |   });
  185 | 
  186 |   test('[NEGATIF] TC-APV3-09: Double-approve tahap akhir -> percobaan kedua 422', async ({ page }) => {
  187 |     test.setTimeout(120000);
  188 |     const { data } = await createAndApproveThroughStage3(page, fx);
  189 | 
  190 |     const first = await approveAsVerifikator(page, VRF_USERS.KETUA_JURUSAN, data.pengajuan_nama, 'Disetujui');
  191 |     expect(first.result.body.status).toBe(true);
  192 | 
  193 |     const second = await processApprovalAs(page, VRF_USERS.KETUA_JURUSAN, first.approvalId, 'Disetujui');
  194 |     expect(second.status).toBe(422);
  195 |     expect(second.body.message).toContain('sudah diproses');
  196 |   });
  197 | 
  198 |   test('[NEGATIF] TC-APV3-10: Tolak tahap akhir tanpa alasan_penolakan -> 422', async ({ page }) => {
  199 |     test.setTimeout(120000);
  200 |     const { data } = await createAndApproveThroughStage3(page, fx);
  201 | 
  202 |     const { result } = await approveAsVerifikator(page, VRF_USERS.KETUA_JURUSAN, data.pengajuan_nama, 'Ditolak', '');
  203 |     expect(result.status).toBe(422);
  204 |     expect(result.body.msgField?.alasan_penolakan).toBeDefined();
  205 |   });
  206 | });
  207 | 
  208 | test.describe('Approval Berjenjang — Alur Pendek Dosen/Tendik (Ketua Pelaksana -> Ketua Jurusan)', () => {
  209 |   let fx;
  210 | 
  211 |   test.beforeAll(async ({ browser }) => {
  212 |     test.setTimeout(150000);
  213 |     const page = await browser.newPage();
  214 |     fx = await resolveApprovalFixtureIds(page);
  215 |     await page.close();
  216 |   });
  217 | 
  218 |   test('[POSITIF] TC-APV3-11: Pemohon Dosen -> hanya 2 tahap dibuat (Ketua Pelaksana, Ketua Jurusan), tanpa Ketua Umum/Presiden BEM/DPK', async ({ page }) => {
  219 |     test.setTimeout(90000);
  220 |     const { id } = await createPengajuanForApproval(page, fx, {}, USERS.DSN);
  221 |     expect(id).not.toBeNull();
  222 | 
  223 |     const timeline = await getTimelineHtml(page, id, USERS.DSN);
  224 |     expect(timeline.body).toContain('Ketua Pelaksana');
  225 |     expect(timeline.body).toContain('Ketua Jurusan');
  226 |     expect(timeline.body).not.toContain('Ketua Umum');
  227 |     expect(timeline.body).not.toContain('Presiden BEM');
  228 |     expect(timeline.body).not.toContain('DPK');
  229 |   });
  230 | 
  231 |   test('[POSITIF] TC-APV3-12: Pemohon Dosen — Ketua Pelaksana lalu Ketua Jurusan setujui -> pengajuan langsung Diterima', async ({ page }) => {
  232 |     test.setTimeout(90000);
  233 |     const { data, id } = await createPengajuanForApproval(page, fx, {}, USERS.DSN);
  234 |     const ids = await computeShortStageApprovalIds(page, data.pengajuan_nama);
  235 |     expect(ids).not.toBeNull();
  236 | 
  237 |     const stage0 = await processApprovalAs(page, USERS.DSN, ids.stage0KetuaPelaksana, 'Disetujui');
  238 |     expect(stage0.body.status).toBe(true);
  239 | 
  240 |     const stage1 = await processApprovalAs(page, VRF_USERS.KETUA_JURUSAN, ids.stage1KetuaJurusan, 'Disetujui');
  241 |     expect(stage1.body.status).toBe(true);
  242 |     expect(stage1.body.message).toContain('diterima');
  243 | 
  244 |     const detail = await getPengajuanStatus(page, id);
  245 |     expect(detail).toContain('Diterima');
  246 |     expect(detail).toContain('cetak_surat_ajax');
  247 |   });
  248 | 
  249 |   test('[POSITIF] TC-APV3-13: Pemohon Tendik -> alur pendek yang sama (Ketua Pelaksana -> Ketua Jurusan)', async ({ page }) => {
  250 |     test.setTimeout(90000);
  251 |     const { data, id } = await createPengajuanForApproval(page, fx, {}, USERS.TDK);
  252 |     const ids = await computeShortStageApprovalIds(page, data.pengajuan_nama);
  253 |     expect(ids).not.toBeNull();
  254 | 
  255 |     await processApprovalAs(page, USERS.DSN, ids.stage0KetuaPelaksana, 'Disetujui');
  256 |     const stage1 = await processApprovalAs(page, VRF_USERS.KETUA_JURUSAN, ids.stage1KetuaJurusan, 'Disetujui');
  257 |     expect(stage1.body.status).toBe(true);
  258 | 
  259 |     const detail = await getPengajuanStatus(page, id);
  260 |     expect(detail).toContain('Diterima');
  261 |   });
  262 | 
  263 |   test('[NEGATIF] TC-APV3-14: Pemohon Dosen — Ketua Jurusan tolak -> pengajuan Ditolak', async ({ page }) => {
  264 |     test.setTimeout(90000);
  265 |     const { data, id } = await createPengajuanForApproval(page, fx, {}, USERS.DSN);
  266 | 
  267 |     await approveKetuaPelaksana(page, data.pengajuan_nama, 'Disetujui');
  268 |     const { result } = await approveAsVerifikator(page, VRF_USERS.KETUA_JURUSAN, data.pengajuan_nama, 'Ditolak', 'Ruangan tidak tersedia pada tanggal tersebut');
  269 |     expect(result.body.status).toBe(true);
  270 | 
  271 |     const detail = await getPengajuanStatus(page, id);
  272 |     expect(detail).toContain('Ditolak');
  273 |   });
  274 | });
  275 | 
```