# School Learning Platform

An AI-powered educational platform designed to help students learn more effectively by providing personalized learning experiences through script analysis, study planning, and career guidance.

## Features

### 1. Script Analysis and Memorandum Generation
- Students can upload their study scripts (notes, assignments, etc.)
- AI analyzes the content to identify key topics
- Automatically generates memorandums highlighting important concepts
- Identifies challenging topics that need more attention

### 2. Personalized Study Plans
- Creates customized study plans based on identified challenging topics
- Prioritizes areas where the student needs the most help
- Provides resource recommendations for better understanding

### 3. Career Guidance System
- Students can upload their report cards or transcripts
- AI analyzes academic performance to identify strengths and weaknesses
- Provides personalized career recommendations based on academic results
- Highlights areas for improvement

### 4. AI Integration
- Powered by OpenAI GPT for intelligent content analysis
- Natural language processing for topic identification
- Personalized recommendations based on individual performance

## Technology Stack

- **Backend**: Django (Python)
- **Frontend**: HTML, CSS, JavaScript with Bootstrap
- **Database**: SQLite (default) or PostgreSQL
- **AI Integration**: OpenAI API
- **Document Processing**: python-docx, pdfplumber
- **Authentication**: Django built-in auth system

## Setup Instructions

### Prerequisites
- Python 3.8+
- Pip package manager

### Installation Steps

1. Clone or download the project
2. Navigate to the project directory:
   ```bash
   cd SchoolApp
   ```

3. Create a virtual environment:
   ```bash
   python -m venv venv
   ```

4. Activate the virtual environment:
   - On Windows:
     ```bash
     venv\Scripts\activate
     ```
   - On macOS/Linux:
     ```bash
     source venv/bin/activate
     ```

5. Install dependencies:
   ```bash
   pip install -r requirements.txt
   ```

6. Set up the database:
   ```bash
   python manage.py makemigrations
   python manage.py migrate
   ```

7. Create a superuser account (optional):
   ```bash
   python manage.py createsuperuser
   ```

8. Set your OpenAI API key:
   - Option 1: Set environment variable:
     ```bash
     set OPENAI_API_KEY=your_api_key_here  # Windows
     export OPENAI_API_KEY=your_api_key_here  # macOS/Linux
     ```
   - Option 2: Update the settings.py file directly (not recommended for production)

9. Run the development server:
   ```bash
   python manage.py runserver
   ```

10. Access the application at `http://127.0.0.1:8000/`

## Usage Guide

### For Students

1. **Registration**: Create an account using the registration form
2. **Upload Scripts**: Go to "Upload Script" to submit study materials
3. **View Memorandums**: Check generated memorandums for your scripts
4. **Follow Study Plans**: Access personalized study plans from your dashboard
5. **Upload Report Cards**: Submit report cards for career recommendations
6. **Explore Career Options**: View personalized career recommendations

### Supported File Formats
- Scripts: PDF, DOCX, TXT
- Report Cards: PDF, DOCX, JPG, PNG

## Project Structure

```
SchoolApp/
├── manage.py
├── db.sqlite3
├── requirements.txt
├── school_app/
│   ├── __init__.py
│   ├── settings.py
│   ├── urls.py
│   └── wsgi.py
└── learning_platform/
    ├── __init__.py
    ├── admin.py
    ├── apps.py
    ├── models.py
    ├── views.py
    ├── forms.py
    ├── urls.py
    ├── migrations/
    ├── templates/
    │   └── learning_platform/
    └── static/
```

## Key Components

### Models
- `Student`: Represents a student user
- `UploadedScript`: Stores uploaded study scripts
- `Memorandum`: Generated memorandums for scripts
- `StudyPlan`: Personalized study plans
- `ReportCard`: Uploaded report cards
- `CareerRecommendation`: Career recommendations based on academic performance

### Views
- Home page with feature overview
- Registration and login
- Dashboard with activity summary
- Script upload and memorandum viewing
- Report card upload and career recommendations
- Study plan management

## API Integration

The application integrates with OpenAI API for:
- Topic identification in documents
- Challenge detection in learning materials
- Memorandum generation
- Study plan creation
- Career recommendation generation

## Security Considerations

- Passwords are securely hashed using Django's built-in authentication
- File uploads are validated for security
- Environment variables are used for sensitive configuration
- CSRF protection is enabled by default

## Future Enhancements

- Advanced analytics dashboard
- Collaboration features for group studies
- Mobile application development
- Integration with learning management systems
- Enhanced AI capabilities for deeper content analysis
- Gamification elements to increase engagement

## Troubleshooting

### Common Issues

1. **OpenAI API Key Error**: Ensure your API key is correctly set in environment variables
2. **File Upload Issues**: Check that uploaded files are in supported formats
3. **Database Errors**: Run migrations if you encounter database-related errors
4. **Static Files Not Loading**: Collect static files using `python manage.py collectstatic`

### Server Management

If the application continues running after closing VS Code or stopping the debugger:

**Using Management Scripts (Recommended):**

To stop any running server processes:
```
python stop_server.py
```

Or using the batch file:
```
manage_server.bat stop
```

**Traditional Method:**
1. Check Task Manager for any Python processes and end them if necessary
2. Or restart your computer to ensure all processes are terminated

## Contributing

Contributions are welcome! Please feel free to submit a pull request or open an issue for bugs and feature requests.

## License

This project is created for educational purposes. See the LICENSE file for details.