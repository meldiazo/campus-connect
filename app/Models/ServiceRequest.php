<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'category_id', 'assigned_to', 'title', 'description', 'location', 'priority', 'status'];

    protected $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function histories()
    {
        return $this->hasMany(RequestHistory::class)->latest();
    }
}
