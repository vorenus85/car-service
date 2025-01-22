<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'car_id',
        'type',
        'registered',
        'ownbrand',
        'accident',
    ];

    /**
     * Kapcsolat az ügyféllel (Client).
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the registered date of a car by client_id and car_id.
     */
    public static function getRegisteredDate($clientId, $carId)
    {
        return self::where('client_id', $clientId)
            ->where('car_id', $carId)
            ->value('registered');
    }
}
