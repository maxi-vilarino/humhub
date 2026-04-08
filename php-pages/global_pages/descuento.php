<?php

/**
 * Custom Page - Tarjeta de Empleado con Wallets operativos
 */

use yii\helpers\Url;
use humhub\modules\qrcode\QrGenerator;
use humhub\modules\qrcode\assets\QrcodeAsset;

$userAgent = Yii::$app->request->userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');
$esMovil = (bool) preg_match(
    '/(android|iphone|ipod|ipad|blackberry|iemobile|opera mini|mobile|tablet|webos|windows phone)/i',
    $userAgent
);

if (!$esMovil) { ?>
    <div class="alert alert-warning" style="margin: 2rem auto; max-width: 400px; text-align: center; padding: 2rem; border-radius: 12px;">
        <i class="fa fa-mobile fa-3x" style="display:block; margin-bottom: 1rem; color: #D42E18;"></i>
        <strong style="font-size: 1.1rem;">Solo disponible en dispositivos m&oacute;viles</strong>
        <p style="margin-top: 0.75rem; color: #555;">
            Esta p&aacute;gina est&aacute; dise&ntilde;ada para usarse desde tu smartphone.<br>
            Por favor, accede desde tu dispositivo m&oacute;vil.
        </p>
    </div>
<?php
    return;
}

require_once Yii::getAlias('@qrcode') . '/vendor/autoload.php';

$user     = Yii::$app->user->identity;
$fax      = $user->profile->fax ?? '';
$ean      = QrGenerator::completarEAN13($fax);
$bundle   = QrcodeAsset::register($this);
$fontsUrl = $bundle->baseUrl . '/fonts';
$logoUrl  = $bundle->baseUrl . '/img/vegalsa-eroski-logo.png';
$logoGoogleWallet = $bundle->baseUrl . '/img/esES_add_to_google_wallet_wallet-button.svg';
$logoAppleWallet  = $bundle->baseUrl . '/img/ES_Add_to_Apple_Wallet_RGB_101921.svg';
$qr_svg   = '';
$error    = '';

// --- LÓGICA DE WALLETS ---
$googleUrl = '';
$appleUrl  = '';

try {
    $qr_svg = QrGenerator::generate($ean);

    $googleUrl = \yii\helpers\Url::to(['/wallet/wallet/google'], true);
    $appleUrl  = \yii\helpers\Url::to(['/wallet/wallet/apple', 'ean' => $ean], true);
} catch (RuntimeException $e) {
    $error = 'Error al generar el código QR: ' . $e->getMessage();
} catch (\Throwable $e) {
    $error = 'Error inesperado: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarjeta de empleado</title>
    <style>
        /* Mantengo tus estilos originales... */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --red: #D42E18;
            --red-dark: #B02010;
            --red-deep: #8A1608;
        }

        @font-face {
            font-family: 'Aptos';
            src: url('<?= $fontsUrl ?>/Aptos.ttf') format('truetype');
            font-weight: 400;
        }

        @font-face {
            font-family: 'Aptos';
            src: url('<?= $fontsUrl ?>/Aptos-Bold.ttf') format('truetype');
            font-weight: 800;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef1f0;
            font-family: 'Aptos', sans-serif;
            padding: 2rem;
        }

        .card {
            position: relative;
            background: linear-gradient(175deg, var(--red) 0%, var(--red-dark) 55%, var(--red-deep) 100%);
            border-radius: 28px;
            padding: 1.75rem 1.5rem 2rem;
            width: 100%;
            max-width: 300px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.25rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35), 0 20px 60px rgba(212, 46, 24, 0.45), 0 4px 16px rgba(0, 0, 0, 0.2);
            animation: appear 0.5s cubic-bezier(.22, 1, .36, 1) both;
            overflow: hidden;
        }

        .card::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.07) 1px, transparent 1px);
            background-size: 18px 18px;
            z-index: 0;
        }

         /* Reflejo de luz diagonal */
        .card::after {
            content: "";
            position: absolute;
            top: -60%;
            left: -40%;
            width: 80%;
            height: 160%;
            background: linear-gradient(105deg,
                    transparent 40%,
                    rgba(255, 255, 255, 0.08) 50%,
                    transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        @keyframes appear {
            from {
                opacity: 0;
                transform: translateY(22px) scale(0.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Destello de luz al cargar */
        .card-shine {
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(105deg,
                    transparent 20%,
                    rgba(255, 255, 255, 0.18) 50%,
                    transparent 80%);
            z-index: 2;
            pointer-events: none;
            animation: shine 1.1s cubic-bezier(.4, 0, .2, 1) 0.4s both;
        }

        @keyframes shine {
            from {
                left: -100%;
            }

            to {
                left: 160%;
            }
        }


        .card-header {
            position: relative;
            z-index: 1;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.25);
        }

         .card-logo {
            display: flex;
            align-items: center;
        }

        .card-logo img {
            filter: drop-shadow(0 0 1px rgba(255, 255, 255, 0.4))
            drop-shadow(0 0 6px rgba(255, 255, 255, 0.6));
        }    

        .qr-panel {
            position: relative;
            z-index: 1;
            background: #fff;
            border-radius: 16px;
            padding: 1rem;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-panel svg {
            display: block;
            width: 100%;
            height: auto;
        }

        .card-footer {
            position: relative;
            z-index: 1;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
        }

        .card-name {
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
            text-transform: uppercase;
            text-align: center;
        }

        .ean-number {
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.95rem;
            letter-spacing: 0.12em;
            text-align: center;
        }

        .error {
            position: relative;
            z-index: 1;
            color: #fff;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            font-size: 0.85rem;
            text-align: center;
            padding: 0.75rem 1rem;
            width: 100%;
        }

        /* Estilos para botones de Wallet */
        .card-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 300px;
            gap: 1rem;
        }

        .wallet-buttons {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            margin-top: 0.5rem;
        }

        .wallet-button {
            display: flex;
            justify-content: center;
            padding: 4px 0;
            transition: transform 0.2s;
        }

        .wallet-button:active {
            transform: scale(0.96);
        }

        .wallet-button img {
            height: 48px;
            width: auto;
            border: 0;
            display: block;
        }
    </style>
</head>

<body>
    <div class="card-wrapper">
        <div class="card">
            <div class="card-header">
                <div class="card-logo">
                    <img src="<?= $logoUrl ?>" width="140" alt="Vegalsa Eroski">
                </div>
            </div>

            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php else: ?>
                <div class="qr-panel">
                    <?= $qr_svg ?>
                </div>
                <div class="card-footer">
                    <span class="card-name"><?= htmlspecialchars($user->displayName) ?></span>
                    <p class="ean-number"><?= htmlspecialchars($ean) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$error): ?>
            <div class="wallet-buttons">
                <?php
                $ua = strtolower($userAgent);

                // Definimos las plataformas
                $isApple = (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false || strpos($ua, 'ipod') !== false || strpos($ua, 'macintosh') !== false);
                $isAndroid = (strpos($ua, 'android') !== false);
                ?>

                <?php if ($isApple): ?>
                    <a href="<?= $appleUrl ?>" class="wallet-button">
                        <img src="<?= $logoAppleWallet ?>" alt="Añadir a Cartera de Apple">
                    </a>
                <?php elseif ($isAndroid): ?>
                    <a href="<?= $googleUrl ?>" target="_blank" class="wallet-button">
                        <img src="<?= $logoGoogleWallet ?>" alt="Añadir a Google Wallet">
                    </a>
                <?php else: ?>
                    <a href="<?= $appleUrl ?>" class="wallet-button" style="margin-bottom: 8px;">
                        <img src="<?= $logoAppleWallet ?>" alt="Añadir a Cartera de Apple">
                    </a>
                    <a href="<?= $googleUrl ?>" target="_blank" class="wallet-button">
                        <img src="<?= $logoGoogleWallet ?>" alt="Añadir a Google Wallet">
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>