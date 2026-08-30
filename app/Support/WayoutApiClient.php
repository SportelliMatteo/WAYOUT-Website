<?php

namespace App\Support;

use App\Exceptions\WayoutApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use JsonException;
use Throwable;

class WayoutApiClient
{
    public function internalGet(string $path): array
    {
        return $this->sendInternal('GET', $path, '');
    }

    /** @param array<string, mixed> $payload */
    public function internalPost(string $path, array $payload): array
    {
        try {
            $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new WayoutApiException(
                'Impossibile preparare la richiesta al backend WAYOUT.',
                requestMethod: 'POST',
                requestPath: $path,
                previous: $exception,
            );
        }

        return $this->sendInternal('POST', $path, $body);
    }

    /** @param array<string, mixed> $payload @param array<string, string> $headers */
    public function post(string $path, array $payload, array $headers = []): Response
    {
        try {
            $response = $this->request()
                ->withHeaders($headers)
                ->asJson()
                ->post($path, $payload);
        } catch (WayoutApiException $exception) {
            throw $exception->withRequest('POST', $path);
        } catch (ConnectionException $exception) {
            throw new WayoutApiException('Il backend WAYOUT non è raggiungibile.', requestMethod: 'POST', requestPath: $path, previous: $exception);
        } catch (Throwable $exception) {
            throw new WayoutApiException('La richiesta al backend WAYOUT non è riuscita.', requestMethod: 'POST', requestPath: $path, previous: $exception);
        }

        $this->throwForFailedResponse($response, 'POST', $path);

        return $response;
    }

    public function bearerGet(string $path, string $accessToken): array
    {
        try {
            $response = $this->request()
                ->withToken($accessToken)
                ->get($path);
        } catch (WayoutApiException $exception) {
            throw $exception->withRequest('GET', $path);
        } catch (ConnectionException $exception) {
            throw new WayoutApiException('Il backend WAYOUT non è raggiungibile.', requestMethod: 'GET', requestPath: $path, previous: $exception);
        } catch (Throwable $exception) {
            throw new WayoutApiException('La richiesta al backend WAYOUT non è riuscita.', requestMethod: 'GET', requestPath: $path, previous: $exception);
        }

        $this->throwForFailedResponse($response, 'GET', $path);

        return $response->json() ?? [];
    }

    private function sendInternal(string $method, string $path, string $body): array
    {
        $secret = (string) config('services.wayout.internal_secret', '');

        if (strlen($secret) < 32) {
            throw new WayoutApiException(
                'Il canale sicuro con il backend WAYOUT non è configurato.',
                503,
                'WAYOUT_INTERNAL_SECRET_MISSING',
                requestMethod: $method,
                requestPath: $path,
            );
        }

        $timestamp = time();
        $headers = [
            'Content-Type' => 'application/json',
            'X-Wayout-Timestamp' => (string) $timestamp,
            'X-Wayout-Signature' => hash_hmac('sha256', $timestamp.'.'.$body, $secret),
        ];

        try {
            $request = $this->request()->withHeaders($headers);
            $response = $method === 'GET'
                ? $request->get($path)
                : $request->withBody($body, 'application/json')->post($path);
        } catch (WayoutApiException $exception) {
            throw $exception->withRequest($method, $path);
        } catch (ConnectionException $exception) {
            throw new WayoutApiException('Il backend WAYOUT non è raggiungibile.', requestMethod: $method, requestPath: $path, previous: $exception);
        } catch (Throwable $exception) {
            throw new WayoutApiException('La richiesta firmata al backend WAYOUT non è riuscita.', requestMethod: $method, requestPath: $path, previous: $exception);
        }

        $this->throwForFailedResponse($response, $method, $path);

        return $response->json() ?? [];
    }

    private function request(): PendingRequest
    {
        $baseUrl = rtrim((string) config('services.wayout.base_url', ''), '/');

        if ($baseUrl === '' || ! str_starts_with($baseUrl, 'https://')) {
            throw new WayoutApiException(
                'L’indirizzo HTTPS del backend WAYOUT non è configurato.',
                503,
                'WAYOUT_BASE_URL_MISSING',
            );
        }

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->connectTimeout((int) config('services.wayout.connect_timeout', 5))
            ->timeout((int) config('services.wayout.timeout', 15));
    }

    private function throwForFailedResponse(Response $response, string $method, string $path): void
    {
        if ($response->successful()) {
            return;
        }

        $payload = $response->json() ?? [];
        $message = is_string($payload['message'] ?? null)
            ? $payload['message']
            : 'Il backend WAYOUT ha rifiutato la richiesta.';
        $code = is_string($payload['code'] ?? null) ? $payload['code'] : null;

        throw new WayoutApiException($message, $response->status(), $code, $payload, strtoupper($method), $path);
    }
}
