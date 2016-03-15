<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />

    <title>Sistema de resultados do modelo de bloco - Instituto Infnet</title>

    <link rel="icon" href="<?php echo site_url('../assets/img/favicon.ico') ?>" type="image/ico"/>

<?php
foreach($css_files as $file): ?>
    <link rel="stylesheet" href="<?php echo $file; ?>" type="text/css"/>
<?php endforeach; ?>

    <link rel="stylesheet" href="<?php echo site_url('../assets/css/estilo.css')?>" type="text/css"/>


<?php foreach($js_files as $file): ?>
    <script src="<?php echo $file; ?>"></script>
<?php endforeach; ?>
</head>