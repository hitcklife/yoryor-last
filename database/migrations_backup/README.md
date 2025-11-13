# Old Migration Files Backup

## Overview
This folder contains the original 72+ migration files that were created during the development of the YorYor dating application. These files have been moved here to make way for the new consolidated migration structure.

## What's in this folder:
- **72 original migration files** from 2023-2025
- Files include both CREATE and ALTER TABLE operations
- Many files contain incremental changes to existing tables
- Some files have dependency issues that were resolved in the new structure

## Why these files were moved:
1. **Consolidation**: The original migrations were scattered across many files with ALTER operations
2. **Organization**: New structure groups related tables by feature/domain
3. **Performance**: New migrations create tables with all indexes and constraints from the start
4. **Maintainability**: Easier to understand and modify the schema

## New Migration Structure:
The new organized migrations are now in:
```
database/migrations/
├── 01_core/           # Core system tables
├── 02_profiles/       # User profile extensions  
├── 03_chat/          # Chat and messaging
├── 04_matching/      # Matching and interactions
├── 05_settings/      # User settings
├── 06_subscription/  # Payment system
├── 07_safety/        # Safety features
├── 08_matchmaker/    # Professional matchmaking
├── 09_features/      # Additional features
├── 10_auth/          # Roles and permissions
└── 99_foreign_keys/  # Cross-table relationships
```

## Important Notes:
- **DO NOT DELETE** these files - they contain the historical development record
- These files are kept for reference and potential rollback scenarios
- The new consolidated migrations contain all the same functionality
- All table structures, indexes, and constraints have been preserved

## Migration History:
- **Original migrations**: 72+ files (2023-2025)
- **New consolidated migrations**: 46 files (organized by feature)
- **Tables covered**: 80+ tables
- **All functionality preserved**: ✅

## If you need to reference old migrations:
1. Check the documentation in `docs/migrations/` for detailed schemas
2. The new migrations contain the final, consolidated schema
3. All original functionality has been preserved and optimized

---
*Backup created: September 24, 2025*
*Total files moved: 72*
