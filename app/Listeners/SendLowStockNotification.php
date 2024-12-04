<?php

namespace App\Listeners;

use App\Events\StokUpdated;
use App\Models\BarangModel;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;

class SendLowStockNotification
{
    public function handle(StokUpdated $event)
    {
        // Menentukan batas stok minimum secara manual
        $threshold = 10;  // Misalnya, batas stok rendah adalah 10 unit

        // Mengambil stok barang terbaru
        $barang = BarangModel::find($event->id_barang);

        // Mengecek apakah stok lebih rendah dari batas minimum yang sudah ditentukan
        if ($barang && $event->stok < $threshold) {
            // Mengirim notifikasi jika stok lebih rendah dari threshold
            Notification::route('mail', 'admin@example.com')
                ->notify(new LowStockNotification($barang, $event->stok));
        }
    }
}
