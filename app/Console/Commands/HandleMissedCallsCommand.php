<?php

namespace App\Console\Commands;

use App\Models\Call;
use App\Services\CallMessageService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class HandleMissedCallsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:handle-missed {--timeout=30 : Call timeout in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark initiated calls as missed if they exceed the timeout period';

    protected $callMessageService;

    public function __construct(CallMessageService $callMessageService)
    {
        parent::__construct();
        $this->callMessageService = $callMessageService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeoutSeconds = $this->option('timeout');
        $timeoutThreshold = Carbon::now()->subSeconds($timeoutSeconds);

        $this->info("Checking for missed calls older than {$timeoutSeconds} seconds...");

        // Find initiated calls that are older than the timeout threshold
        $missedCalls = Call::where('status', 'initiated')
            ->where('created_at', '<', $timeoutThreshold)
            ->get();

        if ($missedCalls->isEmpty()) {
            $this->info('No missed calls found.');
            return Command::SUCCESS;
        }

        $processedCount = 0;
        $errorCount = 0;

        foreach ($missedCalls as $call) {
            try {
                $this->info("Processing missed call ID: {$call->id}");
                
                // Handle missed call
                $this->callMessageService->handleMissedCall($call);
                
                $processedCount++;
                
                // Log the missed call for monitoring
                Log::info("Missed call handled automatically", [
                    'call_id' => $call->id,
                    'caller_id' => $call->caller_id,
                    'receiver_id' => $call->receiver_id,
                    'type' => $call->type,
                    'created_at' => $call->created_at,
                    'timeout_seconds' => $timeoutSeconds
                ]);
                
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("Failed to handle missed call ID: {$call->id} - {$e->getMessage()}");
                
                Log::error("Failed to handle missed call automatically", [
                    'call_id' => $call->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("Processed {$processedCount} missed calls successfully.");
        
        if ($errorCount > 0) {
            $this->warn("Failed to process {$errorCount} missed calls.");
        }

        return Command::SUCCESS;
    }
} 