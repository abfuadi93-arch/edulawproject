<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Users\UserResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Akses Admin';

    protected static ?string $navigationLabel = 'Role';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $pluralModelLabel = 'Role';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Role')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Role')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('guard_name')
                            ->label('Guard')
                            ->required()
                            ->default('web')
                            ->maxLength(255)
                            ->disabledOn('edit'),
                    ])
                    ->columns(2),

                Section::make('Permission')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('Pilih Permission')
                            ->relationship('permissions', 'name')
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                            ->helperText('Gunakan pilih semua atau bersihkan semua untuk mengatur izin role secara efisien.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'edulaw-access-table edulaw-role-table'])
            ->columns([
                TextColumn::make('name')
                    ->label('Role')
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nama role disalin')
                    ->tooltip(fn (Role $record): string => $record->name)
                    ->extraHeaderAttributes(['class' => 'edulaw-role-name-header'])
                    ->extraCellAttributes(['class' => 'edulaw-role-name-cell']),

                TextColumn::make('permissions_count')
                    ->label('Permission')
                    ->numeric()
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => "{$state} izin")
                    ->color('primary')
                    ->sortable()
                    ->visibleFrom('md')
                    ->extraHeaderAttributes(['class' => 'edulaw-role-permission-header'])
                    ->extraCellAttributes(['class' => 'edulaw-role-permission-cell']),

                TextColumn::make('users_count')
                    ->label('Akun')
                    ->numeric()
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => "{$state} akun")
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray')
                    ->sortable()
                    ->visibleFrom('md')
                    ->extraHeaderAttributes(['class' => 'edulaw-role-user-header'])
                    ->extraCellAttributes(['class' => 'edulaw-role-user-cell']),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->date('d M Y')
                    ->tooltip(fn (Role $record): string => $record->updated_at?->locale('id')->translatedFormat('d M Y, H:i') ?? '—')
                    ->sortable()
                    ->visibleFrom('lg')
                    ->extraHeaderAttributes(['class' => 'edulaw-role-updated-header'])
                    ->extraCellAttributes(['class' => 'edulaw-role-updated-cell']),
            ])
            ->defaultSort('name')
            ->searchPlaceholder('Cari role...')
            ->emptyStateIcon('heroicon-o-shield-check')
            ->emptyStateHeading('Belum ada role')
            ->emptyStateDescription('Buat role untuk mengelompokkan kewenangan akun admin.')
            ->filters([
                TernaryFilter::make('used')
                    ->label('Penggunaan')
                    ->placeholder('Semua')
                    ->trueLabel('Digunakan')
                    ->falseLabel('Belum digunakan')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->has('users'),
                        false: fn (Builder $query): Builder => $query->doesntHave('users'),
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Edit'),
                    Action::make('view_users')
                        ->label('Lihat Akun')
                        ->icon('heroicon-o-users')
                        ->url(fn (Role $record): string => UserResource::getUrl('index', [
                            'tableFilters' => ['roles' => ['values' => [$record->id]]],
                        ]))
                        ->visible(fn (Role $record): bool => $record->users_count > 0),
                    Action::make('duplicate')
                        ->label('Duplikasi')
                        ->icon('heroicon-o-document-duplicate')
                        ->authorize('create', Role::class)
                        ->action(function (Role $record): void {
                            $copy = $record->replicate();
                            $copy->name = static::uniqueDuplicateName($record->name);
                            $copy->save();
                            $copy->syncPermissions($record->permissions);
                        }),
                    DeleteAction::make()
                        ->label('Hapus')
                        ->requiresConfirmation()
                        ->visible(fn (Role $record): bool => $record->name !== 'super_admin' && $record->users_count === 0),
                ])->label('Aksi lainnya')->icon('heroicon-o-ellipsis-vertical')->tooltip('Aksi lainnya')->color('gray'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('permissions:id,name')->withCount(['permissions', 'users']);
    }

    public static function uniqueDuplicateName(string $name): string
    {
        $base = Str::limit($name.' copy', 240, '');
        $candidate = $base;
        $suffix = 2;

        while (Role::query()->where('name', $candidate)->exists()) {
            $candidate = Str::limit("{$base} {$suffix}", 255, '');
            $suffix++;
        }

        return $candidate;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
