<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>WAYOUT Pre-launch Benefits API</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
</head>
<body>
<div id="swagger-ui"></div>
<script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
<script>
    SwaggerUIBundle({
        url: @json(asset('openapi/prelaunch-benefits.yaml')),
        dom_id: '#swagger-ui',
        deepLinking: true,
        persistAuthorization: false,
        displayRequestDuration: true,
    });
</script>
</body>
</html>
