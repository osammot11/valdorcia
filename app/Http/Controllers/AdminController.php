<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\PriceTier;
use App\Services\Registration;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Str;
use App\Jobs\SendTicketEmail;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    // --- 1. Dashboard con Filtri di Ricerca ---
    public function dashboard(Request $request)
    {
        $query = \App\Models\Ticket::with('order')->where('status', 'active');

        // Filtro di ricerca testuale (Nome, Cognome, Email, Codice)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('unique_ticket_code', 'like', "%{$search}%");
            });
        }

        // Filtri Temporali
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activeTickets = $query->orderBy('created_at', 'desc')->get();
        $totalRevenue = $activeTickets->sum('price_paid');
        $tiers = PriceTier::orderBy('starts_on')->get();
        $taken = Registration::takenByRoute();
        $remaining = Registration::remainingByRoute();
        $currentTier = Registration::publicTier() ?? Registration::reservedTier();

        return view('admin.dashboard', compact('activeTickets', 'totalRevenue', 'tiers', 'taken', 'remaining', 'currentTier'));
    }

    // --- 2. Esportazione CSV (Il Super CSV Completo, Filtrato e TUTTO MAIUSCOLO) ---
    public function exportCsv(Request $request)
    {
        // Partiamo dalla query base
        $query = \App\Models\Ticket::with('order')->where('status', 'active');

        // 1. APPLICHIAMO GLI STESSI FILTRI DELLA DASHBOARD
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('unique_ticket_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Eseguiamo la query filtrata
        $tickets = $query->orderBy('created_at', 'desc')->get();

        return response()->stream(function () use ($tickets) {
            $handle = fopen('php://output', 'w');
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); 
            
            // Intestazione colonne (già scritte in maiuscolo)
            fputcsv($handle, [
                'CODICE BIGLIETTO', 'GRUPPO ORDINE', 'NOME', 'COGNOME', 'EMAIL', 'TELEFONO', 
                'DATA NASCITA', 'LUOGO NASCITA', 'NAZIONALITA', 'CODICE FISCALE', 
                'INDIRIZZO', 'CITTA', 'CAP', 'PROVINCIA', 'REGIONE', 'PAESE', 
                'PERCORSO', 'TESSERA', 'NUMERO TESSERA', 'TESSERA GIORNALIERA', 'CERTIFICATO MEDICO', 'CELIACO',
                'ASSOCIAZIONE SPORTIVA', 'NOME ASSOCIAZIONE', 'SCAGLIONE',
                'METODO PAGAMENTO', 'IMPORTO PAGATO', 'TAG 1', 'TAG 2', 
                'DATA ISCRIZIONE', 'ORA ISCRIZIONE'
            ], ';');

            foreach ($tickets as $ticket) {
                $isAssoc = ($ticket->order && $ticket->order->is_sports_association) ? 'SI' : 'NO';
                $assocName = $ticket->order->association_name ?? '-';
                $discountLabel = $ticket->order->discount_code ?? '-';

                // Creiamo la riga dati
                $rowData = [
                    $ticket->unique_ticket_code,
                    $ticket->order->group_code ?? '-',
                    $ticket->first_name,
                    $ticket->last_name,
                    $ticket->email,
                    $ticket->phone,
                    $ticket->dob,
                    $ticket->birth_place,
                    $ticket->nationality,
                    $ticket->codice_fiscale,
                    $ticket->residence_address,
                    $ticket->city,
                    $ticket->zip_code,
                    $ticket->province,
                    $ticket->region,
                    $ticket->country,
                    config("event.routes.{$ticket->route_choice}.name", $ticket->route_choice),
                    $ticket->membership ?? '-',
                    $ticket->membership_number ?? '-',
                    $ticket->day_card ? 'SI' : 'NO',
                    $ticket->medical_certificate_path ? 'CARICATO' : 'MANCANTE',
                    $ticket->celiac ? 'SI' : 'NO',
                    $isAssoc,
                    $assocName,
                    $discountLabel,
                    $ticket->payment_tag,
                    number_format($ticket->price_paid, 2, ',', ''),
                    $ticket->tag_1,
                    $ticket->tag_2,
                    $ticket->created_at->format('d/m/Y'), 
                    $ticket->created_at->format('H:i')    
                ];

                // ECCO LA MAGIA: Applichiamo il maiuscolo a tutti gli elementi della riga in un colpo solo
                $uppercaseRow = array_map(function($value) {
                    return mb_strtoupper((string)$value, 'UTF-8');
                }, $rowData);

                // Scriviamo la riga maiuscola nel CSV
                fputcsv($handle, $uppercaseRow, ';');
            }
            fclose($handle);
        }, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=iscritti_filtrati_" . date('Y-m-d_H-i') . ".csv",
        ]);
    }
    
    // --- 3. Modifica Utente ---
    public function editTicket($id)
    {
        $ticket = \App\Models\Ticket::findOrFail($id);
        return view('admin.edit_ticket', compact('ticket'));
    }

    public function updateTicketData(Request $request, $id)
    {
        $ticket = \App\Models\Ticket::findOrFail($id);

        $validated = $request->validate([
            'tag_1' => 'nullable|string|max:255',
            'tag_2' => 'nullable|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'codice_fiscale' => 'nullable|string|max:16',
            'route_choice' => ['required', \Illuminate\Validation\Rule::in(array_keys(config('event.routes')))],
            'membership' => ['required', \Illuminate\Validation\Rule::in(array_merge(config('event.memberships'), ['giornaliera']))],
            'membership_number' => 'nullable|string|max:50',
            'medical_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'residence_address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip_code' => 'required|string|max:20',
        ]);

        $validated['day_card'] = $validated['membership'] === 'giornaliera';
        if ($validated['day_card']) {
            $validated['membership'] = null;
            $validated['membership_number'] = null;
        }

        unset($validated['medical_certificate']);
        if ($request->hasFile('medical_certificate')) {
            $validated['medical_certificate_path'] = $request->file('medical_certificate')
                ->store('certificates/' . ($ticket->order->group_code ?? 'manuali'), 'local');
        }

        $ticket->update($validated);

        return redirect()->route('admin.dashboard')->with('success', 'Dati di ' . $ticket->first_name . ' aggiornati con successo!');
    }

    // Certificato medico caricato dal partecipante (solo admin)
    public function downloadCertificate($id)
    {
        $ticket = \App\Models\Ticket::findOrFail($id);
        abort_unless($ticket->medical_certificate_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($ticket->medical_certificate_path), 404);

        $extension = pathinfo($ticket->medical_certificate_path, PATHINFO_EXTENSION);

        return \Illuminate\Support\Facades\Storage::disk('local')->download(
            $ticket->medical_certificate_path,
            'Certificato_' . \Illuminate\Support\Str::slug($ticket->last_name . ' ' . $ticket->first_name, '_') . '.' . $extension
        );
    }

    // --- 4. Download PDF Admin ---
    public function downloadPdf($id)
    {
        $ticket = \App\Models\Ticket::findOrFail($id);
        // Usiamo la stessa vista del biglietto che usa l'utente
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.ticket', compact('ticket'));
        return $pdf->download('Biglietto_' . $ticket->unique_ticket_code . '.pdf');
    }

    // Salva gli scaglioni di prezzo (righe esistenti, nuove e da eliminare)
    public function updatePrice(Request $request)
    {
        $routes = config('event.routes');

        // Le righe nuove lasciate vuote non vanno validate
        $request->merge(['tiers' => array_values(array_filter(
            $request->input('tiers', []),
            fn ($row) => filled($row['id'] ?? null) || filled($row['name'] ?? null)
        ))]);

        $rules = [
            'tiers' => 'array',
            'tiers.*.id' => 'nullable|integer|exists:price_tiers,id',
            'tiers.*.delete' => 'nullable|boolean',
            'tiers.*.name' => 'exclude_if:tiers.*.delete,1|required|string|max:100',
            'tiers.*.starts_on' => 'exclude_if:tiers.*.delete,1|required|date',
            'tiers.*.ends_on' => 'exclude_if:tiers.*.delete,1|required|date|after_or_equal:tiers.*.starts_on',
            'tiers.*.access_code' => 'exclude_if:tiers.*.delete,1|nullable|string|max:50|alpha_dash',
        ];
        foreach (array_keys($routes) as $route) {
            $rules["tiers.*.prices.$route"] = 'exclude_if:tiers.*.delete,1|nullable|numeric|min:0|max:9999';
        }

        $validated = $request->validate($rules, [
            'tiers.*.ends_on.after_or_equal' => 'In ogni scaglione la data di fine deve venire dopo quella di inizio.',
            'tiers.*.access_code.alpha_dash' => 'Il codice può contenere solo lettere, numeri e trattini.',
        ]);

        foreach ($validated['tiers'] ?? [] as $row) {
            $tier = filled($row['id'] ?? null) ? PriceTier::find($row['id']) : null;

            if (! empty($row['delete'])) {
                $tier?->delete();
                continue;
            }

            $prices = [];
            foreach (array_keys($routes) as $route) {
                $value = $row['prices'][$route] ?? null;
                $prices[$route] = $value === null || $value === '' ? null : round((float) $value, 2);
            }

            ($tier ?? new PriceTier())->fill([
                'name' => $row['name'],
                'starts_on' => $row['starts_on'],
                'ends_on' => $row['ends_on'],
                'access_code' => filled($row['access_code'] ?? null) ? strtoupper($row['access_code']) : null,
                'prices' => $prices,
            ])->save();
        }

        return back()->with('success', 'Scaglioni di prezzo aggiornati.');
    }

    // Mostra la pagina dei biglietti in attesa e il form di upload
    public function pending()
    {
        $pendingTickets = Ticket::with('order')->where('status', 'in-approvazione')->get();
        return view('admin.pending', compact('pendingTickets'));
    }

    // Processa il file CSV (Versione Completa - Tutti i campi)
    public function uploadCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $filePath = $file->getRealPath();

        // 1. Apriamo il file in lettura in modo sicuro
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return back()->with('error', 'Impossibile leggere il file CSV.');
        }

        // 2. Leggiamo la primissima riga per capire se usa la virgola (,) o il punto e virgola (;)
        $firstLine = fgets($handle);
        $delimiter = strpos($firstLine, ';') !== false ? ';' : ',';
        
        // Riportiamo il "cursore" di lettura all'inizio del file
        rewind($handle); 

        // 3. Saltiamo la prima riga (l'intestazione)
        fgetcsv($handle, 1000, $delimiter);

        // 4. Creiamo l'ordine fittizio per raggrupparli
        $order = \App\Models\Order::create([
            'group_code' => 'CSV_' . strtoupper(\Illuminate\Support\Str::random(8)),
            'total_amount' => 0, 
            'payment_method' => 'import_csv',
            'status' => 'paid',
        ]);

        $count = 0;
        // Prezzo dello scaglione pubblico di oggi (0 se le iscrizioni online sono chiuse)
        $tier = Registration::publicTier();

        // 5. Leggiamo riga per riga (Ora con TUTTE le 18 colonne)
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
            
            // Se la riga è completamente vuota, saltala
            if (empty(array_filter($row))) continue;

            // Se mancano delle colonne in fondo, le riempiamo con vuoto per non far schiantare Laravel
            $row = array_pad($row, 18, '');

            // Funzione di utilità per capire se l'utente ha scritto "Si", "True", "1" o "Yes" nelle checkbox
            $isTrue = function($val) {
                return in_array(strtolower(trim($val)), ['1', 'si', 'sì', 'true', 'yes', 'y']);
            };

            // Convertiamo le date dal formato italiano (GG/MM/AAAA) al formato DB (AAAA-MM-GG) se necessario
            $dob = trim($row[4]);
            if (strpos($dob, '/') !== false) {
                $dateParts = explode('/', $dob);
                if(count($dateParts) == 3) {
                    $dob = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                }
            }

            $route = Registration::routeFromLabel($row[3]) ?? array_key_first(config('event.routes'));

            $order->tickets()->create([
                // Dati Anagrafici Base
                'first_name' => trim($row[0]) ?: 'Sconosciuto',
                'last_name' => trim($row[1]) ?: 'Sconosciuto',
                'email' => trim($row[2]) ?: 'no-email@test.com',
                
                // Scelta percorso
                'route_choice' => $route,
                'tshirt_size' => trim($row[9]) ?: null,
                
                // Dati di Nascita e Documenti
                'dob' => $dob ?: '1990-01-01',
                'birth_place' => trim($row[5]) ?: 'N/D',
                'nationality' => strtoupper(trim($row[6])) ?: 'IT',
                'codice_fiscale' => strtoupper(trim($row[7])) ?: null,
                
                // Contatti e Residenza
                'phone' => trim($row[8]) ?: '0000000000',
                'residence_address' => trim($row[10]) ?: 'N/D',
                'city' => trim($row[11]) ?: 'N/D',
                'zip_code' => trim($row[12]) ?: '00000',
                'province' => trim($row[13]) ?: 'N/D',
                'region' => trim($row[14]) ?: 'N/D',
                'country' => trim($row[15]) ?: 'Italia',
                
                // Opzioni Extra
                'shuttle_needed' => $isTrue($row[16]),
                'celiac' => $isTrue($row[17]),
                
                // Dati di Sistema
                'price_paid' => $tier?->priceFor($route) ?? 0,
                'payment_tag' => 'Importato CSV',
                'status' => 'in-approvazione', 
            ]);
            $count++;
        }

        fclose($handle);

        return back()->with('success', "$count biglietti caricati con tutti i dettagli e messi in attesa di approvazione.");
    }

    // Approva il singolo biglietto e invia l'email
    public function approveTicket($id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->status !== 'active') {
            $ticket->update([
                'status' => 'active',
                'unique_ticket_code' => 'TK_' . strtoupper(Str::random(8)) // Genera il codice solo ora!
            ]);

            // SPARA L'EMAIL IN BACKGROUND TRAMITE BREVO!
            SendTicketEmail::dispatch($ticket);
        }

        return back()->with('success', "Biglietto di {$ticket->first_name} approvato! Email in fase di invio.");
    }

    // Elimina un biglietto in attesa se c'è un errore
    public function deleteTicket($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return back()->with('success', 'Biglietto eliminato correttamente.');
    }

    // Mostra il form per creare un biglietto manuale
    public function createTicket()
    {
        return view('admin.create_ticket');
    }

    // Salva il biglietto manuale e spedisce l'email
    public function storeTicket(Request $request)
    {
        // 1. Validazione
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'route_choice' => 'required|in:' . implode(',', array_keys(config('event.routes'))),
            'dob' => 'required|date',
            'birth_place' => 'required|string|max:255',
            'residence_address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip_code' => 'required|string|max:20',
            'province' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'nationality' => 'required|string|max:2',
            'codice_fiscale' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('nationality') === 'IT') {
                        if (!preg_match('/^[A-Z]{6}[0-9LMNPQRSTUV]{2}[ABCDEHLMPRST][0-9LMNPQRSTUV]{2}[A-Z][0-9LMNPQRSTUV]{3}[A-Z]$/i', $value)) {
                            $fail('Il Codice Fiscale inserito non è valido.');
                        }
                    }
                },
            ],
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'membership' => ['required', \Illuminate\Validation\Rule::in(array_merge(config('event.memberships'), ['giornaliera']))],
            'membership_number' => 'exclude_if:membership,giornaliera|nullable|string|max:50',
            'medical_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'celiac' => 'required|boolean',
            'price_paid' => 'required|numeric|min:0',
        ]);

        $validated['day_card'] = $validated['membership'] === 'giornaliera';
        if ($validated['day_card']) {
            $validated['membership'] = null;
            $validated['membership_number'] = null;
        }
        unset($validated['medical_certificate']);

        // 2. Creiamo un ordine fittizio con l'importo corretto
        $order = Order::create([
            'group_code' => 'MAN_' . strtoupper(Str::random(8)),
            'total_amount' => $validated['price_paid'], // Salviamo l'incasso
            'payment_method' => 'manual',
            'status' => 'paid',
        ]);

        // 3. Creiamo il biglietto (Laravel prenderà 'price_paid' direttamente da $validated)
        $ticket = $order->tickets()->create(array_merge($validated, [
            'medical_certificate_path' => $request->file('medical_certificate')?->store('certificates/' . $order->group_code, 'local'),
            'payment_tag' => 'Contanti / Manuale',
            'status' => 'active', 
            'unique_ticket_code' => 'TK_' . strtoupper(Str::random(8)) 
        ]));

        // 4. Invio email in background
        SendTicketEmail::dispatch($ticket);

        return redirect()->route('admin.dashboard')->with('success', "Biglietto creato con successo! Email inviata a {$ticket->email}.");
    }

}
