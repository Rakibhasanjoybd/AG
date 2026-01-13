<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class ManageFaqController extends Controller
{
    public function index()
    {
        $pageTitle = 'Manage FAQs';
        $faqs = Faq::ordered()->paginate(getPaginate());
        return view('admin.faq.index', compact('pageTitle', 'faqs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'order' => 'required|integer|min:0',
        ]);

        Faq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'order' => $request->order,
            'status' => $request->status ? 1 : 0,
        ]);

        $notify[] = ['success', 'FAQ created successfully'];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'order' => 'required|integer|min:0',
        ]);

        $faq = Faq::findOrFail($id);
        $faq->question = $request->question;
        $faq->answer = $request->answer;
        $faq->order = $request->order;
        $faq->status = $request->status ? 1 : 0;
        $faq->save();

        $notify[] = ['success', 'FAQ updated successfully'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        Faq::findOrFail($id)->delete();
        $notify[] = ['success', 'FAQ deleted successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->status = !$faq->status;
        $faq->save();

        $notify[] = ['success', 'Status updated successfully'];
        return back()->withNotify($notify);
    }
}
