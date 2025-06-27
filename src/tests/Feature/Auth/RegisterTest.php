<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_register_validation_error_when_name_is_empty()
    {
        $data = [
            'name' => '',
            'email' => 'test@example',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください'
        ]);
    }

    public function test_register_validation_error_when_email_is_empty()
    {
        $data = [
            'name' => '山田太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
    }

    public function test_register_validation_error_when_password_is_less_than_8_characters()
    {
        $data = [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください'
        ]);
    }

    public function test_register_validation_error_when_password_confirmation_does_not_match()
    {
        $data = [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません'
        ]);
    }

    public function test_register_validation_error_when_password_is_empty()
    {
        $data = [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
    }

    public function test_register_successfully_saves_user_data()
    {
        $data = [
            'name' => '山田太郎',
            'email' => 'test_success@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);

        $response->assertRedirect('/verify-email');

        $this->assertDatabaseHas('users', [
            'name' => '山田太郎',
            'email' => 'test_success@example.com',
        ]);
    }
}
