<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A BREAD definition maps a configurable CRUD UI onto a database table —
     * either a dedicated Eloquent model, or (when `model` is null) the generic
     * schema-driven BreadRecord, whose columns the builder manages itself.
     */
    public function up(): void
    {
        Schema::create('breads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_plural');
            $table->string('slug')->unique();
            $table->string('model')->nullable();
            $table->string('table_name');
            $table->string('icon')->nullable();
            $table->string('description')->nullable();
            $table->unsignedInteger('order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breads');
    }
};
