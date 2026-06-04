<?php

namespace Rjcodes\Rjcms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Permission model with helpers for the `{action}_{group}` naming convention,
 * e.g. `view_users` has action `view` and group `users`.
 */
class Permission extends SpatiePermission
{
    /**
     * The action portion of the permission name (before the first underscore).
     *
     * @return Attribute<string, never>
     */
    protected function action(): Attribute
    {
        return Attribute::get(fn (): string => Str::before((string) $this->name, '_'));
    }

    /**
     * The resource group a permission belongs to (after the first underscore).
     *
     * @return Attribute<string, never>
     */
    protected function group(): Attribute
    {
        return Attribute::get(fn (): string => str_contains((string) $this->name, '_')
            ? Str::after((string) $this->name, '_')
            : 'other');
    }
}
