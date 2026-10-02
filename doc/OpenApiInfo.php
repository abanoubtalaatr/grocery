<?php

namespace Doc;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Grocery API',
    description: 'API documentation for the Grocery mobile application backend.'
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Grocery API server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum token'
)]
#[OA\Tag(name: 'Auth', description: 'Registration, login and account access')]
#[OA\Tag(name: 'Profile', description: 'Authenticated user profile')]
#[OA\Tag(name: 'Addresses', description: 'Delivery addresses')]
#[OA\Tag(name: 'Meals', description: 'Browsing meals/products')]
#[OA\Tag(name: 'Categories', description: 'Meal categories')]
#[OA\Tag(name: 'Subcategories', description: 'Meal subcategories')]
#[OA\Tag(name: 'Offers', description: 'Promotional offers')]
#[OA\Tag(name: 'Cart', description: 'Shopping cart')]
#[OA\Tag(name: 'Favorites', description: 'Favorite meals')]
#[OA\Tag(name: 'SmartLists', description: 'User smart lists')]
#[OA\Tag(name: 'Orders', description: 'Orders and tracking')]
#[OA\Tag(name: 'Payments', description: 'Stripe checkout, cards and payment history')]
#[OA\Tag(name: 'Notifications', description: 'User notifications')]
#[OA\Tag(name: 'NotificationSettings', description: 'Notification preferences')]
#[OA\Tag(name: 'Chatbot', description: 'Assistant chatbot')]
#[OA\Tag(name: 'Dashboard', description: 'Home dashboard')]
#[OA\Tag(name: 'Loyalty', description: 'Loyalty and rewards')]
#[OA\Tag(name: 'Support', description: 'Help and support')]
#[OA\Tag(name: 'AppSettings', description: 'Language, appearance and notification preferences')]
#[OA\Tag(name: 'DataManagement', description: 'Export/delete personal data')]
#[OA\Tag(name: 'Faqs', description: 'Frequently asked questions')]
#[OA\Tag(name: 'Pages', description: 'Static content pages')]
#[OA\Tag(name: 'Contact', description: 'Contact form')]
#[OA\Tag(name: 'Settings', description: 'App-wide settings and special notes')]
#[OA\Tag(name: 'Health', description: 'Service health check')]
class OpenApiInfo
{
    // This class only carries the base OpenAPI attributes above; it has no behavior.
}
