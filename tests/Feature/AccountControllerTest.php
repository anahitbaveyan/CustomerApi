<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Customer;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test deposit functionality.
     *
     * @return void
     */
    public function test_deposit()
    {
        $customer = Customer::factory()->create(['balance' => 100]);
        $response = $this->postJson("/api/accounts/{$customer->id}/deposit", ['funds' => 50]);
        $this->assertWithDepositResponse($response, $customer);
    }

    /**
     * Test withdrawal functionality with sufficient balance.
     *
     * @return void
     */
    public function test_withdraw_sufficient_balance()
    {
        $customer = Customer::factory()->create(['balance' => 100]);
        $response = $this->postJson("/api/accounts/{$customer->id}/withdraw", ['funds' => 50]);
        $this->assertWithWithdrawSufficientBalanceResponse($response, $customer);
    }

    /**
     * Test withdrawal functionality with insufficient balance.
     *
     * @return void
     */
    public function test_withdraw_insufficient_balance()
    {
        $customer = Customer::factory()->create(['balance' => 50]);
        $response = $this->postJson("/api/accounts/{$customer->id}/withdraw", ['funds' => 100]);
        $this->assertWithWithdrawInsufficientBalanceResponse($response);
    }

    /**
     * Test transfer functionality with sufficient balance.
     *
     * @return void
     */
    public function test_transfer_sufficient_balance()
    {
        $customerOne = Customer::factory()->create(['balance' => 100]);
        $customerSecond = Customer::factory()->create(['balance' => 50]);

        $response = $this->postJson("/api/accounts/transfer", [
            'from' => $customerOne->id,
            'to' => $customerSecond->id,
            'funds' => 50
        ]);

        $this->assertWithTransferSufficientBalanceResponse($response, $customerOne, $customerSecond);
    }

    /**
     * Test preventing transfer with insufficient balance.
     *
     * @return void
     */
    public function test_transfer_insufficient_balance()
    {
        $customerOne = Customer::factory()->create(['balance' => 50]);
        $customerSecond = Customer::factory()->create(['balance' => 50]);

        $response = $this->postJson("/api/accounts/transfer", [
            'from' => $customerOne->id,
            'to' => $customerSecond->id,
            'funds' => 100
        ]);

        $this->assertWithTransferInsufficientBalanceResponse($response);
    }

    /**
     * Test rebuilt balance.
     *
     * @return void
     */
    public function test_rebuilt_balance()
    {
        $customer = Customer::factory()->create(['balance' => 100]);
        $this->postJson("/api/accounts/{$customer->id}/deposit", ['funds' => 50]);
        $this->postJson("/api/accounts/{$customer->id}/withdraw", ['funds' => 25]);

        $response = $this->getJson("/api/accounts/{$customer->id}/rebuilt-balance");
        $this->assertWithRebuiltBalanceResponse($response);
    }



    protected function assertWithDepositResponse($response, $customer)
    {
        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $customer->id,
                     'balance' => 150,
                 ]);
    }

    protected function assertWithWithdrawSufficientBalanceResponse($response, $customer)
    {
        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $customer->id,
                     'balance' => 50,
                 ]);
    }

    protected function assertWithWithdrawInsufficientBalanceResponse($response)
    {
        $response->assertStatus(400)
                 ->assertJson([
                     'message' => 'Insufficient funds for transfer.',
                 ]);
    }

    protected function assertWithTransferSufficientBalanceResponse($response, $customerOne, $customerSecond)
    {
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Transfer successful.',
                     'from' => ['id' => $customerOne->id, 'balance' => 50],
                     'to' => ['id' => $customerSecond->id, 'balance' => 100],
                 ]);
    }

    protected function assertWithTransferInsufficientBalanceResponse($response)
    {
        $response->assertStatus(400)
                 ->assertJson([
                     'error' => 'Transfer failed.',
                     'message' => 'Insufficient funds for transfer.',
                 ]);
    }

    protected function assertWithRebuiltBalanceResponse($response)
    {
        $response->assertStatus(200)
                 ->assertJson([
                     'rebuilt_balance' => 150,
                 ]);
    }
}
