{{--
    Rich-text editor for every <textarea data-rich-editor>, using the full
    open-source CKEditor 5 CDN build (no API key, GPL). Provides headings,
    formatting, tables, source-code editing, and image upload straight into the
    media library. Loaded once per page.
--}}
@once
    @php($ckVersion = '43.3.1')
    @push('scripts')
        <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/{{ $ckVersion }}/ckeditor5.css">
        <script type="module">
            import {
                ClassicEditor, Essentials, Paragraph, Heading,
                Bold, Italic, Underline, Link, List, BlockQuote, Alignment,
                Table, TableToolbar, TableProperties, TableCellProperties,
                Image, ImageToolbar, ImageStyle, ImageCaption, ImageResize, ImageUpload, SimpleUploadAdapter,
                SourceEditing, GeneralHtmlSupport, PasteFromOffice,
            } from 'https://cdn.ckeditor.com/ckeditor5/{{ $ckVersion }}/ckeditor5.js';

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            document.querySelectorAll('textarea[data-rich-editor]').forEach(function (el) {
                ClassicEditor
                    .create(el, {
                        plugins: [
                            Essentials, Paragraph, Heading, Bold, Italic, Underline, Link, List, BlockQuote, Alignment,
                            Table, TableToolbar, TableProperties, TableCellProperties,
                            Image, ImageToolbar, ImageStyle, ImageCaption, ImageResize, ImageUpload, SimpleUploadAdapter,
                            SourceEditing, GeneralHtmlSupport, PasteFromOffice,
                        ],
                        toolbar: [
                            'sourceEditing', '|',
                            'heading', '|',
                            'bold', 'italic', 'underline', 'link', '|',
                            'bulletedList', 'numberedList', 'blockQuote', 'alignment', '|',
                            'insertTable', 'uploadImage', '|',
                            'undo', 'redo',
                        ],
                        heading: {
                            options: [
                                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                                { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                            ],
                        },
                        table: {
                            contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties'],
                        },
                        image: {
                            toolbar: ['imageStyle:inline', 'imageStyle:block', 'imageStyle:side', '|', 'toggleImageCaption', 'imageTextAlternative', '|', 'resizeImage'],
                        },
                        htmlSupport: {
                            allow: [{ name: /.*/, attributes: true, classes: true, styles: true }],
                        },
                        simpleUpload: {
                            uploadUrl: '{{ route('admin.editor.upload') }}',
                            headers: { 'X-CSRF-TOKEN': token },
                        },
                    })
                    .then(function (editor) {
                        editor.model.document.on('change:data', function () {
                            el.value = editor.getData();
                        });
                    })
                    .catch(function (error) {
                        console.error('Rich editor failed to load:', error);
                    });
            });
        </script>
    @endpush
@endonce
