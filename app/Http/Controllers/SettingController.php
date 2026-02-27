<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\AwCurrency;
use App\Services\CurrencyService;

class SettingController extends Controller
{
    protected $title = 'Settings';
    protected $view = 'settings.';
    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function index()
    {
        $setting = Setting::first();
        $title = $this->title;
        $subTitle = 'Manage Application Settings';
        return view($this->view . 'index', compact('title', 'subTitle', 'setting'));
    }

    public function update(Request $request)
    {
        $setting = Setting::first();
        $request->validate([
            'name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:1024',
            'base_currency_id' => 'nullable|exists:aw_currencies,id',
        ]);

        $data = $request->only([
            'name',
            'theme_color',
            'base_currency_id'
        ]);

        $destinationPath = public_path('settings-media');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            $data['logo'] = $filename;
        }
        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $filename = 'favicon.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            $data['favicon'] = $filename;
        }

        // Update base currency if changed
        if (!empty($data['base_currency_id'])) {
            $currency = AwCurrency::find($data['base_currency_id']);
            if ($currency) {
                $currency->setAsBase();
            }
        }

        if ($setting) {
            $setting->update($data);
        } else {
            Setting::create($data);
        }

        // Clear currency cache
        $this->currencyService->clearCache();

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
