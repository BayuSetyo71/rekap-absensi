@extends('layouts.app')

@section('title', 'Matriks Izin Role: ' . $role->display_name)
@section('page-title', 'Matriks Izin Role')
@section('page-subtitle', 'Atur hak akses granular CRUD per menu untuk role: ' . $role->display_name)

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Role
                    </a>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-shield-check text-primary me-1"></i> Hak Akses Role: <span class="text-primary">{{ $role->display_name }}</span> (<code>{{ $role->name }}</code>)
                        </h6>
                    </div>
                </div>

                <!-- Quick Action Buttons -->
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnCheckAllMaster">
                        <i class="bi bi-check-all me-1"></i> Centang Semua
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnUncheckAllMaster">
                        <i class="bi bi-x-lg me-1"></i> Hapus Semua Centang
                    </button>
                </div>
            </div>

            @if($role->name === 'superadmin')
                <div class="p-3 bg-light border-bottom">
                    <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
                        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                        <div>
                            <strong>Catatan Khusus Super Admin:</strong> Role <code>superadmin</code> memiliki akses mutlak ke seluruh menu dan seluruh aksi (Create, Read, Update, Delete, Export) secara otomatis di level sistem.
                        </div>
                    </div>
                </div>
            @endif

            <form id="formPermissions" action="{{ route('roles.permissions.update', $role->id) }}" method="POST">
                @csrf
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablePermissions">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 240px;">Nama Menu</th>
                                    <th style="min-width: 140px;">Kode / Route</th>
                                    <th class="text-center" style="width: 110px;">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" id="checkAllRowMaster" title="Pilih Semua Kolom">
                                            <label class="form-check-label fw-bold small text-muted ms-1" for="checkAllRowMaster">Semua Baris</label>
                                        </div>
                                    </th>
                                    <!-- Column View -->
                                    <th class="text-center" style="width: 100px;">
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="badge badge-subtle-primary mb-1">LIHAT (READ)</span>
                                            <input class="form-check-input check-col" type="checkbox" data-col="view" id="col_view" title="Centang Semua Lihat">
                                        </div>
                                    </th>
                                    <!-- Column Create -->
                                    <th class="text-center" style="width: 100px;">
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="badge badge-subtle-success mb-1">TAMBAH</span>
                                            <input class="form-check-input check-col" type="checkbox" data-col="create" id="col_create" title="Centang Semua Tambah">
                                        </div>
                                    </th>
                                    <!-- Column Update -->
                                    <th class="text-center" style="width: 100px;">
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="badge badge-subtle-warning mb-1">UBAH</span>
                                            <input class="form-check-input check-col" type="checkbox" data-col="update" id="col_update" title="Centang Semua Ubah">
                                        </div>
                                    </th>
                                    <!-- Column Delete -->
                                    <th class="text-center" style="width: 100px;">
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="badge badge-subtle-danger mb-1">HAPUS</span>
                                            <input class="form-check-input check-col" type="checkbox" data-col="delete" id="col_delete" title="Centang Semua Hapus">
                                        </div>
                                    </th>
                                    <!-- Column Export -->
                                    <th class="text-center" style="width: 100px;">
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="badge badge-subtle-info mb-1">EXPORT</span>
                                            <input class="form-check-input check-col" type="checkbox" data-col="export" id="col_export" title="Centang Semua Export">
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($menus as $menu)
                                    @php
                                        $perm = $permissions->get($menu->id);
                                        $canView = $perm?->can_view ?? false;
                                        $canCreate = $perm?->can_create ?? false;
                                        $canUpdate = $perm?->can_update ?? false;
                                        $canDelete = $perm?->can_delete ?? false;
                                        $canExport = $perm?->can_export ?? false;
                                    @endphp
                                    <!-- Baris Menu Utama -->
                                    <tr class="table-row-menu bg-white" data-menu-id="{{ $menu->id }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="{{ $menu->icon }} text-primary fs-5 me-2"></i>
                                                <div>
                                                    <span class="fw-bold text-dark">{{ $menu->name }}</span>
                                                    @if(!$menu->is_active)
                                                        <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">Nonaktif</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="text-muted small">{{ $menu->code }}</code>
                                        </td>
                                        <!-- Checkbox Pilih Semua per Baris -->
                                        <td class="text-center">
                                            <input class="form-check-input check-row" type="checkbox" data-menu-id="{{ $menu->id }}" title="Pilih Semua Aksi untuk Menu Ini">
                                        </td>
                                        <!-- View (Read) -->
                                        <td class="text-center">
                                            <input class="form-check-input perm-cb perm-view" type="checkbox" name="permissions[{{ $menu->id }}][view]" value="1" {{ $canView ? 'checked' : '' }} data-col="view" data-menu-id="{{ $menu->id }}">
                                        </td>
                                        <!-- Create -->
                                        <td class="text-center">
                                            @if($menu->has_create)
                                                <input class="form-check-input perm-cb perm-create" type="checkbox" name="permissions[{{ $menu->id }}][create]" value="1" {{ $canCreate ? 'checked' : '' }} data-col="create" data-menu-id="{{ $menu->id }}">
                                            @else
                                                <span class="text-muted small">&mdash;</span>
                                            @endif
                                        </td>
                                        <!-- Update -->
                                        <td class="text-center">
                                            @if($menu->has_update)
                                                <input class="form-check-input perm-cb perm-update" type="checkbox" name="permissions[{{ $menu->id }}][update]" value="1" {{ $canUpdate ? 'checked' : '' }} data-col="update" data-menu-id="{{ $menu->id }}">
                                            @else
                                                <span class="text-muted small">&mdash;</span>
                                            @endif
                                        </td>
                                        <!-- Delete -->
                                        <td class="text-center">
                                            @if($menu->has_delete)
                                                <input class="form-check-input perm-cb perm-delete" type="checkbox" name="permissions[{{ $menu->id }}][delete]" value="1" {{ $canDelete ? 'checked' : '' }} data-col="delete" data-menu-id="{{ $menu->id }}">
                                            @else
                                                <span class="text-muted small">&mdash;</span>
                                            @endif
                                        </td>
                                        <!-- Export -->
                                        <td class="text-center">
                                            @if($menu->has_export)
                                                <input class="form-check-input perm-cb perm-export" type="checkbox" name="permissions[{{ $menu->id }}][export]" value="1" {{ $canExport ? 'checked' : '' }} data-col="export" data-menu-id="{{ $menu->id }}">
                                            @else
                                                <span class="text-muted small">&mdash;</span>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Sub-Menu Children jika ada -->
                                    @foreach($menu->children as $child)
                                        @php
                                            $childPerm = $permissions->get($child->id);
                                            $cView = $childPerm?->can_view ?? false;
                                            $cCreate = $childPerm?->can_create ?? false;
                                            $cUpdate = $childPerm?->can_update ?? false;
                                            $cDelete = $childPerm?->can_delete ?? false;
                                            $cExport = $childPerm?->can_export ?? false;
                                        @endphp
                                        <tr class="table-row-menu bg-light bg-opacity-50" data-menu-id="{{ $child->id }}">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center ms-3">
                                                    <i class="bi bi-arrow-return-right text-muted me-2"></i>
                                                    <i class="{{ $child->icon }} text-secondary fs-6 me-2"></i>
                                                    <span class="fw-semibold text-dark">{{ $child->name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <code class="text-muted small">{{ $child->code }}</code>
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input check-row" type="checkbox" data-menu-id="{{ $child->id }}" title="Pilih Semua Aksi untuk Sub-Menu Ini">
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input perm-cb perm-view" type="checkbox" name="permissions[{{ $child->id }}][view]" value="1" {{ $cView ? 'checked' : '' }} data-col="view" data-menu-id="{{ $child->id }}">
                                            </td>
                                            <td class="text-center">
                                                @if($child->has_create)
                                                    <input class="form-check-input perm-cb perm-create" type="checkbox" name="permissions[{{ $child->id }}][create]" value="1" {{ $cCreate ? 'checked' : '' }} data-col="create" data-menu-id="{{ $child->id }}">
                                                @else
                                                    <span class="text-muted small">&mdash;</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($child->has_update)
                                                    <input class="form-check-input perm-cb perm-update" type="checkbox" name="permissions[{{ $child->id }}][update]" value="1" {{ $cUpdate ? 'checked' : '' }} data-col="update" data-menu-id="{{ $child->id }}">
                                                @else
                                                    <span class="text-muted small">&mdash;</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($child->has_delete)
                                                    <input class="form-check-input perm-cb perm-delete" type="checkbox" name="permissions[{{ $child->id }}][delete]" value="1" {{ $cDelete ? 'checked' : '' }} data-col="delete" data-menu-id="{{ $child->id }}">
                                                @else
                                                    <span class="text-muted small">&mdash;</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($child->has_export)
                                                    <input class="form-check-input perm-cb perm-export" type="checkbox" name="permissions[{{ $child->id }}][export]" value="1" {{ $cExport ? 'checked' : '' }} data-col="export" data-menu-id="{{ $child->id }}">
                                                @else
                                                    <span class="text-muted small">&mdash;</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light d-flex justify-content-between align-items-center p-3">
                    <span class="text-muted small">
                        <i class="bi bi-shield-lock me-1"></i> Perubahan izin akan langsung berdampak pada akses menu dan tombol aksi pengguna.
                    </span>
                    <button type="submit" class="btn btn-primary px-4" id="btnSimpanPermissions">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Matriks Hak Akses
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
        // 1. Centang Semua Master Button
        $('#btnCheckAllMaster').on('click', function() {
            $('.perm-cb').prop('checked', true);
            $('.check-row').prop('checked', true);
            $('.check-col').prop('checked', true);
            Toast.fire({ icon: 'info', title: 'Semua izin dipilih.' });
        });

        // 2. Hapus Semua Centang Master Button
        $('#btnUncheckAllMaster').on('click', function() {
            $('.perm-cb').prop('checked', false);
            $('.check-row').prop('checked', false);
            $('.check-col').prop('checked', false);
            Toast.fire({ icon: 'info', title: 'Semua izin dibatalkan.' });
        });

        // 3. Centang Semua Kolom (Header Checkbox)
        $('.check-col').on('change', function() {
            const col = $(this).data('col');
            const isChecked = $(this).is(':checked');
            $(`.perm-${col}`).prop('checked', isChecked);
        });

        // 4. Centang Semua Baris (Row Checkbox)
        $('.check-row').on('change', function() {
            const menuId = $(this).data('menu-id');
            const isChecked = $(this).is(':checked');
            $(`tr[data-menu-id="${menuId}"] .perm-cb`).prop('checked', isChecked);
        });

        // 5. Otomatis centang View (Read) jika Create/Update/Delete/Export dicentang
        $('.perm-create, .perm-update, .perm-delete, .perm-export').on('change', function() {
            if ($(this).is(':checked')) {
                const menuId = $(this).data('menu-id');
                $(`tr[data-menu-id="${menuId}"] .perm-view`).prop('checked', true);
            }
        });

        // 6. Submit Permissions Form via jQuery AJAX
        $('#formPermissions').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const url = form.attr('action');
            const formData = form.serialize();

            $('#btnSimpanPermissions').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan Izin...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#btnSimpanPermissions').prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Simpan Matriks Hak Akses');
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Tersimpan!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    $('#btnSimpanPermissions').prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Simpan Matriks Hak Akses');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan hak akses.'
                    });
                }
            });
        });
    });
</script>
@endsection
