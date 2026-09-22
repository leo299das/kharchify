<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Expense;
use App\Models\Category;

class AdminSystemController extends Controller
{
    public function index()
    {
        $tables = [
            'users' => User::count(),
            'expenses' => Expense::count(),
            'categories' => Category::count(),
            'sessions' => DB::table('sessions')->count(),
            'cache' => DB::table('cache')->count(),
            'jobs' => DB::table('jobs')->count(),
        ];

        $serverInfo = [
            'os' => php_uname(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP CLI / Built-in',
            'database_engine' => config('database.default'),
            'app_debug' => config('app.debug') ? 'Enabled (True)' : 'Disabled (False)',
            'app_env' => config('app.env'),
            'app_url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];

        return view('admin.system', compact('tables', 'serverInfo'));
    }

    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');

        return back()->with('success', '⚡ Application cache, views, and configuration successfully cleared!');
    }
}
