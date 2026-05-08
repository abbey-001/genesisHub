<?php

namespace App\Services;

use App\Models\Seller;
use App\Models\User;
use App\Notifications\SellerSecurityAlert;

/**
 * Fires security alert emails whenever a seller changes sensitive account fields.
 *
 * Usage — call from your seller account settings controller BEFORE saving:
 *
 *   SellerSecurityAlertService::checkAndAlert($seller, $request->all());
 *
 * Or after saving with the old values:
 *
 *   SellerSecurityAlertService::alertIfChanged($user, $seller, $oldValues, $newValues);
 */
class SellerSecurityAlertService
{
    /**
     * Monitored Seller fields → human-readable labels.
     */
    private static array $sellerFields = [
        'bank_account'        => 'Bank Account Number',
        'bank_name'           => 'Bank Name',
        'account_holder_name' => 'Account Holder Name',
        'phone_number'        => 'Phone Number',
    ];

    /**
     * Monitored User fields → human-readable labels.
     */
    private static array $userFields = [
        'email' => 'Email Address',
        'name'  => 'Account Name',
    ];

    /**
     * Compare old vs new values for a Seller model and fire alerts for any changes.
     *
     * @param  User   $user     The user who owns the seller account
     * @param  Seller $seller   The seller model (with original values still loaded)
     * @param  array  $newData  The new values being saved (from request)
     */
    public static function checkSellerChanges(User $user, Seller $seller, array $newData): void
    {
        $changes = [];

        foreach (self::$sellerFields as $field => $label) {
            if (isset($newData[$field]) && $newData[$field] !== $seller->getOriginal($field)) {
                $changes[] = [
                    'field'     => $label,
                    'old_value' => self::mask($field, $seller->getOriginal($field)),
                    'new_value' => self::mask($field, $newData[$field]),
                ];
            }
        }

        if (! empty($changes)) {
            $user->notify(new SellerSecurityAlert('seller_profile_changed', $changes));
        }
    }

    /**
     * Compare old vs new values for User model fields.
     *
     * @param  User  $user    The user model (with original values still loaded)
     * @param  array $newData The new values being saved
     */
    public static function checkUserChanges(User $user, array $newData): void
    {
        $changes = [];

        foreach (self::$userFields as $field => $label) {
            if (isset($newData[$field]) && $newData[$field] !== $user->getOriginal($field)) {
                $changes[] = [
                    'field'     => $label,
                    'old_value' => self::mask($field, $user->getOriginal($field)),
                    'new_value' => self::mask($field, $newData[$field]),
                ];
            }
        }

        if (! empty($changes)) {
            $user->notify(new SellerSecurityAlert('seller_profile_changed', $changes));
        }
    }

    /**
     * Mask sensitive values before including them in emails.
     * Bank account: show last 4 digits only.
     * Email: mask middle characters.
     */
    private static function mask(string $field, ?string $value): string
    {
        if (is_null($value)) return '(empty)';

        if ($field === 'bank_account') {
            return str_repeat('*', max(0, strlen($value) - 4)) . substr($value, -4);
        }

        if ($field === 'email') {
            [$local, $domain] = explode('@', $value, 2);
            $masked = substr($local, 0, 2) . str_repeat('*', max(0, strlen($local) - 2));
            return $masked . '@' . $domain;
        }

        return $value;
    }
}