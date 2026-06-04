<?php

namespace Rjcodes\Rjcms\Enums;

use Illuminate\Database\Schema\Blueprint;
use Rjcodes\Rjcms\Bread\Fields\BooleanField;
use Rjcodes\Rjcms\Bread\Fields\DateField;
use Rjcodes\Rjcms\Bread\Fields\FieldContract;
use Rjcodes\Rjcms\Bread\Fields\ImageField;
use Rjcodes\Rjcms\Bread\Fields\ImagesField;
use Rjcodes\Rjcms\Bread\Fields\MultiSelectField;
use Rjcodes\Rjcms\Bread\Fields\NumberField;
use Rjcodes\Rjcms\Bread\Fields\RelationshipField;
use Rjcodes\Rjcms\Bread\Fields\RepeaterField;
use Rjcodes\Rjcms\Bread\Fields\SelectField;
use Rjcodes\Rjcms\Bread\Fields\TextareaField;
use Rjcodes\Rjcms\Bread\Fields\TextField;
use Rjcodes\Rjcms\Casts\MediaCollectionCast;
use Rjcodes\Rjcms\Casts\SingleMediaCast;

/**
 * The field types a BREAD field may be configured as.
 */
enum BreadFieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Number = 'number';
    case Boolean = 'boolean';
    case Toggle = 'toggle';
    case Date = 'date';
    case Image = 'image';
    case Images = 'images';
    case Select = 'select';
    case MultiSelect = 'multiselect';
    case Relationship = 'relationship';
    case Repeater = 'repeater';

    /**
     * A human-readable label for the type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text field',
            self::Textarea => 'Text area',
            self::Number => 'Number',
            self::Boolean => 'Checkbox',
            self::Toggle => 'Toggle (on/off)',
            self::Date => 'Date',
            self::Image => 'Image',
            self::Images => 'Image gallery',
            self::Select => 'Dropdown',
            self::MultiSelect => 'Multi-select',
            self::Relationship => 'Relationship',
            self::Repeater => 'Repeater',
        };
    }

    /**
     * The handler responsible for validating and persisting this type.
     */
    public function handler(): FieldContract
    {
        return match ($this) {
            self::Text => new TextField,
            self::Textarea => new TextareaField,
            self::Number => new NumberField,
            self::Boolean, self::Toggle => new BooleanField,
            self::Date => new DateField,
            self::Image => new ImageField,
            self::Images => new ImagesField,
            self::Select => new SelectField,
            self::MultiSelect => new MultiSelectField,
            self::Relationship => new RelationshipField,
            self::Repeater => new RepeaterField,
        };
    }

    /**
     * The Blade partial that renders this type's form input.
     */
    public function formView(): string
    {
        return "rjcms::admin.bread.fields.{$this->value}";
    }

    /**
     * The Eloquent attribute cast this type needs on its backing column, or
     * null when the raw scalar value is fine as-is.
     */
    public function castType(): ?string
    {
        return match ($this) {
            self::Boolean, self::Toggle => 'boolean',
            self::Date => 'date',
            self::Image => SingleMediaCast::class,
            self::Images => MediaCollectionCast::class,
            self::MultiSelect, self::Repeater => 'array',
            default => null,
        };
    }

    /**
     * Add this type's backing column to a table blueprint. Columns are always
     * nullable (or defaulted) so they can be added to a table that already
     * holds rows without needing a back-fill.
     */
    public function defineColumn(Blueprint $table, string $name): void
    {
        match ($this) {
            self::Textarea => $table->text($name)->nullable(),
            self::Number => $table->integer($name)->nullable(),
            self::Boolean, self::Toggle => $table->boolean($name)->default(false),
            self::Date => $table->date($name)->nullable(),
            self::Image, self::Relationship => $table->unsignedBigInteger($name)->nullable(),
            self::Images, self::MultiSelect, self::Repeater => $table->json($name)->nullable(),
            default => $table->string($name)->nullable(), // Text, Select
        };
    }
}
