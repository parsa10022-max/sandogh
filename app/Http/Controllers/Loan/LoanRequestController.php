<?php

namespace App\Http\Controllers\Loan;

use App\Http\Controllers\Controller;
use App\Models\LoanRequest;
use App\Models\Customer;
use App\Http\Requests\LoanRequest\StoreLoanRequestRequest;
use App\Enums\LoanRequestStatus;
use App\Services\Date\JalaliDateService;
use Illuminate\Http\Request;
use App\Models\LoanType;
use App\Services\LoanType\LoanTypeService;


class LoanRequestController extends Controller
{
    private LoanTypeService $loanTypeService;

    public function __construct(
        LoanTypeService $loanTypeService
    ) {
        $this->loanTypeService = $loanTypeService;
    }

    public function index()
    {
        $loanRequests = LoanRequest::with([
            'customer',
            'loan',
        ])
            ->latest()
            ->paginate(15);

        return view('loan_requests.index', compact('loanRequests'));
    }


    public function create()
    {
        $customers = Customer::orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('loan_requests.create', compact('customers'));
    }


    public function store(StoreLoanRequestRequest $request)
    {
        LoanRequest::create([

            'customer_id' => $request->customer_id,

            'requested_amount' => $request->requested_amount,

            'description' => $request->description,

            'status' => LoanRequestStatus::PENDING,

        ]);


        return redirect()
            ->route('loan-requests.index')
            ->with('success', 'درخواست وام ثبت شد.');
    }


    public function show(LoanRequest $loanRequest)
    {
        $loanRequest->load([
            'customer',
            'loanType',
            'loan',
        ]);

        $loanTypes = $this->loanTypeService->getActive();

        return view(
            'loan_requests.show',
            compact(
                'loanRequest',
                'loanTypes'
            )
        );
    }


    public function edit(LoanRequest $loanRequest)
    {
        //
    }


    public function approve(Request $request, LoanRequest $loanRequest)
    {


        $request->validate([

            'approved_amount' => [
                'required',
                'numeric',
                'min:1',
            ],

            'review_note' => [
                'required',
                'string',
            ],

        ]);


        $loanRequest->update([

            'status' => LoanRequestStatus::APPROVED,

            'approved_amount' => $request->approved_amount,

            'loan_type_id' => $request->loan_type_id,

            'approved_installment_count' => $request->approved_installment_count,

            'approved_installment_interval' => $request->approved_installment_interval,

            'review_note' => $request->review_note,

            'reviewed_by' => auth()->id(),

            'reviewed_at' => now(),

        ]);


        return redirect()
            ->back()
            ->with('success', 'درخواست وام تایید شد.');
    }



    public function reject(Request $request, LoanRequest $loanRequest)
    {

        $request->validate([

            'review_note' => [
                'required',
                'string',
            ],

            'next_review_date' => [
                'nullable',
                'string',
            ],

        ]);


        $nextReviewDate = null;


        if ($request->filled('next_review_date')) {

            $nextReviewDate = app(JalaliDateService::class)
                ->toGregorian($request->next_review_date);

        }



        $loanRequest->update([

            'status' => LoanRequestStatus::REJECTED,

            'review_note' => $request->review_note,

            'next_review_date' => $nextReviewDate,

            'reviewed_by' => auth()->id(),

            'reviewed_at' => now(),

        ]);


        return redirect()
            ->back()
            ->with('success', 'درخواست وام رد شد.');
    }

    public function updateReviewDate(Request $request, LoanRequest $loanRequest)
    {
        $request->validate([

            'next_review_date' => [
                'required',
                'string',
            ],

        ]);


        $nextReviewDate = app(\App\Services\Date\JalaliDateService::class)
            ->toGregorian($request->next_review_date);


        $loanRequest->update([

            'next_review_date' => $nextReviewDate,

        ]);


        return redirect()
            ->back()
            ->with('success', 'تاریخ مراجعه مجدد ثبت شد.');
    }


    public function update(LoanRequest $loanRequest)
    {
        //
    }


    public function destroy(LoanRequest $loanRequest)
    {
        //
    }
}
