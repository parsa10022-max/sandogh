<?php

namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Models\Customer;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService
    ) {
    }

    public function index(Request $request): View
    {
        $customers = $this->customerService->getPaginated(
            search: $request->input('search')
        );

        return view('customer.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customer.create', [
            'customer' => new Customer(),
        ]);
    }

    public function store(StoreCustomerRequest $request)
    {
        $this->customerService->create(
            $request->validated()
        );

        return redirect()
            ->route('customers.index')
            ->with('success', 'مشتری با موفقیت ثبت شد.');
    }
}
