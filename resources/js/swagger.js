import SwaggerUI from 'swagger-ui-dist/swagger-ui-es-bundle.js';
import 'swagger-ui-dist/swagger-ui.css';

SwaggerUI({
    url: '/openapi/prelaunch-benefits.yaml',
    dom_id: '#swagger-ui',
    deepLinking: true,
    persistAuthorization: false,
    displayRequestDuration: true,
    tryItOutEnabled: false,
});
