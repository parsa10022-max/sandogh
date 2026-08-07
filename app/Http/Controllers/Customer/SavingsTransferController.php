<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Savings\SavingsTransferService;
use Illuminate\Http\Request;


class SavingsTransferController extends Controller
{

    public function __construct(
        private readonly SavingsTransferService $savingsTransferService,
    ) {
    }



    /**
     * فرم واریز
     */
    public function create()
    {
        return view(
            'customer.savings-transfer.create'
        );
    }



    /**
     * جستجوی عضو مقصد
     */
    public function search(Request $request)
    {
        $request->validate([

            'keyword' => 'required|string',

        ]);


        $keyword = $request->keyword;



        $customer = Customer::query()

            ->active()

            ->where(function ($query) use ($keyword) {


                $query->where(
                    'customer_code',
                    $keyword
                )


                    ->orWhere(
                        'national_code',
                        $keyword
                    )


                    ->orWhereHas(
                        'accounts',
                        function ($q) use ($keyword) {

                            $q->where(
                                'account_number',
                                $keyword
                            );

                        }
                    );


            })

            ->first();



        if (! $customer) {

            return response()->json([

                'found' => false,

                'message' =>
                    'عضو مورد نظر پیدا نشد.'

            ]);

        }



        return response()->json([

            'found' => true,


            'customer' => [

                'id' =>
                    $customer->id,


                'code' =>
                    $customer->customer_code,


                'name' =>
                    $customer->full_name,

            ]

        ]);

    }



    /**
     * شروع پرداخت
     */
    public function store(Request $request)
    {

        $request->validate([

            'receiver_customer_id' =>
                'required|exists:customers,id',

            'amount' =>
                'required|integer|min:1000',

        ]);



        $receiver = Customer::findOrFail(
            $request->receiver_customer_id
        );



        $result =
            $this->savingsTransferService->startPayment(

                $receiver,

                $request->amount

            );



        return redirect(
            $result['gateway']['redirect_url']
        );

    }

    public function ownDepositCreate()
    {
        $customer = auth()->user()->customer;

        $account = $customer->accounts()
            ->where(
                'account_type',
                \App\Enums\AccountType::SAVING->value
            )
            ->where(
                'status',
                \App\Enums\AccountStatus::ACTIVE->value
            )
            ->firstOrFail();


        return view(
            'customer.savings.deposit.create',
            compact('account')
        );
    }

    public function ownDepositStore(Request $request)
    {
        $request->validate([

            'amount' => [
                'required',
                'integer',
                'min:50000'
            ],

        ]);


        $customer = auth()->user()->customer;


        $response = $this->savingsTransferService
            ->startPayment(
                $customer,
                (int)$request->amount
            );


        return redirect()->away(
            $response['gateway']['redirect_url']
        );
    }

    public function transactions()
    {
        $customer = auth()->user()->customer;


        $account = $customer->accounts()
            ->where(
                'account_type',
                \App\Enums\AccountType::SAVING->value
            )
            ->where(
                'status',
                \App\Enums\AccountStatus::ACTIVE->value
            )
            ->firstOrFail();


        $transactions = $account->transactions()
            ->latest('transaction_date')
            ->paginate(20);


        return view(
            'customer.savings.transactions',
            compact(
                'account',
                'transactions'
            )
        );
    }

}
