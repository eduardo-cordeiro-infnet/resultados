<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8" />
    <title>Sistema de Resultados do Modelo de Bloco - Instituto Infnet</title>
    <link rel="icon" href="<?php echo site_url('../assets/img/favicon.ico') ?>" type="image/ico"/>
    <link rel="stylesheet" href="<?php echo site_url('../assets/css/bootstrap/bootstrap.css')?>" type="text/css"/>

    <?php foreach($css_files as $file): ?>
        <link rel="stylesheet" href="<?php echo $file; ?>" type="text/css"/>
    <?php endforeach; ?>

    <link rel="stylesheet" href="<?php echo site_url('../assets/css/estilo.css')?>" type="text/css"/>

    <?php foreach($js_files as $file): ?>
        <script src="<?php echo $file; ?>"></script>
    <?php endforeach; ?>
</head>
<body>
