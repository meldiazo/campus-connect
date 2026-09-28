<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestHistory extends Model
{
    protected $fillable = ['service_request_id', 'user_id', 'action', 'description', 'old_value', 'new_value'];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
