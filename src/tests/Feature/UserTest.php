<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\User;
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

    /*public function test_get_all_product_endpoint_status_code_is_200()
    {
        $response = $this->json('GET', '/api/products');

        $response->assertStatus(200);        
    }*/
}
