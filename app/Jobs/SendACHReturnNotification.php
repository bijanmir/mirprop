<?php
namespace App\Jobs;
use App\Mail\ACHReturnMail;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
class SendACHReturnNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public Payment $payment,
        public array $returnData
    ) {
    }

    public function handle(): void
    {
        // Load relationships
        $this->payment->load(['lease.unit.property', 'contact']);

        // Check if contact has email
        if (!$this->payment->contact->email) {
            Log::warning('Cannot send ACH return notification - no email address', [
                'payment_id' => $this->payment->id,
                'contact_id' => $this->payment->contact_id
            ]);
            return;
        }

        try {
            // Send to tenant
            Mail::to($this->payment->contact->email)
                ->queue(new ACHReturnMail($this->payment, $this->returnData));

            // Also notify property managers
            if ($this->payment->lease && $this->payment->lease->organization) {
                $managers = $this->payment->lease->organization->users()
                    ->wherePivotIn('role', ['owner', 'manager'])
                    ->get();

                foreach ($managers as $manager) {
                    Mail::to($manager->email)
                        ->queue(new ACHReturnMail($this->payment, $this->returnData, true));
                }
            }

            Log::info('ACH return notification queued', [
                'payment_id' => $this->payment->id,
                'return_code' => $this->returnData['return_code'] ?? 'unknown',
                'recipient' => $this->payment->contact->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue ACH return notification', [
                'payment_id' => $this->payment->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}