import { Router, Request, Response, NextFunction } from 'express'
import multer from 'multer'
import path from 'path'
import fs from 'fs'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { attachmentService } from '../services/attachment'
import { storage } from '../utils/storage'
import { wrap } from '../utils/wrap'
import { logger } from '../config/logger'

const log = logger.child({ module: 'attachment' })

const router = Router()

const ALLOWED_MIME_TYPES = [
  'image/jpeg', 'image/png', 'image/gif', 'image/webp',
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'application/vnd.ms-excel',
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  'text/plain',
]

const upload = multer({
  storage: multer.memoryStorage(),
  limits: { fileSize: 50 * 1024 * 1024 },
  fileFilter: (req, file, cb) => {
    if (ALLOWED_MIME_TYPES.includes(file.mimetype)) {
      cb(null, true)
    } else {
      cb(new Error('File type not allowed. Allowed: images, PDF, Word, Excel, text'))
    }
  }
})

router.post('/:cardId', requireAuth, (req: AuthRequest, res: Response, next: NextFunction) => {
  upload.single('file')(req, res, (err) => {
    if (err) {
      log.error({ err: err.message }, 'Multer upload error')
      return res.status(400).json({ message: err.message || 'File upload failed' })
    }
    next()
  })
}, wrap(async (req: AuthRequest, res: Response) => {
  try {
    if (!req.file) {
      return res.status(400).json({ message: 'No file uploaded' })
    }

    const cardId = Number(req.params.cardId)
    if (isNaN(cardId)) {
      return res.status(400).json({ message: 'Invalid card ID' })
    }

    log.info({ cardId, filename: req.file.originalname, mimetype: req.file.mimetype, size: req.file.size, s3: storage.isS3Active() }, 'Uploading file')

    const { path: filePath } = await storage.upload(req.file.buffer, req.file.originalname, req.file.mimetype)

    const attachment = await attachmentService.create({
      cardId,
      filename: path.basename(filePath),
      originalFilename: req.file.originalname,
      contentType: req.file.mimetype,
      size: req.file.size,
      path: filePath,
    }, req.user!.id)

    res.status(201).json({ data: attachment, message: 'File uploaded' })
  } catch (error) {
    log.error({ err: error }, 'Attachment creation error')
    throw error
  }
}))

router.get('/:cardId', requireAuth, wrap(async (req: AuthRequest, res) => {
  const cardId = Number(req.params.cardId)
  const attachments = await attachmentService.getByCard(cardId)
  res.json({ data: attachments })
}))

router.delete('/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  const attachment = await attachmentService.getById(req.params.publicId)

  await storage.delete(attachment.path)
  await attachmentService.delete(req.params.publicId)
  res.json({ message: 'Attachment deleted' })
}))

router.get('/download/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  const attachment = await attachmentService.getById(req.params.publicId)

  // S3 public bucket: redirect to the public URL
  if (storage.isS3Active()) {
    return res.redirect(attachment.path)
  }

  // Local: serve the file directly
  const filePath = storage.getLocalPath(attachment.path)
  if (!fs.existsSync(filePath)) {
    return res.status(404).json({ message: 'File not found' })
  }

  res.download(filePath, attachment.originalFilename)
}))

export default router
