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
     * Foreign key with Client.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Foreign key with Car.
     */
    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }
}
