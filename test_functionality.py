"""
Test script for the School Learning Platform
This script verifies that the basic functionality works correctly
"""
import os
import sys
import django
from django.conf import settings

# Add the project directory to Python path
sys.path.append(os.path.join(os.path.dirname(__file__)))

# Configure Django settings
os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'school_app.settings')
django.setup()

from django.contrib.auth.models import User
from learning_platform.models import Student, UploadedScript, Memorandum, StudyPlan, ReportCard, CareerRecommendation

def test_basic_functionality():
    print("Testing basic functionality of School Learning Platform...")

    # Create a test user
    try:
        user, created = User.objects.get_or_create(
            username='test_student',
            defaults={
                'email': 'test@example.com'
            }
        )
        if created:
            user.set_password('test_password123')
            user.save()
            print("[OK] Created test user")
        else:
            print("[INFO] Using existing test user")
    except Exception as e:
        print(f"[ERROR] Failed to get/create test user: {e}")
        return False

    # Create a student profile
    try:
        student = Student.objects.create(user=user, grade_level='Grade 10')
        print("[OK] Created student profile")
    except Exception as e:
        print(f"[ERROR] Failed to create student profile: {e}")
        return False

    # Test creating an uploaded script (without file for now)
    try:
        script = UploadedScript.objects.create(
            student=student,
            title='Sample Script',
            processed_topics=['Algebra', 'Geometry'],
            challenging_topics=['Calculus']
        )
        print("[OK] Created sample script")
    except Exception as e:
        print(f"[ERROR] Failed to create sample script: {e}")
        return False

    # Test creating a memorandum
    try:
        memorandum = Memorandum.objects.create(
            script=script,
            content='This is a sample memorandum for the uploaded script.'
        )
        print("[OK] Created sample memorandum")
    except Exception as e:
        print(f"[ERROR] Failed to create sample memorandum: {e}")
        return False

    # Test creating a study plan
    try:
        study_plan = StudyPlan.objects.create(
            student=student,
            title='Sample Study Plan',
            content='Focus on challenging topics identified in the uploaded script.'
        )
        print("[OK] Created sample study plan")
    except Exception as e:
        print(f"[ERROR] Failed to create sample study plan: {e}")
        return False

    # Test creating a report card
    try:
        report_card = ReportCard.objects.create(
            student=student,
            grades_data={'Mathematics': 'A', 'Science': 'B+', 'English': 'A-'}
        )
        print("[OK] Created sample report card")
    except Exception as e:
        print(f"[ERROR] Failed to create sample report card: {e}")
        return False

    # Test creating a career recommendation
    try:
        career_rec = CareerRecommendation.objects.create(
            student=student,
            report_card=report_card,
            recommended_careers=['Software Engineer', 'Data Scientist'],
            strengths=['Mathematics', 'Problem Solving'],
            areas_for_improvement=['Writing', 'History']
        )
        print("[OK] Created sample career recommendation")
    except Exception as e:
        print(f"[ERROR] Failed to create sample career recommendation: {e}")
        return False

    print("\n[SUCCESS] All basic functionality tests passed!")
    print("\nSummary:")
    print(f"- User created: {user.username}")
    print(f"- Student profile: {student.grade_level}")
    print(f"- Uploaded scripts: {UploadedScript.objects.count()}")
    print(f"- Memorandums: {Memorandum.objects.count()}")
    print(f"- Study plans: {StudyPlan.objects.count()}")
    print(f"- Report cards: {ReportCard.objects.count()}")
    print(f"- Career recommendations: {CareerRecommendation.objects.count()}")

    return True

if __name__ == '__main__':
    success = test_basic_functionality()
    if success:
        print("\n[APP] School Learning Platform is functioning correctly!")
    else:
        print("\n[APP] There were issues with the application.")