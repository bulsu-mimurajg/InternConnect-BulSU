<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Question;
use Illuminate\Database\Seeder;

class HTEAssessmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * HTE Assessment - Likert Scale (1-5)
     * This seeder is idempotent - safe to run multiple times.
     */
    public function run(): void
    {
        $this->command->info('Starting HTE Assessment Seeder...');

        // Get or create main categories
        $technicalSkill = Category::firstOrCreate(['category_name' => 'Technical Skill']);
        $softSkill = Category::firstOrCreate(['category_name' => 'Soft Skill']);

        // TECHNICAL SKILLS ASSESSMENTS
        
        // 1. General Programming Concepts (15 Points)
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'General Programming Concepts',
            [
                'Interns are expected to understand fundamental programming logic and syntax.',
                'Interns are expected to identify and apply basic data structures and algorithms.',
                'Interns are expected to demonstrate familiarity with core programming languages (e.g., Java, Python, C++).',
            ]
        );

        // 2. Problem Solving and Logic (15 Points)
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Problem Solving and Logic',
            [
                'Interns are expected to analyze programming problems systematically.',
                'Interns are expected to create efficient solutions using algorithmic thinking.',
                'Interns are expected to apply logical reasoning in code implementation.',
            ]
        );

        // 3. Code Quality & Best Practices (15 Points)
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Code Quality and Best Practices',
            [
                'Interns are expected to write clean, readable, and maintainable code.',
                'Interns are expected to follow best practices such as proper variable naming and DRY principle.',
                'Interns are expected to include useful comments and documentation in their code.',
            ]
        );

        // 4. Object-Oriented Programming (15 Points)
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Object-Oriented Programming',
            [
                'Interns are expected to understand the concepts of classes, objects, and inheritance.',
                'Interns are expected to apply encapsulation and polymorphism in coding tasks.',
                'Interns are expected to design modular and reusable object-oriented programs.',
            ]
        );

        // 5. Debugging & Testing (15 Points)
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Debugging and Testing',
            [
                'Interns are expected to identify syntax, logic, and runtime errors effectively.',
                'Interns are expected to perform unit or module testing during development.',
                'Interns are expected to use debugging tools to ensure code reliability.',
            ]
        );

        // 6. Developer Tools & Practices (15 Points)
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Developer Tools and Practices',
            [
                'Interns are expected to use version control systems (e.g., Git) effectively.',
                'Interns are expected to collaborate using repositories (push, pull, and commit changes).',
                'Interns are expected to follow the stages of the software development lifecycle (SDLC).',
                'Interns are expected to apply proper documentation and teamwork in coding projects.',
            ]
        );

        // DATABASE MANAGEMENT
        // 7. Basic Concepts
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Database Management - Basic Concepts',
            [
                'Interns are expected to understand database structures and relationships.',
                'Interns are expected to identify the role of primary and foreign keys.',
                'Interns are expected to explain basic database operations and management principles.',
            ]
        );

        // 8. SQL Queries
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Database Management - SQL Queries',
            [
                'Interns are expected to create and execute SELECT, INSERT, UPDATE, and DELETE statements.',
                'Interns are expected to apply filters and conditions using SQL clauses.',
                'Interns are expected to use commands that modify data structure appropriately.',
                'Interns are expected to interpret and analyze query results.',
            ]
        );

        // 9. Database Design & Normalization
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Database Management - Database Design and Normalization',
            [
                'Interns are expected to understand normalization and data integrity concepts.',
                'Interns are expected to apply normalization rules in table design.',
                'Interns are expected to optimize databases through indexing and relationships.',
                'Interns are expected to design relational database schemas based on requirements.',
                'Interns are expected to join tables effectively to extract meaningful results.',
            ]
        );

        // SYSTEM AND SOFTWARE DEVELOPMENT
        // 10. Software Development Life Cycle
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'System and Software Development - Software Development Life Cycle',
            [
                'Interns are expected to understand each SDLC phase (analysis to maintenance).',
                'Interns are expected to participate in iterative or Agile development processes.',
                'Interns are expected to adapt to documentation and testing requirements during development.',
            ]
        );

        // 11. System Development Concepts
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'System and Software Development - System Development Concepts',
            [
                'Interns are expected to differentiate functional and non-functional requirements.',
                'Interns are expected to conduct feasibility and requirement analysis effectively.',
            ]
        );

        // 12. Software Engineering Practices
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'System and Software Development - Software Engineering Practices',
            [
                'Interns are expected to conduct testing and code reviews to ensure quality.',
                'Interns are expected to refactor code for maintainability without changing behavior.',
                'Interns are expected to utilize version control and project management tools.',
                'Interns are expected to adapt quickly to collaborative software development models.',
            ]
        );

        // WEB DEVELOPMENT
        // 13. HTML & CSS
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Web Development - HTML and CSS',
            [
                'Interns are expected to understand the structure of web pages using HTML.',
                'Interns are expected to apply CSS for styling and layout.',
                'Interns are expected to develop responsive designs that adapt to various devices.',
            ]
        );

        // 14. JavaScript & Client-Side Scripting
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Web Development - JavaScript and Client-Side Scripting',
            [
                'Interns are expected to understand JavaScript syntax and logic.',
                'Interns are expected to manipulate webpage content dynamically using DOM.',
                'Interns are expected to handle client-side events and input validation.',
            ]
        );

        // 15. Backend Development & Databases
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Web Development - Backend Development and Databases',
            [
                'Interns are expected to understand backend frameworks and server-side operations.',
                'Interns are expected to connect applications to databases securely.',
                'Interns are expected to execute queries from backend scripts.',
                'Interns are expected to develop simple full-stack web applications.',
            ]
        );

        // PYTHON PROGRAMMING
        // 16. Python Basics
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Python Programming - Python Basics',
            [
                'Interns are expected to understand Python syntax and simple I/O operations.',
                'Interns are expected to differentiate between interpreted and compiled languages.',
                'Interns are expected to use comments and follow Python\'s coding conventions.',
            ]
        );

        // 17. Variables & Data Types
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Python Programming - Variables and Data Types',
            [
                'Interns are expected to declare and manipulate variables properly.',
                'Interns are expected to understand Python\'s data types and mutability.',
                'Interns are expected to perform type conversions when necessary.',
            ]
        );

        // 18. Control Structures
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Python Programming - Control Structures',
            [
                'Interns are expected to use loops and conditional statements appropriately.',
                'Interns are expected to apply logical expressions in program flow.',
            ]
        );

        // 19. Functions & OOP
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Python Programming - Functions and OOP',
            [
                'Interns are expected to define and call user-defined functions.',
                'Interns are expected to apply inheritance and class-based programming.',
                'Interns are expected to identify constructors and special methods in Python.',
                'Interns are expected to apply encapsulation and code reuse effectively.',
                'Interns are expected to use functions for modular design and readability.',
            ]
        );

        // JAVA PROGRAMMING
        // 20. Java Syntax and Data Types
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Java Programming - Java Syntax and Data Types',
            [
                'Interns are expected to write basic Java programs using correct syntax.',
                'Interns are expected to distinguish between primitive and reference data types.',
                'Interns are expected to use constants, variables, and operators correctly.',
            ]
        );

        // 21. Operators and Program Flow
        $this->createSubcategoryWithQuestions(
            $technicalSkill,
            'Java Programming - Operators and Program Flow',
            [
                'Interns are expected to understand the structure of a Java program (main method, loops).',
                'Interns are expected to manage logical flow and operator precedence in coding tasks.',
            ]
        );

        // SOFT SKILLS ASSESSMENTS

        // 22. Communication Skills Assessment
        $this->createSubcategoryWithQuestions(
            $softSkill,
            'Communication Skills',
            [
                'Interns are expected to communicate professionally in oral and written forms.',
                'Interns are expected to compose professional emails and messages.',
                'Interns are expected to practice active listening during meetings.',
                'Interns are expected to handle disagreements respectfully and constructively.',
                'Interns are expected to ask for clarification when instructions are unclear.',
            ]
        );

        // 23. Problem-Solving and Analytical Skills Assessment
        $this->createSubcategoryWithQuestions(
            $softSkill,
            'Problem-Solving and Analytical Skills',
            [
                'Interns are expected to analyze problems systematically before acting.',
                'Interns are expected to identify and correct logical or technical errors.',
                'Interns are expected to consider efficiency and resource usage in solutions.',
                'Interns are expected to diagnose and resolve coding or operational errors.',
                'Interns are expected to apply step-by-step methods to complex problems.',
            ]
        );

        // 24. Time Management Skills Assessment
        $this->createSubcategoryWithQuestions(
            $softSkill,
            'Time Management',
            [
                'Interns are expected to prioritize tasks based on importance and deadlines.',
                'Interns are expected to set SMART goals for their work tasks.',
                'Interns are expected to plan schedules effectively during busy periods.',
                'Interns are expected to break large tasks into manageable parts.',
                'Interns are expected to communicate proactively about delays or challenges.',
            ]
        );

        // 25. Workplace Ethics and Professionalism Assessment
        $this->createSubcategoryWithQuestions(
            $softSkill,
            'Workplace Ethics and Professionalism',
            [
                'Interns are expected to uphold honesty and integrity in all tasks.',
                'Interns are expected to admit and correct mistakes responsibly.',
                'Interns are expected to show respect during discussions and feedback.',
                'Interns are expected to maintain confidentiality of company information.',
                'Interns are expected to follow company policies and meet professional standards.',
            ]
        );

        $this->command->info('HTE Assessment Seeder completed successfully!');
        $this->command->info('Total categories seeded: Technical Skill, Soft Skill');
        $this->command->info('Total subcategories: 25');
        $this->command->info('All questions use Likert scale (1-5) for HTE assessment.');
    }

    /**
     * Create subcategory and its questions (idempotent)
     */
    private function createSubcategoryWithQuestions(Category $category, string $subcategoryName, array $questions): void
    {
        // Create or get subcategory
        $subcategory = SubCategory::firstOrCreate([
            'subcategory_name' => $subcategoryName,
            'category_id' => $category->id,
        ]);

        // Create questions if they don't exist
        foreach ($questions as $questionText) {
            Question::firstOrCreate(
                [
                    'question' => $questionText,
                    'subcategory_id' => $subcategory->id,
                ],
                [
                    'is_active' => true,
                    'question_type' => null, // Likert scale questions don't have a type
                    'points' => null, // Likert scale uses 1-5 rating, not points
                ]
            );
        }

        $questionCount = count($questions);
        $this->command->info("✓ Seeded: {$subcategoryName} ({$questionCount} questions)");
    }
}


