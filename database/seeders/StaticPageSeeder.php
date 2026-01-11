<?php

namespace Database\Seeders;

use App\Models\StaticPage;
use Illuminate\Database\Seeder;

class StaticPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms and Conditions',
                'content' => $this->getTermsContent(),
                'meta_title' => 'Terms and Conditions',
                'meta_description' => 'Read our terms and conditions',
                'order' => 1,
                'is_published' => true
            ],
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'content' => $this->getPrivacyContent(),
                'meta_title' => 'Privacy Policy',
                'meta_description' => 'Learn about our privacy practices',
                'order' => 2,
                'is_published' => true
            ],
            [
                'slug' => 'return-policy',
                'title' => 'Return Policy',
                'content' => $this->getReturnContent(),
                'meta_title' => 'Return Policy',
                'meta_description' => 'Learn about our return policy',
                'order' => 3,
                'is_published' => true
            ],
            [
                'slug' => 'cookie-policy',
                'title' => 'Cookie Policy',
                'content' => $this->getCookieContent(),
                'meta_title' => 'Cookie Policy',
                'meta_description' => 'Learn about our cookie policy',
                'order' => 4,
                'is_published' => true
            ],
            [
                'slug' => 'about-us',
                'title' => 'About Us',
                'content' => $this->getAboutContent(),
                'meta_title' => 'About Us',
                'meta_description' => 'Learn more about our company',
                'order' => 3,
                'is_published' => true
            ],
            [
                'slug' => 'contact-us',
                'title' => 'Contact Us',
                'content' => $this->getContactContent(),
                'meta_title' => 'Contact Us',
                'meta_description' => 'Get in touch with us',
                'order' => 4,
                'is_published' => true
            ],
            [
                'slug' => 'refund-policy',
                'title' => 'Refund Policy',
                'content' => $this->getRefundContent(),
                'meta_title' => 'Refund Policy',
                'meta_description' => 'Our refund and return policy',
                'order' => 5,
                'is_published' => true
            ]
        ];

        foreach ($pages as $page) {
            StaticPage::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }

    private function getReturnContent(): string
    {
        return '<h1>Return Policy</h1>
                <p>We offer returns under certain conditions...</p>
                <h2>Eligibility</h2>
                <p>Returns are available within 30 days...</p>
                <h2>Process</h2>
                <p>To request a return, contact our support...</p>';
    }
    private function getTermsContent(): string
    {
        return '<h1>Terms and Conditions</h1>
                <p>Welcome to our website...</p>
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing and using this website...</p>
                <h2>2. User Responsibilities</h2>
                <p>You agree to use the website responsibly...</p>';
    }

    private function getCookieContent(): string
    {
        return '<h1>Cookie Policy</h1>
                <p>We use cookies to improve your experience...</p>
                <h2>What are Cookies?</h2>
                <p>Cookies are small data files stored on your device...</p>';
    }

    private function getPrivacyContent(): string
    {
        return '<h1>Privacy Policy</h1>
                <p>Your privacy is important to us...</p>
                <h2>Information We Collect</h2>
                <p>We collect information when you register...</p>
                <h2>How We Use Information</h2>
                <p>We use the information to provide services...</p>';
    }

    private function getAboutContent(): string
    {
        return '<h1>About Us</h1>
                <p>We are a company dedicated to...</p>
                <h2>Our Mission</h2>
                <p>To provide excellent services...</p>
                <h2>Our Team</h2>
                <p>We have a team of professionals...</p>';
    }

    private function getContactContent(): string
    {
        return '<h1>Contact Us</h1>
                <p>We\'d love to hear from you...</p>
                <p><strong>Email:</strong> support@example.com</p>
                <p><strong>Phone:</strong> +1 (555) 123-4567</p>
                <p><strong>Address:</strong> 123 Main St, City, Country</p>
                <p><strong>Business Hours:</strong> Mon-Fri 9am-5pm</p>';
    }

    private function getRefundContent(): string
    {
        return '<h1>Refund Policy</h1>
                <p>We offer refunds under certain conditions...</p>
                <h2>Eligibility</h2>
                <p>Refunds are available within 30 days...</p>
                <h2>Process</h2>
                <p>To request a refund, contact our support...</p>';
    }
}