<?php

namespace Rjcodes\Rjcms\Database\Seeders;

use Illuminate\Database\Seeder;
use Rjcodes\Rjcms\Enums\BreadFieldType;
use Rjcodes\Rjcms\Models\Bread;
use Rjcodes\Rjcms\Models\Post;

class BreadSeeder extends Seeder
{
    /**
     * Seed the demo "Posts" BREAD definition and its fields.
     */
    public function run(): void
    {
        $bread = Bread::updateOrCreate(
            ['slug' => 'posts'],
            [
                'name' => 'Post',
                'name_plural' => 'Posts',
                'model' => Post::class,
                'table_name' => 'posts',
                'icon' => 'document-text',
                'description' => 'Blog posts managed through the BREAD engine.',
                'order' => 1,
            ],
        );

        $fields = [
            ['column_name' => 'title', 'label' => 'Title', 'type' => BreadFieldType::Text, 'required' => true, 'order' => 1],
            ['column_name' => 'slug', 'label' => 'Slug', 'type' => BreadFieldType::Text, 'required' => true, 'order' => 2, 'in_browse' => false],
            ['column_name' => 'status', 'label' => 'Status', 'type' => BreadFieldType::Select, 'required' => true, 'order' => 3, 'options' => [
                'choices' => ['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'],
            ]],
            ['column_name' => 'excerpt', 'label' => 'Excerpt', 'type' => BreadFieldType::Textarea, 'required' => false, 'order' => 4, 'in_browse' => false],
            ['column_name' => 'body', 'label' => 'Body', 'type' => BreadFieldType::Textarea, 'required' => false, 'order' => 5, 'in_browse' => false],
            ['column_name' => 'featured_image', 'label' => 'Featured image', 'type' => BreadFieldType::Image, 'required' => false, 'order' => 6, 'in_browse' => false],
            ['column_name' => 'tags', 'label' => 'Tags', 'type' => BreadFieldType::MultiSelect, 'required' => false, 'order' => 7, 'options' => [
                'choices' => ['laravel' => 'Laravel', 'php' => 'PHP', 'livewire' => 'Livewire', 'tailwind' => 'Tailwind'],
            ]],
            ['column_name' => 'sections', 'label' => 'Content sections', 'type' => BreadFieldType::Repeater, 'required' => false, 'order' => 8, 'in_browse' => false, 'options' => [
                'subfields' => [
                    ['column_name' => 'heading', 'label' => 'Heading', 'type' => 'text', 'required' => false],
                    ['column_name' => 'body', 'label' => 'Body', 'type' => 'textarea', 'required' => false],
                ],
            ]],
            ['column_name' => 'is_published', 'label' => 'Published', 'type' => BreadFieldType::Boolean, 'required' => false, 'order' => 9],
            ['column_name' => 'published_at', 'label' => 'Publish date', 'type' => BreadFieldType::Date, 'required' => false, 'order' => 10],
        ];

        foreach ($fields as $field) {
            $bread->fields()->updateOrCreate(['column_name' => $field['column_name']], $field);
        }

        $bread->syncPermissions();
    }
}
