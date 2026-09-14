<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Reports\GatewayTransactionsReportService;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;

class GatewayTransactionsReportController extends Controller
{
    public function __construct(
        private readonly GatewayTransactionsReportService $reportService
    ) {
    }

    public function index(Request $request)
    {
        // تاریخ شمسی که کاربر در فرم وارد کرده
        $from = $request->input('from');
        $to = $request->input('to');
        $type = $request->input('type');

        // تبدیل تاریخ شمسی به میلادی برای جستجو در دیتابیس
        $fromGregorian = $this->toGregorianDate($from);
        $toGregorian = $this->toGregorianDate($to);

        $transactions = $this->reportService->getTransactions(
            from: $fromGregorian,
            to: $toGregorian,
            type: $type
        );

        $summary = $this->reportService->getSummary(
            $transactions
        );

        return view(
            'admin.reports.gateway-transactions',
            compact(
                'transactions',
                'summary',
                'from',
                'to',
                'type'
            )
        );
    }

    /**
     * تبدیل تاریخ شمسی به میلادی
     */
    public function export(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $type = $request->input('type');

        $fromGregorian = $this->toGregorianDate($from);
        $toGregorian = $this->toGregorianDate($to);

        $transactions = $this->reportService->getTransactions(
            from: $fromGregorian,
            to: $toGregorian,
            type: $type
        );

        $filename = 'gateway-transactions-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ];

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // BOM برای نمایش صحیح فارسی در Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                '#',
                'تاریخ',
                'ساعت',
                'نوع عملیات',
                'پرداخت‌کننده',
                'دریافت‌کننده',
                'مبلغ',
                'درگاه',
                'کد پیگیری',
                'شناسه تراکنش بانکی',
                'شماره مرجع بانکی',
                'وضعیت',
            ]);

            foreach ($transactions as $index => $transaction) {
                $date = $transaction['date']
                    ? \Morilog\Jalali\Jalalian::fromCarbon(
                        \Carbon\Carbon::parse($transaction['date'])
                    )
                    : null;

                fputcsv($handle, [
                    $index + 1,
                    $date?->format('Y/m/d') ?? '—',
                    $transaction['date']
                        ? \Carbon\Carbon::parse($transaction['date'])->format('H:i:s')
                        : '—',
                    $transaction['type_label'] ?? '—',
                    $transaction['payer'] ?? '—',
                    $transaction['receiver'] ?? '—',
                    (int) ($transaction['amount'] ?? 0),
                    $transaction['gateway'] ?? '—',
                    $transaction['tracking_code'] ?? '—',
                    $transaction['bank_transaction_id'] ?? '—',
                    $transaction['bank_reference_number'] ?? '—',
                    $transaction['status_label'] ?? '—',
                ]);
            }

            fputcsv($handle, [
                '',
                '',
                '',
                '',
                '',
                'جمع کل',
                (int) $transactions->sum('amount'),
                '',
                '',
                '',
                '',
                '',
            ]);

            fclose($handle);
        }, $filename, $headers);
    }


    private function toGregorianDate(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        // تبدیل اعداد فارسی و عربی به انگلیسی
        $date = strtr($date, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);

        try {
            return Jalalian::fromFormat(
                'Y/m/d',
                $date
            )->toCarbon()->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
