<?php

namespace App\Filament\Pages;

use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use LogicException;

class EditMyProfile extends Page
{
    protected static ?string $title = 'Profil Saya';

    protected static ?string $navigationLabel = 'Profil Saya';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?int $navigationSort = -9;

    protected string $view = 'filament.pages.edit-my-profile';

    protected Width|string|null $maxContentWidth = Width::FourExtraLarge;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Auth::check();
    }

    public function mount(): void
    {
        $this->form->fill($this->getUser()->only([
            'name',
            'email',
            'avatar',
            'bio',
            'institution',
            'position',
        ]));
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->model($this->getUser())
            ->operation('edit')
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'lg' => 5,
                ])
                    ->schema([
                        Section::make('Foto Profil')
                            ->schema([
                                FileUpload::make('avatar')
                                    ->label('Avatar')
                                    ->image()
                                    ->disk('public')
                                    ->directory('avatars')
                                    ->visibility('public')
                                    ->avatar()
                                    ->imageEditor()
                                    ->maxSize(2048)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 2,
                            ]),

                        Section::make('Informasi Akun')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255)
                                    ->autofocus(),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(User::class, 'email', ignoreRecord: true),

                                TextInput::make('institution')
                                    ->label('Institusi')
                                    ->maxLength(255),

                                TextInput::make('position')
                                    ->label('Posisi')
                                    ->maxLength(255),

                                Textarea::make('bio')
                                    ->label('Bio')
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 3,
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Ubah Password')
                    ->schema([
                        TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->same('password_confirmation')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                            ->autocomplete('new-password'),

                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password Baru')
                            ->password()
                            ->revealable()
                            ->requiredWith('data.password')
                            ->dehydrated(false)
                            ->autocomplete('new-password'),
                    ])
                    ->columns(2),
            ]);
    }

    public function save(): void
    {
        $user = $this->getUser();
        $data = $this->form->getState();

        $user->fill($data);
        $user->save();

        $this->data['password'] = null;
        $this->data['password_confirmation'] = null;

        Notification::make()
            ->success()
            ->title('Profil berhasil diperbarui')
            ->send();
    }

    private function getUser(): User&Model
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            throw new LogicException('Authenticated user must be an App\Models\User instance.');
        }

        return $user;
    }
}
