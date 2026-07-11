<?php

namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Models\Customer;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\Customer\UpdateCustomerRequest;


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


    public function edit(Customer $customer): View
    {
        return view('customer.edit', compact('customer'));
    }

    public function update(
        UpdateCustomerRequest $request,
        Customer $customer
    ) {
        $this->customerService->update(
            $customer,
            $request->validated()
        );

        return redirect()
            ->route('customers.index')
            ->with('success', 'اطلاعات مشتری با موفقیت بروزرسانی شد.');
    }
    public function destroy(Customer $customer)
    {
        $this->customerService->delete($customer);

        return redirect()
            ->route('customers.index')
            ->with('success', 'مشتری با موفقیت حذف شد.');
    }

    public function show(Customer $customer): View
    {
        return view('customer.show', compact('customer'));
    }

    public function archive(): View
    {
        $customers = $this->customerService
            ->getArchived();

        return view(
            'customer.archive',
            compact('customers')
        );
    }

    public function restore(int $id)
    {
        $this->customerService
            ->restore($id);

        return redirect()
            ->route('customers.archive')
            ->with(
                'success',
                'مشتری با موفقیت بازگردانی شد.'
            );
    }

}
