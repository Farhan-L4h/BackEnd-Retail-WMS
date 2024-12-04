<?php

namespace App\Notifications;

use App\Models\BarangModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    protected $barang;
    protected $stok;

    public function __construct(BarangModel $barang, $stok)
    {
        $this->barang = $barang;
        $this->stok = $stok;
    }

    public function via($notifiable)
    {
        //Notifikasi dikirim via
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('Stok barang ' . $this->barang->nama_barang . ' sudah rendah.')
            ->line('Stok saat ini: ' . $this->stok)
            ->action('Periksa Stok', url('/admin/stok/' . $this->barang->id));
    }
}
