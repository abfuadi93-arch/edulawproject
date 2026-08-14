<?php

namespace App\Filament\Widgets;

use App\Models\Insight;
use App\Models\Program;
use App\Models\Publication;
use App\Models\User;
use Filament\Widgets\Widget;

class EdulawContentAlerts extends Widget
{
    protected string $view = 'filament.widgets.edulaw-content-alerts';

    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 6,
    ];

    protected static ?int $sort = 40;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        if (! auth()->user()?->hasRole('super_admin')) {
            return false;
        }

        return true;
    }

    protected function getViewData(): array
    {
        $debugActive = (bool) config('app.debug');
        $inactiveUsers = User::where('is_active', false)->count();

        return [
            'summary' => [
                [
                    'label' => 'Kritis',
                    'count' => $debugActive ? 1 : 0,
                    'tone' => 'danger',
                ],
                [
                    'label' => 'Prioritas Tinggi',
                    'count' => Publication::where('status', 'draft')->count(),
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Perlu Ditinjau',
                    'count' => $inactiveUsers,
                    'tone' => 'primary',
                ],
            ],
            'alerts' => collect([
                $debugActive ? [
                    'label' => 'APP_DEBUG masih aktif',
                    'severity' => 'critical',
                    'description' => 'Matikan debug di production agar detail error dan path server tidak tampil ke publik.',
                ] : null,
                Publication::where('status', 'draft')->exists() ? [
                    'label' => 'Publikasi masih draft',
                    'severity' => 'high',
                    'description' => 'Periksa metadata, file PDF, dan status publikasi sebelum ditayangkan.',
                ] : null,
                Insight::whereIn('status', ['draft', 'review'])->exists() ? [
                    'label' => 'Ada editorial dalam alur editorial',
                    'severity' => 'medium',
                    'description' => 'Tinjau naskah yang belum terbit agar jadwal editorial tetap rapi.',
                ] : null,
                Program::whereNull('program_category_id')->exists() ? [
                    'label' => 'Program tanpa kategori',
                    'severity' => 'medium',
                    'description' => 'Lengkapi kategori agar filter dan halaman program lebih mudah dipakai.',
                ] : null,
                $inactiveUsers > 0 ? [
                    'label' => 'Ada akun dengan akses nonaktif',
                    'severity' => 'medium',
                    'description' => "{$inactiveUsers} akun tidak dapat masuk. Super admin dapat meninjau dan mengaktifkan aksesnya.",
                ] : null,
            ])->filter()->values(),
        ];
    }
}
