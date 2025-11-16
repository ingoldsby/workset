<?php

use App\Models\User;

describe('Disabled Registration', function () {
    it('redirects registration page to login with message', function () {
        $response = $this->get(route('register'));

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('info');
    });

    it('prevents direct user registration', function () {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('info');

        expect(User::where('email', 'test@example.com')->exists())->toBeFalse();
    });
});
