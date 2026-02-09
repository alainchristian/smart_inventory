<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">QR Code Generation Test</h1>
            <p class="text-gray-600 mb-6">Testing SimpleSoftwareIO QR Code Package</p>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Test 1: Using Short Alias -->
                <div class="border-2 border-gray-200 rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4 text-indigo-600">Test 1: Short Alias</h2>
                    <div class="bg-gray-50 p-4 rounded mb-4">
                        <code class="text-sm">QrCode::size(200)->generate('TEST1')</code>
                    </div>
                    <div class="flex justify-center">
                        @php
                            try {
                                echo QrCode::size(200)->generate('https://example.com/test1');
                                echo '<p class="text-green-600 font-semibold mt-4">✓ SUCCESS</p>';
                            } catch (\Exception $e) {
                                echo '<p class="text-red-600 font-semibold mt-4">✗ ERROR: ' . $e->getMessage() . '</p>';
                            }
                        @endphp
                    </div>
                </div>

                <!-- Test 2: Full Namespace -->
                <div class="border-2 border-gray-200 rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4 text-purple-600">Test 2: Full Namespace</h2>
                    <div class="bg-gray-50 p-4 rounded mb-4">
                        <code class="text-sm">\SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate('TEST2')</code>
                    </div>
                    <div class="flex justify-center">
                        @php
                            try {
                                echo \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate('https://example.com/test2');
                                echo '<p class="text-green-600 font-semibold mt-4">✓ SUCCESS</p>';
                            } catch (\Exception $e) {
                                echo '<p class="text-red-600 font-semibold mt-4">✗ ERROR: ' . $e->getMessage() . '</p>';
                            }
                        @endphp
                    </div>
                </div>
            </div>
        </div>

        <!-- Test 3: Production Style (With Route Helper) -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-semibold mb-4 text-green-600">Test 3: Production Style</h2>
            <p class="text-gray-600 mb-4">This simulates the actual usage in your transfer pages</p>

            <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- QR Code -->
                    <div class="text-center">
                        <div class="bg-white p-6 rounded-lg inline-block shadow-lg border-4 border-purple-200">
                            @php
                                try {
                                    $testUrl = url('/scanner') . '?code=ABC123';
                                    echo QrCode::size(250)->generate($testUrl);
                                } catch (\Exception $e) {
                                    echo '<p class="text-red-600">ERROR: ' . $e->getMessage() . '</p>';
                                }
                            @endphp
                        </div>
                        <div class="mt-4 bg-purple-100 rounded-lg p-3">
                            <p class="text-sm font-bold text-purple-900">📱 Scan This QR Code</p>
                            <p class="text-xs text-purple-700 mt-1">URL: {{ url('/scanner?code=ABC123') }}</p>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="flex flex-col justify-center">
                        <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg p-4">
                            <p class="font-bold mb-3 text-lg">📷 Quick Setup:</p>
                            <ol class="text-sm space-y-2">
                                <li>1. Open phone camera app</li>
                                <li>2. Point at QR code (left)</li>
                                <li>3. Tap notification</li>
                                <li>4. Start scanning!</li>
                            </ol>
                        </div>

                        <div class="mt-4 bg-green-50 border-2 border-green-200 rounded-lg p-4">
                            <p class="text-green-800 font-semibold">✓ If you see the QR code above, your system is working perfectly!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6 mt-6">
            <h3 class="text-xl font-semibold text-blue-900 mb-3">✅ Next Steps</h3>
            <ul class="space-y-2 text-blue-800">
                <li>1. If all 3 QR codes show above → Your system works perfectly</li>
                <li>2. Go to your actual transfer page (e.g., /shop/transfers/14/receive)</li>
                <li>3. Press <kbd class="px-2 py-1 bg-gray-200 rounded">Ctrl+Shift+R</kbd> to hard refresh (with DevTools cache disabled)</li>
                <li>4. Click "Enable Phone Scanner"</li>
                <li>5. The QR code should now appear!</li>
            </ul>
        </div>

        <!-- Troubleshooting -->
        <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-6 mt-6">
            <h3 class="text-xl font-semibold text-yellow-900 mb-3">⚠️ If QR Codes Don't Show</h3>
            <div class="space-y-2 text-yellow-800 text-sm">
                <p><strong>Error "Class 'QrCode' not found":</strong></p>
                <pre class="bg-white p-2 rounded">php artisan config:clear
php artisan config:cache
composer dump-autoload</pre>

                <p class="mt-4"><strong>Error "A facade root has not been set":</strong></p>
                <p>Restart your Laravel development server or Apache.</p>
            </div>
        </div>
    </div>
</body>
</html>
