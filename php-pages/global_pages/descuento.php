<?php

/**
 * Custom page — Tarjeta de fidelización Vegalsa Eroski.
 *
 * La generación del SVG se delega al módulo vegalsa-barcode,
 * que debe estar instalado y activado en HumHub.
 *
 * En HumHub el autoload de Yii2/Composer ya está disponible,
 * por lo que no hace falta ningún require_once adicional.
 */


require_once Yii::getAlias('@qrcode') . '/vendor/autoload.php';

use humhub\modules\qrcode\QrGenerator;
use humhub\modules\qrcode\assets\QrcodeAsset;

$user     = Yii::$app->user->identity;
$fax      = $user->profile->fax ?? '';
$ean      = QrGenerator::completarEAN13($fax);
$bundle = QrcodeAsset::register($this);
$fontsUrl = $bundle->baseUrl . '/fonts';
$logoUrl = $bundle->baseUrl . '/img/vegalsa-eroski-logo.png';
$qr_svg   = '';
$error    = '';

try {
    $qr_svg = QrGenerator::generate($ean);
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
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --red: #D42E18;
            /* Pantone 485 C — base */
            --red-dark: #B02010;
            /* tono oscuro medio  */
            --red-deep: #8A1608;
            /* tono más profundo  */
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
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.35),
                0 20px 60px rgba(46, 158, 107, 0.45),
                0 4px 16px rgba(0, 0, 0, 0.2);
            animation: appear 0.5s cubic-bezier(.22, 1, .36, 1) both;
            overflow: hidden;
        }

        /* Patrón hexagonal decorativo */
        .card::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.07) 1px, transparent 1px);
            background-size: 18px 18px;
            pointer-events: none;
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

        /* Panel QR */
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
            box-shadow:
                inset 0 2px 6px rgba(0, 0, 0, 0.06),
                0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .qr-panel svg {
            display: block;
            width: 100%;
            height: auto;
        }

        /* Footer: nombre + EAN */
        .card-footer {
            position: relative;
            z-index: 1;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            padding-top: 0.1rem;
        }

        .card-name {
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .ean-number {
            color: rgba(255, 255, 255, 0.88);
            font-family: 'Aptos';
            font-weight: 400;
            font-size: 0.95rem;
            letter-spacing: 0.12em;
            text-align: center;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            white-space: nowrap;
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

        .card-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 300px;
            gap: 0.75rem;
        }

        .wallet-buttons {
            width: 100%;
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }


        .wallet-button {
            display: flex;
            align-items: center;
            padding-top: 1rem;
            flex: 1;
            justify-content: center;
        }

        .wallet-button img {
            height: 48px;
            width: 100%;
            object-fit: contain;
            border-radius: 8px;
        }

        @media (max-width: 340px) {
            .card {
                padding: 1.25rem 1rem 1.5rem;
                border-radius: 20px;
                max-width: 100%;
            }

            .ean-number {
                font-size: 0.82rem;
                letter-spacing: 0.08em;
            }
        }
    </style>
</head>

<body>
    <div class="card-wrapper">
        <div class="card">
            <div class="card-shine"></div>
            <div class="card-header">
                <div class="card-logo">
                    <img src="<?= $logoUrl ?>" width="160" height="54" alt="Vegalsa Eroski">
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
        <!-- 
        <div class="wallet-buttons">
            <a href="" target="_blank" class="wallet-button">
                <img src="https://upload.wikimedia.org/wikipedia/commons/3/30/Add_to_Apple_Wallet_badge.svg"
                    alt="Añadir a Apple Wallet"
                    style="height: 48px;">
            </a>
            <a href="" target="_blank" class="wallet-button">
                <img src="https://upload.wikimedia.org/wikipedia/commons/b/bb/Add_to_Google_Wallet_badge.svg"
                    alt="Añadir a Google Wallet"
                    style="height: 48px;">
            </a>
        </div>
        -->
    </div>
</body>
</html>