<?php
    $trace = new \app\widgets\stackTrace($trace);
    echo (new \app\widgets\HTML5Layout((new \app\core\HTMLTag("head"))))->head();
?>
<style>
    body > .header
    {
        height: 200px;
    }

    .header
    {
        width: 100%;
        display: flex;
        background: #e5e5e5;
        flex-direction: column;
    }
    .header h1
    {
        display: flex;
        color: #ff2025;
        padding: 2rem;
    }
    .header h5, h6
    {
        padding-left: 2rem;
        color: black;
    }
    .stack_trace
    {
        margin-top: 40px;
    }
    .stack_item
    {
        margin-bottom: 40px;
        background: #e5e5e5;
        display: flex;
        flex-direction: column;
    }
    .stack_item .top
    {
        display: flex;
        flex-direction: row;
    }
    .stack_item .top h4
    {
        padding: 1rem;
    }
    .stack_item .top .header
    {
        padding: 1rem;
        padding-top: 1.5rem;
    }
    .stack_item .code-block
    {
        font-size: 0.9rem;
        padding: 1rem;
        padding-bottom: 2rem;
    }
</style>
<body>
<div class="header">
    <h1><?=$exp_inf['class_name']?></h1><h5>#<?= $exp_inf['error_code'] ?> <?= $exp_inf['error_message'] ?></h5>
    <div class="error_place">
        <h6>in <i>'<?= $exp_inf['file'] ?>'</i>, on <b>line <?= $exp_inf['line'] ?></b>, in function <i><?= $exp_inf['call_by'] ?></i></h6>
    </div>
</div>
<?= $trace->render(); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="js/bootstrap.js"></script>
<script src="js/bootstrap.bundle.js"></script>
<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
        $('[data-toggle="tooltip"]').css({'cursor': "pointer", 'text-decoration' : "underline"});
    });
</script>
</body>
</html>
