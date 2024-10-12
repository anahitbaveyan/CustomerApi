<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Customer;
use App\Constants\MessageConstants;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test index functionality.
     *
     * @return void
     */
    public function test_index_no_customers()
    {
        $response = $this->getJson('/api/customers');
        $this->assertNoCustomersResponse($response);
       
    }

    public function test_index_with_customers()
    {
        $customer = Customer::factory()->create();
        $response = $this->getJson('/api/customers');
        $this->assertWithCustomersResponse($response,$customer);
    }

    /**
     * Test store functionality.
     *
     * @return void
     */
    public function test_store_customer()
    {
        $data = [
            'name' => 'Anahit',
            'surname' => 'Baveyan',
            'balance' => 100
        ];

        $response = $this->postJson('/api/customers', $data);
        $this->assertStoreCustomersResponse($response);
    }

    /**
     * Test show functionality.
     *
     * @return void
     */
    public function test_show_customer_not_found()
    {
        $response = $this->getJson('/api/customers/40');
        $this->assertNotFoundCustomersResponse($response);
        
    }

    public function test_show_customer_found()
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson("/api/customers/{$customer->id}");
        $this->assertWithCustomersResponse($response, $customer);
    }

    /**
     * Test update functionality.
     *
     * @return void
     */
    public function test_update_customer()
    {
        $customer = Customer::factory()->create();
        $data = ['name' => 'Anahit', 'surname' => 'Baveyan', 'balance' => 200];
        $response = $this->putJson("/api/customers/{$customer->id}", $data);
        $this->assertUpdateCustomersResponse($response);
    }

    /**
     * Test delete functionality.
     *
     * @return void
     */
    public function test_destroy_customer()
    {
        $customer = Customer::factory()->create();
        $response = $this->deleteJson("/api/customers/{$customer->id}");
        $this->assertDeletedCustomersResponse($response);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    protected function  assertNoCustomersResponse($response)
    {
           $response->assertStatus(200)
                 ->assertJson([
                     'message' => MessageConstants::CUSTOMER_NOT_FOUND,
                     'data' => []
                 ]);
    }
    protected function  assertWithCustomersResponse($response,$customer)
    {
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $customer->id,
                'name' => $customer->name,
                'surname' => $customer->surname
            ]);
    }
   

    protected function  assertStoreCustomersResponse($response)
    {
        $response->assertStatus(201)
                 ->assertJson([
                     'message' => MessageConstants::CUSTOMER_CREATED,
                     'customer' => [
                         'name' => 'Anahit',
                         'surname' => 'Baveyan',
                         'balance' => 100
                     ]
                 ]);
    }

    protected function  assertNotFoundCustomersResponse($response)
    {
        $response->assertStatus(404)
                ->assertJson([
                    'message' => MessageConstants::CUSTOMER_NOT_FOUND
        ]);
    }

    protected function  assertUpdateCustomersResponse($response)
    {
        $response->assertStatus(200)
        ->assertJson([
            'message' => MessageConstants::CUSTOMER_UPDATED,
            'customer' => [
                'name' => 'Anahit',
                'surname' => 'Baveyan',
                'balance' => 200
            ]
        ]);
    }

    protected function  assertDeletedCustomersResponse($response)
    {
        $response->assertStatus(200)
        ->assertJson([
            'message' => MessageConstants::CUSTOMER_DELETED
        ]);
    }
   
}
