<?php

namespace App\Filament\Pages;

use App\Models\Page as ModelsPage;
use Exception;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PrivacyPolicy extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.privacy-policy';

    public static function getNavigationGroup(): ?string
    {
        return 'Content Management';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Privacy Policy';
    }

    public static function getNavigationLabel(): string
    {
        return 'Privacy Policy';
    }

    public array $data = [];

    public function mount()
    {
        $page = ModelsPage::where('slug', ModelsPage::PRIVACY_POLICY)->first();

        $this->form->fill([
            'content' => $page->content,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Privacy Policy')
                    ->description('Manage the privacy policy of the application.')
                    ->aside()
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->label('Content')
                            ->rows(10)
                            ->required(),
                    ]),
            ]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('Publish')
                ->action(fn () => $this->publish()),
        ];
    }

    public function publish()
    {
        $content = $this->data['content'];
        if (! $content) {
            return;
        }

        try {
            $page = ModelsPage::where('slug', ModelsPage::PRIVACY_POLICY)->first();
            if (! $page) {
                throw new Exception('Privacy policy page not found');
            }

            $page->content = Str::trim($content);
            $page->save();

            Notification::make()
                ->success()
                ->title('Privacy Policy')
                ->body('Privacy policy published successfully.')
                ->send();
        } catch (Exception $e) {
            Log::error('Failed to publish privacy policy page', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);

            Notification::make()
                ->danger()
                ->title('Failed to publish privacy policy. Please try again later.')
                ->send();
        }
    }
}
