@extends('layouts.template')

@section('content')
<div class="card" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
    <div class="card-header">
        <h3 class="card-title"><strong>Dashboard</strong></h3>
    </div>
    <div class="card-body text-center py-5">
        <i class="fas fa-user-lock fa-3x text-secondary mb-3"></i>
        <h4>Akun Anda belum memiliki hak akses (permission)</h4>
        <p class="text-secondary">
            Selamat datang, <strong>{{ Auth::user()->username }}</strong>. Akun ini terdaftar dengan
            role <strong>{{ $accountRoleName }}</strong>, yang saat ini belum dipetakan ke menu atau
            hak akses apa pun di sistem ini.
        </p>
        <p class="text-secondary">
            Silakan hubungi Administrator sistem untuk memastikan akun Anda dipetakan ke role yang
            sesuai (Admin, Dosen, Tendik, atau Mahasiswa).
        </p>
        <a href="{{ route('logout') }}" class="btn btn-outline-secondary mt-2">
            <i class="fas fa-sign-out-alt mr-1"></i> Logout
        </a>
    </div>
</div>
@endsection
