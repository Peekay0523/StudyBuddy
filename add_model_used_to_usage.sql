-- Add model_used column to track which AI model was used (Grok vs OpenAI)
ALTER TABLE openai_usage_logs ADD COLUMN model_used TEXT DEFAULT 'openai';

-- Add index for faster queries by model
CREATE INDEX IF NOT EXISTS idx_usage_model_used ON openai_usage_logs(model_used);

-- Update existing records to use 'openai' as default
UPDATE openai_usage_logs SET model_used = 'openai' WHERE model_used IS NULL;
