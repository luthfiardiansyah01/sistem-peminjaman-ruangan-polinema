/**
 * Helper untuk test E2E Approval Berjenjang (FR-6).
 *
 * Alur bercabang menurut role akun pemohon (PengajuanService::
 * alurApprovalUntukPengajuan()), dan SELURUH tahap SEKUENSIAL — tidak ada
 * tahap paralel:
 * - Mahasiswa: Tahap 0 Ketua Pelaksana -> Tahap 1 Ketua Umum (organisasi
 *   pengaju) -> Tahap 2 Presiden BEM -> Tahap 3 DPK -> Tahap 4 Ketua Jurusan.
 * - Dosen/Tendik: Tahap 0 Ketua Pelaksana -> Tahap 1 Ketua Jurusan.
 * Jika salah satu ruangan yang dipinjam berkategori 'Umum', rantai di atas
 * mendapat SATU tahap tambahan di akhir: Wakil Direktur II.
 * Tahap terakhir yang disetujui -> Diterima (nomor_surat + Jadwal dibuat).
 * Ditolak pada tahap manapun langsung mengakhiri seluruh proses.
 *
 * Helper-helper di bawah ini (createPengajuanForApproval dkk.) memakai
 * requester default MHS, sehingga mengikuti alur 5-tahap di atas kecuali
 * disebutkan lain.
 *
 * Route-route "*_ajax" (create_ajax, antrian_ajax non-view, timeline_ajax) hanya
 * merender partial view saat Laravel mendeteksi $request->ajax() true (header
 * X-Requested-With) — dipakai jQuery $.ajax (ajaxGetHtml/ajaxSend), BUKAN
 * page.goto(), agar tidak di-redirect ke halaman lain (lihat riwayat perbaikan
 * findPengajuanId/findJadwalId di modules/pengajuan.spec.js & jadwal.spec.js).
 * Pengecualian: GET /pengajuan/approval/antrian_ajax me-render halaman penuh
 * TANPA syarat ajax (lihat PengajuanController::antrian_ajax), jadi page.goto()
 * aman dipakai khusus untuk mengambil approval_id dari situ.
 */

import { USERS, login, logout } from './auth.fixture.js';

const VRF_USERS = {
  KETUA_UMUM: USERS.VRF_KETUA_UMUM,
  DPK: USERS.VRF_DPK,
  PRESIDEN_BEM: USERS.VRF_PRESIDEN_BEM,
  KETUA_JURUSAN: USERS.VRF_KETUA_JURUSAN,
  WADIR_2: USERS.VRF_WADIR_2,
};

async function ensureJqueryContext(page) {
  const hasContext = await page.evaluate(() => {
    return typeof $ !== 'undefined' &&
      typeof $.ajax !== 'undefined' &&
      document.querySelector('meta[name="csrf-token"]') !== null;
  }).catch(() => false);

  if (!hasContext) {
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {});
    await page.waitForTimeout(500);
  }
}

/** GET via jQuery $.ajax — lihat catatan header file ini soal kenapa bukan page.goto(). */
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

/** POST/PUT/DELETE via jQuery $.ajax dengan CSRF token otomatis (sama seperti doAjax di auth.fixture.js). */
async function ajaxSend(page, url, method, data = {}) {
  await ensureJqueryContext(page);
  const token = await page.evaluate(() => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
  const payload = { ...data, _token: token };
  if (method === 'PUT' || method === 'DELETE') {
    payload._method = method;
  }

  return page.evaluate(({ url, payload }) => {
    return new Promise((resolve) => {
      $.ajax({
        url,
        type: 'POST',
        data: payload,
        dataType: 'json',
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

function escapeForHtmlMatch(str) {
  return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// ──────────────────────────────────────────────────
// Fixture data resolution (organisasi & ruangan id) — dicari dinamis lewat
// dropdown di /pengajuan/create_ajax, BUKAN di-hardcode, karena id auto-increment
// bergantung pada data yang sudah ada di DB (lihat database/seeders/OrganisasiSeeder.php,
// RuanganSeeder.php). Di-cache di module-level supaya hanya di-resolve sekali per run.
// ──────────────────────────────────────────────────

let cachedFixtureIds = null;

async function resolveApprovalFixtureIds(page) {
  if (cachedFixtureIds) return cachedFixtureIds;

  // /pengajuan/create_ajax kini dibatasi permission create (DSN/TDK/MHS saja) —
  // ADM tidak lagi mengajukan peminjaman, jadi TIDAK bisa dipakai untuk resolve fixture
  // ini lagi. USERS.MHS dipakai sebagai gantinya (role apa saja di luar ADM valid di sini).
  await login(page, USERS.MHS.username, USERS.MHS.password);
  const res = await ajaxGetHtml(page, '/pengajuan/create_ajax');

  const findOptionId = (label) => {
    const re = new RegExp(`<option value="(\\d+)">\\s*${escapeForHtmlMatch(label)}\\s*</option>`);
    const m = res.body.match(re);
    return m ? Number(m[1]) : null;
  };

  cachedFixtureIds = {
    hmjOrgId: findOptionId('Himpunan Mahasiswa Jurusan'),
    bemOrgId: findOptionId('Badan Eksekutif Mahasiswa'),
    // Ruangan 1 dari RuanganSeeder.php, kategori 'Jurusan' — rantai approval berhenti
    // di Ketua Jurusan (lihat PengajuanService::alurApprovalUntukPengajuan()).
    ruanganJurusanId: findOptionId('Ruangan 1'),
    // Dibuat khusus untuk E2E (kategori Umum). Lihat setup: php artisan tinker insert
    // m_ruangan kode RUM. Memakai ruangan ini menambah SATU tahap approval akhir
    // (Wakil Direktur II) setelah Ketua Jurusan — lihat alurApprovalUntukPengajuan().
    ruanganUmumId: findOptionId('Ruangan Umum E2E'),
  };

  await logout(page);

  // PengajuanService::ketuaPelaksanaCandidates() menyaring dropdown menurut role
  // AKUN YANG SEDANG LOGIN (MHS -> hanya mahasiswa, DSN/TDK -> hanya dosen), jadi
  // 'Dosen 1' tidak pernah muncul saat resolve di atas dilakukan sebagai MHS.
  // create() sendiri tetap menerima ketua_pelaksana ber-role Dosen ATAUPUN Mahasiswa
  // apa pun role pemohonnya, jadi di sini resolve 'Dosen 1' lewat login DSN terpisah.
  await login(page, USERS.DSN.username, USERS.DSN.password);
  const resDsn = await ajaxGetHtml(page, '/pengajuan/create_ajax');
  const findOptionIdDsn = (label) => {
    const re = new RegExp(`<option value="(\\d+)">\\s*${escapeForHtmlMatch(label)}\\s*</option>`);
    const m = resDsn.body.match(re);
    return m ? Number(m[1]) : null;
  };
  cachedFixtureIds.ketuaPelaksanaUserId = findOptionIdDsn('Dosen 1');
  await logout(page);

  if (!cachedFixtureIds.hmjOrgId || !cachedFixtureIds.bemOrgId || !cachedFixtureIds.ruanganJurusanId || !cachedFixtureIds.ruanganUmumId || !cachedFixtureIds.ketuaPelaksanaUserId) {
    throw new Error(`Fixture data approval tidak lengkap, jalankan seeder terlebih dahulu: ${JSON.stringify(cachedFixtureIds)}`);
  }

  return cachedFixtureIds;
}

// ──────────────────────────────────────────────────
// Data generator
// ──────────────────────────────────────────────────

let counter = Date.now();

function generateApprovalPengajuanData(overrides = {}) {
  counter++;
  return {
    pengajuan_nama: `APV-${counter}`,
    pengajuan_tgl: '2026-09-01',
    pengajuan_jam_mulai: '08:00',
    pengajuan_jam_selesai: '10:00',
    // Ruangan 1 (ruanganJurusanId, dipakai default di createPengajuanForApproval)
    // kuotanya 10 (lihat RuanganSeeder.php) — jumPes harus <= itu.
    pengajuan_jumPes: 10,
    pengajuan_keterangan: 'E2E approval berjenjang test',
    ...overrides,
  };
}

/**
 * Buat pengajuan sebagai pemohon (default MHS) yang organisasi_id-nya HARUS HMJ
 * (fixtureIds.hmjOrgId) agar tahap 1 (Ketua Umum) menemukan verifikatornya —
 * lihat PengajuanService::generateApprovalStages(), tahap dilewati diam-diam
 * (silent skip) jika tidak ada verifikator yang cocok untuk organisasi_id itu.
 *
 * ketua_pelaksana_user_id (tahap 0, poin 1) default ke fixtureIds.ketuaPelaksanaUserId
 * ('Dosen 1' / USERS.DSN) supaya approval.fixture.js bisa langsung login sebagai
 * identitas yang sudah dikenal untuk memproses tahap ini di test.
 */
async function createPengajuanForApproval(page, fixtureIds, overrides = {}, requester = USERS.MHS) {
  await login(page, requester.username, requester.password);
  const data = generateApprovalPengajuanData({
    organisasi_id: fixtureIds.hmjOrgId,
    ketua_pelaksana_user_id: fixtureIds.ketuaPelaksanaUserId,
    ruangan_ids: [fixtureIds.ruanganJurusanId],
    ...overrides,
  });
  const result = await ajaxSend(page, '/pengajuan/ajax', 'POST', data);
  await logout(page);
  return { data, result, id: result.body?.pengajuan_id ?? null };
}

/**
 * Ambil approval_id milik `vrfUser` untuk pengajuan bernama `pengajuanNama`,
 * dari halaman antrian (`GET /pengajuan/approval/antrian_ajax`, full page —
 * lihat catatan di header file, page.goto() aman di sini). TIDAK memproses
 * apa pun, hanya membaca — dipakai baik untuk approve maupun untuk skenario
 * pelanggaran kepemilikan (user lain mencoba proses approval_id ini).
 */
async function getApprovalIdFromAntrian(page, vrfUser, pengajuanNama) {
  await login(page, vrfUser.username, vrfUser.password);
  await page.goto('/pengajuan/approval/antrian_ajax', { waitUntil: 'domcontentloaded', timeout: 15000 });
  const html = await page.content();
  const re = new RegExp(`modalProses\\((\\d+),\\s*'${escapeForHtmlMatch(pengajuanNama)}'\\)`);
  const m = html.match(re);
  await logout(page);
  return m ? Number(m[1]) : null;
}

/**
 * Hitung approval_id kelima tahap (Ketua Pelaksana, Ketua Umum, Presiden BEM, DPK,
 * Ketua Jurusan) dari SATU lookup antrian saja (sebagai Ketua Pelaksana — USERS.DSN),
 * memanfaatkan fakta bahwa PengajuanService::generateApprovalStages() membuat kelima
 * baris t_pengajuan_approval sekaligus & berurutan saat pengajuan dibuat, dengan
 * Ketua Pelaksana (tahap 0) SELALU dibuat PALING PERTAMA, lalu SEKUENSIAL sesuai
 * alurApprovalUntukPengajuan() untuk pemohon MHS: Ketua Umum=N+1, Presiden BEM=N+2,
 * DPK=N+3, Ketua Jurusan=N+4 (sudah diverifikasi manual). Anchor di tahap 0 karena
 * itu baris PERTAMA yang dibuat, jadi lebih robust. Jauh lebih cepat daripada login
 * sebagai tiap verifikator satu-satu, dan memungkinkan test "proses di luar urutan"
 * (approval_id tahap lanjutan sebelum tahap sebelumnya disetujui) karena baris itu
 * memang sudah ada sejak awal (batas_waktu saja yang masih null sampai tahapnya
 * aktif — lihat processApproval()).
 *
 * HANYA valid untuk pengajuan dengan requester MHS (default createPengajuanForApproval)
 * yang organisasi_id-nya HMJ (fixtureIds.hmjOrgId) DAN ketua_pelaksana_user_id-nya
 * USERS.DSN, karena kalau tidak, tahap Ketua Umum dilewati (silent skip) dan
 * pergeseran approval_id ini tidak berlaku. Untuk requester DSN/TDK, alurnya hanya
 * 2 tahap (Ketua Pelaksana -> Ketua Jurusan) — pakai computeShortStageApprovalIds().
 */
async function computeStageApprovalIds(page, pengajuanNama, ketuaPelaksanaUser = USERS.DSN) {
  const stage0 = await getApprovalIdFromAntrian(page, ketuaPelaksanaUser, pengajuanNama);
  if (stage0 === null) return null;
  return {
    stage0KetuaPelaksana: stage0,
    stage1KetuaUmum: stage0 + 1,
    stage2PresidenBem: stage0 + 2,
    stage3Dpk: stage0 + 3,
    stage4KetuaJurusan: stage0 + 4,
  };
}

/**
 * Sama seperti computeStageApprovalIds(), tapi untuk alur pendek pemohon Dosen/Tendik
 * (Ketua Pelaksana -> Ketua Jurusan saja, lihat alurApprovalUntukPengajuan()).
 */
async function computeShortStageApprovalIds(page, pengajuanNama, ketuaPelaksanaUser = USERS.DSN) {
  const stage0 = await getApprovalIdFromAntrian(page, ketuaPelaksanaUser, pengajuanNama);
  if (stage0 === null) return null;
  return {
    stage0KetuaPelaksana: stage0,
    stage1KetuaJurusan: stage0 + 1,
  };
}

/**
 * Login sebagai Ketua Pelaksana (USERS.DSN, default), cari approval_id tahap 0 miliknya
 * untuk `pengajuanNama` di antrian, lalu proses (Disetujui/Ditolak). Pola sama seperti
 * approveAsVerifikator, dipisah namanya supaya jelas ini tahap 0 (bukan posisi tetap).
 */
async function approveKetuaPelaksana(page, pengajuanNama, statusApproval, alasanPenolakan = '', ketuaPelaksanaUser = USERS.DSN) {
  return approveAsVerifikator(page, ketuaPelaksanaUser, pengajuanNama, statusApproval, alasanPenolakan);
}

/**
 * Login sebagai `vrfUser`, cari approval_id miliknya untuk `pengajuanNama` di
 * antrian, lalu proses (Disetujui/Ditolak). Menggabungkan login+lookup+proses
 * dalam satu helper supaya tidak perlu login berulang untuk verifikator yang sama.
 */
async function approveAsVerifikator(page, vrfUser, pengajuanNama, statusApproval, alasanPenolakan = '') {
  await login(page, vrfUser.username, vrfUser.password);
  await page.goto('/pengajuan/approval/antrian_ajax', { waitUntil: 'domcontentloaded', timeout: 15000 });
  const html = await page.content();
  const re = new RegExp(`modalProses\\((\\d+),\\s*'${escapeForHtmlMatch(pengajuanNama)}'\\)`);
  const m = html.match(re);
  const approvalId = m ? Number(m[1]) : null;

  if (approvalId === null) {
    await logout(page);
    return { approvalId: null, result: null };
  }

  const result = await ajaxSend(page, `/pengajuan/approval/${approvalId}/proses_ajax`, 'PUT', {
    status_approval: statusApproval,
    alasan_penolakan: alasanPenolakan,
  });
  await logout(page);
  return { approvalId, result };
}

/** Proses approval_id yang SUDAH diketahui (mis. dari getApprovalIdFromAntrian), sebagai `vrfUser`. */
async function processApprovalAs(page, vrfUser, approvalId, statusApproval, alasanPenolakan = '') {
  await login(page, vrfUser.username, vrfUser.password);
  const result = await ajaxSend(page, `/pengajuan/approval/${approvalId}/proses_ajax`, 'PUT', {
    status_approval: statusApproval,
    alasan_penolakan: alasanPenolakan,
  });
  await logout(page);
  return result;
}

/**
 * Ambil timeline approval (HTML partial) sebuah pengajuan. Hanya user peminjam ruangan
 * (pengajuan->user_id, yaitu si PEMOHON) yang boleh mengakses timeline_ajax — ADM/verifikator
 * lain akan mendapat 403 (lihat PengajuanService::timelineFor()). Default asUser = USERS.MHS
 * karena itu requester default createPengajuanForApproval(); untuk pengajuan yang dibuat
 * dengan requester lain (mis. USERS.DSN/USERS.TDK), WAJIB oper asUser eksplisit yang sesuai.
 */
async function getTimelineHtml(page, pengajuanId, asUser = USERS.MHS) {
  await login(page, asUser.username, asUser.password);
  const res = await ajaxGetHtml(page, `/pengajuan/${pengajuanId}/timeline_ajax`);
  await logout(page);
  return res;
}

/** Ambil status pengajuan (Diajukan/Diterima/Ditolak) via show_ajax, sebagai ADM secara default. */
async function getPengajuanStatus(page, pengajuanId, asUser = USERS.ADM) {
  await login(page, asUser.username, asUser.password);
  const res = await ajaxGetHtml(page, `/pengajuan/${pengajuanId}/show_ajax`);
  await logout(page);
  return res.body;
}

export {
  VRF_USERS,
  ajaxGetHtml,
  ajaxSend,
  resolveApprovalFixtureIds,
  generateApprovalPengajuanData,
  createPengajuanForApproval,
  getApprovalIdFromAntrian,
  computeStageApprovalIds,
  computeShortStageApprovalIds,
  approveAsVerifikator,
  approveKetuaPelaksana,
  processApprovalAs,
  getTimelineHtml,
  getPengajuanStatus,
};
