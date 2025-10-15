<?php

namespace App\Http\Requests\Admin;

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class PermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->can('create permission') || Auth::user()->can('update permission');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $permissionId = $this->route('permission')?->id;
        return [
            'name' => 'required|max:255|unique:permissions,name,' . $permissionId,
            'guard_name' => 'nullable|max:255',
            'group' => 'required|max:255',
        ];
    }

    public function handle($permission = null)
    {
        if ($permission) {
            $this->update($permission, $this->validated());
        } else {
            $permission = $this->store($this->validated());
        }
        return $permission;
    }

    public function store($data)
    {
        DB::transaction(function () use ($data) {
            return Permission::create($data);
        });
    }

    public function update(Permission $permission, $data)
    {
        DB::transaction(function () use ($permission, $data) {
            $permission->update($data);
        });
    }
}
