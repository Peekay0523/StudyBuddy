# Hybrid AI System Setup Guide

## Overview

Your StudySmart application now uses a **Hybrid AI System** that intelligently routes requests between **Grok/LLaMA** and **OpenAI** to optimize costs while maintaining high-quality responses.

### How It Works

The system uses **smart routing** based on task complexity:

- **Basic Tasks** → Grok/LLaMA (50-70% cheaper)
- **Intermediate Tasks** → Grok/LLaMA (cost-effective)
- **Advanced Tasks** → OpenAI GPT-4o-mini (highest quality)

### Cost Savings

By using Grok/LLaMA for basic and intermediate tasks, you can reduce your OpenAI costs by **60-70%**.

---

## Architecture

### Components

1. **AIRouter** (`helpers/AIRouter.php`)
   - Central routing system
   - Detects task complexity automatically
   - Routes requests to appropriate AI model

2. **GrokAI** (`helpers/GrokAI.php`)
   - Handles Grok/LLaMA API requests
   - Supports multiple providers (xAI, Together AI, Replicate)
   - OpenAI-compatible endpoint support

3. **AIHelper** (`helpers/AIHelper.php`)
   - Handles OpenAI API requests
   - Vision API for image extraction
   - Advanced task processing

### Task Routing

| Complexity | Model | Used For |
|-----------|-------|----------|
| **Basic** | Grok/LLaMA | Simple Q&A, greetings, basic explanations, chat |
| **Intermediate** | Grok/LLaMA | Study plans, topic analysis, explanations |
| **Advanced** | OpenAI | Memorandum generation, career recommendations, Vision API, SEO content |

---

## Setup Instructions

### Step 1: Run Database Migration

The hybrid AI system tracks which model was used for each request. Run this migration once:

```bash
php add_model_used_to_usage.php
```

This adds the `model_used` column to the `openai_usage_logs` table.

### Step 2: Configure Grok/LLaMA API

#### Option A: Using xAI Grok API (Recommended)

1. Get your API key from [xAI Console](https://console.x.ai/)
2. Update your `.env` file:

```env
GROK_PROVIDER=xai
GROK_API_KEY=your_xai_api_key_here
GROK_MODEL=grok-beta  # Optional, defaults to grok-beta
```

#### Option B: Using Together AI (LLaMA)

1. Get your API key from [Together AI](https://api.together.xyz/)
2. Update your `.env` file:

```env
GROK_PROVIDER=together
GROK_API_KEY=your_together_api_key_here
GROK_MODEL=meta-llama/Llama-3.3-70B-Instruct-Turbo  # Optional
```

#### Option C: Using Replicate (LLaMA)

1. Get your API key from [Replicate](https://replicate.com/)
2. Update your `.env` file:

```env
GROK_PROVIDER=replicate
GROK_API_KEY=your_replicate_api_key_here
GROK_MODEL=meta/meta-llama-3-70b-instruct  # Optional
```

#### Option D: Using Any OpenAI-Compatible Endpoint

1. Update your `.env` file:

```env
GROK_PROVIDER=openai_compatible
GROK_API_KEY=your_api_key_here
GROK_API_URL=https://your-custom-endpoint.com/v1/chat/completions
GROK_MODEL=your-model-name
```

### Step 3: Configure AI Routing

Customize which model handles each complexity level in your `.env` file:

```env
# Which model to use for different task complexity levels
# Options: grok or openai
AI_BASIC_MODEL=grok              # Simple tasks (greetings, basic Q&A)
AI_INTERMEDIATE_MODEL=grok       # Moderate tasks (study plans, analysis)
AI_ADVANCED_MODEL=openai         # Complex tasks (memorandums, careers)
AI_DEFAULT_MODEL=openai          # Fallback model
```

### Step 4: Verify OpenAI Configuration

Ensure your OpenAI API key is configured:

```env
OPENAI_API_KEY=sk-proj-your_openai_api_key_here
```

---

## Configuration Reference

### Environment Variables

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `OPENAI_API_KEY` | OpenAI API key | - | Yes |
| `GROK_PROVIDER` | Grok/LLaMA provider | `xai` | No |
| `GROK_API_KEY` | Grok/LLaMA API key | - | Yes (for hybrid) |
| `GROK_API_URL` | Custom API endpoint | Auto-set | No |
| `GROK_MODEL` | Model name | Auto-set | No |
| `AI_BASIC_MODEL` | Model for basic tasks | `grok` | No |
| `AI_INTERMEDIATE_MODEL` | Model for intermediate tasks | `grok` | No |
| `AI_ADVANCED_MODEL` | Model for advanced tasks | `openai` | No |
| `AI_DEFAULT_MODEL` | Default fallback model | `openai` | No |

---

## Monitoring Usage

### Admin Dashboard

Visit `/admin/openai-settings` to view:

- **Total tokens** used across all models
- **OpenAI tokens** vs **Grok/LLaMA tokens**
- **Estimated cost** savings from hybrid AI
- **Model status** (Active/Not Configured)
- **Recent usage** logs with model breakdown

### Database Queries

View usage by model:

```sql
-- Total tokens by model
SELECT model_used, SUM(total_tokens) as tokens, COUNT(*) as calls
FROM openai_usage_logs
GROUP BY model_used;

-- Monthly usage
SELECT 
    model_used,
    DATE(created_at) as date,
    SUM(total_tokens) as tokens
FROM openai_usage_logs
WHERE created_at >= DATE('now', '-30 days')
GROUP BY model_used, date
ORDER BY date DESC;
```

---

## Troubleshooting

### GrokAI Not Working

1. **Check API Key**: Ensure `GROK_API_KEY` is set in `.env`
2. **Verify Provider**: Check `GROK_PROVIDER` matches your service
3. **Check Logs**: Look for errors in your PHP error log:
   ```bash
   tail -f /path/to/error.log | grep GrokAI
   ```

### Fallback to OpenAI

If GrokAI fails, the system automatically falls back to OpenAI. Check logs to see why:

```bash
grep "GrokAI.*Error" /path/to/error.log
```

### All Requests Using OpenAI

If all requests are using OpenAI:

1. Check `AI_BASIC_MODEL` and `AI_INTERMEDIATE_MODEL` are set to `grok`
2. Verify `GROK_API_KEY` is not empty or placeholder
3. Check GrokAI status in admin dashboard

### Migration Issues

If the migration fails:

```bash
# Check if column exists
sqlite3 database.sqlite3 "PRAGMA table_info(openai_usage_logs);"

# Manually add column if needed
sqlite3 database.sqlite3 "ALTER TABLE openai_usage_logs ADD COLUMN model_used TEXT DEFAULT 'openai';"
```

---

## Performance Optimization

### Recommended Configuration

For **maximum cost savings**:

```env
AI_BASIC_MODEL=grok
AI_INTERMEDIATE_MODEL=grok
AI_ADVANCED_MODEL=openai
```

For **highest quality** (less cost savings):

```env
AI_BASIC_MODEL=openai
AI_INTERMEDIATE_MODEL=openai
AI_ADVANCED_MODEL=openai
```

### Custom Routing

You can customize routing by modifying the `getRoutingConfig()` method in `AIRouter.php`.

---

## API Provider Comparison

| Provider | Model | Cost | Speed | Quality |
|----------|-------|------|-------|---------|
| **xAI Grok** | grok-beta | $$ | Fast | High |
| **Together AI** | LLaMA 3.3 70B | $ | Fast | High |
| **Replicate** | LLaMA 3 70B | $ | Medium | High |
| **OpenAI** | GPT-4o-mini | $$$ | Fast | Very High |

---

## Migration Checklist

- [ ] Run database migration: `php add_model_used_to_usage.php`
- [ ] Configure Grok/LLaMA API key in `.env`
- [ ] Set `GROK_PROVIDER` to your chosen provider
- [ ] Verify `OPENAI_API_KEY` is configured
- [ ] Test basic chat functionality
- [ ] Check admin dashboard for model status
- [ ] Monitor usage after first day

---

## Support

If you encounter issues:

1. Check the admin dashboard at `/admin/openai-settings`
2. Review error logs for specific error messages
3. Verify all environment variables are set correctly
4. Test with a simple chat request first

---

## Benefits

✅ **60-70% cost reduction** on AI processing  
✅ **Intelligent routing** based on task complexity  
✅ **Automatic fallback** if one model fails  
✅ **Usage tracking** per model  
✅ **Easy configuration** via `.env` file  
✅ **Admin dashboard** monitoring  
✅ **Flexible provider** support  

---

*Last Updated: April 11, 2026*
