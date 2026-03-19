<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your Password — Smart Inventory</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body style="margin:0;background:var(--bg)">
    <livewire:auth.change-password />
    @livewireScripts
</body>
</html>
