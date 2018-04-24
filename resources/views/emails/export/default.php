<p><?= $content ?></p>
<p></p>

<p>Top<?= $previewCount ?>数据:</p>
<?php foreach ($sqls as $key => $sqlCfg): ?>
    <p><?= $key ?>:仅展示前<?= $previewCount ?>条,共<?= count($sqlCfg['data']) ?>条</p>

    <?= \App\ETL\Util::format2DArrayToHtml(array_splice($sqlCfg['data'], 0, $previewCount)) ?>
<?php endforeach; ?>

<p>备注导表SQL:</p>
<?php foreach ($sqls as $key => $sqlCfg): ?>
    <p><?= $key ?></p>
    <p>database : <?= $sqlCfg['database'] ?></p>
    <p>sql: <?= $sqlCfg['sql'] ?></p>
<?php endforeach; ?>

