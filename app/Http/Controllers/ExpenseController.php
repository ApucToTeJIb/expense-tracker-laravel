<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    //
    public function index()
    {
        $expenses = Expense::orderBy('spent_at', 'desc')->get();
        return view('expenses.index', compact('expenses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'amount' => 'required|numeric',
            'category' => 'required',
            'spent_at' => 'required|date',
        ]);

        Expense::create($request->all());

        return redirect()->back()->with('success', 'Расход добавлен!');
    }
}
