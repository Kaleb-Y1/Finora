<?php

namespace Tests\Feature\Ledger;

use App\Models\Book;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MultiLedgerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $userA;
    protected User $userB;
    protected Book $bookA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userA = User::factory()->create([
            'name' => 'User A',
            'username' => 'usera_' . uniqid(),
            'email' => 'usera_' . uniqid() . '@example.com',
            'role' => 'umum',
            'status' => 'active',
        ]);

        $this->userB = User::factory()->create([
            'name' => 'User B',
            'username' => 'userb_' . uniqid(),
            'email' => 'userb_' . uniqid() . '@example.com',
            'role' => 'umum',
            'status' => 'active',
        ]);

        $this->bookA = Book::create([
            'name' => 'Buku Kas User A',
            'description' => 'Deskripsi Buku A',
            'owner_id' => $this->userA->id,
            'opening_balance' => 5000000.00,
            'opening_balance_date' => '2026-01-01',
            'status' => 'active',
        ]);

        $this->bookA->members()->attach($this->userA->id, [
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    public function test_user_can_view_books_list(): void
    {
        $response = $this->actingAs($this->userA)->get('/books');

        $response->assertStatus(200);
        $response->assertSee('Buku Kas User A');
        $response->assertSee('5.000.000');
    }

    public function test_user_can_create_new_book_with_opening_balance_and_default_categories(): void
    {
        $response = $this->actingAs($this->userA)->post('/books', [
            'name' => 'Buku Usaha Baru',
            'description' => 'Kas Toko Kelontong',
            'opening_balance' => 12500000.50,
            'opening_balance_date' => '2026-02-01',
        ]);

        $response->assertRedirect('/books');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('books', [
            'name' => 'Buku Usaha Baru',
            'owner_id' => $this->userA->id,
            'opening_balance' => 12500000.50,
        ]);

        $newBook = Book::where('name', 'Buku Usaha Baru')->first();
        $this->assertNotNull($newBook);

        // Pastikan owner terhubung di pivot dengan role owner
        $this->assertTrue($newBook->members()->where('users.id', $this->userA->id)->wherePivot('role', 'owner')->exists());

        // Pastikan 10 kategori standar otomatis terbuat
        $this->assertGreaterThanOrEqual(10, $newBook->categories()->count());
    }

    public function test_user_can_switch_active_book(): void
    {
        $secondBook = Book::create([
            'name' => 'Buku Kedua',
            'owner_id' => $this->userA->id,
            'opening_balance' => 1000000,
            'opening_balance_date' => '2026-01-01',
        ]);
        $secondBook->members()->attach($this->userA->id, ['role' => 'owner', 'status' => 'active']);

        $response = $this->actingAs($this->userA)->post('/ledger/switch', [
            'book_id' => $secondBook->id,
        ]);

        $response->assertSessionHas('active_book_id', $secondBook->id);
    }

    public function test_user_cannot_switch_to_unauthorized_book_anti_idor(): void
    {
        // User B mencoba switch ke buku milik User A yang bukan anggotanya
        $response = $this->actingAs($this->userB)->post('/ledger/switch', [
            'book_id' => $this->bookA->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertNotEquals($this->bookA->id, session('active_book_id'));
    }

    public function test_book_owner_can_add_member(): void
    {
        $response = $this->actingAs($this->userA)->post("/books/{$this->bookA->id}/members", [
            'identifier' => $this->userB->username,
            'role' => 'member',
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue($this->bookA->members()->where('users.id', $this->userB->id)->exists());
    }

    public function test_book_owner_can_change_member_role(): void
    {
        $this->bookA->members()->attach($this->userB->id, ['role' => 'member', 'status' => 'active']);

        $response = $this->actingAs($this->userA)->put("/books/{$this->bookA->id}/members/{$this->userB->id}", [
            'role' => 'admin',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('admin', $this->bookA->members()->where('users.id', $this->userB->id)->first()->pivot->role);
    }

    public function test_book_owner_can_remove_member_and_transactions_remain_intact(): void
    {
        $this->bookA->members()->attach($this->userB->id, ['role' => 'member', 'status' => 'active']);

        $cat = Category::create([
            'book_id' => $this->bookA->id,
            'name' => 'Pendapatan B',
            'type' => 'income',
        ]);

        // Transaksi dicatat oleh user B
        $trx = Transaction::create([
            'book_id' => $this->bookA->id,
            'category_id' => $cat->id,
            'created_by' => $this->userB->id,
            'type' => 'income',
            'title' => 'Setoran Tunai B',
            'amount' => 500000.00,
            'transaction_date' => '2026-02-10',
        ]);

        // Hapus keanggotaan user B
        $response = $this->actingAs($this->userA)->delete("/books/{$this->bookA->id}/members/{$this->userB->id}");

        $response->assertSessionHas('success');
        $this->assertFalse($this->bookA->members()->where('users.id', $this->userB->id)->exists());

        // Verifikasi bahwa data transaksi milik user B TIDAK terhapus
        $this->assertDatabaseHas('transactions', [
            'id' => $trx->id,
            'created_by' => $this->userB->id,
            'amount' => 500000.00,
        ]);
    }

    public function test_non_admin_member_cannot_manage_members(): void
    {
        $this->bookA->members()->attach($this->userB->id, ['role' => 'member', 'status' => 'active']);

        $userC = User::factory()->create(['status' => 'active']);

        // User B (role: member) mencoba menambahkan User C
        $response = $this->actingAs($this->userB)->post("/books/{$this->bookA->id}/members", [
            'identifier' => $userC->username,
            'role' => 'member',
        ]);

        $response->assertStatus(403);
    }

    public function test_owner_can_transfer_ownership(): void
    {
        $this->bookA->members()->attach($this->userB->id, ['role' => 'member', 'status' => 'active']);

        $response = $this->actingAs($this->userA)->post("/books/{$this->bookA->id}/members/transfer-ownership", [
            'new_owner_id' => $this->userB->id,
        ]);

        $response->assertSessionHas('success');

        // Refresh book
        $this->bookA->refresh();
        $this->assertEquals($this->userB->id, $this->bookA->owner_id);

        // Verifikasi peran di pivot
        $this->assertEquals('owner', $this->bookA->members()->where('users.id', $this->userB->id)->first()->pivot->role);
        $this->assertEquals('admin', $this->bookA->members()->where('users.id', $this->userA->id)->first()->pivot->role);
    }

    public function test_unauthorized_user_cannot_access_book_members_page(): void
    {
        // User B yang bukan anggota mencoba mengakses daftar anggota Buku A
        $response = $this->actingAs($this->userB)->get("/books/{$this->bookA->id}/members");

        $response->assertStatus(403);
    }
}
