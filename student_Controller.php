// app/Http/Controllers/Student/StudentController.php
<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Attendance;
use App\Models\Class as ClassModel;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function dashboard()
    {
        $account = $this->user->account;
        $recentTransactions = Transaction::whereHas('account', function($q) {
            $q->where('user_id', $this->user->id);
        })->latest()->take(5)->get();

        return view('student.dashboard', compact('account', 'recentTransactions'));
    }

    public function checkBalance()
    {
        $account = $this->user->account;
        return view('student.balance', compact('account'));
    }

    public function makeTransaction(Request $request)
    {
        $request->validate([
            'rfid_uid' => 'required|exists:users,rfid_uid',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $user = User::where('rfid_uid', $request->rfid_uid)->first();

        if (!$user || $user->id !== $this->user->id) {
            return back()->with('error', 'Invalid RFID UID');
        }

        $account = $this->user->account;

        if ($account->deductCredits($request->amount)) {
            Transaction::create([
                'account_id' => $account->id,
                'amount' => $request->amount,
                'description' => $request->description ?? 'Purchase',
                'type' => 'debit',
                'location' => $request->location ?? 'School',
            ]);

            return back()->with('success', 'Transaction successful');
        }

        return back()->with('error', 'Insufficient credits');
    }

    public function markAttendance(Request $request)
    {
        $request->validate([
            'rfid_uid' => 'required|exists:users,rfid_uid',
            'class_id' => 'required|exists:classes,id',
        ]);

        $user = User::where('rfid_uid', $request->rfid_uid)->first();

        if (!$user || $user->id !== $this->user->id) {
            return back()->with('error', 'Invalid RFID UID');
