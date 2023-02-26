<?php
if(empty($data)){ exit; } // check data

$checkSign = $data['checkSign'];
echo '<p class="checkPaymentMessage checkPaymentSuccess"><span>'.$L->sL('Payment success').'.</span></p>';
?>
    <ul class="paymentData">
        <li><span><b><?= $L->sL('Status') ?>:</b></span> <?= $L->sL('Payment success') ?></li>
        <li><span><b><?= $L->sL('Order id') ?>:</b></span> <?= $checkSign['checkSign']['orderId'] ?></li>
        <li><span><b><?= $L->sL('Chain name') ?>:</b></span> id: <?= $checkSign['checkSign']['chainId'] ?>; <?= $checkSign['PaymentSettingsByChainId']['chainData']['chainName'] ?></li>
        <li><span><b><?= $L->sL('Transaction Hash') ?>:</b></span> <a target="_blank" href="<?= $checkSign['PaymentSettingsByChainId']['chainData']['BlockExplorerURL'] ?>/tx/<?= $checkSign['checkSign']['tx'] ?>"><?= $checkSign['checkSign']['tx'] ?></a></li>
        <li><span><b><?= $L->sL('Sender address') ?>:</b></span> <?= $checkSign['checkSign']['addressSender'] ?></li>
        <li><span><b><?= $L->sL('Sender') ?>:</b></span> <?= $L->sL('Transferred') ?> <b><?= $checkSign['checkSign']['InputData']['amountSenderToken']['extData'] ?> <?= $checkSign['checkSign']['InputData']['addressSenderToken']['extData']['nameCoin'] ?></b> <?= $L->sL('and received') ?> <a target="_blank" href="<?= $checkSign['CashbackData']['CashbackLink'] ?>"><b><?= $checkSign['CashbackData']['percent'] ?>% <?= $L->sL('cashback in') ?> <?= $checkSign['CashbackData']['token']['nameCoin'] ?></b></a></li>
        <li><span><b><?= $L->sL('Recipient address') ?>:</b></span> <?= $checkSign['checkSign']['InputData']['addressRecipient']['data'] ?></li>
        <li><span><b><?= $L->sL('Recipient') ?>:</b></span> <?= $L->sL('Received') ?> <b><?= $checkSign['checkSign']['InputData']['amountRecipientToken']['extData'] ?> <?= $checkSign['checkSign']['InputData']['addressRecipientToken']['extData']['nameCoin'] ?></b> <?= $L->sL('minus') ?> <?= $checkSign['CashbackData']['percent'] ?>% <?= $L->sL('for cashback') ?></li>
    </ul>
<div class="htmlSuccess">
    <?= (!empty($data['htmlSuccess']))?$data['htmlSuccess']:'<a class="checkPaymentBtn" href="/">'.$L->sL('Home page').'</a>' ?>
</div>