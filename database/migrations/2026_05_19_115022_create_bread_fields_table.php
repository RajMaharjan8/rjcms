<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Rjcodes\Rjcms\Models\Bread;

return new class extends Migration
{
    /**
     * A field maps one database column of a BREAD's model to a typed,
     * configurable form/display control.
     */
    public function up(): void
    {
        Schema::create('bread_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Bread::class)->constrained()->cascadeOnDelete();
            $table->string('column_name');
            $table->string('label');
            $table->string('type')->default('text');
            $table->boolean('required')->default(false);
            $table->json('options')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('in_browse')->default(true);
            $table->boolean('in_read')->default(true);
            $table->boolean('in_edit')->default(true);
            $table->boolean('in_add')->default(true);
            $table->timestamps();

            $table->unique(['bread_id', 'column_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bread_fields');
    }
};
