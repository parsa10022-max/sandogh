<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FundStatistic\UpdateFundStatisticRequest;
use App\Models\FundStatistic;
use App\Services\FundStatistic\FundStatisticService;

class FundStatisticController extends Controller
{
    public function __construct(
        private readonly FundStatisticService $service
    ) {
    }

    public function edit()
    {
        $statistic = FundStatistic::query()->first();

        return view('admin.fund-statistics.edit', compact('statistic'));
    }

    public function update(UpdateFundStatisticRequest $request)
    {
        $this->service->update($request->validated());

        return back()->with('success', 'آمار صندوق با موفقیت به‌روزرسانی شد.');
    }
}
