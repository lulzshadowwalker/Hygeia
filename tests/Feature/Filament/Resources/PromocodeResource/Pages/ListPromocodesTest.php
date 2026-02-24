<?php

namespace Tests\Feature\Filament\Resources\PromocodeResource\Pages;

use App\Filament\Resources\PromocodeResource;
use App\Filament\Resources\PromocodeResource\Pages\CreatePromocode;
use App\Filament\Resources\PromocodeResource\Pages\EditPromocode;
use App\Filament\Resources\PromocodeResource\Pages\ListPromocodes;
use App\Models\Promocode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\WithAdmin;

class ListPromocodesTest extends TestCase
{
    use RefreshDatabase, WithAdmin;

    public function test_it_renders_the_page(): void
    {
        $this->get(PromocodeResource::getUrl('index'))->assertOk();
    }

    public function test_page_contains_promocode_records(): void
    {
        $items = Promocode::factory()->count(3)->create();

        Livewire::test(ListPromocodes::class)
            ->assertCanSeeTableRecords($items);
    }

    public function test_admin_can_create_promocode(): void
    {
        Livewire::test(CreatePromocode::class)
            ->fillForm([
                'code' => 'SPRING10',
                'discount_percentage' => 10,
                'max_discount_amount' => 2000,
                'starts_at' => now()->subHour(),
                'expires_at' => now()->addDays(7),
                'max_global_uses' => 20,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('promocodes', [
            'code' => 'SPRING10',
            'discount_percentage' => '10.00',
            'max_global_uses' => 20,
        ]);
    }

    public function test_admin_can_edit_promocode(): void
    {
        $promocode = Promocode::factory()->create([
            'code' => 'SUMMER20',
            'discount_percentage' => 20,
        ]);

        Livewire::test(EditPromocode::class, ['record' => $promocode->getRouteKey()])
            ->fillForm([
                'discount_percentage' => 25,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('promocodes', [
            'id' => $promocode->id,
            'discount_percentage' => '25.00',
        ]);
    }
}
