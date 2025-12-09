<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;

// Initialisation TOTP
$tfa = new TwoFactorAuth(
    issuer: 'Supportini.TN',
    qrProvider: new QRServerProvider()
);

// Générer une seule fois le secret
if (!isset($_SESSION['2fa_secret'])) {
    $_SESSION['2fa_secret'] = $tfa->createSecret();
}

$secret = $_SESSION['2fa_secret'];
$qr = $tfa->getQRCodeImageAsDataUri("Admin Supportini", $secret);//QR Code pour Google Authenticator



// TRAITEMENT DU FORMULAIRE
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $code = trim($_POST['code']);

    if ($tfa->verifyCode($secret, $code)) {//Vérification du code

        // Validation OK
        $_SESSION['2fa_validated'] = true;

        // On peut supprimer le secret après validation
        unset($_SESSION['2fa_secret']);

        // Redirection vers BackOffice
        header("Location: backoffice/manageCategories.php");
        exit;
    } else {
        $message = "❌ Code incorrect. Veuillez réessayer.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion 2FA - Google Authenticator</title>
<style>
    body{
        background:#f3f3f3;
        display:flex;
        justify-content:center;
        align-items:center;
        height:100vh;
        font-family:Arial;
        margin:0;
    }
    .box{
        background:white;
        padding:25px;
        border-radius:10px;
        width:360px;
        text-align:center;
        box-shadow:0 0 10px rgba(0,0,0,0.1);
    }
    img{
        margin:15px 0;
    }
    input{
        width:100%;
        padding:12px;
        font-size:17px;
        border:1px solid #ddd;
        border-radius:5px;
        margin-bottom:10px;
    }
    button{
        width:100%;
        padding:12px;
        border:none;
        border-radius:5px;
        background:#1976D2;
        color:white;
        font-size:17px;
        cursor:pointer;
    }
    button:hover{
        background:#115293;
    }
    .error{
        color:#d32f2f;
        margin-bottom:10px;
        font-weight:bold;
    }
</style>
</head>

<body>
<div class="box">

    <h2>🔐 Vérification Google Authenticator</h2>

    <p>Scanne ce code avec Google Authenticator :</p>
    <img src="<?= $qr ?>" alt="QR Code">

    <p><b>Clé secrète :</b></p>
    <p><?= $secret ?></p>

    <?php if (!empty($message)): ?>
        <div class="error"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="code" placeholder="Code à 6 chiffres" required>
        <button type="submit">Valider</button>
    </form>

</div>
</body>
</html>
