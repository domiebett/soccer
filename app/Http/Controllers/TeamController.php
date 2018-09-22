<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;

class TeamController extends Controller
{
    public function index() {
        
        return view('teams.teams', [
            'teams' => Team::all()
        ]);
    }
}
