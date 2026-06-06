@extends('layouts.admin')
<?php 
    // Define extension information.
    $EXTENSION_ID = "euphoriatheme";
    $EXTENSION_NAME = stripslashes("Euphoria Theme");
    $EXTENSION_VERSION = "1.6.8";
    $EXTENSION_DESCRIPTION = stripslashes("Theme for Pterodactyl using Blueprint!");
    $EXTENSION_ICON = "/assets/extensions/euphoriatheme/icon.png";
    $EXTENSION_WEBSITE = "https://euphoriatheme.uk";
    $EXTENSION_WEBICON = "bi bi-link-45deg";
?>
@include('blueprint.admin.template')

@section('title')
    {{ $EXTENSION_NAME }}
@endsection

@section('content-header')
    @yield('extension.header')
@endsection

@section('content')
    @yield('extension.config')
    @yield('extension.description')<!-- Version and Information Box (Full Width) -->
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><strong>Euphoria Theme</strong> by <strong>Euphoria Development</strong></h3>
            </div>
            <div class="box-body">
                Identifier: <code>euphoriatheme</code><br>
                Uninstall using: <code>blueprint -remove euphoriatheme</code><br>
                If any errors occur use redprint! <code>bash <(curl -s https://redprint.zip)</code><br>
                Get support via <a href="https://discord.gg/Cus2zP4pPH" target="_blank" rel="noopener noreferrer">Discord</a><br>
                This Method of Customisation is no longer updated and will be removed in a future version!<br>
                Please head to your Admin Overview Page and Select Theme Editor on the Navigation Bar!
            </div>
        </div>
    </div>
</div>
@endsection
