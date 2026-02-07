<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\MealController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SmartListController;
use App\Http\Controllers\Api\StaticPageController;
use App\Http\Controllers\Api\SpecialNoteController;
use App\Http\Controllers\Api\SubcategoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\NotificationSettingsController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes - Authentication
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/google', [GoogleAuthController::class, 'login']);
});

// Protected routes - Require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::post('/image', [ProfileController::class, 'updateImage']);
        Route::put('/info', [ProfileController::class, 'updateInfo']);
        Route::delete('/image', [ProfileController::class, 'deleteImage']);
    });

    // Address routes
    Route::prefix('addresses')->group(function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'store']);
        Route::get('/{id}', [AddressController::class, 'show']);
        Route::put('/{id}', [AddressController::class, 'update']);
        Route::delete('/{id}', [AddressController::class, 'destroy']);
        Route::post('/{id}/set-default', [AddressController::class, 'setDefault']);
    });

    Route::apiResource('smart-lists',SmartListController::class);

    Route::prefix('notification-settings')->group(function () {
        Route::get('/', [NotificationSettingsController::class, 'index']);
        Route::put('/', [NotificationSettingsController::class, 'update']);
        Route::put('/category/{category}', [NotificationSettingsController::class, 'updateCategory']);
    });

    Route::prefix('notifications')->group(function () {
        // Get notifications
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/with-resources', [NotificationController::class, 'indexWithResources']);
        
        // Statistics
        Route::get('/stats', [NotificationController::class, 'stats']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::get('/recent', [NotificationController::class, 'recent']);
        
        // Single notification operations
        Route::get('/{id}', [NotificationController::class, 'show']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/{id}/unread', [NotificationController::class, 'markAsUnread']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        
        // Bulk operations
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/delete-multiple', [NotificationController::class, 'destroyMultiple']);
        Route::delete('/clear-all', [NotificationController::class, 'clearAll']);
        
        // Filtered notifications
        Route::get('/type/{type}', [NotificationController::class, 'byType']);
    });
    // Meals routes
    Route::prefix('meals')->group(function () {
        Route::get('/today', [MealController::class, 'today']);
        Route::get('hot',[MealController::class, 'hot']);
       
        Route::get('/recommendations', [MealController::class, 'recommendations']);
        Route::get('/', [MealController::class, 'index']);
        Route::get('/{id}', [MealController::class, 'show']);
      
    });
    Route::get('/new-products',[MealController::class, 'newProducts']);
    Route::get('best-sells',[MealController::class, 'bestSells']);
    Route::get('sliders',[MealController::class, 'slider']);
    Route::get('brands',[MealController::class, 'brands']);
    Route::get('more-to-explore',[MealController::class, 'moreToExplore']);
    Route::get('frequency',[MealController::class, 'frequency']);
    Route::get('settings',[SettingController::class, 'index']);
    Route::get('special-notes',[SpecialNoteController::class, 'index']);
    // Categories routes

    Route::prefix('offers')->group(function () {
        Route::get('/', [OfferController::class, 'index']);
        Route::get('/featured', [OfferController::class, 'featured']);
        Route::get('/validate', [OfferController::class, 'validateOffer']);
        Route::get('/{code}', [OfferController::class, 'showByCode']);
    });
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::get('/{id}/meals', [CategoryController::class, 'meals']);
    });

    // Subcategories routes
    Route::prefix('subcategories')->group(function () {
        Route::get('/', [SubcategoryController::class, 'index']);
        Route::get('/{id}', [SubcategoryController::class, 'show']);
        Route::get('/{id}/meals', [SubcategoryController::class, 'meals']);
    });

    // Cart routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/items', [CartController::class, 'addItem']);
        Route::put('/items/{itemId}', [CartController::class, 'updateItem']);
        Route::delete('/items/{itemId}', [CartController::class, 'removeItem']);
        Route::delete('/clear', [CartController::class, 'clear']);
    });

    // Favorites routes
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('/{mealId}/toggle', [FavoriteController::class, 'toggle']);
        Route::get('/{mealId}/check', [FavoriteController::class, 'check']);
        Route::delete('/{mealId}', [FavoriteController::class, 'remove']);
    });

    // Chatbot routes
    Route::prefix('chatbot')->group(function () {
        Route::post('/', [ChatbotController::class, 'chat']);
        Route::get('/history', [ChatbotController::class, 'history']);
        Route::get('/suggestions', [ChatbotController::class, 'suggestions']);
    });

    Route::get('/cards', [StripeController::class, 'listCards']);
    Route::post('/setup-intent', [StripeController::class, 'createSetupIntent']);
    Route::post('/charge-card', [StripeController::class, 'chargeSavedCard']);
    Route::delete('/cards/{id}', [StripeController::class, 'deleteCard']);

    // Order routes
    Route::prefix('orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/track', [OrderController::class, 'track']);
        Route::get('/{id}', [OrderController::class, 'show']);
    });

    // Payment routes
    Route::prefix('payments')->group(function () {
        Route::get('/history', [PaymentController::class, 'paymentHistory']);
        Route::get('/receipt/{order}', [PaymentController::class, 'receipt']);
        Route::get('/invoice/{order}', [PaymentController::class, 'invoice']);
    });

    // Dashboard route
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

Route::get('/faqs', [FaqController::class, 'index']);
Route::get('/pages', [StaticPageController::class, 'index']);
Route::get('/pages/slug/{slug}', [StaticPageController::class, 'showBySlug']);
Route::get('/pages/important', [StaticPageController::class, 'importantPages']);
Route::post('/contact', [ContactController::class, 'submit']);

// Health check route
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now(),
    ]);
});
