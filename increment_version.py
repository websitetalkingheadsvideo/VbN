#!/usr/bin/env python3
"""
Automated Version Increment Script
Follows the project's version increment rules from VERSION.md
"""

import re
import os
from datetime import datetime

def get_current_version():
    """Read current version from VERSION.md"""
    version_file = 'VERSION.md'
    if not os.path.exists(version_file):
        print("Error: VERSION.md not found")
        return None
    
    with open(version_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Extract version from "## Version X.X.X (Current)" pattern
    match = re.search(r'## Version (\d+\.\d+\.\d+) \(Current\)', content)
    if match:
        return match.group(1)
    else:
        print("Error: Could not find current version in VERSION.md")
        return None

def increment_version(version, increment_type='patch'):
    """Increment version based on type"""
    parts = version.split('.')
    major, minor, patch = int(parts[0]), int(parts[1]), int(parts[2])
    
    if increment_type == 'patch':
        patch += 1
    elif increment_type == 'minor':
        minor += 1
        patch = 0
    elif increment_type == 'major':
        major += 1
        minor = 0
        patch = 0
    else:
        print(f"Error: Unknown increment type '{increment_type}'")
        return None
    
    return f"{major}.{minor}.{patch}"

def update_version_file(new_version, increment_type):
    """Update VERSION.md with new version"""
    version_file = 'VERSION.md'
    
    with open(version_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Replace current version line
    pattern = r'## Version \d+\.\d+\.\d+ \(Current\)'
    replacement = f'## Version {new_version} (Current)'
    new_content = re.sub(pattern, replacement, content)
    
    # Add new version entry
    new_entry = f"""## Version {new_version} (Current)
**Date:** {datetime.now().strftime('%B %d, %Y')}

### Changes:
- Auto-increment {increment_type} version

---

"""
    
    # Insert new entry after the title
    new_content = re.sub(
        r'(# LOTN Character Creator - Version History\n)',
        r'\1\n' + new_entry,
        new_content
    )
    
    with open(version_file, 'w', encoding='utf-8') as f:
        f.write(new_content)
    
    print(f"Updated VERSION.md to version {new_version}")

def main():
    """Main function"""
    import sys
    
    # Get increment type from command line
    increment_type = sys.argv[1] if len(sys.argv) > 1 else 'patch'
    
    if increment_type not in ['patch', 'minor', 'major']:
        print("Error: Increment type must be 'patch', 'minor', or 'major'")
        print("Usage: python increment_version.py [patch|minor|major]")
        sys.exit(1)
    
    # Get current version
    current_version = get_current_version()
    if not current_version:
        sys.exit(1)
    
    print(f"Current version: {current_version}")
    
    # Increment version
    new_version = increment_version(current_version, increment_type)
    if not new_version:
        sys.exit(1)
    
    print(f"New version: {new_version} ({increment_type} increment)")
    
    # Update VERSION.md
    update_version_file(new_version, increment_type)
    
    print(f"Version increment complete!")
    print(f"Version updated from {current_version} to {new_version} ({increment_type} increment)")
    
    # Show version increment rules
    print("\nVersion Increment Rules:")
    print("• PATCH (Z): Bug fixes, small improvements, work-in-progress features")
    print("• MINOR (Y): New WORKING features, complete systems, major UI overhauls")
    print("• MAJOR (X): Only when explicitly requested")

if __name__ == "__main__":
    main()
