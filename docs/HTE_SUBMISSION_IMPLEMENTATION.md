# HTE Submission Implementation

## Overview
This document describes the implementation of the HTE (Host Training Establishment) form submission functionality that saves data to multiple database tables.

## Database Tables Involved

### 1. HTE Table (`hte`)
Stores the basic HTE information:
- `user_id` - Foreign key to the user who submitted the form
- `company_name` - Name of the company/organization
- `company_address` - Address of the company
- `company_email` - Email address of the company
- `cperson_fname` - First name of the contact person
- `cperson_lname` - Last name of the contact person (currently empty)
- `cperson_position` - Position of the contact person
- `cperson_contactnum` - Contact number of the contact person
- `is_active` - Whether the HTE is active

### 2. Internships Table (`internships`)
Stores the internship opportunities offered by the HTE:
- `hte_id` - Foreign key to the HTE record
- `position_title` - Title of the internship position
- `department` - Department where the internship is located
- `placement_description` - Description including duration, start date, and end date
- `slot_count` - Number of internship slots available
- `is_active` - Whether the internship is active

### 3. Subcategory Weights Table (`subcategory_weights`)
Stores the weight assignments for each subcategory:
- `hte_id` - Foreign key to the HTE record
- `subcategory_id` - Foreign key to the subcategory
- `weight` - Weight percentage assigned to this subcategory

## Implementation Details

### Controller: `HTEController@submit`

The submission process follows these steps:

1. **Validation**: Validates all required fields including:
   - Basic company information (name, contact person, email, phone, address)
   - Internship details (position, department, number of interns, duration, dates)
   - Category and subcategory weights

2. **HTE Record Creation**: Creates a new HTE record with the company information

3. **Internship Record Creation**: Creates a new internship record linked to the HTE with:
   - Position title and department from the form
   - Placement description combining duration, start date, and end date
   - Slot count from the number of interns field
   - Active status

4. **Weight Storage**: 
   - Stores subcategory weights in the `subcategory_weights` table
   - Both are linked to the HTE record via foreign keys

### Form Data Flow

1. **Basic Information Step**: Collects company details
2. **Internship Offered Step**: Collects position and internship details
3. **Criteria Step**: Collects category and subcategory weights
4. **Review and Submit Step**: Shows summary and submits all data

### Data Relationships

```
HTE (1) -----> (Many) Internships
HTE (1) -----> (Many) SubcategoryWeights
```

## Usage

When an HTE user submits the form:

1. All form data is validated
2. HTE record is created with company information
3. Internship record is created with position and availability details
4. Category weights are stored for assessment criteria
5. Subcategory weights are stored for detailed assessment criteria
6. Success message is shown to the user

## Error Handling

- All database operations are wrapped in try-catch blocks
- Validation errors are returned to the user
- Database errors are caught and appropriate error messages are shown
- Foreign key constraints ensure data integrity

## Future Enhancements

- Add support for multiple internships per HTE
- Implement weight validation (ensure totals equal 100%)
- Add support for file uploads (company logos, documents)
- Implement approval workflow for HTE submissions
