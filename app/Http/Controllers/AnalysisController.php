<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class AnalysisController extends Controller
{
    public function index()
    {
        return Inertia::render('Analysis/Index');
    }
}
