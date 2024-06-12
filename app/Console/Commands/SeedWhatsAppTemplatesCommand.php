<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Academic\Database\Seeders\WhatsAppTemplateSeeder;

class SeedWhatsAppTemplatesCommand extends Command
{
    protected $signature = 'whatsapp:seed-templates';

    protected $description = 'Create missing default WhatsApp templates (result, fee collected, absent) for every institute';

    public function handle(): int
    {
        (new WhatsAppTemplateSeeder)->run();

        $this->info('Default WhatsApp templates have been assigned.');

        return self::SUCCESS;
    }
}
