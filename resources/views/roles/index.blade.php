@extends('layouts.app')

@section('title', 'Manajemen Role & Izin')
@section('page-title', 'Manajemen Role & Izin')
@section('page-subtitle', 'Kelola daftar role dan konfigurasi matriks hak akses CRUD per menu')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="fs-6 fw-bold"><i class="bi bi-shield-lock-fill me-2 text-warning"></i>Daftar Role Pengguna</span>
                
                @canCreate('roles')
                <button type="button" class="btn btn-primary btn-sm" id="btnTambahRole">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Role Baru
                </button>
                @endcanCreate
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;" class="text-center">#</th>
                                <th>Nama Role (Slug)</th>
                                <th>Nama Tampilan</th>
                                <th>Deskripsi</th>
                                <th class="text-center">Jumlah Pengguna</th>
                                <th class="text-center">Tipe</th>
                                <th class="text-end" style="width: 220px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $index => $role)
                                <tr>
                                    <td class="text-center fw-semibold text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <code>{{ $role->name }}</code>
                                    </td>
                                    <td class="fw-bold text-dark">
                                        <i class="bi bi-shield-shaded me-1 {{ $role->name === 'superadmin' ? 'text-danger' : ($role->name === 'admin' ? 'text-primary' : 'text-secondary') }}"></i>
                                        {{ $role->display_name }}
                                    </td>
                                    <td class="text-muted small">{{ $role->description ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-subtle-primary rounded-pill px-3 py-1">
                                            <i class="bi bi-people-fill me-1"></i> {{ $role->users_count }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($role->is_system)
                                            <span class="badge bg-secondary" title="Role bawaan sistem">Sistem</span>
                                        @else
                                            <span class="badge bg-success" title="Role kustom">Kustom</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <!-- Tombol Matriks Izin -->
                                            <a href="{{ route('roles.permissions', $role->id) }}" class="btn btn-warning text-dark fw-semibold" title="Atur Izin CRUD Menu">
                                                <i class="bi bi-key-fill me-1"></i> Izin Menu
                                            </a>

                                            @canUpdate('roles')
                                            <!-- Tombol Edit Role -->
                                            <button type="button" class="btn btn-light border btn-edit-role" data-id="{{ $role->id }}" title="Edit Role">
                                                <i class="bi bi-pencil-fill text-primary"></i>
                                            </button>
                                            @endcanUpdate

                                            @canDelete('roles')
                                            @if(!$role->is_system)
                                                <!-- Tombol Hapus Role -->
                                                <button type="button" class="btn btn-light border btn-delete-role" data-id="{{ $role->id }}" data-name="{{ $role->display_name }}" title="Hapus Role">
                                                    <i class="bi bi-trash-fill text-danger"></i>
                                                </button>
                                            @endif
                                            @endcanDelete
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada data role.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Role (jQuery Handled) -->
<div class="modal fade" id="modalRole" tabindex="-1" aria-labelledby="modalRoleTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalRoleTitle">
                    <i class="bi bi-shield-plus me-2 text-primary"></i>Tambah Role Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRole">
                <div class="modal-body p-4">
                    <input type="hidden" id="role_id" name="role_id">

                    <div class="mb-3">
                        <label for="display_name" class="form-label fw-semibold">Nama Tampilan Role <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="display_name" name="display_name" placeholder="contoh: Manajer Operasional" required>
                        <div class="invalid-feedback" id="error_display_name"></div>
                    </div>

                    <div class="mb-3" id="group_name">
                        <label for="name" class="form-label fw-semibold">Slug/Kode Role (Opsional)</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="contoh: manager-ops (otomatis jika dikosongkan)">
                        <small class="text-muted" style="font-size: 0.75rem;">Hanya huruf kecil, angka, dan tanda hubung (-).</small>
                        <div class="invalid-feedback" id="error_name"></div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Deskripsi Role</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Jelaskan cakupan wewenang role ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanRole">
                        <i class="bi bi-save me-1"></i> Simpan
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
        const modalRole = new bootstrap.Modal(document.getElementById('modalRole'));

        // Tombol Tambah Role
        $('#btnTambahRole').on('click', function() {
            $('#formRole')[0].reset();
            $('#role_id').val('');
            $('#modalRoleTitle').html('<i class="bi bi-shield-plus me-2 text-primary"></i>Tambah Role Baru');
            $('#group_name').show();
            $('.form-control').removeClass('is-invalid');
            modalRole.show();
        });

        // Tombol Edit Role
        $('.btn-edit-role').on('click', function() {
            const roleId = $(this).data('id');
            $('.form-control').removeClass('is-invalid');

            // Ambil data role via jQuery AJAX
            $.ajax({
                url: "{{ url('/roles') }}/" + roleId + "/edit",
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    Swal.showLoading();
                },
                success: function(response) {
                    Swal.close();
                    if (response.status === 'success') {
                        const data = response.data;
                        $('#role_id').val(data.id);
                        $('#display_name').val(data.display_name);
                        $('#description').val(data.description);
                        $('#group_name').hide(); // Sembunyikan field slug saat edit
                        $('#modalRoleTitle').html('<i class="bi bi-pencil-square me-2 text-primary"></i>Edit Role: ' + data.display_name);
                        modalRole.show();
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    Toast.fire({
                        icon: 'error',
                        title: 'Gagal mengambil data role.'
                    });
                }
            });
        });

        // Submit Form Tambah / Edit Role via jQuery AJAX
        $('#formRole').on('submit', function(e) {
            e.preventDefault();
            $('.form-control').removeClass('is-invalid');

            const roleId = $('#role_id').val();
            const isEdit = roleId !== '';
            const url = isEdit ? "{{ url('/roles') }}/" + roleId + "/update" : "{{ route('roles.store') }}";
            const method = 'POST';

            const formData = {
                display_name: $('#display_name').val(),
                description: $('#description').val(),
            };

            if (!isEdit) {
                formData.name = $('#name').val();
            }

            $('#btnSimpanRole').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: url,
                type: method,
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#btnSimpanRole').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                    if (response.status === 'success') {
                        modalRole.hide();
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
                    $('#btnSimpanRole').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            $('#' + field).addClass('is-invalid');
                            $('#error_' + field).text(messages[0]);
                        });
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Terjadi kesalahan sistem.'
                        });
                    }
                }
            });
        });

        // Hapus Role via SweetAlert2 & jQuery AJAX
        $('.btn-delete-role').on('click', function() {
            const roleId = $(this).data('id');
            const roleName = $(this).data('name');

            Swal.fire({
                title: 'Hapus Role?',
                text: `Apakah Anda yakin ingin menghapus role "${roleName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('/roles') }}/" + roleId,
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
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Tidak dapat menghapus role.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
