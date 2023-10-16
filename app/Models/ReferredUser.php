<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferredUser extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function referUser() {
        return $this->belongsTo(User::class,'refer_user_id');
    }

    public function user() {
        return $this->belongsTo(User::class,'new_user_id');
    }
}
