<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // Added this line
use Illuminate\Support\Facades\Log; // Added this line

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the command to run daily at 02:00 AM server time
Schedule::command('invoices:generate-renewals')
         ->daily()->at('02:00')
         ->onSuccess(function () {
             Log::info('Scheduled command invoices:generate-renewals executed successfully from routes/console.php.');
         })
         ->onFailure(function (Throwable $e) { // Ensured Throwable type-hint
             Log::error('Scheduled command invoices:generate-renewals failed from routes/console.php.', ['error' => $e->getMessage()]);
         });
