@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')
@section('page-subtitle', 'Kelola akun karyawan, penetapan role, dan hak akses pengguna sistem')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <!-- Filter & Search Toolbar -->
            <div class="card-header bg-transparent py-3">
                <form action="{{ route('users.index') }}" method="GET" class="row g-2 align-items-center">
                    <!-- Search Input -->
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIP, email, username...">
                        </div>
                    </div>

                    <!-- Filter Role -->
                    <div class="col-md-3">
                        <select name="role_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Role --</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>
                                    {{ $r->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div class="col-md-2">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Status --</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="col-md-3 text-md-end d-flex gap-2 justify-content-md-end">
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        
                        @canExport('users')
                        <a href="{{ route('users.export') }}" class="btn btn-outline-success btn-sm" title="Unduh data pengguna ke CSV">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                        </a>
                        @endcanExport

                        @canCreate('users')
                        <button type="button" class="btn btn-primary btn-sm" id="btnTambahUser">
                            <i class="bi bi-person-plus-fill me-1"></i> Tambah
                        </button>
                        @endcanCreate
                    </div>
                </form>
            </div>

            <!-- Table of Users -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th>Pengguna</th>
                                <th>NIP & Username</th>
                                <th>Jabatan & Divisi</th>
                                <th>Role</th>
                                <th class="text-center" style="width: 100px;">Status</th>
                                <th class="text-end" style="width: 130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $index => $u)
                                <tr>
                                    <td class="text-center fw-semibold text-muted">{{ $users->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" class="rounded-circle me-3 border" width="40" height="40">
                                            <div>
                                                <div class="fw-bold text-dark">{{ $u->name }}</div>
                                                <small class="text-muted d-block">{{ $u->email }}</small>
                                                @if($u->phone)
                                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $u->phone }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><span class="badge bg-light text-dark border">{{ $u->nip ?? 'Belum ada NIP' }}</span></div>
                                        <small class="text-muted">@<span>{{ $u->username ?? '-' }}</span></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark small">{{ $u->position ?? '-' }}</div>
                                        <small class="text-muted">{{ $u->department ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($u->role)
                                            <span class="badge {{ $u->role->name === 'superadmin' ? 'badge-subtle-danger' : ($u->role->name === 'admin' ? 'badge-subtle-primary' : 'badge-subtle-info') }} rounded-pill px-3 py-1">
                                                <i class="bi bi-shield-check me-1"></i> {{ $u->role->display_name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Tanpa Role</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @canUpdate('users')
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input switch-toggle-user" type="checkbox" role="switch" data-id="{{ $u->id }}" {{ $u->is_active ? 'checked' : '' }} {{ auth()->id() === $u->id ? 'disabled' : '' }} title="Klik untuk mengubah status aktif">
                                        </div>
                                        @else
                                        <span class="badge {{ $u->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                        @endcanUpdate
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            @canUpdate('users')
                                            <button type="button" class="btn btn-light border btn-edit-user" data-id="{{ $u->id }}" title="Edit Pengguna">
                                                <i class="bi bi-pencil-fill text-primary"></i>
                                            </button>
                                            @endcanUpdate

                                            @canDelete('users')
                                            @if(auth()->id() !== $u->id)
                                                <button type="button" class="btn btn-light border btn-delete-user" data-id="{{ $u->id }}" data-name="{{ $u->name }}" title="Hapus Pengguna">
                                                    <i class="bi bi-trash-fill text-danger"></i>
                                                </button>
                                            @endif
                                            @endcanDelete
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Tidak ditemukan data pengguna yang cocok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="card-footer bg-light d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="text-muted small">Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari total {{ $users->total() }} pengguna</span>
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Pengguna (jQuery Handled) -->
<div class="modal fade" id="modalUser" tabindex="-1" aria-labelledby="modalUserTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalUserTitle">
                    <i class="bi bi-person-plus me-2 text-primary"></i>Tambah Pengguna Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUser">
                <div class="modal-body p-4">
                    <input type="hidden" id="user_id" name="user_id">

                    <div class="row g-3">
                        <!-- Nama Lengkap -->
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="contoh: Budi Santoso" required>
                            <div class="invalid-feedback" id="error_name"></div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Email Pengguna <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="contoh: budi@absensi.com" required>
                            <div class="invalid-feedback" id="error_email"></div>
                        </div>

                        <!-- Username -->
                        <div class="col-md-6">
                            <label for="username" class="form-label fw-semibold">Username Login</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="contoh: budisantoso">
                            <div class="invalid-feedback" id="error_username"></div>
                        </div>

                        <!-- Role -->
                        <div class="col-md-6">
                            <label for="modal_role_id" class="form-label fw-semibold">Role / Hak Akses <span class="text-danger">*</span></label>
                            <select class="form-select" id="modal_role_id" name="role_id" required>
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->display_name }} ({{ $r->name }})</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="error_role_id"></div>
                        </div>

                        <!-- NIP -->
                        <div class="col-md-6">
                            <label for="nip" class="form-label fw-semibold">Nomor Induk Pegawai (NIP)</label>
                            <input type="text" class="form-control" id="nip" name="nip" placeholder="contoh: EMP-2026-001">
                            <div class="invalid-feedback" id="error_nip"></div>
                        </div>

                        <!-- No HP -->
                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold">Nomor Telepon / WhatsApp</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="contoh: 08123456789">
                        </div>

                        <!-- Jabatan -->
                        <div class="col-md-6">
                            <label for="position" class="form-label fw-semibold">Jabatan</label>
                            <input type="text" class="form-control" id="position" name="position" placeholder="contoh: Supervisor Operasional">
                        </div>

                        <!-- Divisi / Departemen -->
                        <div class="col-md-6">
                            <label for="department" class="form-label fw-semibold">Divisi / Departemen</label>
                            <input type="text" class="form-control" id="department" name="department" placeholder="contoh: Operasional & Logistik">
                        </div>

                        <!-- Kata Sandi -->
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold">Kata Sandi <span id="passwordRequiredStar" class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter">
                                <button class="btn btn-outline-secondary btn-toggle-password" type="button" title="Lihat/Sembunyikan Kata Sandi">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="passwordHelpText" style="font-size: 0.75rem;">Biarkan kosong jika tidak ingin mengubah kata sandi.</small>
                            <div class="invalid-feedback" id="error_password"></div>
                        </div>

                        <!-- Status Aktif -->
                        <div class="col-md-6 d-flex align-items-center pt-3">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="user_is_active" name="is_active" value="1" checked>
                                <label class="form-check-label fw-semibold" for="user_is_active">Akun Aktif (Dapat Login)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanUser">
                        <i class="bi bi-save me-1"></i> Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const modalUser = new bootstrap.Modal(document.getElementById('modalUser'));

        // Tombol Tambah Pengguna
        $('#btnTambahUser').on('click', function() {
            $('#formUser')[0].reset();
            $('#user_id').val('');
            $('#user_is_active').prop('checked', true);
            $('#passwordRequiredStar').show();
            $('#password').prop('required', true);
            $('#passwordHelpText').hide();
            $('.form-control, .form-select').removeClass('is-invalid');
            $('#modalUserTitle').html('<i class="bi bi-person-plus me-2 text-primary"></i>Tambah Pengguna Baru');
            modalUser.show();
        });

        // Tombol Edit Pengguna
        $('.btn-edit-user').on('click', function() {
            const userId = $(this).data('id');
            $('.form-control, .form-select').removeClass('is-invalid');

            $.ajax({
                url: "{{ url('/users') }}/" + userId + "/edit",
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    Swal.showLoading();
                },
                success: function(response) {
                    Swal.close();
                    if (response.status === 'success') {
                        const data = response.data;
                        $('#user_id').val(data.id);
                        $('#name').val(data.name);
                        $('#email').val(data.email);
                        $('#username').val(data.username || '');
                        $('#modal_role_id').val(data.role_id || '');
                        $('#nip').val(data.nip || '');
                        $('#phone').val(data.phone || '');
                        $('#position').val(data.position || '');
                        $('#department').val(data.department || '');
                        $('#password').val('').prop('required', false);
                        $('#passwordRequiredStar').hide();
                        $('#passwordHelpText').show();
                        $('#user_is_active').prop('checked', !!data.is_active);

                        $('#modalUserTitle').html('<i class="bi bi-pencil-square me-2 text-primary"></i>Edit Pengguna: ' + data.name);
                        modalUser.show();
                    }
                },
                error: function() {
                    Swal.close();
                    Toast.fire({ icon: 'error', title: 'Gagal mengambil data pengguna.' });
                }
            });
        });

        // Submit Form Pengguna via jQuery AJAX
        $('#formUser').on('submit', function(e) {
            e.preventDefault();
            $('.form-control, .form-select').removeClass('is-invalid');

            const userId = $('#user_id').val();
            const isEdit = userId !== '';
            const url = isEdit ? "{{ url('/users') }}/" + userId + "/update" : "{{ route('users.store') }}";

            const formData = {
                name: $('#name').val(),
                email: $('#email').val(),
                username: $('#username').val(),
                role_id: $('#modal_role_id').val(),
                nip: $('#nip').val(),
                phone: $('#phone').val(),
                position: $('#position').val(),
                department: $('#department').val(),
                password: $('#password').val(),
                is_active: $('#user_is_active').is(':checked') ? 1 : 0,
            };

            $('#btnSimpanUser').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#btnSimpanUser').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Pengguna');
                    if (response.status === 'success') {
                        modalUser.hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    $('#btnSimpanUser').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Pengguna');
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            if (field === 'role_id') {
                                $('#modal_role_id').addClass('is-invalid');
                            } else {
                                $('#' + field).addClass('is-invalid');
                            }
                            $('#error_' + field).text(messages[0]);
                        });
                    } else {
                        Toast.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Gagal menyimpan pengguna.' });
                    }
                }
            });
        });

        // Toggle Status Aktif Pengguna via jQuery AJAX
        $('.switch-toggle-user').on('change', function() {
            const userId = $(this).data('id');
            const switchEl = $(this);

            $.ajax({
                url: "{{ url('/users') }}/" + userId + "/toggle",
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Toast.fire({ icon: 'success', title: response.message });
                    }
                },
                error: function(xhr) {
                    switchEl.prop('checked', !switchEl.is(':checked'));
                    Toast.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Gagal mengubah status pengguna.' });
                }
            });
        });

        // Hapus Pengguna via SweetAlert2 & jQuery AJAX
        $('.btn-delete-user').on('click', function() {
            const userId = $(this).data('id');
            const userName = $(this).data('name');

            Swal.fire({
                title: 'Hapus Pengguna?',
                text: `Apakah Anda yakin ingin menghapus akun pengguna "${userName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('/users') }}/" + userId,
                        type: 'DELETE',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menghapus pengguna.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
