<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue chez Orange</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #FF6600, #FF8533);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .credentials {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #FF6600;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background: #FF6600;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 12px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏠 Bienvenue chez Orange !</h1>
        <p>Votre compte bancaire a été créé avec succès</p>
    </div>

    <div class="content">
        <h2>Bonjour {{ $nom }} {{ $prenom }},</h2>

        <p>Félicitations ! Votre compte bancaire Orange a été créé avec succès. Vous pouvez maintenant profiter de tous nos services bancaires en ligne.</p>

        <div class="credentials">
            <h3>🔐 Informations de votre compte</h3>
            <p><strong>Email :</strong> {{ $email }}</p>
            @if($numero_compte)
            <p><strong>Numéro de compte :</strong> {{ $numero_compte }}</p>
            @endif
            @if($code_paiement)
            <p><strong>Code de paiement :</strong> {{ $code_paiement }}</p>
            @endif
            @if($code_marchand)
            <p><strong>Code marchand :</strong> {{ $code_marchand }}</p>
            @endif
            <p>Pour des raisons de sécurité, un mot de passe temporaire a été généré pour votre compte.</p>
            <p>Cliquez sur le bouton ci-dessous pour définir votre propre mot de passe :</p>
            <a href="{{ $reset_link }}" class="button" style="display: inline-block; background: #FF6600; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0;">Définir mon mot de passe</a>
        </div>

        <div class="credentials">
            <h3>📱 QR Code de votre compte</h3>
            <p>Scannez ce QR code pour accéder rapidement à vos informations de paiement :</p>
            @if($numero_compte)
            <div style="text-align: center; margin: 20px 0;">
                <img src="data:image/svg+xml;base64,{{ base64_encode($qr_code ?? '') }}" alt="QR Code" style="max-width: 200px; height: auto;" />
            </div>
            @endif
        </div>

        <div class="warning">
            <strong>⚠️ Sécurité importante :</strong><br>
            Nous vous recommandons de changer votre mot de passe lors de votre première connexion pour des raisons de sécurité.
        </div>

        <p>Pour vous connecter à votre espace client, cliquez sur le bouton ci-dessous :</p>

        <a href="#" class="button">Se connecter à mon compte</a>

        <h3>🚀 Services disponibles</h3>
        <ul>
            <li>Consultation de solde en temps réel</li>
            <li>Historique des transactions</li>
            <li>Virements entre comptes</li>
            <li>Paiements par code</li>
            <li>Transferts par numéro de téléphone</li>
        </ul>

        <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>

        <p>Cordialement,<br>
        <strong>L'équipe Orange</strong></p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p>&copy; 2025 Orange. Tous droits réservés.</p>
    </div>
</body>
</html>