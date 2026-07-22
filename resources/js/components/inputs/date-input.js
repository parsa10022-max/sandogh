import '@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js';

document.addEventListener('DOMContentLoaded', () => {

    jalaliDatepicker.startWatch({

        selector: 'input[data-jdp]',

        format: 'YYYY/MM/DD',

        autoClose: true,

        hideAfterChange: true,

    });

});
