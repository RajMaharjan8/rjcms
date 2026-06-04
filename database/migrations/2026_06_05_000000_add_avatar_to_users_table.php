<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add an optional avatar (media id) to the users table. Guarded so it is safe
 * on a host app that already has a `users` table — and a no-op if re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || Schema::hasColumn('users', 'avatar')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('avatar')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('avatar');
            });
        }
    }
};
