@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil & Keamanan')
@section('page-subtitle', 'Kelola informasi pribadi dan keamanan kata sandi akun Anda')

@section('content')
<div class="row g-4">
    <!-- Left Column: User Summary Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm text-center p-4">
            <div class="position-relative d-inline-block mx-auto mb-3">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle border border-4 border-primary p-1" width="110" height="110">
            </div>
            <h5 class="fw-bold mb-1 text-dark">{{ $user->name }}</h5>
            <p class="text-muted small mb-2">{{ $user->email }}</p>
            
            <div class="mb-3">
                <span class="badge badge-subtle-primary px-3 py-1 rounded-pill">
                    <i class="bi bi-shield-check me-1"></i> Role: {{ $user->role?->display_name ?? 'User' }}
                </span>
            </div>

            <hr class="my-3">

            <div class="text-start small">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">NIP:</span>
                    <span class="fw-semibold">{{ $user->nip ?? '-' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Username:</span>
                    <span class="fw-semibold">@<span>{{ $user->username ?? '-' }}</span></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Jabatan:</span>
                    <span class="fw-semibold">{{ $user->position ?? '-' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Divisi:</span>
                    <span class="fw-semibold">{{ $user->department ?? '-' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Status Akun:</span>
                    <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Update Forms -->
    <div class="col-lg-8">
        <!-- Form Update Profile -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Perbarui Informasi Profil</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="profile_name" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="profile_name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="profile_email" class="form-label fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="profile_email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="profile_phone" class="form-label fw-semibold">Nomor WhatsApp / Telepon</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="profile_phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="contoh: 08123456789">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Simpan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Form Update Password -->
        <div class="card shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="mb-0 fw-bold"><i class="bi bi-key-fill me-2 text-warning"></i>Ubah Kata Sandi</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="current_password" class="form-label fw-semibold">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary btn-toggle-password" type="button" title="Lihat/Sembunyikan Kata Sandi">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="new_password" class="form-label fw-semibold">Kata Sandi Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="new_password" name="password" required>
                                <button class="btn btn-outline-secondary btn-toggle-password" type="button" title="Lihat/Sembunyikan Kata Sandi">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">Minimal 6 karakter.</small>
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Kata Sandi Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                <button class="btn btn-outline-secondary btn-toggle-password" type="button" title="Lihat/Sembunyikan Kata Sandi">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-warning text-dark fw-semibold">
                            <i class="bi bi-shield-lock-fill me-1"></i> Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
