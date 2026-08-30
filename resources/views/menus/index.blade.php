@extends('layouts.app')

@section('title', 'Manajemen Menu')
@section('page-title', 'Manajemen Menu')
@section('page-subtitle', 'Konfigurasi struktur menu navigasi dinamis dan aturan aksi yang didukung')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="fs-6 fw-bold"><i class="bi bi-menu-button-wide-fill me-2 text-primary"></i>Daftar Struktur Menu Navigasi</span>
                
                @canCreate('menus')
                <button type="button" class="btn btn-primary btn-sm" id="btnTambahMenu">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Menu Baru
                </button>
                @endcanCreate
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;" class="text-center">Urutan</th>
                                <th>Nama Menu</th>
                                <th>Kode Menu</th>
                                <th>Route / URL</th>
                                <th>Izin yang Didukung</th>
                                <th class="text-center" style="width: 100px;">Status</th>
                                <th class="text-end" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($menus as $menu)
                                <!-- Menu Utama -->
                                <tr class="bg-white">
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ $menu->order_index }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded p-2 bg-light text-primary me-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                                <i class="{{ $menu->icon }} fs-5"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark">{{ $menu->name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><code>{{ $menu->code }}</code></td>
                                    <td>
                                        <small class="text-muted">
                                            @if($menu->route_name)
                                                <i class="bi bi-link-45deg me-1"></i>Route: <code>{{ $menu->route_name }}</code>
                                            @elseif($menu->url)
                                                <i class="bi bi-globe me-1"></i>URL: <code>{{ $menu->url }}</code>
                                            @else
                                                <span class="badge bg-light text-muted">Menu Induk (Dropdown)</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 flex-wrap">
                                            <span class="badge badge-subtle-primary" style="font-size: 0.7rem;">Read</span>
                                            @if($menu->has_create) <span class="badge badge-subtle-success" style="font-size: 0.7rem;">Create</span> @endif
                                            @if($menu->has_update) <span class="badge badge-subtle-warning" style="font-size: 0.7rem;">Update</span> @endif
                                            @if($menu->has_delete) <span class="badge badge-subtle-danger" style="font-size: 0.7rem;">Delete</span> @endif
                                            @if($menu->has_export) <span class="badge badge-subtle-info" style="font-size: 0.7rem;">Export</span> @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @canUpdate('menus')
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input switch-toggle-menu" type="checkbox" role="switch" data-id="{{ $menu->id }}" {{ $menu->is_active ? 'checked' : '' }} title="Klik untuk mengubah status aktif">
                                        </div>
                                        @else
                                        <span class="badge {{ $menu->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $menu->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                        @endcanUpdate
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            @canUpdate('menus')
                                            <button type="button" class="btn btn-light border btn-edit-menu" data-id="{{ $menu->id }}" title="Edit Menu">
                                                <i class="bi bi-pencil-fill text-primary"></i>
                                            </button>
                                            @endcanUpdate

                                            @canDelete('menus')
                                            <button type="button" class="btn btn-light border btn-delete-menu" data-id="{{ $menu->id }}" data-name="{{ $menu->name }}" title="Hapus Menu">
                                                <i class="bi bi-trash-fill text-danger"></i>
                                            </button>
                                            @endcanDelete
                                        </div>
                                    </td>
                                </tr>

                                <!-- Sub-Menu Children -->
                                @foreach($menu->children as $child)
                                    <tr class="bg-light bg-opacity-50">
                                        <td class="text-center">
                                            <span class="badge bg-white text-muted border">{{ $child->order_index }}</span>
                                        </td>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center ms-3">
                                                <i class="bi bi-arrow-return-right text-muted me-2"></i>
                                                <i class="{{ $child->icon }} text-secondary me-2"></i>
                                                <span class="fw-semibold text-dark">{{ $child->name }}</span>
                                            </div>
                                        </td>
                                        <td><code>{{ $child->code }}</code></td>
                                        <td>
                                            <small class="text-muted">
                                                @if($child->route_name)
                                                    <i class="bi bi-link-45deg me-1"></i>Route: <code>{{ $child->route_name }}</code>
                                                @elseif($child->url)
                                                    <i class="bi bi-globe me-1"></i>URL: <code>{{ $child->url }}</code>
                                                @else
                                                    -
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <span class="badge badge-subtle-primary" style="font-size: 0.7rem;">Read</span>
                                                @if($child->has_create) <span class="badge badge-subtle-success" style="font-size: 0.7rem;">Create</span> @endif
                                                @if($child->has_update) <span class="badge badge-subtle-warning" style="font-size: 0.7rem;">Update</span> @endif
                                                @if($child->has_delete) <span class="badge badge-subtle-danger" style="font-size: 0.7rem;">Delete</span> @endif
                                                @if($child->has_export) <span class="badge badge-subtle-info" style="font-size: 0.7rem;">Export</span> @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @canUpdate('menus')
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input switch-toggle-menu" type="checkbox" role="switch" data-id="{{ $child->id }}" {{ $child->is_active ? 'checked' : '' }}>
                                            </div>
                                            @else
                                            <span class="badge {{ $child->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $child->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                            @endcanUpdate
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                @canUpdate('menus')
                                                <button type="button" class="btn btn-light border btn-edit-menu" data-id="{{ $child->id }}" title="Edit Sub-Menu">
                                                    <i class="bi bi-pencil-fill text-primary"></i>
                                                </button>
                                                @endcanUpdate

                                                @canDelete('menus')
                                                <button type="button" class="btn btn-light border btn-delete-menu" data-id="{{ $child->id }}" data-name="{{ $child->name }}" title="Hapus Sub-Menu">
                                                    <i class="bi bi-trash-fill text-danger"></i>
                                                </button>
                                                @endcanDelete
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada daftar menu.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Menu (jQuery Handled) -->
<div class="modal fade" id="modalMenu" tabindex="-1" aria-labelledby="modalMenuTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalMenuTitle">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Menu Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formMenu">
                <div class="modal-body p-4">
                    <input type="hidden" id="menu_id" name="menu_id">

                    <div class="row g-3">
                        <!-- Nama Menu -->
                        <div class="col-md-6">
                            <label for="menu_name" class="form-label fw-semibold">Nama Label Menu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="menu_name" name="name" placeholder="contoh: Data Presensi" required>
                            <div class="invalid-feedback" id="error_name"></div>
                        </div>

                        <!-- Kode Menu -->
                        <div class="col-md-6">
                            <label for="menu_code" class="form-label fw-semibold">Kode Unik Menu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="menu_code" name="code" placeholder="contoh: attendances (huruf kecil & underscore)">
                            <div class="invalid-feedback" id="error_code"></div>
                        </div>

                        <!-- Menu Induk (Parent) -->
                        <div class="col-md-6">
                            <label for="parent_id" class="form-label fw-semibold">Menu Induk (Parent)</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">-- Menu Utama (Tanpa Induk) --</option>
                                @foreach($parentMenus as $pm)
                                    <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Icon Class -->
                        <div class="col-md-6">
                            <label for="menu_icon" class="form-label fw-semibold">Icon Class (Bootstrap Icons)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-app" id="iconPreview"></i></span>
                                <input type="text" class="form-control" id="menu_icon" name="icon" value="bi bi-circle" placeholder="contoh: bi bi-calendar-check">
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">Gunakan class icon Bootstrap Icons (misal: <code>bi bi-people</code>, <code>bi bi-clock</code>).</small>
                        </div>

                        <!-- Route Name -->
                        <div class="col-md-6">
                            <label for="route_name" class="form-label fw-semibold">Nama Route Laravel</label>
                            <input type="text" class="form-control" id="route_name" name="route_name" placeholder="contoh: attendances.index">
                            <small class="text-muted" style="font-size: 0.75rem;">Nama rute terdaftar di routes/web.php.</small>
                        </div>

                        <!-- URL Fallback -->
                        <div class="col-md-6">
                            <label for="menu_url" class="form-label fw-semibold">URL Path</label>
                            <input type="text" class="form-control" id="menu_url" name="url" placeholder="contoh: /attendances">
                            <small class="text-muted" style="font-size: 0.75rem;">Digunakan jika tidak menggunakan Route Name.</small>
                        </div>

                        <!-- Urutan Tampil -->
                        <div class="col-md-6">
                            <label for="order_index" class="form-label fw-semibold">Urutan Tampil</label>
                            <input type="number" class="form-control" id="order_index" name="order_index" value="0" min="0">
                        </div>

                        <!-- Status Aktif -->
                        <div class="col-md-6 d-flex align-items-center pt-3">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label fw-semibold" for="is_active">Aktifkan Menu Ini</label>
                            </div>
                        </div>

                        <!-- Konfigurasi Izin yang Didukung -->
                        <div class="col-12 mt-4">
                            <label class="form-label fw-semibold d-block border-bottom pb-2">
                                <i class="bi bi-shield-check me-1 text-primary"></i> Aksi CRUD yang Didukung Menu Ini
                            </label>
                            <div class="row g-2">
                                <div class="col-sm-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="has_create" name="has_create" value="1" checked>
                                        <label class="form-check-label" for="has_create">Tambah (Create)</label>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="has_update" name="has_update" value="1" checked>
                                        <label class="form-check-label" for="has_update">Ubah (Update)</label>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="has_delete" name="has_delete" value="1" checked>
                                        <label class="form-check-label" for="has_delete">Hapus (Delete)</label>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="has_export" name="has_export" value="1">
                                        <label class="form-check-label" for="has_export">Export (Unduh)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanMenu">
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
        const modalMenu = new bootstrap.Modal(document.getElementById('modalMenu'));

        // Live preview icon saat diketik
        $('#menu_icon').on('input', function() {
            const iconClass = $(this).val();
            $('#iconPreview').attr('class', iconClass || 'bi bi-app');
        });

        // Tombol Tambah Menu
        $('#btnTambahMenu').on('click', function() {
            $('#formMenu')[0].reset();
            $('#menu_id').val('');
            $('#menu_icon').val('bi bi-circle');
            $('#iconPreview').attr('class', 'bi bi-circle');
            $('#is_active').prop('checked', true);
            $('#has_create').prop('checked', true);
            $('#has_update').prop('checked', true);
            $('#has_delete').prop('checked', true);
            $('#has_export').prop('checked', false);
            $('.form-control').removeClass('is-invalid');
            $('#modalMenuTitle').html('<i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Menu Baru');
            modalMenu.show();
        });

        // Tombol Edit Menu
        $('.btn-edit-menu').on('click', function() {
            const menuId = $(this).data('id');
            $('.form-control').removeClass('is-invalid');

            $.ajax({
                url: "{{ url('/menus') }}/" + menuId + "/edit",
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    Swal.showLoading();
                },
                success: function(response) {
                    Swal.close();
                    if (response.status === 'success') {
                        const data = response.data;
                        $('#menu_id').val(data.id);
                        $('#menu_name').val(data.name);
                        $('#menu_code').val(data.code);
                        $('#parent_id').val(data.parent_id || '');
                        $('#menu_icon').val(data.icon);
                        $('#iconPreview').attr('class', data.icon);
                        $('#route_name').val(data.route_name || '');
                        $('#menu_url').val(data.url || '');
                        $('#order_index').val(data.order_index);
                        $('#is_active').prop('checked', !!data.is_active);
                        $('#has_create').prop('checked', !!data.has_create);
                        $('#has_update').prop('checked', !!data.has_update);
                        $('#has_delete').prop('checked', !!data.has_delete);
                        $('#has_export').prop('checked', !!data.has_export);

                        $('#modalMenuTitle').html('<i class="bi bi-pencil-square me-2 text-primary"></i>Edit Menu: ' + data.name);
                        modalMenu.show();
                    }
                },
                error: function() {
                    Swal.close();
                    Toast.fire({ icon: 'error', title: 'Gagal mengambil data menu.' });
                }
            });
        });

        // Submit Form Menu via jQuery AJAX
        $('#formMenu').on('submit', function(e) {
            e.preventDefault();
            $('.form-control').removeClass('is-invalid');

            const menuId = $('#menu_id').val();
            const isEdit = menuId !== '';
            const url = isEdit ? "{{ url('/menus') }}/" + menuId + "/update" : "{{ route('menus.store') }}";

            const formData = {
                name: $('#menu_name').val(),
                code: $('#menu_code').val(),
                parent_id: $('#parent_id').val(),
                icon: $('#menu_icon').val(),
                route_name: $('#route_name').val(),
                url: $('#menu_url').val(),
                order_index: $('#order_index').val(),
                is_active: $('#is_active').is(':checked') ? 1 : 0,
                has_create: $('#has_create').is(':checked') ? 1 : 0,
                has_update: $('#has_update').is(':checked') ? 1 : 0,
                has_delete: $('#has_delete').is(':checked') ? 1 : 0,
                has_export: $('#has_export').is(':checked') ? 1 : 0,
            };

            $('#btnSimpanMenu').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#btnSimpanMenu').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                    if (response.status === 'success') {
                        modalMenu.hide();
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
                    $('#btnSimpanMenu').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            $('#menu_' + field).addClass('is-invalid');
                            $('#' + field).addClass('is-invalid');
                            $('#error_' + field).text(messages[0]);
                        });
                    } else {
                        Toast.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Gagal menyimpan menu.' });
                    }
                }
            });
        });

        // Toggle Status Aktif Menu via jQuery AJAX
        $('.switch-toggle-menu').on('change', function() {
            const menuId = $(this).data('id');
            const switchEl = $(this);

            $.ajax({
                url: "{{ url('/menus') }}/" + menuId + "/toggle",
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Toast.fire({ icon: 'success', title: response.message });
                    }
                },
                error: function() {
                    switchEl.prop('checked', !switchEl.is(':checked'));
                    Toast.fire({ icon: 'error', title: 'Gagal mengubah status menu.' });
                }
            });
        });

        // Hapus Menu via SweetAlert2 & jQuery AJAX
        $('.btn-delete-menu').on('click', function() {
            const menuId = $(this).data('id');
            const menuName = $(this).data('name');

            Swal.fire({
                title: 'Hapus Menu?',
                text: `Menghapus menu "${menuName}" juga akan menghapus sub-menu dan relasi izin terkait. Lanjutkan?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('/menus') }}/" + menuId,
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
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menghapus menu.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
