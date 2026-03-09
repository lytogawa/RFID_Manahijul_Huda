// app/Http/Controllers/Admin/AdminController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Models\Class as ClassModel;
use App\Models\Transaction;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalStudents = User::where('role', 'student')->count();
        $totalParents = User::where('role', 'parent')->count();
        $totalTransactions = Transaction::count();
        $totalCredits = Account::sum('credits');

        return view('admin.dashboard', compact(
            'totalStudents', 'totalParents', 'totalTransactions', 'totalCredits'
        ));
    }

    public function loadCredits(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $user = User::findOrFail($request->user_id);
        $account = $user->account;

        if ($account) {
            $account->addCredits($request->amount);
            Transaction::create([
                'account_id' => $account->id,
                'amount' => $request->amount,
                'description' => 'Credit loaded by admin',
                'type' => 'credit',
                'location' => 'Admin Portal',
            ]);
        }

        return back()->with('success', 'Credits loaded successfully');
    }

    public function addUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:student,parent',
            'rfid_uid' => 'nullable|unique:users,rfid_uid',
            'phone' => 'nullable|string|max:20',
            'parent_id' => 'nullable|exists:users,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'rfid_uid' => $request->rfid_uid,
            'phone' => $request->phone,
            'parent_id' => $request->parent_id,
        ]);

        if ($request->role === 'student') {
            Account::create(['user_id' => $user->id]);
        }

        return back()->with('success', 'User added successfully');
    }

    public function addClass(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'teacher' => 'nullable|string|max:255',
            'schedule' => 'nullable|string|max:255',
            'room' => 'nullable|string|max:255',
        ]);

        ClassModel::create($request->all());

        return back()->with('success', 'Class added successfully');
    }

    public function reports()
    {
        $transactions = Transaction::with('account.user')->latest()->paginate(20);
        $attendances = Attendance::with('user', 'class')->latest()->paginate(20);

        return view('admin.reports', compact('transactions', 'attendances'));
    }
}
