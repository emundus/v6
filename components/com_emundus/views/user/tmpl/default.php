<?php

$email = $this->user;
?>

    <section class="em-activation">

        <section class="info">
            <div class="iconEmail"><i class="fas fa-envelope-open-text"></i></div>
            <div class="email"><h3>Félicitations !</h3></div>
            <div class="instructions"><p>Pour accéder à votre espace, vous devez désormais <span>cliquer sur le lien d’activation qui vient de vous être envoyé par email à <?= $email ?></span></p></div>
        </section>
        <section class="resend">
            <h3>Vous n’avez pas reçu l’email d’activation ?</h3>
            <p>Si vous n’avez pas reçu cet email ou que vous avez saisi une adresse email erronée, nous pouvons vous renvoyer un nouveau lien d’activation. Pour cela, veuillez saisir votre adresse email :</p>
            <input type="text" value="<?= $email ?>" class="mail">
            <button class="btn btn-primary btn-resend">Renvoyer l'email d'activation</button>
        </section>
    </section>

