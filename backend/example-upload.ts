import fs from 'fs'
import path from 'path'
import { storage } from './src/utils/storage'

async function exampleUploads() {
  console.log('📁 Example file uploads using storage utility\n')
  
  // Example 1: Upload a text file
  const textContent = Buffer.from('Hello from S3 upload test!')
  const textResult = await storage.upload(textContent, 'hello.txt', 'text/plain')
  console.log('1. Text file uploaded:')
  console.log(`   Key: ${textResult.key}`)
  console.log(`   Path: ${textResult.path}\n`)
  
  // Example 2: Upload an image (simulated)
  const imageBuffer = fs.readFileSync(path.join(__dirname, 'test-image.png')) // Replace with actual image
  const imageResult = await storage.upload(imageBuffer, 'photo.jpg', 'image/jpeg')
  console.log('2. Image uploaded:')
  console.log(`   Key: ${imageResult.key}`)
  console.log(`   Path: ${imageResult.path}\n`)
  
  // Example 3: Upload from stream
  const stream = fs.createReadStream('package.json')
  const chunks: Buffer[] = []
  for await (const chunk of stream) {
    chunks.push(Buffer.from(chunk))
  }
  const streamBuffer = Buffer.concat(chunks)
  const streamResult = await storage.upload(streamBuffer, 'package.json', 'application/json')
  console.log('3. Stream upload:')
  console.log(`   Key: ${streamResult.key}`)
  console.log(`   Path: ${streamResult.path}\n`)
  
  // Example 4: Check if S3 is active
  console.log(`4. S3 active: ${storage.isS3Active()}`)
  
  // Example 5: Delete a file
  await storage.delete(textResult.path)
  console.log('5. Text file deleted')
}

// Run examples
exampleUploads()
  .then(() => console.log('\n✅ All examples completed'))
  .catch(console.error)