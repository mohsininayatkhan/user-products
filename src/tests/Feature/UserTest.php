<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\User;
use App\Models\Purchased;
use App\Services\ProductService;
use Illuminate\Support\Str;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_access_user_information()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->get('/api/user');

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('id', 1)
                    ->where('name', $user->name)
                    ->missing('password')
                    ->etc()
            );
    }

    public function test_token_validation_for_user_information()
    {
        $response = $this->json('GET', '/api/user');

        $response->assertUnauthorized();
    }

    public function test_auth_response_for_valid_credentials()
    {
        $user = User::find(1);

        if ($user) {
            $response = $this->json('POST', '/api/auth', [
                'email' => $user->email, 
                'password' => 'secret']
            );
    
            $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => 
                $json->hasAll('user', 'token')
                    ->has('user', fn ($json) => 
                        $json->where('id', 1)
                            ->where('name', $user->name)
                            ->missing('password')                
                            ->etc()
                    )
                );
        }       
    }

    public function test_auth_validation_for_invalid_credentials()
    {
        $response = $this->json('POST', '/api/auth', [
            'email' => 'fake@email.com', 
            'password' => Str::random(8)]
        );

        $response->assertStatus(422)->assertJson(fn (AssertableJson $json) => 
            $json->hasAll('errors', 'message')
                ->where('message', 'The given data was invalid.')
                ->etc()
        );
    }

    public function test_authentication_validation_for_user_products()
    {
        $response = $this->json('GET', '/api/user/products');

        $response->assertStatus(401);
    }

    public function test_user_products_for_auth_user()
    {
        $purchased = Purchased::find(1);

        if ($purchased) {
            $purchasedCount = Purchased::where('user_id', $purchased->user_id)->count();

            $user = User::find($purchased->user_id);

            $response = $this->actingAs($user)->get('/api/user/products');

            $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => 
                $json->hasAll('data', 'total')
                    ->where('total', $purchasedCount)
                    ->etc()
            );
        }
    }
    
    public function test_authentication_validation_for_attach_user_products()
    {
        $response = $this->json('POST', '/api/user/products', ['sku' => 'test']);

        $response->assertUnauthorized();
    }

    public function test_sku_required_for_attach_user_products()
    {
        $user = User::find(1);

        if ($user) {
            $response = $this->actingAs($user)->postJson('/api/user/products', []);
            $response->assertStatus(422)->assertJson(fn (AssertableJson $json) => 
                $json->hasAll('message', 'errors')
                    ->has('errors', fn ($json) => 
                        $json->has('sku')
                            ->where('sku.0', 'The sku field is required.')
                    )
                    ->etc()
            );
        }        
    }

    public function test_sku_already_attached_to_user()
    {
        $purchased = Purchased::find(1);

        if ($purchased) {
            $user = User::find($purchased->user_id);

            if ($user) {
                $response = $this->actingAs($user)->postJson('/api/user/products', ['sku' => $purchased->product_sku]);
                $response->assertStatus(409)->assertJson(fn (AssertableJson $json) => 
                    $json->hasAll('message', 'errors')
                        ->has('errors', fn ($json) => 
                            $json->has('sku')
                                ->where('sku.0', 'Already exists')
                        )
                        ->etc()
                );
            }
        }
    }

    public function test_invalid_sku_to_user()
    {
        $user = User::find(1);

        if ($user) {
            $response = $this->actingAs($user)->postJson('/api/user/products', ['sku' => Str::random(8)]);
            $response->assertStatus(422)->assertJson(fn (AssertableJson $json) => 
                $json->hasAll('message', 'errors')
                    ->has('errors', fn ($json) => 
                        $json->has('sku')
                            ->where('sku.0', 'The selected sku is invalid.')
                    )
                    ->etc()
            );
        }
    }

    public function test_new_valid_sku_to_user()
    {
        $user = User::find(1);

        if ($user) {            
            $service =  new ProductService();
            $products = $service->getUserUnpurchasedProducts($user->id);

            if ($products) {
                $first = $products->first();

                $response = $this->actingAs($user)->postJson('/api/user/products', ['sku' => $first->sku]);
                $response->assertStatus(201)->assertJson(fn (AssertableJson $json) => 
                    $json->has('sku')
                    ->where('sku', $first->sku)
                    ->etc()
                );

                $this->assertTrue($service->isAlreadyPurchased($user->id, $first->sku));
            }
        }
    }

    public function test_authentication_validation_for_delete_user_product()
    {
        $response = $this->json('DELETE', '/api/user/products/'.Str::random(8));

        $response->assertUnauthorized();
    }

    public function test_un_attached_product_validation_for_delete_user_product()
    {
        $user = User::find(1);

        if ($user) { 
            $service =  new ProductService();
            $products = $service->getUserUnpurchasedProducts($user->id);

            if ($products) {
                $first = $products->first();
                $response = $this->actingAs($user)->deleteJson('/api/user/products/'.$first->sku);
                $response->assertNotFound()->assertJson(fn (AssertableJson $json) => 
                    $json->has('error')
                    ->where('error', 'Record not found')
                    ->etc()
                );

                $this->assertFalse($service->isAlreadyPurchased($user->id, $first->sku));
            }
        }
    }

    public function test_attached_user_product_delete()
    {
        $purchased = Purchased::find(1);

        if ($purchased) {
            $user = User::find($purchased->user_id);

            if ($user) {
               $response = $this->actingAs($user)->deleteJson('/api/user/products/'.$purchased->product_sku);
                $response->assertOk()->assertJson(fn (AssertableJson $json) => 
                    $json->has('success')
                        ->where('success', 'Rmoved successfully')
                        ->etc()
                );
            }
        }
    }
}