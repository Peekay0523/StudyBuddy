#!/usr/bin/env python
"""
Script to stop any running Django development server processes.
"""

import os
import subprocess
import sys


def stop_django_processes():
    """Stop any running Django development server processes"""
    try:
        # Find processes running Django development server
        result = subprocess.run(['tasklist'], capture_output=True, text=True, shell=True)
        
        # Look for Python processes that might be running Django
        lines = result.stdout.split('\n')
        django_pids = []
        
        for line in lines:
            if 'python.exe' in line.lower() and ('manage.py' in line or 'runserver' in line or 'start_server.py' in line):
                # Extract PID from the line (second column)
                parts = line.split()
                if len(parts) > 1:
                    try:
                        pid = int(parts[1])
                        django_pids.append(pid)
                    except ValueError:
                        continue
        
        if django_pids:
            print(f"Found {len(django_pids)} Django process(es) running:")
            for pid in django_pids:
                print(f"  - PID: {pid}")
                
            response = input("Do you want to terminate these processes? (y/N): ")
            if response.lower() == 'y':
                for pid in django_pids:
                    try:
                        subprocess.run(['taskkill', '/PID', str(pid), '/F'], check=True, shell=True)
                        print(f"Terminated process {pid}")
                    except subprocess.CalledProcessError:
                        print(f"Could not terminate process {pid}")
            else:
                print("Processes not terminated.")
        else:
            print("No Django processes found running.")
            
    except Exception as e:
        print(f"Error checking for Django processes: {e}")


if __name__ == '__main__':
    print("Checking for running Django processes...")
    stop_django_processes()