<?php

namespace App\Filament\Pages;

use App\Enums\Audience;
use App\Services\FirebasePushNotification\AudienceNotificationStrategy;
use App\Support\PushNotification as SupportPushNotification;
use Exception;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class PushNotification extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static string $view = 'filament.pages.push-notification';

    public array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return 'Notifications';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Push Notifications';
    }

    public static function getNavigationLabel(): string
    {
        return 'Push Notifications';
    }

    public function mount()
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Publish Notification')
                    ->description('Send a push notification to your users.')
                    ->aside()
                    ->schema([
                        Forms\Components\Select::make('audience')
                            ->label('Audience')
                            ->placeholder('Target Audience')
                            ->options(Arr::collapse(Arr::map(Audience::cases(), fn($status) => [$status->value => $status->label()])))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\FileUpload::make('image')
                            ->label('Image')
                            ->image()
                            ->imageEditor()
                            ->openable()
                            ->downloadable()
                            ->storeFiles(false)
                            ->maxSize(4 * 1024 * 1024),

                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->placeholder('Short and catchy title (e.g., Limited Time Offer)')
                            ->required()
                            ->maxLength(36),

                        Forms\Components\TextInput::make('body')
                            ->label('Body')
                            ->placeholder('Provide the message details (e.g., Enjoy 20% off on all items until Friday!)')
                            ->required()
                            ->maxLength(150),
                    ]),
            ]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('Publish')
                ->action(fn() => $this->publish()),
        ];
    }

    public function publish(): void
    {
        try {
            $notification = new SupportPushNotification(
                title: $this->data['title'],
                body: $this->data['body'],
            );

            // TODO: use PushNotificationService instead
            (new AudienceNotificationStrategy)->send($notification, $this->data['audience']);

            Log::info('Push notification sent', [
                'audience' => $this->data['audience'],
                'title' => $this->data['title'],
                'body' => $this->data['body'],
            ]);

            $this->form->fill();

            Notification::make()
                ->success()
                ->title('Notification sent successfully.')
                ->send();
        } catch (Exception $e) {
            Log::error('Failed to send push notification', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->danger()
                ->title('Failed to send notification. Please try again later.')
                ->send();
        }
    }
}
