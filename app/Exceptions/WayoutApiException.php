<?php

namespace App\Exceptions;

use RuntimeException;

class WayoutApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 502,
        public readonly ?string $apiCode = null,
        public readonly array $response = [],
        public readonly ?string $requestMethod = null,
        public readonly ?string $requestPath = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
    }

    /** @return array<string, mixed> */
    public function logContext(): array
    {
        $context = [
            'method' => $this->requestMethod,
            'path' => $this->requestPath,
            'status' => $this->status,
            'code' => $this->apiCode,
            'message' => $this->redact($this->getMessage()),
        ];

        $reason = data_get($this->response, 'reason') ?? data_get($this->response, 'data.reason');
        if (is_string($reason) && $reason !== '') {
            $context['reason'] = mb_substr($reason, 0, 100);
        }

        $validationErrors = $this->validationErrors();
        if ($validationErrors !== []) {
            $context['validation_errors'] = $validationErrors;
        }

        if ($this->getPrevious()) {
            $context['cause_class'] = $this->getPrevious()::class;
            $context['cause_code'] = $this->getPrevious()->getCode();
        }

        return array_filter($context, static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    public function withRequest(string $method, string $path): self
    {
        return new self(
            $this->getMessage(),
            $this->status,
            $this->apiCode,
            $this->response,
            strtoupper($method),
            $path,
            $this,
        );
    }

    /** @return array<string, array<int, string>> */
    private function validationErrors(): array
    {
        $errors = data_get($this->response, 'errors') ?? data_get($this->response, 'data.errors');
        if (! is_array($errors)) {
            return [];
        }

        $safe = [];

        foreach (array_slice($errors, 0, 20, true) as $field => $messages) {
            $fieldName = is_string($field) ? mb_substr($field, 0, 100) : 'general';
            $values = is_array($messages) ? $messages : [$messages];

            foreach (array_slice($values, 0, 5) as $message) {
                if (is_string($message) && $message !== '') {
                    $safe[$fieldName][] = $this->redact($message);
                }
            }
        }

        return $safe;
    }

    private function redact(string $message): string
    {
        $message = preg_replace('/https?:\/\/\S+/i', '[redacted-url]', $message) ?? $message;
        $message = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[redacted-email]', $message) ?? $message;
        $message = preg_replace('/\+[1-9]\d{6,14}/', '[redacted-phone]', $message) ?? $message;
        $message = preg_replace('/\b[A-Za-z0-9_-]{40,}\b/', '[redacted-value]', $message) ?? $message;

        return mb_substr($message, 0, 500);
    }
}
