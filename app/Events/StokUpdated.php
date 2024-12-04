<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class StokUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $id_barang;
    public $stok;

    // Menerima data id_barang dan stok
    public function __construct($id_barang, $stok)
    {
        $this->id_barang = $id_barang;
        $this->stok = $stok;
    }

    // Channel yang digunakan untuk broadcasting
    public function broadcastOn()
    {
        return new Channel('stok-channel');
    }
}
