<?php

namespace App\Http\Controllers;

use App\Models\AwCurrency;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class CurrencyController extends Controller
{
    protected $title = 'Currency Management';
    protected $view = 'currencies.';
    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Display a listing of currencies
     */
    public function index(Request $request)
    {
        $title = $this->title;
        $subTitle = 'Manage Currencies';

        // Handle DataTables AJAX request
        if ($request->ajax()) {
            $query = AwCurrency::query()->orderBy('sort_order');

            return DataTables::of($query)
                ->addColumn('status_badge', function ($currency) {
                    $class = $currency->is_active ? 'success' : 'secondary';
                    $text = $currency->is_active ? 'Active' : 'Inactive';
                    return "<span class=\"badge bg-{$class}\">{$text}</span>";
                })
                ->addColumn('base_badge', function ($currency) {
                    if ($currency->is_base) {
                        return '<span class="badge bg-primary">Base Currency</span>';
                    }
                    return '';
                })
                ->addColumn('formatted_rate', function ($currency) {
                    if ($currency->is_base) {
                        return '1.000000 (Base)';
                    }
                    return number_format($currency->exchange_rate, 6);
                })
                ->addColumn('sample_format', function ($currency) {
                    return $currency->formatPrice(1234.56);
                })
                ->addColumn('actions', function ($currency) {
                    $editUrl = route('currencies.edit', $currency->id);
                    $deleteUrl = route('currencies.destroy', $currency->id);

                    $actions = '<div class="btn-group">';
                    $actions .= "<a href=\"{$editUrl}\" class=\"btn btn-sm btn-primary\"><i class=\"fas fa-edit\"></i></a>";

                    // Don't allow deleting base currency
                    if (!$currency->is_base) {
                        $actions .= "<button type=\"button\" class=\"btn btn-sm btn-danger delete-btn\" data-url=\"{$deleteUrl}\"><i class=\"fas fa-trash\"></i></button>";
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['status_badge', 'base_badge', 'actions'])
                ->make(true);
        }

        return view($this->view . 'index', compact('title', 'subTitle'));
    }

    /**
     * Show the form for creating a new currency
     */
    public function create()
    {
        $title = $this->title;
        $subTitle = 'Add New Currency';
        $currency = null;
        $isEdit = false;

        return view($this->view . 'form', compact('title', 'subTitle', 'currency', 'isEdit'));
    }

    /**
     * Store a newly created currency
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'iso_code' => 'required|string|size:3|unique:aw_currencies,iso_code|alpha',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001|max:999999.999999',
            'decimal_places' => 'required|integer|min:0|max:4',
            'symbol_position' => 'required|in:before,after',
            'is_active' => 'boolean',
            'is_base' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Normalize ISO code to uppercase
        $validated['iso_code'] = strtoupper($validated['iso_code']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_base'] = $request->boolean('is_base');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $currency = AwCurrency::create($validated);

        // If this is set as base currency, update others
        if ($currency->is_base) {
            $currency->setAsBase();
        }

        // Clear cache
        $this->currencyService->clearCache();

        return redirect()
            ->route('currencies.index')
            ->with('success', 'Currency created successfully.');
    }

    /**
     * Show the form for editing a currency
     */
    public function edit($id)
    {
        $currency = AwCurrency::findOrFail($id);
        $title = $this->title;
        $subTitle = 'Edit Currency: ' . $currency->name;
        $isEdit = true;

        return view($this->view . 'form', compact('title', 'subTitle', 'currency', 'isEdit'));
    }

    /**
     * Update the specified currency
     */
    public function update(Request $request, $id)
    {
        $currency = AwCurrency::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'iso_code' => [
                'required',
                'string',
                'size:3',
                'alpha',
                Rule::unique('aw_currencies', 'iso_code')->ignore($currency->id),
            ],
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001|max:999999.999999',
            'decimal_places' => 'required|integer|min:0|max:4',
            'symbol_position' => 'required|in:before,after',
            'is_active' => 'boolean',
            'is_base' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Normalize ISO code to uppercase
        $validated['iso_code'] = strtoupper($validated['iso_code']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_base'] = $request->boolean('is_base');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        // Can't change exchange rate of base currency
        if ($currency->is_base && !$validated['is_base']) {
            // If removing base status, another currency must become base
            return back()
                ->withInput()
                ->withErrors(['is_base' => 'Cannot remove base currency status. Set another currency as base first.']);
        }

        $currency->update($validated);

        // If this is set as base currency, update others
        if ($validated['is_base'] && !$currency->wasChanged('is_base')) {
            $currency->setAsBase();
        } elseif ($validated['is_base']) {
            $currency->setAsBase();
        }

        // Clear cache
        $this->currencyService->clearCache();

        return redirect()
            ->route('currencies.index')
            ->with('success', 'Currency updated successfully.');
    }

    /**
     * Remove the specified currency
     */
    public function destroy($id)
    {
        $currency = AwCurrency::findOrFail($id);

        // Prevent deleting base currency
        if ($currency->is_base) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete base currency. Set another currency as base first.'
            ], 400);
        }

        // Check if currency is in use by orders
        $orderCount = $currency->orders()->count();
        if ($orderCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete currency. It is used in {$orderCount} order(s)."
            ], 400);
        }

        $currency->delete();

        // Clear cache
        $this->currencyService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Currency deleted successfully.'
        ]);
    }

    /**
     * Toggle currency active status (AJAX)
     */
    public function toggleStatus($id)
    {
        $currency = AwCurrency::findOrFail($id);

        // Can't deactivate base currency
        if ($currency->is_base && $currency->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deactivate base currency.'
            ], 400);
        }

        $currency->update(['is_active' => !$currency->is_active]);

        // Clear cache
        $this->currencyService->clearCache();

        return response()->json([
            'success' => true,
            'is_active' => $currency->is_active,
            'message' => $currency->is_active ? 'Currency activated.' : 'Currency deactivated.'
        ]);
    }
}
