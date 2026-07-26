@php

    $guarantor1 = $loan->guarantors
        ->where('guarantor_order', 1)
        ->first();


    $guarantor2 = $loan->guarantors
        ->where('guarantor_order', 2)
        ->first();


    $guarantor2Type = old(
        'guarantor2_type',
        $guarantor2?->guarantor_type?->value
        ?? $guarantor2?->guarantor_type
    );

@endphp



<div class="row g-4">


    {{-- ================= ضامن اول ================= --}}

    <div class="col-md-6">

        <div class="card shadow-sm border-0 h-100">


            <div class="card-header bg-light border-bottom">

                <h6 class="mb-0 fw-bold text-primary">

                    <i class="bi bi-person-badge me-2"></i>

                    ضامن اول

                </h6>

            </div>



            <div class="card-body">


                @include('customer._picker',[

                    'name'=>'guarantor1_customer_id',

                    'label'=>'کد مشتری',

                    'required'=>true,

                    'value'=>old(
                        'guarantor1_customer_id',
                        $guarantor1?->customer_id
                    )

                ])



                <input type="hidden"
                       name="guarantor1_type"
                       value="customer">



                <x-inputs.select-input

                    name="guarantor1_guarantee_type"

                    label="نوع مدرک ضمانت"

                    :options="\App\Enums\GuaranteeType::options()"

                    :value="old(
                    'guarantor1_guarantee_type',
                    $guarantor1?->guarantee_type?->value
                    ?? $guarantor1?->guarantee_type
                )"

                    required

                />


            </div>

        </div>

    </div>





    {{-- ================= ضامن دوم ================= --}}

    <div class="col-md-6">


        <div class="card shadow-sm border-0 h-100">


            <div class="card-header bg-light border-bottom">

                <h6 class="mb-0 fw-bold text-primary">

                    <i class="bi bi-people-fill me-2"></i>

                    ضامن دوم

                </h6>

            </div>



            <div class="card-body">



                <x-inputs.select-input

                    name="guarantor2_type"

                    label="نوع ضامن"

                    :options="\App\Enums\GuarantorType::options()"

                    :value="$guarantor2Type"

                    required

                />





                {{-- عضو صندوق --}}

                <div id="guarantor2-customer"

                     class="mt-3 {{ $guarantor2Type == 'customer' ? '' : 'd-none' }}">


                    @include('customer._picker',[

                        'name'=>'guarantor2_customer_id',

                        'label'=>'کد مشتری',

                        'value'=>old(
                            'guarantor2_customer_id',
                            $guarantor2?->customer_id
                        )

                    ])


                </div>





                {{-- خود وام گیرنده --}}

                <div id="guarantor2-borrower"

                     class="mt-3 {{ $guarantor2Type == 'borrower' ? '' : 'd-none' }}">


                    <div class="alert alert-info mb-0">


                        <i class="bi bi-person-check me-2"></i>


                        ضامن دوم همان وام گیرنده است.


                    </div>


                </div>





                {{-- خارج از صندوق --}}

                <div id="guarantor2-external"

                     class="mt-3 {{ $guarantor2Type == 'external' ? '' : 'd-none' }}">



                    <x-inputs.text-input

                        name="guarantor2_first_name"

                        label="نام"

                        :value="old(
    'guarantor2_first_name',
    $guarantor2?->first_name
)"

                    />



                    <x-inputs.text-input

                        name="guarantor2_last_name"

                        label="نام خانوادگی"

                        :value="old(
    'guarantor2_last_name',
    $guarantor2?->last_name
)"

                    />



                    <x-inputs.text-input

                        name="guarantor2_national_code"

                        label="کد ملی"

                        :value="old(
    'guarantor2_national_code',
    $guarantor2?->national_code
)"

                    />



                    <x-inputs.text-input

                        name="guarantor2_mobile"

                        label="موبایل"

                        :value="old(
    'guarantor2_mobile',
    $guarantor2?->mobile
)"

                    />


                </div>





                <x-inputs.select-input

                    name="guarantor2_guarantee_type"

                    label="نوع مدرک ضمانت"

                    :options="\App\Enums\GuaranteeType::options()"

                    :value="old(
    'guarantor2_guarantee_type',
    $guarantor2?->guarantee_type?->value
    ?? $guarantor2?->guarantee_type
)"

                    required

                />



            </div>

        </div>

    </div>


</div>
