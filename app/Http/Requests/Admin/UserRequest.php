<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return Auth::user()->can('create user') || Auth::user()->can('update user');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users,email,' . $userId,
            ],
            'role' => 'required',
            'password' => [
                $this->isMethod('POST') ? 'required' : 'nullable',
                'string',
                'min:8',
            ],
            'confirm_password' => [
                $this->isMethod('POST') ? 'required' : 'nullable',
                'same:password',
            ],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = data_get($this->validator->validated(), $key, $default);
        return data_forget($data, 'confirm_password');
    }


    public function handle(User $user = null)
    {
        if ($user) {
            $this->update($user, $this->validated());
        } else {
            $user = $this->store($this->validated());
        }
        return $user;
    }

    public function update($user, array $data)
    {
        DB::transaction(function () use ($user, $data) {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'] ? $data['password'] : $user->password,
            ]);
            $user->syncRoles($data['role']);
            return $user;
        });
    }

    public function store(array $data)
    {
        DB::transaction(function () use ($data) {
            $user = User::create($data);
            $user->assignRole($data['role']);
            return $user;
        });
    }
}
