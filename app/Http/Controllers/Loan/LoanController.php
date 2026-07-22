<?php

namespace App\Http\Controllers\Loan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Loan\StoreLoanRequest;
use App\Http\Requests\Loan\UpdateLoanRequest;
use App\Models\Loan;
use App\Services\Customer\CustomerService;
use App\Services\Loan\LoanService;
use App\Services\LoanType\LoanTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\Loan\LoanCalculationService;
use Illuminate\Http\JsonResponse;
use App\Services\Date\JalaliDateService;

class LoanController extends Controller
{
    public function __construct(
        private readonly LoanService $loanService,
        private readonly CustomerService $customerService,
        private readonly LoanTypeService $loanTypeService,
        private readonly LoanCalculationService $calculator,
        private JalaliDateService $jalaliDateService

    ) {
    }

    /**
     * لیست وام‌ها
     */
    public function index(Request $request): View
    {
        $loans = $this->loanService->getPaginated(
            search: $request->input('search')
        );

        return view('loan.index', compact('loans'));
    }

    /**
     * فرم ثبت
     */
    public function create(): View
    {
        return view('loan.create', [
            'loan' => new Loan(),
            'customers' => $this->customerService->getActive(),
            'loanTypes' => $this->loanTypeService->getActive(),
        ]);
    }

    /**
     * ذخیره
     */
    public function store(
        StoreLoanRequest $request
    ): RedirectResponse {

        $this->loanService->create(
            $request->validated()
        );

        return redirect()
            ->route('loans.index')
            ->with(
                'success',
                'وام با موفقیت ثبت شد.'
            );
    }

    /**
     * نمایش
    /**
     * نمایش وام
     */
    public function show(
        Loan $loan
    ): View {

        $loan->load([

            'customer',

            'loanType',

            'installments',

            'creator',

            'updater',

        ]);

        return view(
            'loan.show',
            compact('loan')
        );
    }
    /**
     * فرم ویرایش
     */
    public function edit(
        Loan $loan
    ): View {

        return view('loan.edit', [
            'loan' => $loan,
            'customers' => $this->customerService->getActive(),
            'loanTypes' => $this->loanTypeService->getActive(),
        ]);
    }

    /**
     * بروزرسانی
     */
    public function update(
        UpdateLoanRequest $request,
        Loan $loan
    ): RedirectResponse {
        $this->loanService->update(
            $loan,
            $request->validated()
        );

        return redirect()
            ->route('loans.index')
            ->with('success', 'وام با موفقیت بروزرسانی شد.');
    }

    /**
     * حذف
     */
    public function destroy(
        Loan $loan
    ): RedirectResponse {

        $this->loanService->delete($loan);

        return redirect()
            ->route('loans.index')
            ->with(
                'success',
                'وام حذف شد.'
            );
    }

    public function calculate(Request $request): JsonResponse
    {
        $request->validate([

            'loan_amount' => ['required', 'numeric'],

            'installment_count' => ['required', 'integer', 'min:1'],

            'installment_interval' => ['required', 'integer', 'min:1'],

            'start_date' => ['required', 'string'],

        ]);

        $result = $this->calculator->generate(

            loanAmount: (int) $request->loan_amount,

            installmentCount: (int) $request->installment_count,

            startDate: $request->start_date,

            interval: (int) $request->installment_interval,

        );

        return response()->json([

            'success' => true,

            'data' => [

                /*
                |--------------------------------------------------------------------------
                | نمایش
                |--------------------------------------------------------------------------
                */

                'start_date' => $result['start_date_jalali'],

                'first_due_date' => $result['first_due_date_jalali'],

                'last_due_date' => $result['last_due_date_jalali'],

                'installment_count' => $result['installment_count'],

                'installment_amount' => number_format(
                    $result['base_installment_amount']
                ),

                /*
                |--------------------------------------------------------------------------
                | برنامه اقساط
                |--------------------------------------------------------------------------
                */

                'schedule' => collect($result['schedule'])->map(function ($item) {

                    return [

                        'number' => $item['number'],

                        'amount' => number_format($item['amount']),

                        // فقط نمایش
                        'date' => $item['jalali_date'],

                        // فقط جهت ذخیره
                        'gregorian_date' => $item['gregorian_date'],

                    ];

                }),

            ],

        ]);

    }
}
