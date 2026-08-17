@php
    $id = $getId();
    $key = $getKey();
    $label = $getLabel();
    $statePath = $getStatePath();
    $acceptedFileTypes = $getFileAttachmentsAcceptedFileTypes();
    $maxFileSize = $getFileAttachmentsMaxSize();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @if ($isDisabled())
        <div id="{{ $id }}" class="fi-prose max-w-none rounded-lg border border-gray-200 p-4 dark:border-white/10">
            {!! $getState() !!}
        </div>
    @else
        <x-filament::input.wrapper
            :valid="! $errors->has($statePath)"
            :attributes="\Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())"
        >
            <div
                id="{{ $id }}"
                role="group"
                aria-labelledby="{{ $id }}-label"
                wire:ignore
                x-load
                x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('edulaw-tinymce-editor') }}"
                x-data="tinyMceEditorFormComponent({
                    config: @js($getEditorConfig()),
                    height: @js($getHeight()),
                    label: @js($label),
                    skinBaseUrl: @js(asset('build/tinymce/skins')),
                    state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')", isOptimisticallyLive: false) }},
                    tinyMceBaseUrl: @js(asset('build/tinymce')),
                    tinyMceScriptUrl: @js(asset('build/tinymce/tinymce.min.js')),
                    uploadFileAttachmentUsing: (file, onProgress) => new Promise((resolve, reject) => {
                        const acceptedTypes = @js($acceptedFileTypes)

                        if (acceptedTypes && ! acceptedTypes.includes(file.type)) {
                            reject(new Error(@js($acceptedFileTypes ? __('filament-forms::components.rich_editor.file_attachments_accepted_file_types_message', ['values' => implode(', ', $acceptedFileTypes)]) : 'Jenis gambar tidak didukung.')))
                            return
                        }

                        const maxSize = @js($maxFileSize)

                        if (maxSize && file.size > +maxSize * 1024) {
                            reject(new Error(@js($maxFileSize ? trans_choice('filament-forms::components.rich_editor.file_attachments_max_size_message', $maxFileSize, ['max' => $maxFileSize]) : 'Ukuran gambar terlalu besar.')))
                            return
                        }

                        $wire.upload(
                            'componentFileAttachments.{{ $statePath }}',
                            file,
                            () => {
                                $wire.callSchemaComponentMethod(
                                    '{{ $key }}',
                                    'saveUploadedFileAttachmentAndGetUrl',
                                ).then((url) => url ? resolve(url) : reject(new Error('Upload gambar gagal.')))
                            },
                            () => reject(new Error('Upload gambar gagal.')),
                            (event) => onProgress(event.detail.progress),
                        )
                    }),
                })"
                {{ $getExtraAlpineAttributeBag() }}
            >
                <textarea x-ref="editor" x-cloak></textarea>
            </div>
        </x-filament::input.wrapper>
    @endif
</x-dynamic-component>
