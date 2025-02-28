# Run these commands in your terminal
php artisan make:migration add_prepared_by_to_invoices_table --table=invoices
# Now edit the migration file in database/migrations/ to add the prepared_by column
# Then run:
php artisan migrate
