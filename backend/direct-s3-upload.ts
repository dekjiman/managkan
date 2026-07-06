import { S3Client, PutObjectCommand, GetObjectCommand } from '@aws-sdk/client-s3'
import { env } from './src/config/env'

// Direct S3 upload example
async function directS3Upload() {
  const s3 = new S3Client({
    region: env.S3_REGION,
    credentials: {
      accessKeyId: env.S3_ACCESS_KEY || env.S3_ACCESS_KEY_ID!,
      secretAccessKey: env.S3_SECRET_KEY || env.S3_SECRET_ACCESS_KEY!,
    },
  })

  const bucketName = env.S3_BUCKET || env.PUBLIC_ATTACHMENTS_BUCKET_NAME!
  const testKey = `direct-upload-test-${Date.now()}.json`
  
  // Test data
  const testData = {
    message: 'Direct S3 upload test',
    timestamp: new Date().toISOString(),
    random: Math.random().toString(36).substring(7),
  }

  try {
    console.log('🚀 Starting direct S3 upload...')
    
    // Upload
    await s3.send(new PutObjectCommand({
      Bucket: bucketName,
      Key: testKey,
      Body: JSON.stringify(testData, null, 2),
      ContentType: 'application/json',
      Metadata: {
        'test-type': 'direct-upload',
        'uploaded-by': 'test-script',
      },
    }))
    
    console.log(`✅ Uploaded to: ${bucketName}/${testKey}`)
    
    // Verify by downloading
    const result = await s3.send(new GetObjectCommand({
      Bucket: bucketName,
      Key: testKey,
    }))
    
    const downloaded = await result.Body?.transformToString()
    console.log('📥 Downloaded content:', JSON.parse(downloaded!))
    
    // Generate public URL
    const publicUrl = env.S3_PUBLIC_URL
      ? `${env.S3_PUBLIC_URL}/${testKey}`
      : `https://${bucketName}.s3.${env.S3_REGION}.amazonaws.com/${testKey}`
    
    console.log('🌐 Public URL:', publicUrl)
    
  } catch (error) {
    console.error('❌ Upload failed:', error)
    throw error
  }
}

directS3Upload()
  .then(() => console.log('\n🎉 Direct upload test completed!'))
  .catch(() => process.exit(1))