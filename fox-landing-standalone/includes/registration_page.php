<?php

declare(strict_types=1);

require_once __DIR__ . '/registration_portal.php';

function render_registration_page(string $mode = 'store'): void
{
    registration_render_page($mode === 'delivery' ? 'delivery' : 'store');
}
