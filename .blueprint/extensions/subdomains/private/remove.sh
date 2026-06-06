#!/bin/bash

PTERODACTYL_DIRECTORY="/var/www/pterodactyl"
DATA_DIRECTORY="/var/www/pterodactyl/.blueprint/extensions/subdomains/private"

# Delete added files
rm -rf "$PTERODACTYL_DIRECTORY/app/Models/Subdomain.php"
rm -rf "$PTERODACTYL_DIRECTORY/app/Models/Domain.php"
rm -rf "$PTERODACTYL_DIRECTORY/app/Http/Controllers/Api/Client/Servers/DomainController.php"
rm -rf "$PTERODACTYL_DIRECTORY/app/Listeners/ServerDeleting.php"


# Remove `api-client` route modifications
ROUTE_FILE="$PTERODACTYL_DIRECTORY/routes/api-client.php"
OUTPUT=$(cat "$ROUTE_FILE" | grep -v "SUBDOMAINS")
echo "$OUTPUT" > "$ROUTE_FILE"

# Remove EventServiceProvider modifications
EVENT_FILE="$PTERODACTYL_DIRECTORY/app/Providers/EventServiceProvider.php"
OUTPUT=$(cat "$EVENT_FILE" | grep -v "SUBDOMAINS")
echo "$OUTPUT" > "$EVENT_FILE"
