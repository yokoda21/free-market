document.addEventListener('DOMContentLoaded', function () {
    const dropdown = document.querySelector('.custom-dropdown');
    const selected = document.getElementById('dropdown_selected');
    const selectedText = selected.querySelector('.selected-text');
    const options = document.getElementById('dropdown_options');
    const hiddenInput = document.getElementById('payment_method_hidden');
    const paymentDisplay = document.getElementById('payment-display');
    const allOptions = options.querySelectorAll('.dropdown-option');

    // 初期値の設定
    const oldValue = hiddenInput.value || '';
    if (oldValue) {
        allOptions.forEach(option => {
            if (option.dataset.value === oldValue) {
                option.classList.add('selected');
                const text = option.querySelector('.option-text').textContent;
                selectedText.textContent = text;
                if (paymentDisplay) {
                    paymentDisplay.textContent = text;
                }
            }
        });
        hiddenInput.value = oldValue;
    } else {
        // 初期値が空の場合の表示
        selectedText.textContent = '選択してください';
        if (paymentDisplay) {
            paymentDisplay.textContent = '未選択';
        }
    }

    // ドロップダウンの開閉
    selected.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.toggle('active');
    });

    // オプション選択時の処理
    allOptions.forEach(option => {
        option.addEventListener('click', function (e) {
            e.stopPropagation();

            allOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');

            const value = this.dataset.value;
            const text = this.querySelector('.option-text').textContent;

            // 値を設定
            hiddenInput.value = value;
            selectedText.textContent = text;
            if (paymentDisplay) {
                paymentDisplay.textContent = text;
            }

            dropdown.classList.remove('active');
        });
    });

    // ドロップダウン外クリックで閉じる
    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });
});