# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: jadwal.spec.js >> CRUD Jadwal & Status State Machine (FR-4) >> Edge Cases Jadwal >> [EDGE] TC-JDW-EC-09: Nama dengan leading/trailing spaces
- Location: modules\jadwal.spec.js:787:5

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
  690 |     test('[PERMISSION] TC-JDW-PM-08: Dosen update_status (403)', async ({ page }) => {
  691 |       await logout(page); await login(page, USERS.DSN.username, USERS.DSN.password);
  692 |       const jPage = new JadwalPage(page);
  693 |       const result = await jPage.updateStatus(1, 'Berlangsung');
  694 |       expect([403, 302]).toContain(result.status);
  695 |     });
  696 | 
  697 |     test('[PERMISSION] TC-JDW-PM-09: Mahasiswa delete (403)', async ({ page }) => {
  698 |       await logout(page); await login(page, USERS.MHS.username, USERS.MHS.password);
  699 |       const jPage = new JadwalPage(page);
  700 |       const result = await jPage.delete(1);
  701 |       expect([403, 302]).toContain(result.status);
  702 |     });
  703 | 
  704 |     test('[PERMISSION] TC-JDW-PM-10: Dosen show_ajax (200)', async ({ page }) => {
  705 |       await logout(page); await login(page, USERS.DSN.username, USERS.DSN.password);
  706 |       expect([200, 404]).toContain((await page.goto('/jadwal/1/show_ajax', { waitUntil: 'domcontentloaded' })).status());
  707 |     });
  708 | 
  709 |     test('[PERMISSION] TC-JDW-PM-11: Dosen get_kelas_by_prodi (200)', async ({ page }) => {
  710 |       await logout(page); await login(page, USERS.DSN.username, USERS.DSN.password);
  711 |       const jPage = new JadwalPage(page);
  712 |       expect((await jPage.getKelasByProdi(1)).status).toBe(200);
  713 |     });
  714 | 
  715 |     test('[PERMISSION] TC-JDW-PM-12: Dosen store_ajax (403)', async ({ page }) => {
  716 |       await logout(page); await login(page, USERS.DSN.username, USERS.DSN.password);
  717 |       const jPage = new JadwalPage(page);
  718 |       const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL') });
  719 |       const result = await jPage.create(data);
  720 |       expect([403, 302]).toContain(result.status);
  721 |     });
  722 | 
  723 |     test('[PERMISSION] TC-JDW-PM-13: Dosen then admin masih bisa', async ({ page }) => {
  724 |       await logout(page); await login(page, USERS.DSN.username, USERS.DSN.password);
  725 |       await page.goto('/jadwal', { waitUntil: 'domcontentloaded' });
  726 |       await logout(page); await login(page, USERS.ADM.username, USERS.ADM.password);
  727 |       expect((await page.goto('/jadwal/create_ajax', { waitUntil: 'domcontentloaded' })).status()).toBe(200);
  728 |     });
  729 |   });
  730 | 
  731 |   // ══════════════════════════════════════════════════
  732 |   // 7. EDGE CASES (12 TC)
  733 |   // ══════════════════════════════════════════════════
  734 | 
  735 |   test.describe('Edge Cases Jadwal', () => {
  736 | 
  737 |     test('[EDGE] TC-JDW-EC-01: Nama dengan XSS/HTML', async ({ page }) => {
  738 |       const jPage = new JadwalPage(page);
  739 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: '<script>alert("xss")</script> ' + Date.now() }));
  740 |       expect(result.body.status).toBe(true);
  741 |     });
  742 | 
  743 |     test('[EDGE] TC-JDW-EC-02: Nama karakter spesial', async ({ page }) => {
  744 |       const jPage = new JadwalPage(page);
  745 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: '@#$%^&*() Test ' + Date.now() }));
  746 |       expect(result.body.status).toBe(true);
  747 |     });
  748 | 
  749 |     test('[EDGE] TC-JDW-EC-03: Full day 00:00 - 23:59', async ({ page }) => {
  750 |       const jPage = new JadwalPage(page);
  751 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '00:00', jadwal_jam_selesai: '23:59' }));
  752 |       expect(result.body.status).toBe(true);
  753 |     });
  754 | 
  755 |     test('[EDGE] TC-JDW-EC-04: Jumlah peserta 0 (zero)', async ({ page }) => {
  756 |       const jPage = new JadwalPage(page);
  757 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jumPes: 0 }));
  758 |       expect([200, 422]).toContain(result.status);
  759 |     });
  760 | 
  761 |     test('[EDGE] TC-JDW-EC-05: Negative jumPes -100', async ({ page }) => {
  762 |       const jPage = new JadwalPage(page);
  763 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jumPes: -100 }));
  764 |       expect(result.body.status).toBe(false);
  765 |     });
  766 | 
  767 |     test('[EDGE] TC-JDW-EC-06: 29 Feb kabisat (2028)', async ({ page }) => {
  768 |       const jPage = new JadwalPage(page);
  769 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_tgl: '2028-02-29' }));
  770 |       expect(result.body.status).toBe(true);
  771 |     });
  772 | 
  773 |     test('[EDGE] TC-JDW-EC-07: 29 Feb non-kabisat (2025)', async ({ page }) => {
  774 |       const jPage = new JadwalPage(page);
  775 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_tgl: '2025-02-29' }));
  776 |       expect([422, 500]).toContain(result.status);
  777 |     });
  778 | 
  779 |     test('[EDGE] TC-JDW-EC-08: Batch create 3 jadwal cepat', async ({ page }) => {
  780 |       const jPage = new JadwalPage(page);
  781 |       for (let i = 0; i < 3; i++) {
  782 |         const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL') }));
  783 |         expect(result.body.status).toBe(true);
  784 |       }
  785 |     });
  786 | 
  787 |     test('[EDGE] TC-JDW-EC-09: Nama dengan leading/trailing spaces', async ({ page }) => {
  788 |       const jPage = new JadwalPage(page);
  789 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: '  ' + JadwalPage.generateUniqueName('JDL') + '  ' }));
> 790 |       expect(result.body.status).toBe(true);
      |                                  ^ Error: expect(received).toBe(expected) // Object.is equality
  791 |     });
  792 | 
  793 |     test('[EDGE] TC-JDW-EC-10: Interval 1 menit (minimum)', async ({ page }) => {
  794 |       const jPage = new JadwalPage(page);
  795 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '10:00', jadwal_jam_selesai: '10:01' }));
  796 |       expect(result.body.status).toBe(true);
  797 |     });
  798 | 
  799 |     test('[EDGE] TC-JDW-EC-11: Interval 23 jam 59 menit (mendekati maks)', async ({ page }) => {
  800 |       const jPage = new JadwalPage(page);
  801 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '00:00', jadwal_jam_selesai: '23:59' }));
  802 |       expect(result.body.status).toBe(true);
  803 |     });
  804 | 
  805 |     test('[EDGE] TC-JDW-EC-12: jadwal_jumPes floating point (1.5)', async ({ page }) => {
  806 |       const jPage = new JadwalPage(page);
  807 |       const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jumPes: 1.5 }));
  808 |       expect([200, 422]).toContain(result.status);
  809 |     });
  810 |   });
  811 | });
  812 | 
```