<?php

$configuredReleaseId = trim((string) env('ARUBA_RELEASE_ID', ''));

return [
    'release_id' => $configuredReleaseId !== ''
        ? $configuredReleaseId
        : (is_file(base_path('RELEASE_ID')) ? trim((string) file_get_contents(base_path('RELEASE_ID'))) : null),
];
