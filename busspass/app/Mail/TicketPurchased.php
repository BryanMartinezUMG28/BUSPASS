<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TicketPurchased extends Mailable
{
    use Queueable, SerializesModels;

    protected $ticket;

    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    public function build()
    {
        $pdf = PDF::loadView('tickets.ticket', ['ticket' => $this->ticket]);
        $pdfContent = $pdf->output();
        if ($pdfContent) {
            Log::info("PDF generado correctamente.");
        } else {
            Log::error("Error al generar el PDF.");
        }

        return $this->view('emails.ticket', ['ticket' => $this->ticket])
            ->attachData($pdf->output(), 'ticket.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
