<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\HomePageSetting;
use Illuminate\Http\Request;

use App\Models\AwCategory;
use App\Models\AwProduct;

class HomePageSettingController extends Controller
{
    public function index()
    {
        $settings = HomePageSetting::orderBy('ordering')->get();
        $products = AwProduct::select('id', 'name')->get();
        $categories = AwCategory::select('id', 'name')->get();
        return view('home-page-settings.index', compact('settings', 'products', 'categories'));
    }

    public function update(Request $request, $key)
    {
        $setting = HomePageSetting::where('key', $key)->firstOrFail();
        $value = $setting->value;

        if (isset($value->is_editable) && $value->is_editable === false) {
            return redirect()->back()->with('error', 'This setting is not editable.');
        }

        if ($key === 'banner_carousel') {
            $slides = $request->slides ?? [];

            foreach ($slides as $index => &$slide) {
                if (isset($slide['image']) && gettype($slide['image']) == 'object') {
                    $path = $slide['image']->store('home-page-banners', 'public');
                    $slides[$index]['image'] = $path;
                }

                if (!array_key_exists('has_button', $slide)) {
                    $slide['has_button'] = false;
                }

                if (!array_key_exists('button_title', $slide)) {
                    $slide['button_title'] = '';
                }
            }

            $value->slides = $slides;
            $value->visible = $request->has('visible');
        } elseif ($key === 'top_categories_grid' || $key === 'top_categories_linear') {
            $value->visible = $request->has('visible');
            $value->categories = $request->input('categories', []);
        } elseif ($key === 'top_selling_products') {
            $value->visible = $request->has('visible');
            $value->products = $request->input('products', []);
        } elseif ($key === 'footer') {
            $value = $request->input('footer_content');
        }

        $setting->update(['value' => $value]);

        return redirect()->route('home-page-settings.index')->with('success', 'Settings updated successfully.');
    }
}
