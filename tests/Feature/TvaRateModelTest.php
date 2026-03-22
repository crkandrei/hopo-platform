<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\TvaRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TvaRateModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_tva_rate_can_be_created(): void
    {
        $rate = TvaRate::create([
            'name' => 'Cotă standard',
            'percentage' => 19.00,
            'vat_class' => 1,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('tva_rates', [
            'name' => 'Cotă standard',
            'vat_class' => 1,
        ]);
        $this->assertEquals('19.00', $rate->percentage);
    }

    public function test_tva_rate_has_many_products(): void
    {
        $rate = TvaRate::create([
            'name' => 'Cotă redusă',
            'percentage' => 9.00,
            'vat_class' => 2,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $rate->products);
    }

    public function test_product_belongs_to_tva_rate(): void
    {
        $rate = TvaRate::create([
            'name' => 'Cotă standard',
            'percentage' => 19.00,
            'vat_class' => 1,
            'is_active' => true,
        ]);

        $location = \App\Models\Location::factory()->create();
        $product = Product::create([
            'location_id' => $location->id,
            'name' => 'Sosete',
            'price' => 10.00,
            'tva_rate_id' => $rate->id,
        ]);

        $this->assertEquals($rate->id, $product->fresh()->tvaRate->id);
        $this->assertEquals(1, $product->fresh()->tvaRate->vat_class);
    }

    public function test_product_tva_rate_is_nullable(): void
    {
        $location = \App\Models\Location::factory()->create();
        $product = Product::create([
            'location_id' => $location->id,
            'name' => 'Sosete',
            'price' => 10.00,
        ]);

        $this->assertNull($product->fresh()->tvaRate);
    }
}
