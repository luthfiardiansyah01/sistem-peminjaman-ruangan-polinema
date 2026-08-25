# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: security.spec.js >> Security Tests >> 419 - CSRF / Page Expired >> should include CSRF token in page
- Location: modules\security.spec.js:125:5

# Error details

```
Error: expect(received).toBeGreaterThan(expected)

Expected: > 0
Received:   0
```

# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - navigation [ref=e2]:
    - generic [ref=e3]:
      - img "Logo JTI" [ref=e4]
      - text: Sistem Peminjaman Ruangan JTI
    - link " Login" [ref=e5] [cursor=pointer]:
      - /url: http://127.0.0.1:8000/login
      - generic [ref=e6]: 
      - text: Login
  - generic [ref=e7]:
    - heading "Statistik Publik" [level=3] [ref=e8]
    - generic [ref=e9]:
      - generic [ref=e12]:
        - heading "11" [level=3] [ref=e13]
        - paragraph [ref=e14]: Total Ruangan
        - generic [ref=e16]: 
      - generic [ref=e19]:
        - heading "11" [level=3] [ref=e20]
        - paragraph [ref=e21]: Total Ruangan Kosong
        - generic [ref=e23]: 
      - generic [ref=e26]:
        - heading "0" [level=3] [ref=e27]
        - paragraph [ref=e28]: Jadwal Hari Ini
        - generic [ref=e30]: 
    - generic [ref=e31]:
      - heading "Top 5 Ruangan Terfavorit" [level=3] [ref=e35]
      - heading "Distribusi Peminjam" [level=3] [ref=e42]
    - heading "Tren Peminjaman (6 Bulan)" [level=3] [ref=e49]
```

# Test source

```ts
  30  |     await page.waitForTimeout(2000);
  31  |   } catch (e) {
  32  |     console.log(`Login error for ${username}: ${e.message}`);
  33  |   }
  34  | }
  35  | 
  36  | // Logout helper
  37  | async function logout(page) {
  38  |   try {
  39  |     await page.goto(`${BASE_URL}/logout`, { timeout: 5000 });
  40  |     await page.waitForTimeout(1000);
  41  |   } catch (e) {
  42  |     // Ignore logout errors
  43  |   }
  44  | }
  45  | 
  46  | test.describe('Security Tests', () => {
  47  |   
  48  |   test.describe('401 - Unauthorized Access', () => {
  49  |     test('should redirect to login when accessing protected route', async ({ page }) => {
  50  |       await page.goto(`${BASE_URL}/dashboard`);
  51  |       await expect(page).toHaveURL(/.*login/);
  52  |     });
  53  | 
  54  |     test('should show error for invalid credentials', async ({ page }) => {
  55  |       await page.goto(`${BASE_URL}/login`);
  56  |       await page.fill('input[name="username"]', 'nonexistent_user_xyz_abc');
  57  |       await page.fill('input[name="password"]', 'wrongpassword123');
  58  |       await page.click('button[type="submit"]').catch(async () => {
  59  |         await page.keyboard.press('Enter');
  60  |       });
  61  |       await page.waitForTimeout(2000);
  62  |       const content = await page.content();
  63  |       expect(content).toMatch(/Invalid|Username|Password|error| Salah|Login|gagal/i);
  64  |     });
  65  |   });
  66  | 
  67  |   test.describe('403 - Forbidden / Permission', () => {
  68  |     test('should deny mahasiswa access to admin routes', async ({ page }) => {
  69  |       await loginAs(page, 'mahasiswa1', '2241760001');
  70  |       await page.goto(`${BASE_URL}/admin`);
  71  |       await page.waitForTimeout(3000);
  72  |       const body = await page.locator('body').textContent();
  73  |       // Either should show 403 OR still be on login page (failed login)
  74  |       const hasAccess = body.match(/403|Forbidden|Unauthorized|Tidak diizinkan|Level|Insufficient/);
  75  |       const stillLoggedOut = body.match(/login|username|password|Masukkan username/i);
  76  |       expect(hasAccess || stillLoggedOut).toBeTruthy();
  77  |       await logout(page);
  78  |     });
  79  | 
  80  |     test('should deny dosen access to admin management', async ({ page }) => {
  81  |       await loginAs(page, 'dosen', '1111111111');
  82  |       await page.goto(`${BASE_URL}/admin`);
  83  |       await page.waitForTimeout(2000);
  84  |       const body = await page.locator('body').textContent();
  85  |       expect(body).toMatch(/403|Forbidden|Unauthorized|Tidak diizinkan|Level|Insufficient/);
  86  |       await logout(page);
  87  |     });
  88  | 
  89  |     test('should allow admin access to admin dashboard', async ({ page }) => {
  90  |       await loginAs(page, 'admin1', '0000000000');
  91  |       await page.goto(`${BASE_URL}/admin`);
  92  |       await page.waitForTimeout(2000);
  93  |       const url = page.url();
  94  |       expect(url).toContain('/admin');
  95  |       await logout(page);
  96  |     });
  97  |   });
  98  | 
  99  |   test.describe('404 - Not Found', () => {
  100 |     test('should return 404 for non-existent route', async ({ page }) => {
  101 |       await page.goto(`${BASE_URL}/nonexistent-route-xyz-123-abc`);
  102 |       await page.waitForTimeout(1000);
  103 |       const body = await page.locator('body').textContent();
  104 |       expect(body).toMatch(/404|Not Found|Halaman tidak ditemukan/);
  105 |     });
  106 |   });
  107 | 
  108 |   test.describe('405 - Method Not Allowed', () => {
  109 |     test('should handle wrong HTTP method', async ({ request }) => {
  110 |       const response = await request.get(`${BASE_URL}/admin/ajax`);
  111 |       expect([405, 302, 404]).toContain(response.status());
  112 |     });
  113 |   });
  114 | 
  115 |   test.describe('419 - CSRF / Page Expired', () => {
  116 |     test('should fail with expired session', async ({ page }) => {
  117 |       await loginAs(page, 'admin1', '0000000000');
  118 |       await page.context().clearCookies();
  119 |       await page.goto(`${BASE_URL}/admin/create_ajax`);
  120 |       await page.waitForTimeout(1500);
  121 |       const content = await page.content();
  122 |       expect(content).toMatch(/login|419|expired|CSRF|Session|token/i) || expect(page.url()).toMatch(/login/);
  123 |     });
  124 | 
  125 |     test('should include CSRF token in page', async ({ page }) => {
  126 |       await loginAs(page, 'admin1', '0000000000');
  127 |       await page.goto(`${BASE_URL}/admin/create_ajax`);
  128 |       const csrfInput = await page.locator('input[name="_token"]').count();
  129 |       const csrfMeta = await page.locator('meta[name="csrf-token"]').count();
> 130 |       expect(csrfInput + csrfMeta).toBeGreaterThan(0);
      |                                    ^ Error: expect(received).toBeGreaterThan(expected)
  131 |       await logout(page);
  132 |     });
  133 |   });
  134 | 
  135 |   test.describe('422 - Validation Error', () => {
  136 |     test('should show validation errors for empty fields', async ({ page }) => {
  137 |       // Login first
  138 |       await page.goto(`${BASE_URL}/login`);
  139 |       await page.fill('input[name="username"]', 'admin1');
  140 |       await page.fill('input[name="password"]', '0000000000');
  141 |       await page.click('button[type="submit"]').catch(() => {});
  142 |       await page.waitForTimeout(3000);
  143 |       
  144 |       // If still on login page, skip test
  145 |       const currentUrl = page.url();
  146 |       if (currentUrl.includes('login')) {
  147 |         console.log('Skipping test - admin user not found');
  148 |         expect(true).toBe(true);
  149 |         return;
  150 |       }
  151 |       
  152 |       // Try to access create page
  153 |       const response = await page.request.get(`${BASE_URL}/admin/create_ajax`);
  154 |       expect([200, 302, 403]).toContain(response.status());
  155 |     });
  156 |   });
  157 | 
  158 |   test.describe('Permission & Ownership', () => {
  159 |     test('should enforce role-based access control', async ({ page }) => {
  160 |       await loginAs(page, 'mahasiswa1', '2241760001');
  161 |       const allowedRoutes = ['/jadwal', '/ruangan'];
  162 |       for (const route of allowedRoutes) {
  163 |         await page.goto(`${BASE_URL}${route}`);
  164 |         await page.waitForTimeout(1000);
  165 |         const url = page.url();
  166 |         expect(url).not.toContain('/403');
  167 |       }
  168 |       await logout(page);
  169 |     });
  170 |   });
  171 | 
  172 |   test.describe('Session Security', () => {
  173 |     test('should expire session after logout', async ({ page }) => {
  174 |       await loginAs(page, 'admin1', '0000000000');
  175 |       await page.waitForTimeout(1000);
  176 |       
  177 |       // Try to find and click logout button
  178 |       const logoutLink = page.locator('a:has-text("Logout"), a:has-text("Logout"), [href*="logout"]');
  179 |       if (await logoutLink.count() > 0) {
  180 |         await logoutLink.click().catch(() => {});
  181 |       } else {
  182 |         await page.goto(`${BASE_URL}/logout`).catch(() => {});
  183 |       }
  184 |       
  185 |       await page.waitForTimeout(1500);
  186 |       await page.goto(`${BASE_URL}/dashboard`);
  187 |       await expect(page).toHaveURL(/.*login/);
  188 |     });
  189 | 
  190 |     test('should maintain session for authenticated requests', async ({ page }) => {
  191 |       await loginAs(page, 'admin1', '0000000000');
  192 |       await page.goto(`${BASE_URL}/admin`);
  193 |       await page.waitForTimeout(1000);
  194 |       expect(page.url()).toContain('/admin');
  195 |       
  196 |       await page.goto(`${BASE_URL}/dashboard`);
  197 |       await page.waitForTimeout(1000);
  198 |       expect(page.url()).toContain('/dashboard');
  199 |       await logout(page);
  200 |     });
  201 |   });
  202 | 
  203 |   test.describe('Security Additional', () => {
  204 |     test('should have session cookie', async ({ page }) => {
  205 |       await loginAs(page, 'admin1', '0000000000');
  206 |       const cookies = await page.context().cookies();
  207 |       const sessionCookie = cookies.find(c => c.name === 'laravel_session');
  208 |       expect(sessionCookie).toBeDefined();
  209 |       await logout(page);
  210 |     });
  211 | 
  212 |     test('should prevent SQL injection in login', async ({ page }) => {
  213 |       await page.goto(`${BASE_URL}/login`);
  214 |       await page.fill('input[name="username"]', "' OR '1'='1");
  215 |       await page.fill('input[name="password"]', "' OR '1'='1");
  216 |       await page.click('button[type="submit"]').catch(async () => {
  217 |         await page.keyboard.press('Enter');
  218 |       });
  219 |       await page.waitForTimeout(2000);
  220 |       expect(page.url()).not.toContain('/dashboard');
  221 |     });
  222 | 
  223 |     test('should escape XSS in output', async ({ page }) => {
  224 |       await page.goto(`${BASE_URL}/login`);
  225 |       await page.fill('input[name="username"]', '<script>alert(1)</script>');
  226 |       await page.fill('input[name="password"]', 'test123');
  227 |       await page.click('button[type="submit"]').catch(async () => {
  228 |         await page.keyboard.press('Enter');
  229 |       });
  230 |       await page.waitForTimeout(1500);
```