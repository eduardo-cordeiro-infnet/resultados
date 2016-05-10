<div id="warp">
<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand icon-menu" href="#menu">
        <i class="fa fa-bars"></i>
      </a>
      <a class="navbar-brand" href="<?php echo base_url(); ?>">
        <img alt="Voltar para o início" src="<?php echo base_url('assets/img/infnet_logo_adaptada.svg'); ?>">
      </a>
    </div>
    <ul class="navbar-right">
      <li><?php echo (isset($title) ? $title : ''); ?></li>
      <li><button class="btn btn-primary">Logout</button></li>
    </ul>
  </div>
</nav>
