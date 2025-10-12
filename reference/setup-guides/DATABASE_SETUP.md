# LOTN Database Integration Setup Guide

This guide will help you set up the database integration for the LOTN Character Creator.

## Prerequisites

- XAMPP installed and running
- MySQL service running
- phpMyAdmin accessible

## Step 1: Create Database Tables

1. Open phpMyAdmin in your browser: `http://localhost/phpmyadmin`
2. Select the `lotn_characters` database
3. Go to the "SQL" tab
4. Copy and paste the contents of `setup_xampp.sql`
5. Click "Go" to execute the script

This will create the following new tables:
- `disciplines` - Master list of all 22 disciplines
- `discipline_powers` - All 110 individual powers (5 per discipline)
- `clans` - All 14 clans with descriptions and availability
- `clan_disciplines` - Mapping of which disciplines each clan can access
- `character_discipline_powers` - New table for storing character's selected powers

## Step 2: Populate Database with Data

1. Open a command prompt or terminal
2. Navigate to your project directory
3. Run the population script:

```bash
php populate_discipline_data.php
```

This will insert:
- 22 disciplines with their categories
- 110 discipline powers with descriptions
- 14 clans with full information
- All clan-discipline access mappings

## Step 3: Test the Integration

1. Open your browser and navigate to: `http://localhost/test_database_integration.php`
2. Verify all tests show ✅ (green checkmarks)
3. If any tests fail, check the error messages and troubleshoot

## Step 4: Test the API

1. Open your browser and navigate to: `http://localhost/api_disciplines.php?action=all`
2. You should see a JSON response with all discipline data
3. Test other endpoints:
   - `http://localhost/api_disciplines.php?action=disciplines`
   - `http://localhost/api_disciplines.php?action=clans`
   - `http://localhost/api_disciplines.php?action=clan-disciplines`

## Step 5: Test the Frontend

1. Open your browser and navigate to: `http://localhost/lotn_char_create.php`
2. Open the browser's developer console (F12)
3. Look for the message: "✅ Discipline data loaded from database"
4. Test the discipline selection to ensure everything works

## Troubleshooting

### Common Issues:

1. **"Table doesn't exist" errors**
   - Make sure you ran the `setup_xampp.sql` script first
   - Check that you're using the correct database name

2. **"Connection failed" errors**
   - Ensure XAMPP MySQL service is running
   - Check that the database credentials in `includes/connect.php` are correct

3. **"API endpoint not working"**
   - Make sure Apache is running
   - Check that the `api_disciplines.php` file is in the correct location

4. **"Fallback data being used"**
   - Check the browser console for error messages
   - Verify the API endpoint is accessible
   - Ensure the database has been populated with data

### Manual Database Check:

1. Open phpMyAdmin
2. Select the `lotn_characters` database
3. Check that these tables exist and have data:
   - `disciplines` (should have 22 records)
   - `discipline_powers` (should have 110 records)
   - `clans` (should have 14 records)
   - `clan_disciplines` (should have many records)

## Benefits of Database Integration

- **Maintainability**: Discipline data can be updated without modifying JavaScript
- **Scalability**: Easy to add new disciplines, powers, or clans
- **Consistency**: Single source of truth for all discipline data
- **Admin Control**: Admins can modify discipline data through database
- **Performance**: Data is loaded once and cached in JavaScript
- **Fallback System**: App works even if database is unavailable

## File Structure

```
project/
├── api_disciplines.php          # API endpoint for discipline data
├── populate_discipline_data.php # Script to populate database
├── test_database_integration.php # Test script for verification
├── DATABASE_SETUP.md           # This setup guide
├── setup_xampp.sql            # Database schema
├── includes/
│   └── connect.php            # Database connection
└── js/
    └── script.js              # Updated to load from database
```

## Next Steps

After successful setup, you can:
1. Modify discipline data through the database
2. Add new disciplines or powers
3. Update clan information
4. Implement admin panels for data management
5. Add character saving/loading functionality

## Support

If you encounter issues:
1. Check the browser console for error messages
2. Verify all prerequisites are met
3. Run the test script to identify specific problems
4. Check the database directly in phpMyAdmin
