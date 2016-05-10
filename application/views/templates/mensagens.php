  <div id='list-report-info' class='report-div error' <?php if (!empty($mensagem_erro)) {?>style="display:block"}<?php } ?>>
    <?php if (!empty($mensagem_erro)): ?>
    <p><?php echo $mensagem_erro . ((strpos($mensagem_erro, '</p>') === false ? '</p>' : '')); ?>
    <?php endif; ?>
  </div>
  <div id='list-report-info' class='report-div alerta' <?php if (!empty($mensagem_alerta)) {?>style="display:block"}<?php } ?>>
    <?php if (!empty($mensagem_alerta)): ?>
    <p><?php echo $mensagem_alerta . ((strpos($mensagem_alerta, '</p>') === false ? '</p>' : '')); ?>
    <?php endif; ?>
  </div>
  <div id='list-report-info' class='report-div info' <?php if (!empty($mensagem_informativa)) {?>style="display:block"}<?php } ?>>
    <?php if (!empty($mensagem_informativa)): ?>
    <p><?php echo $mensagem_informativa . ((strpos($mensagem_informativa, '</p>') === false ? '</p>' : '')); ?>
    <?php endif; ?>
  </div>
