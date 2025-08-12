<?php
namespace App\Mail;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
class PaymentReceiptMail extends Mailable
{
use Queueable, SerializesModels;
public function __construct(
    public Payment $payment
) {}

public function envelope(): Envelope
{
    return new Envelope(
        subject: 'Payment Receipt - ' . $this->payment->lease->unit->full_identifier,
    );
}

public function content(): Content
{
    return new Content(
        markdown: 'emails.payment-receipt',
        with: [
            'payment' => $this->payment,
            'property' => $this->payment->lease->unit->property->name,
            'unit' => $this->payment->lease->unit->label,
            'amount' => number_format($this->payment->amount_cents / 100, 2),
            'paymentDate' => $this->payment->posted_at->format('F j, Y'),
            'paymentMethod' => ucfirst($this->payment->method ?? 'Unknown'),
            'referenceNumber' => $this->payment->processor_id,
        ]
    );
}

public function attachments(): array
{
    return [];
}
}