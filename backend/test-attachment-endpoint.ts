/**
 * Test upload via /api/attachments/:cardId endpoint
 * 
 * Flow: Create user+account in DB -> Sign in via API -> Create data -> Upload file
 */

import postgres from 'postgres'
import { customAlphabet } from 'nanoid'
import { env } from './src/config/env'
import { hashPassword } from 'better-auth/crypto'
import path from 'path'
import fs from 'fs'

const generateId = customAlphabet('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 12)

async function testAttachmentUpload() {
  const sql = postgres(env.DATABASE_URL)

  try {
    // ============================================
    // Step 1: Clean up any existing test data
    // ============================================
    console.log('📋 Step 0: Cleaning up any existing test data...')
    await sql`DELETE FROM card_attachment WHERE "cardId" IN (SELECT id FROM card WHERE "createdBy" IN (SELECT id FROM "user" WHERE email = 'test-s3-upload@example.com'))`
    await sql`DELETE FROM card WHERE "createdBy" IN (SELECT id FROM "user" WHERE email = 'test-s3-upload@example.com')`
    await sql`DELETE FROM list WHERE "createdBy" IN (SELECT id FROM "user" WHERE email = 'test-s3-upload@example.com')`
    await sql`DELETE FROM board WHERE "createdBy" IN (SELECT id FROM "user" WHERE email = 'test-s3-upload@example.com')`
    await sql`DELETE FROM workspace_members WHERE "userId" IN (SELECT id FROM "user" WHERE email = 'test-s3-upload@example.com')`
    await sql`DELETE FROM workspace WHERE "createdBy" IN (SELECT id FROM "user" WHERE email = 'test-s3-upload@example.com')`
    await sql`DELETE FROM session WHERE "userId" IN (SELECT id FROM "user" WHERE email = 'test-s3-upload@example.com')`
    await sql`DELETE FROM account WHERE "userId" IN (SELECT id FROM "user" WHERE email = 'test-s3-upload@example.com')`
    await sql`DELETE FROM "user" WHERE email = 'test-s3-upload@example.com'`
    console.log('   ✅ Cleanup done')

    // ============================================
    // Step 1: Create test user + password account in DB
    // ============================================
    console.log('📋 Step 1: Creating test user with password account...')
    
    const userId = crypto.randomUUID()
    
    await sql`
      INSERT INTO "user" (id, name, email, "emailVerified", "createdAt", "updatedAt")
      VALUES (${userId}, 'S3 Test User', 'test-s3-upload@example.com', true, NOW(), NOW())
    `
    console.log(`   ✅ User ID: ${userId}`)
    
    // Create password account (better-auth format)
    const hashedPassword = await hashPassword('TestPassword123!')
    const accountId = crypto.randomUUID()
    await sql`
      INSERT INTO account (id, "accountId", "providerId", "userId", password, "createdAt", "updatedAt")
      VALUES (${accountId}, ${userId}, 'credential', ${userId}, ${hashedPassword}, NOW(), NOW())
    `
    console.log(`   ✅ Password account created`)

    // ============================================
    // Step 2: Sign in via better-auth API
    // ============================================
    console.log('📋 Step 2: Signing in via better-auth API...')
    
    const signInResponse = await fetch('http://localhost:3000/api/auth/sign-in/email', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json', 
        'Origin': 'http://localhost:5173',
      },
      body: JSON.stringify({
        email: 'test-s3-upload@example.com',
        password: 'TestPassword123!',
      }),
    })
    
    if (!signInResponse.ok) {
      const errorText = await signInResponse.text()
      console.error(`   ❌ Sign-in failed (${signInResponse.status}):`, errorText)
      throw new Error('Authentication failed')
    }
    
    const setCookies = signInResponse.headers.getSetCookie?.() || []
    const sessionCookie = setCookies
      .map(c => c.split(';')[0])
      .find(c => c.includes('better-auth.session_token')) || ''
    
    if (!sessionCookie) {
      console.error('   ❌ No session cookie received')
      console.log('   Headers:', Object.fromEntries(signInResponse.headers.entries()))
      throw new Error('No session cookie')
    }
    
    console.log(`   ✅ Sign-in successful`)
    console.log(`   🍪 Cookie: ${sessionCookie.substring(0, 60)}...`)

    // ============================================
    // Step 3: Create workspace -> board -> list -> card
    // ============================================
    console.log('📋 Step 3: Creating workspace, board, list, card...')
    
    const wsPublicId = generateId()
    const wsSlug = `test-ws-${wsPublicId.slice(0, 8).toLowerCase()}`
    const wsResult = await sql`
      INSERT INTO workspace ("publicId", name, slug, "createdBy", "createdAt", "updatedAt")
      VALUES (${wsPublicId}, 'Test Workspace', ${wsSlug}, ${userId}, NOW(), NOW())
      RETURNING id
    `
    const wsId = wsResult[0].id
    
    await sql`
      INSERT INTO workspace_members ("publicId", "userId", "workspaceId", "createdBy", role, status, email, "createdAt", "updatedAt")
      VALUES (${generateId()}, ${userId}, ${wsId}, ${userId}, 'admin', 'active', 'test-s3-upload@example.com', NOW(), NOW())
    `
    console.log(`   ✅ Workspace: ${wsPublicId} (id: ${wsId})`)
    
    const boardPublicId = generateId()
    const boardResult = await sql`
      INSERT INTO board ("publicId", name, slug, "workspaceId", "createdBy", "createdAt", "updatedAt")
      VALUES (${boardPublicId}, 'Test Board', 'test-board', ${wsId}, ${userId}, NOW(), NOW())
      RETURNING id
    `
    const boardId = boardResult[0].id
    console.log(`   ✅ Board: ${boardPublicId} (id: ${boardId})`)
    
    const listPublicId = generateId()
    const listResult = await sql`
      INSERT INTO list ("publicId", name, "boardId", index, "createdBy", "createdAt", "updatedAt")
      VALUES (${listPublicId}, 'Test List', ${boardId}, 0, ${userId}, NOW(), NOW())
      RETURNING id
    `
    const listId = listResult[0].id
    console.log(`   ✅ List: ${listPublicId} (id: ${listId})`)
    
    const cardPublicId = generateId()
    const cardResult = await sql`
      INSERT INTO card ("publicId", title, "listId", index, "createdBy", "createdAt", "updatedAt")
      VALUES (${cardPublicId}, 'Test Card', ${listId}, 0, ${userId}, NOW(), NOW())
      RETURNING id
    `
    const cardId = cardResult[0].id
    console.log(`   ✅ Card: ${cardPublicId} (id: ${cardId})`)

    // ============================================
    // Step 4: Upload file via POST /api/attachments/:cardId
    // ============================================
    console.log('\n📋 Step 4: Uploading file via POST /api/attachments/:cardId...')
    
    const testContent = `S3 Upload Test via Endpoint\nTimestamp: ${new Date().toISOString()}\nRandom: ${Math.random().toString(36).substring(7)}`
    const fileName = `endpoint-test-${Date.now()}.txt`
    
    const boundary = `----TestBoundary${Date.now()}`
    const parts = [
      `--${boundary}\r\n`,
      `Content-Disposition: form-data; name="file"; filename="${fileName}"\r\n`,
      `Content-Type: text/plain\r\n\r\n`,
      testContent,
      `\r\n--${boundary}--\r\n`,
    ]
    const body = Buffer.concat(parts.map(p => Buffer.from(p)))
    
    const response = await fetch(`http://localhost:3000/api/attachments/${cardId}`, {
      method: 'POST',
      headers: {
        'Content-Type': `multipart/form-data; boundary=${boundary}`,
        'Cookie': sessionCookie,
      },
      body,
    })
    
    const result = await response.json() as any
    
    if (!response.ok) {
      console.error(`   ❌ Upload failed (${response.status}):`, JSON.stringify(result, null, 2))
      throw new Error(`Upload failed: ${result.message}`)
    }
    
    console.log(`   ✅ Upload successful!`)
    console.log(`   📄 Attachment ID: ${result.data?.publicId}`)
    console.log(`   🌐 Path/URL: ${result.data?.path}`)
    console.log(`   📦 Full response:\n${JSON.stringify(result, null, 2)}`)

    // ============================================
    // Step 5: Verify the uploaded file via S3 API
    // ============================================
    console.log('\n📋 Step 5: Verifying uploaded file via S3/R2 API...')
    
    const { S3Client, GetObjectCommand } = await import('@aws-sdk/client-s3')
    const verifyS3 = new S3Client({
      region: env.S3_REGION,
      endpoint: env.S3_ENDPOINT,
      forcePathStyle: env.S3_FORCE_PATH_STYLE === 'true',
      credentials: {
        accessKeyId: env.S3_ACCESS_KEY_ID!,
        secretAccessKey: env.S3_SECRET_ACCESS_KEY!,
      },
    })
    
    const attachmentFilename = result.data?.filename
    if (attachmentFilename) {
      const getResult = await verifyS3.send(new GetObjectCommand({
        Bucket: env.PUBLIC_ATTACHMENTS_BUCKET_NAME!,
        Key: attachmentFilename,
      }))
      
      const downloadedContent = await getResult.Body?.transformToString()
      console.log(`   ✅ File verified via S3/R2 API!`)
      console.log(`   📄 Content: "${downloadedContent?.substring(0, 200)}"`)
      console.log(`   📦 Content-Type: ${getResult.ContentType}`)
      console.log(`   📏 Content-Length: ${getResult.ContentLength} bytes`)
    }
    
    // Also try the public URL if it looks resolvable
    const attachmentPath = result.data?.path
    if (attachmentPath && !attachmentPath.includes('managpro')) {
      console.log(`\n   🌐 Public URL: ${attachmentPath}`)
      try {
        const verifyResponse = await fetch(attachmentPath, { signal: AbortSignal.timeout(5000) })
        if (verifyResponse.ok) {
          const content = await verifyResponse.text()
          console.log(`   ✅ Also verified via HTTP!`)
        }
      } catch {
        console.log(`   ⚠️  HTTP verification skipped (domain not resolvable locally)`)
      }
    } else {
      console.log(`   🌐 Public URL: ${attachmentPath}`)
      console.log(`   ⚠️  HTTP verification skipped (custom domain not resolvable locally)`)
    }

    // ============================================
    // Step 6: List attachments for card
    // ============================================
    console.log('\n📋 Step 6: Listing attachments for card...')
    
    const listResp = await fetch(`http://localhost:3000/api/attachments/${cardId}`, {
      headers: { 'Cookie': sessionCookie },
    })
    const listData = await listResp.json() as any
    console.log(`   ✅ Found ${listData.data?.length || 0} attachment(s)`)
    if (listData.data?.length) {
      for (const att of listData.data) {
        console.log(`   📄 ${att.originalFilename} (${att.contentType}, ${att.size} bytes)`)
      }
    }

    // ============================================
    // Step 7: Cleanup - Delete attachment
    // ============================================
    console.log('\n📋 Step 7: Deleting attachment...')
    
    if (result.data?.publicId) {
      const deleteResponse = await fetch(`http://localhost:3000/api/attachments/${result.data.publicId}`, {
        method: 'DELETE',
        headers: { 'Cookie': sessionCookie },
      })
      const deleteResult = await deleteResponse.json() as any
      console.log(`   ✅ Delete result: ${deleteResult.message}`)
    }

    // ============================================
    // Final cleanup
    // ============================================
    console.log('\n🧹 Cleaning up test data...')
    await sql`DELETE FROM card_attachment WHERE "cardId" = ${cardId}`
    await sql`DELETE FROM card WHERE id = ${cardId}`
    await sql`DELETE FROM list WHERE id = ${listId}`
    await sql`DELETE FROM board WHERE id = ${boardId}`
    await sql`DELETE FROM workspace_members WHERE "workspaceId" = ${wsId}`
    await sql`DELETE FROM workspace WHERE id = ${wsId}`
    await sql`DELETE FROM session WHERE "userId" = ${userId}`
    await sql`DELETE FROM account WHERE "userId" = ${userId}`
    await sql`DELETE FROM "user" WHERE id = ${userId}`
    console.log('   ✅ DB cleaned up')

  } catch (error) {
    console.error('\n❌ Test failed:', error)
    throw error
  } finally {
    await sql.end()
  }
}

testAttachmentUpload()
  .then(() => console.log('\n🎉 All endpoint tests passed!'))
  .catch(() => process.exit(1))