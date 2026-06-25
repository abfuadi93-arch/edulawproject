<?php

namespace App\Filament\Widgets;

use App\Models\CollaborationSubmission;
use App\Models\ContactMessage;
use App\Models\Insight;
use App\Models\Program;
use App\Models\Publication;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -20;

    protected function getColumns(): int|array|null
    {
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }

    protected function getStats(): array
    {
        $newInteractions = ContactMessage::where('status', 'new')->count()
            + CollaborationSubmission::where('status', 'new')->count();

        return [
            Stat::make('Insight Terbit', Insight::where('status', 'published')->count())
                ->description('Artikel terpublikasi')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->chart([7, 9, 10, 12, 13, 15, 16])
                ->color('primary')
                ->icon('heroicon-o-newspaper'),

            Stat::make('Publikasi Riset', Publication::where('status', 'published')->count())
                ->description('Dokumen tampil di website')
                ->descriptionIcon('heroicon-o-document-check')
                ->chart([2, 2, 3, 3, 4, 4, 5])
                ->color('success')
                ->icon('heroicon-o-document-text'),

            Stat::make('Program Aktif', Program::whereIn('status', ['upcoming', 'ongoing'])->count())
                ->description('Upcoming dan ongoing')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->chart([3, 4, 4, 5, 6, 6, 7])
                ->color('warning')
                ->icon('heroicon-o-academic-cap'),

            Stat::make('Interaksi Baru', $newInteractions)
                ->description('Pesan dan kolaborasi baru')
                ->descriptionIcon('heroicon-o-inbox-arrow-down')
                ->chart([1, 2, 1, 3, 2, 4, max(4, $newInteractions)])
                ->color('danger')
                ->icon('heroicon-o-chat-bubble-left-right'),
        ];
    }
}
