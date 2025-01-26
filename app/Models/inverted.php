<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inverted extends Model
{
    use HasFactory;

    protected $table = 'registry_inverted';

    protected $fillable = ['inverted'];
}
