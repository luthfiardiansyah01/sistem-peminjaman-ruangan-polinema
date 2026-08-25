# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: ruangan-availability.spec.js >> Status Ruangan Per-Tanggal (Poin 2) >> [POSITIF] TC-AV-04: Ruangan dengan pengajuan status Diajukan di tanggal itu -> Diajukan
- Location: modules\ruangan-availability.spec.js:135:3

# Error details

```
Error: expect(received).toBe(expected) // Object.is equality

Expected: "Diajukan"
Received: "Tersedia"
```

# Page snapshot

```yaml
- generic [ref=e2]:
  - navigation [ref=e3]:
    - list [ref=e4]:
      - listitem [ref=e5]:
        - button "" [ref=e6] [cursor=pointer]:
          - generic [ref=e7]: 
    - list [ref=e8]:
      - listitem [ref=e9]:
        - button "Admin 1 " [ref=e10] [cursor=pointer]:
          - generic [ref=e11]: Admin 1
          - generic [ref=e12]: 
      - listitem [ref=e13]:
        - button " Logout" [ref=e14] [cursor=pointer]:
          - generic [ref=e15]: 
          - generic [ref=e16]: Logout
  - complementary [ref=e17]:
    - link "Logo JTI Polinema Sistem Peminjaman Ruangan" [ref=e18] [cursor=pointer]:
      - /url: http://127.0.0.1:8000
      - img "Logo JTI Polinema" [ref=e19]
      - generic [ref=e20]:
        - text: Sistem Peminjaman
        - text: Ruangan
    - generic [ref=e25]:
      - generic [ref=e26]:
        - searchbox "Search" [ref=e27]
        - button "" [ref=e29] [cursor=pointer]:
          - generic [ref=e30]: 
      - navigation [ref=e31]:
        - menu [ref=e32]:
          - listitem [ref=e33]:
            - link " Dashboard" [ref=e34] [cursor=pointer]:
              - /url: http://127.0.0.1:8000/dashboard
              - generic [ref=e35]: 
              - paragraph [ref=e36]: Dashboard
          - listitem [ref=e37]:
            - link " Daftar Jadwal" [ref=e38] [cursor=pointer]:
              - /url: http://127.0.0.1:8000/jadwal
              - generic [ref=e39]: 
              - paragraph [ref=e40]: Daftar Jadwal
          - listitem [ref=e41]:
            - link " Daftar Ruangan" [ref=e42] [cursor=pointer]:
              - /url: http://127.0.0.1:8000/ruangan
              - generic [ref=e43]: 
              - paragraph [ref=e44]: Daftar Ruangan
          - listitem [ref=e45]:
            - link " Daftar Penyelesaian" [ref=e46] [cursor=pointer]:
              - /url: http://127.0.0.1:8000/penyelesaian
              - generic [ref=e47]: 
              - paragraph [ref=e48]: Daftar Penyelesaian
          - listitem [ref=e49]:
            - link " Daftar Periode" [ref=e50] [cursor=pointer]:
              - /url: http://127.0.0.1:8000/periode
              - generic [ref=e51]: 
              - paragraph [ref=e52]: Daftar Periode
          - listitem [ref=e53]:
            - link " Daftar Pengguna " [ref=e54] [cursor=pointer]:
              - /url: "#"
              - generic [ref=e55]: 
              - paragraph [ref=e56]:
                - text: Daftar Pengguna
                - generic [ref=e57]: 
            - text:    
          - listitem [ref=e58]:
            - link " Daftar Organisasi" [ref=e59] [cursor=pointer]:
              - /url: http://127.0.0.1:8000/organisasi
              - generic [ref=e60]: 
              - paragraph [ref=e61]: Daftar Organisasi
          - listitem [ref=e62]:
            - link " Daftar Program Studi" [ref=e63] [cursor=pointer]:
              - /url: http://127.0.0.1:8000/prodi
              - generic [ref=e64]: 
              - paragraph [ref=e65]: Daftar Program Studi
          - listitem [ref=e66]:
            - link " Daftar Kelas" [ref=e67] [cursor=pointer]:
              - /url: http://127.0.0.1:8000/kelas
              - generic [ref=e68]: 
              - paragraph [ref=e69]: Daftar Kelas
  - generic [ref=e70]:
    - generic [ref=e73]:
      - heading "Selamat Datang!" [level=1] [ref=e75]
      - list [ref=e77]:
        - listitem [ref=e78]: Home
        - listitem [ref=e79]: / Dashboard
    - generic [ref=e80]:
      - generic [ref=e81]:
        - generic [ref=e84]:
          - heading "8" [level=3] [ref=e85]
          - paragraph [ref=e86]: Total Ruangan
          - generic [ref=e88]: 
        - generic [ref=e91]:
          - heading "0" [level=3] [ref=e92]
          - paragraph [ref=e93]: Jadwal Hari Ini
          - generic [ref=e95]: 
        - generic [ref=e98]:
          - heading "8" [level=3] [ref=e99]
          - paragraph [ref=e100]: Total Ruangan Kosong
          - generic [ref=e102]: 
        - generic [ref=e105]:
          - heading "11" [level=3] [ref=e106]
          - paragraph [ref=e107]: Total Pengguna Aktif
          - generic [ref=e109]: 
      - generic [ref=e110]:
        - generic [ref=e112]:
          - generic [ref=e113]:
            - heading "Top 5 Ruangan Terfavorit" [level=3] [ref=e114]
            - generic [ref=e115]:
              - button "" [ref=e116] [cursor=pointer]:
                - generic [ref=e117]: 
              - button "" [ref=e118] [cursor=pointer]:
                - generic [ref=e119]: 
          - generic [ref=e121]:
            - generic [ref=e122]: 
            - paragraph [ref=e123]: Belum ada data peminjaman ruangan bulan ini.
        - generic [ref=e125]:
          - generic [ref=e126]:
            - heading "Distribusi Status Peminjam" [level=3] [ref=e127]
            - generic [ref=e128]:
              - button "" [ref=e129] [cursor=pointer]:
                - generic [ref=e130]: 
              - button "" [ref=e131] [cursor=pointer]:
                - generic [ref=e132]: 
          - generic [ref=e134]:
            - generic [ref=e135]: 
            - paragraph [ref=e136]: Belum ada pengguna peminjam terdaftar.
      - generic [ref=e140]:
        - heading "Tren Peminjaman (6 Bulan)" [level=3] [ref=e141]
        - generic [ref=e142]:
          - button "" [ref=e143] [cursor=pointer]:
            - generic [ref=e144]: 
          - button "" [ref=e145] [cursor=pointer]:
            - generic [ref=e146]: 
  - contentinfo [ref=e150]:
    - strong [ref=e151]:
      - text: Copyright © 2025
      - link "Jurusan Teknologi Informasi - Politeknik Negeri Malang" [ref=e152] [cursor=pointer]:
        - /url: https://jti.polinema.ac.id/
      - text: .
    - text: All rights reserved.
    - generic [ref=e153]: Version 1.0.0
```

# Test source

```ts
  47  |         success: (response) => resolve({ status: 200, body: response }),
  48  |         error: (xhr) => {
  49  |           try {
  50  |             resolve({ status: xhr.status, body: JSON.parse(xhr.responseText) });
  51  |           } catch (e) {
  52  |             resolve({ status: xhr.status, body: xhr.responseText });
  53  |           }
  54  |         },
  55  |       });
  56  |     });
  57  |   }, url);
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
> 147 |     expect(ruanganJurusan.status).toBe('Diajukan');
      |                                   ^ Error: expect(received).toBe(expected) // Object.is equality
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
  158 |     expect(id).not.toBeNull();
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
```