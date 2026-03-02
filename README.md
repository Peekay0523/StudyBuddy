# StudySmart - School Learning Platform (PHP Version)

An AI-powered educational platform designed to help students learn more effectively. This is the PHP conversion of the original Django application.

## Features

- **User Authentication**: Register, login, and logout functionality
- **Script Upload & Analysis**: Upload study scripts (PDF, DOCX, TXT) for AI-powered topic extraction
- **AI Memorandum Generation**: Get AI-generated summaries of your study materials
- **Personalized Study Plans**: Receive customized study plans based on challenging topics
- **Career Recommendations**: Upload report cards to get career suggestions based on academic performance
- **AI Chat Assistant**: Chat with an AI study assistant for help with various subjects

## Requirements

- PHP 8.0 or higher
- SQLite3 (built into PHP)
- cURL extension (for AI features)
- Zip extension (for DOCX file processing)

## Installation

1. **Clone or copy the application** to your desired location:
   ```
   school_app_php/
   ```

2. **Set up the database**:
   The database will be automatically created when you first run the application.

3. **Configure environment variables** (optional):
   Set the `OPENAI_API_KEY` environment variable for AI features:
   ```bash
   export OPENAI_API_KEY=your-api-key-here
   ```

4. **Start the PHP development server**:
   ```bash
   cd school_app_php
   php -S localhost:8000 -t public public/index.php
   ```

5. **Open your browser** and navigate to:
   ```
   http://localhost:8000
   ```

## Project Structure

```
school_app_php/
├── config/
│   ├── config.php          # Application configuration
│   └── database.php        # Database connection (PDO)
├── controllers/
│   ├── AuthController.php  # Authentication (login, register, logout)
│   ├── HomeController.php  # Home page
│   ├── DashboardController.php
│   ├── ScriptController.php
│   ├── StudyPlanController.php
│   ├── ReportCardController.php
│   └── AIChatController.php
├── models/
│   ├── User.php
│   ├── Student.php
│   ├── UploadedScript.php
│   ├── Memorandum.php
│   ├── StudyPlan.php
│   ├── ReportCard.php
│   └── CareerRecommendation.php
├── helpers/
│   ├── AIHelper.php        # OpenAI API integration
│   └── FileHelper.php      # File upload and text extraction
├── templates/
│   ├── layouts/
│   │   ├── header.php
│   │   └── footer.php
│   ├── pages/
│   │   ├── home.php
│   │   ├── dashboard.php
│   │   ├── upload_script.php
│   │   ├── study_plan.php
│   │   ├── ai_chat.php
│   │   ├── upload_report_card.php
│   │   ├── view_memorandum.php
│   │   ├── view_study_plan.php
│   │   └── view_career_recommendations.php
│   └── auth/
│       ├── login.php
│       └── register.php
├── public/
│   ├── index.php           # Main entry point (router)
│   └── css/
│       └── style.css       # Application styles
├── uploads/
│   ├── scripts/            # Uploaded script files
│   └── report_cards/       # Uploaded report card files
├── router.php              # Simple URL router
├── database_schema.sql     # Database schema
└── database.sqlite3        # SQLite database (auto-created)
```

## Database Schema

The application uses SQLite with the following tables:

- **users**: User authentication
- **students**: Student profiles linked to users
- **uploaded_scripts**: Uploaded study scripts
- **memorandums**: AI-generated memorandums
- **study_plans**: Personalized study plans
- **report_cards**: Uploaded report cards
- **career_recommendations**: AI-generated career suggestions

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Home page |
| GET/POST | `/login` | User login |
| GET/POST | `/register` | User registration |
| GET | `/logout` | User logout |
| GET | `/dashboard` | User dashboard |
| GET/POST | `/upload-script` | Upload study script |
| GET | `/view-memorandum/{id}` | View memorandum |
| GET | `/study-plan` | View study plans |
| GET | `/view-study-plan/{id}` | View single study plan |
| GET/POST | `/upload-report-card` | Upload report card |
| GET | `/view-career-recommendations/{id}` | View career recommendations |
| GET | `/ai-chat` | AI chat interface |
| POST | `/api/chatbot` | Chat API endpoint |

## AI Integration

The application uses OpenAI's GPT models for:
- Topic extraction from documents
- Identifying challenging topics
- Generating memorandums
- Creating personalized study plans
- Career recommendations
- Chat assistant

To enable AI features, set the `OPENAI_API_KEY` environment variable.

## Security Notes

- Passwords are hashed using `password_hash()` with `PASSWORD_DEFAULT`
- Sessions use HTTP-only cookies
- File uploads are validated for type and size
- SQL injection prevention via PDO prepared statements

## Differences from Django Version

| Django | PHP |
|--------|-----|
| ORM (Active Record) | Raw SQL with PDO |
| Django Templates | PHP templates |
| Class-based views | Controller classes |
| URL patterns | Simple router |
| Built-in auth | Custom session auth |
| Forms | HTML forms with validation |

## Troubleshooting

**Database not created:**
- Ensure the application has write permissions in the project directory

**AI features not working:**
- Check that `OPENAI_API_KEY` is set correctly
- Verify cURL extension is enabled

**File upload fails:**
- Check `upload_max_filesize` and `post_max_size` in php.ini
- Ensure the `uploads/` directory is writable

## License

This project is a conversion of the original Django application to PHP.
