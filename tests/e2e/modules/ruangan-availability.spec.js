/**
 * Test Suite: Status Ruangan Per-Tanggal (Poin 2)
 *
 * Module: Ruangan Availability
 * Service: RuanganService::availabilityStatusForDate() / availabilityForAllRoomsOnDate() / monthlyAvailability()
 * Controller: RuanganController::availability_ajax / kalender_ajax / kalender_data_ajax
 *
 * Precedence status (lihat RuanganService.php):
 *   1. ruangan_status='Tidak Tersedia' (override manual) -> selalu Tidak Tersedia
 *   2. Ada Jadwal utk ruangan itu di tanggal itu dgn status != 'Selesai' -> Tidak Tersedia
 *   3. Ada Pengajuan utk ruangan itu di tanggal itu dgn status = 'Diajukan' -> Diajukan
 *   4. Selain itu -> Tersedia
 *
 * Strategy: pakai helper approval.fixture.js utk membangun data nyata (pengajuan
 * pending = kasus "Diajukan"; pengajuan yang lolos seluruh tahap approval = kasus
 * "Tidak Tersedia" via Jadwal yang otomatis dibuat). Route *_ajax yang gated
 * $request->ajax() diakses lewat jQuery $.ajax (bukan page.goto()) — lihat
 * catatan yang sama di approval.fixture.js.
 */

import { test, expect } from '@playwright/test';
import { USERS, login, logout } from '../fixtures/auth.fixture.js';
import {
  VRF_USERS,
  resolveApprovalFixtureIds,
  createPengajuanForApproval,
  approveAsVerifikator,
  approveKetuaPelaksana,
} from '../fixtures/approval.fixture.js';

async function ensureJqueryContext(page) {
  const hasContext = await page.evaluate(() => typeof $ !== 'undefined' && typeof $.ajax !== 'undefined').catch(() => false);
  if (!hasContext) {
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {});
    await page.waitForTimeout(500);
  }
}

async function ajaxGetJson(page, url) {
  await ensureJqueryContext(page);
  return page.evaluate((requestUrl) => {
    return new Promise((resolve) => {
      $.ajax({
        url: requestUrl,
        type: 'GET',
        dataType: 'json',
        success: (response) => resolve({ status: 200, body: response }),
        error: (xhr) => {
          try {
            resolve({ status: xhr.status, body: JSON.parse(xhr.responseText) });
          } catch (e) {
            resolve({ status: xhr.status, body: xhr.responseText });
          }
        },
      });
    });
  }, url);
}

async function ajaxGetHtml(page, url) {
  await ensureJqueryContext(page);
  return page.evaluate((requestUrl) => {
    return new Promise((resolve) => {
      $.ajax({
        url: requestUrl,
        type: 'GET',
        dataType: 'html',
        success: (response) => resolve({ status: 200, body: String(response) }),
        error: (xhr) => resolve({ status: xhr.status, body: xhr.responseText || '' }),
      });
    });
  }, url);
}

async function ajaxSend(page, url, method, data = {}) {
  await ensureJqueryContext(page);
  const token = await page.evaluate(() => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
  const payload = { ...data, _token: token };
  if (method === 'PUT' || method === 'DELETE') payload._method = method;

  return page.evaluate(({ url, payload }) => {
    return new Promise((resolve) => {
      $.ajax({
        url, type: 'POST', data: payload, dataType: 'json',
        success: (response) => resolve({ status: 200, body: response }),
        error: (xhr) => {
          try {
            resolve({ status: xhr.status, body: JSON.parse(xhr.responseText) });
          } catch (e) {
            resolve({ status: xhr.status, body: { status: false, message: xhr.statusText } });
          }
        },
      });
    });
  }, { url, payload });
}

test.describe('Status Ruangan Per-Tanggal (Poin 2)', () => {
  let fx;

  test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    fx = await resolveApprovalFixtureIds(page);
    await page.close();
  });

  test('[NEGATIF] TC-AV-01: availability_ajax tanpa parameter tanggal -> 422', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    const result = await ajaxGetJson(page, '/ruangan/availability_ajax');
    expect(result.status).toBe(422);
    await logout(page);
  });

  test('[NEGATIF] TC-AV-02: availability_ajax dengan tanggal tidak valid -> 422', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    const result = await ajaxGetJson(page, '/ruangan/availability_ajax?tanggal=bukan-tanggal');
    expect(result.status).toBe(422);
    await logout(page);
  });

  test('[POSITIF] TC-AV-03: Ruangan tanpa pengajuan/jadwal di tanggal kosong -> Tersedia', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    // Tanggal jauh di masa depan, hampir pasti belum ada booking apapun.
    const result = await ajaxGetJson(page, '/ruangan/availability_ajax?tanggal=2030-01-15');
    expect(result.status).toBe(200);
    expect(Array.isArray(result.body)).toBe(true);
    const ruanganJurusan = result.body.find((r) => r.ruangan_id === fx.ruanganJurusanId);
    expect(ruanganJurusan.status).toBe('Tersedia');
    await logout(page);
  });

  test('[POSITIF] TC-AV-04: Ruangan dengan pengajuan status Diajukan di tanggal itu -> Diajukan', async ({ page }) => {
    test.setTimeout(60000);
    const tanggal = '2030-02-10';
    const { data } = await createPengajuanForApproval(page, fx, {
      pengajuan_tgl: tanggal,
      ruangan_ids: [fx.ruanganJurusanId],
    });
    expect(data.pengajuan_tgl).toBe(tanggal);

    await login(page, USERS.ADM.username, USERS.ADM.password);
    const result = await ajaxGetJson(page, `/ruangan/availability_ajax?tanggal=${tanggal}`);
    const ruanganJurusan = result.body.find((r) => r.ruangan_id === fx.ruanganJurusanId);
    expect(ruanganJurusan.status).toBe('Diajukan');
    await logout(page);
  });

  test('[POSITIF] TC-AV-05: Ruangan dengan Jadwal aktif (pengajuan lolos semua tahap approval) -> Tidak Tersedia', async ({ page }) => {
    test.setTimeout(150000);
    const tanggal = '2030-03-20';
    const { data, id } = await createPengajuanForApproval(page, fx, {
      pengajuan_tgl: tanggal,
      ruangan_ids: [fx.ruanganJurusanId],
    });
    expect(id).not.toBeNull();

    const s0 = await approveKetuaPelaksana(page, data.pengajuan_nama, 'Disetujui');
    expect(s0.result.body.status).toBe(true);
    const s1 = await approveAsVerifikator(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama, 'Disetujui');
    expect(s1.result.body.status).toBe(true);
    const s2a = await approveAsVerifikator(page, VRF_USERS.DPK, data.pengajuan_nama, 'Disetujui');
    expect(s2a.result.body.status).toBe(true);
    const s2b = await approveAsVerifikator(page, VRF_USERS.PRESIDEN_BEM, data.pengajuan_nama, 'Disetujui');
    expect(s2b.result.body.status).toBe(true);
    const s3 = await approveAsVerifikator(page, VRF_USERS.KETUA_JURUSAN, data.pengajuan_nama, 'Disetujui');
    expect(s3.result.body.message).toContain('diterima');

    await login(page, USERS.ADM.username, USERS.ADM.password);
    const result = await ajaxGetJson(page, `/ruangan/availability_ajax?tanggal=${tanggal}`);
    const ruanganJurusan = result.body.find((r) => r.ruangan_id === fx.ruanganJurusanId);
    expect(ruanganJurusan.status).toBe('Tidak Tersedia');

    // kalender_data_ajax bulan yang sama harus konsisten dengan availability_ajax.
    const kalender = await ajaxGetJson(page, `/ruangan/${fx.ruanganJurusanId}/kalender_data_ajax?bulan=2030-03`);
    expect(kalender.status).toBe(200);
    expect(kalender.body[tanggal]).toBe('Tidak Tersedia');
    await logout(page);
  });

  test('[POSITIF] TC-AV-06: ruangan_status Tidak Tersedia (override manual) -> selalu Tidak Tersedia walau tanggal kosong', async ({ page }) => {
    test.setTimeout(60000);
    await login(page, USERS.ADM.username, USERS.ADM.password);

    const before = await ajaxGetJson(page, '/ruangan/availability_ajax?tanggal=2030-04-01');
    const ruanganUmumSebelum = before.body.find((r) => r.ruangan_id === fx.ruanganUmumId);
    expect(ruanganUmumSebelum.status).toBe('Tersedia');

    const update = await ajaxSend(page, `/ruangan/${fx.ruanganUmumId}/update_ajax`, 'PUT', {
      ruangan_kode: 'RUM',
      ruangan_nama: 'Ruangan Umum E2E',
      ruangan_fasilitas: 'AC, proyektor',
      ruangan_kuota: 50,
      ruangan_kategori: 'Umum',
      ruangan_status: 'Tidak Tersedia',
    });
    expect(update.body.status).toBe(true);

    const after = await ajaxGetJson(page, '/ruangan/availability_ajax?tanggal=2030-04-01');
    const ruanganUmumSesudah = after.body.find((r) => r.ruangan_id === fx.ruanganUmumId);
    expect(ruanganUmumSesudah.status).toBe('Tidak Tersedia');

    // Kembalikan ke Tersedia supaya tidak mengganggu test lain yang memakai ruanganUmumId.
    await ajaxSend(page, `/ruangan/${fx.ruanganUmumId}/update_ajax`, 'PUT', {
      ruangan_kode: 'RUM',
      ruangan_nama: 'Ruangan Umum E2E',
      ruangan_fasilitas: 'AC, proyektor',
      ruangan_kuota: 50,
      ruangan_kategori: 'Umum',
      ruangan_status: 'Tersedia',
    });
    await logout(page);
  });

  test('[NEGATIF] TC-AV-07: kalender_data_ajax dengan format bulan tidak valid -> 422', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    const result = await ajaxGetJson(page, `/ruangan/${fx.ruanganJurusanId}/kalender_data_ajax?bulan=2030-3`);
    expect(result.status).toBe(422);
    await logout(page);
  });

  test('[POSITIF] TC-AV-08: kalender_data_ajax mengembalikan seluruh tanggal dalam bulan (default Tersedia)', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    const result = await ajaxGetJson(page, `/ruangan/${fx.ruanganJurusanId}/kalender_data_ajax?bulan=2030-06`);
    expect(result.status).toBe(200);
    expect(Object.keys(result.body).length).toBe(30); // Juni = 30 hari
    expect(result.body['2030-06-01']).toBe('Tersedia');
    expect(result.body['2030-06-30']).toBe('Tersedia');
    await logout(page);
  });

  test('[POSITIF] TC-AV-09: kalender_ajax merender modal berisi nama ruangan', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    const result = await ajaxGetHtml(page, `/ruangan/${fx.ruanganJurusanId}/kalender_ajax`);
    expect(result.status).toBe(200);
    expect(result.body).toContain('Kalender Ketersediaan');
    expect(result.body).toContain('Ruangan 1');
    await logout(page);
  });

  test('[NEGATIF] TC-AV-10: kalender_ajax untuk ruangan tidak ditemukan', async ({ page }) => {
    test.setTimeout(45000);
    await login(page, USERS.ADM.username, USERS.ADM.password);
    const result = await ajaxGetHtml(page, '/ruangan/999999/kalender_ajax');
    expect([404, 200]).toContain(result.status);
    if (result.status === 200) {
      expect(result.body).toContain('tidak ditemukan');
    }
    await logout(page);
  });
});
