---
goal: Implement Major Table and Student Major History Tracking System
version: 1.1
date_created: 2025-11-01
last_updated: 2025-11-01
owner: Development Team
status: "In progress"
tags: ["feature", "database", "migration", "major", "student-tracking"]
---

# Introduction

![Status: In progress](https://img.shields.io/badge/status-In%20progress-yellow)

This implementation plan outlines the creation of a normalized major tracking system to replace the current string-based major field in the students table. The plan includes creating a dedicated majors table and implementing a student major history tracking system to monitor major changes over time.

## 1. Requirements & Constraints

-   **REQ-001**: Create a new `majors` table with `id` (PK), `label` (string, nullable), and `system_name` (string, required)
-   **REQ-002**: Create a new `student_major_history` table with `id` (PK), `major_id` (FK), `student_id` (FK), `semester_id` (FK)
-   **REQ-003**: Migrate existing major data from students table to majors table using system_name field
-   **REQ-004**: Update student creation and import functionality to handle major normalization
-   **REQ-005**: Automatically create new major records when encountering unknown majors during student operations
-   **REQ-006**: Track major changes in history table when student major differs from previous records
-   **REQ-007**: Keep existing major string column during initial implementation for validation and rollback purposes
-   **REQ-008**: Maintain backward compatibility during migration phase
-   **SEC-001**: Ensure foreign key constraints maintain data integrity
-   **SEC-002**: Use database transactions for all multi-table operations
-   **CON-001**: Must work with existing Laravel Eloquent ORM patterns
-   **CON-002**: Must preserve all existing student data during migration
-   **GUD-001**: Follow Laravel naming conventions for migrations and models
-   **GUD-002**: Implement proper repository pattern for data access
-   **PAT-001**: Use DTO pattern for data transfer between layers

## 2. Implementation Steps

### Implementation Phase 1: Database Schema Creation

-   GOAL-001: Create new database tables and establish relationships

| Task     | Description                                                                         | Completed | Date |
| -------- | ----------------------------------------------------------------------------------- | --------- | ---- |
| TASK-001 | Create migration for `majors` table with id, label (nullable), system_name fields   |           |      |
| TASK-002 | Create migration for `student_major_history` table with foreign key relationships   |           |      |
| TASK-003 | Create Major model with proper fillable fields and relationships                    |           |      |
| TASK-004 | Create StudentMajorHistory model with relationships to Student, Major, and Semester |           |      |
| TASK-005 | Create MajorRepository class following existing repository pattern                  |           |      |
| TASK-006 | Create StudentMajorHistoryRepository class for history tracking                     |           |      |

### Implementation Phase 2: Data Migration and Normalization

-   GOAL-002: Migrate existing major data and establish relationships

| Task     | Description                                                                    | Completed | Date |
| -------- | ------------------------------------------------------------------------------ | --------- | ---- |
| TASK-007 | Create migration to populate majors table from existing student.major values   |           |      |
| TASK-008 | Create migration to add major_id foreign key to students table                 |           |      |
| TASK-009 | Create migration to populate student.major_id based on existing major strings  |           |      |
| TASK-010 | Create seeder for initial student major history records based on existing data |           |      |
| TASK-011 | Test data migration with rollback capabilities                                 |           |      |

### Implementation Phase 3: Service Layer Updates

-   GOAL-003: Update application logic to use normalized major system

| Task     | Description                                                                             | Completed | Date |
| -------- | --------------------------------------------------------------------------------------- | --------- | ---- |
| TASK-013 | Update StudentRepository.findOrCreateStudent() to handle major normalization            |           |      |
| TASK-014 | Update StudentRepository.createStudentFromDTO() to use major_id instead of major string |           |      |
| TASK-015 | Create MajorService class with findOrCreateMajor() method                               |           |      |
| TASK-016 | Update StudentAttendanceService.createStudentWithAttendances() for major tracking       |           |      |
| TASK-017 | Update StudentImportService to detect and track major changes                           |           |      |
| TASK-018 | Create StudentMajorHistoryService for tracking major changes                            |           |      |

### Implementation Phase 4: DTO and Model Updates

-   GOAL-004: Update data transfer objects and model relationships

| Task     | Description                                                                             | Completed | Date |
| -------- | --------------------------------------------------------------------------------------- | --------- | ---- |
| TASK-019 | Update Student model to include major relationship while keeping major string field     |           |      |
| TASK-020 | Update StudentCreateDTO to handle major string input but convert to major_id internally |           |      |
| TASK-021 | Update StudentAttendanceSummaryDTO.fromDatabaseRow() to use major relationship          |           |      |
| TASK-022 | Update StudentAttendanceListDTO.fromDatabaseRow() to use major relationship             |           |      |
| TASK-023 | Create MajorListDTO for API responses                                                   |           |      |
| TASK-024 | Create StudentMajorHistoryDTO for history tracking responses                            |           |      |

### Implementation Phase 5: API and Controller Updates

-   GOAL-005: Update controllers and API endpoints to handle new major system

| Task     | Description                                                                     | Completed | Date |
| -------- | ------------------------------------------------------------------------------- | --------- | ---- |
| TASK-025 | Update CourseSectionController.createStudent() to track major history           |           |      |
| TASK-026 | Update CourseSectionController.importStudents() to detect major changes         |           |      |
| TASK-027 | Update StudentRepository queries to include major relationship loading          |           |      |
| TASK-028 | Create optional API endpoints for major management (list majors, update labels) |           |      |
| TASK-029 | Update database queries in repositories to use joins instead of major string    |           |      |
| TASK-030 | Update AttendanceService queries to use major relationship                      |           |      |
| TASK-031 | Add data validation to ensure major_id and major string consistency             |           |      |

### Implementation Phase 6: Testing and Validation

-   GOAL-006: Ensure system works correctly with comprehensive testing

| Task     | Description                                                             | Completed | Date |
| -------- | ----------------------------------------------------------------------- | --------- | ---- |
| TASK-032 | Create unit tests for MajorRepository and MajorService                  |           |      |
| TASK-033 | Create unit tests for StudentMajorHistoryRepository and service         |           |      |
| TASK-034 | Create integration tests for student creation with major tracking       |           |      |
| TASK-035 | Create integration tests for student import with major change detection |           |      |
| TASK-036 | Test backward compatibility with existing API endpoints                 |           |      |
| TASK-037 | Perform data integrity validation after migration                       |           |      |
| TASK-038 | Test major_id and major string field consistency validation             |           |      |

## 3. Alternatives

-   **ALT-001**: Keep major as string field and create separate major_normalized table - Rejected due to data redundancy and complexity
-   **ALT-002**: Use enum for majors instead of separate table - Rejected due to lack of flexibility for dynamic major creation
-   **ALT-003**: Create major history only for imports, not manual creation - Rejected as requirement specifies both scenarios need tracking
-   **ALT-004**: Remove major string column immediately - Rejected to maintain safety and validation capabilities during initial rollout

## 4. Dependencies

-   **DEP-001**: Laravel Eloquent ORM relationships
-   **DEP-002**: Existing Student, Semester models and their repositories
-   **DEP-003**: Current StudentCreateDTO and student creation workflows
-   **DEP-004**: Database migration system with proper rollback support

## 5. Files

-   **FILE-001**: `database/migrations/xxxx_create_majors_table.php` - New majors table migration
-   **FILE-002**: `database/migrations/xxxx_create_student_major_history_table.php` - New history tracking table
-   **FILE-003**: `database/migrations/xxxx_migrate_existing_majors_to_majors_table.php` - Data migration
-   **FILE-004**: `database/migrations/xxxx_add_major_id_to_students_table.php` - Add foreign key
-   **FILE-005**: `database/migrations/xxxx_populate_student_major_ids.php` - Link existing students to majors
-   **FILE-006**: `app/Models/Major.php` - New Major model
-   **FILE-007**: `app/Models/StudentMajorHistory.php` - New history tracking model
-   **FILE-008**: `app/Repositories/MajorRepository.php` - Major data access layer
-   **FILE-009**: `app/Repositories/StudentMajorHistoryRepository.php` - History data access layer
-   **FILE-010**: `app/Services/MajorService.php` - Major business logic
-   **FILE-011**: `app/Services/StudentMajorHistoryService.php` - History tracking logic
-   **FILE-012**: `app/DTOs/Major/MajorListDTO.php` - Major data transfer object
-   **FILE-013**: `app/DTOs/Student/StudentMajorHistoryDTO.php` - History data transfer object
-   **FILE-014**: `app/Models/Student.php` - Updated with major relationship (keeping major string field)
-   **FILE-015**: `app/Repositories/StudentRepository.php` - Updated for major handling
-   **FILE-016**: `app/Services/StudentAttendanceService.php` - Updated for major tracking
-   **FILE-017**: `app/Services/StudentImportService.php` - Updated for major change detection
-   **FILE-018**: `database/seeders/StudentMajorHistorySeeder.php` - Initial history data seeder

## 6. Testing

-   **TEST-001**: Unit test for Major model relationships and validation
-   **TEST-002**: Unit test for StudentMajorHistory model relationships
-   **TEST-003**: Unit test for MajorRepository.findOrCreateMajor() method
-   **TEST-004**: Unit test for StudentMajorHistoryRepository.createHistory() method
-   **TEST-005**: Integration test for student creation with new major tracking
-   **TEST-006**: Integration test for student import with major change detection
-   **TEST-007**: Integration test for data migration scripts with rollback
-   **TEST-008**: Feature test for API endpoints maintaining backward compatibility
-   **TEST-009**: Performance test for queries with new major relationships
-   **TEST-010**: Data integrity test ensuring no orphaned records after migration
-   **TEST-011**: Validation test for major_id and major string field consistency

## 7. Risks & Assumptions

-   **RISK-001**: Data migration may take significant time with large student datasets
-   **RISK-002**: Existing API consumers may be affected if major field structure changes in responses
-   **RISK-003**: Rollback complexity increases with multiple dependent migrations
-   **RISK-004**: Data inconsistency risk between major_id and major string fields during transition period
-   **ASSUMPTION-001**: Current major strings in database are reasonably clean and consistent
-   **ASSUMPTION-002**: System can tolerate brief downtime during migration execution
-   **ASSUMPTION-003**: Student creation and import operations are the only sources of major changes
-   **ASSUMPTION-004**: Keeping both major fields temporarily is acceptable for validation purposes

## 8. Related Specifications / Further Reading

[Laravel Migration Documentation](https://laravel.com/docs/10.x/migrations)
[Laravel Eloquent Relationships](https://laravel.com/docs/10.x/eloquent-relationships)
[Repository Pattern in Laravel](https://laravel.com/docs/10.x/repositories)
