<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailSend extends Mailable
{
    use Queueable, SerializesModels;
    public $title;
    public $content;
    /**
     * Create a new message instance.
     */
    public function __construct($title,$content)
    {
        $this->title = $title;
        $this->content = $content;
    }
    
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.sendmail',
            with: [
                'title' => $this->title,
                'content' => $this->content,
            ],
        );
    }
}