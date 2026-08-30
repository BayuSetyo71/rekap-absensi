<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Kolom yang dapat diisi secara massal.
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'nip',
        'phone',
        'position',
        'department',
        'avatar',
        'is_active',
    ];

    /**
     * Kolom yang disembunyikan dalam serialisasi.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting atribut tipe data.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke role pengguna
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Relasi ke seluruh riwayat data absensi pengguna
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    /**
     * Mengecek apakah user memiliki role tertentu
     */
    public function hasRole(string|array $roles): bool
    {
        if (!$this->role) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($this->role->name, $roles);
        }

        return $this->role->name === $roles;
    }

    /**
     * Mengecek apakah user adalah Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Mengecek izin aksi pada menu tertentu
     *
     * @param string|int $menuIdentifier (menu code, route_name, atau menu_id)
     * @param string $action ('view', 'create', 'update', 'delete', 'export')
     * @return bool
     */
    public function canAccessMenu(string|int $menuIdentifier, string $action = 'view'): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!$this->role) {
            return false;
        }

        return $this->role->hasPermission($menuIdentifier, $action);
    }

    /**
     * Relasi ke unit-unit sekolah tempat mengajar / bertugas (Multi-Unit)
     */
    public function units(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'employee_units', 'user_id', 'unit_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Relasi ke pengaturan jadwal jam kerja harian spesifik pegawai
     */
    public function workSchedules(): HasMany
    {
        return $this->hasMany(EmployeeWorkSchedule::class, 'user_id')->orderBy('day_of_week', 'asc');
    }

    /**
     * Relasi ke sesi / slot mengajar harian spesifik guru
     */
    public function teachingSlots(): HasMany
    {
        return $this->hasMany(EmployeeTeachingSlot::class, 'user_id')->orderBy('start_time', 'asc');
    }

    /**
     * Relasi ke riwayat penggajian / payroll pegawai
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class, 'user_id')->orderBy('period_month', 'desc');
    }

    /**
     * Mendapatkan unit utama (homebase) pegawai
     */
    public function getPrimaryUnitAttribute(): ?Unit
    {
        return $this->units->firstWhere('pivot.is_primary', true) ?? $this->units->first();
    }

    /**
     * Mengecek apakah pegawai ini sudah dikonfigurasi jam kerjanya (memiliki minimal 1 hari aktif mengajar/bekerja)
     */
    public function hasConfiguredSchedule(): bool
    {
        // 1. Cek apakah memiliki slot mengajar aktif
        if ($this->relationLoaded('teachingSlots')) {
            if ($this->teachingSlots->isNotEmpty()) {
                return true;
            }
        } elseif ($this->teachingSlots()->exists()) {
            return true;
        }

        // 2. Cek apakah memiliki jadwal kerja harian yang aktif (tidak libur dan ada jam masuk)
        if ($this->relationLoaded('workSchedules')) {
            if ($this->workSchedules->where('is_day_off', false)->whereNotNull('time_in')->isNotEmpty()) {
                return true;
            }
        } elseif ($this->workSchedules()->where('is_day_off', false)->whereNotNull('time_in')->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Mendapatkan konfigurasi jam kerja pegawai untuk hari tertentu (1=Senin ... 7=Minggu)
     * Mendukung multi-slot mengajar fleksibel (misal SD 07:30-08:30 lalu SMP 08:30-09:30)
     *
     * @param int $dayOfWeek (1=Senin, 7=Minggu)
     * @return object
     */
    public function getWorkScheduleForDay(int $dayOfWeek): object
    {
        $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $dayName = $dayNames[$dayOfWeek] ?? 'Hari';

        // Jika pegawai sama sekali belum memiliki hari aktif di Senin - Minggu, anggap belum dikonfigurasi
        if (!$this->hasConfiguredSchedule()) {
            return (object) [
                'day_of_week'            => $dayOfWeek,
                'day_name'               => $dayName,
                'unit'                   => null,
                'unit_id'                => null,
                'time_in'                => null,
                'time_out'               => null,
                'late_tolerance_minutes' => 15,
                'is_day_off'             => in_array($dayOfWeek, [7]),
                'notes'                  => 'Belum Dikonfigurasi',
                'slots'                  => collect(),
                'slot_count'             => 0,
                'total_teaching_minutes' => 0,
                'is_multi_slot'          => false,
                'is_custom'              => false,
                'is_configured'          => false,
            ];
        }

        // 1. Cek apakah ada sesi/slot mengajar (Multi-Slot Teaching) pada hari tersebut
        $slots = $this->teachingSlots->where('day_of_week', $dayOfWeek)->sortBy('start_time');

        if ($slots->isNotEmpty()) {
            $firstSlot = $slots->first();
            $lastSlot = $slots->sortByDesc('end_time')->first();

            $totalMins = $slots->sum(fn($s) => $s->duration_minutes);

            return (object) [
                'day_of_week'            => $dayOfWeek,
                'day_name'               => $dayName,
                'unit'                   => $firstSlot->unit,
                'unit_id'                => $firstSlot->unit_id,
                'time_in'                => $firstSlot->start_time,
                'time_out'               => $lastSlot->end_time,
                'late_tolerance_minutes' => $firstSlot->unit?->default_late_tolerance ?? 15,
                'is_day_off'             => false,
                'notes'                  => $slots->pluck('subject')->filter()->implode(', ') ?: 'Sesi Mengajar',
                'slots'                  => $slots,
                'slot_count'             => $slots->count(),
                'total_teaching_minutes' => $totalMins,
                'is_multi_slot'          => $slots->count() > 1,
                'is_custom'              => true,
                'is_configured'          => true,
            ];
        }

        // Jika guru ini sudah diatur jadwal fleksibelnya (punya slot di hari lain), maka hari tanpa slot ini adalah LIBUR / KOSONG
        if ($this->teachingSlots->isNotEmpty()) {
            return (object) [
                'day_of_week'            => $dayOfWeek,
                'day_name'               => $dayName,
                'unit'                   => null,
                'unit_id'                => null,
                'time_in'                => null,
                'time_out'               => null,
                'late_tolerance_minutes' => 15,
                'is_day_off'             => true,
                'notes'                  => 'Libur / Tidak Mengajar',
                'slots'                  => collect(),
                'slot_count'             => 0,
                'total_teaching_minutes' => 0,
                'is_multi_slot'          => false,
                'is_custom'              => true,
                'is_configured'          => true,
            ];
        }

        // 2. Cek apakah ada jadwal jam kerja harian biasa
        $schedule = $this->workSchedules->firstWhere('day_of_week', $dayOfWeek);

        if ($schedule) {
            $isOff = (bool)$schedule->is_day_off || empty($schedule->time_in);

            return (object) [
                'day_of_week'            => $dayOfWeek,
                'day_name'               => $schedule->day_name ?: $dayName,
                'unit'                   => $schedule->unit,
                'unit_id'                => $schedule->unit_id,
                'time_in'                => $isOff ? null : $schedule->time_in,
                'time_out'               => $isOff ? null : $schedule->time_out,
                'late_tolerance_minutes' => $schedule->late_tolerance_minutes ?? 15,
                'is_day_off'             => $isOff,
                'notes'                  => $schedule->notes ?: ($isOff ? 'Libur' : ''),
                'slots'                  => collect(),
                'slot_count'             => 0,
                'total_teaching_minutes' => 0,
                'is_multi_slot'          => false,
                'is_custom'              => true,
                'is_configured'          => true,
            ];
        }

        // 3. Fallback jika belum dikonfigurasi
        return (object) [
            'day_of_week'            => $dayOfWeek,
            'day_name'               => $dayName,
            'unit'                   => null,
            'unit_id'                => null,
            'time_in'                => null,
            'time_out'               => null,
            'late_tolerance_minutes' => 15,
            'is_day_off'             => in_array($dayOfWeek, [7]),
            'notes'                  => 'Belum Dikonfigurasi',
            'slots'                  => collect(),
            'slot_count'             => 0,
            'total_teaching_minutes' => 0,
            'is_multi_slot'          => false,
            'is_custom'              => false,
            'is_configured'          => false,
        ];
    }

    /**
     * Mendapatkan konfigurasi jam kerja pegawai untuk tanggal tertentu
     *
     * @param \Carbon\Carbon|string $date
     * @return object
     */
    public function getWorkScheduleForDate($date): object
    {
        $carbonDate = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        $dayOfWeek = (int)$carbonDate->format('N'); // 1 (Senin) s.d. 7 (Minggu)

        return $this->getWorkScheduleForDay($dayOfWeek);
    }

    /**
     * Accessor untuk avatar url pengguna
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && file_exists(public_path('storage/' . $this->avatar))) {
            return asset('storage/' . $this->avatar);
        }

        // Default avatar menggunakan UI Avatars berdasarkan nama pengguna
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=4f46e5&color=fff&size=128';
    }
}
