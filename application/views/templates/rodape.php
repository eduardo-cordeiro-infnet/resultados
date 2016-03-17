        <div class="footer">
            <div class="container">
              <p class="text-muted">&copy; 2016 <a href="http://infnet.edu.br" target="blank">Instituto Infnet</a></p>
            </div>
        </div>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script>window.jQuery || document.write("<?php echo '<script src=\"' . base_url('assets/js/vendor/jquery-1.11.2.min.js') . '\"><\/script>'?>")</script>

        <?php foreach($js_files as $file): ?>
          <script src="<?php echo $file; ?>"></script>
        <?php endforeach; ?>
        <script src="<?php echo base_url('assets/js/vendor/modernizr-2.8.3-respond-1.4.2.min.js')?>"></script>
        <script src="<?php echo base_url('assets/js/vendor/bootstrap.min.js')?>"></script>
        <script src="<?php echo base_url('assets/js/vendor/wow.min.js')?>"></script>
        <script src="<?php echo base_url('assets/js/main.js')?>"></script>
    </body>
</html>
