/**
 * Page Object: Organisasi Module
 *
 * Covers:
 * - Index (Daftar Organisasi) — GET /organisasi
 * - List (DataTables) — POST /organisasi/list
 * - Create — GET /organisasi/create_ajax, POST /organisasi/ajax
 * - Show Detail — GET /organisasi/{id}/show_ajax
 * - Edit — GET /organisasi/{id}/edit_ajax, PUT /organisasi/{id}/update_ajax
 * - Confirm Delete — GET /organisasi/{id}/confirm_ajax
 * - Delete — DELETE /organisasi/{id}/delete_ajax
 *
 * FIX (Page Objects): Sebelumnya menggunakan page.goto() untuk endpoint *_ajax.
 * Route-route itu hanya merender partial view saat $request->ajax() true.
 * page.goto() (full navigation) TIDAK mengirim header X-Requested-With,
 * sehingga selalu redirect ke '/' — modal tidak pernah muncul.
 * Sekarang menggunakan modalAction() via page.evaluate() seperti JadwalPage.
 *
 * Architecture: Controller -> OrganisasiService -> OrganisasiModel
 * All CRUD operations use AJAX modals (jQuery + modalAction).
 */
class OrganisasiPage {
  constructor(page) {
    this.page = page;
  }

  // ──────────────────────────────────────────────────
  // URL Constants
  // ──────────────────────────────────────────────────
  static INDEX_URL = '/organisasi';
  static LIST_URL = '/organisasi/list';
  static CREATE_URL = '/organisasi/create_ajax';
  static STORE_URL = '/organisasi/ajax';

  static showUrl(id) { return `/organisasi/${id}/show_ajax`; }
  static editUrl(id) { return `/organisasi/${id}/edit_ajax`; }
  static updateUrl(id) { return `/organisasi/${id}/update_ajax`; }
  static confirmUrl(id) { return `/organisasi/${id}/confirm_ajax`; }
  static deleteUrl(id) { return `/organisasi/${id}/delete_ajax`; }

  // ──────────────────────────────────────────────────
  // Navigation
  // ──────────────────────────────────────────────────

  async gotoIndex() {
    await this.page.goto(OrganisasiPage.INDEX_URL, { waitUntil: 'networkidle', timeout: 15000 });
    await this.page.waitForTimeout(1000);
  }

  /**
   * FIX: Buka modal via fungsi JS `modalAction(url)` (bukan page.goto() langsung ke URL *_ajax).
   * Route-route *_ajax hanya merender partial view saat $request->ajax() true;
   * modalAction() memenuhi itu via $.ajax GET lalu menyuntikkan hasilnya ke #modalOrganisasi.
   * page.goto() tanpa header X-Requested-With akan selalu redirect.
   */
  async openModalVia(url) {
    const onIndex = this.page.url().includes(OrganisasiPage.INDEX_URL);
    if (!onIndex) {
      await this.gotoIndex();
    }
    await this.page.evaluate((u) => window.modalAction(u), url);
    await this.page.waitForSelector('#modalOrganisasi.show, #modalOrganisasi.in, .modal.fade.show', { timeout: 10000 }).catch(() => {});
    await this.page.waitForTimeout(300);
  }

  async openCreateModal() {
    await this.openModalVia(OrganisasiPage.CREATE_URL);
  }

  async openShowModal(id) {
    await this.openModalVia(OrganisasiPage.showUrl(id));
  }

  async openEditModal(id) {
    await this.openModalVia(OrganisasiPage.editUrl(id));
    // Wait for form fields to render
    await this.page.waitForSelector('input[name="organisasi_nama"]', { timeout: 10000 }).catch(() => {});
    await this.page.waitForTimeout(300);
  }

  async openConfirmModal(id) {
    await this.openModalVia(OrganisasiPage.confirmUrl(id));
  }

  /**
   * Buka modal dengan klik tombol dari DataTables action column
   * @param {number} rowIndex - Index baris di tabel (0-based)
   * @param {'show'|'edit'|'delete'} action - Jenis aksi
   */
  async clickActionButton(rowIndex, action) {
    const actionMap = {
      show: 'button.btn-outline-info',
      edit: 'button.btn-outline-warning',
      delete: 'button.btn-outline-danger',
    };
    const selector = actionMap[action];
    if (!selector) throw new Error(`Unknown action: ${action}`);

    const rows = this.page.locator('#table_id tbody tr');
    const row = rows.nth(rowIndex);
    const btn = row.locator(selector);
    await btn.waitFor({ state: 'visible', timeout: 10000 });
    await btn.click();
    await this.page.waitForTimeout(800);
  }

  // ──────────────────────────────────────────────────
  // jQuery Context (untuk AJAX calls)
  // ──────────────────────────────────────────────────

  async ensureJqueryContext() {
    const checkContext = async () => {
      return await this.page.evaluate(() => {
        return typeof window.$ !== 'undefined' &&
               typeof window.$.ajax !== 'undefined' &&
               document.querySelector('meta[name="csrf-token"]') !== null;
      }).catch(() => false);
    };

    let hasContext = await checkContext();
    if (hasContext) return;

    // Retry: navigasi ke index dan tunggu jQuery benar-benar termuat
    await this.gotoIndex();
    await this.page.waitForFunction(
      () => typeof window.$ !== 'undefined' && typeof window.$.ajax !== 'undefined',
      { timeout: 10000 }
    ).catch(() => {});

    hasContext = await checkContext();
    if (!hasContext) {
      throw new Error(
        'jQuery context tidak tersedia setelah navigasi ke /organisasi. ' +
        'Kemungkinan akun tidak memiliki akses ke halaman ini (redirect ke halaman lain), ' +
        'atau halaman tidak memuat jQuery. Periksa apakah role yang digunakan benar-benar ' +
        'diberi otorisasi mengakses endpoint ini sebelum memanggil ajax().'
      );
    }
  }

  /**
   * Dapatkan CSRF token dari meta tag
   * @returns {Promise<string>}
   */
  async getCsrfToken() {
    return await this.page.evaluate(() => {
      const meta = document.querySelector('meta[name="csrf-token"]');
      return meta ? meta.getAttribute('content') : '';
    });
  }

  // ──────────────────────────────────────────────────
  // AJAX Operations
  // ──────────────────────────────────────────────────

  /**
   * Execute jQuery $.ajax call di dalam browser context
   * @param {string} url - Endpoint URL
   * @param {'POST'|'PUT'|'DELETE'} method - HTTP method
   * @param {object} data - Request payload
   * @returns {Promise<{status: number, body: object}>}
   */
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
          url: url,
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

  /**
   * Buat organisasi baru via AJAX
   * @param {object} data - { organisasi_kode, organisasi_nama, organisasi_logo? }
   * @returns {Promise<{status: number, body: object}>}
   */
  async create(data) {
    const payload = {};
    if (data.organisasi_kode !== undefined) payload.organisasi_kode = data.organisasi_kode;
    if (data.organisasi_nama !== undefined) payload.organisasi_nama = data.organisasi_nama;
    if (data.organisasi_logo !== undefined) payload.organisasi_logo = data.organisasi_logo;

    return await this.ajax(OrganisasiPage.STORE_URL, 'POST', payload);
  }

  /**
   * Isi form create di modal secara manual (via UI, bukan AJAX)
   * @param {object} data - { organisasi_kode, organisasi_nama, organisasi_logo? }
   */
  async fillCreateForm(data) {
    await this.page.fill('input[name="organisasi_kode"]', data.organisasi_kode);
    await this.page.fill('input[name="organisasi_nama"]', data.organisasi_nama);
    if (data.organisasi_logo !== undefined) {
      await this.page.fill('input[name="organisasi_logo"]', data.organisasi_logo);
    }
  }

  /**
   * Submit form create di modal
   */
  async submitCreateForm() {
    const submitBtn = this.page.locator('button[type="submit"]').last();
    await submitBtn.waitFor({ state: 'visible', timeout: 5000 });
    await submitBtn.click();
    await this.page.waitForTimeout(1000);
  }

  // ──────────────────────────────────────────────────
  // Business Operations: READ
  // ──────────────────────────────────────────────────

  /**
   * Dapatkan DataTables response
   * @returns {Promise<{status: number, body: object}>}
   */
  async getDataTable() {
    return await this.ajax(OrganisasiPage.LIST_URL, 'POST', {});
  }

  /**
   * FIX: show_ajax hanya merender partial view saat AJAX.
   * Gunakan ajaxGet (jQuery GET) untuk mendapat konten HTML.
   * @param {number} id
   * @returns {Promise<{status: number, body: string|null}>}
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
    }, OrganisasiPage.showUrl(id));
    return { status: html ? 200 : 404, body: html };
  }

  /**
   * Baca data dari tabel detail yang ditampilkan di modal show
   * @returns {Promise<{kode: string, nama: string, logo: string|null}>}
   */
  async readDetailFromModal() {
    const kode = await this.page.locator('#organisasi_kode').textContent().catch(() => '');
    const nama = await this.page.locator('#organisasi_nama').textContent().catch(() => '');
    const logo = await this.page.locator('#organisasi_logo').textContent().catch(() => null);
    return { kode: kode?.trim() || '', nama: nama?.trim() || '', logo: logo?.trim() || null };
  }

  // ──────────────────────────────────────────────────
  // Business Operations: UPDATE
  // ──────────────────────────────────────────────────

  /**
   * Update organisasi via AJAX (partial update — hanya field yang dikirim).
   * @param {number} id
   * @param {object} data - { organisasi_kode?, organisasi_nama?, organisasi_logo? }
   * @returns {Promise<{status: number, body: object}>}
   */
  async update(id, data) {
    const payload = {};
    if (data.organisasi_kode !== undefined) payload.organisasi_kode = data.organisasi_kode;
    if (data.organisasi_nama !== undefined) payload.organisasi_nama = data.organisasi_nama;
    if (data.organisasi_logo !== undefined) payload.organisasi_logo = data.organisasi_logo;

    return await this.ajax(OrganisasiPage.updateUrl(id), 'PUT', payload);
  }

  /**
   * Update organisasi via AJAX dengan payload lengkap (semua field wajib).
   * @param {number} id
   * @param {object} data - { organisasi_kode, organisasi_nama, organisasi_logo? }
   * @returns {Promise<{status: number, body: object}>}
   */
  async updateFull(id, data) {
    return await this.ajax(OrganisasiPage.updateUrl(id), 'PUT', {
      organisasi_kode: data.organisasi_kode,
      organisasi_nama: data.organisasi_nama,
      organisasi_logo: data.organisasi_logo ?? '',
    });
  }

  /**
   * Isi form edit di modal
   * @param {object} data - { organisasi_kode?, organisasi_nama?, organisasi_logo? }
   */
  async fillEditForm(data) {
    if (data.organisasi_kode !== undefined) {
      await this.page.fill('input[name="organisasi_kode"]', data.organisasi_kode);
    }
    if (data.organisasi_nama !== undefined) {
      await this.page.fill('input[name="organisasi_nama"]', data.organisasi_nama);
    }
    if (data.organisasi_logo !== undefined) {
      await this.page.fill('input[name="organisasi_logo"]', data.organisasi_logo);
    }
  }

  /**
   * Submit form edit di modal
   */
  async submitEditForm() {
    const submitBtn = this.page.locator('#form-edit button[type="submit"], .modal.fade.show button[type="submit"]').last();
    await submitBtn.waitFor({ state: 'visible', timeout: 5000 });
    await submitBtn.click();
    await this.page.waitForTimeout(1000);
  }

  // ──────────────────────────────────────────────────
  // Business Operations: DELETE
  // ──────────────────────────────────────────────────

  /**
   * Hapus organisasi via AJAX
   * @param {number} id
   * @returns {Promise<{status: number, body: object}>}
   */
  async delete(id) {
    return await this.ajax(OrganisasiPage.deleteUrl(id), 'DELETE', {});
  }

  /**
   * Konfirmasi delete di modal (klik tombol hapus)
   */
  async confirmDelete() {
    const deleteBtn = this.page.locator('.modal.fade.show button.btn-danger, #form-hapus button[type="submit"]').last();
    await deleteBtn.waitFor({ state: 'visible', timeout: 5000 });
    await deleteBtn.click();
    await this.page.waitForTimeout(1000);
  }

  // ──────────────────────────────────────────────────
  // Data Generators (untuk testing)
  // ──────────────────────────────────────────────────

  /**
   * Generate data organisasi unik untuk testing
   * @param {object} overrides - Field yang mau di-override
   * @returns {object}
   */
  static generateData(overrides = {}) {
    const timestamp = Date.now().toString().slice(-6);
    return {
      organisasi_kode: `TST${timestamp}`,
      organisasi_nama: `Test Organisasi ${timestamp}`,
      organisasi_logo: '',
      ...overrides,
    };
  }

  /**
   * Generate kode dengan panjang tertentu
   * @param {number} length
   * @returns {string}
   */
  static generateKode(length) {
    return 'X'.repeat(length);
  }

  /**
   * Generate kode unik untuk duplicate test
   * @param {string} prefix
   * @returns {string}
   */
  static generateUniqueKode(prefix = 'TST') {
    return `${prefix}${Date.now().toString().slice(-6)}`;
  }
}

export { OrganisasiPage };

