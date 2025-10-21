# LOTN Character Creator - Development Setup

This guide will help you set up XAMPP and Python integration for the Laws of the Night Character Creator.

## Prerequisites

- **XAMPP** installed and running
- **Python 3.7+** installed
- **Git** (optional, for version control)

## Quick Start

1. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL** services
   - Ensure both are running (green status)

2. **Set Up Database**
   - Access database at vdb5.pit.pair.com
   - Create a new database called `lotn_characters`
   - Import the `setup_xampp.sql` file or run the SQL commands

3. **Start Development Environment**
   - Double-click `start_development.bat`
   - This will:
     - Set up Python virtual environment
     - Install required packages
     - Start the Python API server

4. **Access the Application**
   - **Production Site**: http://vbn.talkingheads.video/
   - **Development**: http://vbn.talkingheads.video/
   - **Python API**: http://vbn.talkingheads.video/api/health

## Manual Setup

### Database Setup

1. **Create Database**
   ```sql
   CREATE DATABASE lotn_characters;
   ```

2. **Import Schema**
   - Use phpMyAdmin to import `setup_xampp.sql`
   - Or run the SQL commands manually

3. **Verify Tables**
   - Check that all tables are created
   - Default admin user: `admin` / `password`

### Python API Setup

1. **Create Virtual Environment**
   ```bash
   python -m venv venv
   venv\Scripts\activate  # Windows
   # or
   source venv/bin/activate  # Linux/Mac
   ```

2. **Install Dependencies**
   ```bash
   pip install -r requirements.txt
   ```

3. **Start API Server**
   ```bash
   python python_api.py
   ```

### PHP Configuration

The `includes/connect.php` file is already configured for XAMPP:
- Host: `vdb5.pit.pair.com`
- Username: `root`
- Password: (empty)
- Database: `lotn_characters`

## File Structure

```
VbN/
├── includes/
│   └── connect.php          # Database connection (XAMPP)
├── js/
│   └── script.js            # Frontend JavaScript with API integration
├── python_api.py            # Python Flask API server
├── requirements.txt          # Python dependencies
├── setup_xampp.sql          # Database schema
├── start_development.bat    # Windows development starter
├── config.env               # Environment configuration template
└── README_SETUP.md          # This file
```

## API Endpoints

The Python API provides these endpoints:

- `GET /api/health` - Health check
- `GET /api/characters?user_id=X` - Get user's characters
- `POST /api/characters` - Create new character
- `PUT /api/characters/{id}` - Update character
- `DELETE /api/characters/{id}` - Delete character
- `GET /api/traits?category=X` - Get available traits
- `POST /api/xp/calculate` - Calculate XP costs

## Troubleshooting

### XAMPP Issues
- **MySQL won't start**: Check if port 3306 is in use
- **Apache won't start**: Check if port 80 is in use
- **Permission errors**: Run XAMPP as Administrator

### Python Issues
- **Module not found**: Make sure virtual environment is activated
- **Port 5000 in use**: Change port in `python_api.py`
- **Database connection failed**: Verify MySQL is running and database exists

### Database Issues
- **Connection refused**: Check MySQL is running in XAMPP
- **Access denied**: Verify username/password in `connect.php`
- **Table doesn't exist**: Run the SQL setup script

## Development Workflow

1. **Start XAMPP** (Apache + MySQL)
2. **Run `start_development.bat`** (starts Python API)
3. **Open browser** to http://vbn.talkingheads.video/
4. **Make changes** to PHP/JavaScript files
5. **Test API** at http://vbn.talkingheads.video/api/health

## Production Deployment

For production deployment:

1. **Update database credentials** in `connect.php`
2. **Set up proper Python environment** on server
3. **Configure web server** (Apache/Nginx) to serve PHP
4. **Set up Python WSGI** for API server
5. **Update API_BASE_URL** in `script.js` to production URL (http://vbn.talkingheads.video/)

## Support

If you encounter issues:
1. Check XAMPP Control Panel for service status
2. Verify database connection in phpMyAdmin
3. Check Python API logs in terminal
4. Review browser console for JavaScript errors
