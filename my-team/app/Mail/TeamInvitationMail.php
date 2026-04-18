<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invitation $invitation)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been invited to join '.$this->invitation->team->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitations.team-invitation',
            with: [
                'invitation' => $this->invitation,
                'invitationUrl' => route('invitations.show', $this->invitation->token),
            ],
        );
    }
}
