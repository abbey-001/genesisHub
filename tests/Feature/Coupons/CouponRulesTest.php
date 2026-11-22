<?php

namespace Tests\Feature\Coupons;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponRulesTest extends TestCase
{
    use RefreshDatabase;

    private function coupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'seller_id'         => Seller::factory()->create()->id,
            'code'              => 'SAVE20',
            'type'              => 'percent',
            'value'             => 20,
            'min_order_amount'  => 0,
            'max_uses_per_user' => 1,
            'is_active'         => true,
        ], $overrides));
    }

    public function test_percent_discount_is_capped_by_max_discount_amount(): void
    {
        $coupon = $this->coupon(['value' => 20, 'max_discount_amount' => 1500]);

        $this->assertSame(1000.0, $coupon->calculateDiscount(5000));   // 20% of 5,000
        $this->assertSame(1500.0, $coupon->calculateDiscount(50000));  // capped at 1,500
    }

    public function test_fixed_discount_never_exceeds_the_subtotal(): void
    {
        $coupon = $this->coupon(['type' => 'fixed', 'value' => 2000]);

        $this->assertSame(2000.0, $coupon->calculateDiscount(10000));
        $this->assertSame(800.0, $coupon->calculateDiscount(800));
    }

    public function test_inactive_expired_and_future_coupons_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($this->coupon(['code' => 'OFF', 'is_active' => false])->validate($user->id, 5000)['valid']);
        $this->assertFalse($this->coupon(['code' => 'OLD', 'expires_at' => now()->subDay()])->validate($user->id, 5000)['valid']);
        $this->assertFalse($this->coupon(['code' => 'SOON', 'starts_at' => now()->addDay()])->validate($user->id, 5000)['valid']);
    }

    public function test_minimum_order_amount_is_enforced(): void
    {
        $user   = User::factory()->create();
        $coupon = $this->coupon(['min_order_amount' => 10000]);

        $this->assertFalse($coupon->validate($user->id, 9999.99)['valid']);
        $this->assertTrue($coupon->validate($user->id, 10000)['valid']);
    }

    public function test_global_usage_limit_is_enforced(): void
    {
        $user   = User::factory()->create();
        $coupon = $this->coupon(['max_uses' => 50, 'used_count' => 50]);

        $result = $coupon->validate($user->id, 5000);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('usage limit', $result['message']);
    }

    public function test_each_customer_can_only_use_a_coupon_up_to_their_personal_limit(): void
    {
        $user   = User::factory()->create();
        $other  = User::factory()->create();
        $coupon = $this->coupon(['max_uses_per_user' => 1]);

        CouponUsage::create([
            'coupon_id'        => $coupon->id,
            'user_id'          => $user->id,
            'order_id'         => Order::factory()->create(['user_id' => $user->id])->id,
            'discount_applied' => 1000,
        ]);

        $this->assertFalse($coupon->validate($user->id, 5000)['valid']);
        $this->assertTrue($coupon->validate($other->id, 5000)['valid']);
    }
}
