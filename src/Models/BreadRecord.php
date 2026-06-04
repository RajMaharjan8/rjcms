<?php

namespace Rjcodes\Rjcms\Models;

use Illuminate\Database\Eloquent\Model;
use Rjcodes\Rjcms\Bread\SchemaManager;

/**
 * A generic, schema-driven record for BREAD content types that have no
 * dedicated model class. The table is assigned at runtime by
 * {@see Bread::newRecord()}, and the attribute casts are derived from the
 * BREAD's field definitions — so a content type needs no PHP model at all.
 */
class BreadRecord extends Model
{
    protected $guarded = [];

    /**
     * Casts come from the BREAD's field types, keyed by the (runtime) table.
     *
     * Resolved here rather than in casts(), because that method runs in the
     * constructor — before newRecord() assigns the table — whereas getCasts()
     * is evaluated lazily on attribute access, once the table is known. The
     * lookup is memoised per table, so repeated calls are cheap.
     *
     * @return array<string, string>
     */
    public function getCasts()
    {
        return array_merge(parent::getCasts(), SchemaManager::castsFor($this->getTable()));
    }
}
