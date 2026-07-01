<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanTempFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-temp-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean temporary files older than 1 day in storage/app/public/temp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tempDir = 'temp';
        $disk = Storage::disk('public');

        if (!$disk->exists($tempDir)) {
            $this->info("Directory $tempDir does not exist.");
            return;
        }

        $files = $disk->files($tempDir);
        $deleted = 0;
        $now = Carbon::now();

        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp($disk->lastModified($file));
            if ($now->diffInHours($lastModified) >= 24) {
                $disk->delete($file);
                $deleted++;
            }
        }

        $this->info("Deleted $deleted temporary files.");
        Log::info("CleanTempFiles: Deleted $deleted temporary files.");
    }
}
