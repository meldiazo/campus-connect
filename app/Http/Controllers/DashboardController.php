<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('requests.index');
        }
        $base = ServiceRequest::query();
        $counts = [
            'total' => $base->count(), 'Pendiente' => (clone $base)->where('status', 'Pendiente')->count(),
            'En proceso' => (clone $base)->where('status', 'En proceso')->count(), 'Resuelta' => (clone $base)->where('status', 'Resuelta')->count(),
            'Cerrada' => (clone $base)->where('status', 'Cerrada')->count(), 'Urgente' => (clone $base)->where('priority', 'Urgente')->count(),
        ];
        $byCategory = Category::withCount('requests')->orderByDesc('requests_count')->get();
        $byStatus = ServiceRequest::select('status', DB::raw('count(*) as total'))->groupBy('status')->orderBy('status')->get();
        $byPriority = ServiceRequest::select('priority', DB::raw('count(*) as total'))->groupBy('priority')->orderBy('priority')->get();

        return view('dashboard', compact('counts', 'byCategory', 'byStatus', 'byPriority'));
    }
}
