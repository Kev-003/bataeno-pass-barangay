<?php

namespace App\Filament\Official\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Filament\Actions\Action;

class Profile extends Page
{
    protected static ?string $navigationIcon = null;

    protected static string $view = 'filament.official.pages.profile';

    protected static ?string $title = 'My Profile';

    protected static ?string $slug = 'profile';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'digital_signature' => $user->digital_signature,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Account Information')
                    ->schema([
                        TextInput::make('name')
                            ->disabled(),
                        TextInput::make('email')
                            ->disabled(),
                    ])->columns(2),

                Section::make('Digital Signature')
                    ->description('Manage your official digital signature for document signing.')
                    ->schema([
                        FileUpload::make('digital_signature')
                            ->label('Signature Image')
                            ->disk('local')
                            ->directory(function () {
                                $barangayCode = auth()->user()->getActiveBarangayCode();
                                return "barangay-assets/{$barangayCode}/signatures";
                            })
                            ->getUploadedFileNameForStorageUsing(function ($file) {
                                return auth()->id() . '.' . $file->getClientOriginalExtension();
                            })
                            ->image()
                            ->imageEditor()
                            ->maxSize(1024)
                            ->helperText('Supported formats: JPG, PNG, WebP.'),
                    ])
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        auth()->user()->update([
            'digital_signature' => $data['digital_signature'],
        ]);

        Notification::make()
            ->title('Profile updated successfully')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save changes')
                ->submit('save'),
        ];
    }
}
