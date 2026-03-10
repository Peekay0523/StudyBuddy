# OpenAI API Setup Guide for StudySmart

## Step 1: Get Your OpenAI API Key

1. Go to https://platform.openai.com/api-keys
2. Sign in or create an account
3. Click **"Create new secret key"**
4. Give it a name (e.g., "StudySmart")
5. **Copy the key immediately** - you won't see it again!
   - It looks like: `sk-proj-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`

## Step 2: Configure Your Application

### Option A: Using .env File (Recommended)

Create a `.env` file in your project root:

```env
OPENAI_API_KEY=sk-proj-your-actual-api-key-here
```

### Option B: Edit config.php Directly

Edit `C:\Users\mmereko\Desktop\SchoolApp\SchoolApp\config\config.php`:

```php
// Line 29 - Replace with your actual API key
define('OPENAI_API_KEY', 'sk-proj-your-actual-api-key-here');
```

## Step 3: Test Your Configuration

Visit: **http://localhost:8000/ai-chat**

Try sending a message like:
- "Hello, can you help me with math?"
- "Explain photosynthesis"
- "Give me a quiz on World War 2"

## Step 4: Check API Usage & Costs

1. Go to https://platform.openai.com/usage
2. Monitor your daily/monthly usage
3. Set usage limits to prevent unexpected charges

## Available OpenAI Models

Your system currently uses **GPT-4o-mini** (affordable and fast).

### To Change Models:

Edit `C:\Users\mmereko\Desktop\SchoolApp\SchoolApp\helpers\AIHelper.php` line 20:

```php
// Current (Recommended - Cheap & Fast)
'model' => 'gpt-4o-mini',

// Other Options:

// Cheapest option (good for simple tasks)
'model' => 'gpt-3.5-turbo',

// More powerful (higher cost)
'model' => 'gpt-4o',

// Most powerful (most expensive)
'model' => 'gpt-4-turbo',

// For image analysis (if you add image upload)
'model' => 'gpt-4-vision-preview',
```

## Model Comparison

| Model | Speed | Cost per 1K tokens | Best For |
|-------|-------|-------------------|----------|
| **gpt-4o-mini** | ⚡⚡⚡ | $0.00015 | Chat, study help (CURRENT) |
| gpt-3.5-turbo | ⚡⚡⚡ | $0.0005 | Simple Q&A |
| gpt-4o | ⚡⚡ | $0.005 | Complex explanations |
| gpt-4-turbo | ⚡ | $0.01 | Advanced reasoning |

**Recommendation:** Stick with `gpt-4o-mini` - it's fast, smart, and very affordable!

## Cost Estimates

**Average chat message:** ~100-200 tokens

**Example Monthly Costs** (with gpt-4o-mini):
- 100 chats/day = 3,000 chats/month
- 3,000 × 150 tokens = 450,000 tokens
- **Cost: ~$0.07/month** (yes, cents!)

**With $10 credit:** ~140,000 chat messages!

## Troubleshooting

### "Invalid API Key" Error

1. Check your API key is correct (no extra spaces)
2. Make sure key starts with `sk-proj-` or `sk-`
3. Verify API key is active: https://platform.openai.com/api-keys

### "Insufficient Quota" Error

1. Add credits: https://platform.openai.com/account/billing
2. Check usage: https://platform.openai.com/usage
3. Set spending limit: https://platform.openai.com/account/billing/limits

### API Not Working

Test with this command (replace YOUR_KEY):

```bash
curl https://api.openai.com/v1/chat/completions ^
  -H "Content-Type: application/json" ^
  -H "Authorization: Bearer sk-proj-YOUR_KEY_HERE" ^
  -d "{\"model\": \"gpt-4o-mini\", \"messages\": [{\"role\": \"user\", \"content\": \"Hello!\"}]}"
```

## Features Using OpenAI in Your System

1. **AI Chat** (/ai-chat) - Study assistant
2. **Topic Analysis** - Analyzes uploaded documents
3. **Memorandum Generation** - Creates study summaries
4. **Study Plan Generation** - Personalized study plans
5. **Career Recommendations** - Suggests careers based on grades
6. **Bursary Search** - Finds relevant bursaries

## Security Tips

1. ✅ **Never commit API key to Git** (already in .gitignore)
2. ✅ **Set spending limits** in OpenAI dashboard
3. ✅ **Monitor usage** regularly
4. ✅ **Use HTTPS** in production
5. ✅ **Rotate keys** every few months

## Advanced: Add Usage Tracking

Want to track how many AI calls each user makes?

Add to your database:

```sql
CREATE TABLE ai_usage (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    tokens_used INTEGER DEFAULT 0,
    model TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

Then log usage in `AIHelper.php`:

```php
// After successful API call in makeRequest()
if (isset($result['usage']['total_tokens'])) {
    $tokensUsed = $result['usage']['total_tokens'];
    $userId = getCurrentUser()['id'] ?? null;
    if ($userId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO ai_usage (user_id, tokens_used, model) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $tokensUsed, $data['model']]);
    }
}
```

## Need Help?

- **OpenAI Docs:** https://platform.openai.com/docs
- **API Status:** https://status.openai.com
- **Support:** https://help.openai.com

---

## Quick Start Checklist

- [ ] Get API key from https://platform.openai.com/api-keys
- [ ] Add to `.env` file or `config.php`
- [ ] Test at http://localhost:8000/ai-chat
- [ ] Set spending limit at https://platform.openai.com/account/billing/limits
- [ ] Monitor usage at https://platform.openai.com/usage

**You're all set! 🎉**
