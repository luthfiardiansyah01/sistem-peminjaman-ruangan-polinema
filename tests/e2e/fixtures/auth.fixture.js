/**
 * Auth fixture untuk Playwright E2E tests.
 * Login via UI form submit (jQuery AJAX).
 * Session cookie otomatis tersimpan di browser context Playwright.
 *
 * RESPONSE FORMAT COMPATIBILITY:
 * Laravel services return response dengan key `status` (bool).
 * Tapi ErrorResponse::fromServiceResult() mengubah jadi key `success`.
 * Helper `doAjax()` di sini menormalisasi response agar BENTUK APAPUN
 * (status ATAU success) tetap bisa diakses sebagai `result.body.status`.
 */

const USERS = {
  ADM: { username: 'admin1', password: '0000000000', role: 'ADM' },
  DSN: { username: 'dosen', password: '1111111111', role: 'DSN' },
  TDK: { username: 'tendik1', password: '0021111111', role: 'TDK' },
  /**
   * MHS: mahasiswa1 — password dari database (spr_jti.sql) adalah NIM user_id=4
   * mahasiswa1 memiliki user_id=4, NIM=2241760001, password=2241760001 (bcrypt hash)
   */
  MHS: { username: 'mahasiswa1', password: '2241760001', role: 'MHS' },
  /**
   * Jabatan approval — approval berjenjang, lihat database/seeders/JabatanApprovalSeeder.php.
   * BUKAN akun/level tersendiri lagi (level VRF sudah dihapus) — ini dosen/mahasiswa
   * ASLI yang sudah ada di m_dosen/m_mahasiswa, cuma dipasangi jabatan approval
   * tambahan. Login-nya tetap sebagai role DSN/MHS biasa. Semua password: 123456
   * (di-set khusus untuk 5 akun ini oleh seeder; TIDAK menimpa mahasiswa1/dosen/
   * tendik1/admin1 yang dipakai USERS.MHS/DSN/TDK/ADM di atas).
   */
  VRF_KETUA_UMUM: { username: '2241760003', password: '123456', role: 'MHS' },
  VRF_DPK: { username: 'dosen2', password: '123456', role: 'DSN' },
  VRF_PRESIDEN_BEM: { username: '2241760063', password: '123456', role: 'MHS' },
  VRF_KETUA_JURUSAN: { username: '0013333333', password: '123456', role: 'DSN' },
  VRF_WADIR_2: { username: '0014444444', password: '123456', role: 'DSN' },
};

const BASE_URL = 'http://127.0.0.1:8000';

/**
 * Normalisasi response body agar key `success` (dari ErrorResponse)
 * bisa diakses sebagai `status`, dan sebaliknya.
 */
function normalizeBody(body) {
  if (!body || typeof body !== 'object') return body;
  const result = { ...body };
  if (result.success !== undefined && result.status === undefined) {
    result.status = result.success;
  }
  if (result.status !== undefined && result.success === undefined) {
    result.success = result.status;
  }
  return result;
}

/**
 * Login via UI form submit (jQuery AJAX).
 * Login response ditangkap melalui page.waitForResponse sebelum redirect terjadi.
 * Setelah response diterima, redirect dilakukan oleh browser secara otomatis
 * melalui SweetAlert -> window.location.href.
 */
async function login(page, username, password) {
  await page.goto(BASE_URL + '/login', { waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {});
  await page.waitForSelector('#username', { timeout: 10000 });
  await page.waitForTimeout(500);

  await page.fill('#username', username);
  await page.fill('#password', password);

  // Tunggu response dan ambil body SEBELUM redirect terjadi
  let loginData = null;
  let loginResponse = null;
  
  // Capture response sebelum redirect
  page.on('response', async (response) => {
    if (response.url().includes('/login') && response.request().method() === 'POST' && response.status() === 200) {
      try {
        loginResponse = response;
        loginData = await response.json();
      } catch (e) {
        // Ignore - response mungkin sudah terhapus
      }
    }
  });

  await page.click('button[type="submit"]');
  
  // Tunggu response dengan timeout
  const respPromise = page.waitForResponse(r => {
    return r.url().includes('/login') && r.request().method() === 'POST' && r.status() === 200;
  }, { timeout: 15000 });
  
  const resp = await respPromise;
  
  // Ambil data response jika belum ada
  if (!loginData) {
    try {
      loginData = await resp.json();
    } catch (e) {
      throw new Error(`Gagal membaca response login: ${e.message}`);
    }
  }

  if (!loginData.status) {
    throw new Error(`Login gagal untuk ${username}: ${loginData.message || 'Unknown error'}`);
  }

  // SweetAlert sukses menunggu klik "OK" sebelum redirect dijalankan
  await page.click('.swal2-confirm', { timeout: 5000 }).catch(() => {});

  // Tunggu redirect ke dashboard (redirect dilakukan oleh browser setelah SweetAlert)
  await page.waitForURL('**/dashboard', { timeout: 20000 });
  await page.waitForTimeout(500);
}

/**
 * Logout.
 */
async function logout(page) {
  try {
    await page.goto(BASE_URL + '/logout', { waitUntil: 'domcontentloaded', timeout: 10000 });
    await page.waitForTimeout(500);
  } catch (e) {}
}

/**
 * Navigasi ke halaman yang memiliki jQuery + CSRF token.
 */
async function gotoJqueryPage(page) {
  await page.goto(BASE_URL + '/dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {});
  await page.waitForTimeout(1000);
}

/**
 * AJAX via jQuery $.ajax.
 * CSRF token otomatis diambil dari meta tag di halaman.
 * Response body dinormalisasi agar key `status` dan `success` tersedia.
 */
async function doAjax(page, url, method, data = {}) {
  const hasJquery = await page.evaluate(() => {
    return typeof $ !== 'undefined' && typeof $.ajax !== 'undefined' && document.querySelector('meta[name="csrf-token"]') !== null;
  }).catch(() => false);

  if (!hasJquery) {
    await gotoJqueryPage(page);
  }

  const token = await page.evaluate(() => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  });

  const payload = { ...data, _token: token };
  if (method === 'PUT' || method === 'DELETE') {
    payload._method = method;
  }

  const rawResult = await page.evaluate(async ({ url, payload }) => {
    return new Promise((resolve) => {
      $.ajax({
        url: url,
        type: 'POST',
        data: payload,
        dataType: 'json',
        success: function(response) {
          resolve({ status: 200, body: response });
        },
        error: function(xhr) {
          try {
            const body = JSON.parse(xhr.responseText);
            resolve({ status: xhr.status, body });
          } catch (e) {
            resolve({ status: xhr.status, body: { status: false, message: xhr.statusText || 'Unknown error' } });
          }
        }
      });
    });
  }, { url, payload });

  // Normalisasi: jika Laravel ErrorResponse mengembalikan {success: bool},
  // tambahkan key `status` agar test bisa akses result.body.status
  if (rawResult.body && typeof rawResult.body === 'object') {
    rawResult.body = normalizeBody(rawResult.body);
  }

  return rawResult;
}

export { USERS, login, logout, doAjax };

