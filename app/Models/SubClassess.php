<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubClassess extends Model
{
    use HasFactory;

    protected $table = 'sub_classess';
    protected $fillable = ['name','class_id'];
}
