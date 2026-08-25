# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: jadwal.spec.js >> CRUD Jadwal & Status State Machine (FR-4) >> Read / List Jadwal >> [POSITIF] TC-JDW-RD-02: List endpoint returns array
- Location: modules\jadwal.spec.js:283:5

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
  227 |       expect((await jPage.create(data)).body.status).toBe(true);
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
> 288 |       expect(Array.isArray(result.body)).toBe(true);
      |                                          ^ Error: expect(received).toBe(expected) // Object.is equality
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
  328 |       expect(result.status).toBe(200);
  329 |       expect(Array.isArray(result.body)).toBe(true);
  330 |     });
  331 |   });
  332 | 
  333 |   // ══════════════════════════════════════════════════
  334 |   // 3. UPDATE (11 TC)
  335 |   // ══════════════════════════════════════════════════
  336 | 
  337 |   test.describe('Update Jadwal', () => {
  338 | 
  339 |     test('[POSITIF] TC-JDW-UP-01: Update nama jadwal', async ({ page }) => {
  340 |       const { data, id } = await createJadwal(page);
  341 |       if (!id) { test.skip(); return; }
  342 |       const jPage = new JadwalPage(page);
  343 |       const namaBaru = JadwalPage.generateUniqueName('UPD');
  344 |       const result = await jPage.update(id, { ...data, jadwal_nama: namaBaru });
  345 |       expect(result.status).toBe(200);
  346 |       expect(result.body.status).toBe(true);
  347 |       expect(result.body.message).toContain('berhasil diupdate');
  348 |     });
  349 | 
  350 |     test('[POSITIF] TC-JDW-UP-02: Update jam jadwal', async ({ page }) => {
  351 |       const { data, id } = await createJadwal(page);
  352 |       if (!id) { test.skip(); return; }
  353 |       const jPage = new JadwalPage(page);
  354 |       const result = await jPage.update(id, { ...data, jadwal_jam_mulai: '09:00', jadwal_jam_selesai: '17:00' });
  355 |       expect(result.status).toBe(200);
  356 |       expect(result.body.status).toBe(true);
  357 |     });
  358 | 
  359 |     test('[POSITIF] TC-JDW-UP-03: Update tanggal jadwal', async ({ page }) => {
  360 |       const { data, id } = await createJadwal(page);
  361 |       if (!id) { test.skip(); return; }
  362 |       const jPage = new JadwalPage(page);
  363 |       const result = await jPage.update(id, { ...data, jadwal_tgl: '2026-12-25' });
  364 |       expect(result.status).toBe(200);
  365 |       expect(result.body.status).toBe(true);
  366 |     });
  367 | 
  368 |     test('[POSITIF] TC-JDW-UP-04: Update jumlah peserta', async ({ page }) => {
  369 |       const { data, id } = await createJadwal(page);
  370 |       if (!id) { test.skip(); return; }
  371 |       const jPage = new JadwalPage(page);
  372 |       const result = await jPage.update(id, { ...data, jadwal_jumPes: 200 });
  373 |       expect(result.status).toBe(200);
  374 |       expect(result.body.status).toBe(true);
  375 |     });
  376 | 
  377 |     test('[POSITIF] TC-JDW-UP-05: Update ruangan_ids', async ({ page }) => {
  378 |       const { data, id } = await createJadwal(page);
  379 |       if (!id) { test.skip(); return; }
  380 |       const jPage = new JadwalPage(page);
  381 |       const result = await jPage.update(id, { ...data, ruangan_ids: [2] });
  382 |       expect(result.status).toBe(200);
  383 |       expect(result.body.status).toBe(true);
  384 |     });
  385 | 
  386 |     test('[NEGATIF] TC-JDW-UP-06: Update ID tidak ditemukan', async ({ page }) => {
  387 |       const jPage = new JadwalPage(page);
  388 |       const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL') });
```