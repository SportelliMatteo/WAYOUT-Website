<?php

return [
    'release_id' => env(
        'ARUBA_RELEASE_ID',
        is_file(base_path('RELEASE_ID')) ? trim((string) file_get_contents(base_path('RELEASE_ID'))) : null,
    ),
];
