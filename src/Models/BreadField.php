<?php

namespace Rjcodes\Rjcms\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rjcodes\Rjcms\Bread\Fields\FieldContract;
use Rjcodes\Rjcms\Database\Factories\BreadFieldFactory;
use Rjcodes\Rjcms\Enums\BreadFieldType;

/**
 * One configurable field of a BREAD, mapped to a database column.
 */
#[Fillable([
    'bread_id',
    'column_name',
    'label',
    'group',
    'type',
    'required',
    'options',
    'order',
    'in_browse',
    'in_read',
    'in_edit',
    'in_add',
])]
class BreadField extends Model
{
    /** @use HasFactory<BreadFieldFactory> */
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return BreadFieldFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => BreadFieldType::class,
            'required' => 'boolean',
            'options' => 'array',
            'order' => 'integer',
            'in_browse' => 'boolean',
            'in_read' => 'boolean',
            'in_edit' => 'boolean',
            'in_add' => 'boolean',
        ];
    }

    /**
     * The BREAD this field belongs to.
     *
     * @return BelongsTo<Bread, $this>
     */
    public function bread(): BelongsTo
    {
        return $this->belongsTo(Bread::class);
    }

    /**
     * The type handler responsible for this field's behaviour.
     */
    public function handler(): FieldContract
    {
        return $this->type->handler();
    }

    /**
     * A single option value, e.g. select choices or repeater sub-fields.
     */
    public function option(string $key, mixed $default = null): mixed
    {
        return data_get($this->options, $key, $default);
    }
}
