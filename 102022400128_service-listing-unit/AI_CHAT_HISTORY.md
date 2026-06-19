# AI Prompting and Chat History

This file records the AI-assisted planning and implementation process required for the IAE Assignment 2 repository.

## Project

The user asked for a service-based project for **Pengajuan Keluhan Tiket Tenant**.

Assigned service:

- Service Listing Unit
- Owner NIM: `102022400128`
- Repository name: `102022400128_Rafsan-Service-Listing-Unit`
- Framework: Laravel
- Database: MySQL

## Clarification Result

The service owns property unit data and simple tenant data:

- `unit_code`
- `unit_name`
- `tower`
- `floor`
- `room_number`
- `unit_type`
- `status`
- `tenant_name`
- `tenant_phone`

The PDF assignment used resource name `listings`, so the implementation follows:

- `GET /api/v1/listings`
- `GET /api/v1/listings/{id}`
- `POST /api/v1/listings`

## Additional Request

The user then requested:

- L5 Swagger for testing output from the three REST endpoints.
- GraphQL Playground for testing GraphQL output.

## Implementation

The repository was updated to include:

- `darkaonline/l5-swagger` in `composer.json`
- `config/l5-swagger.php`
- L5 Swagger annotations in `app/OpenApi/OpenApiSpec.php`
- L5 Swagger endpoint documentation annotations in `app/Http/Controllers/ListingController.php`
- GraphQL Playground page at `/graphql-playground`
- GraphQL endpoint at `/graphql`
- Tests for L5 Swagger and GraphQL Playground availability

## API Key

```http
X-IAE-KEY: 102022400128
```
