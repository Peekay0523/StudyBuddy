from django import forms
from django.contrib.auth.forms import UserCreationForm
from django.contrib.auth.models import User


class ScriptUploadForm(forms.Form):
    title = forms.CharField(max_length=200, required=False, widget=forms.TextInput(attrs={
        'class': 'form-control',
        'placeholder': 'Enter a title for your script'
    }))
    script_file = forms.FileField(widget=forms.FileInput(attrs={
        'class': 'form-control',
        'accept': '.pdf,.docx,.txt'
    }))
    subject = forms.CharField(max_length=100, required=True, widget=forms.TextInput(attrs={
        'class': 'form-control',
        'placeholder': 'e.g., Mathematics, Physics, History'
    }))
    grade_level = forms.ChoiceField(required=True, choices=[
        ('8', 'Grade 8'),
        ('9', 'Grade 9'),
        ('10', 'Grade 10'),
        ('11', 'Grade 11'),
        ('12', 'Grade 12'),
    ], widget=forms.Select(attrs={
        'class': 'form-control'
    }))


class ReportCardUploadForm(forms.Form):
    report_card_file = forms.FileField(widget=forms.FileInput(attrs={
        'class': 'form-control',
        'accept': '.pdf,.docx,.jpg,.jpeg,.png'
    }))
    grade = forms.ChoiceField(required=True, choices=[
        ('8', 'Grade 8'),
        ('9', 'Grade 9'),
        ('10', 'Grade 10'),
        ('11', 'Grade 11'),
        ('12', 'Grade 12'),
    ], widget=forms.Select(attrs={
        'class': 'form-control'
    }))
    term = forms.ChoiceField(required=True, choices=[
        ('1', 'Term 1'),
        ('2', 'Term 2'),
        ('3', 'Term 3'),
        ('4', 'Term 4'),
    ], widget=forms.Select(attrs={
        'class': 'form-control'
    }))