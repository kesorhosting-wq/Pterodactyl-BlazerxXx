<!DOCTYPE html>
<html lang="en">
<head>
@section('meta')
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <link rel="icon" type="image/png" href="/assets/extensions/euphoriatheme/logo.png" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/extensions/euphoriatheme/logo.png">
    <link rel="icon" type="image/png" href="/assets/extensions/euphoriatheme/logo.png" sizes="16x16">
    <link rel="shortcut icon" href="/assets/extensions/euphoriatheme/logo.png">
    <link rel="manifest" href="/favicons/manifest.json">
    <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#bc6e3c">
    <meta name="msapplication-config" content="/favicons/browserconfig.xml">
    <meta name="theme-color" content="#0e4688">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/extensions/euphoriatheme/lib/licensing.css?{timestamp}">    
    <script src="/extensions/euphoriatheme/lib/licensing.js?{timestamp}"></script>
@show
    <title>Licensing - Euphoria Theme</title>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="{{ asset('/extensions/euphoriatheme/images/logo.png') }}" alt="Euphoria Theme Logo">
        </div>
        <h1 class="title">Euphoria Theme Licensing</h1>
        <form id="license-form" action="{{ route('blueprint.extensions.euphoriatheme.wrapper.admin.license.submit') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="licenseKey">Enter License Key:</label>
                <input type="text" id="licenseKey" name="licenseKey" class="license-key-input" value="{{ $licenseKey }}" required>
                <input type="hidden" name="hwid" value="{{ $hwid }}">
                <input type="hidden" name="productId" value="{{ $productId }}">
            </div>
            <button type="submit" class="submit-button">Submit</button>
            <a href="https://discord.gg/Cus2zP4pPH" target="_blank" class="support-button" style="text-decoration: none;">Get Help</a>
        </form>
    </div>
</body>
</html>
