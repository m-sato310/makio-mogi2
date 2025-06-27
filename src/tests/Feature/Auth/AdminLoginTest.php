<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_login_validation_error_when_email_is_empty()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass123'),
            'is_admin' => true,
        ]);

        $data = [
            'email' => '',
            'password' => 'adminpass123',
        ];

        $response = $this->post('/admin/login', $data);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
    }

    public function test_admin_login_validation_error_when_password_is_empty()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass123'),
            'is_admin' => true,
        ]);

        $data = [
            'email' => 'admin@example.com',
            'password' => '',
        ];

        $response = $this->post('/admin/login', $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
    }

    public function test_admin_login_validation_error_when_credentials_are_wrong()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass123'),
            'is_admin' => true,
        ]);

        $data = [
            'email' => 'wrong_admin@example.com',
            'password' => 'adminpass123',
        ];

        $response = $this->post('/admin/login', $data);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
    }
}
