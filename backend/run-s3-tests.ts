#!/usr/bin/env tsx
import { execSync } from 'child_process'

console.log('🧪 S3 Upload Test Suite\n')
console.log('=' .repeat(50))

const tests = [
  {
    name: 'Connection Test',
    file: 'test-s3.ts',
    description: 'Tests basic S3 connectivity and operations',
  },
  {
    name: 'Direct Upload Test',
    file: 'direct-s3-upload.ts',
    description: 'Tests direct S3 upload without storage utility',
  },
  {
    name: 'Storage Utility Test',
    file: 'example-upload.ts',
    description: 'Tests using your existing storage utility',
  },
]

async function runTests() {
  for (const test of tests) {
    console.log(`\n▶️  Running: ${test.name}`)
    console.log(`   ${test.description}`)
    console.log('-'.repeat(40))
    
    try {
      execSync(`npx tsx ${test.file}`, { 
        stdio: 'inherit',
        cwd: __dirname,
      })
      console.log(`✅ ${test.name} passed\n`)
    } catch (error) {
      console.error(`❌ ${test.name} failed\n`)
    }
  }
  
  console.log('=' .repeat(50))
  console.log('📋 Test Summary:')
  console.log('   1. Check your .env file has S3 credentials')
  console.log('   2. Run individual tests: npx tsx test-s3.ts')
  console.log('   3. Or run all tests: npx tsx run-s3-tests.ts')
}

runTests().catch(console.error)