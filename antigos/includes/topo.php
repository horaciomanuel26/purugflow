<?php $u = utilizador(); ?>
<header class="topo">
    <?php if(!empty($com_burger)):?>
        <button class="burger" id="burger" aria-label="Menu">
            ☰
        </button>
        <?php endif;?>
        <a class="logo" href="index.php">
            purugFlow
        </a>
        <span class="grow">

        </span>
        <span class="quem">
            <?= e($u['nome']) ?>
        </span>
        <a class="btn ghost" href="logout.php">
            Sair
        </a>
    </header>
