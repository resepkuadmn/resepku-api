<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewItemNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $type;
    public $item;

    /**
     * Create a new message instance.
     */
    public function __construct($type, $item)
    {
        $this->type = $type; // 'resep' | 'tips' | 'about'
        $this->item = $item;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = strtoupper($this->type) . ' baru: ' . ($this->item->judul ?? 'Item baru');
        return $this->subject($subject)
                    ->view('emails.new_item')
                    ->with(['type' => $this->type, 'item' => $this->item]);
    }
}
