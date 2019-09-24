<?= $layout->head() ?>
<h1>Шаблон</h1>
<?php
    include_once '../views/'.$view_name;
?>
<?= $layout->endPage() ?>
