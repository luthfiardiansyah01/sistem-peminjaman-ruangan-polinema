# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: ruangan-availability.spec.js >> Status Ruangan Per-Tanggal (Poin 2) >> [POSITIF] TC-AV-05: Ruangan dengan Jadwal aktif (pengajuan lolos semua tahap approval) -> Tidak Tersedia
- Location: modules\ruangan-availability.spec.js:151:3

# Error details

```
Error: expect(received).not.toBeNull()

Received: null
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
  58  | }
  59  | 
  60  | async function ajaxGetHtml(page, url) {
  61  |   await ensureJqueryContext(page);
  62  |   return page.evaluate((requestUrl) => {
  63  |     return new Promise((resolve) => {
  64  |       $.ajax({
  65  |         url: requestUrl,
  66  |         type: 'GET',
  67  |         dataType: 'html',
  68  |         success: (response) => resolve({ status: 200, body: String(response) }),
  69  |         error: (xhr) => resolve({ status: xhr.status, body: xhr.responseText || '' }),
  70  |       });
  71  |     });
  72  |   }, url);
  73  | }
  74  | 
  75  | async function ajaxSend(page, url, method, data = {}) {
  76  |   await ensureJqueryContext(page);
  77  |   const token = await page.evaluate(() => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
  78  |   const payload = { ...data, _token: token };
  79  |   if (method === 'PUT' || method === 'DELETE') payload._method = method;
  80  | 
  81  |   return page.evaluate(({ url, payload }) => {
  82  |     return new Promise((resolve) => {
  83  |       $.ajax({
  84  |         url, type: 'POST', data: payload, dataType: 'json',
  85  |         success: (response) => resolve({ status: 200, body: response }),
  86  |         error: (xhr) => {
  87  |           try {
  88  |             resolve({ status: xhr.status, body: JSON.parse(xhr.responseText) });
  89  |           } catch (e) {
  90  |             resolve({ status: xhr.status, body: { status: false, message: xhr.statusText } });
  91  |           }
  92  |         },
  93  |       });
  94  |     });
  95  |   }, { url, payload });
  96  | }
  97  | 
  98  | test.describe('Status Ruangan Per-Tanggal (Poin 2)', () => {
  99  |   let fx;
  100 | 
  101 |   test.beforeAll(async ({ browser }) => {
  102 |     const page = await browser.newPage();
  103 |     fx = await resolveApprovalFixtureIds(page);
  104 |     await page.close();
  105 |   });
  106 | 
  107 |   test('[NEGATIF] TC-AV-01: availability_ajax tanpa parameter tanggal -> 422', async ({ page }) => {
  108 |     test.setTimeout(45000);
  109 |     await login(page, USERS.ADM.username, USERS.ADM.password);
  110 |     const result = await ajaxGetJson(page, '/ruangan/availability_ajax');
  111 |     expect(result.status).toBe(422);
  112 |     await logout(page);
  113 |   });
  114 | 
  115 |   test('[NEGATIF] TC-AV-02: availability_ajax dengan tanggal tidak valid -> 422', async ({ page }) => {
  116 |     test.setTimeout(45000);
  117 |     await login(page, USERS.ADM.username, USERS.ADM.password);
  118 |     const result = await ajaxGetJson(page, '/ruangan/availability_ajax?tanggal=bukan-tanggal');
  119 |     expect(result.status).toBe(422);
  120 |     await logout(page);
  121 |   });
  122 | 
  123 |   test('[POSITIF] TC-AV-03: Ruangan tanpa pengajuan/jadwal di tanggal kosong -> Tersedia', async ({ page }) => {
  124 |     test.setTimeout(45000);
  125 |     await login(page, USERS.ADM.username, USERS.ADM.password);
  126 |     // Tanggal jauh di masa depan, hampir pasti belum ada booking apapun.
  127 |     const result = await ajaxGetJson(page, '/ruangan/availability_ajax?tanggal=2030-01-15');
  128 |     expect(result.status).toBe(200);
  129 |     expect(Array.isArray(result.body)).toBe(true);
  130 |     const ruanganJurusan = result.body.find((r) => r.ruangan_id === fx.ruanganJurusanId);
  131 |     expect(ruanganJurusan.status).toBe('Tersedia');
  132 |     await logout(page);
  133 |   });
  134 | 
  135 |   test('[POSITIF] TC-AV-04: Ruangan dengan pengajuan status Diajukan di tanggal itu -> Diajukan', async ({ page }) => {
  136 |     test.setTimeout(60000);
  137 |     const tanggal = '2030-02-10';
  138 |     const { data } = await createPengajuanForApproval(page, fx, {
  139 |       pengajuan_tgl: tanggal,
  140 |       ruangan_ids: [fx.ruanganJurusanId],
  141 |     });
  142 |     expect(data.pengajuan_tgl).toBe(tanggal);
  143 | 
  144 |     await login(page, USERS.ADM.username, USERS.ADM.password);
  145 |     const result = await ajaxGetJson(page, `/ruangan/availability_ajax?tanggal=${tanggal}`);
  146 |     const ruanganJurusan = result.body.find((r) => r.ruangan_id === fx.ruanganJurusanId);
  147 |     expect(ruanganJurusan.status).toBe('Diajukan');
  148 |     await logout(page);
  149 |   });
  150 | 
  151 |   test('[POSITIF] TC-AV-05: Ruangan dengan Jadwal aktif (pengajuan lolos semua tahap approval) -> Tidak Tersedia', async ({ page }) => {
  152 |     test.setTimeout(150000);
  153 |     const tanggal = '2030-03-20';
  154 |     const { data, id } = await createPengajuanForApproval(page, fx, {
  155 |       pengajuan_tgl: tanggal,
  156 |       ruangan_ids: [fx.ruanganJurusanId],
  157 |     });
> 158 |     expect(id).not.toBeNull();
      |                    ^ Error: expect(received).not.toBeNull()
  159 | 
  160 |     const s0 = await approveKetuaPelaksana(page, data.pengajuan_nama, 'Disetujui');
  161 |     expect(s0.result.body.status).toBe(true);
  162 |     const s1 = await approveAsVerifikator(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama, 'Disetujui');
  163 |     expect(s1.result.body.status).toBe(true);
  164 |     const s2a = await approveAsVerifikator(page, VRF_USERS.DPK, data.pengajuan_nama, 'Disetujui');
  165 |     expect(s2a.result.body.status).toBe(true);
  166 |     const s2b = await approveAsVerifikator(page, VRF_USERS.PRESIDEN_BEM, data.pengajuan_nama, 'Disetujui');
  167 |     expect(s2b.result.body.status).toBe(true);
  168 |     const s3 = await approveAsVerifikator(page, VRF_USERS.KETUA_JURUSAN, data.pengajuan_nama, 'Disetujui');
  169 |     expect(s3.result.body.message).toContain('diterima');
  170 | 
  171 |     await login(page, USERS.ADM.username, USERS.ADM.password);
  172 |     const result = await ajaxGetJson(page, `/ruangan/availability_ajax?tanggal=${tanggal}`);
  173 |     const ruanganJurusan = result.body.find((r) => r.ruangan_id === fx.ruanganJurusanId);
  174 |     expect(ruanganJurusan.status).toBe('Tidak Tersedia');
  175 | 
  176 |     // kalender_data_ajax bulan yang sama harus konsisten dengan availability_ajax.
  177 |     const kalender = await ajaxGetJson(page, `/ruangan/${fx.ruanganJurusanId}/kalender_data_ajax?bulan=2030-03`);
  178 |     expect(kalender.status).toBe(200);
  179 |     expect(kalender.body[tanggal]).toBe('Tidak Tersedia');
  180 |     await logout(page);
  181 |   });
  182 | 
  183 |   test('[POSITIF] TC-AV-06: ruangan_status Tidak Tersedia (override manual) -> selalu Tidak Tersedia walau tanggal kosong', async ({ page }) => {
  184 |     test.setTimeout(60000);
  185 |     await login(page, USERS.ADM.username, USERS.ADM.password);
  186 | 
  187 |     const before = await ajaxGetJson(page, '/ruangan/availability_ajax?tanggal=2030-04-01');
  188 |     const ruanganUmumSebelum = before.body.find((r) => r.ruangan_id === fx.ruanganUmumId);
  189 |     expect(ruanganUmumSebelum.status).toBe('Tersedia');
  190 | 
  191 |     const update = await ajaxSend(page, `/ruangan/${fx.ruanganUmumId}/update_ajax`, 'PUT', {
  192 |       ruangan_kode: 'RUM',
  193 |       ruangan_nama: 'Ruangan Umum E2E',
  194 |       ruangan_fasilitas: 'AC, proyektor',
  195 |       ruangan_kuota: 50,
  196 |       ruangan_kategori: 'Umum',
  197 |       ruangan_status: 'Tidak Tersedia',
  198 |     });
  199 |     expect(update.body.status).toBe(true);
  200 | 
  201 |     const after = await ajaxGetJson(page, '/ruangan/availability_ajax?tanggal=2030-04-01');
  202 |     const ruanganUmumSesudah = after.body.find((r) => r.ruangan_id === fx.ruanganUmumId);
  203 |     expect(ruanganUmumSesudah.status).toBe('Tidak Tersedia');
  204 | 
  205 |     // Kembalikan ke Tersedia supaya tidak mengganggu test lain yang memakai ruanganUmumId.
  206 |     await ajaxSend(page, `/ruangan/${fx.ruanganUmumId}/update_ajax`, 'PUT', {
  207 |       ruangan_kode: 'RUM',
  208 |       ruangan_nama: 'Ruangan Umum E2E',
  209 |       ruangan_fasilitas: 'AC, proyektor',
  210 |       ruangan_kuota: 50,
  211 |       ruangan_kategori: 'Umum',
  212 |       ruangan_status: 'Tersedia',
  213 |     });
  214 |     await logout(page);
  215 |   });
  216 | 
  217 |   test('[NEGATIF] TC-AV-07: kalender_data_ajax dengan format bulan tidak valid -> 422', async ({ page }) => {
  218 |     test.setTimeout(45000);
  219 |     await login(page, USERS.ADM.username, USERS.ADM.password);
  220 |     const result = await ajaxGetJson(page, `/ruangan/${fx.ruanganJurusanId}/kalender_data_ajax?bulan=2030-3`);
  221 |     expect(result.status).toBe(422);
  222 |     await logout(page);
  223 |   });
  224 | 
  225 |   test('[POSITIF] TC-AV-08: kalender_data_ajax mengembalikan seluruh tanggal dalam bulan (default Tersedia)', async ({ page }) => {
  226 |     test.setTimeout(45000);
  227 |     await login(page, USERS.ADM.username, USERS.ADM.password);
  228 |     const result = await ajaxGetJson(page, `/ruangan/${fx.ruanganJurusanId}/kalender_data_ajax?bulan=2030-06`);
  229 |     expect(result.status).toBe(200);
  230 |     expect(Object.keys(result.body).length).toBe(30); // Juni = 30 hari
  231 |     expect(result.body['2030-06-01']).toBe('Tersedia');
  232 |     expect(result.body['2030-06-30']).toBe('Tersedia');
  233 |     await logout(page);
  234 |   });
  235 | 
  236 |   test('[POSITIF] TC-AV-09: kalender_ajax merender modal berisi nama ruangan', async ({ page }) => {
  237 |     test.setTimeout(45000);
  238 |     await login(page, USERS.ADM.username, USERS.ADM.password);
  239 |     const result = await ajaxGetHtml(page, `/ruangan/${fx.ruanganJurusanId}/kalender_ajax`);
  240 |     expect(result.status).toBe(200);
  241 |     expect(result.body).toContain('Kalender Ketersediaan');
  242 |     expect(result.body).toContain('Ruangan 1');
  243 |     await logout(page);
  244 |   });
  245 | 
  246 |   test('[NEGATIF] TC-AV-10: kalender_ajax untuk ruangan tidak ditemukan', async ({ page }) => {
  247 |     test.setTimeout(45000);
  248 |     await login(page, USERS.ADM.username, USERS.ADM.password);
  249 |     const result = await ajaxGetHtml(page, '/ruangan/999999/kalender_ajax');
  250 |     expect([404, 200]).toContain(result.status);
  251 |     if (result.status === 200) {
  252 |       expect(result.body).toContain('tidak ditemukan');
  253 |     }
  254 |     await logout(page);
  255 |   });
  256 | });
  257 | 
```