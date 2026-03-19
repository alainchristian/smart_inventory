<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ChangePassword extends Component
{
    public string $password              = '';
    public string $password_confirmation = '';
    public bool   $show                  = false;
    public bool   $show_confirm          = false;

    public function save(): void
    {
        $this->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'Passwords do not match.',
            'password.min'       => 'Password must be at least 8 characters.',
        ]);

        $user = auth()->user();

        $user->update([
            'password'             => Hash::make($this->password),
            'must_change_password' => false,
        ]);

        $this->redirect($user->getDashboardRoute(), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.change-password');
    }
}
