import { drizzle } from 'drizzle-orm/postgres-js'
import postgres from 'postgres'
import { env } from '../config/env'
import * as schema from './schema'

// Force IPv4 for Supabase connection (cPanel server doesn't support IPv6)
import dns from 'dns'
dns.setDefaultResultOrder('ipv4first')

const client = postgres(env.DATABASE_URL, {
  connect_timeout: 10,
  idle_timeout: 10,
  max: 5,
})

export const db = drizzle(client, { schema })
