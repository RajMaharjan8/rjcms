{{--
    Reference note shown in the BREAD builder. For no-code content types (no
    bound model) the builder creates the table and adds a column for each field
    automatically — additively; it never drops or retypes an existing column.
    The table below is just a reference for what gets created per field type.
--}}
<details class="rounded-md border border-wp-border bg-blue-50/60 text-sm text-wp-ink">
    <summary class="cursor-pointer px-4 py-3 font-semibold select-none">
        ⓘ What column each field type creates
    </summary>
    <div class="border-t border-wp-border px-4 py-3">
        <p class="mb-3 text-wp-muted">
            For a <strong>no-code content type</strong> (no model bound), the builder creates the table and a
            column for every field automatically when you save — and applies the right model cast. Adding a
            field only ever <strong>adds</strong> a column; removing a field leaves its column and data intact.
            This is what each type maps to:
        </p>

        <div class="overflow-hidden rounded border border-wp-border bg-white">
            <table class="min-w-full divide-y divide-wp-border-light">
                <thead class="bg-gray-50 text-left text-xs font-semibold tracking-wide text-wp-muted uppercase">
                    <tr>
                        <th class="px-3 py-2">Field type</th>
                        <th class="px-3 py-2">Migration column</th>
                        <th class="px-3 py-2">Model cast</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-wp-border-light font-mono text-xs">
                    <tr>
                        <td class="px-3 py-2">Text / Dropdown</td>
                        <td class="px-3 py-2">$table->string('col')->nullable();</td>
                        <td class="px-3 py-2 text-wp-muted">—</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Text area</td>
                        <td class="px-3 py-2">$table->text('col')->nullable();</td>
                        <td class="px-3 py-2 text-wp-muted">—</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Number</td>
                        <td class="px-3 py-2">$table->integer('col')->nullable();</td>
                        <td class="px-3 py-2 text-wp-muted">—</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Checkbox / Toggle</td>
                        <td class="px-3 py-2">$table->boolean('col')->default(false);</td>
                        <td class="px-3 py-2">'col' => 'boolean'</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Date</td>
                        <td class="px-3 py-2">$table->date('col')->nullable();</td>
                        <td class="px-3 py-2">'col' => 'date'</td>
                    </tr>
                    <tr class="bg-amber-50">
                        <td class="px-3 py-2">Image (single)</td>
                        <td class="px-3 py-2">$table->unsignedBigInteger('col')->nullable();</td>
                        <td class="px-3 py-2">'col' => SingleMediaCast::class</td>
                    </tr>
                    <tr class="bg-amber-50">
                        <td class="px-3 py-2">Image gallery</td>
                        <td class="px-3 py-2">$table->json('col')->nullable();</td>
                        <td class="px-3 py-2">'col' => MediaCollectionCast::class</td>
                    </tr>
                    <tr class="bg-amber-50">
                        <td class="px-3 py-2">Multi-select</td>
                        <td class="px-3 py-2">$table->json('col')->nullable();</td>
                        <td class="px-3 py-2">'col' => 'array'</td>
                    </tr>
                    <tr class="bg-amber-50">
                        <td class="px-3 py-2">Repeater</td>
                        <td class="px-3 py-2">$table->json('col')->nullable();</td>
                        <td class="px-3 py-2">'col' => 'array'</td>
                    </tr>
                    <tr class="bg-emerald-50">
                        <td class="px-3 py-2">Relationship (belongsTo)</td>
                        <td class="px-3 py-2">$table->unsignedBigInteger('related_id')->nullable();</td>
                        <td class="px-3 py-2 text-wp-muted">— (a real FK column)</td>
                    </tr>
                    <tr class="bg-emerald-50">
                        <td class="px-3 py-2">Relationship (others)</td>
                        <td class="px-3 py-2 text-wp-muted">— (uses the related table's FK / a pivot table)</td>
                        <td class="px-3 py-2 text-wp-muted">—</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ul class="mt-3 list-inside list-disc space-y-1 text-xs text-wp-muted">
            <li><strong>Image / Gallery</strong> store only media <em>ids</em> (pointing at <span class="font-mono">medias</span>), never the files. With the casts above, the column resolves straight to a <span class="font-mono">Media</span> model (single) or a <span class="font-mono">Collection</span> of them (gallery).</li>
            <li>Each upload keeps <strong>two files</strong>: the untouched original and a downsized (≤400px) compressed thumbnail. Access them via <span class="font-mono">$media->url</span> (full size) and <span class="font-mono">$media->thumbnail_url</span> (thumbnail — falls back to the original for SVG/PDF or non-images). Prefer <span class="font-mono">thumbnail_url</span> in lists/grids, <span class="font-mono">url</span> for the full view. Also available: <span class="font-mono">alt_text</span>, <span class="font-mono">width</span>, <span class="font-mono">height</span>.</li>
            <li><strong>Multi-select, Repeater</strong> store JSON and cast to <span class="font-mono">'array'</span>.</li>
            <li><strong>Relationship</strong> stores data the <em>real</em> relational way (a foreign-key column, the related table's FK, or a pivot table) based on the type you pick — belongsTo, hasOne, hasMany, belongsToMany. The picker reads/writes that storage, so a normal Eloquent relationship on your model reads the same data. The FK column / pivot table must already exist.</li>
            <li><strong>No-code content types</strong> (no model bound) get every cast above applied <strong>automatically</strong> — nothing to add.</li>
            <li>The field's <strong>column name</strong> in the builder must exactly match the migration column.</li>
        </ul>

        <p class="mt-4 mb-1 font-semibold">Bound your own model? Add the media casts:</p>
        @verbatim
            <pre class="overflow-x-auto rounded border border-wp-border bg-white p-3 font-mono text-xs leading-relaxed">use Rjcodes\Rjcms\Casts\SingleMediaCast;
use Rjcodes\Rjcms\Casts\MediaCollectionCast;

protected $casts = [
    'image'   => SingleMediaCast::class,     // single Image field
    'gallery' => MediaCollectionCast::class, // Image gallery field
];</pre>
        @endverbatim

        <p class="mt-3 mb-1 font-semibold">Relationships use real Eloquent methods — define them on your model and use as normal:</p>
        @verbatim
            <pre class="overflow-x-auto rounded border border-wp-border bg-white p-3 font-mono text-xs leading-relaxed">// On the Destination model:
public function tours()
{
    return $this->belongsToMany(Tour::class); // pivot: destination_tour
}

// In a view:
@foreach ($destination->tours as $tour)
    {{ $tour->name }} — {{ $tour->price }}
@endforeach</pre>
        @endverbatim

        <p class="mt-3 mb-1 font-semibold">Then fetch the images in a view:</p>
        @verbatim
            <pre class="overflow-x-auto rounded border border-wp-border bg-white p-3 font-mono text-xs leading-relaxed">{{-- single image --}}
{{-- thumbnail (compressed) → use thumbnail_url --}}
&lt;img src="{{ $record->image?->thumbnail_url }}" alt="{{ $record->image?->alt_text }}"&gt;
{{-- original (full size) → use url --}}
&lt;img src="{{ $record->image?->url }}" alt="{{ $record->image?->alt_text }}"&gt;

{{-- gallery: a Media collection, loop it --}}
@foreach ($record->gallery as $media)
    {{-- thumbnail_url for the grid, url for the full image --}}
    &lt;a href="{{ $media->url }}"&gt;
        &lt;img src="{{ $media->thumbnail_url }}" alt="{{ $media->alt_text }}"&gt;
    &lt;/a&gt;
@endforeach</pre>
        @endverbatim
    </div>
</details>
