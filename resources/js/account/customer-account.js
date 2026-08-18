document.addEventListener('DOMContentLoaded', function () {

    const accountType = document.getElementById('account_type');
    const prefix = document.getElementById('account-prefix');
    const suffix = document.getElementById('account_number_suffix');
    const accountNumber = document.getElementById('account_number');

    if (!accountType || !prefix || !suffix || !accountNumber) {
        return;
    }

    function updateAccountNumber() {

        const option = accountType.options[accountType.selectedIndex];

        const selectedPrefix = option?.dataset.prefix || '';

        prefix.textContent = selectedPrefix
            ? selectedPrefix + '-'
            : '----';

        accountNumber.value =
            selectedPrefix && suffix.value
                ? selectedPrefix + '-' + suffix.value
                : '';
    }

    accountType.addEventListener('change', updateAccountNumber);

    suffix.addEventListener('input', function () {

        this.value = this.value.replace(/\D/g, '');

        updateAccountNumber();
    });

    updateAccountNumber();
});
