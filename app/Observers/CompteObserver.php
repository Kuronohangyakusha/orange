<?php

namespace App\Observers;

use App\Models\Compte;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class CompteObserver
{
    /**
     * Handle the Compte "creating" event.
     */
    public function creating(Compte $compte): void
    {
        // Générer automatiquement le numéro de compte si non défini
        if (empty($compte->numero_compte)) {
            $compte->numero_compte = $this->genererNumeroCompte();
        }

        // Générer automatiquement le code de paiement si non défini
        if (empty($compte->code_paiement)) {
            $compte->code_paiement = $this->genererCodePaiement();
        }

        // Générer le QR code
        $compte->qr_code = $this->genererQrCode($compte);

        // Initialiser le solde si non défini
        if (is_null($compte->solde)) {
            $compte->solde = 0;
        }
    }

    /**
     * Handle the Compte "updating" event.
     */
    public function updating(Compte $compte): void
    {
        // Régénérer le QR code si les champs critiques ont changé
        if ($compte->isDirty(['numero_compte', 'code_paiement'])) {
            $compte->qr_code = $this->genererQrCode($compte);
        }
    }

    /**
     * Générer un numéro de compte unique
     */
    private function genererNumeroCompte(): string
    {
        do {
            $numero = 'FR'.rand(1000000000, 9999999999);
        } while (Compte::where('numero_compte', $numero)->exists());

        return $numero;
    }

    /**
     * Générer un code de paiement unique
     */
    private function genererCodePaiement(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (Compte::where('code_paiement', $code)->exists());

        return $code;
    }

    /**
     * Générer le QR code pour le compte
     */
    private function genererQrCode(Compte $compte): string
    {
        // Données à encoder dans le QR code (chiffrées)
        $qrData = encrypt(json_encode([
            'numero_compte' => $compte->numero_compte,
            'code_paiement' => $compte->code_paiement,
        ]));

        // Créer le renderer SVG
        $renderer = new ImageRenderer(
            new RendererStyle(256),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);

        // Générer le QR code en SVG et le convertir en base64
        $svgContent = $writer->writeString($qrData);

        // Retourner le SVG en base64 pour stockage en base de données
        return 'data:image/svg+xml;base64,'.base64_encode($svgContent);
    }
}
