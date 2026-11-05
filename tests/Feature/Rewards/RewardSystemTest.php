<?php

namespace Tests\Feature\Rewards;

use App\Models\PointTransaction;
use App\Models\Product;
use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RewardPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RewardSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_student_can_view_rewards_dashboard(): void
    {
        $user = User::factory()->create([
            'points_balance' => 150,
            'points_lifetime' => 200,
        ]);

        $reward = RewardItem::factory()->create([
            'title' => 'Voucher Diskon Belanja Rp 10.000',
            'points_cost' => 50,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('rewards.index'));

        $response->assertStatus(200);
        $response->assertSee('Poin & Reward Mahasiswa');
        $response->assertSee('150');
        $response->assertSee('Mahasiswa Aktif'); // Tier for 200 lifetime points
        $response->assertSee('Voucher Diskon Belanja Rp 10.000');
    }

    public function test_guest_is_redirected_to_login_when_accessing_rewards(): void
    {
        $response = $this->get(route('rewards.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_can_perform_daily_checkin_once_per_day(): void
    {
        $user = User::factory()->create([
            'points_balance' => 0,
            'points_lifetime' => 0,
            'last_check_in_at' => null,
        ]);

        // First check-in
        $response = $this->actingAs($user)->post(route('rewards.checkin'));

        $response->assertSessionHas('status');
        $this->assertEquals(RewardPointService::POINTS_DAILY_CHECKIN, $user->fresh()->points_balance);
        $this->assertNotNull($user->fresh()->last_check_in_at);

        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $user->id,
            'amount' => 10,
            'type' => PointTransaction::TYPE_DAILY_CHECK_IN,
        ]);

        // Second check-in on the same day should fail
        $responseSecond = $this->actingAs($user->fresh())->post(route('rewards.checkin'));

        $responseSecond->assertSessionHas('error');
        $this->assertEquals(10, $user->fresh()->points_balance);
    }

    public function test_student_can_redeem_reward_item_when_points_are_sufficient(): void
    {
        $user = User::factory()->create([
            'points_balance' => 100,
            'points_lifetime' => 100,
        ]);

        $reward = RewardItem::factory()->create([
            'title' => 'Voucher Diskon Belanja Rp 15.000',
            'points_cost' => 60,
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('rewards.redeem', $reward));

        $response->assertSessionHas('status');
        $this->assertEquals(40, $user->fresh()->points_balance);
        $this->assertEquals(9, $reward->fresh()->stock);

        $this->assertDatabaseHas('reward_redemptions', [
            'user_id' => $user->id,
            'reward_item_id' => $reward->id,
            'points_spent' => 60,
            'status' => RewardRedemption::STATUS_ACTIVE,
        ]);

        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $user->id,
            'amount' => -60,
            'type' => PointTransaction::TYPE_REWARD_REDEMPTION,
        ]);
    }

    public function test_student_cannot_redeem_reward_item_when_points_are_insufficient(): void
    {
        $user = User::factory()->create([
            'points_balance' => 20,
            'points_lifetime' => 20,
        ]);

        $reward = RewardItem::factory()->create([
            'title' => 'Tote Bag Eksklusif',
            'points_cost' => 150,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('rewards.redeem', $reward));

        $response->assertSessionHas('error');
        $this->assertEquals(20, $user->fresh()->points_balance);
        $this->assertDatabaseMissing('reward_redemptions', [
            'user_id' => $user->id,
            'reward_item_id' => $reward->id,
        ]);
    }

    public function test_student_can_view_points_ledger_history(): void
    {
        $user = User::factory()->create(['points_balance' => 50]);

        PointTransaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 50,
            'type' => PointTransaction::TYPE_TRANSACTION_COMPLETED,
            'description' => 'Reward Transaksi Selesai #TRX-999',
        ]);

        $response = $this->actingAs($user)->get(route('rewards.history'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Poin Mahasiswa');
        $response->assertSee('Reward Transaksi Selesai #TRX-999');
        $response->assertSee('+50');
    }

    public function test_admin_can_access_rewards_management_and_create_reward_item(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $responseIndex = $this->actingAs($admin)->get(route('admin.rewards.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Kelola Reward & Poin');

        $responseCreate = $this->actingAs($admin)->post(route('admin.rewards.store'), [
            'title' => 'Voucher Diskon Spesial Ujian Rp 20.000',
            'description' => 'Potongan harga belanja perlengkapan ujian.',
            'points_cost' => 80,
            'reward_type' => RewardItem::TYPE_DISCOUNT_FIXED,
            'discount_amount' => 20000,
            'stock' => 50,
            'validity_days' => 14,
            'is_active' => 1,
        ]);

        $responseCreate->assertRedirect(route('admin.rewards.index'));

        $this->assertDatabaseHas('reward_items', [
            'title' => 'Voucher Diskon Spesial Ujian Rp 20.000',
            'points_cost' => 80,
            'discount_amount' => 20000,
            'stock' => 50,
            'is_active' => true,
        ]);
    }

    public function test_non_admin_cannot_access_admin_rewards_management(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('admin.rewards.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_update_toggle_and_delete_reward_item(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $reward = RewardItem::factory()->create([
            'title' => 'Old Title',
            'is_active' => true,
        ]);

        // Update
        $this->actingAs($admin)->put(route('admin.rewards.update', $reward), [
            'title' => 'Updated Reward Title',
            'points_cost' => 99,
            'reward_type' => RewardItem::TYPE_FREE_SHIPPING,
            'stock' => -1,
            'validity_days' => 30,
            'is_active' => 1,
        ]);

        $this->assertEquals('Updated Reward Title', $reward->fresh()->title);

        // Toggle
        $this->actingAs($admin)->post(route('admin.rewards.toggle', $reward));
        $this->assertFalse($reward->fresh()->is_active);

        // Delete
        $this->actingAs($admin)->delete(route('admin.rewards.destroy', $reward));
        $this->assertDatabaseMissing('reward_items', ['id' => $reward->id]);
    }

    public function test_admin_can_adjust_user_points_manually_with_audit_reason(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create([
            'points_balance' => 50,
            'points_lifetime' => 50,
        ]);

        // Add bonus points
        $responseAdd = $this->actingAs($admin)->post(route('admin.rewards.adjust', $student), [
            'action' => 'add',
            'points' => 75,
            'reason' => 'Bonus Duta Kampus Berprestasi',
        ]);

        $responseAdd->assertSessionHas('status');
        $this->assertEquals(125, $student->fresh()->points_balance);
        $this->assertEquals(125, $student->fresh()->points_lifetime);

        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $student->id,
            'amount' => 75,
            'type' => PointTransaction::TYPE_ADMIN_ADJUSTMENT,
            'created_by_admin_id' => $admin->id,
        ]);

        // Deduct points
        $responseDeduct = $this->actingAs($admin)->post(route('admin.rewards.adjust', $student), [
            'action' => 'deduct',
            'points' => 25,
            'reason' => 'Koreksi poin duplikat',
        ]);

        $responseDeduct->assertSessionHas('status');
        $this->assertEquals(100, $student->fresh()->points_balance);
    }

    public function test_admin_can_mark_reward_redemption_as_fulfilled(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create();
        $reward = RewardItem::factory()->create();

        $redemption = RewardRedemption::factory()->create([
            'user_id' => $student->id,
            'reward_item_id' => $reward->id,
            'status' => RewardRedemption::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.rewards.redemptions.fulfill', $redemption), [
            'notes' => 'Tote bag diserahkan langsung di sekretariat kampus.',
        ]);

        $response->assertSessionHas('status');
        $this->assertEquals(RewardRedemption::STATUS_FULFILLED, $redemption->fresh()->status);
        $this->assertNotNull($redemption->fresh()->used_at);
        $this->assertEquals('Tote bag diserahkan langsung di sekretariat kampus.', $redemption->fresh()->admin_notes);
    }

    public function test_profile_page_displays_student_reward_points_card(): void
    {
        $student = User::factory()->create([
            'points_balance' => 85,
            'points_lifetime' => 85,
        ]);

        $response = $this->actingAs($student)->get(route('profile'));

        $response->assertStatus(200);
        $response->assertSee('Poin &amp; Reward Mahasiswa', false);
        $response->assertSee('85 Poin');
        $response->assertSee('Mahasiswa Perintis');
    }

    public function test_submitting_review_awards_points_to_reviewer(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create(['points_balance' => 0]);
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $transaction = Transaction::create([
            'transaction_code' => 'TRX-RW-001',
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'amount' => 50000,
            'payment_method' => 'bank_transfer',
            'payment_status' => 'paid',
            'delivery_method' => 'pickup_point',
            'status' => Transaction::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($buyer)->post(route('transactions.reviews.store', $transaction), [
            'rating' => 5,
            'comment' => 'Penjual ramah dan barang sesuai deskripsi!',
            'tags' => ['Ramah', 'Sesuai Deskripsi'],
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(RewardPointService::POINTS_REVIEW_SUBMITTED, $buyer->fresh()->points_balance);

        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $buyer->id,
            'amount' => RewardPointService::POINTS_REVIEW_SUBMITTED,
            'type' => PointTransaction::TYPE_REVIEW_SUBMITTED,
        ]);
    }
}
