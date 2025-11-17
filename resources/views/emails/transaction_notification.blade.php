<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification de transaction - Orange</title>
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
        .transaction-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #FF6600;
            margin: 20px 0;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .debit { color: #dc3545; }
        .credit { color: #28a745; }
        .details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
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
        <h1>🏦 Orange Banque</h1>
        <p>Notification de transaction</p>
    </div>

    <div class="content">
        <h2>Bonjour {{ $client->nom }} {{ $client->prenom }},</h2>

        <p>Une nouvelle transaction a été effectuée sur votre compte bancaire Orange.</p>

        <div class="transaction-card">
            <h3>📄 Détails de la transaction</h3>

            <div class="amount @if($isDebit) debit @else credit @endif">
                {{ $isDebit ? '-' : '+' }}{{ number_format($transaction->montant, 2, ',', ' ') }}€
            </div>

            <div class="details">
                <p><strong>Type :</strong> {{ $typeLabel }}</p>
                <p><strong>Numéro de compte :</strong> {{ $compte->numero_compte }}</p>
                <p><strong>Type de compte :</strong> {{ ucfirst($compte->type) }}</p>
                <p><strong>Date :</strong> {{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                @if($transaction->description)
                <p><strong>Description :</strong> {{ $transaction->description }}</p>
                @endif
                @if($transaction->code_marchand)
                <p><strong>Code marchand :</strong> {{ $transaction->code_marchand }}</p>
                @endif
                @if($transaction->numero_destinataire)
                <p><strong>Destinataire :</strong> {{ $transaction->numero_destinataire }}</p>
                @endif
            </div>
        </div>

        <div class="warning">
            <strong>⚠️ Sécurité :</strong><br>
            Si vous n'avez pas effectué cette transaction, contactez immédiatement notre service client.
        </div>

        <p>Vous pouvez consulter l'historique complet de vos transactions dans votre espace client Orange.</p>

        <p>Cordialement,<br>
        <strong>L'équipe Orange Banque</strong></p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p>&copy; 2025 Orange. Tous droits réservés.</p>
    </div>
</body>
</html>