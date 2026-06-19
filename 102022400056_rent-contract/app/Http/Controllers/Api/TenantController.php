<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

// ── Schema: ContractSummary (nested inside Tenant) ───────────────────────────
#[OA\Schema(
    schema: 'ContractSummary',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'listing_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440002'),
        new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2024-01-01'),
        new OA\Property(property: 'end_date', type: 'string', format: 'date', example: '2024-12-31'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(
            property: 'status',
            type: 'string',
            enum: ['DRAFT', 'ACTIVE', 'EXPIRED', 'TERMINATED'],
            example: 'ACTIVE'
        ),
    ]
)]

// ── Schema: TenantResource ───────────────────────────────────────────────────
#[OA\Schema(
    schema: 'TenantResource',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440001'),
        new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
        new OA\Property(property: 'phone', type: 'string', example: '08123456789'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01T00:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-01T00:00:00Z'),
        new OA\Property(
            property: 'contracts',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ContractSummary')
        ),
    ]
)]

// ── Schema: TenantRequest ────────────────────────────────────────────────────
#[OA\Schema(
    schema: 'TenantRequest',
    required: ['name', 'email'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
        new OA\Property(property: 'phone', type: 'string', example: '08123456789'),
    ]
)]

// ── Schema: TenantSuccessCollectionResponse ──────────────────────────────────
#[OA\Schema(
    schema: 'TenantSuccessCollectionResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Data retrieved successfully'),
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TenantResource')
        ),
        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
    ]
)]

// ── Schema: TenantSuccessSingleResponse ─────────────────────────────────────
#[OA\Schema(
    schema: 'TenantSuccessSingleResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Data retrieved successfully'),
        new OA\Property(property: 'data', ref: '#/components/schemas/TenantResource'),
        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
    ]
)]

#[OA\Tag(name: 'Tenants', description: 'API Endpoints for managing tenants')]
class TenantController extends Controller
{
    #[OA\Get(
        path: '/api/v1/tenants',
        summary: 'Get all tenants',
        description: 'Returns a list of all tenants with their contracts, ordered by newest first.',
        security: [['bearerAuth' => [], 'apiKeyAuth' => []]],
        tags: ['Tenants'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tenants retrieved successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/TenantSuccessCollectionResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(): JsonResponse
    {
        $tenants = Tenant::with('contracts')->orderByDesc('created_at')->get();

        return $this->successResponse(
            TenantResource::collection($tenants),
            'Data retrieved successfully',
            $this->apiMeta()
        );
    }

    #[OA\Post(
        path: '/api/v1/tenants',
        summary: 'Create a new tenant',
        description: 'Stores a newly created tenant and returns it with associated contracts.',
        security: [['bearerAuth' => [], 'apiKeyAuth' => []]],
        tags: ['Tenants'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TenantRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tenant created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/TenantSuccessSingleResponse')
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function store(StoreTenantRequest $request): JsonResponse
    {
        $tenant = Tenant::create($request->validated());

        return $this->successResponse(
            new TenantResource($tenant->load('contracts')),
            'Tenant created successfully',
            $this->apiMeta()
        );
    }

    #[OA\Get(
        path: '/api/v1/tenants/{id}',
        summary: 'Get a specific tenant',
        description: 'Returns a single tenant by UUID with their associated contracts.',
        security: [['bearerAuth' => [], 'apiKeyAuth' => []]],
        tags: ['Tenants'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'UUID of the tenant',
                schema: new OA\Schema(type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440001')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tenant retrieved successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/TenantSuccessSingleResponse')
            ),
            new OA\Response(response: 404, description: 'Tenant not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function show(Tenant $tenant): JsonResponse
    {
        $tenant->load('contracts');

        return $this->successResponse(
            new TenantResource($tenant),
            'Data retrieved successfully',
            $this->apiMeta()
        );
    }

    #[OA\Put(
        path: '/api/v1/tenants/{id}',
        summary: 'Update a tenant',
        description: 'Updates an existing tenant by UUID and returns the updated resource.',
        security: [['bearerAuth' => [], 'apiKeyAuth' => []]],
        tags: ['Tenants'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'UUID of the tenant',
                schema: new OA\Schema(type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440001')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TenantRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tenant updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/TenantSuccessSingleResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Failed to update tenant',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(response: 404, description: 'Tenant not found'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function update(StoreTenantRequest $request, Tenant $tenant): JsonResponse
    {
        if (! $tenant->update($request->validated())) {
            return $this->errorResponse(
                'Unable to update tenant',
                500,
                null,
                $this->apiMeta()
            );
        }

        return $this->successResponse(
            new TenantResource($tenant->load('contracts')),
            'Tenant updated successfully',
            $this->apiMeta()
        );
    }

    #[OA\Delete(
        path: '/api/v1/tenants/{id}',
        summary: 'Delete a tenant',
        description: 'Deletes a tenant by UUID and returns the deleted resource.',
        security: [['bearerAuth' => [], 'apiKeyAuth' => []]],
        tags: ['Tenants'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'UUID of the tenant',
                schema: new OA\Schema(type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440001')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tenant deleted successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/TenantSuccessSingleResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Failed to delete tenant',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(response: 404, description: 'Tenant not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function destroy(Tenant $tenant): JsonResponse
    {
        if (! $tenant->delete()) {
            return $this->errorResponse(
                'Unable to delete tenant',
                500,
                null,
                $this->apiMeta()
            );
        }

        return $this->successResponse(
            new TenantResource($tenant),
            'Tenant deleted successfully',
            $this->apiMeta()
        );
    }

    private function apiMeta(): array
    {
        return [
            'service_name' => 'Rent-Contract-Service',
            'api_version' => 'v1',
        ];
    }
}
