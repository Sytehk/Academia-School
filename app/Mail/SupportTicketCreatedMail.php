<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\SupportTicket\Models\SupportTicket;

class SupportTicketCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public SupportTicket $ticket;

    public function __construct(SupportTicket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function build()
    {
        return $this->subject('Support Ticket Created: '.$this->ticket->title)
            ->markdown('emails.support-ticket.created')
            ->with([
                'ticket' => $this->ticket,
            ]);
    }
}
