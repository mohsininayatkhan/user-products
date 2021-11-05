<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\User;
use App\Models\Purchased;
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

        $response->assertStatus(401);
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

        $response->assertStatus(401);
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
}
