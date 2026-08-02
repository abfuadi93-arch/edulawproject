<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\CreateContactMessage;
use App\Filament\Resources\ContactMessages\Pages\EditContactMessage;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Models\ContactMessage;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Interaksi';

    protected static ?string $navigationLabel = 'Pesan';

    protected static ?string $modelLabel = 'Pesan';

    protected static ?string $pluralModelLabel = 'Pesan';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 12,
                ])
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Identitas Pengirim')
                                    ->icon('heroicon-o-user-circle')
                                    ->description('Data ini berasal dari formulir kontak publik.')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama')
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('phone')
                                            ->label('Nomor Telepon')
                                            ->disabled()
                                            ->dehydrated(false),
                                    ])
                                    ->columns(2),

                                Section::make('Isi Pesan')
                                    ->icon('heroicon-o-envelope')
                                    ->description('Isi pesan dari formulir kontak publik.')
                                    ->schema([
                                        TextInput::make('subject')
                                            ->label('Subjek')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpanFull(),

                                        Textarea::make('message')
                                            ->label('Pesan')
                                            ->rows(8)
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(['xl' => 8])
                            ->extraAttributes(['class' => 'edulaw-admin-main-column']),

                        Group::make()
                            ->schema([
                                Section::make('Tindak Lanjut Admin')
                                    ->icon('heroicon-o-clipboard-document-check')
                                    ->description('Bagian ini digunakan untuk mencatat tindak lanjut pesan kontak.')
                                    ->schema([
                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'new' => 'Baru',
                                                'read' => 'Dibaca',
                                                'replied' => 'Dibalas',
                                                'archived' => 'Diarsipkan',
                                            ])
                                            ->default('new')
                                            ->required(),

                                        Select::make('handled_by')
                                            ->label('Ditangani Oleh')
                                            ->relationship('handler', 'name')
                                            ->searchable()
                                            ->preload(),

                                        DateTimePicker::make('handled_at')
                                            ->label('Waktu Ditangani')
                                            ->seconds(false),

                                        Textarea::make('internal_notes')
                                            ->label('Catatan Internal')
                                            ->rows(7)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(['xl' => 4])
                            ->extraAttributes(['class' => 'edulaw-admin-side-column']),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-edit-shell']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'edulaw-inbox-table'])
            ->columns([
                ViewColumn::make('sender')
                    ->label('Pengirim')
                    ->view('filament.tables.columns.inbox-sender', fn (ContactMessage $record): array => [
                        'name' => $record->name,
                        'email' => $record->email,
                        'phone' => $record->phone,
                    ])
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where(function (Builder $query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('subject', 'like', "%{$search}%")
                            ->orWhere('message', 'like', "%{$search}%");
                    }))
                    ->sortable(),

                TextColumn::make('subject')
                    ->label('Subjek')
                    ->description(fn (ContactMessage $record): string => str($record->message)->squish()->limit(72))
                    ->limit(44)
                    ->tooltip(fn (?string $state): ?string => filled($state) && mb_strlen($state) > 44 ? $state : null),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::statusLabel($state))
                    ->color(fn (?string $state): string => static::statusColor($state)),

                TextColumn::make('created_at')
                    ->label('Diterima')
                    ->date('d M Y')
                    ->tooltip(fn (ContactMessage $record): ?string => $record->created_at?->translatedFormat('d F Y, H:i'))
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('handler.name')
                    ->label('Penanggung Jawab')
                    ->placeholder('Belum ditugaskan')
                    ->visibleFrom('lg'),

                TextColumn::make('phone')
                    ->label('Nomor Telepon')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('handled_at')
                    ->label('Ditangani')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Cari pengirim, subjek, atau isi pesan...')
            ->emptyStateIcon('heroicon-o-envelope')
            ->emptyStateHeading('Belum ada pesan masuk')
            ->emptyStateDescription('Pesan dari halaman kontak akan muncul di sini.')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(static::statusOptions()),

                SelectFilter::make('handled_by')
                    ->label('Penanggung Jawab')
                    ->relationship('handler', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->label('Rentang Tanggal')
                    ->schema([
                        DatePicker::make('from')->label('Dari tanggal')->native(false),
                        DatePicker::make('until')->label('Sampai tanggal')->native(false),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date))),

                TernaryFilter::make('unassigned')
                    ->label('Penugasan')
                    ->placeholder('Semua')
                    ->trueLabel('Belum ditugaskan')
                    ->falseLabel('Sudah ditugaskan')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNull('handled_by'),
                        false: fn (Builder $query): Builder => $query->whereNotNull('handled_by'),
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Lihat')->icon('heroicon-o-eye'),
                    static::statusAction('read', 'Tandai Dibaca', 'heroicon-o-eye'),
                    static::statusAction('replied', 'Tandai Dibalas', 'heroicon-o-paper-airplane', 'success'),
                    Action::make('assign')
                        ->label('Tugaskan')
                        ->icon('heroicon-o-user-plus')
                        ->authorize('update')
                        ->schema([
                            Select::make('handled_by')
                                ->label('Penanggung Jawab')
                                ->options(fn (): array => User::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->required(),
                        ])
                        ->fillForm(fn (ContactMessage $record): array => ['handled_by' => $record->handled_by])
                        ->action(fn (ContactMessage $record, array $data) => $record->update([
                            'handled_by' => $data['handled_by'],
                            'handled_at' => $record->handled_at ?? now(),
                        ])),
                    static::statusAction('archived', 'Arsipkan', 'heroicon-o-archive-box', 'warning'),
                ])->label('Aksi lainnya')->icon('heroicon-o-ellipsis-vertical')->tooltip('Aksi lainnya')->color('gray'),
            ]);
    }

    public static function statusOptions(): array
    {
        return [
            'new' => 'Baru',
            'read' => 'Dibaca',
            'replied' => 'Dibalas',
            'archived' => 'Diarsipkan',
        ];
    }

    public static function statusLabel(?string $status): string
    {
        return static::statusOptions()[$status] ?? ($status ? ucfirst($status) : '—');
    }

    public static function statusColor(?string $status): string
    {
        return match ($status) {
            'new' => 'primary',
            'read' => 'warning',
            'replied' => 'success',
            default => 'gray',
        };
    }

    protected static function statusAction(string $status, string $label, string $icon, string $color = 'gray'): Action
    {
        return Action::make("set_{$status}")
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->authorize('update')
            ->visible(fn (ContactMessage $record): bool => $record->status !== $status)
            ->requiresConfirmation(fn (): bool => $status === 'archived')
            ->action(fn (ContactMessage $record) => $record->update([
                'status' => $status,
                'handled_by' => $record->handled_by ?? Auth::id(),
                'handled_at' => $record->handled_at ?? now(),
            ]));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('handler:id,name');
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
            'index' => ListContactMessages::route('/'),
            'create' => CreateContactMessage::route('/create'),
            'edit' => EditContactMessage::route('/{record}/edit'),
        ];
    }
}
