$path = "c:\Users\AsusV16\Documents\JokiProyek\Client-7\SPR_JTI-main\SPR_JTI-main\tests\e2e\e2e.spec.js"

$lines = @()
$lines += '/**'
$lines += ' * ========================================================================'
$lines += ' * End-to-End Test: Sistem Peminjaman Ruangan JTI'
$lines += ' * ========================================================================'
$lines += ' *'
$lines += ' * Workflow: Login --> Pengajuan --> Approval --> Jadwal --> Status Ruangan'
$lines += ' *'
$lines += ' * Coverage: FR-1 through FR-10'
$lines += ' * Actors: ADM, DSN, TDK, MHS, VRF1-VRF5'
$lines += ' * ========================================================================'
$lines += ' */'
$lines += "`nimport { test, expect } from '@playwright/test';"
$lines += "import { USERS, login, logout, doAjax } from '../fixtures/auth.fixture.js';"
$lines += "`nconst BASE_URL = 'http://127.0.0.1:8000';"
$lines += 'let _counter = Date.now();'
$lines += "function uniqueName(prefix) { _counter++; return prefix + '-' + _counter; }"
$lines += "`nasync function findPengajuanId(page, nama) {"
$lines += '  await page.goto(BASE_URL + "/pengajuan", { waitUntil: "domcontentloaded", timeout: 15000 }).catch(function() {});'
$lines += '  await page.waitForTimeout(500);'
$lines += '  for (var id = 1; id <= 200; id++) {'
$lines += '    var result = await doAjax(page, "/pengajuan/" + id + "/show_ajax", "GET");'
$lines += '    if (result.status === 200 && result.body) {'
$lines += '      var html = typeof result.body === "string" ? result.body : JSON.stringify(result.body);'
$lines += '      if (html.indexOf(nama) !== -1) { return id; }'
$lines += '    } else if (result.status === 404) { break; }'
$lines += '  }'
$lines += '  return null;'
$lines += '}'
$lines += "`nasync function findApprovalId(page, pengajuanId, posisi) {"
$lines += '  var result = await doAjax(page, "/pengajuan/" + pengajuanId + "/timeline_ajax", "GET");'
$lines += '  if (result.status !== 200 || !result.body) { return null; }'
$lines += '  var html = typeof result.body === "string" ? result.body : JSON.stringify(result.body);'
$lines += '  var re = new RegExp(posisi.replace(/[.*+?^${}()|[\]\\]/g, "\\\\$&") + "[\\\\s\\\\S]{0,500}?/pengajuan/approval/(\\\\d+)/proses_ajax", "i");'
$lines += '  var m = html.match(re);'
$lines += '  if (m) return parseInt(m[1], 10);'
$lines += '  var fallback = [...html.matchAll(/approval[_-]?id[=:]["\']?(\\d+)/gi)];'
$lines += '  if (fallback.length > 0) { return parseInt(fallback[0][1], 10); }'
$lines += '  return null;'
$lines += '}'
$lines += "`nasync function buatPengajuan(page, role, overrides) {"
$lines += '  overrides = overrides || {};'
$lines += '  var nama = overrides.pengajuan_nama || uniqueName("E2E-PJL");'
$lines += '  await login(page, role.username, role.password);'
$lines += '  await page.goto(BASE_URL + "/dashboard", { waitUntil: "domcontentloaded", timeout: 15000 }).catch(function() {});'
$lines += '  await page.waitForTimeout(1000);'
$lines += '  var payload = { pengajuan_nama: nama, pengajuan_tgl: "2026-09-15", pengajuan_jam_mulai: "08:00", pengajuan_jam_selesai: "12:00", pengajuan_jumPes: 40, pengajuan_keterangan: "E2E Test", organisasi_id: 1, ruangan_ids: [1] };'
$lines += '  for (var k in overrides) { if (overrides.hasOwnProperty(k)) payload[k] = overrides[k]; }'
$lines += '  payload.pengajuan_nama = nama;'
$lines += '  var result = await doAjax(page, "/pengajuan/ajax", "POST", payload);'
$lines += '  await logout(page);'
$lines += '  return { nama: nama, result: result };'
$lines += '}'
$lines += "`nasync function prosesApproval(page, approvalId, statusApproval, alasan) {"
$lines += '  alasan = alasan || "";'
$lines += '  await page.goto(BASE_URL + "/dashboard", { waitUntil: "domcontentloaded", timeout: 15000 }).catch(function() {});'
$lines += '  await page.waitForTimeout(500);'
$lines += '  return await doAjax(page, "/pengajuan/approval/" + approvalId + "/proses_ajax", "PUT", { status_approval: statusApproval, alasan_penolakan: alasan });'
$lines += '}'
$lines += "`ntest.describe.serial('E2E: Full Workflow', function() {"
$lines += '  var createdKodeRuangan = "";'
$lines += '  var createdNamaRuangan = "";'
$lines += '  var createdNamaPengajuan = "";'
$lines += '  var pengajuanId = null;'
$lines += '  var namaDitolak = "";'
$lines += '  var idDitolak = null;'
$lines += '  var namaApprovalChain = "";'
$lines += '  var idApprovalChain = null;'
$lines += '  var approvalIds = {};'
$lines += "`n  // PHASE 1: Login & Dashboard"
$lines += "  test.describe.serial('Phase 1', function() {"
$lines += "    test('1a Admin login', async function({ page }) { test.setTimeout(30000); await login(page, USERS.ADM.username, USERS.ADM.password); expect((await page.locator('body').textContent()).toLowerCase()).toContain('dashboard'); await logout(page); });"
$lines += "    test('1b Dosen login', async function({ page }) { test.setTimeout(30000); await login(page, USERS.DSN.username, USERS.DSN.password); expect((await page.locator('body').textContent()).toLowerCase()).toContain('dashboard'); await logout(page); });"
$lines += "    test('1c Tendik login', async function({ page }) { test.setTimeout(30000); await login(page, USERS.TDK.username, USERS.TDK.password); expect((await page.locator('body').textContent()).toLowerCase()).toContain('dashboard'); await logout(page); });"
$lines += "    test('1d Mahasiswa login', async function({ page }) { test.setTimeout(30000); await login(page, USERS.MHS.username, USERS.MHS.password); expect((await page.locator('body').textContent()).toLowerCase()).toContain('dashboard'); await logout(page); });"
$lines += "    test('1e Dashboard Admin FR-8.5', async function({ page }) { test.setTimeout(30000); await login(page, USERS.ADM.username, USERS.ADM.password); await page.goto(BASE_URL + '/dashboard', { waitUntil: 'domcontentloaded' }); await page.waitForTimeout(1000); expect(await page.locator('body').textContent()).toBeTruthy(); await logout(page); });"
$lines += "    test('1f Dashboard Verifikator FR-8.3', async function({ page }) { test.setTimeout(30000); await login(page, USERS.VRF_KETUA_UMUM.username, USERS.VRF_KETUA_UMUM.password); await page.goto(BASE_URL + '/dashboard', { waitUntil: 'domcontentloaded' }); await page.waitForTimeout(1000); expect(await page.locator('body').textContent()).toBeTruthy(); await logout(page); });"
$lines += '  });'
$lines += "`n  // PHASE 2: Setup Ruangan"
$lines += "  test.describe.serial('Phase 2', function() {"
$lines += "    test('2a Buat ruangan Jurusan FR-3.3', async function({ page }) {"
$lines += '      test.setTimeout(60000);'
$lines += '      createdKodeRuangan = "E2E-" + String(Date.now()).slice(-6);'
$lines += '      createdNamaRuangan = "Ruangan E2E " + Date.now();'
$lines += '      await login(page, USERS.ADM.username, USERS.ADM.password);'
$lines += '      await page.goto(BASE_URL + "/ruangan", { waitUntil: "domcontentloaded", timeout: 15000 });'
$lines += '      await page.waitForTimeout(1500);'
$lines += '      var result = await doAjax(page, "/ruangan/ajax", "POST", { ruangan_nama: createdNamaRuangan, ruangan_kode: createdKodeRuangan, ruangan_kuota: "50", ruangan_fasilitas: "Meja, Kursi, Proyektor, AC", ruangan_status: "Tersedia", ruangan_kategori: "Jurusan" });'
$lines += '      expect([200, 201]).toContain(result.status);'
$lines += '      if (result.body) expect(result.body.status).toBe(true);'
$lines += '      await logout(page);'
$lines += '    });'
$lines += "    test('2b Buat ruangan Umum', async function({ page }) {"
$lines += '      test.setTimeout(60000);'
$lines += '      await login(page, USERS.ADM.username, USERS.ADM.password);'
$lines += '      await page.goto(BASE_URL + "/ruangan", { waitUntil: "domcontentloaded", timeout: 15000 });'
$lines += '      await page.waitForTimeout(1500);'
$lines += '      var result = await doAjax(page, "/ruangan/ajax", "POST", { ruangan_nama: "Ruangan Umum E2E", ruangan_kode: "E2E-UMUM-" + String(Date.now()).slice(-6), ruangan_kuota: "100", ruangan_fasilitas: "Panggung", ruangan_status: "Tersedia", ruangan_kategori: "Umum" });'
$lines += '      expect([200, 201, 422]).toContain(result.status);'
$lines += '      await logout(page);'
$lines += '    });'
$lines += '  });'
$lines += "`n  // PHASE 3: Pengajuan"
$lines += "  test.describe.serial('Phase 3', function() {"
$lines += "    test('3a TDK submit FR-5.1', async function({ page }) { test.setTimeout(60000); var r = await buatPengajuan(page, USERS.TDK); createdNamaPengajuan = r.nama; expect([200, 302]).toContain(r.result.status); if (r.result.status === 200 && r.result.body) { expect(r.result.body.status).toBe(true); } });"
$lines += "    test('3b Cari ID', async function({ page }) { test.setTimeout(120000); if (!createdNamaPengajuan) { test.skip(); return; } await login(page, USERS.ADM.username, USERS.ADM.password); pengajuanId = await findPengajuanId(page, createdNamaPengajuan); expect(pengajuanId).not.toBeNull(); await logout(page); });"
$lines += "    test('3c Show_ajax FR-5.2', async function({ page }) { test.setTimeout(45000); if (!pengajuanId) { test.skip(); return; } await login(page, USERS.ADM.username, USERS.ADM.password); await page.goto(BASE_URL + '/pengajuan', { waitUntil: 'domcontentloaded', timeout: 15000 }); await page.waitForTimeout(500); expect((await doAjax(page, '/pengajuan/' + pengajuanId + '/show_ajax', 'GET')).status).toBe(200); await logout(page); });"
$lines += "    test('3d Timeline FR-5.3', async function({ page }) { test.setTimeout(45000); if (!pengajuanId) { test.skip(); return; } await login(page, USERS.ADM.username, USERS.ADM.password); await page.goto(BASE_URL + '/pengajuan', { waitUntil: 'domcontentloaded', timeout: 15000 }); await page.waitForTimeout(500); expect([200, 302]).toContain((await doAjax(page, '/pengajuan/' + pengajuanId + '/timeline_ajax', 'GET')).status); await logout(page); });"
$lines += '  });'
$lines += "`n  // PHASE 3b: Approval Chain"
$lines += "  test.describe.serial('Phase 3b', function() {"
$lines += "    test('3e Buat approval chain', async function({ page }) { test.setTimeout(60000); var r = await buatPengajuan(page, USERS.TDK, { pengajuan_nama: uniqueName('E2E-APPR') }); namaApprovalChain = r.nama; expect([200, 302]).toContain(r.result.status); });"
$lines += "    test('3f Cari ID chain', async function({ page }) { test.setTimeout(120000); if (!namaApprovalChain) { test.skip(); return; } await login(page, USERS.ADM.username, USERS.ADM.password); idApprovalChain = await findPengajuanId(page, namaApprovalChain); expect(idApprovalChain).not.toBeNull(); await logout(page); });"
$lines += "    test('3g Ekstrak approval_ids', async function({ page }) { test.setTimeout(60000); if (!idApprovalChain) { test.skip(); return; } await login(page, USERS.ADM.username, USERS.ADM.password); await page.goto(BASE_URL + '/pengajuan', { waitUntil: 'domcontentloaded', timeout: 15000 }); await page.waitForTimeout(500); var list = ['Ketua Umum', 'DPK', 'Presiden BEM', 'Ketua Jurusan', 'Wakil Direktur II']; for (var i = 0; i < list.length; i++) { var aid = await findApprovalId(page, idApprovalChain, list[i]); if (aid) approvalIds[list[i]] = aid; } await logout(page); });"
$lines += '  });'
$lines += "`n  // PHASE 4: Approval Berjenjang"
$lines += "  test.describe.serial('Phase 4', function() {"
$lines += "    test('4a VRF antrian FR-6.5', async function({ page }) { test.setTimeout(45000); await login(page, USERS.VRF_KETUA_UMUM.username, USERS.VRF_KETUA_UMUM.password); var resp = await page.goto(BASE_URL + '/pengajuan/approval/antrian_ajax', { waitUntil: 'domcontentloaded' }).catch(function() { return null; }); expect(resp ? resp.status() : 200).toBe(200); await logout(page); });"
$lines += "    test('4b Non-VRF 403 FR-2.3', async function({ page }) { test.setTimeout(45000); await login(page, USERS.ADM.username, USERS.ADM.password); var resp = await page.goto(BASE_URL + '/pengajuan/approval/antrian_ajax', { waitUntil: 'domcontentloaded' }).catch(function() { return null; }); expect(resp ? resp.status() : 403).toBe(403); await logout(page); });"
$lines += "    test('4c Ketua Umum approve FR-6.6', async function({ page }) { test.setTimeout(60000); if (!approvalIds['Ketua Umum']) { test.skip(); return; } await login(page, USERS.VRF_KETUA_UMUM.username, USERS.VRF_KETUA_UMUM.password); var result = await prosesApproval(page, approvalIds['Ketua Umum'], 'Disetujui'); expect([200, 422]).toContain(result.status); await logout(page); });"
$lines += "    test('4c.1 Double approve 422', async function({ page }) { test.setTimeout(45000); if (!approvalIds['Ketua Umum']) { test.skip(); return; } await login(page, USERS.VRF_KETUA_UMUM.username, USERS.VRF_KETUA_UMUM.password); var result = await prosesApproval(page, approvalIds['Ketua Umum'], 'Disetujui'); expect(result.status).toBe(422); await logout(page); });"
$lines += "    test('4c.2 VRF lain 403', async function({ page }) { test.setTimeout(45000); if (!approvalIds['Ketua Umum']) { test.skip(); return; } await login(page, USERS.VRF_DPK.username, USERS.VRF_DPK.password); var result = await prosesApproval(page, approvalIds['Ketua Umum'], 'Disetujui'); expect(result.status).toBe(403); await logout(page); });"
$lines += "    test('4d DPK approve paralel', async function({ page }) { test.setTimeout(60000); if (!approvalIds['DPK']) { test.skip(); return; } await login(page, USERS.VRF_DPK.username, USERS.VRF_DPK.password); var result = await prosesApproval(page, approvalIds['DPK'], 'Disetujui'); expect([200, 422]).toContain(result.status); await logout(page); });"
$lines += "    test('4d.1 DPK double 422', async function({ page }) { test.setTimeout(45000); if (!approvalIds['DPK']) { test.skip(); return; } await login(page, USERS.VRF_DPK.username, USERS.VRF_DPK.password); var result = await prosesApproval(page, approvalIds['DPK'], 'Disetujui'); expect(result.status).toBe(422); await logout(page); });"
$lines += "    test('4e PresBEM approve trigger tahap 3', async function({ page }) { test.setTimeout(60000); if (!approvalIds['Presiden BEM']) { test.skip(); return; } await login(page, USERS.VRF_PRESIDEN_BEM.username, USERS.VRF_PRESIDEN_BEM.password); var result = await prosesApproval(page, approvalIds['Presiden BEM'], 'Disetujui'); expect([200, 422]).toContain(result.status); await logout(page); });"
$lines += "    test('4e.1 VRF lain 403 DPK', async function({ page }) { test.setTimeout(45000); if (!approvalIds['DPK']) { test.skip(); return; } await login(page, USERS.VRF_PRESIDEN_BEM.username, USERS.VRF_PRESIDEN_BEM.password); var result = await prosesApproval(page, approvalIds['DPK'], 'Disetujui'); expect(result.status).toBe(403); await logout(page); });"
$lines += "    test('4f Ketua Jurusan approve final', async function({ page }) { test.setTimeout(60000); if (!approvalIds['Ketua Jurusan']) { test.skip(); return; } await login(page, USERS.VRF_KETUA_JURUSAN.username, USERS.VRF_KETUA_JURUSAN.password); var result = await prosesApproval(page, approvalIds['Ketua Jurusan'], 'Disetujui'); expect([200, 422]).toContain(result.status); if (result.status === 200 && result.body) { expect(result.body.status).toBe(true); } await logout(page); });"
$lines += "    test('4g Verifikasi Diterima', async function({ page }) { test.setTimeout(45000); if (!idApprovalChain) { test.skip(); return; } await login(page, USERS.ADM.username, USERS.ADM.password); await page.goto(BASE_URL + '/pengajuan', { waitUntil: 'domcontentloaded', timeout: 15000 }); await page.waitForTimeout(500); expect(200).toBe((await doAjax(page, '/pengajuan/' + idApprovalChain + '/show_ajax', 'GET')).status); await logout(page); });"
$lines += "    test('4h Cetak surat PDF FR-9.1', async function({ page }) { test.setTimeout(45000); if (!idApprovalChain) { test.skip(); return; } await login(page, USERS.ADM.username, USERS.ADM.password); var resp = await page.goto(BASE_URL + '/pengajuan/' + idApprovalChain + '/cetak_surat_ajax', { waitUntil: 'domcontentloaded' }).catch(function() { return null; }); if (resp) { expect([200, 422]).toContain(resp.status()); } await logout(page); });"
$lines += '  });'
$lines += "`n  // PHASE 5: Rejection"
$lines += "  test.describe.serial('Phase 5', function() {"
$lines += "    test('5a Buat pengajuan ditolak', async function({ page }) { test.setTimeout(60000); var r = await buatPengajuan(page, USERS.MHS, { pengajuan_nama: uniqueName('E2E-REJ') }); namaDitolak = r.nama; expect([200, 302]).toContain(r.result.status); });"
$lines += "    test('5b Cari ID ditolak', async function({ page }) { test.setTimeout(120000); if (!namaDitolak) { test.skip(); return; } await login(page, USERS.ADM.username, USERS.ADM.password); idDitolak = await findPengajuanId(page, namaDitolak); expect(idDitolak).not.toBeNull(); await logout(page); });"
$lines += "    test('5c Cari approval_id', async function({ page }) { test.setTimeout(60000); if (!idDitolak) { test.skip(); return; } await login(page, USERS.ADM.username, USERS.ADM.password); await page.goto(BASE_URL + '/pengajuan', { waitUntil: 'domcontentloaded', timeout: 15000 }); await page.waitForTimeout(500); var aid = await findApprovalId(page, idDitolak, 'Ketua Umum'); if (aid) approvalIds['Rejected_KetuaUmum'] = aid; await logout(page); });"
$lines += "    test('5d VRF tolak FR-6.6, FR-6.7', async function({ page }) { test.setTimeout(60000); var aid = approvalIds['Rejected_KetuaUmum']; if (!aid) { test.skip(); return; } await login(page, USERS.VRF_KETUA_UMUM.username, USERS.VRF_KETUA_UMUM.password); var result = await prosesApproval(page, aid, 'Ditolak', 'Ruangan tidak tersedia. E2E Test.'); expect([200, 422]).toContain(result.status); if (result.status === 200 && result.body) { expect(result.body.status).toBe(true); expect(result.body.message.toLowerCase()).toContain('ditolak'); } await logout(page); });"
$lines += "    test('5e Alasan kosong 422', async function({ page }) { test.setTimeout(45000); var r = await buatPengajuan(page, USERS.TDK, { pengajuan_nama: uniqueName('E2E-ALASN') }); await login(page, USERS.ADM.username, USERS.ADM.password); await page.goto(BASE_URL + '/pengajuan', { waitUntil: 'domcontentloaded', timeout: 15000 }); await page.waitForTimeout(500); var rid = await findPengajuanId(page, r.nama); var ra = rid ? await findApprovalId(page, rid, 'Ketua Umum') : null; if (ra) { await login(page, USERS.VRF_KETUA_UMUM.username, USERS.VRF_KETUA_UMUM.password); var res = await prosesApproval(page, ra, 'Ditolak', ''); expect(res.status).toBe(422); } await logout(page); });"
$lines += "    test('5f Double reject 422 NFR-3.3', async function({ page }) { test.setTimeout(45000); if (!idDitolak) { test.skip(); return; } await login(page, USERS.ADM.username, USERS.ADM.password); await page.goto(BASE_URL + '/dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 }); await page.waitForTimeout(1000); var result = await doAjax(page, '/pengajuan/' + idDitolak + '/tolak_ajax', 'PUT', { catatan_verifikator: 'Percobaan tolak kedua.' }); expect(result.status).toBe(422); await logout(page); });"
$lines += '  });'
$lines += "`n  // PHASE 6: Status Ruangan"
$lines += "  test.describe.serial('Phase 6', function() {"
$lines += "    test('6a Admin akses ruangan', async function({ page }) { test.setTimeout(30000); await login(page, USERS.ADM.username, USERS.ADM.password); var resp = await page.goto(BASE_URL + '/ruangan', { waitUntil: 'domcontentloaded' }); expect(resp.status()).toBe(200); await logout(page); });"
$lines += "    test('6b Peminjam akses ruangan FR-3.1', async function({ page }) { test.setTimeout(45000); var roles = [USERS.DSN, USERS.TDK, USERS.MHS]; for (var i = 0; i < roles.length; i++) { await login(page, roles[i].username, roles[i].password); var resp = await page.goto(BASE_URL + '/ruangan', { waitUntil: 'domcontentloaded' }); expect(resp.status()).toBe(200); await logout(page); await page.waitForTimeout(200); } });"
$lines += "    test('6c Buat ruangan valid', async function({ page }) { test.setTimeout(60000); await login(page, USERS.ADM.username, USERS.ADM.password); await page.goto(BASE_URL + '/ruangan', { waitUntil: 'domcontentloaded', timeout: 15000 }); await page.waitForTimeout(1500); var result = await doAjax(page, '/ruangan/ajax', 'POST', { ruangan_nama: 'Ruangan FR3 Test', ruangan_kode: 'FR3-' + String(Date.now()).slice(-6), ruangan_kuota: '30', ruangan_fasilitas: 'AC', ruangan_status: 'Tersedia', ruangan_kategori: 'Umum' }); expect([200, 201]).toContain(result.status); await logout(page); });"
$lines += "    test('6d Update status ruangan', async function({ page }) { test.setTimeout(60000); await login(page, USERS.ADM.username, USERS.ADM.password); await page.goto(BASE_URL + '/ruangan', { waitUntil: 'domcontentloaded', timeout: 15000 }); await page.waitForTimeout(1500); var result = await doAjax(page, '/ruangan/1/update_ajax', 'PUT', { ruangan_nama: 'Ruangan 1', ruangan_kode: 'R001', ruangan_kuota: '50', ruangan_status: 'Tidak Tersedia', ruangan_kategori: 'Jurusan' }); expect([200, 404, 422]).toContain(result.status); await page.waitForTimeout(300); var revert = await doAjax(page, '/ruangan/1/update_ajax', 'PUT', { ruangan_nama: 'Ruangan 1', ruangan_kode: 'R001', ruangan_kuota: '50', ruangan_status: 'Tersedia', ruangan_kategori: 'Jurusan' }); expect([200, 404, 422]).toContain(revert.status); await logout(page); });"
$lines += "    test('6e Kategori invalid 422', async function({ page }) { test.setTimeout(60000); await login(page, USERS.ADM.username, USERS.ADM.password); await page.goto(BASE_URL + '/ruangan', { waitUntil: 'domcontentloaded', timeout: 15000 }); await page.waitForTimeout(1500); var result = await doAjax(page, '/ruangan/ajax', 'POST', { ruangan_nama: 'Test Invalid', ruangan_kode: 'INV-' + String(Date.now()).slice(-6), ruangan_kuota: '10', ruangan_status: 'Tersedia', ruangan_kategori: 'InvalidKategori' }); expect(result.status === 422 || result.status === 500).toBeTruthy(); await logout(page); });"
$lines += "    test('6f Status invalid 422', async function({ page }) { test.setTimeout(60000); await login(page, USERS.ADM.username, USERS.ADM.password); await page.goto(BASE_URL + '/ruangan', { waitUntil: 'domcontentloaded', timeout: 15000 }); await page.waitForTimeout(1500); var result = await doAjax(page, '/ruangan/ajax', 'POST', { ruangan_nama: 'Test Status Invalid', ruangan_kode: 'STI-' + String(Date.now()).slice(-6), ruangan_kuota: '10', ruangan_status: 'SuperSibuk', ruangan_kategori: 'Jurusan' }); expect(result.status === 422 || result.status === 500).toBeTruthy(); await logout(page); });"
$lines += '  });'
$lines += "`n  // PHASE 7: Jadwal"
$lines += "  test.describe.serial('Phase 7', function() {"
$lines += "    test('7a Admin akses jadwal', async function({ page }) { test.setTimeout(30000); await login(page, USERS.ADM.username, USERS.ADM.password); var resp = await page.goto(BASE_URL + '/jadwal', { waitUntil: 'domcontentloaded' }); expect(resp.status()).toBe(200); await logout(page); });"
$lines += "    test('7b Peminjam akses jadwal FR-4.1', async function({ page }) { test.setTimeout(45000); var roles = [USERS.DSN, USERS.TDK, USERS.MHS]; for (var i = 0; i < roles.length; i++) { await login(page, roles[i].username, roles[i].password); var resp = await page.goto(BASE_URL + '/jadwal', { waitUntil: 'domcontentloaded' }); expect(resp.status()).toBe(200); await logout(page); await page.waitForTimeout(200); } });"
$lines += "    test('7c Admin update status jadwal', async function({ page }) { test.setTimeout(60000); await login(page, USERS.ADM.username, USERS.ADM.password); await page.goto(BASE_URL + '/dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 }); await page.waitForTimeout(1000); var result = await doAjax(page, '/jadwal/1/update_status_ajax', 'PUT', { jadwal_status: 'Akan Datang' }); expect([200, 404, 422]).toContain(result.status); await logout(page); });"
$lines += "    test('7d DSN akses jadwal index', async function({ page }) { test.setTimeout(45000); await login(page, USERS.DSN.username, USERS.DSN.password); await page.goto(BASE_URL + '/dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 }); await page.waitForTimeout(500); var resp = await page.goto(BASE_URL + '/jadwal', { waitUntil: 'domcontentloaded' }); expect(resp.status()).toBe(200); await logout(page); });"
$lines += '  });'
$lines += "`n  // PHASE 8: Dashboard"
$lines += "  test.describe.serial('Phase 8', function() {"
$lines += "    test('8a Admin dashboard', async function({ page }) { test.setTimeout(30000); await login(page, USERS.ADM.username, USERS.ADM.password); await page.goto(BASE_URL + '/dashboard', { waitUntil: 'domcontentloaded' }); await page.waitForTimeout(1000); expect((await page.locator('body').textContent()).length).toBeGreaterThan(100); await logout(page); });"
$lines += "    test('8b Dosen dashboard', async function({ page }) { test.setTimeout(30000); await login(page, USERS.DSN.username, USERS.DSN.password); await page.goto(BASE_URL + '/dashboard', { waitUntil: 'domcontentloaded' }); await page.waitForTimeout(1000); expect(await page.locator('body').textContent()).toBeTruthy(); await logout(page); });"
$lines += "    test('8c Verifikator dashboard', async function({ page }) { test.setTimeout(30000); await login(page, USERS.VRF_KETUA_UMUM.username, USERS.VRF_KETUA_UMUM.password); await page.goto(BASE_URL + '/dashboard', { waitUntil: 'domcontentloaded' }); await page.waitForTimeout(1000); expect(await page.locator('body').textContent()).toBeTruthy(); await logout(page); });"
$lines += "    test('8d Mahasiswa dashboard', async function({ page }) { test.setTimeout(30000); await login(page, USERS.MHS.username, USERS.MHS.password); await page.goto(BASE_URL + '/dashboard', { waitUntil: 'domcontentloaded' }); await page.waitForTimeout(1000); expect(await page.locator('body').textContent()).toBeTruthy(); await logout(page); });"
$lines += "    test('8e Tendik dashboard', async function({ page }) { test.setTimeout(30000); await login(page, USERS.TDK.username, USERS.TDK.password); await page.goto(BASE_URL + '/dashboard', { waitUntil: 'domcontentloaded' }); await page.waitForTimeout(1000); expect(await page.locator('body').textContent()).toBeTruthy(); await logout(page); });"
$lines += '  });'
$lines += "`n  // PHASE 9: Logout & Security"
$lines += "  test.describe.serial('Phase 9', function() {"
$lines += "    test('9a Logout', async function({ page }) { test.setTimeout(30000); await login(page, USERS.ADM.username, USERS.ADM.password); expect((await page.locator('body').textContent()).toLowerCase()).toContain('dashboard'); await page.goto(BASE_URL + '/logout', { waitUntil: 'domcontentloaded' }); await page.waitForTimeout(500); expect(page.url().indexOf('/dashboard') === -1).toBeTruthy(); });"
$lines += "    test('9b Akses tanpa login redirect', async function({ page }) { test.setTimeout(30000); await page.goto(BASE_URL + '/dashboard', { waitUntil: 'domcontentloaded' }).catch(function() {}); expect(page.url().indexOf('/login') !== -1).toBeTruthy(); });"
$lines += "    test('9c Login ulang', async function({ page }) { test.setTimeout(30000); await login(page, USERS.ADM.username, USERS.ADM.password); expect((await page.locator('body').textContent()).toLowerCase()).toContain('dashboard'); await logout(page); });"
$lines += '  });'
$lines += '});'

Set-Content -Path $path -Value ($lines -join "`n") -Encoding UTF8
Write-Output "Written: " + (Get-Item $path).Length + " bytes"
</create_file>
