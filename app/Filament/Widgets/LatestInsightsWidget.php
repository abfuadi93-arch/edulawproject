<?php

namespace App\Filament\Widgets;

use App\Enums\InsightStatus;
use App\Filament\Resources\Insights\InsightResource;
use App\Models\Insight;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestInsightsWidget extends TableWidget
{
    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 7,
    ];

    protected static ?int $sort = 10;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return auth()->user()?->can('view insights') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Insight Terbaru')
            ->description('Lima naskah yang paling baru diperbarui dalam ruang editorial.')
            ->query(
                InsightResource::getEloquentQuery()
                    ->with(['authors', 'categoryRelation', 'assignedEditor'])
                    ->latest('updated_at')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->description(fn (Insight $record): string => $record->display_author)
                    ->wrap()
                    ->grow(),
                TextColumn::make('categoryRelation.name')
                    ->label('Kategori')
                    ->placeholder('Tanpa kategori')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (InsightStatus $state): string => $state->canonical()->label())
                    ->color(fn (InsightStatus $state): string => $state->canonical()->color()),
                TextColumn::make('assignedEditor.name')
                    ->label('Editor')
                    ->placeholder('Belum ditugaskan')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since(),
            ])
            ->headerActions([
                Action::make('viewAll')
                    ->label('Lihat Semua')
                    ->icon('heroicon-o-arrow-right')
                    ->link()
                    ->color('warning')
                    ->extraAttributes(['class' => 'edulaw-latest-insights-view-all'])
                    ->url(InsightResource::getUrl('index')),
            ])
            ->recordUrl(fn (Insight $record): ?string => InsightResource::canEdit($record)
                ? InsightResource::getUrl('edit', ['record' => $record])
                : null)
            ->paginated(false)
            ->emptyStateHeading('Belum ada Insight')
            ->emptyStateDescription('Naskah terbaru akan muncul setelah konten editorial dibuat.')
            ->emptyStateIcon('heroicon-o-newspaper');
    }
}
