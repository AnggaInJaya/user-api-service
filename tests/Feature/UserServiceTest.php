<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Services\UserService;
use App\Exceptions\Domain\ForbiddenOperationException;
use App\Exceptions\Domain\DuplicateEmailException;
use App\Exceptions\Domain\EmailDispatchException;
use App\Exceptions\Domain\EntityNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        // Inisialisasi service
        $this->userService = new UserService();
        
        // Mencegah pengiriman email asli selama testing
        Mail::fake(); 
    }

    /**
     * TEST: Pembuatan User Berhasil (Role Admin/Manager)
     */
    public function test_create_user_successfully_as_administrator()
    {
        $admin = User::factory()->create(['role' => 'Administrator']);
        
        $data = [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'role' => 'User',
            'active' => true
        ];

        $result = $this->userService->createUser($data, $admin);

        // Assertions
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('budi@example.com', $result->email);
        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'name' => 'Budi Santoso'
        ]);
    }

    /**
     * TEST: Gagal membuat user karena role tidak diizinkan
     */
    public function test_create_user_fails_for_regular_user_role()
    {
        $regularUser = User::factory()->create(['role' => 'User']);
        
        $this->expectException(ForbiddenOperationException::class);
        $this->expectExceptionMessage('Not allowed to create users.');

        $this->userService->createUser([
            'name' => 'Target',
            'email' => 'target@example.com',
            'password' => 'password123'
        ], $regularUser);
    }

    /**
     * TEST: Gagal membuat user karena email duplikat
     */
    public function test_create_user_fails_due_to_duplicate_email()
    {
        $admin = User::factory()->create(['role' => 'Administrator']);
        User::factory()->create(['email' => 'sama@example.com']);

        $this->expectException(DuplicateEmailException::class);
        $this->expectExceptionMessage('Email already exists.');

        $this->userService->createUser([
            'name' => 'User Baru',
            'email' => 'sama@example.com',
            'password' => 'password123'
        ], $admin);
    }

    /**
     * TEST: Menangani error saat pengiriman email welcome gagal
     */
    public function test_create_user_throws_email_exception_on_mail_failure()
    {
        $admin = User::factory()->create(['role' => 'Administrator']);
        
        // Simulasi Mail melempar error
        Mail::shouldReceive('raw')->andThrow(new \Exception('SMTP Connection Error'));

        $this->expectException(EmailDispatchException::class);

        $this->userService->createUser([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'password123'
        ], $admin);
    }

    /**
     * TEST: Update user oleh diri sendiri (Berhasil)
     */
    public function test_update_own_profile_successfully()
    {
        $user = User::factory()->create(['name' => 'Nama Lama', 'role' => 'User']);
        
        $updatedData = ['name' => 'Nama Baru'];
        $result = $this->userService->updateUser($user->id, $updatedData, $user);

        $this->assertEquals('Nama Baru', $result->name);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru'
        ]);
    }

    /**
     * TEST: Manager dilarang mengedit Administrator
     */
    public function test_manager_cannot_edit_administrator()
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $admin = User::factory()->create(['role' => 'Administrator']);

        $this->expectException(ForbiddenOperationException::class);
        $this->expectExceptionMessage('Not allowed to edit this user.');

        $this->userService->updateUser($admin->id, ['name' => 'Vandal'], $manager);
    }

    /**
     * TEST: User tidak ditemukan saat update
     */
    public function test_update_fails_if_user_not_found()
    {
        $admin = User::factory()->create(['role' => 'Administrator']);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('User not found.');

        $this->userService->updateUser(999, ['name' => 'Ghost'], $admin);
    }
}