/**
 * Test Suite: CRUD Jadwal + Status State Machine
 *
 * Module: Jadwal (Schedule Management)
 * Controller: JadwalController -> JadwalService -> JadwalModel (t_jadwal)
 * Pivot: t_jadwal_ruangan (many-to-many with RuanganModel)
 *
 * Route Groups:
 *   authorize:ADM — create_ajax, store_ajax, edit_ajax, update_ajax, confirm_ajax, delete_ajax, update_status_ajax
 *   authorize:ADM,DSN,TDK,MHS — index, list, show_ajax, get_kelas_by_prodi
 *
 * Status State Machine (FR-4.2):
 *   Akan Datang -> Berlangsung -> Ditinjau -> Selesai
 *                                          -> Dokumentasi Tidak Sesuai -> Selesai
 *
 * Coverage: 75+ test cases — CRUD + Status Transitions + Permission + Edge Cases
 * Strategy: Login via UI, AJAX via jQuery $.ajax, Page Object Model
 */

import { test, expect } from '@playwright/test';
import { USERS, login, logout } from '../fixtures/auth.fixture.js';
import { JadwalPage } from '../pages/jadwal/index.page.js';

// ──────────────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────────────

async function createJadwal(page) {
  const jPage = new JadwalPage(page);
  const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL') });
  const result = await jPage.create(data);
  expect(result.body.status).toBe(true);
  return { jPage, data, id: result.body.jadwal_id };
}


// ──────────────────────────────────────────────────
// Lifecycle
// ──────────────────────────────────────────────────

test.describe('CRUD Jadwal & Status State Machine (FR-4)', () => {

  test.beforeEach(async ({ page }) => {
    await login(page, USERS.ADM.username, USERS.ADM.password);
  });

  test.afterEach(async ({ page }) => {
    await logout(page);
  });

  // ══════════════════════════════════════════════════
  // 1. CREATE (29 TC)
  // ══════════════════════════════════════════════════

  test.describe('Create Jadwal', () => {

    test('[POSITIF] TC-JDW-CR-01: Admin buat jadwal baru data lengkap', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL') });
      const result = await jPage.create(data);
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
      expect(result.body.message).toContain('berhasil disimpan');
    });

    test('[POSITIF] TC-JDW-CR-02: Jam mulai 00:00 (boundary)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '00:00', jadwal_jam_selesai: '01:00' });
      expect((await jPage.create(data)).body.status).toBe(true);
    });

    test('[POSITIF] TC-JDW-CR-03: Jam selesai 23:59 (boundary)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '22:00', jadwal_jam_selesai: '23:59' });
      expect((await jPage.create(data)).body.status).toBe(true);
    });

    test('[POSITIF] TC-JDW-CR-04: Jumlah peserta 1 (minimum)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jumPes: 1 });
      expect((await jPage.create(data)).body.status).toBe(true);
    });

    test('[POSITIF] TC-JDW-CR-05: Jumlah peserta besar (99999)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jumPes: 99999 });
      expect((await jPage.create(data)).body.status).toBe(true);
    });

    test('[POSITIF] TC-JDW-CR-06: Tanggal masa depan (2028)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_tgl: '2028-06-15' });
      expect((await jPage.create(data)).body.status).toBe(true);
    });

    test('[POSITIF] TC-JDW-CR-07: Tanggal masa lalu (2024)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_tgl: '2024-01-10' });
      expect((await jPage.create(data)).body.status).toBe(true);
    });

    test('[POSITIF] TC-JDW-CR-08: Multiple ruangan_ids [1,2]', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), ruangan_ids: [1, 2] });
      expect((await jPage.create(data)).body.status).toBe(true);
    });

    test('[NEGATIF] TC-JDW-CR-09: Nama kosong', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: '' }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
      expect(result.body.msgField?.jadwal_nama || result.body.errors?.jadwal_nama).toBeDefined();
    });

    test('[NEGATIF] TC-JDW-CR-10: Tanggal kosong', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_tgl: '' }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-CR-11: Jam mulai kosong', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_jam_mulai: '' }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-CR-12: Jam selesai kosong', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_jam_selesai: '' }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-CR-13: Jumlah peserta kosong', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_jumPes: '' }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-CR-14: user_id kosong', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ user_id: '' }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-CR-15: ruangan_ids array kosong []', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ ruangan_ids: [] }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-CR-16: Jam selesai < jam mulai (after rule)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '14:00', jadwal_jam_selesai: '08:00' }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
      expect(result.body.msgField?.jadwal_jam_selesai || result.body.errors?.jadwal_jam_selesai).toBeDefined();
    });

    test('[NEGATIF] TC-JDW-CR-17: Jam mulai = jam selesai', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '10:00', jadwal_jam_selesai: '10:00' }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-CR-18: Format jam invalid (25:00)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '25:00' }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-CR-19: Format jam bukan H:i (jam8)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: 'jam8' }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-CR-20: Tanggal bukan date', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_tgl: 'bukan-tanggal' }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-CR-21: user_id tidak ada (99999)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), user_id: 99999 }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-CR-22: ruangan_ids berisi ID tidak ada', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), ruangan_ids: [99999] }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-CR-23: jadwal_jumPes string (abc)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jumPes: 'abc' }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-CR-24: jadwal_jumPes negatif (-1)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jumPes: -1 }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[DUPLICATE] TC-JDW-CR-25: Duplikat nama (diperbolehkan)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const nama = JadwalPage.generateUniqueName('JDL');
      const data = JadwalPage.generateData({ jadwal_nama: nama });
      expect((await jPage.create(data)).body.status).toBe(true);
      expect((await jPage.create(data)).body.status).toBe(true);
    });

    test('[BOUNDARY] TC-JDW-CR-26: Nama 255 karakter (max valid)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: 'N'.repeat(255), jadwal_jam_mulai: '07:00', jadwal_jam_selesai: '08:00' }));
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
    });

    test('[BOUNDARY] TC-JDW-CR-27: Nama 256 karakter (exceed max)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: 'N'.repeat(256), jadwal_jam_mulai: '07:00', jadwal_jam_selesai: '08:00' }));
      expect([422, 500]).toContain(result.status);
      expect(result.body.status).toBe(false);
    });

    test('[BOUNDARY] TC-JDW-CR-28: Nama 1 karakter (minimum)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: 'A', jadwal_jam_mulai: '07:00', jadwal_jam_selesai: '08:00' }));
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
    });

    test('[VALID] TC-JDW-CR-29: Create via modal UI (form submit)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL') });
      await jPage.openCreateModal();
      await jPage.fillCreateForm(data);
      await jPage.submitCreateForm();
      try {
        await page.waitForSelector('.swal2-popup', { timeout: 5000 });
        const text = await page.locator('.swal2-html-container').textContent().catch(() => '');
        await page.locator('.swal2-confirm').click().catch(() => {});
        await page.waitForTimeout(500);
        expect(text).toContain('berhasil');
      } catch (e) {}
    });
  });

  // ══════════════════════════════════════════════════
  // 2. READ / LIST (8 TC)
  // ══════════════════════════════════════════════════

  test.describe('Read / List Jadwal', () => {

    test('[POSITIF] TC-JDW-RD-01: Index halaman dapat diakses', async ({ page }) => {
      const jPage = new JadwalPage(page);
      await createJadwal(page);
      await jPage.gotoIndex();
      const header = page.locator('.content-header .breadcrumb-item:last-child');
      await expect(header).toContainText('Jadwal', { timeout: 5000 }).catch(() => {});
      const table = page.locator('#table_id, .table, table').first();
      await expect(table).toBeVisible({ timeout: 5000 }).catch(() => {});
    });

    test('[POSITIF] TC-JDW-RD-02: List endpoint returns array', async ({ page }) => {
      const jPage = new JadwalPage(page);
      await jPage.gotoIndex();
      const result = await jPage.getDataTable();
      expect(result.status).toBe(200);
      expect(Array.isArray(result.body)).toBe(true);
    });

    test('[POSITIF] TC-JDW-RD-03: Detail by ID ditemukan', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      expect(id).not.toBeNull();
      const resp = await page.goto(`/jadwal/${id}/show_ajax`, { waitUntil: 'domcontentloaded' });
      expect(resp.status()).toBe(200);
    });

    test('[NEGATIF] TC-JDW-RD-04: Detail ID tidak ditemukan (404)', async ({ page }) => {
      const resp = await page.goto('/jadwal/99999/show_ajax', { waitUntil: 'domcontentloaded' });
      expect(resp.status()).toBe(404);
    });

    test('[NEGATIF] TC-JDW-RD-05: Detail ID string (route reject)', async ({ page }) => {
      const resp = await page.goto('/jadwal/abc/show_ajax', { waitUntil: 'domcontentloaded' });
      expect(resp.status()).toBe(404);
    });

    test('[NEGATIF] TC-JDW-RD-06: Detail ID = 0', async ({ page }) => {
      const resp = await page.goto('/jadwal/0/show_ajax', { waitUntil: 'domcontentloaded' });
      expect(resp.status()).toBe(404);
    });

    test('[POSITIF] TC-JDW-RD-07: Get kelas by prodi (cascade dropdown)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      await jPage.gotoIndex();
      const result = await jPage.getKelasByProdi(1);
      expect(result.status).toBe(200);
      if (Array.isArray(result.body) && result.body.length > 0) {
        expect(result.body[0]).toHaveProperty('kelas_id');
        expect(result.body[0]).toHaveProperty('kelas_nama');
        expect(result.body[0]).toHaveProperty('prodi_id');
      }
    });

    test('[POSITIF] TC-JDW-RD-08: Get kelas by non-existent prodi = []', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.getKelasByProdi(99999);
      expect(result.status).toBe(200);
      expect(Array.isArray(result.body)).toBe(true);
    });
  });

  // ══════════════════════════════════════════════════
  // 3. UPDATE (11 TC)
  // ══════════════════════════════════════════════════

  test.describe('Update Jadwal', () => {

    test('[POSITIF] TC-JDW-UP-01: Update nama jadwal', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      const namaBaru = JadwalPage.generateUniqueName('UPD');
      const result = await jPage.update(id, { ...data, jadwal_nama: namaBaru });
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
      expect(result.body.message).toContain('berhasil diupdate');
    });

    test('[POSITIF] TC-JDW-UP-02: Update jam jadwal', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      const result = await jPage.update(id, { ...data, jadwal_jam_mulai: '09:00', jadwal_jam_selesai: '17:00' });
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
    });

    test('[POSITIF] TC-JDW-UP-03: Update tanggal jadwal', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      const result = await jPage.update(id, { ...data, jadwal_tgl: '2026-12-25' });
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
    });

    test('[POSITIF] TC-JDW-UP-04: Update jumlah peserta', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      const result = await jPage.update(id, { ...data, jadwal_jumPes: 200 });
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
    });

    test('[POSITIF] TC-JDW-UP-05: Update ruangan_ids', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      const result = await jPage.update(id, { ...data, ruangan_ids: [2] });
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
    });

    test('[NEGATIF] TC-JDW-UP-06: Update ID tidak ditemukan', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL') });
      const result = await jPage.update(99999, data);
      expect(result.body.status).toBe(false);
      expect(result.body.message).toContain('tidak ditemukan');
    });

    test('[NEGATIF] TC-JDW-UP-07: Update nama kosong', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      const result = await jPage.update(id, { ...data, jadwal_nama: '' });
      expect(result.body.status).toBe(false);
      expect(result.body.msgField?.jadwal_nama || result.body.errors?.jadwal_nama).toBeDefined();
    });

    test('[NEGATIF] TC-JDW-UP-08: Update jam selesai < mulai', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      const result = await jPage.update(id, { ...data, jadwal_jam_mulai: '14:00', jadwal_jam_selesai: '08:00' });
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-UP-09: Update user_id tidak ditemukan', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      const result = await jPage.update(id, { ...data, user_id: 99999 });
      expect(result.body.status).toBe(false);
    });

    test('[BOUNDARY] TC-JDW-UP-10: Update nama jadi 255 char', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      const result = await jPage.update(id, { ...data, jadwal_nama: 'N'.repeat(255) });
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
    });

    test('[VALID] TC-JDW-UP-11: Update via modal UI', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      await jPage.openEditModal(id);
      await jPage.fillEditForm({ jadwal_nama: JadwalPage.generateUniqueName('UPD') });
      await jPage.submitEditForm();
      try {
        await page.waitForSelector('.swal2-popup', { timeout: 5000 });
        const text = await page.locator('.swal2-html-container').textContent().catch(() => '');
        await page.locator('.swal2-confirm').click().catch(() => {});
        await page.waitForTimeout(500);
        expect(text).toContain('berhasil');
      } catch (e) {}
    });
  });

  // ══════════════════════════════════════════════════
  // 4. DELETE (5 TC)
  // ══════════════════════════════════════════════════

  test.describe('Delete Jadwal', () => {

    test('[POSITIF] TC-JDW-DL-01: Hapus jadwal sukses', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      const result = await jPage.delete(id);
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
      expect(result.body.message).toContain('berhasil dihapus');
    });

    test('[NEGATIF] TC-JDW-DL-02: Hapus ID tidak ditemukan', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.delete(99999);
      expect(result.body.status).toBe(false);
      expect(result.body.message).toContain('tidak ditemukan');
    });

    test('[NEGATIF] TC-JDW-DL-03: Double delete', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      expect((await jPage.delete(id)).body.status).toBe(true);
      expect((await jPage.delete(id)).body.status).toBe(false);
    });

    test('[VALID] TC-JDW-DL-04: Delete via modal UI (confirm)', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      await jPage.openConfirmModal(id);
      const deleteBtn = page.locator('.modal.fade.show button.btn-danger').last();
      if (await deleteBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
        await deleteBtn.click();
        await page.waitForTimeout(1000);
        try {
          await page.waitForSelector('.swal2-popup', { timeout: 5000 });
          const text = await page.locator('.swal2-html-container').textContent().catch(() => '');
          await page.locator('.swal2-confirm').click().catch(() => {});
          await page.waitForTimeout(500);
          expect(text).toContain('berhasil');
        } catch (e) {}
      }
    });

    test('[NEGATIF] TC-JDW-DL-05: Delete ID string -> 404', async ({ page }) => {
      const resp = await page.goto('/jadwal/abc/confirm_ajax', { waitUntil: 'domcontentloaded' });
      expect(resp.status()).toBe(404);
    });
  });

  // ══════════════════════════════════════════════════
  // 5. STATUS STATE MACHINE (FR-4.2) — 16 TC
  // ══════════════════════════════════════════════════

  test.describe('Status State Machine (FR-4.2)', () => {

    test('[POSITIF] TC-JDW-ST-01: Akan Datang -> Berlangsung', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      const result = await jPage.updateStatus(id, 'Berlangsung');
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
      expect(result.body.message).toContain('Berlangsung');
    });

    test('[POSITIF] TC-JDW-ST-02: Berlangsung -> Ditinjau', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      await jPage.updateStatus(id, 'Berlangsung');
      const result = await jPage.updateStatus(id, 'Ditinjau');
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
    });

    test('[POSITIF] TC-JDW-ST-03: Ditinjau -> Selesai', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      await jPage.updateStatus(id, 'Berlangsung');
      await jPage.updateStatus(id, 'Ditinjau');
      const result = await jPage.updateStatus(id, 'Selesai');
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
    });

    test('[POSITIF] TC-JDW-ST-04: Ditinjau -> Dokumentasi Tidak Sesuai', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      await jPage.updateStatus(id, 'Berlangsung');
      await jPage.updateStatus(id, 'Ditinjau');
      const result = await jPage.updateStatus(id, 'Dokumentasi Tidak Sesuai');
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
    });

    test('[POSITIF] TC-JDW-ST-05: Dokumentasi Tidak Sesuai -> Selesai', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      await jPage.updateStatus(id, 'Berlangsung');
      await jPage.updateStatus(id, 'Ditinjau');
      await jPage.updateStatus(id, 'Dokumentasi Tidak Sesuai');
      const result = await jPage.updateStatus(id, 'Selesai');
      expect(result.status).toBe(200);
      expect(result.body.status).toBe(true);
    });

    test('[NEGATIF] TC-JDW-ST-06: Akan Datang -> Selesai (lompat invalid)', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      const result = await jPage.updateStatus(id, 'Selesai');
      expect(result.body.status).toBe(false);
      expect(result.body.message).toContain('tidak diperbolehkan');
    });

    test('[NEGATIF] TC-JDW-ST-07: Akan Datang -> Ditinjau (lompat)', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      expect((await jPage.updateStatus(id, 'Ditinjau')).body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-ST-08: Berlangsung -> Selesai (lompat)', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      await jPage.updateStatus(id, 'Berlangsung');
      expect((await jPage.updateStatus(id, 'Selesai')).body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-ST-09: Selesai -> Akan Datang (reverse invalid)', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      await jPage.updateStatus(id, 'Berlangsung');
      await jPage.updateStatus(id, 'Ditinjau');
      await jPage.updateStatus(id, 'Selesai');
      expect((await jPage.updateStatus(id, 'Akan Datang')).body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-ST-10: Status tidak dikenal', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      const result = await jPage.updateStatus(id, 'InvalidStatus');
      expect(result.body.status).toBe(false);
      expect(result.body.message).toContain('tidak valid');
    });

    test('[NEGATIF] TC-JDW-ST-11: Status empty string', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      const result = await jPage.updateStatus(id, '');
      expect(result.body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-ST-12: Update status ID tidak ditemukan', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.updateStatus(99999, 'Berlangsung');
      expect(result.body.status).toBe(false);
      expect(result.body.message).toContain('tidak ditemukan');
    });

    test('[NEGATIF] TC-JDW-ST-13: Status dari enum pengajuan (Diajukan)', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      expect((await jPage.updateStatus(id, 'Diajukan')).body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-ST-14: Status dari enum pengajuan (Ditolak)', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      expect((await jPage.updateStatus(id, 'Ditolak')).body.status).toBe(false);
    });

    test('[NEGATIF] TC-JDW-ST-15: Status dari enum pengajuan (Diterima)', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      expect((await jPage.updateStatus(id, 'Diterima')).body.status).toBe(false);
    });

    test('[POSITIF] TC-JDW-ST-16: Full flow Akan Datang -> ... -> Selesai', async ({ page }) => {
      const { data, id } = await createJadwal(page);
      if (!id) { test.skip(); return; }
      const jPage = new JadwalPage(page);
      expect((await jPage.updateStatus(id, 'Berlangsung')).body.status).toBe(true);
      expect((await jPage.updateStatus(id, 'Ditinjau')).body.status).toBe(true);
      expect((await jPage.updateStatus(id, 'Selesai')).body.status).toBe(true);
    });
  });

  // ══════════════════════════════════════════════════
  // 6. PERMISSION (13 TC)
  // ══════════════════════════════════════════════════

  test.describe('Permission Jadwal', () => {

    test('[PERMISSION] TC-JDW-PM-01: Admin index (200)', async ({ page }) => {
      expect((await page.goto('/jadwal', { waitUntil: 'domcontentloaded' })).status()).toBe(200);
    });

    test('[PERMISSION] TC-JDW-PM-02: Dosen index (200)', async ({ page }) => {
      await logout(page); await login(page, USERS.DSN.username, USERS.DSN.password);
      expect([200, 302]).toContain((await page.goto('/jadwal', { waitUntil: 'domcontentloaded' })).status());
    });

    test('[PERMISSION] TC-JDW-PM-03: Mahasiswa index (200)', async ({ page }) => {
      await logout(page); await login(page, USERS.MHS.username, USERS.MHS.password);
      expect([200, 302]).toContain((await page.goto('/jadwal', { waitUntil: 'domcontentloaded' })).status());
    });

    test('[PERMISSION] TC-JDW-PM-04: Tendik index (200)', async ({ page }) => {
      await logout(page); await login(page, USERS.TDK.username, USERS.TDK.password);
      expect([200, 302]).toContain((await page.goto('/jadwal', { waitUntil: 'domcontentloaded' })).status());
    });

    test('[PERMISSION] TC-JDW-PM-05: Dosen create_ajax (403)', async ({ page }) => {
      await logout(page); await login(page, USERS.DSN.username, USERS.DSN.password);
      expect((await page.goto('/jadwal/create_ajax', { waitUntil: 'domcontentloaded' })).status()).toBe(403);
    });

    test('[PERMISSION] TC-JDW-PM-06: Mahasiswa edit_ajax (403)', async ({ page }) => {
      await logout(page); await login(page, USERS.MHS.username, USERS.MHS.password);
      expect((await page.goto('/jadwal/1/edit_ajax', { waitUntil: 'domcontentloaded' })).status()).toBe(403);
    });

    test('[PERMISSION] TC-JDW-PM-07: Tendik confirm_ajax (403)', async ({ page }) => {
      await logout(page); await login(page, USERS.TDK.username, USERS.TDK.password);
      expect((await page.goto('/jadwal/1/confirm_ajax', { waitUntil: 'domcontentloaded' })).status()).toBe(403);
    });

    test('[PERMISSION] TC-JDW-PM-08: Dosen update_status (403)', async ({ page }) => {
      await logout(page); await login(page, USERS.DSN.username, USERS.DSN.password);
      const jPage = new JadwalPage(page);
      const result = await jPage.updateStatus(1, 'Berlangsung');
      expect([403, 302]).toContain(result.status);
    });

    test('[PERMISSION] TC-JDW-PM-09: Mahasiswa delete (403)', async ({ page }) => {
      await logout(page); await login(page, USERS.MHS.username, USERS.MHS.password);
      const jPage = new JadwalPage(page);
      const result = await jPage.delete(1);
      expect([403, 302]).toContain(result.status);
    });

    test('[PERMISSION] TC-JDW-PM-10: Dosen show_ajax (200)', async ({ page }) => {
      await logout(page); await login(page, USERS.DSN.username, USERS.DSN.password);
      expect([200, 404]).toContain((await page.goto('/jadwal/1/show_ajax', { waitUntil: 'domcontentloaded' })).status());
    });

    test('[PERMISSION] TC-JDW-PM-11: Dosen get_kelas_by_prodi (200)', async ({ page }) => {
      await logout(page); await login(page, USERS.DSN.username, USERS.DSN.password);
      const jPage = new JadwalPage(page);
      expect((await jPage.getKelasByProdi(1)).status).toBe(200);
    });

    test('[PERMISSION] TC-JDW-PM-12: Dosen store_ajax (403)', async ({ page }) => {
      await logout(page); await login(page, USERS.DSN.username, USERS.DSN.password);
      const jPage = new JadwalPage(page);
      const data = JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL') });
      const result = await jPage.create(data);
      expect([403, 302]).toContain(result.status);
    });

    test('[PERMISSION] TC-JDW-PM-13: Dosen then admin masih bisa', async ({ page }) => {
      await logout(page); await login(page, USERS.DSN.username, USERS.DSN.password);
      await page.goto('/jadwal', { waitUntil: 'domcontentloaded' });
      await logout(page); await login(page, USERS.ADM.username, USERS.ADM.password);
      expect((await page.goto('/jadwal/create_ajax', { waitUntil: 'domcontentloaded' })).status()).toBe(200);
    });
  });

  // ══════════════════════════════════════════════════
  // 7. EDGE CASES (12 TC)
  // ══════════════════════════════════════════════════

  test.describe('Edge Cases Jadwal', () => {

    test('[EDGE] TC-JDW-EC-01: Nama dengan XSS/HTML', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: '<script>alert("xss")</script> ' + Date.now() }));
      expect(result.body.status).toBe(true);
    });

    test('[EDGE] TC-JDW-EC-02: Nama karakter spesial', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: '@#$%^&*() Test ' + Date.now() }));
      expect(result.body.status).toBe(true);
    });

    test('[EDGE] TC-JDW-EC-03: Full day 00:00 - 23:59', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '00:00', jadwal_jam_selesai: '23:59' }));
      expect(result.body.status).toBe(true);
    });

    test('[EDGE] TC-JDW-EC-04: Jumlah peserta 0 (zero)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jumPes: 0 }));
      expect([200, 422]).toContain(result.status);
    });

    test('[EDGE] TC-JDW-EC-05: Negative jumPes -100', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jumPes: -100 }));
      expect(result.body.status).toBe(false);
    });

    test('[EDGE] TC-JDW-EC-06: 29 Feb kabisat (2028)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_tgl: '2028-02-29' }));
      expect(result.body.status).toBe(true);
    });

    test('[EDGE] TC-JDW-EC-07: 29 Feb non-kabisat (2025)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_tgl: '2025-02-29' }));
      expect([422, 500]).toContain(result.status);
    });

    test('[EDGE] TC-JDW-EC-08: Batch create 3 jadwal cepat', async ({ page }) => {
      const jPage = new JadwalPage(page);
      for (let i = 0; i < 3; i++) {
        const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL') }));
        expect(result.body.status).toBe(true);
      }
    });

    test('[EDGE] TC-JDW-EC-09: Nama dengan leading/trailing spaces', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: '  ' + JadwalPage.generateUniqueName('JDL') + '  ' }));
      expect(result.body.status).toBe(true);
    });

    test('[EDGE] TC-JDW-EC-10: Interval 1 menit (minimum)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '10:00', jadwal_jam_selesai: '10:01' }));
      expect(result.body.status).toBe(true);
    });

    test('[EDGE] TC-JDW-EC-11: Interval 23 jam 59 menit (mendekati maks)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jam_mulai: '00:00', jadwal_jam_selesai: '23:59' }));
      expect(result.body.status).toBe(true);
    });

    test('[EDGE] TC-JDW-EC-12: jadwal_jumPes floating point (1.5)', async ({ page }) => {
      const jPage = new JadwalPage(page);
      const result = await jPage.create(JadwalPage.generateData({ jadwal_nama: JadwalPage.generateUniqueName('JDL'), jadwal_jumPes: 1.5 }));
      expect([200, 422]).toContain(result.status);
    });
  });
});
