# Student Assessment Grading System - Database Schema Analysis

**Analysis Date:** October 27, 2025

## Executive Summary

Based on your described process flow for student assessment grading, I have analyzed the current database schema. Here's what exists and what's missing:

---

## Your Described Process

### Process 1 (Scoring):
- **student_response** table stores student's submitted answers
- **questions** table stores correct answers
- System compares these to calculate initial scores

### Process 2 (Final Match):
- **assessment_weight_criteria** table defines weighting/scoring rules
- System compares calculated scores against criteria to determine final grade

---

## Current Database Schema Analysis

### ✅ TABLES THAT EXIST

#### 1. **`questions` Table** ✅ EXISTS
**Purpose:** Stores assessment questions for students

**Schema:**
```sql
- id (primary key)
- question (text) - The question text
- question_type (string, nullable) - e.g., 'multiple_choice', 'true_false', 'essay'
- points (decimal, nullable) - Points awarded for correct answer
- subcategory_id (foreign key → sub_categories)
- is_active (boolean, default: true)
- created_at, updated_at
```

**Status:** ✅ Fully implemented with question types and points system

---

#### 2. **`answers` Table** ✅ EXISTS
**Purpose:** Stores the correct answer choices for questions

**Schema:**
```sql
- id (primary key)
- question_id (foreign key → questions) - Links to the question
- answer_text (text) - The answer option text
- is_correct (boolean, default: false) - Marks the correct answer(s)
- display_order (integer) - Order to display answers
- created_at, updated_at
```

**Status:** ✅ Fully implemented - stores correct answers with is_correct flag

---

#### 3. **`subcategory_weights` Table** ✅ EXISTS (Similar to assessment_weight_criteria)
**Purpose:** Stores weighting criteria for internship matching (HTE assessment)

**Schema:**
```sql
- id (primary key)
- internship_id (foreign key → internships)
- subcategory_id (foreign key → sub_categories)
- weight (unsigned integer) - The weight/importance value
- created_at, updated_at
```

**Status:** ✅ Exists but for HTE-to-student matching, NOT for student quiz grading

**Note:** This is used to weight subcategories when matching students to internships based on HTE company preferences, not for grading student quizzes.

---

### ❌ TABLES THAT DO NOT EXIST

#### 1. **`student_response` Table** ❌ DOES NOT EXIST

**What we have instead:**
- **`quiz_attempts` Table** - EXISTS but is EMPTY (only has id, created_at, updated_at)

**Current Schema (incomplete):**
```sql
- id (primary key)
- created_at
- updated_at
```

**What's Missing:**
- ❌ student_id (to link to student)
- ❌ question_id (to link to question)
- ❌ student_answer (the answer student selected/typed)
- ❌ is_correct (calculated result)
- ❌ points_earned (calculated score)
- ❌ submitted_at (timestamp)

**Status:** ⚠️ Table exists but is NOT functional - requires migration to add columns

---

#### 2. **`assessment_weight_criteria` Table** ❌ DOES NOT EXIST (for student grading)

**What we have instead:**
- **`subcategory_weights`** - But this is for HTE company criteria, not student assessment grading
- **`student_score`** - Stores final calculated scores by subcategory

**What's Missing:**
- ❌ No table that defines grading criteria/rules like:
  - "Basic level = 50-69%"
  - "Intermediate level = 70-84%"
  - "Advanced level = 85-100%"
- ❌ No table that maps score ranges to grades

**Status:** ❌ Does not exist for student assessment grading

---

## Related Tables That DO Exist

### **`student_score` Table** ✅ EXISTS
**Purpose:** Stores student's FINAL calculated scores by subcategory

**Schema:**
```sql
- student_id (foreign key → students, part of composite primary key)
- sub_category_id (foreign key → sub_categories, part of composite primary key)
- score (decimal 5,2) - Final calculated score (e.g., 85.50)
- created_at, updated_at
```

**Status:** ✅ Stores final scores but doesn't store individual question responses

---

### **`hte_assessment_responses` Table** ✅ EXISTS
**Purpose:** Stores HTE company responses when assessing criteria (different from student quiz)

**Schema:**
```sql
- id (primary key)
- hte_id (foreign key → htes)
- internship_id (foreign key → internships)
- question_id (foreign key → questions)
- response (tinyInteger) - Likert scale 1-5
- created_at, updated_at
```

**Status:** ✅ This is for HTE companies, NOT for students taking quizzes

---

## Summary: What Exists vs. What You Need

| Your Requirement | Current Status | Table Name | Functional? |
|-----------------|----------------|------------|-------------|
| **questions table** (correct answers) | ✅ EXISTS | `questions` + `answers` | ✅ YES |
| **student_response table** (student answers) | ⚠️ PARTIAL | `quiz_attempts` | ❌ NO - needs columns |
| **assessment_weight_criteria table** (grading rules) | ❌ MISSING | N/A | ❌ NO |

---

## Current Grading Flow (What Actually Works Now)

### For Student Assessment:
1. **Questions exist** in `questions` table ✅
2. **Correct answers exist** in `answers` table ✅
3. **Student responses** → ❌ `quiz_attempts` table is not functional
4. **Final scores** stored in `student_score` table ✅

### For HTE Assessment (Different System):
1. **HTE questions exist** in `hte_questions` table ✅
2. **HTE responses** stored in `hte_assessment_responses` table ✅
3. **Weights** defined in `subcategory_weights` table ✅
4. **Student scores** matched against HTE criteria for internship placement ✅

---

## What's Missing for Your Process Flow

### To implement your described grading process, you need:

1. **❌ Populate `quiz_attempts` table** (or create `student_quiz_responses` table)
   - Add columns: student_id, question_id, student_answer, is_correct, points_earned, submitted_at

2. **❌ Create `assessment_grading_criteria` table**
   - Define score ranges and corresponding grades
   - Link to assessment/category/subcategory
   - Example: min_score, max_score, grade_label, description

3. **✅ The comparison logic (Process 1):**
   - Compare `quiz_attempts.student_answer` with `answers.is_correct`
   - Calculate score and store in `student_score`

4. **❌ The final grading logic (Process 2):**
   - Compare `student_score.score` with `assessment_grading_criteria`
   - Determine final grade/level

---

## Recommendations

### Option 1: Implement Full Student Quiz System
1. Create migration to add columns to `quiz_attempts` table
2. Create `assessment_grading_criteria` table
3. Implement scoring logic in backend
4. Implement grading logic based on criteria

### Option 2: Use Existing Architecture
The system currently has:
- Student assessment scores stored per subcategory
- HTE companies weight subcategories for their internships
- Matching algorithm compares student scores with HTE weights

If your goal is internship matching (not traditional quiz grading), the architecture already supports this via `student_score` and `subcategory_weights`.

---

## Action Items

**To implement student quiz grading as you described:**

1. ✅ **questions** table - Already functional
2. ✅ **answers** table - Already functional  
3. ❌ **student_quiz_responses** table - Needs to be created/populated
4. ❌ **assessment_grading_criteria** table - Needs to be created
5. ❌ **Scoring Logic** - Needs to be implemented
6. ❌ **Grading Logic** - Needs to be implemented

---

## Conclusion

**Direct Answer to Your Question:**

> **Do these three tables (student_response, questions, and assessment_weight_criteria) currently exist in the system's database?**

**Answer:**
- ✅ **`questions`** - YES, fully functional
- ⚠️ **`student_response`** - PARTIALLY (table `quiz_attempts` exists but is empty/non-functional)
- ❌ **`assessment_weight_criteria`** - NO (does not exist)

**What you CAN do now:**
- Store questions with correct answers ✅
- Store final student scores by subcategory ✅
- Match students to internships based on HTE weights ✅

**What you CANNOT do now:**
- Store individual student quiz responses ❌
- Automatically grade student quizzes ❌
- Apply grading criteria/rules (A, B, C, etc.) ❌

---

**Next Steps:** Would you like me to create the missing migrations and implement the student quiz grading system?

