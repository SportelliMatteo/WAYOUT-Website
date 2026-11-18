<?php

namespace App\Support;

use App\Exceptions\WayoutApiException;

class SiteFaqService
{
    public function __construct(private readonly WayoutApiClient $client) {}

    public function sections(): array
    {
        $response = $this->client->get('/api/v1/faqs/grouped?type=SITE', ['Accept-Language' => app()->getLocale()]);
        if (($response['success'] ?? false) !== true || ! is_array($response['data'] ?? null) || ! array_is_list($response['data'])) {
            throw new WayoutApiException('Risposta FAQ non valida.', 502);
        }
        $sections = [];
        foreach ($response['data'] as $section) {
            if (! is_array($section) || ! array_key_exists('group', $section) || ! is_array($section['faqs'] ?? null)) {
                throw new WayoutApiException('Sezione FAQ non valida.', 502);
            }
            $group = $section['group'];
            if ($group !== null && (! is_array($group) || ! is_string($group['id'] ?? null) || ! is_string($group['name'] ?? null))) {
                throw new WayoutApiException('Gruppo FAQ non valido.', 502);
            }
            $items = [];
            foreach ($section['faqs'] as $faq) {
                if (! is_array($faq) || ! is_string($faq['id'] ?? null)
                    || ! is_string($faq['question'] ?? null) || ! is_string($faq['answer'] ?? null)) {
                    throw new WayoutApiException('Contenuto FAQ non valido.', 502);
                }
                $items[] = ['id' => $faq['id'], 'question' => $faq['question'], 'answer' => $faq['answer']];
            }
            if ($items !== []) {
                $sections[] = [
                    'id' => $group === null ? 'faq-ungrouped' : 'faq-group-'.$group['id'],
                    'title' => $group['name'] ?? null,
                    'items' => $items,
                ];
            }
        }

        return $sections;
    }
}
