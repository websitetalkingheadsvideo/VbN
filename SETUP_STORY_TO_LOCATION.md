# 🚀 Quick Setup Guide - Story to Location Feature

## Step 1: Add API Key to config.env

Your project already has a `config.env` file! Just edit it and add your API key:

1. Open `config.env` in your project root
2. Find the AI API Keys section (already added)
3. Replace `your_anthropic_api_key_here` with your actual key

**Get an Anthropic API Key:**
1. Visit: https://console.anthropic.com/
2. Sign up or log in
3. Go to "API Keys" section
4. Create a new key
5. Copy and paste into `config.env`

**Example:**
```bash
# AI API Keys (for Story to Location feature)
ANTHROPIC_API_KEY=sk-ant-api03-xxxxxxxxxxxxxxxxxxxx
OPENAI_API_KEY=your_openai_api_key_here
```

## Step 2: Verify Configuration

Your `.taskmaster/config.json` is already configured correctly:
- ✅ Provider: Anthropic Claude
- ✅ Model: claude-3-7-sonnet-20250219
- ✅ Max Tokens: 120000
- ✅ Temperature: 0.2

## Step 3: Test the Feature

1. Navigate to: https://www.websitetalkingheads.com/vbn/admin_create_location_story.php
3. Click "Load example narrative" to test
4. Click "Parse Story with AI"
5. Review extracted data
6. Click "Save Location to Database"

## Step 4: Add to Admin Panel

Edit your `admin_panel.php` or `admin_locations.php` to add a link:

```html
<a href="admin_create_location_story.php" class="admin-button">
    <i class="fas fa-magic"></i> Create from Story (AI)
</a>
```

## 🎯 Expected Costs

Using Anthropic Claude Sonnet:
- ~$0.003 per location parse (~2000 tokens)
- Very affordable for personal use!

## 📋 Requirements Checklist

- [x] PHP 7.4+ with cURL enabled
- [x] MySQL database with `locations` table
- [x] Existing `api_create_location.php` working
- [ ] `.env` file with `ANTHROPIC_API_KEY`
- [ ] Internet connection for AI API calls
- [ ] Active Anthropic account with API access

## ✅ Verify Everything Works

Run this test:
1. Open: `admin_create_location_story.php`
2. Paste this test narrative:

```
The Blood Bank sits in Central Phoenix, operating as a legitimate medical 
facility by day. At night, it serves as a secure feeding ground for 
Camarilla vampires. The facility has medical equipment, blood storage 
refrigeration, and security cameras throughout. Access is restricted to 
Camarilla members only, with armed guards protecting the entrance.
```

3. Click "Parse Story with AI"
4. You should see ~30+ fields extracted with confidence scores
5. Edit any field if needed
6. Click "Save to Database"
7. Check `admin_locations.php` to see your new location!

## 🐛 Common Issues

**"AI API key not configured"**
→ Add your API key to `config.env` file in project root

**"cURL error" or "Connection failed"**  
→ Enable PHP cURL extension in php.ini

**"Failed to parse narrative"**
→ Check your API key is valid and has credits

**"Missing required field"**
→ AI didn't extract a required field - edit it manually in preview

## 🎉 You're Done!

The feature is now ready to use. Write location stories, let AI structure them, and save to your database in seconds!

Read `STORY_TO_LOCATION_README.md` for detailed usage instructions.

