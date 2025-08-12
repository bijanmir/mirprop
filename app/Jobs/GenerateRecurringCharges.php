<?php

namespace App\Jobs;
use App\Models\Lease;
use App\Models\LeaseCharge;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class GenerateRecurringCharges implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 1;
    public int $timeout = 300;

    public function handle(): void
    {
        $startTime = now();
        $chargesCreated = 0;

        Log::info('Starting recurring charge generation');

        // Get all active leases
        $activeLeases = Lease::with([
            'charges' => function ($query) {
                $query->where('is_recurring', true);
            }
        ])
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->chunk(100, function ($leases) use (&$chargesCreated) {
                foreach ($leases as $lease) {
                    $chargesCreated += $this->generateChargesForLease($lease);
                }
            });

        $duration = now()->diffInSeconds($startTime);

        Log::info('Recurring charge generation completed', [
            'charges_created' => $chargesCreated,
            'duration_seconds' => $duration
        ]);
    }

    private function generateChargesForLease(Lease $lease): int
    {
        $chargesCreated = 0;

        DB::transaction(function () use ($lease, &$chargesCreated) {
            $recurringCharges = $lease->charges()
                ->where('is_recurring', true)
                ->get();

            foreach ($recurringCharges as $recurringCharge) {
                $nextDueDate = $this->calculateNextDueDate($recurringCharge, $lease);

                if (!$nextDueDate || $nextDueDate->gt($lease->end_date)) {
                    continue;
                }

                // Check if charge already exists for this period
                $existingCharge = LeaseCharge::where('lease_id', $lease->id)
                    ->where('type', $recurringCharge->type)
                    ->where('is_recurring', false)
                    ->whereDate('due_date', $nextDueDate)
                    ->exists();

                if (!$existingCharge) {
                    LeaseCharge::create([
                        'lease_id' => $lease->id,
                        'type' => $recurringCharge->type,
                        'amount_cents' => $recurringCharge->amount_cents,
                        'description' => $recurringCharge->description,
                        'due_date' => $nextDueDate,
                        'balance_cents' => $recurringCharge->amount_cents,
                        'is_recurring' => false,
                        'meta' => [
                            'generated_from' => $recurringCharge->id,
                            'generated_at' => now()->toIso8601String()
                        ]
                    ]);

                    $chargesCreated++;

                    Log::info('Generated recurring charge', [
                        'lease_id' => $lease->id,
                        'charge_type' => $recurringCharge->type,
                        'amount_cents' => $recurringCharge->amount_cents,
                        'due_date' => $nextDueDate->toDateString()
                    ]);
                }
            }
        });

        return $chargesCreated;
    }

    private function calculateNextDueDate(LeaseCharge $recurringCharge, Lease $lease): ?Carbon
    {
        $today = now()->startOfDay();
        $dayOfMonth = $recurringCharge->day_of_month ?? 1;

        // Calculate the next due date based on frequency
        switch ($lease->frequency) {
            case 'monthly':
                $nextDueDate = $today->copy()->day($dayOfMonth);

                // If we've already passed this day in the current month, move to next month
                if ($nextDueDate->lte($today)) {
                    $nextDueDate->addMonth();
                }

                // Handle months with fewer days (e.g., February)
                if ($nextDueDate->day !== $dayOfMonth) {
                    $nextDueDate->endOfMonth();
                }
                break;

            case 'weekly':
                // For weekly, use the day of week from the lease start date
                $dayOfWeek = $lease->start_date->dayOfWeek;
                $nextDueDate = $today->copy()->next($dayOfWeek);
                break;

            case 'yearly':
                $nextDueDate = $lease->start_date->copy()->year($today->year);
                if ($nextDueDate->lte($today)) {
                    $nextDueDate->addYear();
                }
                break;

            default:
                return null;
        }

        // Don't generate charges before lease start or after lease end
        if ($nextDueDate->lt($lease->start_date) || $nextDueDate->gt($lease->end_date)) {
            return null;
        }

        return $nextDueDate;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Recurring charge generation failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}