<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreListingRequest;
use App\Models\Listing;
use App\Services\IaeIntegrationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

// ── Schema: ListingResource (representasi unit listing) ──────────────────────
#[OA\Schema(
    schema: 'ListingResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'unit_code', type: 'string', maxLength: 50, example: 'A-1203'),
        new OA\Property(property: 'unit_name', type: 'string', maxLength: 150, example: 'Unit 1203 Tower A'),
        new OA\Property(property: 'tower', type: 'string', maxLength: 80, example: 'Tower A'),
        new OA\Property(property: 'floor', type: 'integer', minimum: 0, example: 12),
        new OA\Property(property: 'room_number', type: 'string', maxLength: 50, example: '1203'),
        new OA\Property(property: 'unit_type', type: 'string', maxLength: 80, example: 'Studio'),
        new OA\Property(
            property: 'status',
            type: 'string',
            enum: ['available', 'occupied', 'maintenance'],
            example: 'available'
        ),
        new OA\Property(property: 'tenant_name', type: 'string', maxLength: 150, nullable: true, example: 'Budi Santoso'),
        new OA\Property(property: 'tenant_phone', type: 'string', maxLength: 30, nullable: true, example: '081234567890'),
        new OA\Property(property: 'receipt_number', type: 'string', nullable: true, example: 'RCPT-2024-00012'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01T00:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-01T00:00:00Z'),
    ]
)]

// ── Schema: StoreListingRequestBody (body untuk create) ───────────────────────
#[OA\Schema(
    schema: 'StoreListingRequestBody',
    required: ['unit_code', 'unit_name', 'tower', 'floor', 'room_number', 'unit_type', 'status'],
    properties: [
        new OA\Property(property: 'unit_code', type: 'string', maxLength: 50, example: 'A-1203'),
        new OA\Property(property: 'unit_name', type: 'string', maxLength: 150, example: 'Unit 1203 Tower A'),
        new OA\Property(property: 'tower', type: 'string', maxLength: 80, example: 'Tower A'),
        new OA\Property(property: 'floor', type: 'integer', minimum: 0, example: 12),
        new OA\Property(property: 'room_number', type: 'string', maxLength: 50, example: '1203'),
        new OA\Property(property: 'unit_type', type: 'string', maxLength: 80, example: 'Studio'),
        new OA\Property(
            property: 'status',
            type: 'string',
            enum: ['available', 'occupied', 'maintenance'],
            example: 'available'
        ),
        new OA\Property(property: 'tenant_name', type: 'string', maxLength: 150, nullable: true, example: 'Budi Santoso'),
        new OA\Property(property: 'tenant_phone', type: 'string', maxLength: 30, nullable: true, example: '081234567890'),
    ]
)]

// ── Schema: ListingSuccessCollectionResponse ──────────────────────────────────
#[OA\Schema(
    schema: 'ListingSuccessCollectionResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Listing units retrieved successfully'),
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ListingResource')
        ),
    ]
)]

// ── Schema: ListingSuccessSingleResponse ───────────────────────────────────────
#[OA\Schema(
    schema: 'ListingSuccessSingleResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Listing unit retrieved successfully'),
        new OA\Property(property: 'data', ref: '#/components/schemas/ListingResource'),
    ]
)]

// ── Schema: ListingErrorResponse ───────────────────────────────────────────────
#[OA\Schema(
    schema: 'ListingErrorResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Listing unit not found'),
        new OA\Property(property: 'data', nullable: true, example: null),
    ]
)]

#[OA\Tag(name: 'Listings', description: 'API Endpoints for managing listing units')]
class ListingController extends Controller
{
    #[OA\Get(
        path: '/api/v1/listing-service/listings',
        operationId: 'getListings',
        summary: 'Get all listing units',
        security: [['IaeApiKey' => []]],
        tags: ['Listings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listing units retrieved successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ListingSuccessCollectionResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid or missing API key',
                content: new OA\JsonContent(ref: '#/components/schemas/ListingErrorResponse')
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        $listings = Listing::query()
            ->latest('id')
            ->get();

        return ApiResponse::success($listings);
    }

    #[OA\Get(
        path: '/api/v1/listing-service/listings/{id}',
        operationId: 'getListingById',
        summary: 'Get one listing unit by id',
        security: [['IaeApiKey' => []]],
        tags: ['Listings'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID of the listing unit',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listing unit retrieved successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ListingSuccessSingleResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid or missing API key',
                content: new OA\JsonContent(ref: '#/components/schemas/ListingErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Listing unit not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ListingErrorResponse')
            ),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $listing = Listing::query()->find($id);

        if (! $listing) {
            return ApiResponse::error('Listing unit not found', null, 404);
        }

        return ApiResponse::success($listing);
    }

    #[OA\Post(
        path: '/api/v1/listing-service/listings',
        operationId: 'createListing',
        summary: 'Create a new listing unit',
        security: [['IaeApiKey' => []]],
        tags: ['Listings'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreListingRequestBody')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Listing unit created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ListingSuccessSingleResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid or missing API key',
                content: new OA\JsonContent(ref: '#/components/schemas/ListingErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed',
                content: new OA\JsonContent(ref: '#/components/schemas/ListingErrorResponse')
            ),
        ]
    )]
    public function store(StoreListingRequest $request, IaeIntegrationService $integrationService): JsonResponse
    {
        $listing = Listing::query()->create($request->validated());

        // Modul 2: SOAP XML Client
        $receiptNumber = $integrationService->sendAuditLog('CreateListing', $listing->toArray());
        if ($receiptNumber) {
            $listing->update(['receipt_number' => $receiptNumber]);
        }

        // Modul 3: AMQP Publisher
        $integrationService->publishEvent('listing.created', $listing->toArray());

        return ApiResponse::success($listing, 'Listing unit created successfully', 201);
    }
}