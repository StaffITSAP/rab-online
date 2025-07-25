<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
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
            'signature' => $user->status?->signature_path,
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

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        if (!empty($data['signature'])) {
            $user->status()->updateOrCreate([], [
                'signature_path' => $data['signature'],
            ]);
        }

        Notification::make()
            ->title('Profil berhasil diperbarui')
            ->success()
            ->send();
    }
}
