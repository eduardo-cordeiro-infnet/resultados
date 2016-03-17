<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="pt-br"> <!--<![endif]-->
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,500,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <title>
    <?php echo isset($title) ? $title . ' - ' : '' ?>
        Sistema de Resultados do Modelo de Bloco - Instituto Infnet
    </title>
    <?php echo link_tag('assets/img/favicon.ico', 'shortcut icon', 'image/ico'); ?>

    <?php
    foreach($css_files as $file): ?>
        <link rel="stylesheet" href="<?php echo $file; ?>" type="text/css"/>
    <?php endforeach; ?>

    <?php
    //echo link_tag('css/mystyles.css');
    echo link_tag('assets/css/vendor/bootstrap.css');
    echo link_tag('assets/css/vendor/animate.css');
    echo link_tag('assets/css/estilo.css');
    ?>

    <?php foreach($js_files as $file): ?>
        <script src="<?php echo $file; ?>"></script>
    <?php endforeach; ?>
    <script src="<?php echo base_url('assets/js/vendor/modernizr-2.8.3-respond-1.4.2.min.js')?>"></script>
</head>
<body>
