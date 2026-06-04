<?php

namespace Rjcodes\Rjcms\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Rjcodes\Rjcms\Enums\BreadFieldType;

/**
 * A site setting: a typed, grouped key/value pair. Settings reuse the BREAD
 * field types, so each one renders, validates, and persists like a BREAD field.
 */
#[Fillable(['group', 'key', 'display_name', 'type', 'value', 'options', 'required', 'order'])]
class Setting extends Model
{
    /** Cache key holding the flattened key => typed-value map. */
    private const CACHE_KEY = 'settings.values';

    /** Field types whose value is stored as a JSON array. */
    private const ARRAY_TYPES = ['images', 'multiselect', 'repeater'];

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
     * Flush the value cache whenever a setting changes.
     */
    protected static function booted(): void
    {
        $flush = fn () => Cache::forget(self::CACHE_KEY);
        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * Whether this setting is protected from deletion (e.g. core branding like
     * the site name and logo). Configured via `rjcms.locked_settings`.
     */
    public function isLocked(): bool
    {
        return in_array($this->key, config('rjcms.locked_settings', []), true);
    }

    /**
     * The field type enum for this setting.
     */
    public function fieldType(): BreadFieldType
    {
        return BreadFieldType::tryFrom($this->type) ?? BreadFieldType::Text;
    }

    /**
     * The form input name this setting's value submits under (no dots, so PHP
     * does not mangle it into an array key).
     */
    public function inputName(): string
    {
        return 'setting_'.$this->id;
    }

    /**
     * A transient BREAD field describing this setting, used to render its input
     * and to build its validation rules via the shared field handlers.
     */
    public function asField(): BreadField
    {
        return new BreadField([
            'column_name' => $this->inputName(),
            'label' => $this->display_name,
            'type' => $this->fieldType(),
            'required' => (bool) $this->required,
            'options' => $this->options ?? [],
        ]);
    }

    /**
     * The decoded value, shaped according to the setting's type.
     */
    public function typedValue(): mixed
    {
        if (in_array($this->type, self::ARRAY_TYPES, true)) {
            $decoded = json_decode((string) $this->value, true);

            return is_array($decoded) ? $decoded : [];
        }

        if ($this->type === 'boolean') {
            return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
        }

        return $this->value;
    }

    /**
     * Store a value, JSON-encoding array-shaped types.
     */
    public function storeValue(mixed $value): void
    {
        if (in_array($this->type, self::ARRAY_TYPES, true)) {
            $this->value = json_encode(is_array($value) ? array_values($value) : []);
        } elseif ($this->type === 'boolean') {
            $this->value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        } else {
            $this->value = filled($value) ? (string) $value : null;
        }

        $this->save();
    }

    /**
     * Resolve a setting's typed value by key, with an optional default. Cached.
     */
    public static function value(string $key, mixed $default = null): mixed
    {
        $values = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()
            ->get()
            ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->typedValue()])
            ->all());

        return $values[$key] ?? $default;
    }
}
