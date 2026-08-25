# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: jadwal.spec.js >> CRUD Jadwal & Status State Machine (FR-4) >> Create Jadwal >> [DUPLICATE] TC-JDW-CR-25: Duplikat nama (diperbolehkan)
- Location: modules\jadwal.spec.js:222:5

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
  127 |       expect(result.body.status).toBe(false);
  128 |     });
  129 | 
  130 |     test('[NEGATIF] TC-JDW-CR-12: Jam selesai kosong', async ({ page }) => {
  131 |       const jPage = new JadwalPage(page);
  132 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_jam_selesai: '' }));
  133 |       expect([422, 500]).toContain(result.status);
  134 |       expect(result.body.status).toBe(false);
  135 |     });
  136 | 
  137 |     test('[NEGATIF] TC-JDW-CR-13: Jumlah peserta kosong', async ({ page }) => {
  138 |       const jPage = new JadwalPage(page);
  139 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_jumPes: '' }));
  140 |       expect([422, 500]).toContain(result.status);
  141 |       expect(result.body.status).toBe(false);
  142 |     });
  143 | 
  144 |     test('[NEGATIF] TC-JDW-CR-14: user_id kosong', async ({ page }) => {
  145 |       const jPage = new JadwalPage(page);
  146 |       const result = await jPage.create(JadwalPage.generateData({ user_id: '' }));
  147 |       expect([422, 500]).toContain(result.status);
  148 |       expect(result.body.status).toBe(false);
  149 |     });
  150 | 
  151 |     test('[NEGATIF] TC-JDW-CR-15: ruangan_ids array kosong []', async ({ page }) => {
  152 |       const jPage = new JadwalPage(page);
  153 |       const result = await jPage.create(JadwalPage.generateData({ ruangan_ids: [] }));
  154 |       expect([422, 500]).toContain(result.status);
  155 |       expect(result.body.status).toBe(false);
  156 |     });
  157 | 
  158 |     test('[NEGATIF] TC-JDW-CR-16: Jam selesai < jam mulai (after rule)', async ({ page }) => {
  159 |       const jPage = new JadwalPage(page);
  160 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '14:00', jadwal_jam_selesai: '08:00' }));
  161 |       expect([422, 500]).toContain(result.status);
  162 |       expect(result.body.status).toBe(false);
  163 |       expect(result.body.msgField?.jadwal_jam_selesai || result.body.errors?.jadwal_jam_selesai).toBeDefined();
  164 |     });
  165 | 
  166 |     test('[NEGATIF] TC-JDW-CR-17: Jam mulai = jam selesai', async ({ page }) => {
  167 |       const jPage = new JadwalPage(page);
  168 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '10:00', jadwal_jam_selesai: '10:00' }));
  169 |       expect([422, 500]).toContain(result.status);
  170 |       expect(result.body.status).toBe(false);
  171 |     });
  172 | 
  173 |     test('[NEGATIF] TC-JDW-CR-18: Format jam invalid (25:00)', async ({ page }) => {
  174 |       const jPage = new JadwalPage(page);
  175 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '25:00' }));
  176 |       expect([422, 500]).toContain(result.status);
  177 |       expect(result.body.status).toBe(false);
  178 |     });
  179 | 
  180 |     test('[NEGATIF] TC-JDW-CR-19: Format jam bukan H:i (jam8)', async ({ page }) => {
  181 |       const jPage = new JadwalPage(page);
  182 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: 'jam8' }));
  183 |       expect([422, 500]).toContain(result.status);
  184 |       expect(result.body.status).toBe(false);
  185 |     });
  186 | 
  187 |     test('[NEGATIF] TC-JDW-CR-20: Tanggal bukan date', async ({ page }) => {
  188 |       const jPage = new JadwalPage(page);
  189 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_tgl: 'bukan-tanggal' }));
  190 |       expect([422, 500]).toContain(result.status);
  191 |       expect(result.body.status).toBe(false);
  192 |     });
  193 | 
  194 |     test('[NEGATIF] TC-JDW-CR-21: user_id tidak ada (99999)', async ({ page }) => {
  195 |       const jPage = new JadwalPage(page);
  196 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), user_id: 99999 }));
  197 |       expect([422, 500]).toContain(result.status);
  198 |       expect(result.body.status).toBe(false);
  199 |     });
  200 | 
  201 |     test('[NEGATIF] TC-JDW-CR-22: ruangan_ids berisi ID tidak ada', async ({ page }) => {
  202 |       const jPage = new JadwalPage(page);
  203 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), ruangan_ids: [99999] }));
  204 |       expect([422, 500]).toContain(result.status);
  205 |       expect(result.body.status).toBe(false);
  206 |     });
  207 | 
  208 |     test('[NEGATIF] TC-JDW-CR-23: jadwal_jumPes string (abc)', async ({ page }) => {
  209 |       const jPage = new JadwalPage(page);
  210 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jumPes: 'abc' }));
  211 |       expect([422, 500]).toContain(result.status);
  212 |       expect(result.body.status).toBe(false);
  213 |     });
  214 | 
  215 |     test('[NEGATIF] TC-JDW-CR-24: jadwal_jumPes negatif (-1)', async ({ page }) => {
  216 |       const jPage = new JadwalPage(page);
  217 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jumPes: -1 }));
  218 |       expect([422, 500]).toContain(result.status);
  219 |       expect(result.body.status).toBe(false);
  220 |     });
  221 | 
  222 |     test('[DUPLICATE] TC-JDW-CR-25: Duplikat nama (diperbolehkan)', async ({ page }) => {
  223 |       const jPage = new JadwalPage(page);
  224 |       const nama = JadwalPage.generateUniqueName('JDL');
  225 |       const data = JadwalPage.generateData({ jadwal_nama: nama });
  226 |       expect((await jPage.create(data)).body.status).toBe(true);
> 227 |       expect((await jPage.create(data)).body.status).toBe(true);
      |                                                      ^ Error: expect(received).toBe(expected) // Object.is equality
  228 |     });
  229 | 
  230 |     test('[BOUNDARY] TC-JDW-CR-26: Nama 255 karakter (max valid)', async ({ page }) => {
  231 |       const jPage = new JadwalPage(page);
  232 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: 'N'.repeat(255), jadwal_jam_mulai: '07:00', jadwal_jam_selesai: '08:00' }));
  233 |       expect(result.status).toBe(200);
  234 |       expect(result.body.status).toBe(true);
  235 |     });
  236 | 
  237 |     test('[BOUNDARY] TC-JDW-CR-27: Nama 256 karakter (exceed max)', async ({ page }) => {
  238 |       const jPage = new JadwalPage(page);
  239 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: 'N'.repeat(256), jadwal_jam_mulai: '07:00', jadwal_jam_selesai: '08:00' }));
  240 |       expect([422, 500]).toContain(result.status);
  241 |       expect(result.body.status).toBe(false);
  242 |     });
  243 | 
  244 |     test('[BOUNDARY] TC-JDW-CR-28: Nama 1 karakter (minimum)', async ({ page }) => {
  245 |       const jPage = new JadwalPage(page);
  246 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: 'A', jadwal_jam_mulai: '07:00', jadwal_jam_selesai: '08:00' }));
  247 |       expect(result.status).toBe(200);
  248 |       expect(result.body.status).toBe(true);
  249 |     });
  250 | 
  251 |     test('[VALID] TC-JDW-CR-29: Create via modal UI (form submit)', async ({ page }) => {
  252 |       const jPage = new JadwalPage(page);
  253 |       const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL') });
  254 |       await jPage.openCreateModal();
  255 |       await jPage.fillCreateForm(data);
  256 |       await jPage.submitCreateForm();
  257 |       try {
  258 |         await page.waitForSelector('.swal2-popup', { timeout: 5000 });
  259 |         const text = await page.locator('.swal2-html-container').textContent().catch(() => '');
  260 |         await page.locator('.swal2-confirm').click().catch(() => {});
  261 |         await page.waitForTimeout(500);
  262 |         expect(text).toContain('berhasil');
  263 |       } catch (e) {}
  264 |     });
  265 |   });
  266 | 
  267 |   // ══════════════════════════════════════════════════
  268 |   // 2. READ / LIST (8 TC)
  269 |   // ══════════════════════════════════════════════════
  270 | 
  271 |   test.describe('Read / List Jadwal', () => {
  272 | 
  273 |     test('[POSITIF] TC-JDW-RD-01: Index halaman dapat diakses', async ({ page }) => {
  274 |       const jPage = new JadwalPage(page);
  275 |       await createJadwal(page);
  276 |       await jPage.gotoIndex();
  277 |       const header = page.locator('.content-header .breadcrumb-item:last-child');
  278 |       await expect(header).toContainText('Jadwal', { timeout: 5000 }).catch(() => {});
  279 |       const table = page.locator('#table_id, .table, table').first();
  280 |       await expect(table).toBeVisible({ timeout: 5000 }).catch(() => {});
  281 |     });
  282 | 
  283 |     test('[POSITIF] TC-JDW-RD-02: List endpoint returns array', async ({ page }) => {
  284 |       const jPage = new JadwalPage(page);
  285 |       await jPage.gotoIndex();
  286 |       const result = await jPage.getDataTable();
  287 |       expect(result.status).toBe(200);
  288 |       expect(Array.isArray(result.body)).toBe(true);
  289 |     });
  290 | 
  291 |     test('[POSITIF] TC-JDW-RD-03: Detail by ID ditemukan', async ({ page }) => {
  292 |       const { data, id } = await createJadwal(page);
  293 |       expect(id).not.toBeNull();
  294 |       const resp = await page.goto(`/jadwal/${id}/show_ajax`, { waitUntil: 'domcontentloaded' });
  295 |       expect(resp.status()).toBe(200);
  296 |     });
  297 | 
  298 |     test('[NEGATIF] TC-JDW-RD-04: Detail ID tidak ditemukan (404)', async ({ page }) => {
  299 |       const resp = await page.goto('/jadwal/99999/show_ajax', { waitUntil: 'domcontentloaded' });
  300 |       expect(resp.status()).toBe(404);
  301 |     });
  302 | 
  303 |     test('[NEGATIF] TC-JDW-RD-05: Detail ID string (route reject)', async ({ page }) => {
  304 |       const resp = await page.goto('/jadwal/abc/show_ajax', { waitUntil: 'domcontentloaded' });
  305 |       expect(resp.status()).toBe(404);
  306 |     });
  307 | 
  308 |     test('[NEGATIF] TC-JDW-RD-06: Detail ID = 0', async ({ page }) => {
  309 |       const resp = await page.goto('/jadwal/0/show_ajax', { waitUntil: 'domcontentloaded' });
  310 |       expect(resp.status()).toBe(404);
  311 |     });
  312 | 
  313 |     test('[POSITIF] TC-JDW-RD-07: Get kelas by prodi (cascade dropdown)', async ({ page }) => {
  314 |       const jPage = new JadwalPage(page);
  315 |       await jPage.gotoIndex();
  316 |       const result = await jPage.getKelasByProdi(1);
  317 |       expect(result.status).toBe(200);
  318 |       if (Array.isArray(result.body) && result.body.length > 0) {
  319 |         expect(result.body[0]).toHaveProperty('kelas_id');
  320 |         expect(result.body[0]).toHaveProperty('kelas_nama');
  321 |         expect(result.body[0]).toHaveProperty('prodi_id');
  322 |       }
  323 |     });
  324 | 
  325 |     test('[POSITIF] TC-JDW-RD-08: Get kelas by non-existent prodi = []', async ({ page }) => {
  326 |       const jPage = new JadwalPage(page);
  327 |       const result = await jPage.getKelasByProdi(99999);
```