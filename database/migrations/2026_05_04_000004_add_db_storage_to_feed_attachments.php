<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrate feed_attachments from AWS S3 to database-native BLOB storage.
 *
 * Changes:
 *   - file_url        → made nullable (legacy S3 records keep their URL; new DB records set null)
 *   - file_data       → BLOB (SQLite) / LONGBLOB (MySQL/MariaDB): stores the raw binary file content
 *   - uploader_id     → FK to users: records who uploaded the file
 *   - storage_type    → 'db' | 's3'; defaults to 'db'; existing rows set to 's3' for backward compat
 *
 * Backward compatibility: every existing row keeps its file_url value and gets storage_type='s3'.
 * The FeedAttachment model's url accessor routes serving accordingly.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Step 1: add new columns (cross-DB compatible DDL) ─────────────────
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            // MySQL/MariaDB: use LONGBLOB (4 GB capacity) via raw statement
            // since Laravel's binary() maps to BLOB which is only 64 KB.
            DB::statement('ALTER TABLE feed_attachments
                ADD COLUMN storage_type  VARCHAR(10)  NOT NULL DEFAULT \'db\' AFTER uploaded_at,
                ADD COLUMN uploader_id   BIGINT UNSIGNED NULL               AFTER storage_type,
                ADD COLUMN file_data     LONGBLOB NULL                      AFTER uploader_id
            ');
            // Make file_url nullable for DB-stored records that have no URL
            DB::statement('ALTER TABLE feed_attachments MODIFY COLUMN file_url VARCHAR(2048) NULL');
        } else {
            // SQLite / PostgreSQL (bytea handled as binary string)
            Schema::table('feed_attachments', function (Blueprint $table) {
                $table->string('storage_type', 10)->default('db')->after('uploaded_at');
                $table->unsignedBigInteger('uploader_id')->nullable()->after('storage_type');
                // binary() → BLOB in SQLite (up to 2 GB), bytea in PostgreSQL
                $table->binary('file_data')->nullable()->after('uploader_id');
                $table->string('file_url', 2048)->nullable()->change();
            });
        }

        // ── Step 2: tag every existing row as legacy S3 storage ───────────────
        // Rows uploaded before this migration have valid S3 URLs in file_url.
        // Mark them so the accessor knows to serve them from S3 rather than DB.
        DB::table('feed_attachments')
            ->whereNotNull('file_url')
            ->where('file_url', '!=', '')
            ->update(['storage_type' => 's3']);
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE feed_attachments
                DROP COLUMN file_data,
                DROP COLUMN uploader_id,
                DROP COLUMN storage_type
            ');
            DB::statement('ALTER TABLE feed_attachments MODIFY COLUMN file_url VARCHAR(2048) NOT NULL');
        } else {
            Schema::table('feed_attachments', function (Blueprint $table) {
                $table->dropColumn(['file_data', 'uploader_id', 'storage_type']);
                $table->string('file_url', 2048)->nullable(false)->change();
            });
        }
    }
};
