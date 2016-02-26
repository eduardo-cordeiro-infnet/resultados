<h2><?php echo $title; ?></h2>

<?php echo validation_errors(); ?>

<?php echo form_open('escola/cadastrar'); ?>

    <label for="nome">Nome</label>
    <input type="input" name="nome" /><br />

    <label for="text">Sigla</label>
    <input type="input" name="sigla" /><br />

    <input type="submit" name="submit" value="Cadastrar escola" />

</form>