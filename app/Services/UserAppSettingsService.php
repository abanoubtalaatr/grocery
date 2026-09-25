<?php

namespace App\Services;

use App\Models\User;

class UserAppSettingsService
{
    /**
     * @return array{language: string}
     */
    public function getLanguage(User $user): array
    {
        return [
            'language' => $this->normalizeLanguage($user->app_language),
        ];
    }

    /**
     * @return array{language: string}
     */
    public function updateLanguage(User $user, string $language): array
    {
        $user->update(['app_language' => $this->normalizeLanguage($language)]);

        return $this->getLanguage($user->fresh());
    }

    /**
     * @return array{theme: string}
     */
    public function getAppearance(User $user): array
    {
        return [
            'theme' => $this->normalizeTheme($user->app_theme),
        ];
    }

    /**
     * @return array{theme: string}
     */
    public function updateAppearance(User $user, string $theme): array
    {
        $user->update(['app_theme' => $this->normalizeTheme($theme)]);

        return $this->getAppearance($user->fresh());
    }

    /**
     * @return array{
     *     order_updates: bool,
     *     promotion_emails: bool,
     *     nutrition_insights: bool,
     *     price_alerts: bool
     * }
     */
    public function getNotificationPreferences(User $user): array
    {
        $settings = $user->initializeNotificationSettings();

        return [
            'order_updates' => (bool) $settings->order_updates,
            'promotion_emails' => (bool) $settings->promotion_emails,
            'nutrition_insights' => (bool) $settings->nutrition_insights,
            'price_alerts' => (bool) $settings->price_alerts,
        ];
    }

    /**
     * @param  array<string, bool>  $preferences
     * @return array{
     *     order_updates: bool,
     *     promotion_emails: bool,
     *     nutrition_insights: bool,
     *     price_alerts: bool
     * }
     */
    public function updateNotificationPreferences(User $user, array $preferences): array
    {
        $settings = $user->initializeNotificationSettings();
        $settings->update([
            'order_updates' => (bool) ($preferences['order_updates'] ?? $settings->order_updates),
            'promotion_emails' => (bool) ($preferences['promotion_emails'] ?? $settings->promotion_emails),
            'nutrition_insights' => (bool) ($preferences['nutrition_insights'] ?? $settings->nutrition_insights),
            'price_alerts' => (bool) ($preferences['price_alerts'] ?? $settings->price_alerts),
        ]);

        return $this->getNotificationPreferences($user->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    public function buildDataExport(User $user): array
    {
        $user->load([
            'addresses',
            'orders' => fn ($query) => $query->latest()->limit(50),
            'notificationSettings',
        ]);

        return [
            'exported_at' => now()->toIso8601String(),
            'profile' => [
                'id' => $user->id,
                'username' => $user->username,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'phone' => $user->phone,
                'app_language' => $user->app_language,
                'app_theme' => $user->app_theme,
                'loyalty_points' => $user->loyalty_points,
                'store_credits' => $user->store_credits,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'addresses' => $user->addresses->map(fn ($address) => [
                'label' => $address->label,
                'full_name' => $address->full_name,
                'phone' => $address->phone,
                'street_address' => $address->street_address,
                'city' => $address->city,
                'country' => $address->country,
                'is_default' => $address->is_default,
            ])->values()->all(),
            'orders' => $user->orders->map(fn ($order) => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => (float) $order->total,
                'placed_at' => $order->placed_at?->toIso8601String(),
            ])->values()->all(),
            'notification_preferences' => $this->getNotificationPreferences($user),
        ];
    }

    private function normalizeLanguage(?string $language): string
    {
        return in_array($language, ['en', 'ar'], true) ? $language : 'en';
    }

    private function normalizeTheme(?string $theme): string
    {
        return in_array($theme, ['light', 'dark'], true) ? $theme : 'light';
    }
}
