<?php

namespace Rjcodes\Rjcms\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Rjcodes\Rjcms\Enums\BreadFieldType;

/**
 * A typed custom field attached to a built-in content type (Pages or Blogs).
 * Values are stored in the owner's JSON `meta` column under the field's key.
 */
#[Fillable(['group', 'key', 'label', 'type', 'options', 'required', 'order'])]
class CustomField extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'required' => 'boolean',
            'order' => 'integer',
        ];
    }

    /**
     * Custom fields for a content type group, ordered for display.
     *
     * @return Collection<int, CustomField>
     */
    public static function forGroup(string $group)
    {
        return static::where('group', $group)->orderBy('order')->orderBy('id')->get();
    }

    /**
     * The form input name this field submits under (flat, then nested into meta).
     */
    public function inputName(): string
    {
        return 'cf_'.$this->key;
    }

    /**
     * A transient BREAD field used to render this custom field's input and to
     * build its validation rules via the shared field handlers.
     */
    public function asField(): BreadField
    {
        return new BreadField([
            'column_name' => $this->inputName(),
            'label' => $this->label,
            'type' => BreadFieldType::tryFrom($this->type) ?? BreadFieldType::Text,
            'required' => (bool) $this->required,
            'options' => $this->options ?? [],
        ]);
    }
}
