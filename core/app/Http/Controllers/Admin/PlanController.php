<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Referral;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $pageTitle = 'Subscription Plan';
        $levels = Referral::max('level');
        $plans = Plan::get();
        return view('admin.plan',compact('pageTitle','levels','plans'));
    }

    public function savePlan(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric|min:0',
            'daily_limit' => 'required|numeric|min:1',
            'ptc_view_amount' => 'required|numeric|min:0',
            'ref_level' => 'required|numeric|min:0',
            'validity' => 'required|min:0',
            'anytime_withdraw_limit' => 'required|numeric|min:0|max:100',
            'weekly_withdraw_day' => 'required|numeric|min:0|max:6',
            'package_number' => 'required|numeric|min:0|max:255',
            'image' => 'nullable|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        if($request->id == 0){
            $plan = new Plan();
        }else{
            $plan = Plan::findOrFail($request->id);
        }
        $plan->name = $request->name;
        $plan->price = $request->price;
        $plan->daily_limit = $request->daily_limit;
        $plan->ptc_view_amount = $request->ptc_view_amount;
        $plan->ref_level = $request->ref_level;
        $plan->validity = $request->validity;
        $plan->anytime_withdraw_limit = $request->anytime_withdraw_limit;
        $plan->weekly_withdraw_day = $request->weekly_withdraw_day;
        $plan->weekly_withdraw_enabled = isset($request->weekly_withdraw_enabled) ? 1 : 0;
        $plan->package_number = $request->package_number;
        $plan->is_premium_package = isset($request->is_premium_package) ? 1 : 0;
        $plan->status = isset($request->status) ? 1:0;

        if ($request->hasFile('image')) {
            $path = 'assets/images/plan/';
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            if ($plan->image && file_exists($path . $plan->image)) {
                unlink($path . $plan->image);
            }
            $filename = uniqid() . '_' . time() . '.' . $request->image->getClientOriginalExtension();
            $request->image->move($path, $filename);
            $plan->image = $filename;
        }

        $plan->save();

        $notify[] = ['success', 'Plan has been Updated Successfully.'];
        return back()->withNotify($notify);
    }
}
