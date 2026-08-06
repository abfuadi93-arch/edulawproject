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
        $unverifiedUsers = User::whereNull('email_verified_at')->count();

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
                    'count' => $unverifiedUsers,
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
                Insight::whereIn('status', ['draft', 'submitted', 'editor_assigned', 'in_review', 'revision_requested', 'revised', 'approved', 'reviewed'])->exists() ? [
                    'label' => 'Ada editorial dalam alur editorial',
                    'severity' => 'medium',
                    'description' => 'Tinjau naskah yang belum terbit agar jadwal editorial tetap rapi.',
                ] : null,
                Program::whereNull('program_category_id')->exists() ? [
                    'label' => 'Program tanpa kategori',
                    'severity' => 'medium',
                    'description' => 'Lengkapi kategori agar filter dan halaman program lebih mudah dipakai.',
                ] : null,
                $unverifiedUsers > 0 ? [
                    'label' => 'Ada email pengguna belum terverifikasi',
                    'severity' => 'medium',
                    'description' => "{$unverifiedUsers} user menunggu verifikasi email.",
                ] : null,
            ])->filter()->values(),
        ];
    }
}
