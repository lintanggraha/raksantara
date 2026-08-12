<?php

/**
 * Vercel serverless entrypoint for Laravel.
 * The PHP runtime dispatches every non-static request through Laravel's
 * existing public front controller.
 */
require __DIR__ . '/../public/index.php';
