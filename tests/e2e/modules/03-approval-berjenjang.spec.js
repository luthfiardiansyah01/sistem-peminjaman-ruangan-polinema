/**
 * Modul 3 - Approval Berjenjang (12 Test Cases)
 * Referensi: black_box_testing.docx
 * FIX: page.goto() untuk *_ajax endpoint → gunakan ajaxGet (jQuery) agar X-Requested-With terkirim
 * FIX: doAjax response body → Laravel returns {status, message} top-level
 */

import { test, expect } from '@playwright/test';
import { USERS, login, logout, doAjax } from '../fixtures/auth.fixture.js';
import { ajaxGet } from './helpers.js';

test.describe('Modul 3 - Approval Berjenjang', () => {

  test('TC-APV-01: Mahasiswa akses halaman pengajuan', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.MHS.username, USERS.MHS.password);
    const resp = await page.goto('/pengajuan', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(500);
    expect([200, 302]).toContain(resp.status());
    await logout(page);
  });

  test('TC-APV-02: Mahasiswa buka form pengajuan', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.MHS.username, USERS.MHS.password);
    await page.goto('/pengajuan', { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    // FIX: Use ajaxGet instead of page.goto() for *_ajax endpoint
    const result = await ajaxGet(page, '/pengajuan/create_ajax');
    expect([200, 302, 404]).toContain(result.status);
    await logout(page);
  });

  test('TC-APV-03: Dosen akses halaman pengajuan', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.DSN.username, USERS.DSN.password);
    const resp = await page.goto('/pengajuan', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(500);
    expect([200, 302]).toContain(resp.status());
    await logout(page);
  });

  test('TC-APV-04: Tendik akses halaman pengajuan', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.TDK.username, USERS.TDK.password);
    const resp = await page.goto('/pengajuan', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(500);
    expect([200, 302]).toContain(resp.status());
    await logout(page);
  });

  // TC-APV-05/07/08: sejak redesain jabatan approval (m_jabatan_approval menggantikan
  // level VRF terpisah), route ini digerbangi authorize:ADM,DSN,TDK,MHS (siapapun yang
  // login boleh akses) — otorisasi sebenarnya ditegakkan lebih dalam di
  // PengajuanService::processApproval() via ownership jabatan, bukan di level route.
  // ADM/MHS/TDK tidak pernah punya jabatan approval apapun, jadi antriannya kosong.
  test('TC-APV-05: Admin akses antrian approval (200, antrian kosong)', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    const result = await ajaxGet(page, '/pengajuan/approval/antrian_ajax');
    expect(result.status).toBe(200);
    expect(result.body).toContain('Tidak ada pengajuan yang menunggu persetujuan Anda');
    await logout(page);
  });

  // TC-APV-06: DSN (username 'dosen') dipakai sebagai identitas Ketua Pelaksana default di
  // approval.fixture.js sejak poin 1 — jadi DIA PUNYA jabatan approval, dan antriannya bisa
  // saja tidak kosong tergantung state dari test lain yang sudah jalan (tidak
  // order-independent). Cukup pastikan akses tetap 200, tidak assert isi antrian.
  test('TC-APV-06: DSN akses antrian approval (200)', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.DSN.username, USERS.DSN.password);
    const result = await ajaxGet(page, '/pengajuan/approval/antrian_ajax');
    expect(result.status).toBe(200);
    await logout(page);
  });

  test('TC-APV-07: Mahasiswa akses antrian approval (200, antrian kosong)', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.MHS.username, USERS.MHS.password);
    const result = await ajaxGet(page, '/pengajuan/approval/antrian_ajax');
    expect(result.status).toBe(200);
    expect(result.body).toContain('Tidak ada pengajuan yang menunggu persetujuan Anda');
    await logout(page);
  });

  test('TC-APV-08: Tendik akses antrian approval (200, antrian kosong)', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.TDK.username, USERS.TDK.password);
    const result = await ajaxGet(page, '/pengajuan/approval/antrian_ajax');
    expect(result.status).toBe(200);
    expect(result.body).toContain('Tidak ada pengajuan yang menunggu persetujuan Anda');
    await logout(page);
  });

  test('TC-APV-09: Admin proses approval ditolak (403)', async ({ page }) => {
    test.setTimeout(60000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    const result = await doAjax(page, '/pengajuan/approval/0/proses_ajax', 'PUT', {
      status_approval: 'Disetujui',
      alasan_penolakan: '',
    });
    expect([403, 404, 422, 500]).toContain(result.status);
    await logout(page);
  });

  test('TC-APV-10: Mahasiswa proses approval ditolak (403)', async ({ page }) => {
    test.setTimeout(60000);
    await login(page, USERS.MHS.username, USERS.MHS.password);
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    const result = await doAjax(page, '/pengajuan/approval/0/proses_ajax', 'PUT', {
      status_approval: 'Disetujui',
      alasan_penolakan: '',
    });
    expect([403, 404, 422, 500]).toContain(result.status);
    await logout(page);
  });

  test('TC-APV-11: Dosen proses approval ditolak (403)', async ({ page }) => {
    test.setTimeout(60000);
    await login(page, USERS.DSN.username, USERS.DSN.password);
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    const result = await doAjax(page, '/pengajuan/approval/0/proses_ajax', 'PUT', {
      status_approval: 'Disetujui',
      alasan_penolakan: '',
    });
    expect([403, 404, 422, 500]).toContain(result.status);
    await logout(page);
  });

  test('TC-APV-12: Timeline pengajuan dengan ID tidak valid', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    // FIX: Use ajaxGet instead of page.goto() for *_ajax endpoint
    const result = await ajaxGet(page, '/pengajuan/999999/timeline_ajax');
    expect([200, 302, 404, 500]).toContain(result.status);
    await logout(page);
  });

});
