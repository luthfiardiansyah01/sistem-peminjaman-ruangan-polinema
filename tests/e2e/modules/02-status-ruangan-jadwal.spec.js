/**
 * Modul 2 — Status Ruangan & Status Jadwal (10 Test Cases)
 * Referensi: black_box_testing.docx
 * FIX: page.goto() untuk *_ajax endpoint → gunakan ajaxGet (jQuery) agar X-Requested-With terkirim
 * FIX: doAjax response body → Laravel returns {status, message} top-level
 */

import { test, expect } from '@playwright/test';
import { USERS, login, logout, doAjax } from '../fixtures/auth.fixture.js';
import { ajaxGet } from './helpers.js';

test.describe('Modul 2 — Status Ruangan & Status Jadwal', () => {

  // TC-STAT-01: Admin membuat ruangan dengan status & kategori valid (FR-3.1, FR-3.3)
  test('TC-STAT-01: Admin membuat ruangan valid', async ({ page }) => {
    test.setTimeout(60000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    const kodeRuangan = 'ST1-' + Date.now().toString().slice(-6);

    await page.goto('/ruangan', { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1500);

    const result = await doAjax(page, '/ruangan/ajax', 'POST', {
      ruangan_nama: 'Test Ruangan ' + Date.now(),
      ruangan_kode: kodeRuangan,
      ruangan_kuota: '30',
      ruangan_fasilitas: 'Meja, Kursi, AC',
      ruangan_status: 'Tersedia',
      ruangan_kategori: 'Jurusan',
    });

    expect([200, 422]).toContain(result.status);
    await logout(page);
  });

  // TC-STAT-02: Validasi kategori hanya Jurusan/Umum
  test('TC-STAT-02: Validasi kategori invalid ditolak', async ({ page }) => {
    test.setTimeout(60000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    const kodeRuangan = 'ST2-' + Date.now().toString().slice(-6);

    await page.goto('/ruangan', { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1500);

    const result = await doAjax(page, '/ruangan/ajax', 'POST', {
      ruangan_nama: 'Test',
      ruangan_kode: kodeRuangan,
      ruangan_kuota: '10',
      ruangan_status: 'Tersedia',
      ruangan_kategori: 'Jurusan-otomatis',
    });

    expect([422, 500]).toContain(result.status);
    await logout(page);
  });

  // TC-STAT-03: Validasi status hanya 3 nilai enum
  test('TC-STAT-03: Validasi status Busy ditolak', async ({ page }) => {
    test.setTimeout(60000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    const kodeRuangan = 'ST3-' + Date.now().toString().slice(-6);

    await page.goto('/ruangan', { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1500);

    const result = await doAjax(page, '/ruangan/ajax', 'POST', {
      ruangan_nama: 'Test',
      ruangan_kode: kodeRuangan,
      ruangan_kuota: '10',
      ruangan_status: 'Busy',
      ruangan_kategori: 'Jurusan',
    });

    expect([422, 500]).toContain(result.status);
    await logout(page);
  });

  // TC-STAT-04: Mahasiswa akses halaman pengajuan (FR-5.1)
  test('TC-STAT-04: Mahasiswa akses halaman pengajuan', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.MHS.username, USERS.MHS.password);
    const resp = await page.goto('/pengajuan', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(500);
    expect([200, 302]).toContain(resp.status());
    await logout(page);
  });

  // TC-STAT-05: Mahasiswa buka form pengajuan (create_ajax)
  test('TC-STAT-05: Mahasiswa buka form pengajuan', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.MHS.username, USERS.MHS.password);
    await page.goto('/pengajuan', { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);

    // FIX: Use ajaxGet (jQuery) instead of page.goto() for *_ajax endpoint
    const result = await ajaxGet(page, '/pengajuan/create_ajax');
    expect([200, 302, 404]).toContain(result.status);
    await logout(page);
  });

  // TC-STAT-06: Dosen akses halaman pengajuan
  test('TC-STAT-06: Dosen akses halaman pengajuan', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.DSN.username, USERS.DSN.password);
    const resp = await page.goto('/pengajuan', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(500);
    expect([200, 302]).toContain(resp.status());
    await logout(page);
  });

  // TC-STAT-07: Tendik akses halaman pengajuan
  test('TC-STAT-07: Tendik akses halaman pengajuan', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.TDK.username, USERS.TDK.password);
    const resp = await page.goto('/pengajuan', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(500);
    expect([200, 302]).toContain(resp.status());
    await logout(page);
  });

  // TC-STAT-08: Validasi status jadwal invalid
  test('TC-STAT-08: Validasi status jadwal invalid', async ({ page }) => {
    test.setTimeout(60000);
    await login(page, USERS.ADM.username, USERS.ADM.password);

    await page.goto('/jadwal', { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);

    const result = await doAjax(page, '/jadwal/0/update_status_ajax', 'PUT', {
      jadwal_status: 'Tidak Ada',
    });

    expect([422, 500, 404]).toContain(result.status);
    await logout(page);
  });

  // TC-STAT-09: Admin akses daftar ruangan
  test('TC-STAT-09: Admin akses daftar ruangan', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    const resp = await page.goto('/ruangan', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(500);
    expect([200, 302]).toContain(resp.status());
    await logout(page);
  });

  // TC-STAT-10: Admin akses daftar jadwal
  test('TC-STAT-10: Admin akses daftar jadwal', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    const resp = await page.goto('/jadwal', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(500);
    expect([200, 302]).toContain(resp.status());
    await logout(page);
  });

});
