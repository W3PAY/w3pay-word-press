<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= (!empty($data['title']))?$data['title']:'' ?></title>
    <meta name="description" content="<?= (!empty($data['description']))?$data['description']:'' ?>">
</head>
<body>
<?= (!empty($data['content']))?$data['content']:'' ?>
<style>
    .linkResultPay {
        max-width: 1000px;
        background: #f7f7f7;
        padding: 1px 20px 20px 20px;
        margin: 0 auto;
        font-family: 'Roboto', sans-serif !important;
        font-size: 14px;
    }
    .linkResultPay .checkPaymentBtn {
        display: block;
        background: #25a4ed;
        padding: 10px 0px;
        border: none;
        color: #ffffff;
        font-size: 15px;
        margin: 0px 0px 5px 0px;
        cursor: pointer;
        width: 100%;
        text-align: center;
        text-decoration: none;
    }
</style>
</body>
</html>