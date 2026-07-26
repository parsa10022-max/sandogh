<div class="card shadow-sm border-0 mb-4">


    <div class="card-header bg-light border-bottom">

        <h6 class="mb-0 fw-bold text-primary">

            <i class="bi bi-people-fill me-2"></i>

            ضامن‌ها

        </h6>

    </div>



    <div class="card-body">


        <div class="row g-3">


            @foreach($loan->guarantors as $guarantor)


                @php


      $type = $guarantor->guarantor_type?->value
          ?? $guarantor->guarantor_type;

      switch ($type) {

          /*
          |--------------------------------------------------------------------------
          | ضامن عضو صندوق
          |--------------------------------------------------------------------------
          */
          case 'customer':

              $person = $guarantor->customer;

              $name = trim(
                  ($person?->first_name ?? '')
                  .' '.
                  ($person?->last_name ?? '')
              );

              $mobile = $person?->mobile ?? '-';

              break;

          /*
          |--------------------------------------------------------------------------
          | ضامن = خود وام گیرنده
          |--------------------------------------------------------------------------
          */
          case 'borrower':

              $person = $loan->customer;

              $name = trim(
                  ($person?->first_name ?? '')
                  .' '.
                  ($person?->last_name ?? '')
              );

              $mobile = $person?->mobile ?? '-';

              break;

          /*
          |--------------------------------------------------------------------------
          | شخص خارج از صندوق
          |--------------------------------------------------------------------------
          */
          default:

              $name = trim(
                  ($guarantor->first_name ?? '')
                  .' '.
                  ($guarantor->last_name ?? '')
              );

              $mobile = $guarantor->mobile ?? '-';

              break;
      }

                @endphp


                <div class="col-md-6">


                    <div class="border rounded-3 p-3 bg-light h-100">



                        <div class="d-flex align-items-center mb-3">


                            <div class="me-2">


                                @if($guarantor->guarantor_order == 1)

                                    <i class="bi bi-person-badge fs-4 text-primary"></i>

                                @else

                                    <i class="bi bi-person-check fs-4 text-success"></i>

                                @endif


                            </div>



                            <div class="fw-bold text-primary">

                                ضامن
                                {{ $guarantor->guarantor_order }}

                            </div>


                        </div>





                        <div class="small mb-2">


                            <i class="bi bi-person me-1 text-muted"></i>


                            نام:


                            <strong>

                                {{ $name ?: '-' }}

                            </strong>


                        </div>





                        <div class="small mb-2">


                            <i class="bi bi-phone me-1 text-muted"></i>


                            موبایل:


                            <strong>

                                {{ $mobile }}

                            </strong>


                        </div>





                        <div class="small">


                            <i class="bi bi-file-earmark-text me-1 text-muted"></i>


                            مدرک ضمانت:


                            <span class="badge bg-primary-subtle text-primary">


                                {{
                                    $guarantor->guarantee_type?->label()
                                    ?? $guarantor->guarantee_type
                                    ?? '-'
                                }}


                            </span>


                        </div>



                    </div>


                </div>


            @endforeach


        </div>


    </div>


</div>
