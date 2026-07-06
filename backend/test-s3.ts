import { S3Client, ListBucketsCommand, PutObjectCommand, GetObjectCommand } from '@aws-sdk/client-s3'
import { env } from './src/config/env'

// Initialize S3 client
const s3 = new S3Client({
  region: env.S3_REGION,
  endpoint: env.S3_ENDPOINT,
  forcePathStyle: env.S3_FORCE_PATH_STYLE === 'true',
  credentials: {
    accessKeyId: env.S3_ACCESS_KEY || env.S3_ACCESS_KEY_ID!,
    secretAccessKey: env.S3_SECRET_KEY || env.S3_SECRET_ACCESS_KEY!,
  },
})

async function testS3Connection() {
  try {
    console.log('🔍 Testing S3 connection...')
    
    // Test 1: List buckets
    const listResult = await s3.send(new ListBucketsCommand({}))
    console.log('✅ S3 connection successful!')
    console.log('📦 Available buckets:', listResult.Buckets?.map(b => b.Name).join(', '))
    
    // Test 2: Upload a test file
    const testBucket = env.S3_BUCKET || env.PUBLIC_ATTACHMENTS_BUCKET_NAME
    if (testBucket) {
      const testKey = `test/connection-test-${Date.now()}.txt`
      const testContent = `S3 connection test at ${new Date().toISOString()}`
      
      await s3.send(new PutObjectCommand({
        Bucket: testBucket,
        Key: testKey,
        Body: testContent,
        ContentType: 'text/plain',
      }))
      
      console.log(`✅ Test file uploaded to: ${testBucket}/${testKey}`)
      
      // Test 3: Download the file to verify
      const getResult = await s3.send(new GetObjectCommand({
        Bucket: testBucket,
        Key: testKey,
      }))
      
      const downloadedContent = await getResult.Body?.transformToString()
      console.log(`✅ File downloaded successfully: "${downloadedContent}"`)
      
      // Clean up test file
      const { DeleteObjectCommand } = await import('@aws-sdk/client-s3')
      await s3.send(new DeleteObjectCommand({
        Bucket: testBucket,
        Key: testKey,
      }))
      console.log('🧹 Test file cleaned up')
    }
    
  } catch (error) {
    console.error('❌ S3 connection failed:', error)
    throw error
  }
}

// Run the test
testS3Connection()
  .then(() => console.log('\n🎉 All S3 tests passed!'))
  .catch(() => process.exit(1))