<?php

namespace App\Filament\RichEditor;

use App\Filament\RichEditor\Extensions\FootnoteExtension;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\Textarea;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Tiptap\Core\Extension;

class FootnoteRichContentPlugin implements RichContentPlugin
{
    /** @return array<Extension> */
    public function getTipTapPhpExtensions(): array
    {
        return [app(FootnoteExtension::class)];
    }

    /** @return array<string> */
    public function getTipTapJsExtensions(): array
    {
        return [Vite::asset('resources/js/filament/rich-editor-footnote.js')];
    }

    /** @return array<RichEditorTool> */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('footnote')
                ->label('Catatan Kaki')
                ->action(arguments: <<<'JS'
                    {
                        footnoteCount: (() => {
                            let count = 0
                            $getEditor()?.state.doc.descendants((node) => {
                                if (node.type.name === 'footnote') count++
                            })
                            return count
                        })(),
                    }
                    JS)
                ->activeJsExpression('false')
                ->icon('heroicon-o-document-text'),
        ];
    }

    /** @return array<Action> */
    public function getEditorActions(): array
    {
        return [
            Action::make('footnote')
                ->label('Catatan Kaki')
                ->modalHeading('Tambahkan Catatan Kaki')
                ->modalSubmitActionLabel('Tambahkan')
                ->modalWidth(Width::Large)
                ->schema([
                    Textarea::make('content')
                        ->label('Isi Catatan Kaki')
                        ->helperText('Gunakan teks biasa. Nomor akan mengikuti urutan penanda di artikel.')
                        ->rows(7)
                        ->required()
                        ->maxLength(10000),
                ])
                ->action(function (array $arguments, array $data, RichEditor $component): void {
                    $component->runCommands(
                        [
                            EditorCommand::make('insertFootnote', arguments: [[
                                'id' => (string) Str::uuid(),
                                'number' => max(1, ((int) ($arguments['footnoteCount'] ?? 0)) + 1),
                                'content' => trim((string) $data['content']),
                            ]]),
                        ],
                        editorSelection: $arguments['editorSelection'] ?? null,
                    );
                }),
        ];
    }
}
