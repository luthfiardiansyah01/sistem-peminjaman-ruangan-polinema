/**
 * Test Suite: Approval Berjenjang — Tahap 2-4 (Presiden BEM -> DPK -> Ketua Jurusan)
 * & Alur Pendek Dosen/Tendik (Ketua Pelaksana -> Ketua Jurusan)
 *
 * Module: Pengajuan Approval (FR-6)
 * Service: PengajuanService::generateApprovalStages() / processApproval() / alurApprovalUntukPengajuan()
 *
 * Alur bercabang menurut ROLE PEMOHON, dan SELURUH tahap sekuensial (tidak ada
 * tahap paralel), dengan ruangan default fixture (ruanganJurusanId, kategori
 * 'Jurusan') sehingga tahap terakhirnya berhenti di Ketua Jurusan:
 * - Mahasiswa: Tahap 0 Ketua Pelaksana -> Tahap 1 Ketua Umum -> Tahap 2 Presiden
 *   BEM -> Tahap 3 DPK -> Tahap 4 Ketua Jurusan (tahap akhir).
 * - Dosen/Tendik: Tahap 0 Ketua Pelaksana -> Tahap 1 Ketua Jurusan (tahap akhir).
 *
 * Saat tahap akhir disetujui: pengajuan_status -> 'Diterima', nomor_surat
 * digenerate, dan sebuah baris Jadwal dibuat otomatis (createJadwalFromPengajuan()).
 * Tombol "Cetak Surat" pada show_ajax hanya muncul setelah status Diterima.
 * 'Wakil Direktur II' adalah tahap TAMBAHAN yang muncul otomatis ketika salah satu
 * ruangan yang dipinjam berkategori 'Umum' (lihat fixtureIds.ruanganUmumId di
 * approval.fixture.js) — belum ada test suite terpisah untuk jalur itu di sini.
 */

import { test, expect } from '@playwright/test';
import { USERS } from '../fixtures/auth.fixture.js';
import {
  VRF_USERS,
  resolveApprovalFixtureIds,
  createPengajuanForApproval,
  approveAsVerifikator,
  approveKetuaPelaksana,
  processApprovalAs,
  computeStageApprovalIds,
  computeShortStageApprovalIds,
  getTimelineHtml,
  getPengajuanStatus,
} from '../fixtures/approval.fixture.js';

/**
 * `ids` dihitung SEBELUM tahap 0 disetujui — lihat catatan di computeStageApprovalIds():
 * lookup approval_id tahap 0 lewat antriannya sendiri, yang hilang begitu disetujui
 * (antrianFor() hanya menampilkan status_approval='Menunggu').
 */
async function createAndApproveThroughStage3(page, fx, overrides = {}) {
  const { data, id } = await createPengajuanForApproval(page, fx, overrides);
  const ids = await computeStageApprovalIds(page, data.pengajuan_nama);
  expect(ids).not.toBeNull();

  const stage0 = await processApprovalAs(page, USERS.DSN, ids.stage0KetuaPelaksana, 'Disetujui');
  expect(stage0.body.status).toBe(true);
  const stage1 = await processApprovalAs(page, VRF_USERS.KETUA_UMUM, ids.stage1KetuaUmum, 'Disetujui');
  expect(stage1.body.status).toBe(true);
  const stage2 = await processApprovalAs(page, VRF_USERS.PRESIDEN_BEM, ids.stage2PresidenBem, 'Disetujui');
  expect(stage2.body.status).toBe(true);
  const stage3 = await processApprovalAs(page, VRF_USERS.DPK, ids.stage3Dpk, 'Disetujui');
  expect(stage3.body.status).toBe(true);
  return { data, id, ids };
}

test.describe('Approval Berjenjang — Tahap 2-4 (Mahasiswa: Presiden BEM -> DPK -> Ketua Jurusan)', () => {
  let fx;

  test.beforeAll(async ({ browser }) => {
    test.setTimeout(150000);
    const page = await browser.newPage();
    fx = await resolveApprovalFixtureIds(page);
    await page.close();
  });

  test('[POSITIF] TC-APV3-01: Presiden BEM setujui tahap 2 -> tahap 3 (DPK) aktif, pengajuan tetap Diajukan', async ({ page }) => {
    test.setTimeout(120000);
    const { id } = await createAndApproveThroughStage3(page, fx);

    const detail = await getPengajuanStatus(page, id);
    expect(detail).toContain('Diajukan');

    const timeline = await getTimelineHtml(page, id);
    expect(timeline.body).toMatch(/Presiden BEM[\s\S]*?Disetujui/);
    expect(timeline.body).toMatch(/DPK[\s\S]*?Disetujui/);
    expect(timeline.body).toContain('Tahap 4');
  });

  test('[POSITIF] TC-APV3-02: Ketua Jurusan setujui tahap akhir -> pengajuan Diterima, nomor_surat & Cetak Surat muncul', async ({ page }) => {
    test.setTimeout(120000);
    const { data, id } = await createAndApproveThroughStage3(page, fx);

    const { result } = await approveAsVerifikator(page, VRF_USERS.KETUA_JURUSAN, data.pengajuan_nama, 'Disetujui');
    expect(result.status).toBe(200);
    expect(result.body.status).toBe(true);
    expect(result.body.message).toContain('diterima');

    const detail = await getPengajuanStatus(page, id);
    expect(detail).toContain('Diterima');
    expect(detail).toContain('cetak_surat_ajax');

    const timeline = await getTimelineHtml(page, id);
    expect(timeline.body).toMatch(/Tahap 4[\s\S]*?Ketua Jurusan[\s\S]*?Disetujui/);
  });

  test('[POSITIF] TC-APV3-03: Presiden BEM tolak tahap 2 -> pengajuan langsung Ditolak, DPK tidak pernah aktif', async ({ page }) => {
    test.setTimeout(90000);
    const { data, id } = await createPengajuanForApproval(page, fx);
    const ids = await computeStageApprovalIds(page, data.pengajuan_nama);
    expect(ids).not.toBeNull();

    const stage0 = await processApprovalAs(page, USERS.DSN, ids.stage0KetuaPelaksana, 'Disetujui');
    expect(stage0.body.status).toBe(true);
    const stage1 = await processApprovalAs(page, VRF_USERS.KETUA_UMUM, ids.stage1KetuaUmum, 'Disetujui');
    expect(stage1.body.status).toBe(true);

    const { result } = await approveAsVerifikator(page, VRF_USERS.PRESIDEN_BEM, data.pengajuan_nama, 'Ditolak', 'Bertentangan dengan agenda BEM');
    expect(result.body.status).toBe(true);

    const detail = await getPengajuanStatus(page, id);
    expect(detail).toContain('Ditolak');

    const timeline = await getTimelineHtml(page, id);
    expect(timeline.body).toMatch(/Presiden BEM[\s\S]*?Ditolak/);
    // Baris tahap 3 (DPK) SUDAH ADA di timeline sejak awal (semua baris dibuat di
    // muka oleh generateApprovalStages) — yang dipastikan di sini adalah statusnya
    // TETAP 'Menunggu' (tidak pernah diaktifkan/diproses), bukan ketiadaan teksnya.
    expect(timeline.body).toMatch(/Tahap 3[\s\S]*?DPK[\s\S]*?Menunggu/);
  });

  test('[POSITIF] TC-APV3-04: DPK tolak tahap 3 -> pengajuan Ditolak, tidak ada Cetak Surat', async ({ page }) => {
    test.setTimeout(120000);
    const { data, id } = await createPengajuanForApproval(page, fx);
    const ids = await computeStageApprovalIds(page, data.pengajuan_nama);
    expect(ids).not.toBeNull();

    await processApprovalAs(page, USERS.DSN, ids.stage0KetuaPelaksana, 'Disetujui');
    await processApprovalAs(page, VRF_USERS.KETUA_UMUM, ids.stage1KetuaUmum, 'Disetujui');
    await processApprovalAs(page, VRF_USERS.PRESIDEN_BEM, ids.stage2PresidenBem, 'Disetujui');

    const { result } = await approveAsVerifikator(page, VRF_USERS.DPK, data.pengajuan_nama, 'Ditolak', 'Jadwal bentrok agenda internal DPK');
    expect(result.body.status).toBe(true);

    const detail = await getPengajuanStatus(page, id);
    expect(detail).toContain('Ditolak');
    expect(detail).not.toContain('cetak_surat_ajax');
  });

  test('[POSITIF] TC-APV3-05: Ketua Jurusan tolak tahap akhir -> pengajuan Ditolak, tidak ada Cetak Surat', async ({ page }) => {
    test.setTimeout(120000);
    const { data, id } = await createAndApproveThroughStage3(page, fx);

    const { result } = await approveAsVerifikator(page, VRF_USERS.KETUA_JURUSAN, data.pengajuan_nama, 'Ditolak', 'Ruangan sedang dijadwalkan untuk sidang jurusan');
    expect(result.body.status).toBe(true);

    const detail = await getPengajuanStatus(page, id);
    expect(detail).toContain('Ditolak');
    expect(detail).not.toContain('cetak_surat_ajax');
  });

  test('[NEGATIF] TC-APV3-06: Presiden BEM coba proses approval_id tahap 3 (milik DPK) -> 403', async ({ page }) => {
    test.setTimeout(90000);
    const { ids } = await createAndApproveThroughStage3(page, fx); // sudah lunas s/d DPK, dipakai hanya utk ids
    const result = await processApprovalAs(page, VRF_USERS.PRESIDEN_BEM, ids.stage3Dpk, 'Disetujui');
    expect(result.status).toBe(403);
    expect(result.body.message).toContain('tidak berwenang');
  });

  test('[NEGATIF] TC-APV3-07: DPK coba proses approval_id tahap 4 (milik Ketua Jurusan) -> 403', async ({ page }) => {
    test.setTimeout(90000);
    const { ids } = await createAndApproveThroughStage3(page, fx);
    const result = await processApprovalAs(page, VRF_USERS.DPK, ids.stage4KetuaJurusan, 'Disetujui');
    expect(result.status).toBe(403);
  });

  test('[EDGE] TC-APV3-08: Tahap 3 (DPK) diproses sebelum tahap 2 (Presiden BEM) disetujui -> tetap diproses (tidak ada guard urutan eksplisit)', async ({ page }) => {
    test.setTimeout(90000);
    const { data } = await createPengajuanForApproval(page, fx);
    const ids = await computeStageApprovalIds(page, data.pengajuan_nama);
    expect(ids).not.toBeNull();

    await processApprovalAs(page, USERS.DSN, ids.stage0KetuaPelaksana, 'Disetujui');
    await processApprovalAs(page, VRF_USERS.KETUA_UMUM, ids.stage1KetuaUmum, 'Disetujui');
    // Sengaja TIDAK memproses tahap 2 (Presiden BEM) sama sekali.

    // Baris tahap 3 sudah dibuat sejak awal (hanya batas_waktu-nya null / belum "aktif"
    // di UI antrian), dan processApproval() tidak memvalidasi urutan_tahap sebelumnya —
    // hanya ownership + status_approval == 'Menunggu'.
    const result = await processApprovalAs(page, VRF_USERS.DPK, ids.stage3Dpk, 'Disetujui');
    expect(result.status).toBe(200);
    expect(result.body.status).toBe(true);
  });

  test('[NEGATIF] TC-APV3-09: Double-approve tahap akhir -> percobaan kedua 422', async ({ page }) => {
    test.setTimeout(120000);
    const { data } = await createAndApproveThroughStage3(page, fx);

    const first = await approveAsVerifikator(page, VRF_USERS.KETUA_JURUSAN, data.pengajuan_nama, 'Disetujui');
    expect(first.result.body.status).toBe(true);

    const second = await processApprovalAs(page, VRF_USERS.KETUA_JURUSAN, first.approvalId, 'Disetujui');
    expect(second.status).toBe(422);
    expect(second.body.message).toContain('sudah diproses');
  });

  test('[NEGATIF] TC-APV3-10: Tolak tahap akhir tanpa alasan_penolakan -> 422', async ({ page }) => {
    test.setTimeout(120000);
    const { data } = await createAndApproveThroughStage3(page, fx);

    const { result } = await approveAsVerifikator(page, VRF_USERS.KETUA_JURUSAN, data.pengajuan_nama, 'Ditolak', '');
    expect(result.status).toBe(422);
    expect(result.body.msgField?.alasan_penolakan).toBeDefined();
  });
});

test.describe('Approval Berjenjang — Alur Pendek Dosen/Tendik (Ketua Pelaksana -> Ketua Jurusan)', () => {
  let fx;

  test.beforeAll(async ({ browser }) => {
    test.setTimeout(150000);
    const page = await browser.newPage();
    fx = await resolveApprovalFixtureIds(page);
    await page.close();
  });

  test('[POSITIF] TC-APV3-11: Pemohon Dosen -> hanya 2 tahap dibuat (Ketua Pelaksana, Ketua Jurusan), tanpa Ketua Umum/Presiden BEM/DPK', async ({ page }) => {
    test.setTimeout(90000);
    const { id } = await createPengajuanForApproval(page, fx, {}, USERS.DSN);
    expect(id).not.toBeNull();

    const timeline = await getTimelineHtml(page, id, USERS.DSN);
    expect(timeline.body).toContain('Ketua Pelaksana');
    expect(timeline.body).toContain('Ketua Jurusan');
    expect(timeline.body).not.toContain('Ketua Umum');
    expect(timeline.body).not.toContain('Presiden BEM');
    expect(timeline.body).not.toContain('DPK');
  });

  test('[POSITIF] TC-APV3-12: Pemohon Dosen — Ketua Pelaksana lalu Ketua Jurusan setujui -> pengajuan langsung Diterima', async ({ page }) => {
    test.setTimeout(90000);
    const { data, id } = await createPengajuanForApproval(page, fx, {}, USERS.DSN);
    const ids = await computeShortStageApprovalIds(page, data.pengajuan_nama);
    expect(ids).not.toBeNull();

    const stage0 = await processApprovalAs(page, USERS.DSN, ids.stage0KetuaPelaksana, 'Disetujui');
    expect(stage0.body.status).toBe(true);

    const stage1 = await processApprovalAs(page, VRF_USERS.KETUA_JURUSAN, ids.stage1KetuaJurusan, 'Disetujui');
    expect(stage1.body.status).toBe(true);
    expect(stage1.body.message).toContain('diterima');

    const detail = await getPengajuanStatus(page, id);
    expect(detail).toContain('Diterima');
    expect(detail).toContain('cetak_surat_ajax');
  });

  test('[POSITIF] TC-APV3-13: Pemohon Tendik -> alur pendek yang sama (Ketua Pelaksana -> Ketua Jurusan)', async ({ page }) => {
    test.setTimeout(90000);
    const { data, id } = await createPengajuanForApproval(page, fx, {}, USERS.TDK);
    const ids = await computeShortStageApprovalIds(page, data.pengajuan_nama);
    expect(ids).not.toBeNull();

    await processApprovalAs(page, USERS.DSN, ids.stage0KetuaPelaksana, 'Disetujui');
    const stage1 = await processApprovalAs(page, VRF_USERS.KETUA_JURUSAN, ids.stage1KetuaJurusan, 'Disetujui');
    expect(stage1.body.status).toBe(true);

    const detail = await getPengajuanStatus(page, id);
    expect(detail).toContain('Diterima');
  });

  test('[NEGATIF] TC-APV3-14: Pemohon Dosen — Ketua Jurusan tolak -> pengajuan Ditolak', async ({ page }) => {
    test.setTimeout(90000);
    const { data, id } = await createPengajuanForApproval(page, fx, {}, USERS.DSN);

    await approveKetuaPelaksana(page, data.pengajuan_nama, 'Disetujui');
    const { result } = await approveAsVerifikator(page, VRF_USERS.KETUA_JURUSAN, data.pengajuan_nama, 'Ditolak', 'Ruangan tidak tersedia pada tanggal tersebut');
    expect(result.body.status).toBe(true);

    const detail = await getPengajuanStatus(page, id);
    expect(detail).toContain('Ditolak');
  });
});
