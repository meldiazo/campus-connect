<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $query = ServiceRequest::with(['category', 'assignee'])->latest();
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        foreach (['status', 'priority', 'category_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->$field);
            }
        }
        $results = $query->paginate(15)->withQueryString();
        $durationExpression = DB::getDriverName() === 'pgsql'
            ? 'EXTRACT(EPOCH FROM (updated_at - created_at)) / 86400'
            : 'julianday(updated_at) - julianday(created_at)';
        $summary = ['total' => (clone $query)->count(), 'pending' => (clone $query)->where('status', 'Pendiente')->count(), 'closed' => (clone $query)->where('status', 'Cerrada')->count(), 'avg_days' => round((float) (ServiceRequest::whereNotNull('updated_at')->avg(DB::raw($durationExpression)) ?? 0), 1)];

        return view('reports.index', compact('results', 'summary') + ['categories' => Category::orderBy('name')->get(), 'statuses' => ['Pendiente', 'Asignada', 'En proceso', 'Resuelta', 'Cerrada', 'Cancelada'], 'priorities' => ['Baja', 'Media', 'Alta', 'Urgente']]);
    }
}
