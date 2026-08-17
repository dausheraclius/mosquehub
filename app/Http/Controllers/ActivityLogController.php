<?php

namespace App\Http\Controllers;

use App\Support\Concerns\HasMosqueContext;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    use HasMosqueContext;

    public function index(Request $request)
    {
        abort_unless($request->user()?->role === 'Ketua YMBPK', 403);

        $activities = Activity::query()
            ->with('causer:id,name,email')
            ->where('properties->mosque_id', $this->mosqueId)
            ->latest()
            ->paginate(30);

        return view('pages.activity-log', compact('activities'));
    }
}
