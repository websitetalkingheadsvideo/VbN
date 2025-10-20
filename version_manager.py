#!/usr/bin/env python3
"""
Version Manager - Simple interface for version operations
"""

import subprocess
import sys
import os

def run_command(cmd):
    """Run a command and return output"""
    try:
        result = subprocess.run(cmd, shell=True, capture_output=True, text=True)
        return result.returncode, result.stdout, result.stderr
    except Exception as e:
        return 1, "", str(e)

def increment_version(increment_type='patch'):
    """Increment version using Python script"""
    print(f"Incrementing {increment_type.upper()} version...")
    
    cmd = f"python increment_version.py {increment_type}"
    returncode, stdout, stderr = run_command(cmd)
    
    if returncode == 0:
        print(stdout)
        return True
    else:
        print(f"Error: {stderr}")
        return False

def get_current_version():
    """Get current version from VERSION.md"""
    version_file = 'VERSION.md'
    if not os.path.exists(version_file):
        return None
    
    with open(version_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    import re
    match = re.search(r'## Version (\d+\.\d+\.\d+) \(Current\)', content)
    return match.group(1) if match else None

def main():
    """Main function for command line usage"""
    if len(sys.argv) < 2:
        print("Version Manager Commands:")
        print("  python version_manager.py increment  - Increment patch version")
        print("  python version_manager.py patch      - Increment patch version")
        print("  python version_manager.py minor      - Increment minor version")
        print("  python version_manager.py major      - Increment major version")
        print("  python version_manager.py status     - Show current version")
        return
    
    command = sys.argv[1]
    
    if command in ['increment', 'patch', 'minor', 'major']:
        increment_type = command if command != 'increment' else 'patch'
        increment_version(increment_type)
    elif command == 'status':
        version = get_current_version()
        if version:
            print(f"Current Version: {version}")
        else:
            print("Could not determine current version")
    else:
        print(f"Unknown command: {command}")

if __name__ == "__main__":
    main()
