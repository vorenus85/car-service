<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'car_id',
        'lognumber',
        'event',
        'eventtime',
        'document_id',
    ];

    /**
     * Kapcsolat az ügyféllel (Client).
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Kapcsolat az autóval (Car).
     */
    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }
}