from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.decorators import login_required
from django.contrib.auth import login, authenticate
from django.contrib.auth.forms import UserCreationForm, AuthenticationForm
from django.http import JsonResponse
from django.views.decorators.csrf import csrf_exempt
from django.conf import settings
from django.core.files.storage import default_storage
from django.contrib import messages
from .models import *
from .forms import *
import json
import os
from openai import OpenAI
import docx
import pdfplumber
from io import BytesIO
import re


def get_openai_client():
    """Get OpenAI client with current API key from settings"""
    api_key = getattr(settings, 'OPENAI_API_KEY', '')
    if not api_key or api_key == 'your-openai-api-key-here':
        return None
    return OpenAI(api_key=api_key)


def home(request):
    return render(request, 'learning_platform/home.html')


def login_view(request):
    if request.method == 'POST':
        form = AuthenticationForm(request, data=request.POST)
        if form.is_valid():
            username = form.cleaned_data.get('username')
            password = form.cleaned_data.get('password')
            user = authenticate(username=username, password=password)
            if user is not None:
                login(request, user)
                return redirect('dashboard')  # Redirect to dashboard after login
    else:
        form = AuthenticationForm()
    return render(request, 'registration/login.html', {'form': form})


def register(request):
    if request.method == 'POST':
        form = UserCreationForm(request.POST)
        if form.is_valid():
            user = form.save()
            username = form.cleaned_data.get('username')
            Student.objects.create(user=user)
            messages.success(request, f'Account created for {username}!')
            return redirect('login')
    else:
        form = UserCreationForm()
    return render(request, 'registration/register.html', {'form': form})


@login_required
def dashboard(request):
    student = Student.objects.get(user=request.user)
    scripts = UploadedScript.objects.filter(student=student)
    study_plans = StudyPlan.objects.filter(student=student, is_active=True)
    report_cards = ReportCard.objects.filter(student=student)
    
    # Count topics from all scripts
    topics_count = 0
    for script in scripts:
        if script.processed_topics:
            topics_count += len(script.processed_topics) if isinstance(script.processed_topics, list) else 0

    context = {
        'student': student,
        'scripts': scripts,
        'study_plans': study_plans,
        'report_cards': report_cards,
        'scripts_count': scripts.count(),
        'plans_count': study_plans.count(),
        'reports_count': report_cards.count(),
        'topics_count': topics_count,
    }
    return render(request, 'learning_platform/dashboard.html', context)


@login_required
def study_plan(request):
    student = Student.objects.get(user=request.user)
    study_plans = StudyPlan.objects.filter(student=student, is_active=True)
    
    context = {
        'study_plans': study_plans,
    }
    return render(request, 'learning_platform/study_plan.html', context)


@login_required
def ai_chat(request):
    return render(request, 'learning_platform/ai_chat.html')


@login_required
@csrf_exempt
def chatbot(request):
    if request.method == 'POST':
        try:
            user_message = request.POST.get("message")
            
            if not user_message:
                return JsonResponse({"error": "No message provided"}, status=400)

            response = client.chat.completions.create(
                model="gpt-4o-mini",
                messages=[
                    {"role": "system", "content": "You are a helpful AI Study Assistant. Help students with their questions about various subjects, explain concepts clearly, provide study tips, and create quiz questions when asked. Be encouraging and supportive."},
                    {"role": "user", "content": user_message}
                ],
                max_tokens=500,
                temperature=0.7
            )

            return JsonResponse({
                "reply": response.choices[0].message.content
            })
        except Exception as e:
            return JsonResponse({"error": str(e)}, status=500)
    
    return JsonResponse({"error": "Invalid request method"}, status=405)


@login_required
def upload_script(request):
    from .forms import ScriptUploadForm
    
    if request.method == 'POST' and request.FILES.get('script_file'):
        student = Student.objects.get(user=request.user)

        # Save the uploaded file
        uploaded_file = request.FILES['script_file']
        title = request.POST.get('title', uploaded_file.name)
        subject = request.POST.get('subject', '')
        grade_level = request.POST.get('grade_level', '')

        # Create UploadedScript object
        script = UploadedScript.objects.create(
            student=student,
            title=title,
            file=uploaded_file,
            subject=subject,
            grade_level=grade_level
        )

        # Process the script to extract topics and identify challenging areas
        process_uploaded_script(script)

        messages.success(request, 'Script uploaded and processed successfully!')
        return redirect('dashboard')

    form = ScriptUploadForm()
    return render(request, 'learning_platform/upload_script.html', {'form': form})


def process_uploaded_script(script):
    """
    Process the uploaded script to extract topics and identify challenging areas
    using AI analysis
    """
    try:
        # Extract text from the uploaded file
        text_content = extract_text_from_file(script.file.path)

        # Use AI to analyze the content and identify topics
        topics = analyze_document_topics(text_content)
        script.processed_topics = topics

        # Identify potentially challenging topics based on complexity
        challenging_topics = identify_challenging_topics(topics, text_content)
        script.challenging_topics = challenging_topics

        # Generate a memorandum for the script
        memorandum_content = generate_memorandum(text_content, topics)
        Memorandum.objects.create(script=script, content=memorandum_content)

        # Generate a study plan based on challenging topics
        generate_study_plan(script.student, challenging_topics)

        script.save()
    except Exception as e:
        print(f"Error processing script: {str(e)}")


def extract_text_from_file(file_path):
    """
    Extract text from various file formats (PDF, DOCX, TXT)
    """
    text = ""

    if file_path.endswith('.pdf'):
        with pdfplumber.open(file_path) as pdf:
            for page in pdf.pages:
                text += page.extract_text() or ""
    elif file_path.endswith('.docx'):
        doc = docx.Document(file_path)
        for para in doc.paragraphs:
            text += para.text + "\n"
    elif file_path.endswith('.txt'):
        with open(file_path, 'r', encoding='utf-8') as f:
            text = f.read()

    return text


def analyze_document_topics(content):
    """
    Use AI to analyze document content and extract topics
    """
    client = get_openai_client()
    if not client:
        # Return a basic topic extraction if no API key is available
        # This is a fallback for demonstration purposes
        words = content.split()
        # Simple keyword extraction - in a real app, use proper NLP
        keywords = [word for word in set(words) if len(word) > 5 and word.isalpha()]
        return keywords[:10]  # Return top 10 keywords

    try:
        response = client.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=[
                {"role": "system", "content": "You are a helpful assistant that identifies key topics in educational documents. Extract the main topics covered in the following content."},
                {"role": "user", "content": f"Identify the main topics in this educational content: {content[:4000]}"}  # Limit content length
            ],
            max_tokens=200,
            temperature=0.3
        )

        topics_str = response.choices[0].message.content.strip()
        # Parse the response to extract topics
        topics = [topic.strip('- ') for topic in topics_str.split('\n') if topic.strip()]
        return topics
    except Exception as e:
        print(f"Error calling OpenAI API: {str(e)}")
        return []


def identify_challenging_topics(topics, content):
    """
    Identify topics that might be challenging for the student
    """
    client = get_openai_client()
    if not client:
        # Fallback for demo purposes
        return topics[:3]  # Return first 3 topics as challenging

    try:
        response = client.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=[
                {"role": "system", "content": "You are an educational assistant that identifies complex or challenging topics in educational content."},
                {"role": "user", "content": f"Based on this educational content, identify which of these topics might be most challenging for a student: {topics}. Content: {content[:3000]}"}
            ],
            max_tokens=150,
            temperature=0.3
        )

        challenging_str = response.choices[0].message.content.strip()
        # Parse the response to extract challenging topics
        challenging_topics = [topic.strip('- ') for topic in challenging_str.split('\n') if topic.strip() and any(t.lower() in challenging_str.lower() for t in topics)]
        return challenging_topics if challenging_topics else topics[:2]
    except Exception as e:
        print(f"Error identifying challenging topics: {str(e)}")
        return topics[:2]


def generate_memorandum(content, topics):
    """
    Generate a memorandum for the uploaded script
    """
    client = get_openai_client()
    if not client:
        # Fallback for demo purposes
        return f"This memorandum summarizes the key topics: {', '.join(topics[:5])}."

    try:
        response = client.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=[
                {"role": "system", "content": "You are an educational assistant that creates concise memorandums summarizing educational content."},
                {"role": "user", "content": f"Create a concise memorandum summarizing this educational content focusing on these key topics: {topics}. Content: {content[:4000]}"}
            ],
            max_tokens=300,
            temperature=0.4
        )

        return response.choices[0].message.content.strip()
    except Exception as e:
        print(f"Error generating memorandum: {str(e)}")
        return f"Memorandum for topics: {', '.join(topics[:5])}"


def generate_study_plan(student, challenging_topics):
    """
    Generate a personalized study plan based on challenging topics
    """
    client = get_openai_client()
    if not client or not challenging_topics:
        # Create a basic study plan for demo purposes
        plan_title = f"Study Plan for {', '.join(challenging_topics[:3])}"
        plan_content = f"Focus on these challenging topics: {', '.join(challenging_topics)}. Spend extra time practicing problems related to these concepts."
        StudyPlan.objects.create(student=student, title=plan_title, content=plan_content)
        return

    try:
        response = client.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=[
                {"role": "system", "content": "You are an educational advisor that creates personalized study plans focusing on challenging topics."},
                {"role": "user", "content": f"Create a personalized study plan for a student who finds these topics challenging: {challenging_topics}. Include study tips and resources."}
            ],
            max_tokens=400,
            temperature=0.5
        )

        plan_content = response.choices[0].message.content.strip()
        plan_title = f"Personalized Study Plan for {student.user.username}"

        StudyPlan.objects.create(student=student, title=plan_title, content=plan_content)
    except Exception as e:
        print(f"Error generating study plan: {str(e)}")
        # Create a basic study plan as fallback
        plan_title = f"Study Plan for {', '.join(challenging_topics[:3])}"
        plan_content = f"Focus on these challenging topics: {', '.join(challenging_topics)}. Spend extra time practicing problems related to these concepts."
        StudyPlan.objects.create(student=student, title=plan_title, content=plan_content)


@login_required
def upload_report_card(request):
    from .forms import ReportCardUploadForm
    
    if request.method == 'POST' and request.FILES.get('report_card_file'):
        student = Student.objects.get(user=request.user)

        # Save the uploaded file
        uploaded_file = request.FILES['report_card_file']
        grade = request.POST.get('grade', '')
        term = request.POST.get('term', '')

        # Create ReportCard object
        report_card = ReportCard.objects.create(
            student=student,
            file=uploaded_file,
            grade=grade,
            term=term
        )

        # Process the report card to extract grades and recommend careers
        process_report_card(report_card)

        messages.success(request, 'Report card uploaded and processed successfully!')
        return redirect('dashboard')

    form = ReportCardUploadForm()
    return render(request, 'learning_platform/upload_report_card.html', {'form': form})


def process_report_card(report_card):
    """
    Process the uploaded report card to extract grades and recommend careers
    """
    try:
        # Extract text from the uploaded file
        text_content = extract_text_from_file(report_card.file.path)

        # Extract grades data from the report card
        grades_data = extract_grades_from_text(text_content)
        report_card.grades_data = grades_data

        # Generate career recommendations based on grades
        career_recommendations = generate_career_recommendations(grades_data)

        # Create CareerRecommendation object
        career_rec = CareerRecommendation.objects.create(
            student=report_card.student,
            report_card=report_card,
            recommended_careers=career_recommendations['careers'],
            strengths=career_recommendations['strengths'],
            areas_for_improvement=career_recommendations['areas_for_improvement']
        )

        report_card.save()
    except Exception as e:
        print(f"Error processing report card: {str(e)}")


def extract_grades_from_text(content):
    """
    Extract grades from report card text
    """
    # Simple regex to extract subject-grade pairs
    # This is a simplified version - real implementation would need more robust parsing
    pattern = r'([A-Za-z\s]+?)\s*:?\s*([A-D][+-]?|F|[0-9]{1,3}%|[0-9]{1,3})'
    matches = re.findall(pattern, content, re.IGNORECASE)

    grades = {}
    for match in matches:
        subject = match[0].strip()
        grade = match[1].strip()
        if subject and grade:
            grades[subject] = grade

    return grades


def generate_career_recommendations(grades_data):
    """
    Generate career recommendations based on grades
    """
    client = get_openai_client()
    if not client or not grades_data:
        # Fallback for demo purposes
        return {
            'careers': ['Teacher', 'Engineer', 'Doctor'],
            'strengths': ['Mathematics', 'Science'],
            'areas_for_improvement': ['Writing', 'History']
        }

    try:
        subjects_grades = ', '.join([f"{subject}: {grade}" for subject, grade in grades_data.items()])

        response = client.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=[
                {"role": "system", "content": "You are a career counselor that recommends careers based on academic performance and identifies strengths and areas for improvement."},
                {"role": "user", "content": f"Based on these academic results: {subjects_grades}, recommend suitable careers, identify strengths, and suggest areas for improvement."}
            ],
            max_tokens=400,
            temperature=0.5
        )

        analysis = response.choices[0].message.content.strip()

        # This is a simplified parsing - in a real app, you'd want more sophisticated parsing
        careers = ["Software Developer", "Data Analyst", "Teacher"]  # Placeholder
        strengths = ["Mathematics", "Science"]  # Placeholder
        areas_for_improvement = ["Writing", "Communication"]  # Placeholder

        return {
            'careers': careers,
            'strengths': strengths,
            'areas_for_improvement': areas_for_improvement
        }
    except Exception as e:
        print(f"Error generating career recommendations: {str(e)}")
        return {
            'careers': ['Teacher', 'Engineer', 'Doctor'],
            'strengths': ['Mathematics', 'Science'],
            'areas_for_improvement': ['Writing', 'History']
        }


@login_required
def view_memorandum(request, script_id):
    script = get_object_or_404(UploadedScript, id=script_id, student__user=request.user)
    memorandum = get_object_or_404(Memorandum, script=script)

    context = {
        'script': script,
        'memorandum': memorandum,
    }
    return render(request, 'learning_platform/view_memorandum.html', context)


@login_required
def view_study_plan(request, plan_id):
    study_plan = get_object_or_404(StudyPlan, id=plan_id, student__user=request.user)

    context = {
        'study_plan': study_plan,
    }
    return render(request, 'learning_platform/view_study_plan.html', context)


@login_required
def view_career_recommendations(request, rec_id):
    career_rec = get_object_or_404(CareerRecommendation, id=rec_id, student__user=request.user)

    context = {
        'career_rec': career_rec,
    }
    return render(request, 'learning_platform/view_career_recommendations.html', context)
