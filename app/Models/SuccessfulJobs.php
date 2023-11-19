<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuccessfulJobs extends Model
{
    use HasFactory;

    protected  $fillable = [
        'tool_name',
        'success_count'
    ];

}
