<?php

return [
    'max_file_size_kb' => (int) env('ASSET_REVIEW_MAX_FILE_SIZE_KB', 5120),
    'max_files' => (int) env('ASSET_REVIEW_MAX_FILES', 20),
    'max_comment_length' => (int) env('ASSET_REVIEW_MAX_COMMENT_LENGTH', 5000),
];
