/**
 * Page Object: Jadwal Module
 *
 * Covers:
 * - Index (Daftar Jadwal) — GET /jadwal
 * - List (DataTables) — POST /jadwal/list (returns [])
 * - Create — GET /jadwal/create_ajax, POST /jadwal/ajax
 * - Show Detail — GET /jadwal/{id}/show_ajax
 * - Edit — GET /jadwal/{id}/edit_ajax, PUT /jadwal/{id}/update_ajax
 * - Confirm Delete — GET /jadwal/{id}/confirm_ajax
 * - Delete — DELETE /jadwal/{id}/delete_ajax
 * - Get Kelas by Prodi — GET /jadwal/get_kelas_by_prodi/{prodi_id}
 * - Update Status — PUT /jadwal/{id}/update_status_ajax
 *
 * Validations (JadwalService::rules()):
 *   - jadwal_nama: required|string|max:255
 *   - jadwal_tgl: required|date
 *   - jadwal_jam_mulai: required|date_format:H:i
 *   - jadwal_jam_selesai: required|date_format:H:i|after:jadwal_jam_mulai
 *   - jadwal_jumPes: required|integer
 *   - user_id: required|exists:m_user,user_id
 *   - ruangan_ids: required|array
 *   - ruangan_ids.*: exists:m_ruangan,ruangan_id
 *
 * Status State Machine (FR-4.2):
 *   Akan Datang -> Berlangsung -> Ditinjau -> Selesai
 *                                          -> Dokumentasi Tidak Sesuai -> Selesai
 *
 * Route Groups (web.php):
 *   - authorize:ADM — create_ajax, store_ajax, edit_ajax, update_ajax, confirm_ajax, delete_ajax, update_status_ajax
 *   - authorize:ADM,DSN,TDK,MHS — index, list, show_ajax, get_kelas_by_prodi
 *
 * Architecture: JadwalController -> JadwalService -> JadwalModel (table: t_jadwal)
 *   pivot: t_jadwal_ruangan (many-to-many with RuanganModel)
 */
class JadwalPage {
  constructor(page) {
    this.page = page;
  }

  // ──────────────────────────────────────────────────
  // URL Constants
  // ──────────────────────────────────────────────────
  static INDEX_URL = '/jadwal';
  static LIST_URL = '/jadwal/list';
  static CREATE_URL = '/jadwal/create_ajax';
  static STORE_URL = '/jadwal/ajax';
  static GET_KELAS_BY_PRODI_URL = (prodiId) => `/jadwal/get_kelas_by_prodi/${prodiId}`;

  static showUrl(id) { return `/jadwal/${id}/show_ajax`; }
  static editUrl(id) { return `/jadwal/${id}/edit_ajax`; }
  static updateUrl(id) { return `/jadwal/${id}/update_ajax`; }
  static confirmUrl(id) { return `/jadwal/${id}/confirm_ajax`; }
  static deleteUrl(id) { return `/jadwal/${id}/delete_ajax`; }
  static updateStatusUrl(id) { return `/jadwal/${id}/update_status_ajax`; }

  // ──────────────────────────────────────────────────
  // Status State Machine (FR-4.2)
  // ──────────────────────────────────────────────────
  static STATUS_TRANSITIONS = {
    'Akan Datang': ['Berlangsung'],
    'Berlangsung': ['Ditinjau'],
    'Ditinjau': ['Dokumentasi Tidak Sesuai', 'Selesai'],
    'Dokumentasi Tidak Sesuai': ['Selesai'],
    'Selesai': [],
  };

  static VALID_STATUSES = ['Akan Datang', 'Berlangsung', 'Ditinjau', 'Dokumentasi Tidak Sesuai', 'Selesai'];
  static INVALID_STATUSES = ['Diajukan', 'Menunggu', 'Ditolak', 'Diterima', 'Busy', '', 'Invalid'];

  // ──────────────────────────────────────────────────
  // Navigation
  // ──────────────────────────────────────────────────

  async gotoIndex() {
    await this.page.goto(JadwalPage.INDEX_URL, { waitUntil: 'networkidle', timeout: 15000 });
    await this.page.waitForTimeout(1000);
  }

  /**
   * Buka modal via fungsi JS asli aplikasi `modalAction(url)` (lihat
   * resources/views/jadwal/index.blade.php), bukan page.goto() langsung ke
   * URL *_ajax. Route-route itu hanya merender partial view saat
   * $request->ajax() true; modalAction() memenuhi itu lewat $.ajax GET lalu
   * menyuntikkan hasilnya ke #modalJadwal dan menampilkannya — persis seperti
   * saat user klik tombol di UI. page.goto() adalah full navigation biasa
   * (tanpa header X-Requested-With) sehingga selalu di-redirect dan modal
   * (serta form di dalamnya) tidak pernah benar-benar muncul.
   */
  async openModalVia(url) {
    const onIndex = this.page.url().includes(JadwalPage.INDEX_URL);
    if (!onIndex) {
      await this.gotoIndex();
    }
    await this.page.evaluate((u) => window.modalAction(u), url);
    await this.page.waitForSelector('#modalJadwal.show, #modalJadwal.in', { timeout: 10000 }).catch(() => {});
    await this.page.waitForTimeout(300);
  }

  async openCreateModal() {
    await this.openModalVia(JadwalPage.CREATE_URL);
  }

  async openShowModal(id) {
    await this.openModalVia(JadwalPage.showUrl(id));
  }

  async openEditModal(id) {
    await this.openModalVia(JadwalPage.editUrl(id));
  }

  async openConfirmModal(id) {
    await this.openModalVia(JadwalPage.confirmUrl(id));
  }

  // ──────────────────────────────────────────────────
  // jQuery Context Helpers
  // ──────────────────────────────────────────────────

  async ensureJqueryContext() {
    const hasContext = await this.page.evaluate(() => {
      return typeof $ !== 'undefined' &&
             typeof $.ajax !== 'undefined' &&
             document.querySelector('meta[name="csrf-token"]') !== null;
    }).catch(() => false);

    if (!hasContext) {
      await this.gotoIndex();
    }
  }

  async getCsrfToken() {
    return await this.page.evaluate(() => {
      const meta = document.querySelector('meta[name="csrf-token"]');
      return meta ? meta.getAttribute('content') : '';
    });
  }

  // ──────────────────────────────────────────────────
  // AJAX Wrapper
  // ──────────────────────────────────────────────────

  async ajax(url, method = 'POST', data = {}) {
    await this.ensureJqueryContext();

    const token = await this.getCsrfToken();
    const payload = { ...data, _token: token };
    if (method === 'PUT' || method === 'DELETE') {
      payload._method = method;
    }

    const result = await this.page.evaluate(async ({ url, payload }) => {
      return new Promise((resolve) => {
        $.ajax({
          url,
          type: 'POST',
          data: payload,
          dataType: 'json',
          success: (response) => resolve({ status: 200, body: response }),
          error: (xhr) => {
            try {
              const body = JSON.parse(xhr.responseText);
              resolve({ status: xhr.status, body });
            } catch (e) {
              resolve({ status: xhr.status, body: { status: false, message: xhr.statusText } });
            }
          }
        });
      });
    }, { url, payload });

    return result;
  }

  /**
   * GET via jQuery $.ajax (bukan page.goto). Route seperti show_ajax hanya
   * merender partial view bila Laravel mendeteksi $request->ajax() true, yang
   * butuh header X-Requested-With — page.goto() sebagai full navigation tidak
   * pernah mengirim header itu sehingga selalu redirect ke halaman index/dashboard.
   * jQuery mengirim header itu secara default, sama seperti method `ajax()` di atas.
   */
  async ajaxGet(url) {
    await this.ensureJqueryContext();

    return this.page.evaluate((requestUrl) => {
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

  // ──────────────────────────────────────────────────
  // Business Operations: CREATE
  // ──────────────────────────────────────────────────

  async create(data) {
    return await this.ajax(JadwalPage.STORE_URL, 'POST', {
      jadwal_nama: data.jadwal_nama,
      jadwal_tgl: data.jadwal_tgl,
      jadwal_jam_mulai: data.jadwal_jam_mulai,
      jadwal_jam_selesai: data.jadwal_jam_selesai,
      jadwal_jumPes: String(data.jadwal_jumPes),
      user_id: String(data.user_id),
      ruangan_ids: data.ruangan_ids,
    });
  }

  async fillCreateForm(data) {
    await this.page.fill('input[name="jadwal_nama"]', data.jadwal_nama);
    await this.page.fill('input[name="jadwal_tgl"]', data.jadwal_tgl);
    await this.page.fill('input[name="jadwal_jam_mulai"]', data.jadwal_jam_mulai);
    await this.page.fill('input[name="jadwal_jam_selesai"]', data.jadwal_jam_selesai);
    await this.page.fill('input[name="jadwal_jumPes"]', String(data.jadwal_jumPes));
    if (data.user_id) {
      await this.page.selectOption('select[name="user_id"]', String(data.user_id));
    }
    if (data.ruangan_ids && data.ruangan_ids.length > 0) {
      const select = this.page.locator('select[name="ruangan_ids[]"]');
      if (await select.isVisible().catch(() => false)) {
        await select.selectOption(data.ruangan_ids.map(String));
      } else {
        // Checkbox style
        for (const id of data.ruangan_ids) {
          const cb = this.page.locator(`input[name="ruangan_ids[]"][value="${id}"]`);
          if (await cb.isVisible().catch(() => false)) {
            await cb.check();
          }
        }
      }
    }
  }

  async submitCreateForm() {
    const submitBtn = this.page.locator('button[type="submit"]').last();
    await submitBtn.waitFor({ state: 'visible', timeout: 5000 });
    await submitBtn.click();
    await this.page.waitForTimeout(1000);
  }

  // ──────────────────────────────────────────────────
  // Business Operations: READ
  // ──────────────────────────────────────────────────

  async getDataTable() {
    return await this.ajax(JadwalPage.LIST_URL, 'POST', {});
  }

  async getDetail(id) {
    const resp = await this.page.goto(JadwalPage.showUrl(id), { waitUntil: 'domcontentloaded', timeout: 15000 });
    return { status: resp.status(), body: null };
  }

  async getKelasByProdi(prodiId) {
    const resp = await this.page.goto(JadwalPage.GET_KELAS_BY_PRODI_URL(prodiId), { waitUntil: 'domcontentloaded', timeout: 15000 });
    let body = null;
    try {
      body = JSON.parse(await resp.text());
    } catch (e) {}
    return { status: resp.status(), body };
  }

  // ──────────────────────────────────────────────────
  // Business Operations: UPDATE
  // ──────────────────────────────────────────────────

  async update(id, data) {
    return await this.ajax(JadwalPage.updateUrl(id), 'PUT', {
      jadwal_nama: data.jadwal_nama,
      jadwal_tgl: data.jadwal_tgl,
      jadwal_jam_mulai: data.jadwal_jam_mulai,
      jadwal_jam_selesai: data.jadwal_jam_selesai,
      jadwal_jumPes: String(data.jadwal_jumPes),
      user_id: String(data.user_id),
      ruangan_ids: data.ruangan_ids,
    });
  }

  async fillEditForm(data) {
    if (data.jadwal_nama !== undefined) {
      await this.page.fill('input[name="jadwal_nama"]', data.jadwal_nama);
    }
    if (data.jadwal_tgl !== undefined) {
      await this.page.fill('input[name="jadwal_tgl"]', data.jadwal_tgl);
    }
    if (data.jadwal_jam_mulai !== undefined) {
      await this.page.fill('input[name="jadwal_jam_mulai"]', data.jadwal_jam_mulai);
    }
    if (data.jadwal_jam_selesai !== undefined) {
      await this.page.fill('input[name="jadwal_jam_selesai"]', data.jadwal_jam_selesai);
    }
    if (data.jadwal_jumPes !== undefined) {
      await this.page.fill('input[name="jadwal_jumPes"]', String(data.jadwal_jumPes));
    }
    if (data.user_id) {
      await this.page.selectOption('select[name="user_id"]', String(data.user_id));
    }
  }

  async submitEditForm() {
    const submitBtn = this.page.locator('#form-edit button[type="submit"], .modal.fade.show button[type="submit"]').last();
    await submitBtn.waitFor({ state: 'visible', timeout: 5000 });
    await submitBtn.click();
    await this.page.waitForTimeout(1000);
  }

  // ──────────────────────────────────────────────────
  // Business Operations: DELETE
  // ──────────────────────────────────────────────────

  async delete(id) {
    return await this.ajax(JadwalPage.deleteUrl(id), 'DELETE', {});
  }

  async confirmDelete() {
    const deleteBtn = this.page.locator('.modal.fade.show button.btn-danger, #form-hapus button[type="submit"]').last();
    await deleteBtn.waitFor({ state: 'visible', timeout: 5000 });
    await deleteBtn.click();
    await this.page.waitForTimeout(1000);
  }

  // ──────────────────────────────────────────────────
  // Business Operations: UPDATE STATUS (State Machine)
  // ──────────────────────────────────────────────────

  /**
   * Update status jadwal sesuai state machine (FR-4.2)
   * @param {number} id - jadwal_id
   * @param {string} newStatus - Target status
   * @returns {Promise<{status: number, body: object}>}
   */
  async updateStatus(id, newStatus) {
    return await this.ajax(JadwalPage.updateStatusUrl(id), 'PUT', {
      jadwal_status: newStatus,
    });
  }

  // ──────────────────────────────────────────────────
  // Data Generators
  // ──────────────────────────────────────────────────

  static generateData(overrides = {}) {
    const timestamp = Date.now().toString().slice(-6);
    // jadwal_tgl unik per pemanggilan (kecuali di-override) agar test yang tidak
    // menyebutkan tanggal/jam/ruangan secara eksplisit tidak saling bentrok pada
    // slot ruangan yang sama (double-booking check di JadwalService bersifat nyata).
    // Pakai offset hari dari basis tetap, bukan modulo kecil, supaya tidak wrap-around
    // dan bentrok lagi setelah puluhan create() dalam satu file test.
    JadwalPage._dateCounter = (JadwalPage._dateCounter || 0) + 1;
    const base = new Date('2026-12-01T00:00:00Z');
    base.setUTCDate(base.getUTCDate() + JadwalPage._dateCounter);
    const uniqueDate = base.toISOString().slice(0, 10);
    return {
      jadwal_nama: `Test Jadwal ${timestamp}`,
      jadwal_tgl: uniqueDate,
      jadwal_jam_mulai: '08:00',
      jadwal_jam_selesai: '16:00',
      jadwal_jumPes: 50,
      user_id: 1,
      ruangan_ids: [1],
      ...overrides,
    };
  }

  static generateNama(length) {
    return 'N'.repeat(length);
  }

  static generateUniqueName(prefix = 'JDL') {
    return `${prefix} ${Date.now().toString().slice(-6)}`;
  }
}

export { JadwalPage };
