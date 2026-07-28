<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],

            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers(),],

            'team_name' => ['required', 'string', 'max:255'],

            'npsn' => ['nullable', 'string', 'max:50'],

            'jenjang' => [
                'nullable',
                Rule::in(['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']),
            ],

            'alamat' => ['nullable', 'string'],

            'no_telp' => ['nullable', 'string', 'max:30'],

            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature()
                ? ['required', 'accepted']
                : ['nullable'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'no_hp' => $input['no_telp'] ?? null,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Bikin Pangkalan (Team) milik user ini
            $team = $user->ownedTeams()->create([
                'name' => $input['team_name'],
                'npsn' => $input['npsn'] ?? null,
                'jenjang' => $input['jenjang'] ?? null,
                'alamat' => $input['alamat'] ?? null,
                'no_telp' => $input['no_telp'] ?? null,
                'personal_team' => false,
            ]);

            // WAJIB: set team aktif, biar layout Jetstream nggak crash
            $user->forceFill([
                'current_team_id' => $team->id,
            ])->save();

            $user->assignRole('operator-sekolah');

            return $user;
        });
    }
}