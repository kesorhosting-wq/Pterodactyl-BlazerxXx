<?php
Route::post('/upload-profile-picture-url', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'uploadProfilePictureUrl'])->name('blueprint.extensions.euphoriatheme.wrapper.upload.profile.url');
Route::post('/upload-profile-picture-file', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'uploadProfilePictureFile'])->name('blueprint.extensions.euphoriatheme.wrapper.upload.profile.file');
Route::post('/reset-profile-picture-url', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'resetProfilePictureUrl'])->name('blueprint.extensions.euphoriatheme.wrapper.reset.profile.url');
Route::get('/user/profile', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'fetchProfilePicture'])->name('blueprint.extensions.euphoriatheme.wrapper.user.profile');

Route::get('/server-backgrounds', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'showBackgroundForm'])->name('blueprint.extensions.euphoriatheme.wrapper.admin.serverBackgrounds');
Route::get('/configured-egg-backgrounds', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'fetchConfiguredEggBackgrounds'])->name('blueprint.extensions.euphoriatheme.wrapper.styles.import');
Route::get('/configured-server-backgrounds', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'fetchConfiguredServerBackgrounds'])->name('blueprint.extensions.euphoriatheme.wrapper.styles.import');
Route::post('/admin/extensions/{identifier}/bulk-save', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'bulkSaveBackgrounds'])->name('blueprint.extensions.euphoriatheme.bulkSaveSettings');
Route::post('/admin/extensions/{identifier}/update-delete', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'updateAndDeleteBackgroundSettings'])->name('blueprint.extensions.euphoriatheme.updateAndDeleteSettings');

Route::get('/translations', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'showLanguageForm'])->name('blueprint.extensions.euphoriatheme.wrapper.admin.languages');
Route::get('/admin/languages/settings', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'getLanguageSettings'])->name('blueprint.extensions.euphoriatheme.languages.settings');
Route::post('/admin/languages/settings', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'saveLanguageSettings'])->name('blueprint.extensions.euphoriatheme.languages.settings.save');

Route::get('/admin/theme/settings', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'index'])->name('blueprint.extensions.euphoriatheme.wrapper.admin.themeCustomiser');
Route::post('/admin/theme/save-settings', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'update'])->name('blueprint.extensions.euphoriatheme.wrapper.admin.themeCustomiser.save');

Route::get('/admin/licensing', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'showLicenseForm'])->name('blueprint.extensions.euphoriatheme.wrapper.admin.licensing');
Route::post('/admin/licensing', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'saveLicense'])->name('blueprint.extensions.euphoriatheme.wrapper.admin.license.submit');

Route::get('/server-order', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'fetchServerOrder'])->name('blueprint.extensions.euphoriatheme.server.order.fetch');
Route::post('/server-order', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'saveServerOrder'])->name('blueprint.extensions.euphoriatheme.server.order.save');

Route::get('/api/api/api/euphoria-status', [Pterodactyl\Http\Controllers\Admin\Extensions\{identifier}\{identifier}ExtensionController::class, 'getEuphoriaStatus'])->name('blueprint.extensions.euphoriatheme.api.status');
?>