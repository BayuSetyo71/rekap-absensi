-- -------------------------------------------------------------
-- SQL Database Bersih Siap Pakai (Schema + Initial Seeders)
-- Sistem Absensi & Payroll
-- -------------------------------------------------------------

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. TABEL ROLES & DATA
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `roles_name_unique`(`name` ASC)
) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', 'Super Admin', 'Akses penuh ke seluruh sistem tanpa batasan otorisasi.', 1, NOW(), NOW()),
(2, 'admin', 'Administrator', 'Akses administrasi sistem dan manajemen hak akses pengguna.', 1, NOW(), NOW()),
(3, 'user', 'Karyawan / Pegawai', 'Akses standar pengguna untuk fitur harian dan presensi.', 0, NOW(), NOW());

-- 2. TABEL UNITS & DATA
DROP TABLE IF EXISTS `units`;
CREATE TABLE `units` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#4f46e5',
  `default_time_in` time NOT NULL DEFAULT '07:00:00',
  `default_time_out` time NOT NULL DEFAULT '14:00:00',
  `default_late_tolerance` smallint UNSIGNED NOT NULL DEFAULT 15,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `units_code_unique`(`code` ASC)
) ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `units` (`id`, `code`, `name`, `color`, `default_time_in`, `default_time_out`, `default_late_tolerance`, `is_active`, `description`, `created_at`, `updated_at`) VALUES
(1, 'MI', 'Madrasah Ibtidaiyah (MI)', '#059669', '07:00:00', '13:30:00', 15, 1, 'Unit Pendidikan Dasar MI', NOW(), NOW()),
(2, 'MTS', 'Madrasah Tsanawiyah (MTs)', '#2563eb', '07:00:00', '14:00:00', 15, 1, 'Unit Pendidikan Menengah Pertama MTs', NOW(), NOW()),
(3, 'MA', 'Madrasah Aliyah (MA)', '#7c3aed', '07:00:00', '14:30:00', 15, 1, 'Unit Pendidikan Menengah Atas MA', NOW(), NOW()),
(4, 'PONDOK', 'Pondok Pesantren / Asrama', '#d97706', '06:00:00', '21:00:00', 15, 1, 'Kegiatan Pengasuhan & Asrama', NOW(), NOW()),
(5, 'TAHFIDZ', 'Program Tahfidz Al-Qur\'an', '#dc2626', '05:00:00', '20:00:00', 15, 1, 'Program Khusus Tahfidzul Qur\'an', NOW(), NOW());

-- 3. TABEL MENUS & DATA
DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` bigint UNSIGNED NULL DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `route_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bi bi-circle',
  `order_index` int NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `has_create` tinyint(1) NOT NULL DEFAULT 1,
  `has_update` tinyint(1) NOT NULL DEFAULT 1,
  `has_delete` tinyint(1) NOT NULL DEFAULT 1,
  `has_export` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `menus_code_unique`(`code` ASC),
  INDEX `menus_parent_id_foreign`(`parent_id` ASC),
  CONSTRAINT `menus_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 17 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `menus` (`id`, `parent_id`, `code`, `name`, `route_name`, `url`, `icon`, `order_index`, `is_active`, `has_create`, `has_update`, `has_delete`, `has_export`, `created_at`, `updated_at`) VALUES
(1, NULL, 'dashboard', 'Portal Menu', 'dashboard', '/dashboard', 'bi bi-grid-fill', 1, 1, 0, 0, 0, 0, NOW(), NOW()),
(2, NULL, 'schedule-group', 'Jadwal & Jam Kerja', NULL, NULL, 'bi bi-calendar2-range-fill', 2, 1, 0, 0, 0, 0, NOW(), NOW()),
(3, 2, 'my-schedule', 'Jadwal Mengajar Saya', 'my-schedule.index', '/my-schedule', 'bi bi-calendar2-week-fill', 1, 1, 0, 0, 0, 0, NOW(), NOW()),
(4, 2, 'employee-schedule-matrix', 'Matriks Jadwal Pegawai', 'schedules.matrix', '/schedules/matrix', 'bi bi-calendar3-range', 2, 1, 1, 1, 1, 1, NOW(), NOW()),
(5, 2, 'unit-work-schedules', 'Jam Kerja Unit & Lembaga', 'unit-schedules.index', '/unit-schedules', 'bi bi-clock-history', 3, 1, 1, 1, 1, 0, NOW(), NOW()),
(6, NULL, 'attendance-group', 'Presensi & Kehadiran', NULL, NULL, 'bi bi-clock-fill', 3, 1, 0, 0, 0, 0, NOW(), NOW()),
(7, 6, 'my-attendance', 'Presensi Mandiri', 'my-attendance.index', '/my-attendance', 'bi bi-person-check-fill', 1, 1, 1, 0, 0, 0, NOW(), NOW()),
(8, 6, 'attendance-records', 'Rekap Presensi Harian', 'attendance.index', '/attendance', 'bi bi-table', 2, 1, 1, 1, 1, 1, NOW(), NOW()),
(9, 6, 'attendance-reports', 'Laporan Presensi', 'reports.attendance', '/reports/attendance', 'bi bi-file-earmark-bar-graph-fill', 3, 1, 0, 0, 0, 1, NOW(), NOW()),
(10, NULL, 'payroll-group', 'Honor & Penggajian', NULL, NULL, 'bi bi-cash-stack', 4, 1, 0, 0, 0, 0, NOW(), NOW()),
(11, 10, 'my-payslip', 'Slip Gaji Saya', 'my-payroll.index', '/my-payroll', 'bi bi-receipt-cutoff', 1, 1, 0, 0, 0, 1, NOW(), NOW()),
(12, 10, 'payroll-management', 'Kalkulasi Payroll & Honor', 'payroll.index', '/payroll', 'bi bi-calculator-fill', 2, 1, 1, 1, 1, 1, NOW(), NOW()),
(13, 10, 'teaching-rates', 'Tarif Honor Mengajar', 'teaching-rates.index', '/teaching-rates', 'bi bi-tags-fill', 3, 1, 1, 1, 1, 0, NOW(), NOW()),
(14, NULL, 'master-data-group', 'Master Data & Akses', NULL, NULL, 'bi bi-gear-wide-connected', 5, 1, 0, 0, 0, 0, NOW(), NOW()),
(15, 14, 'units-management', 'Data Lembaga / Unit', 'units.index', '/units', 'bi bi-buildings-fill', 1, 1, 1, 1, 1, 0, NOW(), NOW()),
(16, 14, 'users-management', 'Data Pegawai & Guru', 'users.index', '/users', 'bi bi-people-fill', 2, 1, 1, 1, 1, 1, NOW(), NOW());

-- 4. TABEL USERS & DATA AWAL
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` bigint UNSIGNED NULL DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `users_email_unique`(`email` ASC),
  UNIQUE INDEX `users_username_unique`(`username` ASC),
  UNIQUE INDEX `users_nip_unique`(`nip` ASC),
  INDEX `users_role_id_foreign`(`role_id` ASC),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Password default: "password"
INSERT INTO `users` (`id`, `role_id`, `name`, `username`, `nip`, `email`, `phone`, `position`, `department`, `avatar`, `is_active`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 'Super Administrator', 'superadmin', 'SA-001', 'admin@absensi.com', '081234567890', 'IT System Administrator', 'Information Technology', NULL, 1, NOW(), '$2y$12$K1rS2B750a9zBf41Nq.j9eIqgUa3UuI0LkW80r7W.8Kx3K2C9R5v2', NULL, NOW(), NOW()),
(2, 2, 'Admin HRD', 'adminhrd', 'HR-002', 'admin2@absensi.com', '081298765432', 'HR Operations Staff', 'Human Resources', NULL, 1, NOW(), '$2y$12$K1rS2B750a9zBf41Nq.j9eIqgUa3UuI0LkW80r7W.8Kx3K2C9R5v2', NULL, NOW(), NOW()),
(3, 3, 'Ahmad Fauzi', 'ahmadfauzi', 'KY-1001', 'user@absensi.com', '085712345678', 'Staff Operasional', 'Operasional', NULL, 1, NOW(), '$2y$12$K1rS2B750a9zBf41Nq.j9eIqgUa3UuI0LkW80r7W.8Kx3K2C9R5v2', NULL, NOW(), NOW());

-- 5. TABEL SISTEM & CACHE LARAVEL
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  INDEX `cache_expiration_index`(`expiration` ASC)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  INDEX `cache_locks_expiration_index`(`expiration` ASC)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `failed_jobs_uuid_unique`(`uuid` ASC),
  INDEX `failed_jobs_connection_queue_failed_at_index`(`connection` ASC, `queue` ASC, `failed_at` ASC)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `cancelled_at` int NULL DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED NULL DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `jobs_queue_index`(`queue` ASC)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 12 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_01_000001_create_roles_table', 1),
(5, '2026_01_01_000002_create_menus_table', 1),
(6, '2026_01_01_000003_create_role_menu_permissions_table', 1),
(7, '2026_01_01_000004_add_role_and_details_to_users_table', 1),
(8, '2026_01_01_000005_create_attendances_table', 1),
(9, '2026_01_01_000006_create_work_schedules_and_units_tables', 1),
(10, '2026_01_01_000007_create_employee_teaching_slots_table', 1),
(11, '2026_01_01_000008_create_teaching_rates_and_payrolls_tables', 1);

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NULL DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `sessions_user_id_index`(`user_id` ASC),
  INDEX `sessions_last_activity_index`(`last_activity` ASC)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 6. TABEL RELASI & PERMISSIONS
DROP TABLE IF EXISTS `role_menu_permissions`;
CREATE TABLE `role_menu_permissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` bigint UNSIGNED NOT NULL,
  `menu_id` bigint UNSIGNED NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_update` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `can_export` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `role_menu_unique`(`role_id` ASC, `menu_id` ASC),
  INDEX `role_menu_permissions_menu_id_foreign`(`menu_id` ASC),
  CONSTRAINT `role_menu_permissions_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `role_menu_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Permission data for Admin & User
INSERT INTO `role_menu_permissions` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_update`, `can_delete`, `can_export`, `created_at`, `updated_at`)
SELECT 2, id, 1, has_create, has_update, has_delete, has_export, NOW(), NOW() FROM `menus`;

INSERT INTO `role_menu_permissions` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_update`, `can_delete`, `can_export`, `created_at`, `updated_at`)
SELECT 3, id, 1, has_create, 0, 0, has_export, NOW(), NOW() FROM `menus` WHERE `code` IN ('dashboard', 'schedule-group', 'my-schedule', 'attendance-group', 'my-attendance', 'payroll-group', 'my-payslip');

-- 7. TABEL UNIT SCHEDULES & RATES
DROP TABLE IF EXISTS `unit_work_schedules`;
CREATE TABLE `unit_work_schedules` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `unit_id` bigint UNSIGNED NOT NULL,
  `day_of_week` tinyint UNSIGNED NOT NULL,
  `day_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_in` time NULL DEFAULT NULL,
  `time_out` time NULL DEFAULT NULL,
  `late_tolerance_minutes` smallint UNSIGNED NOT NULL DEFAULT 15,
  `is_day_off` tinyint(1) NOT NULL DEFAULT 0,
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `unit_day_unique`(`unit_id` ASC, `day_of_week` ASC),
  CONSTRAINT `unit_work_schedules_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `teaching_rates`;
CREATE TABLE `teaching_rates` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `unit_id` bigint UNSIGNED NOT NULL,
  `subject_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DEFAULT',
  `rate_per_hour` decimal(12, 2) NOT NULL DEFAULT 0.00,
  `rate_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'per_hour',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `unit_subject_rate_idx`(`unit_id` ASC, `subject_name` ASC, `is_active` ASC),
  CONSTRAINT `teaching_rates_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `teaching_rates` (`unit_id`, `subject_name`, `rate_per_hour`, `rate_type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'DEFAULT', 35000.00, 'per_hour', 1, NOW(), NOW()),
(2, 'DEFAULT', 40000.00, 'per_hour', 1, NOW(), NOW()),
(3, 'DEFAULT', 45000.00, 'per_hour', 1, NOW(), NOW()),
(4, 'DEFAULT', 50000.00, 'per_hour', 1, NOW(), NOW()),
(5, 'DEFAULT', 50000.00, 'per_hour', 1, NOW(), NOW());

-- 8. TABEL TRANSAKSI & DETAIL (AWAL BERSIH / KOSONG DATA)
DROP TABLE IF EXISTS `employee_units`;
CREATE TABLE `employee_units` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `employee_unit_unique`(`user_id` ASC, `unit_id` ASC),
  INDEX `employee_units_unit_id_foreign`(`unit_id` ASC),
  CONSTRAINT `employee_units_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `employee_units_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `employee_work_schedules`;
CREATE TABLE `employee_work_schedules` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `day_of_week` tinyint UNSIGNED NOT NULL,
  `day_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_id` bigint UNSIGNED NULL DEFAULT NULL,
  `schedule_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default_unit',
  `time_in` time NULL DEFAULT NULL,
  `time_out` time NULL DEFAULT NULL,
  `late_tolerance_minutes` smallint UNSIGNED NOT NULL DEFAULT 15,
  `is_day_off` tinyint(1) NOT NULL DEFAULT 0,
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `employee_day_unique`(`user_id` ASC, `day_of_week` ASC),
  INDEX `employee_work_schedules_unit_id_foreign`(`unit_id` ASC),
  CONSTRAINT `employee_work_schedules_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `employee_work_schedules_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `employee_teaching_slots`;
CREATE TABLE `employee_teaching_slots` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `day_of_week` tinyint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NULL DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `subject` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `order_index` smallint UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `employee_teaching_slots_unit_id_foreign`(`unit_id` ASC),
  INDEX `user_day_slots_idx`(`user_id` ASC, `day_of_week` ASC),
  CONSTRAINT `employee_teaching_slots_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `employee_teaching_slots_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `attendances`;
CREATE TABLE `attendances` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in` time NULL DEFAULT NULL,
  `check_out` time NULL DEFAULT NULL,
  `status` enum('hadir','terlambat','izin','sakit','alpa') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'hadir',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_by` bigint UNSIGNED NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `user_attendance_date_unique`(`user_id` ASC, `attendance_date` ASC),
  INDEX `attendances_created_by_foreign`(`created_by` ASC),
  CONSTRAINT `attendances_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payrolls`;
CREATE TABLE `payrolls` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `period_month` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_present_days` int UNSIGNED NOT NULL DEFAULT 0,
  `total_sessions_taught` int UNSIGNED NOT NULL DEFAULT 0,
  `total_hours_taught` decimal(8, 2) NOT NULL DEFAULT 0.00,
  `gross_teaching_amount` decimal(15, 2) NOT NULL DEFAULT 0.00,
  `total_allowances` decimal(15, 2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(15, 2) NOT NULL DEFAULT 0.00,
  `net_salary` decimal(15, 2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','locked','paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `processed_by` bigint UNSIGNED NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `user_period_payroll_unique`(`user_id` ASC, `period_month` ASC),
  INDEX `payrolls_processed_by_foreign`(`processed_by` ASC),
  INDEX `period_status_idx`(`period_month` ASC, `status` ASC),
  CONSTRAINT `payrolls_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `payrolls_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payroll_details`;
CREATE TABLE `payroll_details` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `payroll_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NULL DEFAULT NULL,
  `subject` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_sessions` int UNSIGNED NOT NULL DEFAULT 0,
  `total_hours` decimal(8, 2) NOT NULL DEFAULT 0.00,
  `rate_applied` decimal(12, 2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(15, 2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `payroll_details_unit_id_foreign`(`unit_id` ASC),
  INDEX `payroll_details_payroll_id_index`(`payroll_id` ASC),
  CONSTRAINT `payroll_details_payroll_id_foreign` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `payroll_details_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payroll_adjustments`;
CREATE TABLE `payroll_adjustments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `payroll_id` bigint UNSIGNED NOT NULL,
  `type` enum('allowance','deduction') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12, 2) NOT NULL DEFAULT 0.00,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `payroll_adjustments_payroll_id_index`(`payroll_id` ASC),
  CONSTRAINT `payroll_adjustments_payroll_id_foreign` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
