<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    protected $fillable = [
        'name', 'table_link', 'fixtures_link', 'results_link',
        'scores_link', 'logo', 'competition_link', 'service'];
}
