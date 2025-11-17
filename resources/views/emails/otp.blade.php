<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre code de connexion - Orange</title>
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
        .otp-code {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #FF6600;
            margin: 20px 0;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 Code de connexion</h1>
        <p>Orange - Accès sécurisé à votre compte</p>
    </div>

    <div class="content">
        <h2>Bonjour {{ $client->nom }} {{ $client->prenom }},</h2>

        <p>Pour accéder à votre compte bancaire Orange, utilisez le code de vérification ci-dessous :</p>

        <div class="otp-code">
            {{ $otp }}
        </div>

        <div class="warning">
            <strong>⚠️ Sécurité importante :</strong><br>
            Ce code expire dans 10 minutes. Ne partagez jamais ce code avec qui que ce soit.
        </div>

        <p><strong>Vos informations :</strong></p>
        <ul>
            <li>Nom complet : {{ $client->nom }} {{ $client->prenom }}</li>
            <li>Téléphone : {{ $client->telephone }}</li>
            <li>Email : {{ $client->email }}</li>
        </ul>

        <p>Si vous n'avez pas demandé ce code, ignorez cet email.</p>

        <p>Cordialement,<br>
        <strong>L'équipe Orange</strong></p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p>&copy; 2025 Orange. Tous droits réservés.</p>
    </div>
</body>
</html>