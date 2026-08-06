<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Akses Admin';

    protected static ?string $navigationLabel = 'Akun Admin';

    protected static ?string $modelLabel = 'Akun Admin';

    protected static ?string $pluralModelLabel = 'Akun Admin';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun Admin')
                    ->description('Kelola akun login dan identitas dasar admin.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Akun')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->same('password_confirmation')
                            ->autocomplete('new-password'),

                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->requiredWith('password')
                            ->dehydrated(false)
                            ->autocomplete('new-password'),

                        TextInput::make('institution')
                            ->label('Institusi')
                            ->maxLength(255),

                        TextInput::make('position')
                            ->label('Posisi')
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->disabled(fn (?User $record): bool => $record ? static::isLastActiveSuperAdmin($record) : false)
                            ->helperText('Super Admin aktif terakhir tidak dapat dinonaktifkan.'),
                    ])
                    ->columns(['default' => 1, 'lg' => 2]),

                Section::make('Role')
                    ->description('Atur peran pengguna dalam panel admin.')
                    ->schema([
                        Select::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (?User $record): bool => $record ? static::isLastSuperAdmin($record) : false)
                            ->helperText('Satu akun dapat memiliki lebih dari satu role. Role Super Admin terakhir dikunci untuk mencegah kehilangan akses.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'edulaw-access-table edulaw-user-table'])
            ->columns([
                ViewColumn::make('account')
                    ->label('Akun')
                    ->view('filament.tables.columns.user-identity', fn (User $record): array => [
                        'name' => $record->name,
                        'email' => $record->email,
                        'affiliation' => collect([$record->position, $record->institution])->filter()->join(' · '),
                    ])
                    ->searchable(['name', 'email', 'institution', 'position'])
                    ->sortable()
                    ->extraHeaderAttributes(['class' => 'edulaw-user-account-header'])
                    ->extraCellAttributes(['class' => 'edulaw-user-account-cell']),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->visibleFrom('md')
                    ->extraHeaderAttributes(['class' => 'edulaw-user-role-header'])
                    ->extraCellAttributes(['class' => 'edulaw-user-role-cell']),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->sortable()
                    ->extraHeaderAttributes(['class' => 'edulaw-user-status-header'])
                    ->extraCellAttributes(['class' => 'edulaw-user-status-cell']),

                TextColumn::make('email_verified_at')
                    ->label('Verifikasi')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Terverifikasi' : 'Belum Terverifikasi')
                    ->color(fn ($state): string => $state ? 'success' : 'warning')
                    ->visibleFrom('lg')
                    ->extraHeaderAttributes(['class' => 'edulaw-user-verification-header'])
                    ->extraCellAttributes(['class' => 'edulaw-user-verification-cell']),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->date('d M Y')
                    ->tooltip(fn (User $record): string => $record->updated_at?->locale('id')->translatedFormat('d M Y, H:i') ?? '—')
                    ->sortable()
                    ->visibleFrom('xl')
                    ->extraHeaderAttributes(['class' => 'edulaw-user-updated-header'])
                    ->extraCellAttributes(['class' => 'edulaw-user-updated-cell']),

                TextColumn::make('institution')->label('Institusi')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('position')->label('Jabatan')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y, H:i')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at_full')->label('Diperbarui')->state(fn (User $record) => $record->updated_at)->dateTime('d M Y, H:i')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->searchPlaceholder('Cari nama, email, atau institusi...')
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateHeading('Belum ada akun admin')
            ->emptyStateDescription('Tambahkan akun untuk memberikan akses ke panel administrasi.')
            ->filters([
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

                TernaryFilter::make('email_verified')
                    ->label('Verifikasi Email')
                    ->trueLabel('Terverifikasi')
                    ->falseLabel('Belum terverifikasi')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('email_verified_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('email_verified_at'),
                    ),

                TernaryFilter::make('without_role')
                    ->label('Kepemilikan Role')
                    ->trueLabel('Tanpa role')
                    ->falseLabel('Memiliki role')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->doesntHave('roles'),
                        false: fn (Builder $query): Builder => $query->has('roles'),
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Edit'),
                    Action::make('toggle_active')
                        ->label(fn (User $record): string => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                        ->icon(fn (User $record): string => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                        ->color(fn (User $record): string => $record->is_active ? 'warning' : 'success')
                        ->authorize('update')
                        ->visible(fn (User $record): bool => ! ($record->is_active && static::isLastActiveSuperAdmin($record)))
                        ->requiresConfirmation()
                        ->action(fn (User $record) => $record->update(['is_active' => ! $record->is_active])),
                    DeleteAction::make()
                        ->label('Hapus')
                        ->requiresConfirmation()
                        ->visible(fn (User $record): bool => static::canSafelyDelete($record)),
                ])->label('Aksi lainnya')->icon('heroicon-o-ellipsis-vertical')->tooltip('Aksi lainnya')->color('gray'),
            ]);
    }

    public static function isLastSuperAdmin(User $user): bool
    {
        return $user->hasRole('super_admin') && User::role('super_admin')->count() <= 1;
    }

    public static function isLastActiveSuperAdmin(User $user): bool
    {
        return $user->hasRole('super_admin')
            && User::role('super_admin')->where('is_active', true)->count() <= 1;
    }

    public static function canSafelyDelete(User $user): bool
    {
        return Auth::id() !== $user->id && ! static::isLastSuperAdmin($user);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('roles:id,name');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
