<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The old builder had a separate `relationships` (multiple) field type that
     * stored ids in a JSON column. That type no longer exists; fold any leftover
     * rows into the single `relationship` type as a belongsTo so they keep
     * loading. Their data should be reconfigured in the builder.
     */
    public function up(): void
    {
        if (! Schema::hasTable('bread_fields')) {
            return;
        }

        DB::table('bread_fields')->where('type', 'relationships')->get()->each(function ($row): void {
            $options = json_decode($row->options ?: '{}', true) ?: [];
            $options['relationship_type'] = 'belongs_to';

            DB::table('bread_fields')->where('id', $row->id)->update([
                'type' => 'relationship',
                'options' => json_encode($options),
            ]);
        });
    }

    public function down(): void
    {
        // One-way data fix; nothing to reverse.
    }
};
