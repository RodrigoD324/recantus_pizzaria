<?php

namespace App\Models;

use App\Helpers\Error;
use Illuminate\Support\Facades\Hash;

class Login
{
    public function login(array $data): array|User
    {
        try {
            $user = User::query()->where('login', $data['cpf'])->first();
            if (!$user) throw new \Exception("UserNotExist");
            $passwordMatch = Hash::check($data['password'], $user['password']);
            if (!$passwordMatch) throw new \Exception("PasswordNotMatch");
        } catch (\Exception $e) {
            return Error::defineReturnMessageAndCode($e->getMessage());
        }

        return $user;
    }

    private function createUser(array $data): User
    {
        return User::create([
            'email'         =>  $data['email'],
            'password'      =>  isset($data['password']) ? Hash::make($data['password']) : null,
            'google_sub'    =>  $data['sub'] ?? null,
            'name'          =>  $data['name'],
            'picture_url'   =>  $data['picture'] ?? null
        ]);
    }
}
