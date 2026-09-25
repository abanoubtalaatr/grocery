<?php

namespace App\Actions\Chatbot;

class GetChatSuggestionsAction
{
    public function handle(string $locale): array
    {
        if ($locale === 'ar') {
            return [
                ['id' => 'faq',      'label' => 'أسئلة شائعة',        'question' => 'ما هي الأسئلة الشائعة؟'],
                ['id' => 'orders',   'label' => 'تتبع الطلب',          'question' => 'كيف أتتبع طلبي؟'],
                ['id' => 'payment',  'label' => 'طرق الدفع',           'question' => 'ما طرق الدفع المتاحة؟'],
                ['id' => 'products', 'label' => 'المنتجات والمفضلة',   'question' => 'ما المنتجات المتاحة والعروض؟'],
                ['id' => 'offers',   'label' => 'كوبونات وعروض',       'question' => 'ما العروض وكوبونات الخصم الحالية؟'],
            ];
        }

        return [
            ['id' => 'faq',      'label' => 'FAQs',              'question' => 'What are the frequently asked questions?'],
            ['id' => 'orders',   'label' => 'Track order',        'question' => 'How do I track my order?'],
            ['id' => 'payment',  'label' => 'Payment methods',    'question' => 'What payment methods do you accept?'],
            ['id' => 'products', 'label' => 'Products & offers',  'question' => 'What products and offers do you have?'],
            ['id' => 'offers',   'label' => 'Coupons & offers',   'question' => 'What promo codes or offers are available?'],
        ];
    }
}