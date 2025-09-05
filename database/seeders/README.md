# Database Seeders Documentation

This document describes the database seeders used to populate the database with initial data.

## Available Seeders

### 1. RolePermissionSeeder
- **Purpose**: Creates user roles and permissions
- **Usage**: `php artisan db:seed --class=RolePermissionSeeder`
- **Creates**: 
  - Roles: `admin`, `adviser`, `student`, `hte`
  - Permissions for each role

### 2. SectionSeeder
- **Purpose**: Creates academic sections
- **Usage**: `php artisan db:seed --class=SectionSeeder`
- **Creates**: 
  - Third Year Sections: `3A-G1`, `3A-G2`, `3A-G3`, `3A-G4`, `3B-G1`, `3B-G2`, `3B-G3`, `3B-G4`, `3C-G1`, `3C-G2`, `3C-G3`, `3C-G4`
  - Status: `active`

### 3. StudentSeeder
- **Purpose**: Creates initial student records
- **Usage**: `php artisan db:seed --class=StudentSeeder`
- **Dependencies**: RolePermissionSeeder (for student role)
- **Creates**: 
  - 3 sample students with basic information
  - Sections: `BSIT-4A`, `BSIT-4B`, `BSIT-4C`
  - User accounts for each student

### 4. ExtendedStudentSeeder
- **Purpose**: Creates additional student records
- **Usage**: `php artisan db:seed --class=ExtendedStudentSeeder`
- **Dependencies**: RolePermissionSeeder (for student role)
- **Creates**: 
  - 5 additional students with diverse backgrounds
  - Various specializations and sections

### 5. AcademeAccountSeeder
- **Purpose**: Creates academic accounts for users
- **Usage**: `php artisan db:seed --class=AcademeAccountSeeder`
- **Dependencies**: SectionSeeder, UserSeeder
- **Creates**: 
  - Academic accounts linking users to sections
  - Various user types (admin, adviser, student)

### 6. UserSeeder
- **Purpose**: Creates initial user accounts
- **Usage**: `php artisan db:seed --class=UserSeeder`
- **Dependencies**: RolePermissionSeeder
- **Creates**: 
  - Admin user: `admin@example.com`
  - Adviser users for each section
  - Student users (created by StudentSeeder)

### 7. HTESeeder
- **Purpose**: Creates HTE (Host Training Establishment) records
- **Usage**: `php artisan db:seed --class=HTESeeder`
- **Creates**: 
  - Sample HTE companies with contact information
  - Status: `active`

### 8. InternshipSeeder
- **Purpose**: Creates internship opportunities
- **Usage**: `php artisan db:seed --class=InternshipSeeder`
- **Dependencies**: HTESeeder
- **Creates**: 
  - Sample internship positions
  - Various departments and specializations

## Running Seeders

### Run All Seeders
```bash
php artisan db:seed
```

### Run Specific Seeder
```bash
php artisan db:seed --class=StudentSeeder
```

### Run Multiple Seeders
```bash
php artisan db:seed --class=RolePermissionSeeder,SectionSeeder,StudentSeeder
```

### Fresh Database with Seeding
```bash
php artisan migrate:fresh --seed
```

## Seeder Order

The recommended order for running seeders:

1. **RolePermissionSeeder** - Creates roles and permissions
2. **SectionSeeder** - Creates academic sections
3. **UserSeeder** - Creates user accounts
4. **AcademeAccountSeeder** - Links users to sections
5. **StudentSeeder** - Creates student records
6. **ExtendedStudentSeeder** - Creates additional students
7. **HTESeeder** - Creates HTE companies
8. **InternshipSeeder** - Creates internship opportunities

## Data Structure

### Students Table
- `section`: String field containing section name (e.g., "BSIT-4A")
- No foreign key relationships
- Simple string-based section assignment

### Sections Table
- `section_name`: Unique section identifier
- `status`: Section status (active/inactive)
- Independent table with no foreign key constraints

## Notes

- All seeders check for existing data to avoid duplicates
- Student seeders create both User and Student records
- Section names are hardcoded in seeders for consistency
- No complex relationships between students and sections
