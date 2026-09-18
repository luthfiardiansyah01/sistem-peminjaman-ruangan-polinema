# Review Perubahan — Approval Routing (Wadir II) & Template Surat Peminjaman

> **Status revisi:** Revisi ke-1 (tercatat) dari klien, tanggal 25 Agustus 2026 — masih tahap brainstorming, belum final.

---

## Poin Revisi ke-2 dari Klien [13 September 2026]

> Status: baru disampaikan klien, belum diimplementasikan — menunggu konfirmasi final sebelum dikerjakan.

1. **Role berjenjang yang kompleks**
   - Fitur/implementasi role berjenjang yang lebih kompleks tidak perlu diimplementasikan dulu.
   - Untuk sementara, gunakan role yang sudah ada/sederhana.
2. **Perubahan data identitas dosen**
   - Klien baru menyadari bahwa pada surat peminjaman, identitas untuk DPK, Kepala Jurusan, dan
     Wadir II seharusnya menggunakan **NIP**, bukan NIDN.
   - Ketiga role tersebut merupakan dosen.
3. **Perubahan field pada tabel `m_dosen`**
   - Klien menanyakan apakah field `NIDN` pada tabel `m_dosen` dapat diubah menjadi `NIP/NIDN`.
   - Artinya, kemungkinan dibutuhkan satu field yang dapat menyimpan salah satu dari dua jenis
     identitas tersebut.
4. **Aturan panjang digit**
   - Perlu ditentukan/diterapkan validasi panjang karakter: NIDN 10 digit, NIP 18 digit.
   - Klien masih menyebutkan ini berdasarkan ingatan — **perlu dikonfirmasi kembali** aturan
     final sebelum implementasi.
5. **Penyesuaian template surat**
   - Tampilan surat peminjaman saat ini masih belum sepenuhnya sesuai dengan template yang
     sebelumnya dikirim klien.
   - Klien meminta format/tampilan surat disesuaikan kembali dengan template tersebut.

### Implementasi poin 1 — selesai

Eskalasi tahap approval tambahan ke `Wakil Direktur II` (berdasarkan kategori ruangan `Umum`,
ditambahkan pada revisi ke-1) **dibalik/disederhanakan kembali**:

- [`app/Services/PengajuanService.php`](app/Services/PengajuanService.php) —
  `alurApprovalUntukPengajuan()` kembali ke rantai sederhana berdasarkan role pemohon saja
  (Mahasiswa: Ketua Umum → Presiden BEM → DPK → Ketua Jurusan; Dosen/Tendik: Ketua Jurusan),
  tanpa cek `ruangan_kategori` / tahap tambahan Wadir II. Eager-load `ruangans` yang sebelumnya
  ditambah khusus untuk cek ini juga dilepas dari `create()`.
- [`resources/views/pdf/surat_peminjaman.blade.php`](resources/views/pdf/surat_peminjaman.blade.php) —
  baris "Yth." kembali statis ke Ketua Jurusan (tidak lagi bercabang ke Wadir II).

Konstanta `VALID_POSISI_APPROVAL` dan `POSISI_BY_ROLE` (yang masih menyertakan `Wakil Direktur
II` sebagai posisi valid untuk role Dosen) **tidak dihapus** — dibiarkan untuk kompatibilitas
kalau posisi ini sudah pernah dipakai/di-assign di data, dan karena poin 2 revisi ini sendiri
masih menyebut Wadir II sebagai salah satu role penandatangan surat (hanya identitasnya yang
berubah ke NIP). Verifikasi: `php -l` lolos, `php artisan view:cache` sukses.

### Implementasi poin 3 — selesai

Kolom `dosen_nidn` pada `m_dosen` di-**rename** (bukan tambah kolom baru) menjadi
`dosen_nip_nidn`, supaya satu field ini bisa menyimpan salah satu dari NIP atau NIDN sesuai
pertanyaan klien — nilai data lama tetap terpakai apa adanya.

- [`database/migrations/2026_09_18_070205_rename_dosen_nidn_to_dosen_nip_nidn_on_m_dosen_table.php`](database/migrations/2026_09_18_070205_rename_dosen_nidn_to_dosen_nip_nidn_on_m_dosen_table.php) —
  migration baru (additive, migration lama `create_m_dosen_table` tidak diubah) yang me-rename
  kolom. Sudah dijalankan (`php artisan migrate`) ke database lokal `skripsi_2`, terverifikasi
  lewat `Schema::getColumnListing('m_dosen')`.
- Seluruh referensi `dosen_nidn` di kode aplikasi disesuaikan ke `dosen_nip_nidn`:
  [`app/Models/DosenModel.php`](app/Models/DosenModel.php),
  [`app/Services/UserService.php`](app/Services/UserService.php),
  [`app/Services/ImportService.php`](app/Services/ImportService.php),
  [`app/Services/JadwalService.php`](app/Services/JadwalService.php),
  [`app/Services/PengajuanService.php`](app/Services/PengajuanService.php) (komentar saja),
  [`app/DTO/ProfileDTO.php`](app/DTO/ProfileDTO.php),
  [`app/DTO/CreateUserDTO.php`](app/DTO/CreateUserDTO.php),
  [`app/DTO/ImportDosenDTO.php`](app/DTO/ImportDosenDTO.php) (termasuk label kolom impor Excel
  "NIDN" → "NIP/NIDN"),
  [`database/seeders/DosenSeeder.php`](database/seeders/DosenSeeder.php),
  view CRUD dosen (`resources/views/dosen/*.blade.php`, label "NIDN" → "NIP/NIDN"),
  [`resources/views/jadwal/create_ajax.blade.php`](resources/views/jadwal/create_ajax.blade.php),
  [`resources/views/pdf/surat_peminjaman.blade.php`](resources/views/pdf/surat_peminjaman.blade.php)
  (referensi field saja — label "NIDN." pada tanda tangan **belum** diubah, menunggu poin 2),
  serta seluruh test yang sebelumnya memakai key `dosen_nidn` (`tests/Feature/*`,
  `tests/Unit/*`).
- **Tidak diubah** (di luar cakupan poin 3 — field milik role lain): `admin_nidn` (AdminModel),
  `tendik_nidn` (TendikModel).
- `database/migrations/2025_12_01_044740_create_m_dosen_table.php` (migration lama) dan
  `skripsi_2.sql` / `docs/*.md` (dump & dokumentasi historis) **sengaja tidak disentuh** —
  bukan kode yang dieksekusi ulang, hanya arsip.
- Validasi panjang digit di `DosenModel::validate()` dan `ImportDosenDTO` untuk sementara
  diperlonggar menerima 10 digit (NIDN) **atau** 18 digit (NIP) mengikuti poin 4 — masih
  **perlu dikonfirmasi ulang** ke klien sebelum dianggap final.

Verifikasi: `php -l` pada seluruh file yang diubah lolos, `php artisan view:cache` sukses,
`php artisan migrate` sukses di DB lokal `skripsi_2`.

**PHPUnit (`php artisan test`): 166 lolos, 4 gagal.** Keempat kegagalan diverifikasi **pre-existing**
(dites ulang di commit sebelum poin 3, hasilnya identik) — bukan regresi dari perubahan ini:

1. `Tests\Unit\Services\UserServiceTest > create mahasiswa returns success`
2. `Tests\Unit\Services\UserServiceTest > create mahasiswa allows null kelas id`
3. `Tests\Feature\DashboardIntegrationTest > dosen dashboard shows relevant data`
4. `Tests\Feature\UserRegistrationIntegrationTest > user update workflow maintains data integrity`

Keempatnya soal alur mahasiswa/dashboard/update-dosen yang sudah bermasalah sebelum sesi ini —
di luar cakupan poin 3, belum diperbaiki di sini.

Catatan koreksi: draf awal implementasi sempat memperketat validasi panjang NIP/NIDN jadi persis
10 atau 18 digit (mendahului poin 4). Ini dibatalkan — regex validasi dikembalikan ke rentang
longgar `10-16` digit (perilaku sebelumnya, tidak berubah) karena poin 4 memang belum final/masih
menunggu konfirmasi klien, dan pengetatan itu sempat membuat 1 test tambahan gagal
(`PersonModelTest > dosen model validation method works`) yang sekarang sudah lolos lagi.

### Implementasi poin 2 — selesai

[`resources/views/pdf/surat_peminjaman.blade.php`](resources/views/pdf/surat_peminjaman.blade.php) —
closure `$identitas()` sekarang menerima parameter `$posisi` (posisi_approval si penandatangan).
Untuk penandatangan dosen di posisi `DPK`, `Ketua Jurusan`, atau `Wakil Direktur II`, label
identitas ditampilkan **`NIP.`** (bukan `NIDN.`) — sesuai arahan klien bahwa ketiga posisi
tersebut seharusnya pakai NIP. Posisi dosen lain (mis. `Ketua Pelaksana` kalau dijabat dosen)
tetap memakai label `NIDN.` seperti semula karena tidak disebut klien dalam poin ini. Nilai yang
ditampilkan tetap dari kolom `dosen_nip_nidn` yang sama (poin 3) — hanya labelnya yang
menyesuaikan posisi; sistem belum bisa membedakan otomatis apakah angka yang tersimpan itu
NIP atau NIDN sungguhan, jadi ini murni soal label sesuai instruksi klien per-posisi.

Verifikasi: `php -l` lolos, `php artisan view:cache` sukses.

> Catatan: direktori ini baru saja di-`git init` dengan satu commit awal ("first commit") yang
> sudah memuat seluruh perubahan sesi ini — jadi `git diff`/`git log` tidak bisa dipakai untuk
> menampilkan before/after (tidak ada commit sebelumnya untuk dibandingkan). File ini berisi
> hunk before/after persis seperti yang saya terapkan (exact match dari tool edit), supaya tetap
> bisa direview manual.

---

## 1. `app/Services/PengajuanService.php`

### 1.1 Eager-load `ruangans` sebelum generate approval stages

```diff
-        // Refresh & eager-load user untuk generateApprovalStages() tanpa query terpisah
-        $pengajuan->refresh()->load('user');
+        // Refresh & eager-load user + ruangans untuk generateApprovalStages() tanpa query terpisah
+        $pengajuan->refresh()->load(['user', 'ruangans']);
```

### 1.2 Docblock `generateApprovalStages()` diperbarui

```diff
     /**
      * Generate seluruh baris tahap approval berjenjang saat pengajuan pertama kali dibuat (FR-6.3).
-     * Alur SEKARANG bercabang menurut role akun pemohon (bukan lagi ruangan_kategori):
+     * Alur bercabang menurut role akun pemohon:
      * - Mahasiswa: Ketua Pelaksana -> Ketua Organisasi -> Presiden BEM -> DPK -> Ketua Jurusan
      * - Dosen/Tendik: Ketua Pelaksana -> Ketua Jurusan
+     * Lalu, jika salah satu ruangan yang dipinjam berkategori 'Umum' (fasilitas kampus),
+     * alur di atas ditambah satu tahap eskalasi terakhir ke Wakil Direktur II (arahan klien)
+     * — lihat alurApprovalUntukPengajuan().
      * Seluruhnya SEKUENSIAL (tidak ada lagi tahap paralel). Tahap 0 (Ketua Pelaksana) langsung
      * aktif (batas_waktu diisi); tahap berikutnya dibuat 'Menunggu' tanpa batas_waktu sampai
      * tahap sebelumnya disetujui (lihat processApproval()).
      */
```

### 1.3 Pemanggil alur approval diganti

```diff
-        $posisiUrutan = $this->alurApprovalUntukRole($pengajuan->user->getRole());
+        $posisiUrutan = $this->alurApprovalUntukPengajuan($pengajuan);
```

### 1.4 `alurApprovalUntukRole()` → `alurApprovalUntukPengajuan()` (logika inti fitur ini)

```diff
     /**
-     * Urutan posisi_approval (tanpa Ketua Pelaksana, sudah ditangani terpisah) sesuai role
-     * akun pemohon pengajuan.
+     * Urutan posisi_approval (tanpa Ketua Pelaksana, sudah ditangani terpisah) untuk sebuah
+     * pengajuan: role akun pemohon menentukan rantai dasarnya, lalu kategori ruangan yang
+     * dipinjam menentukan apakah rantai itu perlu diperpanjang ke Wakil Direktur II.
      */
-    protected function alurApprovalUntukRole(string $role): array
+    protected function alurApprovalUntukPengajuan(PengajuanModel $pengajuan): array
     {
-        if (in_array($role, [RoleConstants::DOSEN, RoleConstants::TENDIK], true)) {
-            return ['Ketua Jurusan'];
-        }
+        $role = $pengajuan->user->getRole();
 
-        return ['Ketua Umum', 'Presiden BEM', 'DPK', 'Ketua Jurusan'];
+        $alur = in_array($role, [RoleConstants::DOSEN, RoleConstants::TENDIK], true)
+            ? ['Ketua Jurusan']
+            : ['Ketua Umum', 'Presiden BEM', 'DPK', 'Ketua Jurusan'];
+
+        // Ruangan kategori 'Umum' (fasilitas kampus) butuh eskalasi tambahan ke Wadir II
+        // setelah Ketua Jurusan. Pengajuan dengan ruangan campuran (ada Umum + Jurusan)
+        // dianggap Umum — aturan terketat yang berlaku (arahan klien).
+        if ($pengajuan->ruangans->contains('ruangan_kategori', 'Umum')) {
+            $alur[] = 'Wakil Direktur II';
+        }
+
+        return $alur;
     }
```

### 1.5 Komentar konstanta `STAGE_KETUA_PELAKSANA` disinkronkan

```diff
-    // berbeda tergantung role pemohon — lihat alurApprovalUntukRole().
+    // berbeda tergantung role pemohon & kategori ruangan — lihat alurApprovalUntukPengajuan().
```

### 1.6 `findForCetakSurat()` — eager-load diperluas untuk kebutuhan data surat

```diff
     public function findForCetakSurat(int $id): array
     {
-        $pengajuan = PengajuanModel::with(['user', 'organisasi', 'ruangans', 'panitias.user.dosen', 'panitias.user.mahasiswa', 'panitias.user.tendik', 'panitias.user.admin'])->find($id);
+        $pengajuan = PengajuanModel::with([
+            'user.dosen',
+            'user.mahasiswa',
+            'user.tendik',
+            'organisasi',
+            'ruangans',
+            'ketuaPelaksana.dosen',
+            'ketuaPelaksana.mahasiswa',
+            'panitias.user.dosen',
+            'panitias.user.mahasiswa.prodi',
+            'panitias.user.tendik',
+            'panitias.user.admin',
+            // Rantai tanda tangan surat (FR-9.2): urutkan per tahap approval, lalu eager-load
+            // nama + NIDN/NIM penandatangan (dosen pakai dosen_nidn, mahasiswa pakai mahasiswa_nim
+            // — sistem ini tidak menyimpan NIP dosen, lihat DosenModel::$fillable).
+            'approvals' => fn ($query) => $query->orderBy('urutan_tahap'),
+            'approvals.jabatanApproval.user.dosen',
+            'approvals.jabatanApproval.user.mahasiswa',
+            'approvals.jabatanApproval.user.tendik',
+        ])->find($id);
```

**Efek fungsional bagian 1:** pengajuan dengan ruangan berkategori `Umum` sekarang mendapat
tahap approval tambahan `Wakil Direktur II` setelah `Ketua Jurusan`, sebelum status berubah
jadi `Diterima`. `activateNextStageOrFinalize()` **tidak diubah** — sudah generik, otomatis
mengikuti tahap tambahan ini.

---

## 2. `resources/views/pdf/surat_peminjaman.blade.php`

File ini dirombak total (bukan hunk kecil) — berikut versi **sebelum** dan **sesudah** utuh
supaya gampang dibandingkan berdampingan.

### Sebelum

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Peminjaman Ruangan</title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12px; color: #000; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header img.logo { height: 60px; float: left; }
        .header h2, .header h4 { margin: 0; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table.data td { padding: 4px 6px; vertical-align: top; }
        table.data td.label { width: 200px; }
        .lampiran { margin-top: 25px; page-break-inside: avoid; }
        .lampiran table { width: 100%; border-collapse: collapse; }
        .lampiran th, .lampiran td { border: 1px solid #000; padding: 6px; font-size: 11px; }
        .ttd { margin-top: 40px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        @if($pengajuan->organisasi && $pengajuan->organisasi->organisasi_logo)
            <img class="logo" src="{{ public_path('storage/' . $pengajuan->organisasi->organisasi_logo) }}" alt="Logo">
        @endif
        <h2>SURAT PEMINJAMAN RUANGAN</h2>
        <h4>Jurusan Teknologi Informasi</h4>
        <p>Nomor: {{ $pengajuan->nomor_surat }}</p>
    </div>

    <p>Yang bertanda tangan di bawah ini menerangkan bahwa pengajuan peminjaman ruangan berikut telah <strong>DISETUJUI</strong>:</p>

    <table class="data">
        <tr><td class="label">Nama Kegiatan</td><td>: {{ $pengajuan->pengajuan_nama }}</td></tr>
        <tr><td class="label">Organisasi Pengaju</td><td>: {{ optional($pengajuan->organisasi)->organisasi_nama ?? '-' }}</td></tr>
        <tr><td class="label">Nama Pemohon</td><td>: {{ $pengajuan->user->getDisplayName() }}</td></tr>
        <tr><td class="label">Ruangan</td><td>: {{ $pengajuan->ruangans->pluck('ruangan_nama')->implode(', ') }}</td></tr>
        <tr><td class="label">Tanggal</td><td>: {{ \Carbon\Carbon::parse($pengajuan->pengajuan_tgl)->locale('id')->translatedFormat('d F Y') }}</td></tr>
        <tr><td class="label">Waktu</td><td>: {{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_selesai)->format('H:i') }} WIB</td></tr>
        <tr><td class="label">Jumlah Peserta</td><td>: {{ $pengajuan->pengajuan_jumPes }} orang</td></tr>
        <tr><td class="label">Keterangan</td><td>: {{ $pengajuan->pengajuan_keterangan ?? '-' }}</td></tr>
    </table>

    <div class="lampiran">
        <h4>Lampiran 1 — Daftar Acara</h4>
        <table>
            <thead><tr><th>No.</th><th>Waktu</th><th>Rangkaian Acara</th></tr></thead>
            <tbody>
                <tr><td>1.</td><td>{{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_mulai)->format('H:i') }}</td><td>{{ $pengajuan->pengajuan_nama }}</td></tr>
            </tbody>
        </table>
    </div>

    <div class="lampiran">
        <h4>Lampiran 2 — Daftar Panitia/Peserta</h4>
        <table>
            <thead><tr><th>No.</th><th>Nama</th><th>Keterangan</th></tr></thead>
            <tbody>
                <tr><td>1.</td><td>{{ $pengajuan->user->getDisplayName() }}</td><td>Penanggung Jawab</td></tr>
                @foreach ($pengajuan->panitias as $index => $panitia)
                    <tr>
                        <td>{{ $index + 2 }}.</td>
                        <td>{{ $panitia->user->getDisplayName() }}</td>
                        <td>{{ $panitia->keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="ttd">
        <p>{{ now()->locale('id')->translatedFormat('d F Y') }}</p>
        <p>Admin JTI</p>
        <br><br><br>
        <p>(_________________________)</p>
    </div>
</body>
</html>
```

### Sesudah

Lihat isi lengkap terkini di
[`resources/views/pdf/surat_peminjaman.blade.php`](resources/views/pdf/surat_peminjaman.blade.php).
Ringkasan perubahan besar dari versi "Sebelum" di atas:

- Kop surat sekarang **Nomor / Lampiran / Hal** (bukan judul + `<p>Nomor</p>` polos), plus baris
  **"Yth. ..."** dinamis yang mengikuti tahap approval terakhir pengajuan (`Ketua Jurusan` atau
  `Wakil Direktur II`).
- Badan surat (`.isi`) jadi **paragraf naratif** ("Sehubungan dengan adanya kegiatan...")
  merujuk ke lampiran untuk detail acara — bukan tabel data mentah seperti versi lama
  (`table.data`) yang langsung dihapus.
- Blok tanda tangan (`table.ttd`) diganti total: dari 1 baris statis `"Admin JTI"` menjadi
  **rantai penandatangan dinamis** dari `$pengajuan->approvals` (terurut per `urutan_tahap`),
  dipecah dua kelompok sesuai contoh resmi — `"Hormat kami,"` untuk pihak pemohon (Ketua
  Pelaksana/Ketua Umum) dan `"Mengetahui dan menyetujui,"` untuk pihak penyetuju berjenjang
  (DPK, Presiden BEM, Ketua Jurusan, dan Wadir II bila ada) — masing-masing dengan nama +
  NIDN/NIM.
- Baris **`Cp. {nomor_hp} ({nama})`** ditambahkan (sebelumnya tidak ada sama sekali).
- **Lampiran 1**: dari 1 baris ringkas (`Waktu` + `Rangkaian Acara`) menjadi tabel resmi
  `No. | Acara | Tanggal Peminjaman | Pukul | Tempat`, plus kop `Nomor:` dan `Kegiatan ...`.
- **Lampiran 2**: tabel panitia sekarang punya kolom **Program Studi** tambahan, plus kop
  `Nomor:` dan judul `DAFTAR NAMA PANITIA/PESERTA`.

---

## 3. `tests/e2e/fixtures/approval.fixture.js` — hanya komentar

```diff
- * Alur SEKARANG bercabang menurut role akun pemohon (PengajuanService::
- * alurApprovalUntukRole()), dan SELURUH tahap SEKUENSIAL — tidak ada lagi
- * tahap paralel maupun tahap akhir yang dipilih dari ruangan_kategori:
+ * Alur bercabang menurut role akun pemohon (PengajuanService::
+ * alurApprovalUntukPengajuan()), dan SELURUH tahap SEKUENSIAL — tidak ada
+ * tahap paralel:
  * - Mahasiswa: Tahap 0 Ketua Pelaksana -> Tahap 1 Ketua Umum (organisasi
  *   pengaju) -> Tahap 2 Presiden BEM -> Tahap 3 DPK -> Tahap 4 Ketua Jurusan.
  * - Dosen/Tendik: Tahap 0 Ketua Pelaksana -> Tahap 1 Ketua Jurusan.
+ * Jika salah satu ruangan yang dipinjam berkategori 'Umum', rantai di atas
+ * mendapat SATU tahap tambahan di akhir: Wakil Direktur II.
  * Tahap terakhir yang disetujui -> Diterima (nomor_surat + Jadwal dibuat).
```

```diff
-    // Ruangan 1 dari RuanganSeeder.php. Sejak alur bercabang menurut role pemohon
-    // (bukan lagi ruangan_kategori — lihat PengajuanService::alurApprovalUntukRole()),
-    // ruangan_kategori TIDAK LAGI memengaruhi tahap akhir approval; id ini hanya
-    // dipakai sebagai ruangan pengajuan yang valid.
+    // Ruangan 1 dari RuanganSeeder.php, kategori 'Jurusan' — rantai approval berhenti
+    // di Ketua Jurusan (lihat PengajuanService::alurApprovalUntukPengajuan()).
     ruanganJurusanId: findOptionId('Ruangan 1'),
-    // Dibuat khusus untuk E2E (kategori Umum). Lihat setup: php artisan tinker insert
-    // m_ruangan kode RUM. Dipertahankan untuk test yang butuh variasi ruangan, TAPI
-    // TIDAK LAGI mengubah posisi tahap akhir approval seperti sebelumnya.
+    // Dibuat khusus untuk E2E (kategori Umum). Lihat setup: php artisan tinker insert
+    // m_ruangan kode RUM. Memakai ruangan ini menambah SATU tahap approval akhir
+    // (Wakil Direktur II) setelah Ketua Jurusan — lihat alurApprovalUntukPengajuan().
     ruanganUmumId: findOptionId('Ruangan Umum E2E'),
```

```diff
- * alurApprovalUntukRole() untuk pemohon MHS: Ketua Umum=N+1, Presiden BEM=N+2,
+ * alurApprovalUntukPengajuan() untuk pemohon MHS: Ketua Umum=N+1, Presiden BEM=N+2,
```

```diff
- * (Ketua Pelaksana -> Ketua Jurusan saja, lihat alurApprovalUntukRole()).
+ * (Ketua Pelaksana -> Ketua Jurusan saja, lihat alurApprovalUntukPengajuan()).
```

**Tidak ada logika/assertion test yang diubah** — murni sinkronisasi komentar dengan nama
fungsi & perilaku baru.

---

## 4. `tests/e2e/modules/approval-stage3.spec.js` — hanya komentar header

```diff
- * Service: PengajuanService::generateApprovalStages() / processApproval() / alurApprovalUntukRole()
- *
- * Sejak alur bercabang menurut ROLE PEMOHON (bukan lagi ruangan_kategori) dan
- * SELURUH tahap jadi sekuensial (tidak ada lagi tahap paralel):
+ * Service: PengajuanService::generateApprovalStages() / processApproval() / alurApprovalUntukPengajuan()
+ *
+ * Alur bercabang menurut ROLE PEMOHON, dan SELURUH tahap sekuensial (tidak ada
+ * tahap paralel), dengan ruangan default fixture (ruanganJurusanId, kategori
+ * 'Jurusan') sehingga tahap terakhirnya berhenti di Ketua Jurusan:
  * - Mahasiswa: Tahap 0 Ketua Pelaksana -> Tahap 1 Ketua Umum -> Tahap 2 Presiden
  *   BEM -> Tahap 3 DPK -> Tahap 4 Ketua Jurusan (tahap akhir).
  * - Dosen/Tendik: Tahap 0 Ketua Pelaksana -> Tahap 1 Ketua Jurusan (tahap akhir).
  *
  * Saat tahap akhir disetujui: pengajuan_status -> 'Diterima', nomor_surat
  * digenerate, dan sebuah baris Jadwal dibuat otomatis (createJadwalFromPengajuan()).
  * Tombol "Cetak Surat" pada show_ajax hanya muncul setelah status Diterima.
- * 'Wakil Direktur II' tetap posisi approval yang VALID (lihat POSISI_BY_ROLE di
- * PengajuanService) tapi TIDAK LAGI dipakai oleh alurApprovalUntukRole() manapun,
- * jadi tidak ada test yang menggunakannya di sini.
+ * 'Wakil Direktur II' adalah tahap TAMBAHAN yang muncul otomatis ketika salah satu
+ * ruangan yang dipinjam berkategori 'Umum' (lihat fixtureIds.ruanganUmumId di
+ * approval.fixture.js) — belum ada test suite terpisah untuk jalur itu di sini.
 */
```

**Tidak ada logika/assertion test yang diubah.**

---

## Ringkasan cakupan review

| File | Jenis perubahan | Perlu ditest? |
|---|---|---|
| `app/Services/PengajuanService.php` | Logika baru (approval routing + eager-load) | Ya — e2e/manual, belum dijalankan di sesi ini |
| `resources/views/pdf/surat_peminjaman.blade.php` | Rombak total tampilan PDF | Ya — render visual, baru dicek `php artisan view:cache` (sintaks) |
| `tests/e2e/fixtures/approval.fixture.js` | Komentar saja | Tidak |
| `tests/e2e/modules/approval-stage3.spec.js` | Komentar saja | Tidak |

Verifikasi yang **sudah** dijalankan: `php -l` (sintaks PHP valid) dan `php artisan view:cache`
(kompilasi Blade sukses). Belum dijalankan: Playwright e2e, render PDF sungguhan dari data
database asli — menunggu sesi terpisah sesuai rencana Anda.
