<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffUserStoreRequest;
use App\Http\Requests\StaffUserUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffUserController extends Controller
{
    /**
     * Display a listing of staff accounts.
     */
    public function index(): View
    {
        $users = User::latest('id')->paginate(15);

        return view('staff.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new staff account.
     */
    public function create(): View
    {
        return view('staff.users.form', ['user' => null]);
    }

    /**
     * Store a newly created staff account in storage.
     */
    public function store(StaffUserStoreRequest $request): RedirectResponse
    {
        User::create([
            'full_name' => $request->input('full_name'),
            'email' => $request->input('email'),
            'password_hash' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('staff.users.index')
            ->with('success', __('staff.staff_created'));
    }

    /**
     * Show the form for editing the specified staff account.
     */
    public function edit(int $id): View
    {
        $user = User::findOrFail($id);

        return view('staff.users.form', compact('user'));
    }

    /**
     * Update the specified staff account in storage.
     */
    public function update(StaffUserUpdateRequest $request, int $id): RedirectResponse
    {
        $targetUser = $request->getTargetUser();

        $data = [
            'full_name' => $request->input('full_name'),
            'email' => $request->input('email'),
            'role' => $request->input('role'),
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $data['password_hash'] = Hash::make($request->input('password'));
        }

        $targetUser->update($data);

        return redirect()->route('staff.users.index')
            ->with('success', __('staff.staff_updated'));
    }
}
