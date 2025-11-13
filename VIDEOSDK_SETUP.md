# VideoSDK Setup Guide

## Overview
This guide will help you set up VideoSDK for video and audio calling functionality in your application.

## Prerequisites
1. VideoSDK Developer Account
2. Node.js and NPM installed
3. Laravel application with proper authentication

## Step 1: Get VideoSDK Credentials

1. Visit [VideoSDK Dashboard](https://app.videosdk.live/)
2. Sign up for a free account
3. Create a new project
4. Get your API Key and Secret Key from the dashboard

## Step 2: Configure Environment Variables

Add the following variables to your `.env` file:

```env
# VideoSDK Configuration
VIDEOSDK_API_KEY=your_api_key_here
VIDEOSDK_SECRET_KEY=your_secret_key_here
VIDEOSDK_API_ENDPOINT=https://api.videosdk.live/v2
```

## Step 3: Install Dependencies

The VideoSDK JavaScript SDK is already included via CDN in the messages page. If you need to install it locally:

```bash
npm install @videosdk.live/js-sdk
```

## Step 4: Test the Configuration

1. Start your Laravel application
2. Navigate to the messages page
3. Try to start a video call
4. Check the browser console for any errors

## Troubleshooting

### Common Issues

1. **"VideoSDK is not configured" error**
   - Make sure `VIDEOSDK_API_KEY` and `VIDEOSDK_SECRET_KEY` are set in your `.env` file
   - Restart your Laravel application after adding the variables

2. **"API returned HTML instead of JSON" error**
   - Check if the API route is properly configured
   - Ensure you're authenticated when making the API call
   - Check Laravel logs for detailed error messages

3. **Token generation fails**
   - Verify your API key and secret key are correct
   - Check if your VideoSDK account is active
   - Ensure you have sufficient credits in your VideoSDK account

### Testing Without VideoSDK

If you want to test the UI without VideoSDK configuration, you can:

1. Comment out the VideoSDK script in the messages page
2. Use mock data for testing the interface
3. Implement a test mode that simulates video calls

## API Endpoints

The following API endpoints are available for video calling:

- `POST /api/v1/video-call/token` - Get VideoSDK token
- `POST /api/v1/video-call/create-meeting` - Create a new meeting
- `POST /api/v1/video-call/initiate` - Initiate a video call
- `POST /api/v1/video-call/{callId}/join` - Join an existing call
- `POST /api/v1/video-call/{callId}/end` - End a call

## Features

- ✅ Video calling with camera and microphone
- ✅ Audio-only calling
- ✅ Call controls (mute, video toggle, end call)
- ✅ Real-time participant management
- ✅ Call duration tracking
- ✅ Responsive design for mobile and desktop
- ✅ Integration with existing chat interface

## Security Notes

- Never expose your VideoSDK secret key in client-side code
- Always generate tokens on the server side
- Implement proper authentication for API endpoints
- Use HTTPS in production

## Support

For VideoSDK-specific issues:
- [VideoSDK Documentation](https://docs.videosdk.live/)
- [VideoSDK Support](https://videosdk.live/support)

For application-specific issues:
- Check Laravel logs
- Review browser console for JavaScript errors
- Verify API endpoint responses
