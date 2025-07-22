<?php
namespace App\Domains\Products\Infrastructure\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Products\Models\DiscountPercentage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscountPercentageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $discounts = DiscountPercentage::with('billingCycles')
            ->orderBy('percentage', 'asc')
            ->get()
            ->map(function ($discount) {
                return [
                    'id'                   => $discount->id,
                    'name'                 => $discount->name,
                    'percentage'           => $discount->percentage,
                    'description'          => $discount->description,
                    'is_active'            => $discount->is_active,
                    'billing_cycles_count' => $discount->billingCycles->count(),
                    'billing_cycles'       => $discount->billingCycles->pluck('name')->join(', '),
                ];
            });

        return Inertia::render('Admin/DiscountPercentages/Index', [
            'discounts' => $discounts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
