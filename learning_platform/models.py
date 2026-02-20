from django.db import models
from django.contrib.auth.models import User
import uuid


class Student(models.Model):
    user = models.OneToOneField(User, on_delete=models.CASCADE)
    student_id = models.UUIDField(default=uuid.uuid4, unique=True)
    grade_level = models.CharField(max_length=20, blank=True)
    created_at = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        return f"{self.user.username} - {self.grade_level}"


class UploadedScript(models.Model):
    student = models.ForeignKey(Student, on_delete=models.CASCADE)
    title = models.CharField(max_length=200)
    subject = models.CharField(max_length=100, blank=True)
    grade_level = models.CharField(max_length=20, blank=True)
    file = models.FileField(upload_to='scripts/')
    uploaded_at = models.DateTimeField(auto_now_add=True)
    processed_topics = models.JSONField(default=list, blank=True)  # Topics extracted from the script
    challenging_topics = models.JSONField(default=list, blank=True)  # Topics the student finds difficult

    def __str__(self):
        return f"{self.title} - {self.student.user.username}"


class Memorandum(models.Model):
    script = models.ForeignKey(UploadedScript, on_delete=models.CASCADE)
    content = models.TextField()
    created_at = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        return f"Memorandum for {self.script.title}"


class StudyPlan(models.Model):
    student = models.ForeignKey(Student, on_delete=models.CASCADE)
    title = models.CharField(max_length=200)
    content = models.TextField()  # Detailed study plan
    created_at = models.DateTimeField(auto_now_add=True)
    is_active = models.BooleanField(default=True)

    def __str__(self):
        return f"Study Plan for {self.student.user.username}"


class ReportCard(models.Model):
    student = models.ForeignKey(Student, on_delete=models.CASCADE)
    file = models.FileField(upload_to='report_cards/')
    grade = models.CharField(max_length=20, blank=True)
    term = models.CharField(max_length=20, blank=True)
    uploaded_at = models.DateTimeField(auto_now_add=True)
    grades_data = models.JSONField(default=dict, blank=True)  # Extracted grades data

    def __str__(self):
        return f"Report Card for {self.student.user.username} - Grade {self.grade} Term {self.term}"


class CareerRecommendation(models.Model):
    student = models.ForeignKey(Student, on_delete=models.CASCADE)
    report_card = models.ForeignKey(ReportCard, on_delete=models.CASCADE)
    recommended_careers = models.JSONField(default=list)  # List of recommended careers
    strengths = models.JSONField(default=list)  # Strengths identified from grades
    areas_for_improvement = models.JSONField(default=list)  # Areas that could be improved
    created_at = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        return f"Career Recommendation for {self.student.user.username}"
