<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class EditProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Edit Profil';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static string $view = 'filament.pages.edit-profile';
    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();

        $this->form->fill([
            'username' => $user->username,
            'email' => $user->email,
            'no_hp' => $user->no_hp,
            'company' => $user->company,
            'signature' => $user->userStatus?->signature_path,
            'kota' => $user->userStatus?->kota,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Akun')
                    ->schema([
                        TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->label('Password (biarkan kosong jika tidak diubah)')
                            ->maxLength(255),
                        TextInput::make('no_hp')->label('No HP')->tel(),
                        Select::make('company')->options([
                            'sap' => 'CV Solusi Arya Prima',
                            'dinatek' => 'CV Dinatek Jaya Lestari',
                            'ssm' => 'PT Sinergi Subur Makmur',
                        ])->label('Perusahaan'),
                        TextInput::make('kota')
                            ->label('Kota')
                            ->maxLength(255),
                    ])->columns(1),

                Section::make('Tanda Tangan')
                    ->schema([
                        FileUpload::make('signature')
                            ->label('Upload Tanda Tangan')
                            ->image()
                            ->disk('public')
                            ->directory('signatures')
                            ->maxSize(1024),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $user = Auth::user();

        $data = $this->form->getState();

        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->no_hp = $data['no_hp'];
        $user->company = $data['company'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $user->userStatus()->updateOrCreate([], [
            'signature_path' => $data['signature'] ?? $user->userStatus?->signature_path,
            'kota' => $data['kota'] ?? $user->userStatus?->kota,
        ]);

        Notification::make()
            ->title('Profil berhasil diperbarui')
            ->success()
            ->send();
    }
}
