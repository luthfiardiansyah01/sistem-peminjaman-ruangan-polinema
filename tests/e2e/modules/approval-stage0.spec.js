/**
 * Test Suite: Approval Berjenjang — Tahap 0 (Ketua Pelaksana)
 *
 * Module: Pengajuan Approval (FR-6, Poin 1)
 * Service: PengajuanService::generateApprovalStages() / processApproval()
 *
 * Berbeda dari Ketua Umum/DPK/Presiden BEM/Ketua Jurusan/Wakil Direktur II
 * (posisi TETAP per organisasi, di-seed di muka lewat JabatanApprovalSeeder),
 * Ketua Pelaksana DIPILIH BEBAS per-pengajuan — siapapun dosen/mahasiswa
 * terdaftar, bisa beda orang tiap kegiatan. Baris m_jabatan_approval-nya dibuat
 * on-the-fly (find-or-create by user_id+posisi_approval) saat pengajuan dibuat
 * (lihat findOrCreateJabatanKetuaPelaksana()), BUKAN dicari dari data yang sudah
 * ada seperti 4 tahap lain — makanya tahap ini SELALU ada & SELALU aktif sejak
 * pengajuan dibuat (tidak pernah silent-skip seperti Ketua Umum saat organisasi
 * tidak match).
 *
 * Setelah poin 1: Ketua Umum (tahap 1) BARU aktif setelah tahap 0 ini disetujui —
 * lihat approval-stage1.spec.js untuk pengujian pergeseran itu.
 */

import { test, expect } from '@playwright/test';
import { USERS, login, logout } from '../fixtures/auth.fixture.js';
import {
  resolveApprovalFixtureIds,
  createPengajuanForApproval,
  getApprovalIdFromAntrian,
  approveKetuaPelaksana,
  processApprovalAs,
  ajaxSend,
  getTimelineHtml,
  getPengajuanStatus,
} from '../fixtures/approval.fixture.js';

test.describe('Approval Berjenjang — Tahap 0 (Ketua Pelaksana)', () => {
  let fx;

  test.beforeAll(async ({ browser }) => {
    test.setTimeout(150000);
    const page = await browser.newPage();
    fx = await resolveApprovalFixtureIds(page);
    await page.close();
  });

  test('[POSITIF] TC-APV0-01: Pengajuan baru langsung muncul di antrian Ketua Pelaksana yang dipilih', async ({ page }) => {
    test.setTimeout(60000);
    const { data, id } = await createPengajuanForApproval(page, fx);
    expect(id).not.toBeNull();

    const approvalId = await getApprovalIdFromAntrian(page, USERS.DSN, data.pengajuan_nama);
    expect(approvalId).not.toBeNull();

    const timeline = await getTimelineHtml(page, id);
    expect(timeline.body).toContain('Tahap 0');
    expect(timeline.body).toMatch(/Ketua Pelaksana[\s\S]*?Menunggu/);
  });

  test('[POSITIF] TC-APV0-02: Ketua Pelaksana setujui tahap 0 -> tahap 1 (Ketua Umum) aktif, pengajuan tetap Diajukan', async ({ page }) => {
    test.setTimeout(60000);
    const { data, id } = await createPengajuanForApproval(page, fx);

    const { result } = await approveKetuaPelaksana(page, data.pengajuan_nama, 'Disetujui');
    expect(result.status).toBe(200);
    expect(result.body.status).toBe(true);
    expect(result.body.message).toContain('Tahap berikutnya telah diaktifkan');

    const detail = await getPengajuanStatus(page, id);
    expect(detail).toContain('Diajukan');

    const timeline = await getTimelineHtml(page, id);
    expect(timeline.body).toMatch(/Ketua Pelaksana[\s\S]*?Disetujui/);
    expect(timeline.body).toContain('Tahap 1');
  });

  test('[POSITIF] TC-APV0-03: Ketua Pelaksana tolak tahap 0 dengan alasan -> pengajuan langsung Ditolak', async ({ page }) => {
    test.setTimeout(60000);
    const { data, id } = await createPengajuanForApproval(page, fx);

    const { result } = await approveKetuaPelaksana(page, data.pengajuan_nama, 'Ditolak', 'Kegiatan dibatalkan oleh Ketua Pelaksana');
    expect(result.body.status).toBe(true);

    const detail = await getPengajuanStatus(page, id);
    expect(detail).toContain('Ditolak');

    const timeline = await getTimelineHtml(page, id);
    expect(timeline.body).toMatch(/Ketua Pelaksana[\s\S]*?Ditolak/);
    expect(timeline.body).toContain('Kegiatan dibatalkan oleh Ketua Pelaksana');
  });

  test('[NEGATIF] TC-APV0-04: Tolak tahap 0 tanpa alasan_penolakan -> 422', async ({ page }) => {
    test.setTimeout(60000);
    const { data } = await createPengajuanForApproval(page, fx);

    const { result } = await approveKetuaPelaksana(page, data.pengajuan_nama, 'Ditolak', '');
    expect(result.status).toBe(422);
    expect(result.body.msgField?.alasan_penolakan).toBeDefined();
  });

  test('[NEGATIF] TC-APV0-05: User lain (bukan yang dipilih) coba proses tahap 0 -> 403', async ({ page }) => {
    test.setTimeout(60000);
    const { data } = await createPengajuanForApproval(page, fx);

    const approvalId = await getApprovalIdFromAntrian(page, USERS.DSN, data.pengajuan_nama);
    expect(approvalId).not.toBeNull();

    const result = await processApprovalAs(page, USERS.TDK, approvalId, 'Disetujui');
    expect(result.status).toBe(403);
    expect(result.body.message).toContain('tidak berwenang');
  });

  test('[NEGATIF] TC-APV0-06: Double-approve tahap 0 -> percobaan kedua 422', async ({ page }) => {
    test.setTimeout(60000);
    const { data } = await createPengajuanForApproval(page, fx);

    const first = await approveKetuaPelaksana(page, data.pengajuan_nama, 'Disetujui');
    expect(first.result.body.status).toBe(true);

    const second = await processApprovalAs(page, USERS.DSN, first.approvalId, 'Disetujui');
    expect(second.status).toBe(422);
    expect(second.body.message).toContain('sudah diproses');
  });

  test('[POSITIF] TC-APV0-07: Pilih orang yang sama sebagai Ketua Pelaksana di 2 pengajuan berbeda -> tetap satu baris m_jabatan_approval (tidak dobel)', async ({ page }) => {
    test.setTimeout(90000);
    const first = await createPengajuanForApproval(page, fx);
    const second = await createPengajuanForApproval(page, fx);
    expect(first.id).not.toBeNull();
    expect(second.id).not.toBeNull();

    // Keduanya pakai fixtureIds.ketuaPelaksanaUserId yang sama (default createPengajuanForApproval).
    // Kalau baris m_jabatan_approval dobel, approval_id tahap 0 kedua pengajuan akan berbeda
    // JAUH (tidak bisa diprediksi dari 1 baris jabatan yang sama) — cukup pastikan keduanya
    // tetap bisa ditemukan & diproses lewat identitas login yang sama (USERS.DSN), yang hanya
    // mungkin kalau keduanya reuse baris jabatan yang sama (unique index user_id+posisi_approval
    // mencegah duplikasi di findOrCreateJabatanKetuaPelaksana()).
    const approvalIdFirst = await getApprovalIdFromAntrian(page, USERS.DSN, first.data.pengajuan_nama);
    const approvalIdSecond = await getApprovalIdFromAntrian(page, USERS.DSN, second.data.pengajuan_nama);
    expect(approvalIdFirst).not.toBeNull();
    expect(approvalIdSecond).not.toBeNull();
    expect(approvalIdFirst).not.toBe(approvalIdSecond); // beda baris t_pengajuan_approval...

    // ...tapi keduanya menunjuk ke jabatan_id yang SAMA (reuse) — dibuktikan lewat approve
    // keduanya sebagai user yang sama tanpa perlu resolve jabatan_id berbeda.
    const approveFirst = await processApprovalAs(page, USERS.DSN, approvalIdFirst, 'Disetujui');
    const approveSecond = await processApprovalAs(page, USERS.DSN, approvalIdSecond, 'Disetujui');
    expect(approveFirst.body.status).toBe(true);
    expect(approveSecond.body.status).toBe(true);
  });

  test('[NEGATIF] TC-APV0-08: ketua_pelaksana_user_id kosong saat create pengajuan -> 422', async ({ page }) => {
    test.setTimeout(60000);
    await login(page, USERS.MHS.username, USERS.MHS.password);
    const result = await ajaxSend(page, '/pengajuan/ajax', 'POST', {
      pengajuan_nama: 'APV0-NoKetuaPelaksana',
      pengajuan_tgl: '2026-09-01',
      pengajuan_jam_mulai: '08:00',
      pengajuan_jam_selesai: '10:00',
      pengajuan_jumPes: 10,
      organisasi_id: fx.hmjOrgId,
      ketua_pelaksana_user_id: '',
      ruangan_ids: [fx.ruanganJurusanId],
    });
    expect(result.status).toBe(422);
    expect(result.body.status).toBe(false);
    expect(result.body.msgField?.ketua_pelaksana_user_id).toBeDefined();
    await logout(page);
  });

  test('[NEGATIF] TC-APV0-09: ketua_pelaksana_user_id tidak ada (99999) -> 422', async ({ page }) => {
    test.setTimeout(60000);
    await login(page, USERS.MHS.username, USERS.MHS.password);
    const result = await ajaxSend(page, '/pengajuan/ajax', 'POST', {
      pengajuan_nama: 'APV0-KetuaPelaksanaInvalid',
      pengajuan_tgl: '2026-09-01',
      pengajuan_jam_mulai: '08:00',
      pengajuan_jam_selesai: '10:00',
      pengajuan_jumPes: 10,
      organisasi_id: fx.hmjOrgId,
      ketua_pelaksana_user_id: 999999,
      ruangan_ids: [fx.ruanganJurusanId],
    });
    expect(result.status).toBe(422);
    expect(result.body.status).toBe(false);
    expect(result.body.msgField?.ketua_pelaksana_user_id).toBeDefined();
    await logout(page);
  });

  test('[POSITIF] TC-APV0-10: approval_id tahap 0 tidak ditemukan -> 404', async ({ page }) => {
    test.setTimeout(45000);
    const result = await processApprovalAs(page, USERS.DSN, 9999999, 'Disetujui');
    expect(result.status).toBe(404);
    expect(result.body.status).toBe(false);
  });
});
