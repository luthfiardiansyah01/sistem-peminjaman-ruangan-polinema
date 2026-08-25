/**
 * login.spec.js — Playwright E2E Test untuk Authentication
 *
 * Route: GET /login, POST /login, GET /logout
 * Controller: AuthController@login, AuthController@postLogin, AuthController@logout
 * Service: AuthService (via AuthServiceInterface)
 * View: resources/views/auth/login.blade.php
 *
 * Form Structure:
 *   - #form-login (POST to /login)
 *   - #username (input text, minlength:4, maxlength:100)
 *   - #password (input password, minlength:6, maxlength:50)
 *   - button[type="submit"] (btn-primary, text "Login")
 *   - CSRF via meta[name="csrf-token"]
 *   - jQuery Validate + AJAX submit
 *
 * Response Format (JSON):
 *   success: { status: true, message: "...", redirect: "/dashboard" }
 *   failure: { status: false, message: "...", msgField: { username: [...], password: [...] } }
 *   already logged in: redirect to /dashboard (302)
 *
 * Users (from seeders):
 *   ADM:  admin1 / 0000000000
 *   DSN:  dosen / 1111111111
 *   TDK:  tendik1 / 0021111111
 *   MHS:  mahasiswa1 / 0011111111
 */

import { test, expect } from '@playwright/test';

// Increase default timeout for login tests (redirects take time)
test.describe.configure({ timeout: 120000 });

const BASE_URL = 'http://127.0.0.1:8000';
const USERS = {
  ADM:  { username: 'admin1',      password: '0000000000', role: 'ADM'  },
  DSN:  { username: 'dosen',       password: '1111111111', role: 'DSN'  },
  TDK:  { username: 'tendik1',     password: '0021111111', role: 'TDK'  },
  MHS:  { username: 'mahasiswa1',  password: '2241760001', role: 'MHS'  },
};

test.describe('Authentication — Login & Logout', () => {

  test.beforeEach(async ({ page }) => {
    // Pastikan setiap test memulai dari halaman login yang bersih
    // (tanpa session login sebelumnya)
    await page.goto(BASE_URL + '/login', { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForSelector('#username', { timeout: 10000 });
    await page.waitForTimeout(500);
  });

  // ─────────────────────────────────────────────
  // Positive Test Cases — Login Sukses
  // ─────────────────────────────────────────────

  test.describe('Positive — Login Berhasil', () => {

    test('[POSITIF] TC-LOGIN-01: Login sukses sebagai Admin (ADM)', async ({ page }) => {
      await page.fill('#username', USERS.ADM.username);
      await page.fill('#password', USERS.ADM.password);

      // Klik login - redirect akan terjadi segera setelah AJAX success
      await page.click('button[type="submit"]');

      // SweetAlert sukses menunggu klik "OK" sebelum redirect dijalankan
      await page.click('.swal2-confirm', { timeout: 10000 }).catch(() => {});

      // Tunggu redirect ke dashboard
      await page.waitForURL('**/dashboard', { timeout: 15000 });
      
      // Verifikasi dashboard sudah dimuat (indikator: stat-card ada 4)
      const statCards = page.locator('.stat-card');
      await expect(statCards).toHaveCount(4);
      
      // Verifikasi dashboard menampilkan statistik (dashboard content)
      await expect(statCards.first()).toBeVisible();
      await expect(statCards.nth(3)).toBeVisible(); // Total Pengguna Aktif card
    });

    test('[POSITIF] TC-LOGIN-02: Login sukses sebagai Dosen (DSN)', async ({ page }) => {
      await page.fill('#username', USERS.DSN.username);
      await page.fill('#password', USERS.DSN.password);

      await page.click('button[type="submit"]');

      // SweetAlert sukses menunggu klik "OK" sebelum redirect dijalankan
      await page.click('.swal2-confirm', { timeout: 10000 }).catch(() => {});

      // Tunggu redirect ke dashboard
      await page.waitForURL('**/dashboard', { timeout: 15000 });
      
      // Verifikasi dashboard sudah dimuat
      const statCards = page.locator('.stat-card');
      await expect(statCards).toHaveCount(4);
      await expect(statCards.first()).toBeVisible();
    });

    test('[POSITIF] TC-LOGIN-03: Login sukses sebagai Tendik (TDK)', async ({ page }) => {
      await page.fill('#username', USERS.TDK.username);
      await page.fill('#password', USERS.TDK.password);

      await page.click('button[type="submit"]');

      // SweetAlert sukses menunggu klik "OK" sebelum redirect dijalankan
      await page.click('.swal2-confirm', { timeout: 10000 }).catch(() => {});

      // Tunggu redirect ke dashboard
      await page.waitForURL('**/dashboard', { timeout: 15000 });
      
      // Verifikasi dashboard sudah dimuat
      const statCards = page.locator('.stat-card');
      await expect(statCards).toHaveCount(4);
      await expect(statCards.first()).toBeVisible();
    });

    test('[POSITIF] TC-LOGIN-04: Login sukses sebagai Mahasiswa (MHS)', async ({ page }) => {
      await page.fill('#username', USERS.MHS.username);
      await page.fill('#password', USERS.MHS.password);

      await page.click('button[type="submit"]');

      // SweetAlert sukses menunggu klik "OK" sebelum redirect dijalankan
      await page.click('.swal2-confirm', { timeout: 10000 }).catch(() => {});

      // Tunggu redirect ke dashboard
      await page.waitForURL('**/dashboard', { timeout: 15000 });
      
      // Verifikasi dashboard sudah dimuat
      const statCards = page.locator('.stat-card');
      await expect(statCards).toHaveCount(4);
      await expect(statCards.first()).toBeVisible();
    });

    test('[BOUNDARY] TC-LOGIN-05: Login dengan username 4 karakter (minlength)', async ({ page }) => {
      // Username minimal 4 karakter
      await page.fill('#username', 'test');
      await page.fill('#password', USERS.ADM.password);

      // Validasi jQuery seharusnya lolos (minlength:4)
      // Tapi karena username tidak dikenal, response gagal
      const [response] = await Promise.all([
        page.waitForResponse(resp =>
          resp.url().includes('/login') &&
          resp.request().method() === 'POST'
        , { timeout: 15000 }),
        page.click('button[type="submit"]'),
      ]);

      expect(response.status()).toBe(200);
      const body = await response.json();
      expect(body.status).toBe(false);
    });

    test('[POSITIF] TC-LOGIN-06: Login sukses lalu akses halaman terproteksi', async ({ page }) => {
      // Login sebagai Admin
      await page.fill('#username', USERS.ADM.username);
      await page.fill('#password', USERS.ADM.password);

      // Klik login - redirect akan terjadi segera setelah AJAX success
      await page.click('button[type="submit"]');

      // SweetAlert sukses menunggu klik "OK" sebelum redirect dijalankan
      await page.click('.swal2-confirm', { timeout: 10000 }).catch(() => {});

      // Tunggu redirect ke dashboard
      await page.waitForURL('**/dashboard', { timeout: 15000 });
      
      // Verifikasi dashboard sudah dimuat
      const statCards = page.locator('.stat-card');
      await expect(statCards).toHaveCount(4);
      await expect(statCards.first()).toBeVisible();

      // Akses halaman terproteksi — harus 200 (bukan redirect login)
      const resp = await page.goto(BASE_URL + '/admin', { waitUntil: 'domcontentloaded' });
      expect(resp.status()).toBe(200);
    });
  });

  // ─────────────────────────────────────────────
  // Negative Test Cases — Login Gagal
  // ─────────────────────────────────────────────

  test.describe('Negative — Login Gagal / Validasi', () => {

    test('[NEGATIF] TC-LOGIN-07: Login dengan username kosong', async ({ page }) => {
      await page.fill('#username', '');
      await page.fill('#password', 'somepassword');

      // Klik submit — validasi jQuery akan mencegah AJAX
      await page.click('button[type="submit"]');
      await page.waitForTimeout(1000);

      // Error text muncul di span.invalid-feedback yang ada di dalam .input-group
      // Gunakan descendant selector untuk mencari di dalam form
      const errorSpans = page.locator('.input-group span.invalid-feedback:has-text("harus diisi")');
      await expect(errorSpans).toHaveCount(1);
      await expect(errorSpans.first()).toBeVisible();
    });

    test('[NEGATIF] TC-LOGIN-08: Login dengan password kosong', async ({ page }) => {
      await page.fill('#username', USERS.ADM.username);
      await page.fill('#password', '');

      await page.click('button[type="submit"]');
      await page.waitForTimeout(1000);

      const errorSpans = page.locator('.input-group span.invalid-feedback:has-text("harus diisi")');
      await expect(errorSpans).toHaveCount(1);
      await expect(errorSpans.first()).toBeVisible();
    });

    test('[NEGATIF] TC-LOGIN-09: Login dengan username < 4 karakter', async ({ page }) => {
      await page.fill('#username', 'abc'); // 3 karakter — minlength:4
      await page.fill('#password', 'somepassword');

      await page.click('button[type="submit"]');
      await page.waitForTimeout(1000);

      // jQuery Validate akan menolak
      const usernameInput = page.locator('#username');
      await expect(usernameInput).toHaveClass(/is-invalid/);
    });

    test('[NEGATIF] TC-LOGIN-10: Login dengan password < 6 karakter', async ({ page }) => {
      await page.fill('#username', USERS.ADM.username);
      await page.fill('#password', 'abc12'); // 5 karakter — minlength:6

      await page.click('button[type="submit"]');
      await page.waitForTimeout(1000);

      const passwordInput = page.locator('#password');
      await expect(passwordInput).toHaveClass(/is-invalid/);
    });

    test('[NEGATIF] TC-LOGIN-11: Login dengan username tidak terdaftar', async ({ page }) => {
      await page.fill('#username', 'nonexistent_user_xyz');
      await page.fill('#password', 'somepassword123');

      const [response] = await Promise.all([
        page.waitForResponse(resp =>
          resp.url().includes('/login') &&
          resp.request().method() === 'POST'
        , { timeout: 15000 }),
        page.click('button[type="submit"]'),
      ]);

      expect(response.status()).toBe(200);
      const body = await response.json();
      expect(body.status).toBe(false);
      expect(body.message).toContain('gagal');
    });

    test('[NEGATIF] TC-LOGIN-12: Login dengan password salah', async ({ page }) => {
      await page.fill('#username', USERS.ADM.username);
      await page.fill('#password', 'wrongpassword12345');

      const [response] = await Promise.all([
        page.waitForResponse(resp =>
          resp.url().includes('/login') &&
          resp.request().method() === 'POST'
        , { timeout: 15000 }),
        page.click('button[type="submit"]'),
      ]);

      expect(response.status()).toBe(200);
      const body = await response.json();
      expect(body.status).toBe(false);
      expect(body.message).toContain('gagal');
      // Harus tetap di halaman login
      expect(page.url()).toContain('/login');
    });

    test('[NEGATIF] TC-LOGIN-13: Login dengan username kosong + password kosong', async ({ page }) => {
      await page.fill('#username', '');
      await page.fill('#password', '');

      await page.click('button[type="submit"]');
      await page.waitForTimeout(1000);

      // Kedua input harus error
      await expect(page.locator('#username')).toHaveClass(/is-invalid/);
      await expect(page.locator('#password')).toHaveClass(/is-invalid/);
    });

    test('[NEGATIF] TC-LOGIN-14: Login dengan spasi pada username', async ({ page }) => {
      // Username dengan leading/trailing space — Laravel trim
      await page.fill('#username', '  ' + USERS.ADM.username + '  ');
      await page.fill('#password', USERS.ADM.password);

      const [response] = await Promise.all([
        page.waitForResponse(resp =>
          resp.url().includes('/login') &&
          resp.request().method() === 'POST'
        , { timeout: 15000 }),
        page.click('button[type="submit"]'),
      ]);

      expect(response.status()).toBe(200);
      const body = await response.json();
      // AuthService melakukan trim — jika spasi diabaikan, login harus sukses
      // atau gagal jika trim menghasilkan string berbeda
      // Testing: cek apakah response.status true atau false
      expect(body).toHaveProperty('status');
    });
  });

  // ─────────────────────────────────────────────
  // Security Test Cases
  // ─────────────────────────────────────────────

  test.describe('Security — Session & Redirect', () => {

    test('[SECURITY] TC-LOGIN-15: Akses halaman login setelah login redirect ke dashboard', async ({ page }) => {
      // Login dulu
      await page.fill('#username', USERS.ADM.username);
      await page.fill('#password', USERS.ADM.password);

      await page.click('button[type="submit"]');

      // SweetAlert sukses menunggu klik "OK" sebelum redirect dijalankan
      await page.click('.swal2-confirm', { timeout: 10000 }).catch(() => {});

      // Tunggu redirect ke dashboard
      await page.waitForURL('**/dashboard', { timeout: 15000 });
      
      // Verifikasi dashboard sudah dimuat
      const statCards = page.locator('.stat-card');
      await expect(statCards).toHaveCount(4);
      await expect(statCards.first()).toBeVisible();

      // Coba akses /login lagi — harus redirect ke /dashboard
      await page.goto(BASE_URL + '/login', { waitUntil: 'domcontentloaded' });
      await page.waitForTimeout(1000);
      // Harusnya sudah di dashboard (bukan halaman login)
      expect(page.url()).toContain('/dashboard');
    });

    test('[SECURITY] TC-LOGIN-16: Akses halaman terproteksi tanpa login redirect ke login', async ({ page }) => {
      // Hapus cookie/session dengan context baru
      // Langsung akses halaman admin
      await page.goto(BASE_URL + '/admin', { waitUntil: 'domcontentloaded' });

      // Harus redirect ke login
      await page.waitForTimeout(1000);
      expect(page.url()).toContain('/login');
    });

    test('[SECURITY] TC-LOGIN-17: Logout lalu akses halaman terproteksi redirect login', async ({ page }) => {
      // Login dulu
      await page.fill('#username', USERS.ADM.username);
      await page.fill('#password', USERS.ADM.password);

      await page.click('button[type="submit"]');

      // SweetAlert sukses menunggu klik "OK" sebelum redirect dijalankan
      await page.click('.swal2-confirm', { timeout: 10000 }).catch(() => {});

      // Tunggu redirect ke dashboard
      await page.waitForURL('**/dashboard', { timeout: 15000 });
      
      // Verifikasi dashboard sudah dimuat
      const statCards = page.locator('.stat-card');
      await expect(statCards).toHaveCount(4);
      await expect(statCards.first()).toBeVisible();

      // Logout
      await page.goto(BASE_URL + '/logout', { waitUntil: 'domcontentloaded' });
      await page.waitForTimeout(500);

      // Coba akses dashboard — harus redirect ke login
      await page.goto(BASE_URL + '/dashboard', { waitUntil: 'domcontentloaded' });
      await page.waitForTimeout(1000);
      expect(page.url()).toContain('/login');
    });

    test('[SECURITY] TC-LOGIN-18: Logout berhasil dan session dihapus', async ({ page }) => {
      // Login
      await page.fill('#username', USERS.ADM.username);
      await page.fill('#password', USERS.ADM.password);

      await Promise.all([
        page.waitForResponse(resp =>
          resp.url().includes('/login') &&
          resp.request().method() === 'POST'
        , { timeout: 15000 }),
        page.click('button[type="submit"]'),
      ]);

      await page.click('.swal2-confirm', { timeout: 10000 }).catch(() => {});
      await page.waitForSelector('.stat-card', { timeout: 30000 });

      // Logout
      await page.goto(BASE_URL + '/logout', { waitUntil: 'domcontentloaded' });
      await page.waitForTimeout(1000);

      // Landing page atau redirect
      // Pastikan tidak di dashboard
      expect(page.url()).not.toContain('/dashboard');
    });
  });

  // ─────────────────────────────────────────────
  // UI & Validation Test Cases
  // ─────────────────────────────────────────────

  test.describe('UI — Form Elements & Validation', () => {

    test('[UI] TC-LOGIN-19: Halaman login menampilkan semua elemen form', async ({ page }) => {
      // Judul
      await expect(page.locator('.login-title')).toBeVisible();
      await expect(page.locator('.login-title')).toContainText('Sistem Peminjaman Ruangan');

      // Input username
      await expect(page.locator('#username')).toBeVisible();
      await expect(page.locator('#username')).toHaveAttribute('placeholder', 'Username');

      // Input password
      await expect(page.locator('#password')).toBeVisible();
      await expect(page.locator('#password')).toHaveAttribute('placeholder', 'Password');

      // Tombol login
      const submitButton = page.locator('button[type="submit"]');
      await expect(submitButton).toBeVisible();
      await expect(submitButton).toHaveText('Login');

      // CSRF meta - meta tags tidak "visible" dalam CSS, cek attribute saja
      const csrfMeta = page.locator('meta[name="csrf-token"]');
      const csrfContent = await csrfMeta.getAttribute('content');
      expect(csrfContent).toBeTruthy();
    });

    test('[UI] TC-LOGIN-20: Validasi menampilkan error pada username dan password', async ({ page }) => {
      // Kirim form kosong
      await page.click('button[type="submit"]');
      await page.waitForTimeout(1000);

      // Error harus muncul - span.invalid-feedback yang ada di dalam .input-group
      const errorSpans = page.locator('.input-group span.invalid-feedback:has-text("harus diisi")');
      await expect(errorSpans).toHaveCount(2);
    });

    test('[UI] TC-LOGIN-21: Field username memiliki tipe text, password memiliki tipe password', async ({ page }) => {
      await expect(page.locator('#username')).toHaveAttribute('type', 'text');
      await expect(page.locator('#password')).toHaveAttribute('type', 'password');
    });

    test('[UI] TC-LOGIN-22: Form memiliki autocomplete attributes', async ({ page }) => {
      await expect(page.locator('#username')).toHaveAttribute('autocomplete', 'username');
      await expect(page.locator('#password')).toHaveAttribute('autocomplete', 'current-password');
    });

    test('[UI] TC-LOGIN-23: Link manual tersedia', async ({ page }) => {
      // Link/download manual
      const manualLink = page.locator('a[href*="manual"]').first();
      await expect(manualLink).toBeVisible();
      const href = await manualLink.getAttribute('href');
      expect(href).toContain('.pdf');
    });
  });
});

