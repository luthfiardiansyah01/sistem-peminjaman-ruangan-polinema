/**
 * Helpers for Playwright E2E tests.
 *
 * FIX: `ajaxGet` — menggunakan jQuery $.ajax GET (bukan page.goto()) untuk
 * endpoint *_ajax yang hanya merender partial view saat $request->ajax() true.
 * page.goto() sebagai full navigation tidak mengirim header X-Requested-With,
 * sehingga selalu redirect ke halaman lain (dashboard/index).
 */

/**
 * Eksekusi GET request via jQuery $.ajax (mengirim header X-Requested-With).
 * Cocok untuk endpoint show_ajax, timeline_ajax, dll. yang hanya merender
 * partial view saat AJAX request.
 *
 * @param {import('@playwright/test').Page} page
 * @param {string} url - Endpoint URL (full path, e.g., /pengajuan/1/show_ajax)
 * @returns {Promise<{status: number, body: string}>}
 */
async function ajaxGet(page, url) {
  const hasJquery = await page.evaluate(() => {
    return typeof window.$ !== 'undefined' && typeof window.$.ajax !== 'undefined';
  }).catch(() => false);

  if (!hasJquery) {
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {});
    await page.waitForTimeout(500);
  }

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

/**
 * Cari ID pengajuan berdasarkan nama, menggunakan ajaxGet (bukan page.goto()).
 * Scan ID dari maxId ke bawah untuk efisiensi.
 *
 * @param {import('@playwright/test').Page} page
 * @param {string} nama - Nama pengajuan yang dicari
 * @param {number} [maxId=100] - ID maksimum yang akan di-scan
 * @param {number} [windowSize=50] - Jumlah ID yang di-scan
 * @returns {Promise<number|null>}
 */
async function findPengajuanId(page, nama, maxId = 100, windowSize = 50) {
  // Cari max ID yang valid via exponential probe
  const floor = Math.max(1, maxId - windowSize + 1);
  for (let id = maxId; id >= floor; id--) {
    const resp = await ajaxGet(page, `/pengajuan/${id}/show_ajax`);
    if (resp.status === 200 && resp.body && resp.body.includes(nama)) {
      return id;
    }
  }
  return null;
}

/**
 * Generate unique name for testing
 */
let _counter = Date.now();
function uniqueName(prefix) {
  _counter++;
  return `${prefix}-${_counter}`;
}

export { ajaxGet, findPengajuanId, uniqueName };
