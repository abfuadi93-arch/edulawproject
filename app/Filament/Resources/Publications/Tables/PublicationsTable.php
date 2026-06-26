<?php

namespace App\Filament\Resources\Publications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PublicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('publication_type_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                ImageColumn::make('cover_image')
                    ->disk('public'),
                TextColumn::make('pdf_file')
                    ->searchable()
                    ->url(fn ($record): ?string => edulaw_file_url($record->pdf_file))
                    ->openUrlInNewTab(),
                TextColumn::make('external_url')
                    ->searchable(),
                TextColumn::make('source_name')
                    ->searchable(),
                TextColumn::make('published_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('page_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                IconColumn::make('featured')
                    ->boolean(),
                TextColumn::make('seo_title')
                    ->searchable(),
                ImageColumn::make('og_image')
                    ->disk('public'),
                TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
