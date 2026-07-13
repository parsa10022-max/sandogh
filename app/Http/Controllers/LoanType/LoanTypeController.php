<?php

namespace App\Http\Controllers\LoanType;
use App\Http\Controllers\Controller;

use App\Http\Requests\LoanType\StoreLoanTypeRequest;
use App\Http\Requests\LoanType\UpdateLoanTypeRequest;
use App\Models\LoanType;
use App\Services\LoanType\LoanTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;


class LoanTypeController extends Controller
{
    public function __construct(
        private readonly LoanTypeService $loanTypeService
    ) {
    }

    public function index(Request $request): View
    {
        $loanTypes = $this->loanTypeService->getPaginated(
            search: $request->input('search')
        );

        return view('loan-types.index', compact('loanTypes'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('loan-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLoanTypeRequest $request): RedirectResponse
    {
        $this->loanTypeService->create(
            $request->validated()
        );

        return redirect()
            ->route('loan-types.index')
            ->with('success', 'نوع وام با موفقیت ثبت شد.');
    }


    public function edit(LoanType $loanType): View
    {
        return view('loan-types.edit', compact('loanType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateLoanTypeRequest $request,
        LoanType $loanType
    ): RedirectResponse {

        $this->loanTypeService->update(
            $loanType,
            $request->validated()
        );

        return redirect()
            ->route('loan-types.index')
            ->with('success', 'نوع وام با موفقیت ویرایش شد.');
    }


    public function changeStatus(
        LoanType $loanType
    ): RedirectResponse {

        $this->loanTypeService->changeStatus($loanType);

        return redirect()
            ->route('loan-types.index')
            ->with('success', 'وضعیت نوع وام تغییر کرد.');
    }
}
