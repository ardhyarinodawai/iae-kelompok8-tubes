<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GraphQlController extends Controller
{
    private const ALLOWED_FIELDS = [
        'id',
        'unit_code',
        'unit_name',
        'tower',
        'floor',
        'room_number',
        'unit_type',
        'status',
        'tenant_name',
        'tenant_phone',
        'created_at',
        'updated_at',
    ];

    public function handle(Request $request): JsonResponse
    {
        $query = (string) $request->input('query', '');
        $variables = $request->input('variables', []);

        if (is_string($variables)) {
            $variables = json_decode($variables, true) ?: [];
        }

        if (! str_contains($query, 'unit')) {
            return ApiResponse::error('Only the unit(id: ID!) GraphQL query is available', null, 422);
        }

        $id = $variables['id'] ?? $this->extractIdFromQuery($query);

        if (! is_numeric($id)) {
            return ApiResponse::error('GraphQL query requires a numeric unit id', null, 422);
        }

        $listing = Listing::query()->find((int) $id);

        if (! $listing) {
            return ApiResponse::error('Listing unit not found', null, 404);
        }

        $fields = $this->extractSelectedFields($query);
        $selectedData = collect($listing->toArray())
            ->only($fields)
            ->all();

        return ApiResponse::success([
            'unit' => $selectedData,
        ]);
    }

    public function graphiql(): Response
    {
        $html = <<<'HTML'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Listing Unit Service GraphiQL</title>
    <link rel="stylesheet" href="https://unpkg.com/graphiql/graphiql.min.css">
</head>
<body style="margin:0;">
    <div id="graphiql" style="height:100vh;"></div>
    <script src="https://unpkg.com/react/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/graphiql/graphiql.min.js"></script>
    <script>
        const fetcher = GraphiQL.createFetcher({
            url: '/graphql',
            headers: {'X-IAE-KEY': '102022400128'}
        });

        ReactDOM.render(
            React.createElement(GraphiQL, {
                fetcher,
                defaultEditorToolsVisibility: true,
                query: 'query { unit(id: 1) { id unit_code unit_name tower floor room_number unit_type status tenant_name tenant_phone } }'
            }),
            document.getElementById('graphiql')
        );
    </script>
</body>
</html>
HTML;

        return response($html);
    }

    public function playground(): Response
    {
        $html = <<<'HTML'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Listing Unit Service GraphQL Playground</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/graphql-playground-react/build/static/css/index.css">
</head>
<body style="margin:0;">
    <div id="root" style="height:100vh;"></div>
    <script src="https://cdn.jsdelivr.net/npm/graphql-playground-react/build/static/js/middleware.js"></script>
    <script>
        window.addEventListener('load', function () {
            GraphQLPlayground.init(document.getElementById('root'), {
                endpoint: '/graphql',
                settings: {
                    'request.credentials': 'same-origin'
                },
                headers: {
                    'X-IAE-KEY': '102022400128'
                },
                tabs: [
                    {
                        endpoint: '/graphql',
                        query: 'query { unit(id: 1) { id unit_code unit_name tower floor room_number unit_type status tenant_name tenant_phone } }',
                        name: 'unit(id: ID!)'
                    }
                ]
            });
        });
    </script>
</body>
</html>
HTML;

        return response($html);
    }

    private function extractIdFromQuery(string $query): ?int
    {
        preg_match('/unit\s*\(\s*id\s*:\s*"?(\d+)"?\s*\)/', $query, $matches);

        return isset($matches[1]) ? (int) $matches[1] : null;
    }

    private function extractSelectedFields(string $query): array
    {
        preg_match('/unit\s*\([^)]*\)\s*\{([^}]*)\}/s', $query, $matches);

        if (! isset($matches[1])) {
            return self::ALLOWED_FIELDS;
        }

        $fields = preg_split('/\s+/', trim($matches[1])) ?: [];
        $fields = array_values(array_intersect($fields, self::ALLOWED_FIELDS));

        return $fields === [] ? self::ALLOWED_FIELDS : $fields;
    }
}
