<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgcoBrand;
use Illuminate\Http\Request;

class ManageAgcoBrandController extends Controller
{
    public function index()
    {
        $pageTitle = 'Manage AGCO Family Brands';
        $brands = AgcoBrand::ordered()->paginate(getPaginate());
        return view('admin.agco_brand.index', compact('pageTitle', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'order' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $brand = new AgcoBrand();
        $brand->name = $request->name;
        $brand->url = $request->url;
        $brand->order = $request->order;
        $brand->status = $request->status ? 1 : 0;

        if ($request->hasFile('image')) {
            $brand->image = fileUploader($request->image, getFilePath('brand'), getFileSize('brand'));
        }

        $brand->save();

        $notify[] = ['success', 'Brand created successfully'];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'order' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $brand = AgcoBrand::findOrFail($id);
        $brand->name = $request->name;
        $brand->url = $request->url;
        $brand->order = $request->order;
        $brand->status = $request->status ? 1 : 0;

        if ($request->hasFile('image')) {
            $old = $brand->image;
            $brand->image = fileUploader($request->image, getFilePath('brand'), getFileSize('brand'), $old);
        }

        $brand->save();

        $notify[] = ['success', 'Brand updated successfully'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $brand = AgcoBrand::findOrFail($id);
        fileManager()->removeFile(getFilePath('brand') . '/' . $brand->image);
        $brand->delete();

        $notify[] = ['success', 'Brand deleted successfully'];
        return back()->withNotify($notify);
    }
}
