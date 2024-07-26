<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;


class DeleteExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-expired-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = storage_path('app/tokens.json');
        if (File::exists($filePath)) {
            $data = json_decode(File::get($filePath), true);
            $updatedTokens = array_filter($tokens, function ($token) {
                return !Carbon::parse($token['expiry'])->isPast();
            });

            // Store the updated tokens list back to the file
            File::put($filePath, json_encode($updatedTokens));

            $this->info('Expired tokens deleted.');
        } else {
            $this->info('Token file does not exist.');
        }
    }
}
