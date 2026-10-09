<?php

return [
    // Reads the original MOUSE28_ name so existing deployments keep their page size.
    'per_page' => max(1, (int) env('MOUSE28_SEARCH_RESULTS_PER_PAGE', 6)),
];
