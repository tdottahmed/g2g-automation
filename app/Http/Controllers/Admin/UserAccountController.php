<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class UserAccountController extends Controller
{
    /**
     * Display a listing of user accounts.
     */
    public function index()
    {
        $userAccounts = UserAccount::latest()->get();
        return view('admin.user-accounts.index', compact('userAccounts'));
    }

    /**
     * Show the form for creating a new user account.
     */
    public function create()
    {
        return view('admin.user-accounts.create');
    }

    /**
     * Store a newly created user account in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email'      => 'required|email|unique:user_accounts,email',
            'owner_name' => 'required|string|max:255',
            'password'   => 'required|string|min:6',
        ]);

        try {
            UserAccount::create([
                'email'      => $request->email,
                'owner_name' => $request->owner_name,
                'password'   => Crypt::encryptString($request->password),
            ]);

            return redirect()
                ->route('user-accounts.index')
                ->with('success', 'User account created successfully.');
        } catch (\Throwable $th) {
            logger()->error('Error creating user account: ' . $th->getMessage());
            return back()
                ->with('error', 'An error occurred while creating the user account. Please try again.')
                ->withInput();
        }
    }


    /**
     * Show the form for editing the specified user account.
     */
    public function edit(UserAccount $userAccount)
    {
        return view('admin.user-accounts.edit', compact('userAccount'));
    }

    /**
     * Update the specified user account in storage.
     */
    public function update(Request $request, UserAccount $userAccount)
    {
        $request->validate([
            'email'      => 'required|email|unique:user_accounts,email,' . $userAccount->id,
            'owner_name' => 'required|string|max:255',
            'password'   => 'nullable|string|min:6',
        ]);

        try {
            $data = [
                'email'      => $request->email,
                'owner_name' => $request->owner_name,
            ];

            if ($request->filled('password')) {
                $data['password'] = $request->password;
            }

            $userAccount->update($data);

            return redirect()
                ->route('user-accounts.index')
                ->with('success', 'User account updated successfully.');
        } catch (\Throwable $th) {
            logger()->error('Error updating user account: ' . $th->getMessage());

            return back()
                ->withErrors(['error' => 'An error occurred while updating the user account. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified user account from storage.
     */
    public function destroy(UserAccount $userAccount)
    {
        $userAccount->delete();
        return redirect()->route('user-accounts.index')->with('success', 'User account deleted successfully.');
    }
}
