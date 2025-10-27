<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Database\Seeder;

class StudentAssessmentQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Student Assessment Questions...');

        // Define all questions organized by category and subcategory
        $questionsData = $this->getQuestionsData();

        $totalQuestions = 0;

        foreach ($questionsData as $categoryName => $subcategories) {
            $category = Category::firstOrCreate(['category_name' => $categoryName]);

            foreach ($subcategories as $subcategoryName => $questions) {
                $subcategory = SubCategory::firstOrCreate([
                    'subcategory_name' => $subcategoryName,
                    'category_id' => $category->id,
                ]);

                foreach ($questions as $questionData) {
                    $question = Question::firstOrCreate(
                        [
                            'question' => $questionData['question'],
                            'subcategory_id' => $subcategory->id,
                        ],
                        [
                            'question_type' => $questionData['type'] ?? 'multiple_choice',
                            'points' => $questionData['points'] ?? 1,
                            'is_active' => true,
                        ]
                    );

                    // Add answers if they exist
                    if (isset($questionData['answers'])) {
                        foreach ($questionData['answers'] as $index => $answerData) {
                            Answer::firstOrCreate(
                                [
                                    'question_id' => $question->id,
                                    'answer_text' => $answerData['text'],
                                ],
                                [
                                    'is_correct' => $answerData['is_correct'] ?? false,
                                    'display_order' => $index + 1,
                                ]
                            );
                        }
                    }

                    $totalQuestions++;
                }

                $this->command->info("✓ Seeded: {$subcategoryName} (" . count($questions) . " questions)");
            }
        }

        $this->command->info("\nStudent Assessment Questions seeding completed!");
        $this->command->info("Total questions created: {$totalQuestions}");
    }

    /**
     * Get all questions data organized by category and subcategory
     */
    private function getQuestionsData(): array
    {
        return [
            'Technical Skill' => [
                'General Programming Concepts' => [
                    [
                        'question' => 'Which of the following is a compiled language?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Python', 'is_correct' => false],
                            ['text' => 'C++', 'is_correct' => true],
                            ['text' => 'JavaScript', 'is_correct' => false],
                            ['text' => 'PHP', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is not a programming language?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'HTML', 'is_correct' => true],
                            ['text' => 'JAVA', 'is_correct' => false],
                            ['text' => 'C', 'is_correct' => false],
                            ['text' => 'Python', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following describes dynamic typing?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Variables must declare type before assignment', 'is_correct' => false],
                            ['text' => 'Variable type can change at runtime', 'is_correct' => true],
                            ['text' => 'Variables cannot be reassigned', 'is_correct' => false],
                            ['text' => 'Only numeric types are supported', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which search algorithm works only on sorted data?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Linear Search', 'is_correct' => false],
                            ['text' => 'Binary Search', 'is_correct' => true],
                            ['text' => 'Bubble Sort', 'is_correct' => false],
                            ['text' => 'Depth First Search (DFS)', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which data structure is used to implement a stack?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Queue', 'is_correct' => false],
                            ['text' => 'Array or Linked List', 'is_correct' => true],
                            ['text' => 'Hash Table', 'is_correct' => false],
                            ['text' => 'Tree', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is best for checking balanced parentheses in an expression?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Queue', 'is_correct' => false],
                            ['text' => 'Stack', 'is_correct' => true],
                            ['text' => 'Linked List', 'is_correct' => false],
                            ['text' => 'Array', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What does the DRY principle mean?',
                        'points' => 1,
                        'answers' => [
                            ['text' => "Don't Repeat Yourself (reuse functions and avoid duplicate code)", 'is_correct' => true],
                            ['text' => 'Debug, Run, Yield', 'is_correct' => false],
                            ['text' => 'Define Readable YAML', 'is_correct' => false],
                            ['text' => 'Do Run Yourself', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is a good variable naming practice?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'a1, b2, c3', 'is_correct' => false],
                            ['text' => 'totalPrice, studentName', 'is_correct' => true],
                            ['text' => 'x, y, z', 'is_correct' => false],
                            ['text' => 'var1, var2, var3', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Why are code comments important?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'They make the program faster', 'is_correct' => false],
                            ['text' => 'They help humans understand the code', 'is_correct' => true],
                            ['text' => 'They reduce memory usage', 'is_correct' => false],
                            ['text' => 'They are required by the compiler', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following are pillars of OOP?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Inheritance, Encapsulation, Polymorphism', 'is_correct' => true],
                            ['text' => 'Encapsulation, Debugging, Polymorphism', 'is_correct' => false],
                            ['text' => 'Overloading, Debugging, Reusability', 'is_correct' => false],
                            ['text' => 'Compilation, Abstraction, Documentation', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which keyword is used in Java to create a new object?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'new', 'is_correct' => true],
                            ['text' => 'class', 'is_correct' => false],
                            ['text' => 'this', 'is_correct' => false],
                            ['text' => 'make', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which statement best describes encapsulation?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Hiding internal details using private variables and public methods', 'is_correct' => true],
                            ['text' => 'Redefining methods from a parent class', 'is_correct' => false],
                            ['text' => 'Writing multiple functions with the same name', 'is_correct' => false],
                            ['text' => 'Declaring all variables global', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'If a program compiles successfully but gives wrong output, it is a:',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Syntax error', 'is_correct' => false],
                            ['text' => 'Logic error', 'is_correct' => true],
                            ['text' => 'Runtime error', 'is_correct' => false],
                            ['text' => 'Exception error', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What is the purpose of unit testing?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Test the whole system', 'is_correct' => false],
                            ['text' => 'Test individual functions or modules', 'is_correct' => true],
                            ['text' => 'Test only the database', 'is_correct' => false],
                            ['text' => 'Test only user interface', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following tools helps in debugging code?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'IDE Debugger', 'is_correct' => true],
                            ['text' => 'Excel', 'is_correct' => false],
                            ['text' => 'Word', 'is_correct' => false],
                            ['text' => 'Photoshop', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is a version control system?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'MySQL', 'is_correct' => false],
                            ['text' => 'Git', 'is_correct' => true],
                            ['text' => 'Docker', 'is_correct' => false],
                            ['text' => 'Jenkins', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which Git command uploads your local changes to the remote repository?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'git push', 'is_correct' => true],
                            ['text' => 'git pull', 'is_correct' => false],
                            ['text' => 'git clone', 'is_correct' => false],
                            ['text' => 'git commit', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'In software development, which of the following is part of SDLC?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Requirement Analysis', 'is_correct' => false],
                            ['text' => 'Design', 'is_correct' => false],
                            ['text' => 'Implementation', 'is_correct' => false],
                            ['text' => 'All of the above', 'is_correct' => true],
                        ],
                    ],
                    [
                        'question' => 'Method overriding means:',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Defining a method with the same name but different parameters in the same class', 'is_correct' => false],
                            ['text' => 'Redefining a method of the parent class in the child class', 'is_correct' => true],
                            ['text' => 'Defining multiple constructors', 'is_correct' => false],
                            ['text' => 'Using different variable types in a function', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What is the difference between pass by value and pass by reference?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Pass by value sends a copy, pass by reference sends the memory address', 'is_correct' => true],
                            ['text' => 'Pass by value modifies the original variable', 'is_correct' => false],
                            ['text' => 'Pass by reference always increases memory usage', 'is_correct' => false],
                            ['text' => 'They are exactly the same', 'is_correct' => false],
                        ],
                    ],
                ],
                'Database Management' => [
                    [
                        'question' => 'Which of the following is a relational database management system (RDBMS)?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'MySQL', 'is_correct' => true],
                            ['text' => 'MongoDB', 'is_correct' => true],
                            ['text' => 'PostgreSQL', 'is_correct' => true],
                            ['text' => 'Oracle', 'is_correct' => true],
                        ],
                    ],
                    [
                        'question' => 'In a relational database, a primary key must be:',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Unique and not null', 'is_correct' => true],
                            ['text' => 'Always auto-increment', 'is_correct' => false],
                            ['text' => 'Nullable', 'is_correct' => false],
                            ['text' => 'Used only once in the database', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which is true about a foreign key?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'It uniquely identifies a row within a table', 'is_correct' => false],
                            ['text' => 'It enforces a relationship between two tables', 'is_correct' => true],
                            ['text' => 'It stores multiple values in a single column', 'is_correct' => false],
                            ['text' => 'It can only reference the same table', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which SQL command is used to retrieve data from a database?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'UPDATE', 'is_correct' => false],
                            ['text' => 'INSERT', 'is_correct' => false],
                            ['text' => 'SELECT', 'is_correct' => true],
                            ['text' => 'DELETE', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which SQL clause is used to filter results?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'ORDER BY', 'is_correct' => false],
                            ['text' => 'WHERE', 'is_correct' => true],
                            ['text' => 'GROUP BY', 'is_correct' => false],
                            ['text' => 'HAVING', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which SQL command is used to remove all rows from a table but keep the structure?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'DELETE', 'is_correct' => false],
                            ['text' => 'DROP', 'is_correct' => false],
                            ['text' => 'TRUNCATE', 'is_correct' => true],
                            ['text' => 'REMOVE', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What will the following SQL query return? SELECT COUNT(*) FROM Students;',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'The number of columns in Students table', 'is_correct' => false],
                            ['text' => 'The number of rows in Students table', 'is_correct' => true],
                            ['text' => 'The list of all student names', 'is_correct' => false],
                            ['text' => 'The highest student ID', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'The process of removing data redundancy and improving data integrity is called:',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Normalization', 'is_correct' => true],
                            ['text' => 'Denormalization', 'is_correct' => false],
                            ['text' => 'Indexing', 'is_correct' => false],
                            ['text' => 'Aggregation', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is a feature of 1st Normal Form (1NF)?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Each table must have a primary key', 'is_correct' => false],
                            ['text' => 'Each field must contain atomic (indivisible) values', 'is_correct' => true],
                            ['text' => 'No transitive dependencies', 'is_correct' => false],
                            ['text' => 'No repeating groups', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following improves query performance in large tables?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Normalization', 'is_correct' => false],
                            ['text' => 'Foreign keys', 'is_correct' => false],
                            ['text' => 'Indexing', 'is_correct' => true],
                            ['text' => 'Triggers', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'You need to store student grades with subject names and scores. Which design is best?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Store all grades in a single column separated by commas', 'is_correct' => false],
                            ['text' => 'Create a separate Grades table with foreign key to Students table', 'is_correct' => true],
                            ['text' => 'Put grades directly inside the Students table as multiple columns', 'is_correct' => false],
                            ['text' => 'Save grades in a text file instead of a database', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is an advantage of using JOIN in SQL?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'It merges two or more databases into one', 'is_correct' => false],
                            ['text' => 'It combines rows from two or more tables based on related columns', 'is_correct' => true],
                            ['text' => 'It creates a backup of the database', 'is_correct' => false],
                            ['text' => 'It permanently deletes duplicates', 'is_correct' => false],
                        ],
                    ],
                ],
                'System and Software Development' => [
                    [
                        'question' => 'Which of the following is the correct order of the traditional SDLC phases?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Design → Implementation → Testing → Deployment → Requirement Analysis → Maintenance', 'is_correct' => false],
                            ['text' => 'Requirement Analysis → Design → Implementation → Testing → Deployment → Maintenance', 'is_correct' => true],
                            ['text' => 'Testing → Deployment → Requirement Analysis → Design → Implementation → Maintenance', 'is_correct' => false],
                            ['text' => 'Deployment → Maintenance → Design → Implementation → Testing → Requirement Analysis', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which SDLC model delivers software in small increments or iterations?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Waterfall', 'is_correct' => false],
                            ['text' => 'Spiral', 'is_correct' => false],
                            ['text' => 'Agile', 'is_correct' => true],
                            ['text' => 'V-Model', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'The main advantage of the Agile methodology is:',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Strict step-by-step execution', 'is_correct' => false],
                            ['text' => 'Faster adaptability to change and collaboration', 'is_correct' => true],
                            ['text' => 'Complete documentation before coding', 'is_correct' => false],
                            ['text' => 'No need for user involvement', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What is feasibility analysis in system development?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Testing the speed of the system', 'is_correct' => false],
                            ['text' => 'Checking whether the project is financially, technically, and operationally possible', 'is_correct' => true],
                            ['text' => 'Writing code for the system', 'is_correct' => false],
                            ['text' => 'Designing the database schema', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is a non-functional requirement?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'The system must allow students to register online', 'is_correct' => false],
                            ['text' => 'The system must process 1,000 requests per second', 'is_correct' => true],
                            ['text' => 'The system must allow teachers to upload grades', 'is_correct' => false],
                            ['text' => 'The system must provide login access', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which practice ensures quality and correctness of software before release?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Prototyping', 'is_correct' => false],
                            ['text' => 'Testing', 'is_correct' => true],
                            ['text' => 'Refactoring', 'is_correct' => false],
                            ['text' => 'Documentation', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What does refactoring mean in software development?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Adding more features without changing existing code', 'is_correct' => false],
                            ['text' => 'Restructuring existing code to improve readability and maintainability without changing behavior', 'is_correct' => true],
                            ['text' => 'Removing unused functions permanently', 'is_correct' => false],
                            ['text' => 'Debugging syntax errors', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is a version control system used in software development?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Docker', 'is_correct' => false],
                            ['text' => 'Git', 'is_correct' => true],
                            ['text' => 'Jenkins', 'is_correct' => false],
                            ['text' => 'VS Code', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Your team is building an online library system. The client suddenly changes a major requirement. Which development approach is best?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Waterfall', 'is_correct' => false],
                            ['text' => 'Agile', 'is_correct' => true],
                            ['text' => 'Prototype-only model', 'is_correct' => false],
                            ['text' => 'Big Bang', 'is_correct' => false],
                        ],
                    ],
                ],
                'Web Development' => [
                    [
                        'question' => 'Which HTML tag is used to create a hyperlink?',
                        'points' => 1,
                        'answers' => [
                            ['text' => '<link>', 'is_correct' => false],
                            ['text' => '<a>', 'is_correct' => true],
                            ['text' => '<href>', 'is_correct' => false],
                            ['text' => '<span>', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What does the <div> tag in HTML represent?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'A predefined style for text', 'is_correct' => false],
                            ['text' => 'A block-level container for grouping content', 'is_correct' => true],
                            ['text' => 'A hyperlink to another page', 'is_correct' => false],
                            ['text' => 'A metadata description', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is the correct way to apply CSS internally in an HTML page?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'inside <style> ... </style>', 'is_correct' => true],
                            ['text' => 'css {color: blue;}', 'is_correct' => false],
                            ['text' => 'body {color: blue;}', 'is_correct' => false],
                            ['text' => 'style="body {color: blue;}"', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which JavaScript statement is used to declare a variable?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'var, let, or const', 'is_correct' => true],
                            ['text' => 'declare', 'is_correct' => false],
                            ['text' => 'define', 'is_correct' => false],
                            ['text' => 'variable', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What will console.log(typeof null) output in JavaScript?',
                        'points' => 1,
                        'answers' => [
                            ['text' => '"null"', 'is_correct' => false],
                            ['text' => '"undefined"', 'is_correct' => false],
                            ['text' => '"object"', 'is_correct' => true],
                            ['text' => '"number"', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is true about JavaScript?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'It is a server-side only language', 'is_correct' => false],
                            ['text' => 'It is a styling language', 'is_correct' => false],
                            ['text' => 'It can manipulate HTML and CSS dynamically', 'is_correct' => true],
                            ['text' => 'It replaces HTML completely', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which protocol is primarily used for communication between browsers and servers in web development?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'FTP', 'is_correct' => false],
                            ['text' => 'SMTP', 'is_correct' => false],
                            ['text' => 'HTTP/HTTPS', 'is_correct' => true],
                            ['text' => 'TCP/IP only', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is NOT a backend technology?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Node.js', 'is_correct' => false],
                            ['text' => 'PHP', 'is_correct' => false],
                            ['text' => 'Python (Flask/Django)', 'is_correct' => false],
                            ['text' => 'CSS', 'is_correct' => true],
                        ],
                    ],
                    [
                        'question' => 'Which of the following SQL commands is used to fetch data from a database?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'GET', 'is_correct' => false],
                            ['text' => 'SELECT', 'is_correct' => true],
                            ['text' => 'EXTRACT', 'is_correct' => false],
                            ['text' => 'FETCH', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What does responsive web design mean?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'The website responds only to mouse clicks', 'is_correct' => false],
                            ['text' => 'The website adapts to different devices and screen sizes', 'is_correct' => true],
                            ['text' => 'The website reloads after every user input', 'is_correct' => false],
                            ['text' => 'The website responds only to voice commands', 'is_correct' => false],
                        ],
                    ],
                ],
                'Python Programming' => [
                    [
                        'question' => 'Which of the following is the correct way to print "Hello World" in Python?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'echo("Hello World")', 'is_correct' => false],
                            ['text' => 'printf("Hello World")', 'is_correct' => false],
                            ['text' => 'print("Hello World")', 'is_correct' => true],
                            ['text' => 'cout << "Hello World"', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Python is considered a:',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Low-level language', 'is_correct' => false],
                            ['text' => 'Markup language', 'is_correct' => false],
                            ['text' => 'High-level, interpreted language', 'is_correct' => true],
                            ['text' => 'Machine-dependent language', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which symbol is used for comments in Python?',
                        'points' => 1,
                        'answers' => [
                            ['text' => '//', 'is_correct' => false],
                            ['text' => '#', 'is_correct' => true],
                            ['text' => '/* */', 'is_correct' => false],
                            ['text' => '<-- -->', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What is the output of the following code?\nx = 5\ny = "5"\nprint(type(x), type(y))',
                        'points' => 1,
                        'answers' => [
                            ['text' => "<class 'int'> <class 'str'>", 'is_correct' => true],
                            ['text' => "<class 'int'> <class 'int'>", 'is_correct' => false],
                            ['text' => "<class 'str'> <class 'str'>", 'is_correct' => false],
                            ['text' => 'Error', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is mutable in Python?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Tuple', 'is_correct' => false],
                            ['text' => 'String', 'is_correct' => false],
                            ['text' => 'List', 'is_correct' => true],
                            ['text' => 'Integer', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is a valid variable name in Python?',
                        'points' => 1,
                        'answers' => [
                            ['text' => '2value', 'is_correct' => false],
                            ['text' => 'value_2', 'is_correct' => true],
                            ['text' => 'value-2', 'is_correct' => false],
                            ['text' => '@value2', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What is the output of the following code?\nfor i in range(3):\n    print(i)',
                        'points' => 1,
                        'answers' => [
                            ['text' => '1 2 3', 'is_correct' => false],
                            ['text' => '0 1 2', 'is_correct' => true],
                            ['text' => '0 1 2 3', 'is_correct' => false],
                            ['text' => 'Error', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following loop runs at least once in Python?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'for loop', 'is_correct' => false],
                            ['text' => 'while loop', 'is_correct' => false],
                            ['text' => 'Both may not run if conditions fail', 'is_correct' => true],
                            ['text' => 'Python does not support loops', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'How do you define a function in Python?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'function myFunc():', 'is_correct' => false],
                            ['text' => 'def myFunc():', 'is_correct' => true],
                            ['text' => 'func myFunc():', 'is_correct' => false],
                            ['text' => 'create myFunc():', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What is inheritance in Python OOP?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'The ability to reuse existing code by deriving new classes from old ones', 'is_correct' => true],
                            ['text' => 'Writing multiple functions with the same name', 'is_correct' => false],
                            ['text' => 'Hiding implementation details of a class', 'is_correct' => false],
                            ['text' => 'Using one function for multiple purposes', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which keyword is used to create a class in Python?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'define', 'is_correct' => false],
                            ['text' => 'struct', 'is_correct' => false],
                            ['text' => 'class', 'is_correct' => true],
                            ['text' => 'object', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which keyword is used to call the constructor in Python classes?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'init()', 'is_correct' => false],
                            ['text' => '__init__()', 'is_correct' => true],
                            ['text' => 'constructor()', 'is_correct' => false],
                            ['text' => 'new()', 'is_correct' => false],
                        ],
                    ],
                ],
                'Java Programming' => [
                    [
                        'question' => 'Which keyword is used to define a class in Java?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'define', 'is_correct' => false],
                            ['text' => 'new', 'is_correct' => false],
                            ['text' => 'class', 'is_correct' => true],
                            ['text' => 'object', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is not a primitive data type in Java?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'int', 'is_correct' => false],
                            ['text' => 'boolean', 'is_correct' => false],
                            ['text' => 'String', 'is_correct' => true],
                            ['text' => 'double', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What is the correct way to declare a constant variable in Java?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'constant int MAX = 100;', 'is_correct' => false],
                            ['text' => 'final int MAX = 100;', 'is_correct' => true],
                            ['text' => 'const int MAX = 100;', 'is_correct' => false],
                            ['text' => 'static int MAX = 100;', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What will the following code output?\nint x = 5;\nSystem.out.println(x++);',
                        'points' => 1,
                        'answers' => [
                            ['text' => '5', 'is_correct' => true],
                            ['text' => '6', 'is_correct' => false],
                            ['text' => 'Error', 'is_correct' => false],
                            ['text' => '0', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which method is the entry point of every Java application?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'public static void main(String[] args)', 'is_correct' => true],
                            ['text' => 'public static void start(String[] args)', 'is_correct' => false],
                            ['text' => 'public void run()', 'is_correct' => false],
                            ['text' => 'public static void execute(String[] args)', 'is_correct' => false],
                        ],
                    ],
                ],
            ],
            'Soft Skill' => [
                'Communication Skills' => [
                    [
                        'question' => 'During a project presentation, what is the best way to keep your audience engaged?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Speak quickly to cover more points', 'is_correct' => false],
                            ['text' => 'Maintain eye contact and use clear, confident tone', 'is_correct' => true],
                            ['text' => 'Read directly from the slides', 'is_correct' => false],
                            ['text' => 'Avoid questions from the audience', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following is the most professional way to start an email to your project mentor?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Hey there!', 'is_correct' => false],
                            ['text' => 'Good day Sir/Ma\'am,', 'is_correct' => true],
                            ['text' => 'What\'s up!', 'is_correct' => false],
                            ['text' => 'Hi mentor,', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'When a teammate is explaining their idea, what should you do to show active listening?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Interrupt to share your own thoughts', 'is_correct' => false],
                            ['text' => 'Look at your phone while they talk', 'is_correct' => false],
                            ['text' => 'Nod, take notes, and ask clarifying questions', 'is_correct' => true],
                            ['text' => 'Wait silently until they finish without any reaction', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'If a team member disagrees with your idea, what is the most appropriate response?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Argue until they agree with you', 'is_correct' => false],
                            ['text' => 'Ignore their feedback and continue', 'is_correct' => false],
                            ['text' => 'Listen, discuss the reasons, and find a compromise', 'is_correct' => true],
                            ['text' => 'Leave the conversation', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'In a client meeting, what should you do if you don\'t understand a question?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Pretend you understand and answer anyway', 'is_correct' => false],
                            ['text' => 'Stay silent', 'is_correct' => false],
                            ['text' => 'Politely ask for clarification before responding', 'is_correct' => true],
                            ['text' => 'Change the topic', 'is_correct' => false],
                        ],
                    ],
                ],
                'Problem-Solving and Analytical Skills' => [
                    [
                        'question' => 'Which of the following best describes the purpose of an algorithm?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'To make the program run faster', 'is_correct' => false],
                            ['text' => 'To provide a step-by-step solution to a problem', 'is_correct' => true],
                            ['text' => 'To compile code into an executable file', 'is_correct' => false],
                            ['text' => 'To design the user interface', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'If a system suddenly starts producing incorrect output after an update, what should be your first step as a developer?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Reinstall the entire system', 'is_correct' => false],
                            ['text' => 'Check the new code changes for logical errors', 'is_correct' => true],
                            ['text' => 'Blame the database administrator', 'is_correct' => false],
                            ['text' => 'Restart the computer', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'You are given two algorithms:\nAlgorithm A: completes in 5 seconds but uses more memory\nAlgorithm B: completes in 10 seconds but uses less memory\nWhich should you choose if system resources are limited?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Algorithm A', 'is_correct' => false],
                            ['text' => 'Algorithm B', 'is_correct' => true],
                            ['text' => 'Either, since both give the same output', 'is_correct' => false],
                            ['text' => 'None, both are inefficient', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'A loop runs infinitely even though the condition should stop it. What is the most likely cause?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'The variable in the condition is not being updated correctly', 'is_correct' => true],
                            ['text' => 'The compiler is outdated', 'is_correct' => false],
                            ['text' => 'The program lacks comments', 'is_correct' => false],
                            ['text' => 'The syntax of the loop is correct', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'When solving a complex programming problem, which is the best initial approach?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Start coding immediately', 'is_correct' => false],
                            ['text' => 'Break the problem into smaller, manageable parts', 'is_correct' => true],
                            ['text' => 'Search for ready-made code online', 'is_correct' => false],
                            ['text' => 'Wait for someone else to solve it first', 'is_correct' => false],
                        ],
                    ],
                ],
                'Time Management' => [
                    [
                        'question' => 'You have three tasks due this week: a short quiz, a major project, and a group report. What should you do first?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Start with the easiest task to finish quickly', 'is_correct' => false],
                            ['text' => 'Work on the major project because it takes the most time', 'is_correct' => true],
                            ['text' => 'Do tasks randomly as you feel like it', 'is_correct' => false],
                            ['text' => 'Wait until the deadline is near', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'What is the main benefit of setting SMART goals (Specific, Measurable, Achievable, Relevant, Time-bound)?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'It guarantees success', 'is_correct' => false],
                            ['text' => 'It helps organize tasks clearly and track progress effectively', 'is_correct' => true],
                            ['text' => 'It makes the work harder', 'is_correct' => false],
                            ['text' => 'It removes the need for deadlines', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'How can you effectively manage your time during multiple deadlines?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Create a daily or weekly schedule to allocate time for each task', 'is_correct' => true],
                            ['text' => 'Focus only on one subject and ignore others', 'is_correct' => false],
                            ['text' => 'Multitask on everything at the same time', 'is_correct' => false],
                            ['text' => 'Skip rest periods to gain more hours', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'When you feel like procrastinating, what\'s the best strategy to stay productive?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Wait until you feel motivated', 'is_correct' => false],
                            ['text' => 'Break large tasks into smaller, easier parts', 'is_correct' => true],
                            ['text' => 'Watch a show to relax longer', 'is_correct' => false],
                            ['text' => 'Do something else unrelated', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'You realize you might miss a project deadline. What should you do?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Keep quiet and submit late', 'is_correct' => false],
                            ['text' => 'Inform your instructor or supervisor early and request an extension', 'is_correct' => true],
                            ['text' => 'Blame your teammates for the delay', 'is_correct' => false],
                            ['text' => 'Skip submitting entirely', 'is_correct' => false],
                        ],
                    ],
                ],
                'Professionalism' => [
                    [
                        'question' => 'You discover that a teammate copied code from the internet without proper credit. What should you do?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Ignore it since the work was completed', 'is_correct' => false],
                            ['text' => 'Report it to your team leader or instructor', 'is_correct' => true],
                            ['text' => 'Use the same code yourself', 'is_correct' => false],
                            ['text' => 'Remove their name from the project', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'You made a mistake that caused an error in a client project. What is the most professional response?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Hide the mistake and hope no one notices', 'is_correct' => false],
                            ['text' => 'Blame someone else', 'is_correct' => false],
                            ['text' => 'Admit the mistake, fix it, and learn from it', 'is_correct' => true],
                            ['text' => 'Delete the project to avoid issues', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'A coworker or classmate shares an idea during a meeting that you don\'t agree with. What\'s the best way to respond?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Interrupt and say it won\'t work', 'is_correct' => false],
                            ['text' => 'Stay quiet and ignore the idea', 'is_correct' => false],
                            ['text' => 'Listen respectfully and offer constructive feedback', 'is_correct' => true],
                            ['text' => 'Laugh and make a joke about it', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Your supervisor shares confidential project information with you. What should you do?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Post about it on social media to share your excitement', 'is_correct' => false],
                            ['text' => 'Tell your classmates about the project', 'is_correct' => false],
                            ['text' => 'Keep it private and discuss it only with authorized people', 'is_correct' => true],
                            ['text' => 'Use it for your personal school project', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following demonstrates professionalism in the workplace?',
                        'points' => 1,
                        'answers' => [
                            ['text' => 'Arriving late but staying online the whole day', 'is_correct' => false],
                            ['text' => 'Following company policies and meeting deadlines', 'is_correct' => true],
                            ['text' => 'Ignoring emails and messages', 'is_correct' => false],
                            ['text' => 'Complaining about coworkers publicly', 'is_correct' => false],
                        ],
                    ],
                ],
            ],
        ];
    }
}

