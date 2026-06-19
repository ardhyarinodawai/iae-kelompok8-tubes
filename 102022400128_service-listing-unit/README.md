# 102022400128_Rafsan-Service-Listing-Unit

IAE Assignment 2 service-based project for **Pengajuan Keluhan Tiket Tenant**.

- Owner: Rafsan
- NIM/API Key: `102022400128`
- Service: `Listing-Unit-Service`
- Resource: `listings`
- Framework: Laravel
- Database: MySQL
- Swagger package: L5 Swagger

## Run With Docker

```bash
docker compose up --build
```

App:

```text
http://localhost:8001
```

MySQL host port:

```text
3307
```

## API Key

All REST and GraphQL API requests require:

```http
X-IAE-KEY: 102022400128
```

## REST Endpoints

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/api/v1/listings` | Get all listing units |
| GET | `/api/v1/listings/{id}` | Get one listing unit by id |
| POST | `/api/v1/listings` | Create a new listing unit |

## L5 Swagger

L5 Swagger UI:

```text
http://localhost:8001/api/documentation
```

Generate Swagger docs manually:

```bash
php artisan l5-swagger:generate
```

The L5 Swagger annotations are in:

```text
app/OpenApi/OpenApiSpec.php
app/Http/Controllers/ListingController.php
```

Use the Swagger UI **Authorize** button and enter:

```text
102022400128
```

Then test these three endpoints:

```text
GET /api/v1/listings
GET /api/v1/listings/{id}
POST /api/v1/listings
```

## GraphQL Playground

GraphQL Playground:

```text
http://localhost:8001/graphql-playground
```

GraphQL endpoint:

```text
POST http://localhost:8001/graphql
```

Default API key header is already configured in the Playground page:

```json
{
  "X-IAE-KEY": "102022400128"
}
```

Example query:

```graphql
query {
  unit(id: 1) {
    id
    unit_code
    unit_name
    tower
    floor
    room_number
    unit_type
    status
    tenant_name
    tenant_phone
  }
}
```

## Example POST Body

```json
{
  "unit_code": "APT-C-0910",
  "unit_name": "Apartemen Tower C 910",
  "tower": "C",
  "floor": 9,
  "room_number": "910",
  "unit_type": "apartment",
  "status": "occupied",
  "tenant_name": "Rina Wijaya",
  "tenant_phone": "081298765432"
}
```

## Standard Success Response

```json
{
  "status": "success",
  "message": "Operation successful",
  "data": {},
  "meta": {
    "service_name": "Listing-Unit-Service",
    "api_version": "v1"
  }
}
```

## Standard Error Response

```json
{
  "status": "error",
  "message": "Detail pesan kesalahan...",
  "errors": null
}
```

## Local Development

```bash
cp .env.example .env
composer install
php artisan l5-swagger:generate
php artisan migrate --seed
php artisan serve --host=0.0.0.0 --port=8001
```

## Tests

```bash
php artisan test
```

The tests cover:

- The three REST endpoints
- API key protection
- `404`, `422`, and `401` responses
- L5 Swagger UI availability
- Static OpenAPI JSON availability
- GraphQL query availability
- GraphQL Playground availability
