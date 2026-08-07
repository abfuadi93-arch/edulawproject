<?php

namespace App\Filament\Resources\Permissions;

use App\Filament\Resources\Permissions\Pages\CreatePermission;
use App\Filament\Resources\Permissions\Pages\EditPermission;
use App\Filament\Resources\Permissions\Pages\ListPermissions;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Akses Admin';

    protected static ?string $navigationLabel = 'Permission';

    protected static ?string $modelLabel = 'Permission';

    protected static ?string $pluralModelLabel = 'Permission';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Permission')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Permission')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Mengubah nama permission dapat memengaruhi pengecekan izin di aplikasi.'),

                        TextInput::make('guard_name')
                            ->label('Guard')
                            ->required()
                            ->default('web')
                            ->maxLength(255)
                            ->disabledOn('edit'),
                    ])
                    ->columns(2),

                Section::make('Role')
                    ->schema([
                        Select::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'edulaw-access-table edulaw-permission-table'])
            ->columns([
                TextColumn::make('name')
                    ->label('Permission')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nama permission disalin')
                    ->extraHeaderAttributes(['class' => 'edulaw-permission-name-header'])
                    ->extraCellAttributes(['class' => 'edulaw-permission-name-cell']),

                TextColumn::make('group')
                    ->label('Kelompok')
                    ->state(fn (Permission $record): string => static::groupLabel($record->name))
                    ->badge()
                    ->color(fn (string $state): string => static::groupColor($state))
                    ->visibleFrom('md')
                    ->extraHeaderAttributes(['class' => 'edulaw-permission-group-header'])
                    ->extraCellAttributes(['class' => 'edulaw-permission-group-cell']),

                TextColumn::make('roles_count')
                    ->label('Dipakai Role')
                    ->numeric()
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => "{$state} role")
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray')
                    ->sortable()
                    ->visibleFrom('lg')
                    ->extraHeaderAttributes(['class' => 'edulaw-permission-role-header'])
                    ->extraCellAttributes(['class' => 'edulaw-permission-role-cell']),

                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->visibleFrom('md')
                    ->extraHeaderAttributes(['class' => 'edulaw-permission-guard-header'])
                    ->extraCellAttributes(['class' => 'edulaw-permission-guard-cell']),

            ])
            ->defaultSort('name')
            ->searchPlaceholder('Cari permission...')
            ->emptyStateIcon('heroicon-o-key')
            ->emptyStateHeading('Belum ada permission')
            ->emptyStateDescription('Buat permission teknis untuk digunakan oleh role admin.')
            ->filters([
                SelectFilter::make('group')
                    ->label('Kelompok Resource')
                    ->options(static::groupOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        $needle = $data['value'] ?? null;

                        return $query->when($needle, fn (Builder $query): Builder => $query->where('name', 'like', "%{$needle}%"));
                    }),

                TernaryFilter::make('used')
                    ->label('Penggunaan')
                    ->placeholder('Semua')
                    ->trueLabel('Digunakan')
                    ->falseLabel('Belum digunakan')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->has('roles'),
                        false: fn (Builder $query): Builder => $query->doesntHave('roles'),
                    ),

                SelectFilter::make('guard_name')
                    ->label('Guard')
                    ->options(fn (): array => Permission::query()->distinct()->orderBy('guard_name')->pluck('guard_name', 'guard_name')->all()),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Edit'),
                    DeleteAction::make()
                        ->label('Hapus')
                        ->requiresConfirmation()
                        ->visible(fn (Permission $record): bool => $record->roles_count === 0),
                ])->label('Aksi lainnya')->icon('heroicon-o-ellipsis-vertical')->tooltip('Aksi lainnya')->color('gray'),
            ]);
    }

    public static function groupLabel(string $permission): string
    {
        $name = Str::lower(str_replace('_', ' ', $permission));

        return match (true) {
            Str::contains($name, ['insight', 'editorial', 'revision']) => 'Editorial',
            Str::contains($name, 'publication') => 'Publikasi',
            Str::contains($name, 'program') => 'Program',
            Str::contains($name, 'opportunit') => 'Peluang',
            Str::contains($name, 'multimedia') => 'Multimedia',
            Str::contains($name, 'collaboration') => 'Kolaborasi',
            Str::contains($name, 'contact message') => 'Pesan',
            Str::contains($name, ['author', 'tag', 'categor', 'type']) => 'Referensi',
            Str::contains($name, ['user', 'role', 'permission']) => 'Akun dan Akses',
            default => 'Lainnya',
        };
    }

    public static function groupColor(string $group): string
    {
        return match ($group) {
            'Editorial' => 'primary',
            'Publikasi' => 'success',
            'Program' => 'info',
            'Peluang' => 'warning',
            'Multimedia' => 'danger',
            'Akun dan Akses' => 'gray',
            default => 'info',
        };
    }

    public static function groupOptions(): array
    {
        return [
            'insight' => 'Editorial',
            'publication' => 'Publikasi',
            'program' => 'Program',
            'opportunit' => 'Peluang',
            'multimedia' => 'Multimedia',
            'collaboration' => 'Kolaborasi',
            'contact message' => 'Pesan',
            'author' => 'Referensi',
            'user' => 'Akun dan Akses',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('roles');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }
}
