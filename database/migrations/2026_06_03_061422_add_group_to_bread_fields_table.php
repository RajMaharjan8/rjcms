<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An optional group name. Fields sharing a group are rendered together in
     * one titled panel on the record add/edit form.
     */
    public function up(): void
    {
        Schema::table('bread_fields', function (Blueprint $table) {
            $table->string('group')->nullable()->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('bread_fields', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};
