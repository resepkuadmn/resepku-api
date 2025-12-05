<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DeleteItemNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $type;
    public $title;

    /**
     * Create a new message instance.
     */
    public function __construct($type, $title)
    {
        $this->type = $type; // 'resep' | 'tips' | 'about'
        $this->title = $title;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = strtoupper($this->type) . ' dihapus: ' . $this->title;
        return $this->subject($subject)
                    ->view('emails.delete_item')
                    ->with(['type' => $this->type, 'title' => $this->title]);
    }
}
