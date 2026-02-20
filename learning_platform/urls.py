from django.urls import path
from . import views

urlpatterns = [
    path('', views.home, name='home'),
    path('login/', views.login_view, name='login'),
    path('register/', views.register, name='register'),
    path('dashboard/', views.dashboard, name='dashboard'),
    path('study-plan/', views.study_plan, name='study_plan'),
    path('ai-chat/', views.ai_chat, name='ai_chat'),
    path('api/chatbot/', views.chatbot, name='chatbot'),
    path('upload-script/', views.upload_script, name='upload_script'),
    path('upload-report-card/', views.upload_report_card, name='upload_report_card'),
    path('view-memorandum/<int:script_id>/', views.view_memorandum, name='view_memorandum'),
    path('view-study-plan/<int:plan_id>/', views.view_study_plan, name='view_study_plan'),
    path('view-career-recommendations/<int:rec_id>/', views.view_career_recommendations, name='view_career_recommendations'),
]