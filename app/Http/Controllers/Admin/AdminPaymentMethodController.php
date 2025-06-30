<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
// Required for Rule::in

class AdminPaymentMethodController extends Controller
{
    private static $paymentMethodTypes = [
        'bank'          => 'Cuenta Bancaria',
        'wallet'        => 'Billetera Digital (ej: Zinli, PayPal)',
        'crypto_wallet' => 'Billetera de Criptomonedas',
        // 'paypal_manual' => 'PayPal (Instrucciones Manuales)', // Example, can be merged into 'wallet'
    ];

    private function getCommonRules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'type'                => ['required', Rule::in(array_keys(self::$paymentMethodTypes))],
            'instructions'        => 'nullable|string',
            'logo_url'            => 'nullable|url|max:255',
            'is_active'           => 'boolean',
            'account_holder_name' => 'nullable|string|max:255', // Common for most types
        ];
    }

    private function getTypeSpecificRules(Request $request): array
    {
        $typeSpecificRules = [];
        $type              = $request->input('type');

        if ($type === 'bank') {
            $typeSpecificRules = [
                'bank_name'             => 'required|string|max:255',
                'account_number'        => 'required|string|max:255', // Bank account number
                'account_holder_name'   => 'required|string|max:255', // Overrides common if more specific needed
                'identification_number' => 'nullable|string|max:50',  // e.g., Cédula, RIF for bank
                'swift_code'            => 'nullable|string|max:50',
                'iban'                  => 'nullable|string|max:50',
                'branch_name'           => 'nullable|string|max:255',
                                                                    // Fields specific to other types should be nullable or not present
                'platform_name'         => 'nullable|string|max:1', // Effectively forces it to be empty
                'email_address'         => 'nullable|string|max:1',
                'payment_link'          => 'nullable|string|max:1',
            ];
        } elseif ($type === 'wallet' || $type === 'paypal_manual') {
            $typeSpecificRules = [
                'platform_name'         => 'required|string|max:255', // e.g., PayPal, Zinli
                'account_holder_name'   => 'required|string|max:255', // Wallet holder name/ID
                'email_address'         => 'nullable|email|max:255',
                'payment_link'          => 'nullable|url|max:255',
                // Bank specific fields should be nullable or not present
                'bank_name'             => 'nullable|string|max:1',
                'account_number'        => 'nullable|string|max:1', // For wallets, account_number might be an email or phone, handled by email_address or specific field
                'identification_number' => 'nullable|string|max:1',
                'swift_code'            => 'nullable|string|max:1',
                'iban'                  => 'nullable|string|max:1',
                'branch_name'           => 'nullable|string|max:1',
                // Require at least one of email or payment_link for wallets
                // This can be done with a custom rule or by adjusting individual rules:
                // 'email_address' => 'required_without:payment_link|nullable|email|max:255',
                // 'payment_link' => 'required_without:email_address|nullable|url|max:255',
                // For simplicity, current setup allows both to be nullable, admin needs to ensure one is filled.
            ];
        } elseif ($type === 'crypto_wallet') {
            $typeSpecificRules = [
                'platform_name'         => 'required|string|max:255', // e.g., Bitcoin (BTC), Ethereum (ETH)
                'account_number'        => 'required|string|max:255', // Wallet Address
                'account_holder_name'   => 'nullable|string|max:255', // Optional
                                                                      // Bank specific fields
                'bank_name'             => 'nullable|string|max:1',
                'identification_number' => 'nullable|string|max:1',
                'swift_code'            => 'nullable|string|max:1',
                'iban'                  => 'nullable|string|max:1',
                'branch_name'           => 'nullable|string|max:1',
                // Wallet specific fields
                'email_address'         => 'nullable|string|max:1',
                'payment_link'          => 'nullable|string|max:1',
            ];
        }
        return $typeSpecificRules;
    }

    private function prepareValidatedData(array $validatedData, string $type): array
    {
        $dataToSave = [
            'name'                => $validatedData['name'],
            'type'                => $validatedData['type'],
            'instructions'        => $validatedData['instructions'] ?? null,
            'logo_url'            => $validatedData['logo_url'] ?? null,
            'is_active'           => $validatedData['is_active'] ?? false,
            'account_holder_name' => $validatedData['account_holder_name'] ?? null,
        ];

                                                                          // Default bank-specific fields that might be NOT NULL to empty strings
        $bankSpecificNotNullableFields = ['bank_name', 'account_number']; // Add others if they are also NOT NULL and bank-specific
        foreach ($bankSpecificNotNullableFields as $field) {
            $dataToSave[$field] = '';
        }

                                                                                              // Default other bank-specific fields that might be nullable to null (or empty string if NOT NULL)
                                                                                              // Assuming these might be nullable or need specific handling
        $dataToSave['identification_number'] = $validatedData['identification_number'] ?? ''; // Assuming empty string if not provided & NOT NULL
        $dataToSave['swift_code']            = $validatedData['swift_code'] ?? null;
        $dataToSave['iban']                  = $validatedData['iban'] ?? null;
        $dataToSave['branch_name']           = $validatedData['branch_name'] ?? null;

        // Wallet/Crypto specific fields default to null (assuming they are nullable in DB)
        $dataToSave['platform_name'] = null;
        $dataToSave['email_address'] = null;
        $dataToSave['payment_link']  = null;
        // For crypto, account_number is the wallet address, so it's handled under its type.
        // For bank, account_number is handled by bankSpecificNotNullableFields.

        if ($type === 'bank') {
            $dataToSave['bank_name']             = $validatedData['bank_name'] ?? '';             // Should be present due to validation
            $dataToSave['account_number']        = $validatedData['account_number'] ?? '';        // Should be present
            $dataToSave['identification_number'] = $validatedData['identification_number'] ?? ''; // Or null if nullable
            $dataToSave['swift_code']            = $validatedData['swift_code'] ?? null;
            $dataToSave['iban']                  = $validatedData['iban'] ?? null;
            $dataToSave['branch_name']           = $validatedData['branch_name'] ?? null;
        } elseif ($type === 'wallet' || $type === 'paypal_manual') {
            $dataToSave['platform_name'] = $validatedData['platform_name'] ?? null;
            $dataToSave['email_address'] = $validatedData['email_address'] ?? null;
            $dataToSave['payment_link']  = $validatedData['payment_link'] ?? null;
            // Ensure bank_name and bank's account_number remain empty string for wallet type
            $dataToSave['bank_name'] = '';
            // If 'account_number' for wallet means something different (like phone/email),
            // it's currently being cleared by bankSpecificNotNullableFields.
            // The current structure uses 'account_number' for bank account OR crypto wallet address.
            // For 'wallet' type, 'account_number' field is not explicitly used for platform identifier in `getTypeSpecificRules`.
            // So, for 'wallet' type, 'account_number' (as in bank account number) should be empty.
            $dataToSave['account_number'] = '';
        } elseif ($type === 'crypto_wallet') {
            $dataToSave['platform_name']  = $validatedData['platform_name'] ?? null; // e.g. Bitcoin
            $dataToSave['account_number'] = $validatedData['account_number'] ?? '';  // Wallet address, ensure not null if DB requires
                                                                                     // Ensure bank_name remains empty string for crypto_wallet type
            $dataToSave['bank_name'] = '';
        }
        return $dataToSave;
    }

    public function index()
    {
        $paymentMethods = PaymentMethod::select('id', 'name', 'type', 'bank_name', 'platform_name', 'account_number', 'is_active')->get();
        return Inertia::render('Admin/PaymentMethods/Index', ['paymentMethods' => $paymentMethods]);
    }

    public function create()
    {
        return Inertia::render('Admin/PaymentMethods/Create', [
            'paymentMethodTypes' => self::$paymentMethodTypes,
        ]);
    }

    public function store(Request $request)
    {
        $rules         = array_merge($this->getCommonRules(), $this->getTypeSpecificRules($request));
        $validatedData = $request->validate($rules);
        $preparedData  = $this->prepareValidatedData($validatedData, $request->input('type'));

        PaymentMethod::create($preparedData);

        return Redirect::route('admin.payment-methods.index')->with('success', 'Payment method created successfully.');
    }

    public function show(PaymentMethod $paymentMethod)
    {
        // Redirigir al método edit ya que ambos hacen lo mismo
        return $this->edit($paymentMethod);
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return Inertia::render('Admin/PaymentMethods/Edit', [
            'paymentMethod'      => $paymentMethod,
            'paymentMethodTypes' => self::$paymentMethodTypes,
        ]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $rules         = array_merge($this->getCommonRules(), $this->getTypeSpecificRules($request));
        $validatedData = $request->validate($rules);

        // Ensure 'is_active' is set, as unchecked checkboxes might not be sent if false
        $validatedData['is_active'] = $request->has('is_active') && $request->input('is_active');

        $preparedData = $this->prepareValidatedData($validatedData, $request->input('type'));
        $paymentMethod->update($preparedData);

        return Redirect::route('admin.payment-methods.index')->with('success', 'Payment method updated successfully.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();
        return Redirect::route('admin.payment-methods.index')->with('success', 'Payment method deleted successfully.');
    }
}
