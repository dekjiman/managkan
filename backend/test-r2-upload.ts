import { S3Client, PutObjectCommand, GetObjectCommand, DeleteObjectCommand } from '@aws-sdk/client-s3'
import { env } from './src/config/env'

async function testR2Upload() {
  const s3 = new S3Client({
    region: env.S3_REGION,
    endpoint: env.S3_ENDPOINT,
    forcePathStyle: env.S3_FORCE_PATH_STYLE === 'true',
    credentials: {
      accessKeyId: env.S3_ACCESS_KEY_ID!,
      secretAccessKey: env.S3_SECRET_ACCESS_KEY!,
    },
  })

  const bucketName = env.PUBLIC_ATTACHMENTS_BUCKET_NAME!
  const testKey = `test/r2-test-${Date.now()}.txt`
  const testContent = `R2 upload test at ${new Date().toISOString()}\nRandom: ${Math.random().toString(36).substring(7)}`

  try {
    console.log('🚀 Testing Cloudflare R2 upload...')
    console.log(`📦 Bucket: ${bucketName}`)
    console.log(`🔑 Key: ${testKey}`)
    
    // Upload test file
    await s3.send(new PutObjectCommand({
      Bucket: bucketName,
      Key: testKey,
      Body: testContent,
      ContentType: 'text/plain',
      Metadata: {
        'test-type': 'r2-connection-test',
      },
    }))
    
    console.log('✅ Upload successful!')
    
    // Download to verify
    const result = await s3.send(new GetObjectCommand({
      Bucket: bucketName,
      Key: testKey,
    }))
    
    const downloaded = await result.Body?.transformToString()
    console.log('📥 Downloaded content:')
    console.log(downloaded)
    
    // Generate public URL
    const publicUrl = env.PUBLIC_STORAGE_DOMAIN
      ? `https://${env.PUBLIC_STORAGE_DOMAIN}/${testKey}`
      : `${env.S3_ENDPOINT}/${bucketName}/${testKey}`
    
    console.log('🌐 Public URL:', publicUrl)
    
    // Clean up
    await s3.send(new DeleteObjectCommand({
      Bucket: bucketName,
      Key: testKey,
    }))
    console.log('🧹 Test file cleaned up')
    
  } catch (error) {
    console.error('❌ Upload failed:', error)
    throw error
  }
}

testR2Upload()
  .then(() => console.log('\n🎉 R2 upload test completed!'))
  .catch(() => process.exit(1))