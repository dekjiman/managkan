import postgres from 'postgres'
import dotenv from 'dotenv'

dotenv.config()

async function testConnection() {
  console.log('🔗 Testing Supabase connection...')
  console.log(`📡 URL: ${process.env.DATABASE_URL?.replace(/:[^@]+@/, ':***@')}`)
  
  const sql = postgres(process.env.DATABASE_URL!)
  
  try {
    // Test basic connection
    const result = await sql`SELECT 1 as test`
    console.log('✅ Basic query OK:', result)
    
    // Check database version
    const version = await sql`SELECT version()`
    console.log('📋 PostgreSQL version:', version[0].version)
    
    // List tables
    const tables = await sql`
      SELECT table_name 
      FROM information_schema.tables 
      WHERE table_schema = 'public'
      ORDER BY table_name
    `
    console.log(`📦 Tables found: ${tables.length}`)
    tables.forEach(t => console.log(`   - ${t.table_name}`))
    
    // Count users
    const userCount = await sql`SELECT COUNT(*) as count FROM "user"`
    console.log(`👥 Users: ${userCount[0].count}`)
    
    console.log('\n🎉 Supabase connection successful!')
    
  } catch (error) {
    console.error('\n❌ Connection failed:', error)
    throw error
  } finally {
    await sql.end()
  }
}

testConnection()