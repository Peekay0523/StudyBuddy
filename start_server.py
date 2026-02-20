#!/usr/bin/env python
"""
Script to start the Django development server with proper cleanup.
This script will ensure the server stops when you interrupt it with Ctrl+C.
"""

import os
import signal
import sys
from django.core.management import execute_from_command_line


def signal_handler(sig, frame):
    """Handle Ctrl+C gracefully"""
    print('\nShutting down Django development server...')
    sys.exit(0)


def main():
    # Set up signal handler for graceful shutdown
    signal.signal(signal.SIGINT, signal_handler)
    signal.signal(signal.SIGTERM, signal_handler)
    
    print("Starting Django development server...")
    print("Press Ctrl+C to stop the server.")
    
    try:
        # Start the Django development server
        execute_from_command_line([sys.argv[0], 'runserver'] + sys.argv[1:])
    except KeyboardInterrupt:
        print('\nShutting down Django development server...')
    except SystemExit:
        print('Server stopped.')


if __name__ == '__main__':
    # Set the Django settings module
    os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'school_app.settings')
    main()