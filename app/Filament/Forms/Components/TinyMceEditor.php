<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Concerns\CanBeLengthConstrained;
use Filament\Forms\Components\Concerns\HasFileAttachments;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Contracts\CanBeLengthConstrained as CanBeLengthConstrainedContract;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;

class TinyMceEditor extends Field implements CanBeLengthConstrainedContract
{
    use CanBeLengthConstrained;
    use HasExtraAlpineAttributes;
    use HasFileAttachments;
    use HasPlaceholder;

    protected string $view = 'filament.forms.components.tiny-mce-editor';

    protected int|Closure $height = 650;

    /** @var array<string, mixed> | Closure */
    protected array|Closure $editorConfig = [];

    public function height(int|Closure $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->evaluate($this->height);
    }

    /** @param array<string, mixed> | Closure $config */
    public function editorConfig(array|Closure $config): static
    {
        $this->editorConfig = $config;

        return $this;
    }

    /** @return array<string, mixed> */
    public function getEditorConfig(): array
    {
        return array_replace_recursive([
            'block_formats' => 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
            'browser_spellcheck' => true,
            'content_style' => implode(' ', [
                'body { font-family: Lato, Arial, sans-serif; font-size: 16px; line-height: 1.78; max-width: 76ch; margin: 1.5rem auto; padding: 0 1rem; }',
                'p { margin: 0 0 1rem; }',
                'h2, h3, h4 { color: inherit; line-height: 1.3; margin: 1.6em 0 .6em; }',
                'blockquote { border-left: 4px solid #d99a21; margin: 1.5rem 0; padding: .8rem 1.2rem; background: rgba(148, 163, 184, .12); }',
                'img { max-width: 100%; height: auto; }',
                'figure.image { margin: 1.5rem auto; }',
                'figcaption { color: inherit; font-size: .875rem; opacity: .75; text-align: center; }',
                'table { border-collapse: collapse; width: 100%; }',
                'th, td { border: 1px solid #cbd5e1; padding: .65rem; vertical-align: top; }',
                'th { background: rgba(148, 163, 184, .16); }',
                'a { color: #3b82f6; text-decoration: underline; }',
                '.edulaw-footnote-ref { color: #3b82f6; font-weight: 700; }',
            ]),
            'contextmenu' => 'link image table',
            'convert_unsafe_embeds' => true,
            'image_advtab' => true,
            'image_caption' => true,
            'image_class_list' => [
                ['title' => 'Default', 'value' => ''],
                ['title' => 'Rata kiri', 'value' => 'align-left'],
                ['title' => 'Rata tengah', 'value' => 'align-center'],
                ['title' => 'Rata kanan', 'value' => 'align-right'],
            ],
            'image_description' => true,
            'image_title' => true,
            'invalid_elements' => 'script,iframe,object,embed,form,input,button,textarea,select,option,meta,link,style',
            'link_assume_external_targets' => 'https',
            'link_target_list' => [
                ['title' => 'Jendela yang sama', 'value' => ''],
                ['title' => 'Jendela baru', 'value' => '_blank'],
            ],
            'object_resizing' => 'img,table',
            'paste_data_images' => false,
            'paste_merge_formats' => true,
            'paste_webkit_styles' => 'none',
            'table_default_styles' => ['width' => '100%'],
            'table_sizing_mode' => 'responsive',
            'toolbar' => implode(' ', [
                'undo redo | blocks | bold italic underline strikethrough superscript subscript |',
                'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote |',
                'link unlink image table hr charmap footnote | removeformat searchreplace code fullscreen',
            ]),
            'toolbar_mode' => 'sliding',
        ], $this->evaluate($this->editorConfig));
    }
}
