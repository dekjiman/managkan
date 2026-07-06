#!/bin/bash
# ManagPro Database Backup Script (PostgreSQL)
# Usage: ./backup-database.sh
# Cron:  0 3 * * * /path/to/managkan/scripts/backup-database.sh

set -euo pipefail

# Load environment variables
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

if [ -f "$PROJECT_DIR/backend/.env" ]; then
  export $(grep -v '^#' "$PROJECT_DIR/backend/.env" | xargs)
elif [ -f "$PROJECT_DIR/.env" ]; then
  export $(grep -v '^#' "$PROJECT_DIR/.env" | xargs)
fi

# Parse DATABASE_URL: postgresql://user:pass@host:port/dbname
DB_URL="${DATABASE_URL:?DATABASE_URL not set}"
DB_HOST=$(echo "$DB_URL" | sed -n 's|.*@\([^:]*\):\([0-9]*\)/.*|\1|p')
DB_PORT=$(echo "$DB_URL" | sed -n 's|.*@\([^:]*\):\([0-9]*\)/.*|\2|p')
DB_NAME=$(echo "$DB_URL" | sed -n 's|.*/\([^?]*\).*|\1|p')
DB_USER=$(echo "$DB_URL" | sed -n 's|.*://\([^:]*\):.*|\1|p')
DB_PASS=$(echo "$DB_URL" | sed -n 's|.*://[^:]*:\([^@]*\)@.*|\1|p')

BACKUP_DIR="${BACKUP_DIR:-$PROJECT_DIR/backups}"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS="${RETENTION_DAYS:-30}"

mkdir -p "$BACKUP_DIR/DB"

echo "[$(date)] Starting database backup..."

# Dump database
PGPASSWORD="$DB_PASS" pg_dump \
  -h "$DB_HOST" \
  -p "$DB_PORT" \
  -U "$DB_USER" \
  -d "$DB_NAME" \
  --no-owner \
  --no-privileges \
  | gzip > "$BACKUP_DIR/DB/${DB_NAME}_${DATE}.sql.gz"

# Verify integrity
if gzip -t "$BACKUP_DIR/DB/${DB_NAME}_${DATE}.sql.gz"; then
  echo "[$(date)] Backup OK: $BACKUP_DIR/DB/${DB_NAME}_${DATE}.sql.gz ($(du -h "$BACKUP_DIR/DB/${DB_NAME}_${DATE}.sql.gz" | cut -f1))"
else
  echo "[$(date)] ERROR: Backup file is corrupt!" >&2
  exit 1
fi

# Clean old backups
find "$BACKUP_DIR/DB" -name "*.sql.gz" -type f -mtime +${RETENTION_DAYS} -delete
echo "[$(date)] Cleaned backups older than ${RETENTION_DAYS} days"

echo "[$(date)] Backup completed successfully"
