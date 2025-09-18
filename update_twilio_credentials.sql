-- Update Twilio credentials in SMS configuration
-- Replace these values with your actual Twilio credentials

UPDATE sms_config SET 
    service_provider = 'twilio',
    api_key = 'YOUR_TWILIO_ACCOUNT_SID_HERE',           -- Your Account SID from Twilio Console
    api_secret = 'YOUR_TWILIO_AUTH_TOKEN_HERE',         -- Your Auth Token from Twilio Console  
    sender_number = '+1234567890',                      -- Your Twilio phone number
    is_active = TRUE
WHERE id = 1;

-- Example with real format (don't use these exact values):
-- UPDATE sms_config SET 
--     api_key = 'AC1234567890abcdef1234567890abcdef12',
--     api_secret = 'your_auth_token_32_characters_long',
--     sender_number = '+15551234567'
-- WHERE id = 1;

-- Verify the configuration
SELECT * FROM sms_config WHERE is_active = 1;