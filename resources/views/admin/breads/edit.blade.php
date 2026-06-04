@extends('rjcms::layouts.admin')

@section('title', 'Configure ' . $bread->name_plural . ' · ' . config('app.name'))
@section('page-title', 'Configure: ' . $bread->name_plural)

@section('page-actions')
    <x-admin.wp-button variant="secondary" :href="route('admin.bread.index', $bread)">Open content</x-admin.wp-button>
@endsection

@section('content')
@php($state = json_decode(old('payload', ''), true) ?: $state)
@php($inputClass = 'w-full rounded border border-wp-border bg-white px-3 py-1.5 text-sm text-wp-ink shadow-sm transition focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none')

@if ($errors->any())
    <div class="mb-6 rounded border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm text-red-700">
        <p class="font-semibold">Please fix the following:</p>
        <ul class="mt-1 list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.breads.update', $bread) }}"
      @submit="$refs.payload.value = JSON.stringify(form)"
      x-data="{
          form: @js($state),
          open: {},
          typeLabels: @js(collect($fieldTypes)->mapWithKeys(fn ($t) => [$t->value => $t->label()])),
          columnInfo: {
              text: 'VARCHAR (string)', textarea: 'TEXT', number: 'INTEGER',
              boolean: 'BOOLEAN (default false)', toggle: 'BOOLEAN (default false)', date: 'DATE',
              image: 'BIGINT — a media id', images: 'JSON — array of media ids',
              select: 'VARCHAR (string)', multiselect: 'JSON — array',
              relationship: 'belongsTo: a BIGINT FK column · others: no column (related table / pivot)',
              repeater: 'JSON',
          },
          blankField() {
              return { uid: crypto.randomUUID(), column_name: '', label: '', group: '', type: 'text',
                  required: false, in_browse: true, in_read: true, in_edit: true, in_add: true,
                  choices: [],
                  relationship_type: 'belongs_to', related_model: '', display_label: 'name',
                  foreign_key: '', related_key: 'id', pivot_table: '', related_pivot_key: '', taggable: false,
                  subfields: [] };
          },
          groupNames() {
              return [...new Set(this.form.fields.map(f => (f.group || '').trim()).filter(Boolean))];
          },
          isOpen(field) { return this.open[field.uid] ?? false; },
          toggle(field) { this.open[field.uid] = ! this.isOpen(field); },
          addField() { const f = this.blankField(); this.form.fields.push(f); this.open[f.uid] = true; },
          removeField(i) { this.form.fields.splice(i, 1); },
          moveField(i, dir) {
              const j = i + dir;
              if (j < 0 || j >= this.form.fields.length) return;
              [this.form.fields[i], this.form.fields[j]] = [this.form.fields[j], this.form.fields[i]];
          },
          reorder(uid, position) {
              const from = this.form.fields.findIndex(f => f.uid === uid);
              if (from === -1 || from === position) return;
              const [moved] = this.form.fields.splice(from, 1);
              this.form.fields.splice(position, 0, moved);
          },
          addChoice(field) { field.choices.push({ key: '', label: '' }); },
          removeChoice(field, j) { field.choices.splice(j, 1); },
          addSubfield(parent) { parent.subfields.push({ column_name: '', label: '', type: 'text', required: false, choices: [], subfields: [] }); },
          removeSubfield(parent, j) { parent.subfields.splice(j, 1); },

          /* ---- Relationship modal (Voyager-style) ---- */
          relOpen: false,
          relIndex: null,
          rel: {},
          blankRelationship() {
              return { uid: crypto.randomUUID(), type: 'relationship', column_name: '', label: '', group: '',
                  required: false, in_browse: true, in_read: true, in_edit: true, in_add: true, choices: [], subfields: [],
                  relationship_type: 'belongs_to', related_model: '', display_label: 'name',
                  foreign_key: '', related_key: 'id', pivot_table: '', related_pivot_key: '', taggable: false };
          },
          modelName(fqn) { return (fqn || '').split('\\\\').pop() || 'related'; },
          openRelationship(index = null) {
              this.relIndex = index;
              this.rel = index === null ? this.blankRelationship() : JSON.parse(JSON.stringify(this.form.fields[index]));
              this.relOpen = true;
          },
          saveRelationship() {
              const r = this.rel;
              if (! r.related_model) { return; }
              const base = this.modelName(r.related_model).replace(/([a-z])([A-Z])/g, '$1_$2').toLowerCase();
              if (! r.column_name) {
                  r.column_name = r.relationship_type === 'belongs_to' ? base + '_id'
                      : (['has_many', 'belongs_to_many'].includes(r.relationship_type) ? base + 's' : base);
              }
              if (! r.label) { r.label = r.column_name.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()); }
              if (this.relIndex === null) { this.form.fields.push(r); } else { this.form.fields.splice(this.relIndex, 1, r); }
              this.relOpen = false;
          },
      }"
      class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
    @csrf
    @method('PUT')
    <input type="hidden" name="payload" x-ref="payload">

    {{-- Main column --}}
    <div class="flex flex-col gap-6">
        {{-- Settings --}}
        <x-admin.postbox title="Content Type Settings" body-class="px-5 py-1">
            <div class="divide-y divide-wp-border-light">
                <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-center sm:gap-4">
                    <label class="text-sm font-semibold text-wp-ink">Name (singular)</label>
                    <input type="text" x-model="form.name" class="{{ $inputClass }}">
                </div>
                <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-center sm:gap-4">
                    <label class="text-sm font-semibold text-wp-ink">Name (plural)</label>
                    <input type="text" x-model="form.name_plural" class="{{ $inputClass }}">
                </div>
                <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-center sm:gap-4">
                    <label class="text-sm font-semibold text-wp-ink">Slug</label>
                    <div>
                        <input type="text" x-model="form.slug" class="{{ $inputClass }}">
                        <p class="mt-1 text-xs text-wp-muted">Used in the admin URL, e.g. <span class="font-mono">/admin/content/<span x-text="form.slug || 'slug'"></span></span></p>
                    </div>
                </div>
                <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-center sm:gap-4">
                    <label class="text-sm font-semibold text-wp-ink">Menu icon</label>
                    <input type="text" x-model="form.icon" placeholder="document-text" class="{{ $inputClass }}">
                </div>
                <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                    <label class="pt-1.5 text-sm font-semibold text-wp-ink">Description</label>
                    <textarea x-model="form.description" rows="2" class="{{ $inputClass }}"></textarea>
                </div>
                <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                    <label class="pt-0.5 text-sm font-semibold text-wp-ink">Permissions</label>
                    <label class="flex items-start gap-2 text-sm text-wp-ink">
                        <input type="checkbox" x-model="form.use_permissions" class="mt-0.5 rounded border-wp-border text-wp-blue focus:ring-wp-blue">
                        <span>
                            Guard this content type with permissions
                            <span class="mt-0.5 block text-xs font-normal text-wp-muted">When off, every admin can access it and its permissions are removed.</span>
                        </span>
                    </label>
                </div>
            </div>
        </x-admin.postbox>

        @include('rjcms::admin.breads._column-types-help')

        {{-- Fields --}}
        <x-admin.postbox title="Fields" body-class="p-4">
            <x-slot:actions>
                <span class="text-xs text-wp-muted" x-text="`${form.fields.length} field${form.fields.length === 1 ? '' : 's'}`"></span>
                <x-admin.wp-button type="button" size="sm" variant="ghost" @click="openRelationship()">⚭ Create a Relationship</x-admin.wp-button>
                <x-admin.wp-button type="button" size="sm" @click="addField()">+ Add Field</x-admin.wp-button>
            </x-slot:actions>

            <div class="flex flex-col gap-2"
                 x-sort="reorder($item, $position)"
                 x-sort:config="{ ghostClass: 'opacity-40' }">
                <template x-for="(field, index) in form.fields" :key="field.uid">
                    <div class="rounded border border-wp-border bg-white" x-sort:item="field.uid">
                        {{-- Collapsed header bar --}}
                        <div class="flex items-center gap-3 px-3 py-2.5"
                             :class="isOpen(field) ? 'border-b border-wp-border-light bg-gray-50' : ''">
                            <span x-sort:handle class="cursor-grab text-wp-border select-none active:cursor-grabbing" title="Drag to reorder">⠿</span>

                            <button type="button" @click="field.type === 'relationship' ? openRelationship(index) : toggle(field)" class="flex min-w-0 flex-1 items-center gap-2 text-left">
                                <span class="text-wp-muted transition" x-show="field.type !== 'relationship'" :class="isOpen(field) ? 'rotate-90' : ''">›</span>
                                <span class="text-wp-muted" x-show="field.type === 'relationship'">⚭</span>
                                <span class="truncate text-sm font-semibold text-wp-ink" x-text="field.label || field.column_name || 'New Field'"></span>
                                <span class="truncate font-mono text-xs text-wp-muted" x-show="field.column_name" x-text="field.column_name"></span>
                            </button>

                            <span class="inline-flex items-center gap-1 rounded-sm bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700"
                                  x-show="(field.group || '').trim()" x-text="'⊞ ' + (field.group || '').trim()"></span>
                            <span class="rounded-sm bg-blue-50 px-2 py-0.5 text-xs font-medium text-wp-blue" x-text="typeLabels[field.type] || field.type"></span>

                            <div class="flex items-center gap-1 text-wp-muted">
                                <button type="button" @click="moveField(index, -1)" :disabled="index === 0"
                                        class="rounded px-1.5 py-0.5 hover:bg-gray-100 disabled:opacity-30" title="Move up">↑</button>
                                <button type="button" @click="moveField(index, 1)" :disabled="index === form.fields.length - 1"
                                        class="rounded px-1.5 py-0.5 hover:bg-gray-100 disabled:opacity-30" title="Move down">↓</button>
                                <button type="button" @click="removeField(index)"
                                        class="rounded px-1.5 py-0.5 text-red-600 hover:bg-red-50" title="Remove field">&times;</button>
                            </div>
                        </div>

                        {{-- Expanded body --}}
                        <div x-show="isOpen(field)" x-collapse class="p-4">
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-semibold text-wp-ink">Column name</label>
                                    <input type="text" x-model="field.column_name" class="{{ $inputClass }}">
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-semibold text-wp-ink">Label</label>
                                    <input type="text" x-model="field.label" class="{{ $inputClass }}">
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-semibold text-wp-ink">Type</label>
                                    <select x-model="field.type" class="{{ $inputClass }}">
                                        @foreach ($fieldTypes as $type)
                                            @continue ($type === \Rjcodes\Rjcms\Enums\BreadFieldType::Relationship)
                                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-wp-muted">
                                        <span class="font-semibold">Column:</span>
                                        <span class="font-mono" x-text="columnInfo[field.type] || '—'"></span>
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-col gap-1 sm:max-w-xs">
                                <label class="text-xs font-semibold text-wp-ink">Group <span class="font-normal text-wp-muted">(optional)</span></label>
                                <input type="text" x-model="field.group" list="bread-field-groups" placeholder="e.g. SEO" class="{{ $inputClass }}">
                                <p class="text-xs text-wp-muted">Fields sharing a group appear together in one panel on the add/edit form.</p>
                                <datalist id="bread-field-groups">
                                    <template x-for="name in groupNames()" :key="name">
                                        <option :value="name"></option>
                                    </template>
                                </datalist>
                            </div>

                            <div class="mt-4 rounded bg-gray-50 px-3 py-2.5">
                                <p class="mb-2 text-xs font-semibold tracking-wide text-wp-muted uppercase">Visible in</p>
                                <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm text-wp-ink">
                                    <label class="flex items-center gap-1.5">
                                        <input type="checkbox" x-model="field.required"
                                               class="rounded border-wp-border text-wp-blue focus:ring-wp-blue"> Required
                                    </label>
                                    <label class="flex items-center gap-1.5">
                                        <input type="checkbox" x-model="field.in_browse"
                                               class="rounded border-wp-border text-wp-blue focus:ring-wp-blue"> Browse (list)
                                    </label>
                                    <label class="flex items-center gap-1.5">
                                        <input type="checkbox" x-model="field.in_read"
                                               class="rounded border-wp-border text-wp-blue focus:ring-wp-blue"> Read
                                    </label>
                                    <label class="flex items-center gap-1.5">
                                        <input type="checkbox" x-model="field.in_edit"
                                               class="rounded border-wp-border text-wp-blue focus:ring-wp-blue"> Edit
                                    </label>
                                    <label class="flex items-center gap-1.5">
                                        <input type="checkbox" x-model="field.in_add"
                                               class="rounded border-wp-border text-wp-blue focus:ring-wp-blue"> Add
                                    </label>
                                </div>
                            </div>

                            {{-- Choices editor for select / multi-select --}}
                            <template x-if="['select', 'multiselect'].includes(field.type)">
                                <div class="mt-4 rounded border border-wp-border-light bg-gray-50 p-3">
                                    <p class="mb-2 text-xs font-semibold tracking-wide text-wp-muted uppercase">Choices</p>
                                    <div class="flex flex-col gap-2">
                                        <template x-for="(choice, j) in field.choices" :key="j">
                                            <div class="flex gap-2">
                                                <input type="text" x-model="choice.key" placeholder="value"
                                                       class="w-1/3 rounded border border-wp-border px-2 py-1.5 text-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">
                                                <input type="text" x-model="choice.label" placeholder="label"
                                                       class="flex-1 rounded border border-wp-border px-2 py-1.5 text-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">
                                                <button type="button" @click="removeChoice(field, j)"
                                                        class="px-2 text-sm text-red-600 hover:text-red-500">&times;</button>
                                            </div>
                                        </template>
                                    </div>
                                    <button type="button" @click="addChoice(field)"
                                            class="mt-2 text-xs font-semibold text-wp-blue hover:text-wp-blue-hover">+ Add choice</button>
                                </div>
                            </template>

                            {{-- Sub-field editor for repeater (recursive, depth-limited) --}}
                            <template x-if="field.type === 'repeater'">
                                <div class="mt-4">
                                    @include('rjcms::admin.breads._subfield-editor', [
                                        'parent' => 'field',
                                        'var' => 'sub',
                                        'depth' => 0,
                                        'maxDepth' => $maxNestDepth,
                                        'fieldTypes' => $subfieldTypes,
                                    ])
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Empty state --}}
                <div x-show="form.fields.length === 0"
                     class="rounded border border-dashed border-wp-border bg-gray-50 px-4 py-10 text-center">
                    <p class="text-sm font-medium text-wp-ink">No fields yet</p>
                    <p class="mt-1 text-xs text-wp-muted">Add your first field to start building this content type.</p>
                    <x-admin.wp-button type="button" size="sm" class="mt-3" @click="addField()">+ Add Field</x-admin.wp-button>
                </div>
            </div>
        </x-admin.postbox>
    </div>

    {{-- Sidebar --}}
    <div class="flex flex-col gap-6 lg:sticky lg:top-6">
        <x-admin.postbox title="Save">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs font-semibold tracking-wide text-wp-muted uppercase">Model</dt>
                    <dd class="mt-0.5 font-mono text-xs break-all text-wp-ink">{{ $bread->model }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold tracking-wide text-wp-muted uppercase">Database table</dt>
                    <dd class="mt-0.5 font-mono text-xs text-wp-ink">{{ $bread->table_name }}</dd>
                </div>
            </dl>

            <x-slot:footer>
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('admin.breads.index') }}" class="text-sm font-medium text-wp-muted hover:text-wp-ink">Cancel</a>
                    <x-admin.wp-button type="submit">Save Changes</x-admin.wp-button>
                </div>
            </x-slot:footer>
        </x-admin.postbox>

        <x-admin.postbox title="Help">
            <ul class="space-y-2 text-xs leading-relaxed text-wp-muted">
                <li><span class="font-semibold text-wp-ink">Column name</span> must match a database column on the bound table.</li>
                <li><span class="font-semibold text-wp-ink">Browse / Read / Edit / Add</span> control where each field appears in the content screens.</li>
                <li>Drag the <span class="font-mono">⠿</span> handle or use the arrows to reorder fields.</li>
            </ul>
        </x-admin.postbox>
    </div>

    {{-- Relationship modal (Voyager-style) --}}
    <div x-show="relOpen" x-cloak class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/50 p-4 py-10"
         @keydown.escape.window="relOpen = false">
        <div @click.outside="relOpen = false" x-transition
             class="w-full max-w-2xl overflow-hidden rounded-lg bg-white shadow-xl">
            <div class="flex items-center justify-between bg-wp-blue px-5 py-3 text-white">
                <h3 class="flex items-center gap-2 text-sm font-semibold">⚭ Relationship</h3>
                <button type="button" @click="relOpen = false" class="text-white/80 hover:text-white">&times;</button>
            </div>

            <div class="space-y-4 px-5 py-5">
                <div class="grid items-end gap-3 sm:grid-cols-[auto_1fr_1fr]">
                    <span class="rounded bg-gray-100 px-3 py-2 text-sm font-semibold text-wp-ink">{{ $bread->name }}</span>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-wp-ink">Type</label>
                        <select x-model="rel.relationship_type" class="{{ $inputClass }}">
                            <option value="belongs_to">Belongs To</option>
                            <option value="has_one">Has One</option>
                            <option value="has_many">Has Many</option>
                            <option value="belongs_to_many">Belongs To Many</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-wp-ink">Related model</label>
                        <select x-model="rel.related_model" class="{{ $inputClass }}">
                            <option value="">— Select a model —</option>
                            @foreach ($models as $model)
                                <option value="{{ $model }}">{{ class_basename($model) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- belongsTo: the FK column lives on THIS table --}}
                <template x-if="rel.relationship_type === 'belongs_to'">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-wp-ink">Which column on <span x-text="'{{ $bread->name }}'"></span> stores the related id?</label>
                        <input type="text" x-model="rel.column_name" :placeholder="(modelName(rel.related_model)||'related').toLowerCase() + '_id'" class="{{ $inputClass }} font-mono">
                        <p class="text-xs text-wp-muted">A foreign-key column on this table. The builder creates it if it doesn't exist.</p>
                    </div>
                </template>

                {{-- hasOne / hasMany: FK lives on the related table --}}
                <template x-if="['has_one', 'has_many'].includes(rel.relationship_type)">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-wp-ink">Which column on <span x-text="modelName(rel.related_model)"></span> references <span x-text="'{{ $bread->name }}'"></span>?</label>
                        <input type="text" x-model="rel.foreign_key" placeholder="e.g. {{ Str::snake($bread->name) }}_id" class="{{ $inputClass }} font-mono">
                        <p class="text-xs text-wp-muted">Leave blank to use the convention <span class="font-mono">{{ Str::snake($bread->name) }}_id</span>.</p>
                    </div>
                </template>

                {{-- belongsToMany: just the pivot table — keys are derived by convention --}}
                <template x-if="rel.relationship_type === 'belongs_to_many'">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-wp-ink">Pivot table</label>
                        <input type="text" x-model="rel.pivot_table" :placeholder="'e.g. {{ Str::singular(Str::snake($bread->name)) }}_' + (modelName(rel.related_model)||'related').toLowerCase()" class="{{ $inputClass }} font-mono">
                        <p class="text-xs text-wp-muted">The existing join table linking the two models. Pivot keys are derived by convention (<span class="font-mono">{{ Str::snake($bread->name) }}_id</span>, <span class="font-mono">model_id</span>).</p>
                    </div>
                </template>

                <div class="rounded border border-wp-border-light bg-gray-50 p-3">
                    <p class="mb-2 text-xs font-semibold tracking-wide text-wp-muted uppercase">Selection details</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-wp-ink">Display the <span x-text="modelName(rel.related_model)"></span> (label column)</label>
                            <input type="text" x-model="rel.display_label" placeholder="name" class="{{ $inputClass }} font-mono">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-wp-ink">Store the <span x-text="modelName(rel.related_model)"></span> (key column)</label>
                            <input type="text" x-model="rel.related_key" placeholder="id" class="{{ $inputClass }} font-mono">
                        </div>
                        <template x-if="rel.relationship_type === 'belongs_to_many'">
                            <label class="flex items-center gap-1.5 text-sm text-wp-ink">
                                <input type="checkbox" x-model="rel.taggable" class="rounded border-wp-border text-wp-blue focus:ring-wp-blue"> Allow tagging
                            </label>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-wp-border-light bg-gray-50 px-5 py-3">
                <button type="button" @click="relOpen = false" class="text-sm font-medium text-wp-muted hover:text-wp-ink">Cancel</button>
                <x-admin.wp-button type="button" @click="saveRelationship()"
                                   x-text="relIndex === null ? '＋ Add relationship' : 'Save relationship'">＋ Add relationship</x-admin.wp-button>
            </div>
        </div>
    </div>
</form>
@endsection
