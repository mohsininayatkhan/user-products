<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\Product;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_product_endpoint_status_code_is_200()
    {
        $response = $this->json('GET', '/api/products');

        $response->assertStatus(200);        
    }

    public function test_get_all_product_response_has_data_key()
    {
        $response = $this->json('GET', '/api/products');

        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data')
                ->etc()
        );    
    }

    public function test_products_total_number_of_record()
    {
        $totalProducts = Product::all()->count();

        $response = $this->json('GET', '/api/products');

        $response->assertJson(fn (AssertableJson $json) => 
            $json->has('total')
                ->where('total', $totalProducts)
                ->etc()
        );
    }
}
