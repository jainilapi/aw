<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotificationTemplateRequest;
use App\Http\Requests\UpdateNotificationTemplateRequest;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationTemplateController extends Controller
{
    protected string $title = 'Notification Templates';
    protected string $view = 'notification-templates.';

    public function __construct()
    {
        $this->middleware('permission:notification-templates.index')->only(['index']);
        $this->middleware('permission:notification-templates.create')->only(['create']);
        $this->middleware('permission:notification-templates.store')->only(['store']);
        $this->middleware('permission:notification-templates.edit')->only(['edit']);
        $this->middleware('permission:notification-templates.update')->only(['update']);
        $this->middleware('permission:notification-templates.show')->only(['show']);
        $this->middleware('permission:notification-templates.destroy')->only(['destroy']);
    }

    public function index()
    {
        if (request()->ajax()) {
            return $this->ajax();
        }

        $title = $this->title;
        $subTitle = 'Manage notification templates here';

        return view($this->view . 'index', compact('title', 'subTitle'));
    }

    public function create()
    {
        $title = $this->title;
        $subTitle = 'Add Notification Template';

        $channels = [
            'email' => 'Email',
            'sms' => 'SMS'
        ];

        $types = [
            'order_placed' => 'Order Placed',
            'order_shipped' => 'Order Shipped',
            'order_delivered' => 'Order Delivered',
            'password_reset' => 'Password Reset',
            'generic' => 'Generic',
        ];

        $variables = $this->getAvailableVariables();

        return view($this->view . 'create', compact('title', 'subTitle', 'channels', 'types', 'variables'));
    }

    public function store(StoreNotificationTemplateRequest $request)
    {
        $data = $this->prepareData($request->validated());

        NotificationTemplate::create($data);

        return redirect()->route('notification-templates.index')->with('success', 'Notification template created successfully.');
    }

    public function show(NotificationTemplate $notification_template)
    {
        $title = $this->title;
        $subTitle = 'View Notification Template';
        $template = $notification_template;

        return view($this->view . 'show', compact('title', 'subTitle', 'template'));
    }

    public function edit(NotificationTemplate $notification_template)
    {
        $title = $this->title;
        $subTitle = 'Edit Notification Template';
        $template = $notification_template;

        $channels = [
            'email' => 'Email',
            'sms' => 'SMS'
        ];

        $types = [
            'order_placed' => 'Order Placed',
            'order_shipped' => 'Order Shipped',
            'order_delivered' => 'Order Delivered',
            'password_reset' => 'Password Reset',
            'generic' => 'Generic',
        ];

        $variables = $this->getAvailableVariables();

        return view($this->view . 'edit', compact('title', 'subTitle', 'template', 'channels', 'types', 'variables'));
    }

    public function update(UpdateNotificationTemplateRequest $request, NotificationTemplate $notification_template)
    {
        $data = $this->prepareData($request->validated(), $notification_template->id);

        $notification_template->update($data);

        return redirect()->route('notification-templates.index')->with('success', 'Notification template updated successfully.');
    }

    public function destroy(NotificationTemplate $notification_template)
    {
        $notification_template->delete();

        return response()->json(['success' => 'Notification template deleted successfully.']);
    }

    /**
     * Datatables JSON listing.
     */
    public function ajax()
    {
        $query = NotificationTemplate::query()->orderByDesc('created_at');

        return datatables()
            ->eloquent($query)
            ->addColumn('status', function ($row) {
                if ($row->is_active) {
                    return '<span class="badge bg-success">Active</span>';
                }
                return '<span class="badge bg-secondary">Inactive</span>';
            })
            ->editColumn('channel', function ($row) {
                return ucfirst(str_replace('_', ' ', $row->channel));
            })
            ->editColumn('template_type', function ($row) {
                return $row->template_type
                    ? ucfirst(str_replace('_', ' ', $row->template_type))
                    : '-';
            })
            ->editColumn('updated_at', function ($row) {
                return optional($row->updated_at)->format('Y-m-d H:i');
            })
            ->addColumn('action', function ($row) {
                $html = '';
                $user = auth('web')->user();

                if ($user && ($user->isAdmin() || $user->can('notification-templates.show'))) {
                    $html .= '<a href="' . route('notification-templates.show', $row) . '" class="btn btn-sm btn-secondary me-1"><i class="fa fa-eye"></i></a>';
                }

                if ($user && ($user->isAdmin() || $user->can('notification-templates.edit'))) {
                    $html .= '<a href="' . route('notification-templates.edit', $row) . '" class="btn btn-sm btn-primary me-1"><i class="fa fa-edit"></i></a>';
                }

                if ($user && ($user->isAdmin() || $user->can('notification-templates.destroy'))) {
                    $html .= '<button type="button" class="btn btn-sm btn-danger" id="deleteRow" data-row-route="' . route('notification-templates.destroy', $row) . '"><i class="fa fa-trash"></i></button>';
                }

                return $html;
            })
            ->rawColumns(['status', 'action'])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Normalize and generate slug / booleans.
     */
    protected function prepareData(array $data, ?int $id = null): array
    {
        if (empty($data['slug'] ?? null) && !empty($data['name'] ?? null)) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : false;

        return $data;
    }

    /**
     * Variables available for templates, used only for UI reference.
     */
    protected function getAvailableVariables(): array
    {
        return [
            'Common' => [
                ['key' => '{{app_name}}', 'label' => 'Application Name'],
                ['key' => '{{current_date}}', 'label' => 'Current Date'],
            ],
            'Customer' => [
                ['key' => '{{customer_name}}', 'label' => 'Customer Full Name'],
                ['key' => '{{customer_email}}', 'label' => 'Customer Email'],
            ],
            'Order' => [
                ['key' => '{{order_number}}', 'label' => 'Order Number'],
                ['key' => '{{order_total}}', 'label' => 'Order Total Amount'],
                ['key' => '{{order_status}}', 'label' => 'Order Status'],
                ['key' => '{{order_date}}', 'label' => 'Order Date'],
            ],
            'Authentication' => [
                ['key' => '{{reset_link}}', 'label' => 'Password Reset Link'],
                ['key' => '{{verification_link}}', 'label' => 'Email Verification Link'],
            ],
        ];
    }
}

