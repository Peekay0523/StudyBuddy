# StudySmart PHP Application

## Quick Start

1. Open a terminal in this directory
2. Run: `php -S localhost:8000 -t public public/index.php`
3. Open http://localhost:8000 in your browser

## Default Credentials

After registering a new account, you can:
- Upload scripts for AI analysis
- Create study plans
- Upload report cards for career recommendations
- Chat with the AI assistant

## AI Features

To enable AI features, set the OPENAI_API_KEY environment variable:

Windows (PowerShell):
```powershell
$env:OPENAI_API_KEY="your-api-key"
```

Windows (Command Prompt):
```cmd
set OPENAI_API_KEY=your-api-key
```

Linux/Mac:
```bash
export OPENAI_API_KEY=your-api-key
```
