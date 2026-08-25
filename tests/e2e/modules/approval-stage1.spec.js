/**
 * Test Suite: Approval Berjenjang — Tahap 1 (Ketua Umum)
 *
 * Module: Pengajuan Approval (FR-6)
 * Controller: PengajuanController::antrian_ajax / proses_approval_ajax
 * Service: PengajuanService::processApproval() / generateApprovalStages()
 *
 * Sejak poin 1 (tahap "Ketua Pelaksana"), Ketua Umum BUKAN lagi tahap yang aktif
 * duluan — dia menunggu tahap 0 (Ketua Pelaksana) disetujui dulu (lihat
 * generateApprovalStages(): batas_waktu Ketua Umum selalu null saat dibuat, baru
 * diaktifkan oleh processApproval() setelah tahap 0 selesai). Setiap test di sini
 * yang butuh tahap Ketua Umum aktif memakai helper `createAndConfirmKetuaPelaksana()`
 * di bawah, bukan `createPengajuanForApproval()` polos.
 *
 * Tahap 1 sendiri baru dibuat sama sekali jika ada jabatan dengan posisi_approval=
 * 'Ketua Umum' DAN organisasi_id sama dengan organisasi_id pengajuan (lihat
 * generateApprovalStages() di app/Services/PengajuanService.php). Kalau tidak ada
 * yang cocok, tahap ini dilewati diam-diam.
 *
 * Fixture: HMJ (organisasi_id dari resolveApprovalFixtureIds) -> jabatan Ketua Umum
 * (lihat database/seeders/JabatanApprovalSeeder.php).
 */

import { test, expect } from '@playwright/test';
import { USERS, login, logout } from '../fixtures/auth.fixture.js';
import {
  VRF_USERS,
  resolveApprovalFixtureIds,
  createPengajuanForApproval,
  getApprovalIdFromAntrian,
  approveAsVerifikator,
  approveKetuaPelaksana,
  processApprovalAs,
  getTimelineHtml,
  getPengajuanStatus,
} from '../fixtures/approval.fixture.js';

test.describe('Approval Berjenjang — Tahap 1 (Ketua Umum)', () => {
  let fx;

  test.beforeAll(async ({ browser }) => {
    test.setTimeout(150000);
    const page = await browser.newPage();
    fx = await resolveApprovalFixtureIds(page);
    await page.close();
  });

  async function createAndConfirmKetuaPelaksana(page, overrides = {}) {
    const created = await createPengajuanForApproval(page, fx, overrides);
    const stage0 = await approveKetuaPelaksana(page, created.data.pengajuan_nama, 'Disetujui');
    expect(stage0.result.body.status).toBe(true);
    return created;
  }

  test('[POSITIF] TC-APV1-01: Setelah Ketua Pelaksana disetujui, pengajuan muncul di antrian Ketua Umum', async ({ page }) => {
    test.setTimeout(60000);
    const { data, id } = await createAndConfirmKetuaPelaksana(page);
    expect(id).not.toBeNull();

    const approvalId = await getApprovalIdFromAntrian(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama);
    expect(approvalId).not.toBeNull();
  });

  test('[POSITIF] TC-APV1-01b: Sebelum Ketua Pelaksana disetujui, Ketua Umum belum melihat pengajuan di antriannya', async ({ page }) => {
    test.setTimeout(60000);
    const { data, id } = await createPengajuanForApproval(page, fx);
    expect(id).not.toBeNull();

    const approvalId = await getApprovalIdFromAntrian(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama);
    expect(approvalId).toBeNull();
  });

  test('[POSITIF] TC-APV1-02: Ketua Umum setujui tahap 1 -> tahap 2 (Presiden BEM) aktif, pengajuan tetap Diajukan', async ({ page }) => {
    test.setTimeout(60000);
    const { data, id } = await createAndConfirmKetuaPelaksana(page);

    const { result } = await approveAsVerifikator(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama, 'Disetujui');
    expect(result.status).toBe(200);
    expect(result.body.status).toBe(true);
    expect(result.body.message).toContain('Tahap berikutnya telah diaktifkan');

    const detail = await getPengajuanStatus(page, id);
    expect(detail).toContain('Diajukan');

    const timeline = await getTimelineHtml(page, id);
    expect(timeline.body).toContain('Tahap 1');
    expect(timeline.body).toMatch(/Ketua Umum[\s\S]*?Disetujui/);
    // Sejak alur jadi sekuensial, tahap 2 HANYA berisi Presiden BEM (bukan lagi
    // paralel dengan DPK) — DPK baru muncul di tahap 3. Baris tahap 3/4 SUDAH ADA
    // di timeline sejak awal (generateApprovalStages membuat semua baris di muka),
    // jadi yang dicek di sini adalah STATUSNYA ('Menunggu'), bukan ketiadaannya.
    expect(timeline.body).toContain('Tahap 2');
    expect(timeline.body).toMatch(/Tahap 2[\s\S]*?Presiden BEM[\s\S]*?Menunggu/);
    expect(timeline.body).toMatch(/Tahap 3[\s\S]*?DPK[\s\S]*?Menunggu/);
  });

  test('[POSITIF] TC-APV1-03: Ketua Umum tolak tahap 1 dengan alasan -> pengajuan langsung Ditolak', async ({ page }) => {
    test.setTimeout(60000);
    const { data, id } = await createAndConfirmKetuaPelaksana(page);

    const { result } = await approveAsVerifikator(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama, 'Ditolak', 'Kegiatan tidak sesuai ketentuan organisasi');
    expect(result.status).toBe(200);
    expect(result.body.status).toBe(true);

    const detail = await getPengajuanStatus(page, id);
    expect(detail).toContain('Ditolak');

    const timeline = await getTimelineHtml(page, id);
    expect(timeline.body).toMatch(/Ketua Umum[\s\S]*?Ditolak/);
    expect(timeline.body).toContain('Kegiatan tidak sesuai ketentuan organisasi');
  });

  test('[NEGATIF] TC-APV1-04: Tolak tahap 1 tanpa alasan_penolakan -> 422', async ({ page }) => {
    test.setTimeout(60000);
    const { data } = await createAndConfirmKetuaPelaksana(page);

    const { result } = await approveAsVerifikator(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama, 'Ditolak', '');
    expect(result.status).toBe(422);
    expect(result.body.status).toBe(false);
    expect(result.body.msgField?.alasan_penolakan).toBeDefined();
  });

  test('[NEGATIF] TC-APV1-05: Verifikator lain (DPK) coba proses approval_id milik Ketua Umum -> 403', async ({ page }) => {
    test.setTimeout(60000);
    const { data } = await createAndConfirmKetuaPelaksana(page);

    const approvalId = await getApprovalIdFromAntrian(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama);
    expect(approvalId).not.toBeNull();

    const result = await processApprovalAs(page, VRF_USERS.DPK, approvalId, 'Disetujui');
    expect(result.status).toBe(403);
    expect(result.body.status).toBe(false);
    expect(result.body.message).toContain('tidak berwenang');
  });

  test('[NEGATIF] TC-APV1-06: Double-approve tahap 1 -> percobaan kedua 422', async ({ page }) => {
    test.setTimeout(60000);
    const { data } = await createAndConfirmKetuaPelaksana(page);

    const first = await approveAsVerifikator(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama, 'Disetujui');
    expect(first.result.body.status).toBe(true);

    const approvalId = first.approvalId;
    const second = await processApprovalAs(page, VRF_USERS.KETUA_UMUM, approvalId, 'Disetujui');
    expect(second.status).toBe(422);
    expect(second.body.status).toBe(false);
    expect(second.body.message).toContain('sudah diproses');
  });

  test('[NEGATIF] TC-APV1-07: status_approval tidak valid -> 422', async ({ page }) => {
    test.setTimeout(60000);
    const { data } = await createAndConfirmKetuaPelaksana(page);
    const approvalId = await getApprovalIdFromAntrian(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama);

    const result = await processApprovalAs(page, VRF_USERS.KETUA_UMUM, approvalId, 'StatusAsal');
    expect(result.status).toBe(422);
    expect(result.body.status).toBe(false);
    expect(result.body.message).toContain('tidak valid');
  });

  test('[NEGATIF] TC-APV1-08: approval_id tidak ditemukan -> 404', async ({ page }) => {
    test.setTimeout(45000);
    const result = await processApprovalAs(page, VRF_USERS.KETUA_UMUM, 9999999, 'Disetujui');
    expect(result.status).toBe(404);
    expect(result.body.status).toBe(false);
  });

  test('[NEGATIF] TC-APV1-09: User tanpa jabatan approval sama sekali (ADM/MHS/TDK) bisa akses antrian_ajax tapi antriannya kosong', async ({ page }) => {
    test.setTimeout(60000);
    // Sejak redesain (m_jabatan_approval menggantikan level VRF terpisah — jabatan
    // menempel ke dosen/mahasiswa yang sudah ada), route ini tidak lagi digerbangi per
    // role: authorize:ADM,DSN,TDK,MHS (siapapun yang login boleh masuk). Otorisasi
    // SEBENARNYA (siapa boleh memproses baris approval mana) ditegakkan lebih dalam di
    // PengajuanService::processApproval() lewat ownership jabatanApproval->user_id,
    // dan antrianFor() otomatis balikin collection kosong untuk user tanpa jabatan.
    // USERS.DSN SENGAJA tidak diikutkan di sini — sejak poin 1, akun itu dipakai sebagai
    // Ketua Pelaksana default (fixtureIds.ketuaPelaksanaUserId), jadi ia SELALU punya
    // jabatan approval & antriannya bisa saja tidak kosong (tergantung test lain yang
    // berjalan sebelumnya) — bukan lagi representasi "user tanpa jabatan".
    for (const user of [USERS.ADM, USERS.MHS, USERS.TDK]) {
      await login(page, user.username, user.password);
      const resp = await page.goto('/pengajuan/approval/antrian_ajax', { waitUntil: 'domcontentloaded' });
      expect(resp.status()).toBe(200);
      const text = await page.locator('body').textContent();
      expect(text).toContain('Tidak ada pengajuan yang menunggu persetujuan Anda');
      await logout(page);
    }
  });

  test('[NEGATIF] TC-APV1-10: Pengajuan dengan organisasi BUKAN HMJ -> tahap 1 dilewati diam-diam, tidak ada tahap aktif', async ({ page }) => {
    test.setTimeout(60000);
    const { data, id } = await createPengajuanForApproval(page, fx, { organisasi_id: fx.bemOrgId });
    expect(id).not.toBeNull();

    // Ketua Umum (organisasi HMJ) tidak boleh melihat pengajuan berorganisasi BEM ini di antriannya.
    const approvalId = await getApprovalIdFromAntrian(page, VRF_USERS.KETUA_UMUM, data.pengajuan_nama);
    expect(approvalId).toBeNull();

    // Timeline pun tidak memiliki baris "Ketua Umum" sama sekali (tahap dilewati) — meski
    // tahap 0 "Ketua Pelaksana" tetap ada, karena stage itu tidak bergantung organisasi.
    const timeline = await getTimelineHtml(page, id);
    expect(timeline.body).toContain('Ketua Pelaksana');
    expect(timeline.body).not.toContain('Ketua Umum');
  });
});
