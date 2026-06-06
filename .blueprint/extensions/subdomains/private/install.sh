#!/bin/bash

PTERODACTYL_DIRECTORY="/var/www/pterodactyl"
DATA_DIRECTORY="/var/www/pterodactyl/.blueprint/extensions/subdomains/private"

# Copy new files
/bin/cp -f "$DATA_DIRECTORY/Subdomain.php" "$PTERODACTYL_DIRECTORY/app/Models/Subdomain.php"
/bin/cp -f "$DATA_DIRECTORY/Domain.php" "$PTERODACTYL_DIRECTORY/app/Models/Domain.php"
/bin/cp -f "$DATA_DIRECTORY/DomainController.php" "$PTERODACTYL_DIRECTORY/app/Http/Controllers/Api/Client/Servers/DomainController.php"
/bin/cp -f "$DATA_DIRECTORY/ServerDeleting.php" "$PTERODACTYL_DIRECTORY/app/Listeners/ServerDeleting.php"

# Update `api-client` route file (only if it doesn't have the modifications yet!)
ROUTE_FILE="$PTERODACTYL_DIRECTORY/routes/api-client.php"
INPUT=$(grep -v "SUBDOMAINS" "$ROUTE_FILE")
INSERT_LINE=$(echo "$INPUT" | grep -n 'docker-image' | cut -f1 -d:)
INSERT_LINE=$((INSERT_LINE + 1))
LINE_COUNT=$(echo "$INPUT" | wc -l)
SECOND_HALF_COUNT=$((LINE_COUNT - INSERT_LINE))

ROUTE_FRAGMENT="
    Route::group(['prefix' => '/domains'], function () { // SUBDOMAINS
        Route::get('/', [Client\Servers\DomainController::class, 'index']); // SUBDOMAINS
        Route::post('/', [Client\Servers\DomainController::class, 'store']); // SUBDOMAINS
        Route::delete('/{subdomain}', [Client\Servers\DomainController::class, 'delete']); // SUBDOMAINS
    }); // SUBDOMAINS
"

FIRST_HALF=$(echo "$INPUT" | head -n $INSERT_LINE)
SECOND_HALF=$(echo "$INPUT" | tail -n $SECOND_HALF_COUNT)
OUTPUT="${FIRST_HALF}${ROUTE_FRAGMENT}${SECOND_HALF}"
echo "$OUTPUT" > "$ROUTE_FILE"


# Update EventServiceProvider
EVENT_FILE="$PTERODACTYL_DIRECTORY/app/Providers/EventServiceProvider.php"
INPUT=$(grep -v "SUBDOMAINS" "$EVENT_FILE")
INSERT_LINE=$(echo "$INPUT" | grep -n '$listen' | cut -f1 -d:)
INSERT_LINE=$((INSERT_LINE))
LINE_COUNT=$(echo "$INPUT" | wc -l)
SECOND_HALF_COUNT=$((LINE_COUNT - INSERT_LINE))

EVENT_FRAGMENT="
    \Pterodactyl\Events\Server\Deleting::class => [\Pterodactyl\Listeners\ServerDeleting::class], // SUBDOMAINS
"

FIRST_HALF=$(echo "$INPUT" | head -n $INSERT_LINE)
SECOND_HALF=$(echo "$INPUT" | tail -n $SECOND_HALF_COUNT)
OUTPUT="${FIRST_HALF}${EVENT_FRAGMENT}${SECOND_HALF}"
echo "$OUTPUT" > "$EVENT_FILE"


# Clear route cache
# This is needed because currently Blueprint
# clears cache *before* running scripts.
php artisan route:clear
