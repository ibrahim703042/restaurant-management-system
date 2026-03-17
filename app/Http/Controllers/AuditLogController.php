<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:admin.audit.view']);
    }

    public function index(Request $request)
    {
        $q = AuditLog::query()->with('user:id,name,email')->orderByDesc('id');

        if ($request->filled('user_id')) {
            $q->where('user_id', $request->user_id);
        }
        if ($request->filled('search')) {
            $s = '%'.$request->search.'%';
            $q->where(function ($q2) use ($s) {
                $q2->where('action', 'like', $s)->orWhere('path', 'like', $s)->orWhere('route_name', 'like', $s);
            });
        }
        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->to);
        }

        $logs = $q->paginate(40)->withQueryString();
        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);

        return view('audit.index', compact('logs', 'users'));
    }
}
