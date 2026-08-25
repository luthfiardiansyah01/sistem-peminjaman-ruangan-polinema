/**
 * Page Object: Ruangan Module
 *
 * Covers:
 * - Index (Daftar Ruangan) — GET /ruangan
 * - List (DataTables) — POST /ruangan/list
 * - Create — GET /ruangan/create_ajax, POST /ruangan/ajax
 * - Show Detail — GET /ruangan/{id}/show_ajax
 * - Edit — GET /ruangan/{id}/edit_ajax, PUT /ruangan/{id}/update_ajax
 * - Confirm Delete — GET /ruangan/{id}/confirm_ajax
 * - Delete — DELETE /ruangan/{id}/delete_ajax
 *
 * FIX (Page Objects): Sebelumnya menggunakan page.goto() untuk endpoint *_ajax.
 * Route-route itu hanya merender partial view saat $request->ajax() true.
 * page.goto() (full navigation) TIDAK mengirim header X-Requested-With,
 * sehingga selalu redirect ke '/' — modal tidak pernah muncul.
 * Sekarang menggunakan modalAction() via page.evaluate() seperti JadwalPage.
 *
 * Validations (from RuanganService::rules()):
 *   - ruangan_kode: required|string|max:10|unique:m_ruangan
 *   - ruangan_nama: required|string|max:100
 *   - ruangan_fasilitas: nullable|string
 *   - ruangan_kuota: required|integer
 *   - ruangan_status: nullable|in:Tersedia,Diajukan,Tidak Tersedia
 *   - ruangan_kategori: required|in:Jurusan,Umum
 *
 * Architecture: RuanganController -> RuanganService -> RuanganModel (table: m_ruangan)
 * All CRUD: AJAX modals (jQuery + modalAction)
 * Permission: ADM for create/edit/delete; ADM,DSN,TDK,MHS for index/show
 */
class RuanganPage {
  constructor(page) {
    this.page = page;
  }

  // ──────────────────────────────────────────────────
  // URL Constants
  // ──────────────────────────────────────────────────
  static INDEX_URL = '/ruangan';
  static LIST_URL = '/ruangan/list';
  static CREATE_URL = '/ruangan/create_ajax';
  static STORE_URL = '/ruangan/ajax';

  static showUrl(id) { return `/ruangan/${id}/show_ajax`; }
  static editUrl(id) { return `/ruangan/${id}/edit_ajax`; }
  static updateUrl(id) { return `/ruangan/${id}/update_ajax`; }
  static confirmUrl(id) { return `/ruangan/${id}/confirm_ajax`; }
  static deleteUrl(id) { return `/ruangan/${id}/delete_ajax`; }

  // ──────────────────────────────────────────────────
  // Navigation
  // ──────────────────────────────────────────────────

  async gotoIndex() {
    await this.page.goto(RuanganPage.INDEX_URL, { waitUntil: 'networkidle', timeout: 15000 });
    await this.page.waitForTimeout(1000);
  }

  /**
   * FIX: Buka modal via fungsi JS `modalAction(url)` (bukan page.goto() langsung ke URL *_ajax).
   * Route-route *_ajax hanya merender partial view saat $request->ajax() true;
   * modalAction() memenuhi itu via $.ajax GET lalu menyuntikkan hasilnya ke #modalRuangan.
   * page.goto() tanpa header X-Requested-With akan selalu redirect.
   */
  async openModalVia(url) {
    const onIndex = this.page.url().includes(RuanganPage.INDEX_URL);
    if (!onIndex) {
      await this.gotoIndex();
    }
    await this.page.evaluate((u) => window.modalAction(u), url);
    await this.page.waitForSelector('#modalRuangan.show, #modalRuangan.in, .modal.fade.show', { timeout: 10000 }).catch(() => {});
    await this.page.waitForTimeout(300);
  }

  async openCreateModal() {
    await this.openModalVia(RuanganPage.CREATE_URL);
  }

  async openShowModal(id) {
    await this.openModalVia(RuanganPage.showUrl(id));
  }

  async openEditModal(id) {
    await this.openModalVia(RuanganPage.editUrl(id));
  }

  async openConfirmModal(id) {
    await this.openModalVia(RuanganPage.confirmUrl(id));
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
      await this.page.waitForFunction(
        () => typeof window.$ !== 'undefined' && typeof window.$.ajax !== 'undefined',
        { timeout: 10000 }
      ).catch(() => {});
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

  // ──────────────────────────────────────────────────
  // Business Operations: CREATE
  // ──────────────────────────────────────────────────

  async create(data) {
    // TIDAK ada default `?? 'Jurusan'` di sini — RuanganPage.generateData() sudah
    // selalu menyertakan ruangan_kategori sendiri untuk test yang tidak peduli
    // nilainya. Beberapa test (mis. TC-RG-CR-23, TC-RG-ST-06) sengaja memanggil
    // create() langsung TANPA lewat generateData() untuk menguji validasi
    // 'required' saat field itu null/tidak dikirim — default di sini akan diam-diam
    // menggagalkan skenario itu.
    return await this.ajax(RuanganPage.STORE_URL, 'POST', {
      ruangan_kode: data.ruangan_kode,
      ruangan_nama: data.ruangan_nama,
      ruangan_fasilitas: data.ruangan_fasilitas ?? '',
      ruangan_kuota: String(data.ruangan_kuota),
      ruangan_status: data.ruangan_status ?? 'Tersedia',
      ruangan_kategori: data.ruangan_kategori,
    });
  }

  async fillCreateForm(data) {
    await this.page.fill('input[name="ruangan_kode"]', data.ruangan_kode);
    await this.page.fill('input[name="ruangan_nama"]', data.ruangan_nama);
    if (data.ruangan_fasilitas !== undefined) {
      await this.page.fill('input[name="ruangan_fasilitas"]', data.ruangan_fasilitas);
    }
    await this.page.fill('input[name="ruangan_kuota"]', String(data.ruangan_kuota));
    if (data.ruangan_status) {
      const select = this.page.locator('select[name="ruangan_status"]');
      if (await select.isVisible().catch(() => false)) {
        await select.selectOption(data.ruangan_status);
      }
    }
    const kategoriSelect = this.page.locator('select[name="ruangan_kategori"]');
    if (await kategoriSelect.isVisible().catch(() => false)) {
      await kategoriSelect.selectOption(data.ruangan_kategori ?? 'Jurusan');
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
    return await this.ajax(RuanganPage.LIST_URL, 'POST', {});
  }

  /**
   * FIX: show_ajax hanya merender partial view saat AJAX.
   * Gunakan ajaxGet (jQuery GET) untuk mendapat konten HTML.
   */
  async getDetail(id) {
    await this.ensureJqueryContext();
    const html = await this.page.evaluate((url) => {
      return new Promise((resolve) => {
        $.ajax({
          url,
          type: 'GET',
          dataType: 'html',
          success: (response) => resolve(String(response)),
          error: (xhr) => resolve(''),
        });
      });
    }, RuanganPage.showUrl(id));
    return { status: html ? 200 : 404, body: html };
  }

  async readDetailFromModal() {
    const kode = await this.page.locator('#ruangan_kode').textContent().catch(() => '');
    const nama = await this.page.locator('#ruangan_nama').textContent().catch(() => '');
    const fasilitas = await this.page.locator('#ruangan_fasilitas').textContent().catch(() => '');
    const kuota = await this.page.locator('#ruangan_kuota').textContent().catch(() => '');
    const status = await this.page.locator('#ruangan_status').textContent().catch(() => '');
    const kategori = await this.page.locator('#ruangan_kategori').textContent().catch(() => '');
    return {
      kode: kode?.trim() || '',
      nama: nama?.trim() || '',
      fasilitas: fasilitas?.trim() || '',
      kuota: kuota?.trim() || '',
      status: status?.trim() || '',
      kategori: kategori?.trim() || '',
    };
  }

  // ──────────────────────────────────────────────────
  // Business Operations: UPDATE
  // ──────────────────────────────────────────────────

  async update(id, data) {
    const payload = {};
    if (data.ruangan_kode !== undefined) payload.ruangan_kode = data.ruangan_kode;
    if (data.ruangan_nama !== undefined) payload.ruangan_nama = data.ruangan_nama;
    if (data.ruangan_fasilitas !== undefined) payload.ruangan_fasilitas = data.ruangan_fasilitas;
    if (data.ruangan_kuota !== undefined) payload.ruangan_kuota = String(data.ruangan_kuota);
    if (data.ruangan_status !== undefined) payload.ruangan_status = data.ruangan_status;
    if (data.ruangan_kategori !== undefined) payload.ruangan_kategori = data.ruangan_kategori;

    return await this.ajax(RuanganPage.updateUrl(id), 'PUT', payload);
  }

  async fillEditForm(data) {
    if (data.ruangan_kode !== undefined) {
      await this.page.fill('input[name="ruangan_kode"]', data.ruangan_kode);
    }
    if (data.ruangan_nama !== undefined) {
      await this.page.fill('input[name="ruangan_nama"]', data.ruangan_nama);
    }
    if (data.ruangan_fasilitas !== undefined) {
      await this.page.fill('input[name="ruangan_fasilitas"]', data.ruangan_fasilitas);
    }
    if (data.ruangan_kuota !== undefined) {
      await this.page.fill('input[name="ruangan_kuota"]', String(data.ruangan_kuota));
    }
    if (data.ruangan_status) {
      const select = this.page.locator('select[name="ruangan_status"]');
      if (await select.isVisible().catch(() => false)) {
        await select.selectOption(data.ruangan_status);
      }
    }
    if (data.ruangan_kategori) {
      const select = this.page.locator('select[name="ruangan_kategori"]');
      if (await select.isVisible().catch(() => false)) {
        await select.selectOption(data.ruangan_kategori);
      }
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
    return await this.ajax(RuanganPage.deleteUrl(id), 'DELETE', {});
  }

  async confirmDelete() {
    const deleteBtn = this.page.locator('.modal.fade.show button.btn-danger, #form-hapus button[type="submit"]').last();
    await deleteBtn.waitFor({ state: 'visible', timeout: 5000 });
    await deleteBtn.click();
    await this.page.waitForTimeout(1000);
  }

  // ──────────────────────────────────────────────────
  // Business Operations: STATUS SYNC (via PengajuanService)
  // ──────────────────────────────────────────────────

  async getStatusFromShow(id) {
    await this.openShowModal(id);
    const statusEl = this.page.locator('#ruangan_status, .modal-body td:contains("Status") + td, .modal-body tr:nth-child(5) td:last-child');
    try {
      return await statusEl.textContent({ timeout: 3000 }).then(t => t?.trim() || '');
    } catch {
      return '';
    }
  }

  // ──────────────────────────────────────────────────
  // Data Generators
  // ──────────────────────────────────────────────────

  static generateData(overrides = {}) {
    const timestamp = Date.now().toString().slice(-6);
    return {
      ruangan_kode: `RG${timestamp}`,
      ruangan_nama: `Test Ruangan ${timestamp}`,
      ruangan_fasilitas: 'Meja, Kursi, AC, LCD',
      ruangan_kuota: 30,
      ruangan_status: 'Tersedia',
      ruangan_kategori: 'Jurusan',
      ...overrides,
    };
  }

  static generateKode(length) {
    // Bukan sekadar 'R'.repeat(length) — nilai itu DETERMINISTIK (selalu string
    // sama persis tiap run), jadi bentrok dengan ruangan_kode unique dari row sisa
    // run sebelumnya (mis. TC-RG-UP-12 gagal 422 karena 'RRRRRRRRRR' sudah ada di
    // DB). Sisipkan akhiran timestamp supaya tetap unik tiap panggilan, TAPI total
    // panjang string yang diminta tetap presisi (penting untuk test boundary).
    const suffix = Date.now().toString().slice(-4);
    if (length <= suffix.length) {
      return suffix.slice(0, length);
    }
    return 'R'.repeat(length - suffix.length) + suffix;
  }

  static generateUniqueKode(prefix = 'RG') {
    // Ensure total length doesn't exceed 10 chars (max:10 validation rule)
    const ts = Date.now().toString().slice(-6);
    const maxPrefixLen = 10 - ts.length;
    const safePrefix = prefix.slice(0, maxPrefixLen);
    return `${safePrefix}${ts}`;
  }

  static STATUS_ENUM = ['Tersedia', 'Diajukan', 'Tidak Tersedia'];
  static KATEGORI_ENUM = ['Jurusan', 'Umum'];
}

export { RuanganPage };

