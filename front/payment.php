<?php
session_start();
if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in']) {
    header('Location: login.php');
    exit;
}

$payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if (!$payment_id) {
    header('Location: bookings.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оплата заказа - ЮKassa (Тестовый режим)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .payment-card { max-width: 450px; margin: 60px auto; border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
        .payment-header { background-color: #007aff; color: white; padding: 30px; text-align: center; }
        .yoo-logo { height: 32px; margin-bottom: 15px; }
        .amount-display { font-size: 2.5rem; font-weight: 700; margin-bottom: 5px; }
        .payment-body { padding: 40px; background: white; }
        .btn-pay { background-color: #007aff; color: white; border: none; border-radius: 12px; padding: 16px; font-weight: 600; font-size: 1.1rem; width: 100%; transition: 0.2s; }
        .btn-pay:hover { background-color: #0062cc; color: white; transform: translateY(-1px); }
        .test-badge { background-color: #ff9500; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-bottom: 20px; display: inline-block; }
        .card-input-wrap { position: relative; margin-bottom: 20px; }
        .card-input-wrap i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #adb5bd; }
        .card-input-wrap input { padding-left: 45px; height: 55px; border-radius: 10px; border: 1px solid #dee2e6; font-size: 1.1rem; }
        .secure-footer { text-align: center; margin-top: 30px; color: #6c757d; font-size: 0.85rem; }
    </style>
</head>
<body>

<div class="container">
    <div class="payment-card card">
        <div class="payment-header">
            <div class="test-badge">Тестовый режим</div>
            <div class="yoo-logo">
                <svg width="120" height="30" viewBox="0 0 120 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14.5 0C6.5 0 0 6.5 0 14.5C0 22.5 6.5 29 14.5 29C22.5 29 29 22.5 29 14.5C29 6.5 22.5 0 14.5 0ZM14.5 25C8.7 25 4 20.3 4 14.5C4 8.7 8.7 4 14.5 4C20.3 4 25 8.7 25 14.5C25 20.3 20.3 25 14.5 25Z" fill="white"/>
                    <path d="M45 5V25H50V5H45ZM55 5V25H60V5H55ZM65 5V25H70V5H65Z" fill="white"/>
                    <text x="35" y="22" fill="white" style="font-family: Arial; font-weight: bold; font-size: 18px;">ЮKassa</text>
                </svg>
            </div>
            <div class="amount-display"><?= number_format($amount, 0, '.', ' ') ?> ₽</div>
            <div class="opacity-75">Заказ №<?= $booking_id ?></div>
        </div>
        <div class="payment-body">
            <h5 class="fw-bold mb-4">Способ оплаты</h5>
            
            <div class="card-input-wrap">
                <i class="bi bi-credit-card-2-front fs-5"></i>
                <input type="text" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" id="cardNumber" value="1111 2222 3333 4444">
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <input type="text" class="form-control" placeholder="ММ / ГГ" maxlength="5" style="height: 55px; border-radius: 10px;" value="12/26">
                </div>
                <div class="col-6">
                    <input type="password" class="form-control" placeholder="CVC" maxlength="3" style="height: 55px; border-radius: 10px;" value="123">
                </div>
            </div>

            <button class="btn-pay" id="payButton">
                <span id="btnText">Оплатить</span>
                <span id="btnLoader" class="spinner-border spinner-border-sm d-none"></span>
            </button>

            <div class="secure-footer">
                <i class="bi bi-shield-lock-fill me-1"></i> Безопасная оплата через ЮKassa
                <div class="mt-2">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" height="15" class="me-2 opacity-50">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" height="15" class="me-2 opacity-50">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/b9/Mir-logo.svg" height="15" class="opacity-50">
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    const paymentId = <?= $payment_id ?>;
    
    $('#payButton').on('click', function() {
        const $btn = $(this);
        $('#btnText').addClass('d-none');
        $('#btnLoader').removeClass('d-none');
        $btn.prop('disabled', true);

        // Имитируем задержку банковской транзакции
        setTimeout(function() {
            $.ajax({
                url: 'http://' + window.location.hostname + ':8000/payments/confirm',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ payment_id: paymentId }),
                success: function(res) {
                    if (res.success) {
                        alert('Оплата прошла успешно!');
                        window.location.href = 'bookings.php';
                    } else {
                        alert('Ошибка оплаты: ' + (res.error || 'Неизвестная ошибка'));
                        $('#btnText').removeClass('d-none');
                        $('#btnLoader').addClass('d-none');
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Ошибка сервера при подтверждении платежа');
                    $('#btnText').removeClass('d-none');
                    $('#btnLoader').addClass('d-none');
                    $btn.prop('disabled', false);
                }
            });
        }, 2000);
    });

    // Форматирование номера карты
    $('#cardNumber').on('input', function() {
        let val = $(this).val().replace(/\D/g, '');
        let newVal = '';
        for (let i = 0; i < val.length; i++) {
            if (i > 0 && i % 4 === 0) newVal += ' ';
            newVal += val[i];
        }
        $(this).val(newVal.substring(0, 19));
    });
});
</script>

</body>
</html>
