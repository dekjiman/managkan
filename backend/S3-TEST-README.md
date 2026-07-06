# S3 Upload Test Instructions

## Prerequisites

1. **AWS Credentials**: Ensure your `.env` file has S3 configuration:
   ```env
   S3_BUCKET=your-bucket-name
   S3_REGION=us-east-1
   S3_ACCESS_KEY=your-access-key
   S3_SECRET_KEY=your-secret-key
   # Optional:
   S3_ENDPOINT=https://your-s3-endpoint.com
   S3_PUBLIC_URL=https://your-public-url.com
   S3_FORCE_PATH_STYLE=true
   ```

2. **Dependencies**: Already installed in your project:
   - `@aws-sdk/client-s3` (AWS SDK v3)

## Running Tests

### Option 1: Run All Tests
```bash
npx tsx run-s3-tests.ts
```

### Option 2: Run Individual Tests

#### Test 1: Connection Test
```bash
npx tsx test-s3.ts
```
Tests:
- S3 connection
- List buckets
- Upload/download/delete test file

#### Test 2: Direct Upload Test
```bash
npx tsx direct-s3-upload.ts
```
Tests:
- Direct S3 upload without storage utility
- JSON file upload
- Metadata handling
- Public URL generation

#### Test 3: Storage Utility Test
```bash
npx tsx example-upload.ts
```
Tests:
- Using your existing `storage.ts` utility
- Multiple file types
- Stream uploads

## Expected Output

Successful test should show:
```
✅ S3 connection successful!
📦 Available buckets: your-bucket-name
✅ Test file uploaded to: your-bucket-name/test/connection-test-xxx.txt
✅ File downloaded successfully: "S3 connection test at ..."
🧹 Test file cleaned up
🎉 All S3 tests passed!
```

## Troubleshooting

### Common Errors

1. **"Access Denied"**
   - Check AWS credentials
   - Verify bucket permissions
   - Ensure bucket exists in correct region

2. **"Bucket does not exist"**
   - Verify `S3_BUCKET` in `.env`
   - Check bucket name spelling

3. **"Network Error"**
   - Check internet connection
   - Verify `S3_ENDPOINT` if using custom endpoint

4. **"Invalid credentials"**
   - Regenerate AWS access keys
   - Check for extra spaces in `.env`

### Debug Mode

Add to your `.env`:
```env
AWS_SDK_DEBUG=1
```

## Integration with Your App

Your existing upload route in `attachment.ts` already uses the storage utility:

```typescript
// In your route handler
const { path: filePath } = await storage.upload(
  req.file.buffer, 
  req.file.originalname, 
  req.file.mimetype
)
```

The storage utility automatically:
- Uses S3 if configured
- Falls back to local storage if S3 not available
- Generates public URLs for S3 uploads

## Clean Up

Test files are automatically cleaned up. If you need manual cleanup:
```bash
aws s3 rm s3://your-bucket-name/test/ --recursive
```