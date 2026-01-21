<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Condition;

class ConditionController extends Controller
{
    /**
     * Get all conditions
     */
    public function index()
    {
        $conditions = Condition::all();
        return response()->json(['conditions' => $conditions]);
    }
}
