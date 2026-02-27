<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Country;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    protected $title = 'Customers';
    protected $view = 'customers.';

    public function __construct()
    {
        $this->middleware('permission:customers.index')->only(['index']);
        $this->middleware('permission:customers.create')->only(['create']);
        $this->middleware('permission:customers.store')->only(['store']);
        $this->middleware('permission:customers.edit')->only(['edit']);
        $this->middleware('permission:customers.update')->only(['update']);
        $this->middleware('permission:customers.show')->only(['show']);
        $this->middleware('permission:customers.destroy')->only(['destroy']);
    }

    public function index()
    {
        if (request()->ajax()) {
            return $this->ajax();
        }

        $title = $this->title;
        $subTitle = 'Manage customers here';

        return view($this->view . 'index', compact('title', 'subTitle'));
    }

    public function ajax()
    {
        $customerRole = Role::where('slug', 'customer')->first();

        $query = User::query()
            ->whereHas('roles', function ($builder) use ($customerRole) {
                $builder->where('id', $customerRole->id);
            });

        return datatables()
            ->eloquent($query)
            ->editColumn('phone_number', function ($row) {
                return '+' . $row->dial_code . ' ' . $row->phone_number;
            })
            ->addColumn('location', function ($row) {
                $location = [];
                if ($row->city)
                    $location[] = $row->city->name;
                if ($row->state)
                    $location[] = $row->state->name;
                if ($row->country)
                    $location[] = $row->country->name;
                return implode(', ', $location);
            })
            ->addColumn('status', function ($row) {
                if ($row->status) {
                    return '<span class="badge bg-success"> Active </span>';
                } else {
                    return '<span class="badge bg-danger"> InActive </span>';
                }
            })
            ->addColumn('action', function ($row) {
                $html = '';

                if (auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('customers.edit')) {
                    $html .= '<a href="' . route('customers.edit', encrypt($row->id)) . '" class="btn btn-sm btn-primary"> <i class="fa fa-edit"> </i> </a>&nbsp;';
                }

                if (auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('customers.destroy')) {
                    $html .= '<button type="button" class="btn btn-sm btn-danger" id="deleteRow" data-row-route="' . route('customers.destroy', $row->id) . '"> <i class="fa fa-trash"> </i> </button>&nbsp;';
                }

                if (auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('customers.show')) {
                    $html .= '<a href="' . route('customers.show', encrypt($row->id)) . '" class="btn btn-sm btn-secondary"> <i class="fa fa-eye"> </i> </a>';
                }

                return $html;
            })
            ->rawColumns(['status', 'action'])
            ->addIndexColumn()
            ->toJson();
    }

    public function create()
    {
        $title = $this->title;
        $subTitle = 'Add New Customer';
        $countries = Country::pluck('name', 'id');
        return view($this->view . 'create', compact('title', 'subTitle', 'countries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'dial_code' => 'required|string|max:10',
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'status' => 'required|boolean',
            'password' => 'required|string|min:6',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->only(['name', 'email', 'dial_code', 'phone_number', 'country_id', 'state_id', 'city_id', 'status', 'password']);
            if ($request->has('opening_balance')) {
                $data['credit_balance'] = $request->opening_balance;
            }

            if (in_array($request->country_id, \App\Helpers\Helper::$carribianCountries)) {
                $data['city_id'] = null;
            }
            $data['added_by'] = auth()->guard('web')->user()->id;

            $customer = User::create($data);

            $customerRole = Role::where('slug', 'customer')->first();
            if ($customerRole) {
                $customer->roles()->attach($customerRole->id);
            }

            if ($request->filled('opening_balance') && $request->opening_balance > 0) {
                \App\Models\CreditLog::create([
                    'user_id' => $customer->id,
                    'admin_id' => auth()->guard('web')->id(),
                    'amount' => $request->opening_balance,
                    'type' => 'credit',
                    'reason' => 'Opening Balance',
                    'reference_type' => 'manual',
                ]);
            }

            if ($request->has('locations_json')) {
                $locations = json_decode($request->locations_json, true);
                if (is_array($locations)) {
                    foreach ($locations as $locationData) {
                        $locationData['customer_id'] = $customer->id;
                        if (in_array($locationData['country_id'], \App\Helpers\Helper::$carribianCountries)) {
                            $locationData['city_id'] = null;
                        }
                        \App\Models\Location::create($locationData);
                    }
                }
            }

            DB::commit();
            return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('customers.index')->with('error', 'Something Went Wrong.');
        }
    }

    public function show(string $id)
    {
        $customer = User::findOrFail(decrypt($id));
        $title = $this->title;
        $subTitle = 'Customer Details';
        return view($this->view . 'view', compact('title', 'subTitle', 'customer'));
    }

    public function edit(string $id)
    {
        $customer = User::findOrFail(decrypt($id));
        $title = $this->title;
        $subTitle = 'Edit Customer';
        $countries = Country::pluck('name', 'id');
        return view($this->view . 'edit', compact('title', 'subTitle', 'customer', 'countries'));
    }

    public function update(Request $request, string $id)
    {
        $customer = User::findOrFail(decrypt($id));
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $customer->id,
            'dial_code' => 'required|string|max:10',
            'phone_number' => 'required|string|max:20|unique:users,phone_number,' . $customer->id,
            'status' => 'required|boolean',
            'password' => 'nullable|string|min:6',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->only(['name', 'email', 'dial_code', 'phone_number', 'country_id', 'state_id', 'city_id', 'status']);
            if (in_array($request->country_id, \App\Helpers\Helper::$carribianCountries)) {
                $data['city_id'] = null;
            }

            if ($request->filled('password')) {
                $data['password'] = $request->password;
            }

            $customer->update($data);

            DB::commit();
            return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('customers.index')->with('error', 'Something Went Wrong.');
        }
    }

    public function destroy(string $id)
    {
        $customer = User::findOrFail($id);
        $customer->delete();
        return response()->json(['success' => 'Customer deleted successfully.']);
    }

    public function updateCredit(Request $request, $id)
    {
        $customer = User::findOrFail(decrypt($id));
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:credit,debit',
            'reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            if ($request->type == 'credit') {
                $customer->increment('credit_balance', $request->amount);
            } else {
                if ($customer->credit_balance < $request->amount) {
                    return response()->json(['error' => 'Insufficient credit balance.'], 422);
                }
                $customer->decrement('credit_balance', $request->amount);
            }

            \App\Models\CreditLog::create([
                'user_id' => $customer->id,
                'admin_id' => auth()->guard('web')->id(),
                'amount' => $request->amount,
                'type' => $request->type,
                'reason' => $request->reason,
                'reference_type' => 'manual',
            ]);

            DB::commit();

            return response()->json([
                'success' => 'Credit updated successfully.',
                'new_balance' => $customer->credit_balance,
                'formatted_balance' => number_format($customer->credit_balance, 2)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }

    public function getCreditLogs($id)
    {
        $id = decrypt($id);
        $logs = \App\Models\CreditLog::where('user_id', $id)
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->get();

        return datatables()->of($logs)
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('M d, Y H:i A');
            })
            ->editColumn('amount', function ($row) {
                return ($row->type == 'credit' ? '+' : '-') . number_format($row->amount, 2);
            })
            ->addColumn('admin_name', function ($row) {
                return $row->admin ? $row->admin->name : 'System';
            })
            ->addColumn('type_badge', function ($row) {
                if ($row->type == 'credit') {
                    return '<span class="badge bg-success">Credit</span>';
                }
                return '<span class="badge bg-danger">Debit</span>';
            })
            ->rawColumns(['type_badge'])
            ->make(true);
    }
}
