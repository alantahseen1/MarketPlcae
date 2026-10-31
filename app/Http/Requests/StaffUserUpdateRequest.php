<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StaffUserUpdateRequest extends FormRequest
{
    /**
     * Resolved target staff user.
     */
    protected ?User $targetUser = null;

    /**
     * Get the target staff user.
     */
    public function getTargetUser(): User
    {
        if ($this->targetUser === null) {
            $param = $this->route('id') ?? $this->route('user');
            $this->targetUser = $param instanceof User
                ? $param
                : User::findOrFail($param);
        }

        return $this->targetUser;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('staff')->check() && auth('staff')->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $targetUser = $this->getTargetUser();

        return [
            'full_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('user', 'email')->ignore($targetUser->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:admin,subadmin'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $targetUser = $this->getTargetUser();
            $currentStaff = auth('staff')->user();

            if ($currentStaff && $targetUser->id === $currentStaff->id) {
                // Admin cannot deactivate themselves or change their own role
                if ($this->input('role') !== 'admin') {
                    $v->errors()->add('role', __('staff.cannot_deactivate_self'));
                }

                if (! $this->boolean('is_active', true)) {
                    $v->errors()->add('is_active', __('staff.cannot_deactivate_self'));
                }
            }
        });
    }
}
