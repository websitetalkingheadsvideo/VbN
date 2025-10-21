#!/usr/bin/env python3
"""
LOTN Character Creator - Python API
Provides data processing and API endpoints for character management
"""

from flask import Flask, request, jsonify
import mysql.connector
import json
import os
from datetime import datetime
import hashlib
import secrets

app = Flask(__name__)

# Database configuration
DB_CONFIG = {
    'host': 'vdb5.pit.pair.com',
    'user': 'root',
    'password': '',
    'database': 'lotn_characters',
    'charset': 'utf8mb4'
}

def get_db_connection():
    """Get database connection"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        return conn
    except mysql.connector.Error as err:
        print(f"Database connection error: {err}")
        return None

@app.route('/api/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'timestamp': datetime.now().isoformat(),
        'version': '0.2.0'
    })

@app.route('/api/characters', methods=['GET'])
def get_characters():
    """Get all characters for a user"""
    user_id = request.args.get('user_id')
    if not user_id:
        return jsonify({'error': 'user_id required'}), 400
    
    conn = get_db_connection()
    if not conn:
        return jsonify({'error': 'Database connection failed'}), 500
    
    try:
        cursor = conn.cursor(dictionary=True)
        
        # Get basic character info
        query = """
        SELECT c.*, u.username 
        FROM characters c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.user_id = %s
        ORDER BY c.created_at DESC
        """
        cursor.execute(query, (user_id,))
        characters = cursor.fetchall()
        
        # Get traits for each character
        for character in characters:
            traits_query = """
            SELECT trait_name, trait_category, trait_type, xp_cost
            FROM character_traits 
            WHERE character_id = %s
            """
            cursor.execute(traits_query, (character['id'],))
            character['traits'] = cursor.fetchall()
        
        return jsonify({
            'characters': characters,
            'count': len(characters)
        })
    
    except mysql.connector.Error as err:
        return jsonify({'error': f'Database error: {err}'}), 500
    finally:
        if conn:
            conn.close()

@app.route('/api/characters', methods=['POST'])
def create_character():
    """Create a new character"""
    data = request.get_json()
    
    required_fields = ['user_id', 'character_name', 'player_name', 'nature', 'demeanor', 'concept', 'clan', 'generation']
    for field in required_fields:
        if field not in data:
            return jsonify({'error': f'Missing required field: {field}'}), 400
    
    conn = get_db_connection()
    if not conn:
        return jsonify({'error': 'Database connection failed'}), 500
    
    try:
        cursor = conn.cursor()
        
        # Insert character
        character_query = """
        INSERT INTO characters (user_id, character_name, player_name, chronicle, nature, demeanor, concept, clan, generation, sire, pc, total_xp, spent_xp)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        
        character_values = (
            data['user_id'],
            data['character_name'],
            data['player_name'],
            data.get('chronicle', 'Valley by Night'),
            data['nature'],
            data['demeanor'],
            data['concept'],
            data['clan'],
            data['generation'],
            data.get('sire', ''),
            data.get('pc', True),
            data.get('total_xp', 30),
            data.get('spent_xp', 0)
        )
        
        cursor.execute(character_query, character_values)
        character_id = cursor.lastrowid
        
        # Insert traits if provided
        if 'traits' in data:
            for trait in data['traits']:
                trait_query = """
                INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type, xp_cost)
                VALUES (%s, %s, %s, %s, %s)
                """
                trait_values = (
                    character_id,
                    trait['name'],
                    trait['category'],
                    trait.get('type', 'positive'),
                    trait.get('xp_cost', 0)
                )
                cursor.execute(trait_query, trait_values)
        
        conn.commit()
        
        return jsonify({
            'success': True,
            'character_id': character_id,
            'message': 'Character created successfully'
        })
    
    except mysql.connector.Error as err:
        conn.rollback()
        return jsonify({'error': f'Database error: {err}'}), 500
    finally:
        if conn:
            conn.close()

@app.route('/api/characters/<int:character_id>', methods=['PUT'])
def update_character(character_id):
    """Update an existing character"""
    data = request.get_json()
    
    conn = get_db_connection()
    if not conn:
        return jsonify({'error': 'Database connection failed'}), 500
    
    try:
        cursor = conn.cursor()
        
        # Update character basic info
        update_fields = []
        update_values = []
        
        for field in ['character_name', 'player_name', 'chronicle', 'nature', 'demeanor', 'concept', 'clan', 'generation', 'sire', 'pc', 'total_xp', 'spent_xp']:
            if field in data:
                update_fields.append(f"{field} = %s")
                update_values.append(data[field])
        
        if update_fields:
            update_values.append(character_id)
            update_query = f"""
            UPDATE characters 
            SET {', '.join(update_fields)}, updated_at = NOW()
            WHERE id = %s
            """
            cursor.execute(update_query, update_values)
        
        # Update traits if provided
        if 'traits' in data:
            # Delete existing traits
            cursor.execute("DELETE FROM character_traits WHERE character_id = %s", (character_id,))
            
            # Insert new traits
            for trait in data['traits']:
                trait_query = """
                INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type, xp_cost)
                VALUES (%s, %s, %s, %s, %s)
                """
                trait_values = (
                    character_id,
                    trait['name'],
                    trait['category'],
                    trait.get('type', 'positive'),
                    trait.get('xp_cost', 0)
                )
                cursor.execute(trait_query, trait_values)
        
        conn.commit()
        
        return jsonify({
            'success': True,
            'message': 'Character updated successfully'
        })
    
    except mysql.connector.Error as err:
        conn.rollback()
        return jsonify({'error': f'Database error: {err}'}), 500
    finally:
        if conn:
            conn.close()

@app.route('/api/characters/<int:character_id>', methods=['DELETE'])
def delete_character(character_id):
    """Delete a character"""
    conn = get_db_connection()
    if not conn:
        return jsonify({'error': 'Database connection failed'}), 500
    
    try:
        cursor = conn.cursor()
        cursor.execute("DELETE FROM characters WHERE id = %s", (character_id,))
        
        if cursor.rowcount == 0:
            return jsonify({'error': 'Character not found'}), 404
        
        conn.commit()
        
        return jsonify({
            'success': True,
            'message': 'Character deleted successfully'
        })
    
    except mysql.connector.Error as err:
        conn.rollback()
        return jsonify({'error': f'Database error: {err}'}), 500
    finally:
        if conn:
            conn.close()

@app.route('/api/traits', methods=['GET'])
def get_traits():
    """Get available traits by category"""
    category = request.args.get('category', 'all')
    
    # Define trait data (in a real app, this would come from database)
    traits = {
        'Physical': [
            {'name': 'Agile', 'xp_cost': 0},
            {'name': 'Strong', 'xp_cost': 0},
            {'name': 'Tough', 'xp_cost': 0},
            {'name': 'Quick', 'xp_cost': 0},
            {'name': 'Coordinated', 'xp_cost': 0},
            {'name': 'Alert', 'xp_cost': 0},
            {'name': 'Athletic', 'xp_cost': 0},
        ],
        'Social': [
            {'name': 'Charming', 'xp_cost': 0},
            {'name': 'Persuasive', 'xp_cost': 0},
            {'name': 'Charismatic', 'xp_cost': 0},
            {'name': 'Cunning', 'xp_cost': 0},
            {'name': 'Sociable', 'xp_cost': 0},
            {'name': 'Commanding', 'xp_cost': 0},
            {'name': 'Elegant', 'xp_cost': 0},
        ],
        'Mental': [
            {'name': 'Intelligent', 'xp_cost': 0},
            {'name': 'Clever', 'xp_cost': 0},
            {'name': 'Observant', 'xp_cost': 0},
            {'name': 'Strategic', 'xp_cost': 0},
            {'name': 'Curious', 'xp_cost': 0},
            {'name': 'Calm', 'xp_cost': 0},
            {'name': 'Creative', 'xp_cost': 0},
        ]
    }
    
    if category == 'all':
        return jsonify(traits)
    elif category in traits:
        return jsonify({category: traits[category]})
    else:
        return jsonify({'error': 'Invalid category'}), 400

@app.route('/api/xp/calculate', methods=['POST'])
def calculate_xp():
    """Calculate XP costs for character traits"""
    data = request.get_json()
    
    if 'traits' not in data:
        return jsonify({'error': 'traits required'}), 400
    
    total_xp = 0
    breakdown = {
        'Physical': 0,
        'Social': 0,
        'Mental': 0,
        'negative_bonus': 0
    }
    
    for trait in data['traits']:
        category = trait.get('category', 'Physical')
        trait_type = trait.get('type', 'positive')
        xp_cost = trait.get('xp_cost', 0)
        
        if trait_type == 'negative':
            breakdown['negative_bonus'] += 4  # Negative traits give +4 XP
        else:
            breakdown[category] += xp_cost
    
    total_xp = sum(breakdown.values()) - breakdown['negative_bonus']  # Subtract bonus
    
    return jsonify({
        'total_xp': total_xp,
        'breakdown': breakdown,
        'remaining_xp': 30 - total_xp
    })

if __name__ == '__main__':
    print("Starting LOTN Character Creator API...")
    print("Make sure MySQL is running on vdb5.pit.pair.com:3306")
    print("API will be available at: http://vbn.talkingheads.video/api")
    app.run(debug=True, host='0.0.0.0', port=5000)
