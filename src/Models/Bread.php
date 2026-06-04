<?php

namespace Rjcodes\Rjcms\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Rjcodes\Rjcms\Database\Factories\BreadFactory;
use Spatie\Permission\Models\Role;

/**
 * A BREAD definition: a configurable CRUD UI bound to an existing model.
 */
#[Fillable(['name', 'name_plural', 'slug', 'model', 'table_name', 'icon', 'description', 'order', 'use_permissions'])]
class Bread extends Model
{
    /** @use HasFactory<BreadFactory> */
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return BreadFactory::new();
    }

    /**
     * The BREAD actions a field's visibility can be scoped to.
     *
     * @var list<string>
     */
    public const CONTEXTS = ['browse', 'read', 'edit', 'add'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'use_permissions' => 'boolean',
        ];
    }

    /**
     * Resolve route bindings by slug rather than id.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * The configured fields for this BREAD, ordered for display.
     *
     * @return HasMany<BreadField, $this>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(BreadField::class)->orderBy('order');
    }

    /**
     * Fields visible in the given BREAD context (browse, read, edit, add).
     *
     * @return Collection<int, BreadField>
     */
    public function fieldsFor(string $context): Collection
    {
        return $this->fields->where("in_{$context}", true)->values();
    }

    /**
     * Whether this BREAD has no dedicated model class and is therefore backed
     * by the generic, schema-driven {@see BreadRecord} (its columns are
     * managed automatically by the builder).
     */
    public function usesGenericModel(): bool
    {
        return $this->model === null || $this->model === '' || ! class_exists($this->model);
    }

    /**
     * A fresh, empty instance of the BREAD's underlying model — either its
     * bound model class, or a generic record pointed at its table.
     */
    public function newRecord(): Model
    {
        if ($this->usesGenericModel()) {
            return (new BreadRecord)->setTable($this->table_name);
        }

        $class = $this->model;

        return new $class;
    }

    /**
     * The permission name guarding the given action on this BREAD.
     */
    public function permission(string $action): string
    {
        return "{$action}_{$this->slug}";
    }

    /**
     * Whether access to this BREAD is guarded by permissions.
     */
    public function isPermissioned(): bool
    {
        return (bool) $this->use_permissions;
    }

    /**
     * Create this BREAD's CRUD permissions and grant them to super admins, or
     * remove them when the BREAD has opted out of permissions.
     */
    public function syncPermissions(): void
    {
        $names = collect(['browse', 'view', 'create', 'edit', 'delete'])
            ->map(fn (string $action): string => $this->permission($action));

        if (! $this->isPermissioned()) {
            Permission::whereIn('name', $names)->get()->each->delete();

            return;
        }

        $permissions = $names->map(fn (string $name): Permission => Permission::findOrCreate($name, 'web'));

        Role::where('name', config('rjcms.super_admin_role', 'super_admin'))->first()?->givePermissionTo($permissions);
    }
}
