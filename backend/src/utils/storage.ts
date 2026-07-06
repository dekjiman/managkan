import fs from 'fs'
import path from 'path'
import { S3Client, PutObjectCommand, DeleteObjectCommand } from '@aws-sdk/client-s3'
import { env } from '../config/env'
import { generatePublicId } from './publicId'

// Support both naming conventions (S3_ACCESS_KEY or S3_ACCESS_KEY_ID)
const s3AccessKey = env.S3_ACCESS_KEY || env.S3_ACCESS_KEY_ID
const s3SecretKey = env.S3_SECRET_KEY || env.S3_SECRET_ACCESS_KEY
const s3Bucket = env.S3_BUCKET || env.PUBLIC_ATTACHMENTS_BUCKET_NAME

const isS3Active = Boolean(s3Bucket && s3AccessKey && s3SecretKey)

let s3: S3Client | null = null

if (isS3Active) {
  s3 = new S3Client({
    region: env.S3_REGION,
    endpoint: env.S3_ENDPOINT,
    forcePathStyle: env.S3_FORCE_PATH_STYLE === 'true',
    credentials: {
      accessKeyId: s3AccessKey!,
      secretAccessKey: s3SecretKey!,
    },
  })
}

const uploadDir = path.join(process.cwd(), 'uploads')

function ensureUploadDir() {
  if (!fs.existsSync(uploadDir)) {
    fs.mkdirSync(uploadDir, { recursive: true })
  }
}

export const storage = {
  isS3Active() {
    return isS3Active
  },

  async upload(buffer: Buffer, originalFilename: string, contentType: string): Promise<{ key: string; path: string }> {
    const ext = path.extname(originalFilename)
    const key = `${generatePublicId()}${ext}`

    if (isS3Active && s3) {
      await s3.send(new PutObjectCommand({
        Bucket: s3Bucket!,
        Key: key,
        Body: buffer,
        ContentType: contentType,
      }))

      // Build public URL
      const publicUrl = env.S3_PUBLIC_URL
        ? `${env.S3_PUBLIC_URL}/${key}`
        : env.PUBLIC_STORAGE_DOMAIN
          ? `https://${env.PUBLIC_STORAGE_DOMAIN}/${key}`
          : `https://${s3Bucket}.s3.${env.S3_REGION}.amazonaws.com/${key}`

      return { key, path: publicUrl }
    }

    ensureUploadDir()
    const filePath = path.join(uploadDir, key)
    fs.writeFileSync(filePath, buffer)
    return { key, path: `/uploads/${key}` }
  },

  async delete(filePath: string) {
    if (isS3Active && s3) {
      // Extract key from full URL or relative path
      const key = filePath.includes('://')
        ? filePath.split('/').pop()!
        : path.basename(filePath)

      await s3.send(new DeleteObjectCommand({
        Bucket: s3Bucket!,
        Key: key,
      }))
      return
    }

    const fullPath = path.join(process.cwd(), filePath)
    if (fs.existsSync(fullPath)) {
      fs.unlinkSync(fullPath)
    }
  },

  getLocalPath(filePath: string): string {
    return path.join(process.cwd(), filePath)
  },
}
