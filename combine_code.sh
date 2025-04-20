#!/bin/bash

# Define the output file
OUTPUT_FILE="modal_migration_laravel.txt"

# Clear the output file if it exists
> "$OUTPUT_FILE"

# Function to append code from a directory
append_code() {
    local path="$1"
    local type="$2"

    echo -e "\n$type:" >> "$OUTPUT_FILE"
    for file in "$path"/*.php; do
        if [ -f "$file" ]; then
            echo -e "\n\n--- $file ---\n" >> "$OUTPUT_FILE"
            cat "$file" >> "$OUTPUT_FILE"
        fi
    done
}

# Append main models
echo "Main Application Code:" >> "$OUTPUT_FILE"
append_code "app/Models" "Models"

# # Append main migrations
append_code "database/migrations" "Migrations"

append_code "database/settings" "Migrations"

# append_code "app/Enums" "Enums"

# append_code "app/Filament/Client/Resources" "Client Resources"

# append_code "app/Filament/Resources" "Admin Resources"

# append_code "app/Filament/Agent/Resources" "Agent Resources"

# Loop through all module directories
for MODULE in app-modules/*/; do
    if [ -d "$MODULE" ]; then
        echo -e "\nModule: $MODULE" >> "$OUTPUT_FILE"

        # Append module models
        append_code "$MODULE/src/Model" "Models"

        # Append module migrations
        append_code "$MODULE/database/migrations" "Migrations"
    fi
done

echo "Code combined into $OUTPUT_FILE"

