<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Admin\UserRequest;

class UserController extends Controller
{
    public function __construct()
    {
        $permissions = [
            'view user' => ['index'],
            'create user' => ['create', 'store'],
            'update user' => ['edit', 'update'],
            'delete user' => ['destroy'],
        ];

        foreach ($permissions as $permission => $actions) {
            $this->middleware("permission:{$permission}", ['only' => $actions]);
        }
    }

    public function index()
    {
        $users = User::all();
        return view('admin.user.index', compact('users'));
    }

    public function create()
    {
        return view('admin.user.create', ['roles' => $this->getRoles()]);
    }

    public function store(UserRequest $request)
    {
        try {
            $request->handle();
            return $this->redirectWithSuccess('User created successfully');
        } catch (\Throwable $th) {
            Log::error('User creation failed: ' . $th->getMessage());
            return $this->redirectWithError('Something went wrong. Please try again.');
        }
    }

    public function show(User $user)
    {
        return view('admin.user.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.user.edit', [
            'user' => $user,
            'roles' => $this->getRoles(),
        ]);
    }

    public function update(UserRequest $request, User $user)
    {
        try {
            $request->handle($user);
            return $this->redirectWithSuccess('User updated successfully');
        } catch (\Throwable $th) {
            Log::error('User update failed: ' . $th->getMessage());
            return $this->redirectWithError('Something went wrong. Please try again.');
        }
    }

    /**
     * Delete the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        try {
            $user->syncRoles([]);
            $user->delete();
            return $this->redirectWithSuccess('User deleted successfully');
        } catch (\Throwable $th) {
            Log::error('User deletion failed: ' . $th->getMessage());
            return $this->redirectWithError('Something went wrong. Please try again.');
        }
    }

    private function getRoles()
    {
        return Role::all();
    }

    /**
     * Redirect to the user list page with a success message.
     *
     * @param  string  $message
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectWithSuccess($message)
    {
        return redirect()->route('users.index')->with('success', $message);
    }

    /**
     * Redirect back to the previous page with an error message.
     *
     * @param  string  $message
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectWithError($message)
    {
        return back()->with('error', $message);
    }
}
