# Database Migration Guide: MySQL to PostgreSQL

This guide explains how to migrate your database from MySQL to PostgreSQL.

## Prerequisites

1. Access to both MySQL and PostgreSQL databases
2. Update `.env` file with both database credentials
3. Laravel application set up and working

## Migration Steps

### 1. Run Schema Migrations

Run the migration script to create the PostgreSQL schema:

```bash
php database/pgsql-migrate.php
```

This will create all the necessary tables in your PostgreSQL database based on your Laravel migrations.

### 2. Transfer Data

Before running the data transfer, make sure your MySQL credentials are correctly set in the `.env` file:

```
MYSQL_HOST=127.0.0.1
MYSQL_PORT=3306
MYSQL_DATABASE=your_mysql_database
MYSQL_USERNAME=mysql_user
MYSQL_PASSWORD=mysql_password
```

Then run the data transfer script:

```bash
php database/transfer-data.php
```

This script will:
1. Connect to both MySQL and PostgreSQL databases
2. Copy all data from MySQL tables to PostgreSQL tables
3. Display progress and any errors during the process

### 3. Verify Data

After migration, verify your data by:

1. Checking record counts in both databases
2. Sample checking some records for data integrity
3. Testing your application functionality with the new database

### Troubleshooting

- If you encounter sequence issues in PostgreSQL after migration, you may need to reset sequences:

```sql
SELECT setval(pg_get_serial_sequence('table_name', 'id'),
  (SELECT MAX(id) FROM table_name));
```

- For data type incompatibilities, you might need to modify the transfer-data.php script
  to handle specific data type conversions.
