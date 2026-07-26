document.addEventListener('DOMContentLoaded', function () {

    const typeSelect = document.querySelector(
        '[name="guarantor2_type"]'
    );


    const customerBox = document.getElementById(
        'guarantor2-customer'
    );

    const borrowerBox = document.getElementById(
        'guarantor2-borrower'
    );

    const externalBox = document.getElementById(
        'guarantor2-external'
    );


    function toggleGuarantor2Type(value) {

        customerBox?.classList.add('d-none');

        borrowerBox?.classList.add('d-none');

        externalBox?.classList.add('d-none');


        if (value === 'customer') {

            customerBox?.classList.remove('d-none');

        }


        if (value === 'borrower') {

            borrowerBox?.classList.remove('d-none');

        }


        if (value === 'external') {

            externalBox?.classList.remove('d-none');

        }

    }



    if (typeSelect) {

        toggleGuarantor2Type(
            typeSelect.value
        );


        typeSelect.addEventListener(
            'change',
            function () {

                toggleGuarantor2Type(
                    this.value
                );

            }
        );

    }

});
