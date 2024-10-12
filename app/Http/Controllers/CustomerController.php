<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Constants\MessageConstants;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
        if ($customers->isEmpty()) {
            return $this->customersNotFoundResponse();
        }
        return response()->json($customers, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'balance' => 'required|numeric|min:0',
        ]);

        $customer = Customer::create($validated);
        return $this->customerCreatedResponse($customer);
    }

    public function show($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            return response()->json($customer, 200);
        } catch (ModelNotFoundException $e) {
            return $this->customerNotFoundResponse();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $customer = Customer::findOrFail($id);
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'surname' => 'sometimes|string|max:255',
                'balance' => 'sometimes|numeric|min:0',
            ]);

            $customer->update($validated);
            return $this->customerUpdateResponse($customer);
        } catch (ModelNotFoundException $e) {
            return $this->customerNotFoundResponse();
        }
    }

    public function destroy($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            $customer->delete(); 
            return response()->json([
                'message' => MessageConstants::CUSTOMER_DELETED,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->customerNotFoundResponse();
        }
    }

    protected function customerNotFoundResponse()
    {
        return response()->json([
            'message' => MessageConstants::CUSTOMER_NOT_FOUND,
        ], 404);
    }

    protected function customerUpdateResponse($customer)
    {
        return response()->json([
            'message' => MessageConstants::CUSTOMER_UPDATED,
            'customer' => $customer,
        ], 200);
    }

    protected function customerCreatedResponse($customer)
    {
        return response()->json([
            'message' => MessageConstants::CUSTOMER_CREATED,
            'customer' => $customer,
        ], 201);
    }

    public function customersNotFoundResponse()
    {
        return response()->json([
            'message' => MessageConstants::CUSTOMER_NOT_FOUND,
            'data' => []
        ], 200);
    }
}
