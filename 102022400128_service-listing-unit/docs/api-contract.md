# API Contract - Listing Unit Service

Service owner NIM: `102022400128`

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

Generate docs:

```bash
php artisan l5-swagger:generate
```

## GraphQL Playground

Playground page:

```text
http://localhost:8001/graphql-playground
```

Endpoint:

```text
POST http://localhost:8001/graphql
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
