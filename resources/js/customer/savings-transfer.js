document.addEventListener('DOMContentLoaded', function () {


    const searchButton =
        document.getElementById('search_customer');


    if (!searchButton) {
        return;
    }



    searchButton.addEventListener('click', function () {


        const keyword =
            document.getElementById(
                'customer_keyword'
            ).value;



        fetch(
            searchUrl,
            {

                method: 'POST',

                headers: {

                    'Content-Type':
                        'application/json',

                    'X-CSRF-TOKEN':
                    csrfToken

                },


                body: JSON.stringify({

                    keyword: keyword

                })

            }

        )

            .then(response => response.json())


            .then(data => {


                const box =
                    document.getElementById(
                        'customer-result'
                    );



                if (data.found) {


                    document
                        .getElementById(
                            'receiver_customer_id'
                        )
                        .value =
                        data.customer.id;



                    box.className =
                        'alert alert-info';



                    box.classList.remove(
                        'd-none'
                    );


                    box.innerHTML = `

                    <strong>
                    عضو مقصد:
                    </strong>

                    ${data.customer.name}

                    <br>

                    شماره عضویت:
                    ${data.customer.code}

                `;



                    document
                        .getElementById(
                            'payment_button'
                        )
                        .disabled = false;



                } else {


                    box.className =
                        'alert alert-danger';



                    box.classList.remove(
                        'd-none'
                    );


                    box.innerHTML =
                        data.message;



                    document
                        .getElementById(
                            'payment_button'
                        )
                        .disabled = true;


                }


            });


    });


});
