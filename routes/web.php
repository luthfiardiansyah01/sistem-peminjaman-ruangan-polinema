<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JadwalPribadiController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\TendikController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\OrganisasiController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\PeriodeController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
//Route::get('/', [WelcomeController::class, 'index']);
Route::pattern('id', '[0-9]+'); //meaning: ketika ada parameter "id" maka nilainya harus angka, yaitu dari 0 sampai 9.
Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'postLogin']);
Route::get('logout', [AuthController::class, 'logout'])->name('logout');

// Public Route
// Route::get('/', [WelcomeController::class, 'landing'])->name('landing');

// Dashboard publik (tanpa login) — statistik agregat aman-publik saja, lihat
// DashboardService::getGuestDashboardData(). Sengaja di LUAR middleware 'auth'.
Route::get('/', [WelcomeController::class, 'guestDashboard'])->name('guest.dashboard');

Route::middleware(['auth'])->group(function () {
    // Dashboard Route
    Route::get('/dashboard', [WelcomeController::class, 'index'])->name('dashboard');
    
    // Profile Routes
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/show_ajax', [App\Http\Controllers\ProfileController::class, 'show_ajax']);
        Route::get('/edit_ajax', [App\Http\Controllers\ProfileController::class, 'edit_ajax']);
        Route::put('/update_ajax', [App\Http\Controllers\ProfileController::class, 'update_ajax']);
    });
    
    // Formulir Routes
    Route::group(['prefix' => 'formulir'], function () {
        Route::post('/upload', [WelcomeController::class, 'uploadFormulir'])->name('formulir.upload')->middleware('authorize:ADM');
        Route::get('/download', [WelcomeController::class, 'downloadFormulir'])->name('formulir.download')->middleware('authorize:DSN,TDK,MHS');
    });

    Route::group(['prefix' => 'jadwal', 'middleware' => 'authorize:ADM,DSN,TDK,MHS'], function () {
        // ⚠️ URUTAN PENTING: Route spesifik HARUS sebelum route dengan parameter {id}
        // Ini mencegah Laravel salah menginterpretasi "create_ajax" sebagai parameter {id}

        // PUBLIC ROUTES (semua role terdaftar bisa akses)
        Route::get('/', [JadwalController::class, 'index']);
        Route::post('/list', [JadwalController::class, 'list']);
        Route::get('/get_kelas_by_prodi/{prodi_id}', [JadwalController::class, 'get_kelas_by_prodi']);

        Route::get('/pribadi', [JadwalPribadiController::class, 'index'])->name('jadwal.pribadi');
        Route::get('/pribadi/{id}/show_ajax', [JadwalPribadiController::class,'show_ajax']);
        Route::get('/pribadi/{id}/edit_ajax', [JadwalPribadiController::class,'edit_ajax']);
        Route::put('/pribadi/{id}/update_ajax', [JadwalPribadiController::class,'update_ajax'])->middleware('periode_gate');
        Route::get('/pribadi/create_ajax', [JadwalPribadiController::class,'create_ajax']);
        Route::post('/pribadi/store_ajax', [JadwalPribadiController::class,'store_ajax'])->middleware('periode_gate');
        Route::get('/pribadi/{id}/selesaikan_ajax', [JadwalPribadiController::class,'selesaikan_ajax']);
        Route::post('/pribadi/{id}/selesaikan_ajax', [JadwalPribadiController::class,'selesaikan_store_ajax']);


        // ADMIN-ONLY: Route spesifik (tanpa parameter {id}) — HARUS sebelum {id}
        Route::get('/create_ajax', [JadwalController::class, 'create_ajax']);
        Route::post('/ajax', [JadwalController::class, 'store_ajax'])->middleware('periode_gate');

        // ADMIN-ONLY: Route dengan parameter {id}
        Route::get('/{id}/show_ajax', [JadwalController::class, 'show_ajax']);
        Route::get('/{id}/edit_ajax', [JadwalController::class, 'edit_ajax']);
        Route::put('/{id}/update_ajax', [JadwalController::class, 'update_ajax'])->middleware('periode_gate');
        Route::get('/{id}/confirm_ajax', [JadwalController::class, 'confirm_ajax']);
        Route::delete('/{id}/delete_ajax', [JadwalController::class, 'delete_ajax'])->middleware('periode_gate');
        Route::put('/{id}/update_status_ajax', [JadwalController::class, 'update_status_ajax'])->middleware('periode_gate');
    });

    Route::group(['prefix' => 'ruangan', 'middleware' => 'authorize:ADM'], function () {
        // Route::get('/', [RuanganController::class, 'index']); 
        // Route::post('/list', [RuanganController::class, 'list']); 

        Route::get('/create_ajax', [RuanganController::class, 'create_ajax']); 
        Route::post('/ajax', [RuanganController::class, 'store_ajax']); 

        // Route::get('/{id}/show_ajax', [RuanganController::class, 'show_ajax']); 

        Route::get('/{id}/edit_ajax', [RuanganController::class, 'edit_ajax']); 
        Route::put('/{id}/update_ajax', [RuanganController::class, 'update_ajax']); 

        Route::get('/{id}/confirm_ajax', [RuanganController::class, 'confirm_ajax']); 
        Route::delete('/{id}/delete_ajax', [RuanganController::class, 'delete_ajax']); 
    });

    Route::group(['prefix' => 'ruangan', 'middleware' => 'authorize:ADM,DSN,TDK,MHS'], function () {
        Route::get('/', [RuanganController::class, 'index']);
        Route::post('/list', [RuanganController::class, 'list']);
        Route::get('/{id}/show_ajax', [RuanganController::class, 'show_ajax']);

        // Status ruangan per-tanggal (poin 2) — read-only, sama grup role dgn index/show_ajax.
        Route::get('/availability_ajax', [RuanganController::class, 'availability_ajax']);
        Route::get('/{id}/kalender_ajax', [RuanganController::class, 'kalender_ajax']);
        Route::get('/{id}/kalender_data_ajax', [RuanganController::class, 'kalender_data_ajax']);
    });

    Route::group(['prefix' => 'organisasi', 'middleware' => 'authorize:ADM'], function () {
        Route::get('/', [OrganisasiController::class, 'index']);
        Route::post('/list', [OrganisasiController::class, 'list']);
        Route::get('/create_ajax', [OrganisasiController::class, 'create_ajax']);
        Route::post('/ajax', [OrganisasiController::class, 'store_ajax']);
        Route::get('/{id}/show_ajax', [OrganisasiController::class, 'show_ajax']);
        Route::get('/{id}/edit_ajax', [OrganisasiController::class, 'edit_ajax']);
        Route::put('/{id}/update_ajax', [OrganisasiController::class, 'update_ajax']);
        Route::get('/{id}/confirm_ajax', [OrganisasiController::class, 'confirm_ajax']);
        Route::delete('/{id}/delete_ajax', [OrganisasiController::class, 'delete_ajax']);
    });

    // Pengajuan Peminjaman Routes
    // Catatan: route ini sebelumnya sama sekali belum terdaftar meski PengajuanController/
    // PengajuanService/PengajuanModel sudah ada sejak Tahap 1 — didaftarkan sekarang sebagai
    // prasyarat agar modul Approval Berjenjang (G3) bisa diuji end-to-end.
    // show_ajax/timeline_ajax/cetak_surat_ajax dipakai lintas fitur (mis. halaman Jadwal),
    // jadi ADM tetap perlu akses ini walau listing "index"-nya kini terpisah (lihat grup
    // 'penyelesaian' di bawah, khusus ADM).
    Route::group(['prefix' => 'pengajuan', 'middleware' => 'authorize:ADM,DSN,TDK,MHS'], function () {
        Route::get('/{id}/show_ajax', [PengajuanController::class, 'show_ajax']);
        Route::get('/{id}/timeline_ajax', [PengajuanController::class, 'timeline_ajax']);
        Route::get('/{id}/cetak_surat_ajax', [PengajuanController::class, 'cetak_surat_ajax']);
        Route::get('/panitia_by_organisasi/{organisasi_id}', [PengajuanController::class, 'panitia_by_organisasi_ajax']);
    });

    // Listing "Daftar Pengajuan": ADM TIDAK memakai route ini lagi — lihat grup 'penyelesaian'
    // di bawah untuk versi "Daftar Penyelesaian" khusus ADM.
    Route::group(['prefix' => 'pengajuan', 'middleware' => 'authorize:DSN,TDK,MHS'], function () {
        Route::get('/', [PengajuanController::class, 'index']);
    });

    // Permission create Pengajuan Peminjaman: HANYA role DSN/TDK/MHS (peminjam), ADM tidak
    // boleh mengajukan (perannya mengelola/verifikasi, bukan mengajukan peminjaman ruangan).
    Route::group(['prefix' => 'pengajuan', 'middleware' => 'authorize:DSN,TDK,MHS'], function () {
        Route::get('/create_ajax', [PengajuanController::class, 'create_ajax']);
        Route::post('/ajax', [PengajuanController::class, 'store_ajax'])->middleware('periode_gate');
        Route::get('/{id}/edit_ajax', [PengajuanController::class, 'edit_ajax']);
        Route::put('/{id}/update_ajax', [PengajuanController::class, 'update_ajax'])->middleware('periode_gate');
    });

    // Daftar Penyelesaian — fitur khusus role ADM (menggantikan "Daftar Pengajuan" untuk ADM).
    // Route/nama disesuaikan agar tidak lagi memakai istilah "pengajuan"/"verifikasi" untuk ADM;
    // logika bisnis (accept/reject) tetap memakai method service yang sama, hanya nama aksi
    // yang disesuaikan menjadi "edit_ajax" (Edit/Update) sesuai alur baru.
    Route::group(['prefix' => 'penyelesaian', 'middleware' => 'authorize:ADM'], function () {
        Route::get('/', [PengajuanController::class, 'index_penyelesaian']);
        Route::get('/{id}/edit_ajax', [PengajuanController::class, 'verifikasi_ajax']);
        Route::put('/{id}/terima_ajax', [PengajuanController::class, 'terima_ajax'])->middleware('periode_gate');
        Route::put('/{id}/tolak_ajax', [PengajuanController::class, 'tolak_ajax'])->middleware('periode_gate');
    });

    // Approval Berjenjang Routes — FR-6.5–FR-6.9, NFR-2.1
    // Level VRF terpisah sudah dihapus; pemegang jabatan approval tetap login sebagai
    // DSN/MHS biasa (lihat m_jabatan_approval), jadi gate di sini cuma "harus login
    // dengan salah satu role ini" — otorisasi SEBENARNYA (siapa boleh proses baris
    // approval mana) ditegakkan di PengajuanService::processApproval() lewat
    // pengecekan kepemilikan jabatan_approval->user_id.
    Route::group(['prefix' => 'pengajuan/approval', 'middleware' => 'authorize:ADM,DSN,TDK,MHS'], function () {
        Route::get('/antrian_ajax', [PengajuanController::class, 'antrian_ajax']);
        Route::put('/{approvalId}/proses_ajax', [PengajuanController::class, 'proses_approval_ajax'])->middleware('periode_gate');
    });

    // Periode (tahun ajaran + semester, Open/Closed) Routes — CRUD penuh oleh Admin,
    // status Open/Closed jadi gerbang transactional operation di atas (lihat PeriodeGate).
    Route::group(['prefix' => 'periode', 'middleware' => 'authorize:ADM'], function () {
        Route::get('/', [PeriodeController::class, 'index']);
        Route::get('/create_ajax', [PeriodeController::class, 'create_ajax']);
        Route::post('/ajax', [PeriodeController::class, 'store_ajax']);
        Route::get('/{id}/show_ajax', [PeriodeController::class, 'show_ajax']);
        Route::get('/{id}/edit_ajax', [PeriodeController::class, 'edit_ajax']);
        Route::put('/{id}/update_ajax', [PeriodeController::class, 'update_ajax']);
        Route::get('/{id}/confirm_ajax', [PeriodeController::class, 'confirm_ajax']);
        Route::delete('/{id}/delete_ajax', [PeriodeController::class, 'delete_ajax']);
    });

    Route::group(['prefix' => 'admin', 'middleware' => 'authorize:ADM'], function () {
        Route::get('/', [AdminController::class, 'index']); //Menampilkan laman awal admin
        Route::post('/list', [AdminController::class, 'list']); //menampilkan data admin dalam bentuk json untuk datatables.

        Route::get('/create_ajax', [AdminController::class, 'create_ajax']); //Buat data admin w ajax
        Route::post('/ajax', [AdminController::class, 'store_ajax']); //menyimpan data admin baru w ajax

        Route::get('/{id}/show_ajax', [AdminController::class, 'show_ajax']);

        Route::get('/{id}/edit_ajax', [AdminController::class, 'edit_ajax']); //edit data admin dengan ajax
        Route::put('/{id}/update_ajax', [AdminController::class, 'update_ajax']); //menyimpan perubahan data dengan ajax

        Route::get('/{id}/confirm_ajax', [AdminController::class, 'confirm_ajax']); //Munculkan pop up konfirmasi delete dengan ajax
        Route::delete('/{id}/delete_ajax', [AdminController::class, 'delete_ajax']); //Menghapus data admin dengan ajax

        Route::get('/import', [AdminController::class, 'import']); //import excel
        Route::post('/import_ajax', [AdminController::class, 'import_ajax']); //import excel dengan ajax
    });

    Route::group(['prefix' => 'dosen', 'middleware' => 'authorize:ADM'], function () {
        Route::get('/', [DosenController::class, 'index']); //Menampilkan laman awal dosen

        Route::get('/create_ajax', [DosenController::class, 'create_ajax']); //Buat data dosen w ajax
        Route::post('/ajax', [DosenController::class, 'store_ajax']); //menyimpan data dosen baru w ajax

        Route::get('/{id}/show_ajax', [DosenController::class, 'show_ajax']);

        Route::get('/{id}/edit_ajax', [DosenController::class, 'edit_ajax']); //edit data dosen dengan ajax
        Route::put('/{id}/update_ajax', [DosenController::class, 'update_ajax']); //menyimpan perubahan data dengan ajax

        Route::get('/{id}/confirm_ajax', [DosenController::class, 'confirm_ajax']); //Munculkan pop up konfirmasi delete dengan ajax
        Route::delete('/{id}/delete_ajax', [DosenController::class, 'delete_ajax']); //Menghapus data dosen dengan ajax

        Route::get('/import', [DosenController::class, 'import']); //import excel
        Route::post('/import_ajax', [DosenController::class, 'import_ajax']); //import excel dengan ajax
    });

    Route::group(['prefix' => 'tendik', 'middleware' => 'authorize:ADM'], function () {
        Route::get('/', [TendikController::class, 'index']); //Menampilkan laman awal tendik

        Route::get('/create_ajax', [TendikController::class, 'create_ajax']); //Buat data tendik w ajax
        Route::post('/ajax', [TendikController::class, 'store_ajax']); //menyimpan data tendik baru w ajax

        Route::get('/{id}/show_ajax', [TendikController::class, 'show_ajax']);

        Route::get('/{id}/edit_ajax', [TendikController::class, 'edit_ajax']); //edit data tendik dengan ajax
        Route::put('/{id}/update_ajax', [TendikController::class, 'update_ajax']); //menyimpan perubahan data dengan ajax

        Route::get('/{id}/confirm_ajax', [TendikController::class, 'confirm_ajax']); //Munculkan pop up konfirmasi delete dengan ajax
        Route::delete('/{id}/delete_ajax', [TendikController::class, 'delete_ajax']); //Menghapus data tendik dengan ajax

        Route::get('/import', [TendikController::class, 'import']); //import excel
        Route::post('/import_ajax', [TendikController::class, 'import_ajax']); //import excel dengan ajax
    });

    Route::group(['prefix' => 'mahasiswa', 'middleware' => 'authorize:ADM'], function () {
        Route::get('/', [MahasiswaController::class, 'index']); //Menampilkan laman awal mahasiswa

        Route::get('/create_ajax', [MahasiswaController::class, 'create_ajax']); //Buat data mahasiswa w ajax
        Route::post('/ajax', [MahasiswaController::class, 'store_ajax']); //menyimpan data mahasiswa baru w ajax

        Route::get('/{id}/show_ajax', [MahasiswaController::class, 'show_ajax']);

        Route::get('/{id}/edit_ajax', [MahasiswaController::class, 'edit_ajax']); //edit data mahasiswa dengan ajax
        Route::put('/{id}/update_ajax', [MahasiswaController::class, 'update_ajax']); //menyimpan perubahan data dengan ajax

        Route::get('/{id}/confirm_ajax', [MahasiswaController::class, 'confirm_ajax']); //Munculkan pop up konfirmasi delete dengan ajax
        Route::delete('/{id}/delete_ajax', [MahasiswaController::class, 'delete_ajax']); //Menghapus data mahasiswa dengan ajax

        Route::get('/import', [MahasiswaController::class, 'import']); //import excel
        Route::post('/import_ajax', [MahasiswaController::class, 'import_ajax']); //import excel dengan ajax
        Route::get('/get_kelas_by_prodi/{prodi_id}', [MahasiswaController::class, 'get_kelas_by_prodi']); // Get Kelas by Prodi
    });

    Route::group(['prefix' => 'prodi', 'middleware' => 'authorize:ADM'], function () {
        Route::get('/', [ProdiController::class, 'index']); //Menampilkan laman awal prodi

        Route::get('/create_ajax', [ProdiController::class, 'create_ajax']); //Buat data prodi w ajax
        Route::post('/ajax', [ProdiController::class, 'store_ajax']); //menyimpan data prodi baru w ajax

        Route::get('/{id}/edit_ajax', [ProdiController::class, 'edit_ajax']); //edit data prodi dengan ajax
        Route::put('/{id}/update_ajax', [ProdiController::class, 'update_ajax']); //menyimpan perubahan data dengan ajax

        Route::get('/{id}/confirm_ajax', [ProdiController::class, 'confirm_ajax']); //Munculkan pop up konfirmasi delete dengan ajax
        Route::delete('/{id}/delete_ajax', [ProdiController::class, 'delete_ajax']); //Menghapus data prodi dengan ajax
    });

    Route::group(['prefix' => 'kelas', 'middleware' => 'authorize:ADM'], function () {
        Route::get('/', [KelasController::class, 'index']); //Menampilkan laman awal kelas

        Route::get('/create_ajax', [KelasController::class, 'create_ajax']); //Buat data kelas w ajax
        Route::post('/ajax', [KelasController::class, 'store_ajax']); //menyimpan data kelas baru w ajax

        Route::get('/{id}/edit_ajax', [KelasController::class, 'edit_ajax']); //edit data kelas dengan ajax
        Route::put('/{id}/update_ajax', [KelasController::class, 'update_ajax']); //menyimpan perubahan data dengan ajax

        Route::get('/{id}/confirm_ajax', [KelasController::class, 'confirm_ajax']); //Munculkan pop up konfirmasi delete dengan ajax
        Route::delete('/{id}/delete_ajax', [KelasController::class, 'delete_ajax']); //Menghapus data kelas dengan ajax
    });
});

