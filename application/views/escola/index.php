<h2><?php echo $title; ?></h2>

<?php foreach ($escolas as $escola_item): ?>

        <h3><?php echo $escola_item['nome']; ?></h3>
        <div class="main">
                <?php echo $escola_item['sigla']; ?>
        </div>
        <p><a href="<?php echo site_url('escola/'.$escola_item['sigla']); ?>">Ver cursos</a></p>

<?php endforeach; ?>