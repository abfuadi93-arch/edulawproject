<?php

namespace App\Filament\Resources\AssignedInsights;

use App\Enums\InsightStatus;
use App\Filament\Resources\AssignedInsights\Pages\ListAssignedInsights;
use App\Filament\Resources\Editorial\EditorialResource;
use App\Models\Insight;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AssignedInsightResource extends Resource
{
    protected static ?string $model = Insight::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial';

    protected static ?string $navigationLabel = 'Tugas Editor';

    protected static ?string $modelLabel = 'Naskah';

    protected static ?string $pluralModelLabel = 'Tugas Editor';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return Auth::user()?->canAccessAssignedEditorialInsights() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'edulaw-assigned-insight-table'])
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->description(fn (Insight $record): string => $record->authors->pluck('name')->join(', ') ?: 'Tanpa penulis')
                    ->searchable(['title', 'authors.name'])
                    ->sortable()
                    ->wrap()
                    ->lineClamp(2)
                    ->extraHeaderAttributes(['class' => 'edulaw-assigned-title-header'])
                    ->extraCellAttributes(['class' => 'edulaw-assigned-title-cell'])
                    ->grow(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (InsightStatus $state): string => $state->canonical()->label())
                    ->color(fn (InsightStatus $state): string => $state->canonical()->color())
                    ->extraHeaderAttributes(['class' => 'edulaw-assigned-progress-header'])
                    ->extraCellAttributes(['class' => 'edulaw-assigned-progress-cell']),
                TextColumn::make('submitted_at')
                    ->label('Dikirim / diperbarui')
                    ->state(fn (Insight $record) => $record->submitted_at ?: $record->updated_at)
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->visibleFrom('md')
                    ->extraHeaderAttributes(['class' => 'edulaw-assigned-assignment-header'])
                    ->extraCellAttributes(['class' => 'edulaw-assigned-assignment-cell']),
                TextColumn::make('updated_at')
                    ->label('Terakhir diperbarui')
                    ->since()
                    ->sortable()
                    ->visibleFrom('lg')
                    ->extraHeaderAttributes(['class' => 'edulaw-assigned-activity-header'])
                    ->extraCellAttributes(['class' => 'edulaw-assigned-activity-cell']),
            ])
            ->defaultSort('updated_at', 'desc')
            ->searchPlaceholder('Cari judul atau penulis...')
            ->paginationPageOptions([10, 25, 50])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(InsightStatus::options()),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Buka')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Insight $record): string => EditorialResource::getUrl('workspace', ['record' => $record])),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('assigned_editor_id', Auth::id())
            ->with(['authors:id,name', 'assignedEditor:id,name']);
    }

    public static function getPages(): array
    {
        return ['index' => ListAssignedInsights::route('/')];
    }
}
