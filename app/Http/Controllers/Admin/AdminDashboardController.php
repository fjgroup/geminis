<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        $user        = Auth::user();
        $userContext = session('user_context', $user->role);

        return Inertia::render('Admin/Dashboard', [
            'userContext' => $userContext,
            'user'        => $user,
        ]);
    }
}
