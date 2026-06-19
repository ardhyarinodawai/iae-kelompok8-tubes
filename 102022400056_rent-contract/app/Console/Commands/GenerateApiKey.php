<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('apikey:generate')]
#[Description('Generate a new API Key for this service')]
class GenerateApiKey extends Command
{
    /**
     * Execute the console command.
     */
    protected $signature = 'apikey:generate';

    protected $description = 'Generate a new API Key for this service';

    public function handle(): void
    {
        $key = bin2hex(random_bytes(32));

        $this->info('Generated API Key:');
        $this->line($key);
    }
}
