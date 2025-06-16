<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; // Keep for potential future use, though not directly used now
use Inertia\Inertia;
use App\Http\Requests\Client\StoreFundAdditionRequest; // Will be created next
use Illuminate\Support\Facades\Redirect;
use App\Services\PayPalService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Added for logging in new methods
use Illuminate\Support\Facades\DB; // Added for DB transactions in new methods
// Inertia is already imported via `use Inertia\Inertia;`
// PaymentMethod is already imported via `use App\Models\PaymentMethod;`
// Auth is already imported via `use Illuminate\Support\Facades\Auth;`
// Request is already imported via `use Illuminate\Http\Request;`


class ClientFundAdditionController extends Controller
{
    protected PayPalService $payPalService;

    public function __construct(PayPalService $payPalService)
    {
        $this->payPalService = $payPalService;
    }

    /**
     * Show the form for adding funds.
     */
    public function showAddFundsForm()
    {
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'is_automatic', 'type', 'account_holder_name', 'account_number', 'bank_name', 'branch_name', 'swift_code', 'iban', 'instructions', 'logo_url']);

        $client = Auth::user();
        $currencyCode = $client->currency_code ?? 'USD'; // Default to USD if not set

        return Inertia::render('Client/Funds/AddForm', [
            'paymentMethods' => $paymentMethods,
            'currencyCode' => $currencyCode,
        ]);
    }

    /**
     * Process the fund addition request from the client.
     */
    public function processFundAddition(StoreFundAdditionRequest $request)
    {
        $validated = $request->validated();
        $client = Auth::user();

        Transaction::create([
            'client_id' => $client->id,
            'invoice_id' => null,
            'order_id' => null,
            'payment_method_id' => $validated['payment_method_id'],
            'gateway_slug' => 'manual_fund_addition', // Specific slug for these types of transactions
            'gateway_transaction_id' => $validated['reference_number'], // Client's reference
            'amount' => $validated['amount'],
            'currency_code' => $client->currency_code ?? 'USD',
            'status' => 'pending', // Pending confirmation by admin
            'type' => 'credit_added',
            'transaction_date' => $validated['payment_date'],
            'description' => "Solicitud de adición de fondos por cliente.",
            'fees_amount' => 0, // Typically no fees for manual fund addition recording itself
        ]);

        return Redirect::route('client.transactions.index') // Or client.dashboard if more appropriate
            ->with('success', 'Tu solicitud para agregar fondos ha sido enviada y está pendiente de confirmación.');
    }

    public function initiatePayPalPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01', // Validación básica del monto
        ]);

        if ($validator->fails()) {
            return redirect()->route('client.funds.create')->withErrors($validator)->withInput();
        }

        $amount = (float) $request->input('amount');

        // Validación de monto mínimo para PayPal
        if ($amount < 30.00) { // Assuming a minimum like $5.00 USD, adjust as needed
            return redirect()->route('client.funds.create')
                ->with('error', 'Para agregar fondos con PayPal, el monto mínimo es de $30.00 USD.')
                ->withInput();
        }

        $user = Auth::user();
        $currencyCode = $user->currency_code ?? config('paypal.currency', 'USD'); // Usar moneda del usuario o default de PayPal

        try {
            $successUrl = route('client.funds.paypal.success');
            $cancelUrl = route('client.funds.paypal.cancel');

            $fundAdditionIdentifier = 'FUNDS-' . $user->id . '-' . strtoupper(Str::random(8));

            // $orderForPayPal object creation is removed.

            $descriptionSuffix = 'Usuario ID: ' . $user->id; // Example description suffix
            $paypalOrderDetails = $this->payPalService->createFundAdditionOrder(
                $amount,
                $currencyCode,
                $descriptionSuffix,
                $fundAdditionIdentifier, // This is the 'customIdentifier'
                $successUrl,
                $cancelUrl
            );

            if ($paypalOrderDetails && isset($paypalOrderDetails['approval_link'])) {
                $request->session()->put('paypal_fund_order_id', $paypalOrderDetails['order_id']);
                $request->session()->put('paypal_fund_amount', $amount);
                $request->session()->put('paypal_fund_currency', $currencyCode);
                $request->session()->put('paypal_fund_identifier', $fundAdditionIdentifier);

                return Inertia::location($paypalOrderDetails['approval_link']);
            } else {
                Log::error('PayPal fund addition: Failed to get approval link.', ['user_id' => $user->id, 'amount' => $amount, 'paypal_response' => $paypalOrderDetails]);
                return redirect()->route('client.funds.create')
                    ->with('error', 'No se pudo iniciar el pago con PayPal. Por favor, intente más tarde.');
            }
        } catch (\Exception $e) {
            Log::error('PayPal fund addition exception: ' . $e->getMessage(), [
                'user_id' => $user->id, 'amount' => $amount, 'trace' => substr($e->getTraceAsString(), 0, 500)
            ]);
            return redirect()->route('client.funds.create')
                ->with('error', 'Ocurrió un error inesperado con PayPal. Por favor, contacte a soporte.');
        }
    }

    public function handlePayPalSuccess(Request $request)
    {
        $paypalOrderId = $request->session()->get('paypal_fund_order_id');
        $amount = $request->session()->get('paypal_fund_amount');
        $currencyCode = $request->session()->get('paypal_fund_currency');
        // $fundIdentifier = $request->session()->get('paypal_fund_identifier');

        if (!$paypalOrderId || !is_numeric($amount) || !$currencyCode) {
            Log::error('PayPal fund success: Missing or invalid session data.', [
                'session_data' => $request->session()->all(),
                'user_id' => Auth::id()
            ]);
            return redirect()->route('client.funds.create')->with('error', 'Error al procesar el pago de PayPal: Sesión inválida o datos corruptos.');
        }

        $user = Auth::user();

        try {
            $captureResponse = $this->payPalService->captureOrder($paypalOrderId);

            if ($captureResponse && isset($captureResponse['status']) && $captureResponse['status'] === 'COMPLETED') {
                DB::beginTransaction();
                try {
                    $paypalPaymentMethod = PaymentMethod::where('slug', 'paypal')->first();

                    Transaction::create([
                        'client_id' => $user->id,
                        'invoice_id' => null,
                        'payment_method_id' => $paypalPaymentMethod ? $paypalPaymentMethod->id : null,
                        'gateway_slug' => 'paypal',
                        'gateway_transaction_id' => $captureResponse['paypal_capture_id'] ?? 'N/A',
                        'type' => 'credit_added',
                        'amount' => (float) $amount,
                        'currency_code' => $currencyCode,
                        'status' => 'completed',
                        'description' => "Adición de fondos vía PayPal. ID de captura: " . ($captureResponse['paypal_capture_id'] ?? 'N/A'),
                        'transaction_date' => Carbon::now(),
                        'fees_amount' => $captureResponse['paypal_fee'] ?? 0.00,
                    ]);

                    $user->increment('balance', (float) $amount);
                    // $user->save(); // increment already saves

                    DB::commit();

                    $request->session()->forget(['paypal_fund_order_id', 'paypal_fund_amount', 'paypal_fund_currency', 'paypal_fund_identifier']);
                    return redirect()->route('client.dashboard')
                        ->with('success', 'Fondos agregados exitosamente a tu cuenta por ' . $currencyCode . ' ' . number_format((float)$amount, 2) . '.');

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('PayPal fund success - DB transaction error: ' . $e->getMessage(), [
                        'user_id' => $user->id, 'paypal_order_id' => $paypalOrderId, 'trace' => substr($e->getTraceAsString(), 0, 500)
                    ]);
                    return redirect()->route('client.funds.create')->with('error', 'Error al registrar la adición de fondos después del pago. Contacte a soporte.');
                }
            } else {
                Log::error('PayPal fund success - Capture failed or status not COMPLETED.', [
                    'user_id' => $user->id, 'paypal_order_id' => $paypalOrderId, 'capture_response' => $captureResponse
                ]);
                $request->session()->forget(['paypal_fund_order_id', 'paypal_fund_amount', 'paypal_fund_currency', 'paypal_fund_identifier']);
                return redirect()->route('client.funds.create')->with('error', 'Falló la captura del pago con PayPal. No se agregaron fondos.');
            }
        } catch (\Exception $e) {
            Log::error('PayPal fund success - General exception: ' . $e->getMessage(), [
                'user_id' => $user->id, 'paypal_order_id' => $paypalOrderId, 'trace' => substr($e->getTraceAsString(), 0, 500)
            ]);
            $request->session()->forget(['paypal_fund_order_id', 'paypal_fund_amount', 'paypal_fund_currency', 'paypal_fund_identifier']);
            return redirect()->route('client.funds.create')->with('error', 'Error inesperado procesando el pago con PayPal. Contacte a soporte.');
        }
    }

    public function handlePayPalCancel(Request $request)
    {
        $request->session()->forget(['paypal_fund_order_id', 'paypal_fund_amount', 'paypal_fund_currency', 'paypal_fund_identifier']);
        Log::info('PayPal fund addition cancelled by user.', ['user_id' => Auth::id()]);
        return redirect()->route('client.funds.create')->with('info', 'La adición de fondos con PayPal fue cancelada.');
    }
}
