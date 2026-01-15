# Laravel AI Patch Command

This bundle contains the `AIPatch` Artisan command that lets you run:

    php artisan ai:patch "Describe the change you want"

It will:

1. Scan your Laravel project for PHP & JS files
2. Send them to GPT-4.1 via the OpenAI API
3. Receive a unified git patch
4. Save it as `ai.patch`
5. Attempt to apply it with `git apply ai.patch`

## Installation

1. Require the OpenAI PHP client:

    composer require openai-php/client

2. Copy `app/Console/Commands/AIPatch.php` into your Laravel app.

3. Register the command in `app/Console/Kernel.php`:

    protected $commands = [
        \App\Console\Commands\AIPatch::class,
    ];

4. Set your API key in `.env`:

    OPENAI_API_KEY=sk-...

5. Run:

    php artisan ai:patch "Refactor MapGenerator to improve farming cycles"
