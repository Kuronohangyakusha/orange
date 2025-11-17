<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'compte_id' => 'required|exists:comptes,id',
            'type' => ['required', Rule::in(Transaction::TYPES)],
            'montant' => 'required|numeric|min:0.01',
            'code_marchand' => 'nullable|string',
            'numero_destinataire' => 'nullable|string',
            'description' => 'nullable|string|max:255',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $compteId = $this->input('compte_id');
            $type = $this->input('type');
            $montant = $this->input('montant');

            if (in_array($type, ['paiement', 'transfert'])) {
                $compte = \App\Models\Compte::find($compteId);
                if ($compte && $compte->solde < $montant) {
                    $validator->errors()->add('montant', 'Solde insuffisant pour cette transaction.');
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'compte_id.required' => 'Le compte est obligatoire.',
            'compte_id.exists' => 'Le compte spécifié n\'existe pas.',
            'type.required' => 'Le type de transaction est obligatoire.',
            'type.in' => 'Le type de transaction est invalide.',
            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant doit être supérieur à 0.',
            'description.max' => 'La description ne peut pas dépasser 255 caractères.',
        ];
    }
}
