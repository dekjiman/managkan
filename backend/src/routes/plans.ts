import { Router } from 'express'
import { planService } from '../services/plan'
import { wrap } from '../utils/wrap'

const router = Router()

router.get('/', wrap(async (req, res) => {
  const plans = await planService.getAll()
  res.json({ data: plans })
}))

router.get('/:name', wrap(async (req, res) => {
  const planData = await planService.getByName(req.params.name)
  if (!planData) return res.status(404).json({ message: 'Plan not found' })
  res.json({ data: planData })
}))

export default router
