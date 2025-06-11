<?php
session_start();
require 'functions.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? 'your_userid';

$base_url = rtrim(getSetting('base_url'), '/') . '/';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
   <link
        href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
        rel="stylesheet"
    />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
    <style>
        .plan-card {
            transition: all 0.19s cubic-bezier(.4, 0, .2, 1);
        }
        .plan-card:hover {
            transform: translateY(-4px) scale(1.012);
            box-shadow: 0 10px 32px rgba(37, 99, 235, 0.14);
            border-color: #fbbf24;
        }
        .plan-popular {
            border-width: 3px;
            border-color: #fbbf24;
        }
    </style>
</head>
<?php include("head.php"); ?>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 leading-relaxed">

    <?php include("header.php"); ?>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-indigo-900 to-violet-700 text-white py-20">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6 drop-shadow">Simple, All-in-One <span class="text-gold-400">WhatsApp API</span> Plan</h1>
            <p class="max-w-2xl mx-auto text-lg opacity-90 mb-8">
                One affordable price. Zero limitations.<br>
                No tiers, no confusion—just everything your business needs.
            </p>
        </div>
    </section>

    <!-- Pricing Plan Section -->
    <section class="py-20 bg-white dark:bg-gray-900">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center">
                <!-- Only 999 Plan -->
                <div class="plan-card plan-popular bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-10 border w-full max-w-lg flex flex-col items-center relative">
                    <div class="absolute -top-5 left-1/2 transform -translate-x-1/2">
                        <span class="bg-gold-400 text-white font-bold px-5 py-1.5 rounded-full shadow-lg text-sm uppercase tracking-wide">Best Value</span>
                    </div>
                    <h2 class="text-2xl font-bold mb-2 text-indigo-700 dark:text-indigo-200">WhatsApp API Plan</h2>
                    <div class="text-5xl font-extrabold mb-1 text-violet-900 dark:text-violet-100">
                        PKR 999<span class="text-lg font-medium">/month</span>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400 mb-5">(Just $3/month)</span>
                    <ul class="mb-8 space-y-2 text-gray-800 dark:text-gray-200 text-left mx-auto max-w-xs">
                        <li>✔ 1 WhatsApp Number</li>
                        <li>✔ Unlimited Messages (Bulk & API)</li>
                        <li>✔ Analytics Dashboard</li>
                        <li>✔ Secure REST API</li>
                        <li>✔ Support: WhatsApp & Email</li>
                        <li>✔ Free Onboarding</li>
                        <li>✔ Cancel Anytime</li>
                    </ul>
                   <a
    href="<?= $isLoggedIn ? $base_url . 'order?plan=basic' : $base_url . 'login?plan=basic' ?>"
    class="w-full px-8 py-3 bg-gradient-to-r from-yellow-300 to-yellow-400 text-violet-900 rounded-lg font-semibold hover:from-yellow-400 hover:to-yellow-500 transition text-lg shadow text-center"
>
    Get Started for 999 PKR
</a>
                </div>
            </div>
            <p class="mt-14 text-center text-gray-500 dark:text-gray-400 text-base">
                No hidden fees. Unlimited API usage. Cancel anytime.
            </p>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-20 bg-gray-50 dark:bg-gray-800">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-center mb-10">
                Frequently Asked Questions
            </h2>
            <div class="max-w-2xl mx-auto space-y-7">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                    <h3 class="font-semibold text-lg mb-2 text-indigo-700">Can I cancel anytime?</h3>
                    <p class="text-gray-600 dark:text-gray-300">Yes, you can cancel anytime from your dashboard. No lock-in, no penalty.</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                    <h3 class="font-semibold text-lg mb-2 text-indigo-700">Is there a per-message fee?</h3>
                    <p class="text-gray-600 dark:text-gray-300">No! You get unlimited API and bulk messages for a flat monthly fee.</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                    <h3 class="font-semibold text-lg mb-2 text-indigo-700">Can I use my own WhatsApp number?</h3>
                    <p class="text-gray-600 dark:text-gray-300">Absolutely. You connect your own number and manage linking via the dashboard.</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                    <h3 class="font-semibold text-lg mb-2 text-indigo-700">How does support work?</h3>
                    <p class="text-gray-600 dark:text-gray-300">Get fast support via WhatsApp and email. We’re here to help.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call-to-Action Section -->
    <section class="py-20 bg-gradient-to-br from-indigo-900 to-violet-700 text-white text-center">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">
                Ready to start? Try risk-free today.
            </h2>
            <p class="mb-8 text-lg opacity-90 max-w-xl mx-auto">
                No credit card required. Cancel anytime.
            </p>
            <?php
                $admin_number = getSetting('admin_number');
                $wa_link = "https://wa.me/+" . $admin_number . "/?text=" . urlencode("Hello, I would like a WhatsApp API trial demo. Please assist.");
            ?>
            <a
                href="<?= $wa_link ?>"
                target="_blank"
                class="inline-block w-full sm:w-auto px-8 py-3 bg-white text-violet-800 font-bold rounded-lg shadow-lg text-lg hover:bg-gray-200 transition"
            >
                Start Free Trial
            </a>
        </div>
    </section>

</body>
</html>