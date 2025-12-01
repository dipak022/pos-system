<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Product Management & POS System</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* smooth fade animation */
        .fade-in {
            animation: fadeIn 1.2s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-100 to-gray-200 text-gray-800">

    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="max-w-6xl mx-auto py-6 px-6">
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 fade-in">
                Product Management & POS System
            </h1>
            <p class="text-gray-500 mt-1 text-sm fade-in">
                Advanced Laravel Based Billing System with Discounts & Trade Offers
            </p>
        </div>
    </header>

    <!-- Main -->
    <main class="max-w-6xl mx-auto px-6 mt-10 fade-in">

        <!-- Hero Section -->
        <div class="bg-white rounded-2xl shadow-lg p-10 mb-10">
            <h2 class="text-3xl font-semibold mb-4 text-gray-900">Welcome to the System</h2>
            <p class="text-gray-700 text-lg leading-relaxed">
                This Laravel project demonstrates an advanced <strong>Product Management</strong> &
                <strong>Point of Sale (POS)</strong> system built for academic assignment submission.
                It implements high-quality coding standards, API structure, error-handling, and real-world POS logic.
            </p>
        </div>

        <!-- Cards Grid -->
        <div class="grid md:grid-cols-3 gap-8">

            <!-- Features Card -->
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h3 class="text-xl font-semibold mb-3">✨ Key Features</h3>
                <ul class="list-disc pl-5 space-y-2 text-gray-700">
                    <li>Product CRUD</li>
                    <li>Discount (percentage based)</li>
                    <li>Trade Offer (Buy X Get Y)</li>
                    <li>POS Billing API</li>
                    <li>Stock & Min Stock Validation</li>
                    <li>Sanctum Authentication</li>
                </ul>
            </div>

            <!-- API Section -->
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h3 class="text-xl font-semibold mb-3">📌 API Endpoints</h3>
                <ul class="text-gray-700 space-y-1">
                    <li><strong>POST</strong> /api/register</li>
                    <li><strong>POST</strong> /api/login</li>
                    <li><strong>GET</strong> /api/products</li>
                    <li><strong>POST</strong> /api/products</li>
                    <li><strong>POST</strong> /api/pos</li>
                </ul>
            </div>

            <!-- Instruction Card -->
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h3 class="text-xl font-semibold mb-3">📝 Instructions</h3>
                <p class="text-gray-700 leading-relaxed">
                    Use tools like Postman to test API endpoints.
                    Make sure to authenticate using a bearer token after login.
                    The POS endpoint auto-applies discount or trade offers based on rules.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-16 text-gray-500 text-sm fade-in">
            Developed for Academic Assignment — Laravel 10 • TailwindCSS
        </div>

    </main>

</body>
</html>
