<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Mosque;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        $stats = [
            'totalMasjid' => Mosque::count(),
            'masjidAktif' => Mosque::where('status', 'aktif')->count(),
            'totalUser'=> User::where('role', '!=', 'Super Admin')->count(),
            'masjidTerbaru' => Mosque::orderByDesc('id')->take(5)->get(),
        ];
    }
}
