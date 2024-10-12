<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Constants\MessageConstants;
use App\Http\Controllers\CustomerController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AccountController extends Controller
{

    protected $customerController;

   
    public function __construct(CustomerController $customerController)
    {
        $this->customerController = $customerController;
    }
    /**
     * Get the balance of a customer.
     * 
     */
    public function getBalance(int $id)
    {
        try {
            $customer = Customer::findOrFail($id);
            return response()->json(['balance' => $customer->balance], 200);
        } catch (ModelNotFoundException $e) {
            return $this->customerNotFoundResponse();
        }
    }

    /**
     * Deposit funds into a customer's account.
     */
    public function deposit(Request $request, int $id)
    {
        $validated = $this->validateFundsInput($request);
        try {
            $customer = $this->findCustomerById($id);
            $this->updateBalance($customer, $validated['funds']);
            $this->logTransaction($customer->id, $validated['funds'], 'deposit');

            return response()->json($customer, 200);
        } catch (ModelNotFoundException $e) {
            return $this->customerNotFoundResponse();
        }
    }

    /**
     * Withdraw funds from a customer's account.
     */
    public function withdraw(Request $request, int $id)
    {
        $validated = $this->validateFundsInput($request);
        try {
            $customer = $this->findCustomerById($id);

            if ($customer->balance < $validated['funds']) {
                return $this->insufficientFundsResponse();
            }

            $this->updateBalance($customer, -$validated['funds']);
            $this->logTransaction($customer->id, -$validated['funds'], 'withdraw');
            return response()->json($customer, 200);
        } catch (ModelNotFoundException $e) {
            return $this->customerNotFoundResponse();
        }
    }

    /**
     * Transfer funds between two customers.
     */
    public function transfer(Request $request)
    {
        $validated = $this->validateTransferInput($request);
        DB::beginTransaction();
        try {
            $fromCustomer = $this->findCustomerById($validated['from'], true);
            $toCustomer = $this->findCustomerById($validated['to'], true);

            if ($fromCustomer->balance < $validated['funds']) {
                throw new \Exception(MessageConstants::INSUFFICIENT_FUNDS);
            }
            $this->updateBalance($fromCustomer, -$validated['funds']);
            $this->updateBalance($toCustomer, $validated['funds']);
            $this->logTransaction($fromCustomer->id, -$validated['funds'], 'transfer', $toCustomer->id);
            $this->logTransaction($toCustomer->id, $validated['funds'], 'transfer', $fromCustomer->id);
            DB::commit();
            return $this->TransferSuccessfullResponse($fromCustomer,$toCustomer);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->TransferFaildResponse($e);
           
        }
    }

    /**
     * Get the rebuilt balance by summing all transactions.
     */
    public function getRebuiltBalance(int $id)
    {
        $customer = Customer::findOrFail($id);
        $transactionsSum = DB::table('transactions')
            ->where('customer_id', $id)
            ->sum('amount');
        $rebuiltBalance = $customer->balance + $transactionsSum;

        return response()->json(['rebuilt_balance' => $rebuiltBalance], 200);
    }

    /**
     * Get transaction audit log for a customer.
     */

    public function getTransactionAudit(int $id)
    {
        try {
        
            $customer = $this->findCustomerById($id);
            
            $transactions = $this->getCustomerTransactions($id);
            if ($transactions->isEmpty()) {
                return $this->respondWithNoTransactions();
            }
            return $this->respondWithAuditLog($customer, $transactions);

        } catch (ModelNotFoundException $e) {
        
            return $this->customerController->customersNotFoundResponse();
        
        }
    }

    protected function findCustomerById(int $id, bool $lockForUpdate = false): Customer
    {
        return $lockForUpdate
            ? Customer::where('id', $id)->lockForUpdate()->firstOrFail()
            : Customer::findOrFail($id);
    }

    protected function updateBalance(Customer $customer, float $amount): void
    {
        $customer->balance += $amount;
        $customer->save();
    }

    protected function logTransaction(int $customerId, float $amount, string $type, int $relatedCustomerId = null): void
    {
        DB::table('transactions')->insert([
            'customer_id' => $customerId,
            'amount' => $amount,
            'type' => $type,
            'related_customer_id' => $relatedCustomerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function validateFundsInput(Request $request): array
    {
        return $request->validate([
            'funds' => 'required|numeric|min:0.01',
        ]);
    }

    protected function validateTransferInput(Request $request): array
    {
        return $request->validate([
            'from' => 'required|exists:customers,id',
            'to' => 'required|exists:customers,id|different:from',
            'funds' => 'required|numeric|min:0.01',
        ]);
    }

    protected function getCustomerTransactions(int $id)
    {
        return DB::table('transactions')
            ->where('customer_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    protected function respondWithAuditLog(Customer $customer, $transactions)
    {
        return response()->json([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name . ' ' . $customer->surname,
            'total_transactions' => $transactions->count(),
            'transactions' => $transactions,
        ], 200);
    }

    protected function TransferSuccessfullResponse($fromCustomer, $toCustomer)
    {
        return response()->json([
            'message' => MessageConstants::TRANSFER_SUCCESS,
            'from' => $fromCustomer,
            'to' => $toCustomer
        ],200);
    }
    
    protected function TransferFaildResponse($e)
    {
        return response()->json([
            'error' => MessageConstants::TRANSFER_FAILED,
            'message' => $e->getMessage()
        ], 400);
    }

    
    protected function respondWithNoTransactions()
    {
        return response()->json([
            'message' => MessageConstants::TRANSFER_NOT_FOUND_FOR_CUSTOMERS,
        ], 404);
    }

    protected function customerNotFoundResponse()
    {
        return response()->json([
            'message' => MessageConstants::CUSTOMER_NOT_FOUND,
        ], 404);
    }

    protected function insufficientFundsResponse()
    {
        return response()->json([
            'message' => MessageConstants::INSUFFICIENT_FUNDS,
        ], 400);
    }
}
