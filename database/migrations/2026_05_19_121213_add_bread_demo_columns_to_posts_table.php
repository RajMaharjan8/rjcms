<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columns demonstrating the image, select, multi-select and repeater
     * BREAD field types on the Posts content type.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('featured_image')->nullable()->after('body')
                ->constrained('medias')->nullOnDelete();
            $table->string('status')->default('draft')->after('featured_image');
            $table->json('tags')->nullable()->after('status');
            $table->json('sections')->nullable()->after('tags');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('featured_image');
            $table->dropColumn(['status', 'tags', 'sections']);
        });
    }
};
