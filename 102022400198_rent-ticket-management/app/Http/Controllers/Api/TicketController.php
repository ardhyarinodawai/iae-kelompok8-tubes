<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\RabbitMQService;
use App\Services\SoapAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
 
class TicketController extends Controller
{
    public function __construct(
        private SoapAuditService $soapService,
        private RabbitMQService $rabbitService
    ) {}
 
    // GET /api/v1/tickets
    public function index()
    {
        $tickets = Ticket::all();
        return response()->json([
            'success' => true,
            'data'    => $tickets,
        ]);
    }
 
    // GET /api/v1/tickets/{id}
    public function show($id)
    {
        $ticket = Ticket::findOrFail($id);
        return response()->json([
            'success' => true,
            'data'    => $ticket,
        ]);
    }
 
    // POST /api/v1/tickets
    public function store(Request $request)
    {
        // 1. UBAH VALIDASI: Ganti contract_id menjadi tenant_id
        $request->validate([
            'listing_id'   => 'required|int',
            'tenant_id'    => 'required|int',
            'tenant_name'  => 'required|string',
            'tenant_email' => 'required|email',
            'description'  => 'required|string',
        ]);
 
        // =============================================
        // STEP 1: Cross-check Service Listing
        // =============================================
        $listingResponse = Http::get(env('LISTING_SERVICE_URL') . "/api/v1/listing-service/listings/{$request->listing_id}");

        if (! $listingResponse->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Listing tidak valid atau tidak ditemukan'
            ], 404);
        }
        $listing = $listingResponse->json();

        // =============================================
        // STEP 2: Cari Contract ID berdasarkan tenant dan listing
        // =============================================
        $contractResponse = Http::withHeaders([
            'X-API-KEY' => '102022400056' 
        ])->get(env('CONTRACT_SERVICE_URL') . "/api/v1/contract-service/contracts");
 
        if (! $contractResponse->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dari Contract Service'
            ], 500);
        }

        $contracts = $contractResponse->json()['data'] ?? [];

        // Lakukan pencarian (filtering) untuk menemukan ID kontrak yang cocok
        $activeContract = collect($contracts)->first(function ($c) use ($request) {
            return $c['listing_id'] == $request->listing_id && $c['tenant_id'] == $request->tenant_id;
        });

        // Jika tidak ada kontrak yang mengikat Unit dan Tenant ini, tolak request!
        if (!$activeContract) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ditemukan kontrak sewa untuk Unit dan Tenant ini.'
            ], 404);
        }

        // =============================================
        // STEP 3: Simpan tiket ke database
        // =============================================
        $ticket = Ticket::create([
            'listing_id'   => $request->listing_id,
            'contract_id'  => $activeContract['id'],
            'tenant_name'  => $request->tenant_name,
            'tenant_email' => $request->tenant_email,
            'description'  => $request->description,
        ]);

        // =============================================
        // STEP 4: Kirim SOAP Audit pakai M2M token
        // =============================================
        $receiptNumber = $this->soapService->sendAudit($ticket->toArray());
 
        if ($receiptNumber) {
            $ticket->update(['soap_receipt' => $receiptNumber]);
            $ticket->refresh();
        }
 
        // =============================================
        // STEP 5: Publish event ke RabbitMQ pakai M2M token
        // =============================================
        $this->rabbitService->publishTicketCreated($ticket->toArray());
 
        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil dibuat',
            'data'    => [
                'ticket'   => $ticket,
                'listing'  => $listing,
                'contract' => $activeContract,
            ],
        ], 201);
    }
}
 