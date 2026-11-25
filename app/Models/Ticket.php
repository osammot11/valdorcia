<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'certificate_reminded_at' => 'datetime',
    ];

    // Un biglietto appartiene a un ordine
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /** Il certificato medico non è ancora stato caricato (si può fare dopo, dal portale del biglietto) */
    public function missingCertificate(): bool
    {
        return blank($this->medical_certificate_path);
    }

    /** Pagina in cui l'iscritto carica o sostituisce il certificato medico */
    public function certificateUrl(): string
    {
        return route('certificate.show', [$this->order->group_code, $this->unique_ticket_code]);
    }
}