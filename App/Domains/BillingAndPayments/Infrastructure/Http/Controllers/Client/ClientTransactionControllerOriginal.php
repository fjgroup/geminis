<?php

namespace App\Domains\BillingAndPayments\Infrastructure\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Domains\BillingAndPayments\Infrastructure\Persistence\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ClientTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return InertiaResponse
     */
    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', Transaction::class);

        $user = Auth::user();

        $transactions = Transaction::where('client_id', $user->id)
            ->with(['invoice:id,invoice_number']) // Eager load invoice id and number
            ->latest('transaction_date') // Order by most recent transaction date
            ->paginate(15); // Paginate results

        return Inertia::render('Client/Transactions/Index', [
            'transactions' => $transactions,
        ]);
    }
}
