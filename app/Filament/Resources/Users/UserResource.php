<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

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
                    ->description('Kelola akun login, foto, dan identitas dasar admin.')
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
                            ->maxLength(255),

                        FileUpload::make('avatar')
                            ->label('Foto Profil / Avatar')
                            ->image()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->imageEditor(),

                        TextInput::make('institution')
                            ->label('Institusi')
                            ->maxLength(255),

                        TextInput::make('position')
                            ->label('Posisi')
                            ->maxLength(255),

                        Textarea::make('bio')
                            ->label('Bio / Catatan')
                            ->rows(4)
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(1),

                Section::make('Role')
                    ->description('Atur peran pengguna dalam panel admin.')
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
            ->columns([
                ViewColumn::make('avatar')
                    ->label('Avatar')
                    ->view('filament.tables.user-avatar')
                    ->viewData(fn (User $record): array => ['record' => $record]),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(',')
                    ->limitList(3),

                TextColumn::make('institution')
                    ->label('Institusi')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('position')
                    ->label('Posisi')
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                Actions\EditAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
