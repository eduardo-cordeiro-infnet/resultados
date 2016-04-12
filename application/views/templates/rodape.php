    <div id='list-report-info' class='report-div error' <?php if (!empty($mensagem_erro)) {?>style="display:block"}<?php } ?>>
        <?php if (!empty($mensagem_erro)): ?>
        <p><?php echo $mensagem_erro; ?></p>
        <?php endif; ?>
    </div>
    <div id='list-report-info' class='report-div alerta' <?php if (!empty($mensagem_alerta)) {?>style="display:block"}<?php } ?>>
        <?php if (!empty($mensagem_alerta)): ?>
        <p><?php echo $mensagem_alerta; ?></p>
        <?php endif; ?>
    </div>
    <div id='list-report-info' class='report-div info' <?php if (!empty($mensagem_informativa)) {?>style="display:block"}<?php } ?>>
        <?php if (!empty($mensagem_informativa)): ?>
        <p><?php echo $mensagem_informativa; ?></p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <div class="container">
          <p>&copy; 2016 <a href="http://infnet.edu.br" target="blank">Instituto Infnet</a></p>
        </div>
    </div>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script>window.jQuery || document.write("<?php echo '<script src=\"' . base_url('assets/js/vendor/jquery-1.11.2.min.js') . '\"><\/script>'?>")</script>

    <?php if (isset($js_files)): ?>
        <?php foreach($js_files as $file): ?>
    <script src="<?php echo $file; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script src="<?php echo base_url('assets/js/vendor/modernizr-2.8.3-respond-1.4.2.min.js')?>"></script>
    <script src="<?php echo base_url('assets/js/vendor/bootstrap.min.js')?>"></script>
    <script src="<?php echo base_url('assets/js/vendor/jquery.mmenu.all.min.js')?>"></script>
    <script src="<?php echo base_url('assets/js/vendor/wow.min.js')?>"></script>
    <script src="<?php echo base_url('assets/js/main.js')?>"></script>
    </div>
</body>
</html>
