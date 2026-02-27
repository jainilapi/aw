<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\TaxSlab;
use Illuminate\Http\Request;

class TaxSlabController extends Controller
{
    protected $title = 'Tax Slabs';
    protected $view = 'tax-slabs.';

    public function __construct()
    {
        $this->middleware('permission:tax-slabs.index')->only(['index', 'ajax']);
        $this->middleware('permission:tax-slabs.create')->only(['create']);
        $this->middleware('permission:tax-slabs.store')->only(['store']);
        $this->middleware('permission:tax-slabs.edit')->only(['edit']);
        $this->middleware('permission:tax-slabs.update')->only(['update']);
        $this->middleware('permission:tax-slabs.show')->only(['show']);
        $this->middleware('permission:tax-slabs.destroy')->only(['destroy']);
    }

    public function index()
    {
        if (request()->ajax()) {
            return $this->ajax();
        }

        $title = $this->title;
        $subTitle = 'Manage tax slabs here';
        return view($this->view . 'index', compact('title', 'subTitle'));
    }

    public function ajax()
    {
        $query = TaxSlab::query();

        return datatables()
        ->eloquent($query)
        ->addColumn('status_badge', function ($row) {
            return $row->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
        })
        ->addColumn('action', function ($row) {
            $html = '';
            if (auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('tax-slabs.edit')) {
                $html .= '<a href="' . route('tax-slabs.edit', encrypt($row->id)) . '" class="btn btn-sm btn-primary"> <i class="fa fa-edit"> </i> </a>&nbsp;';
            }
            if (auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('tax-slabs.destroy')) {
                $html .= '<button type="button" class="btn btn-sm btn-danger" id="deleteRow" data-row-route="' . route('tax-slabs.destroy', $row->id) . '"> <i class="fa fa-trash"> </i> </button>&nbsp;';
            }
            if (auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('tax-slabs.show')) {
                $html .= '<a href="' . route('tax-slabs.show', encrypt($row->id)) . '" class="btn btn-sm btn-secondary"> <i class="fa fa-eye"> </i> </a>';
            }
            return $html;
        })
        ->rawColumns(['action', 'status_badge'])
        ->addIndexColumn()
        ->toJson();
    }

    public function create()
    {
        $title = $this->title;
        $subTitle = 'Add New Tax Slab';
        return view($this->view . 'create', compact('title', 'subTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tax_slabs,name',
            'tax_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'name' => $request->string('name'),
                'tax_percentage' => $request->input('tax_percentage'),
                'description' => $request->input('description'),
                'status' => (bool) $request->input('status', true),
                'created_by' => auth()->id()
            ];

            TaxSlab::create($data);
            DB::commit();
            return redirect()->route('tax-slabs.index')->with('success', 'Tax Slab created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tax-slabs.index')->with('error', 'Something Went Wrong.');
        }
    }

    public function show(string $id)
    {
        $taxSlab = TaxSlab::findOrFail(decrypt($id));
        $title = $this->title;
        $subTitle = 'Tax Slab Details';
        return view($this->view . 'view', compact('title', 'subTitle', 'taxSlab'));
    }

    public function edit(string $id)
    {
        $taxSlab = TaxSlab::findOrFail(decrypt($id));
        $title = $this->title;
        $subTitle = 'Edit Tax Slab';
        return view($this->view . 'edit', compact('title', 'subTitle', 'taxSlab'));
    }

    public function update(Request $request, string $id)
    {
        $taxSlab = TaxSlab::findOrFail(decrypt($id));
        $request->validate([
            'name' => 'required|string|max:255|unique:tax_slabs,name,' . $taxSlab->id,
            'tax_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'name' => $request->string('name'),
                'tax_percentage' => $request->input('tax_percentage'),
                'description' => $request->input('description'),
                'status' => (bool) $request->input('status', false),
            ];

            $taxSlab->update($data);
            DB::commit();
            return redirect()->route('tax-slabs.index')->with('success', 'Tax Slab updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tax-slabs.index')->with('error', 'Something Went Wrong.');
        }
    }

    public function destroy(string $id)
    {
        $taxSlab = TaxSlab::findOrFail($id);
        $taxSlab->delete();
        return response()->json(['success' => 'Tax Slab deleted successfully.']);
    }
}
