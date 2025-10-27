<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Question;
use App\Models\Choice;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Developer Skills Assessment' => [
                'General Programming Concepts' => [
                    [
                        'question' => 'Which of the following is a compiled language?',
                        'type' => 'quiz',
                        'choices' => ['HTML', 'JAVA', 'C++', 'Python'],
                        'correct_answer' => 'C++'
                    ],
                    [
                        'question' => 'Which of the following is not a programming language?',
                        'type' => 'quiz',
                        'choices' => ['HTML', 'JAVA', 'C', 'Python'],
                        'correct_answer' => 'HTML'
                    ],
                    [
                        'question' => 'Which of the following describes dynamic typing?',
                        'type' => 'quiz',
                        'choices' => [
                            'Variables must declare type before assignment',
                            'Variable type can change at runtime',
                            'Variables cannot be reassigned',
                            'Only numeric types are supported'
                        ],
                        'correct_answer' => 'Variable type can change at runtime'
                    ],
                ],
                'Problem Solving and Logic' => [
                    [
                        'question' => 'Which search algorithm works only on sorted data?',
                        'type' => 'quiz',
                        'choices' => ['Linear Search', 'Binary Search', 'Bubble Sort', 'Depth First Search (DFS)'],
                        'correct_answer' => 'Binary Search'
                    ],
                    [
                        'question' => 'Which data structure is used to implement a stack?',
                        'type' => 'quiz',
                        'choices' => ['Queue', 'Array or Linked List', 'Hash Table', 'Tree'],
                        'correct_answer' => 'Array or Linked List'
                    ],
                    [
                        'question' => 'Which of the following is best for checking balanced parentheses in an expression?',
                        'type' => 'quiz',
                        'choices' => ['Queue', 'Stack', 'Linked List', 'Array'],
                        'correct_answer' => 'Stack'
                    ],
                ],
                'Code Quality and Best Practices' => [
                    [
                        'question' => 'What does the DRY principle mean?',
                        'type' => 'quiz',
                        'choices' => [
                            'Don\'t Repeat Yourself (reuse functions and avoid duplicate code)',
                            'Debug, Run, Yield',
                            'Define Readable YAML',
                            'Do Run Yourself'
                        ],
                        'correct_answer' => 'Don\'t Repeat Yourself (reuse functions and avoid duplicate code)'
                    ],
                    [
                        'question' => 'Which of the following is a good variable naming practice?',
                        'type' => 'quiz',
                        'choices' => ['a1, b2, c3', 'totalPrice, studentName', 'x, y, z', 'var1, var2, var3'],
                        'correct_answer' => 'totalPrice, studentName'
                    ],
                    [
                        'question' => 'Why are code comments important?',
                        'type' => 'quiz',
                        'choices' => [
                            'They make the program faster',
                            'They help humans understand the code',
                            'They reduce memory usage',
                            'They are required by the compiler'
                        ],
                        'correct_answer' => 'They help humans understand the code'
                    ],
                ],
                'Object-Oriented Programming' => [
                    [
                        'question' => 'Which of the following are pillars of OOP?',
                        'type' => 'quiz',
                        'choices' => [
                            'Inheritance, Encapsulation, Polymorphism',
                            'Encapsulation, Debugging, Polymorphism',
                            'Overloading, Debugging, Reusability',
                            'Compilation, Abstraction, Documentation'
                        ],
                        'correct_answer' => 'Inheritance, Encapsulation, Polymorphism'
                    ],
                    [
                        'question' => 'Which keyword is used in Java to create a new object?',
                        'type' => 'quiz',
                        'choices' => ['new', 'class', 'this', 'make'],
                        'correct_answer' => 'new'
                    ],
                    [
                        'question' => 'Which statement best describes encapsulation?',
                        'type' => 'quiz',
                        'choices' => [
                            'Hiding internal details using private variables and public methods',
                            'Redefining methods from a parent class',
                            'Writing multiple functions with the same name',
                            'Declaring all variables global'
                        ],
                        'correct_answer' => 'Hiding internal details using private variables and public methods'
                    ],
                ],
                'Debugging and Testing' => [
                    [
                        'question' => 'If a program compiles successfully but gives wrong output, it is a:',
                        'type' => 'quiz',
                        'choices' => ['Syntax error', 'Logic error', 'Runtime error', 'Exception error'],
                        'correct_answer' => 'Logic error'
                    ],
                    [
                        'question' => 'What is the purpose of unit testing?',
                        'type' => 'quiz',
                        'choices' => [
                            'Test the whole system',
                            'Test individual functions or modules',
                            'Test only the database',
                            'Test only user interface'
                        ],
                        'correct_answer' => 'Test individual functions or modules'
                    ],
                    [
                        'question' => 'Which of the following tools helps in debugging code?',
                        'type' => 'quiz',
                        'choices' => ['IDE Debugger', 'Excel', 'Word', 'Photoshop'],
                        'correct_answer' => 'IDE Debugger'
                    ],
                ],
                'Developer Tools and Practices' => [
                    [
                        'question' => 'Which of the following is a version control system?',
                        'type' => 'quiz',
                        'choices' => ['MySQL', 'Git', 'Docker', 'Jenkins'],
                        'correct_answer' => 'Git'
                    ],
                    [
                        'question' => 'Which Git command uploads your local changes to the remote repository?',
                        'type' => 'quiz',
                        'choices' => ['git push', 'git pull', 'git clone', 'git commit'],
                        'correct_answer' => 'git push'
                    ],
                    [
                        'question' => 'In software development, which of the following is part of SDLC?',
                        'type' => 'quiz',
                        'choices' => ['Requirement Analysis', 'Design', 'Implementation', 'All of the above'],
                        'correct_answer' => 'All of the above'
                    ],
                    [
                        'question' => 'What is the difference between pass by value and pass by reference?',
                        'type' => 'quiz',
                        'choices' => [
                            'Pass by value sends a copy, pass by reference sends the memory address',
                            'Pass by value modifies the original variable',
                            'Pass by reference always increases memory usage',
                            'They are exactly the same'
                        ],
                        'correct_answer' => 'Pass by value sends a copy, pass by reference sends the memory address'
                    ],
                ],
            ],
            'Database Management' => [
                'Basic Concepts' => [
                    [
                        'question' => 'Which of the following is a relational database management system (RDBMS)?',
                        'type' => 'quiz',
                        'choices' => ['MySQL', 'MongoDB', 'PostgreSQL', 'Oracle'],
                        'correct_answer' => 'MySQL'
                    ],
                    [
                        'question' => 'In a relational database, a primary key must be:',
                        'type' => 'quiz',
                        'choices' => ['Unique and not null', 'Always auto-increment', 'Nullable', 'Used only once in the database'],
                        'correct_answer' => 'Unique and not null'
                    ],
                    [
                        'question' => 'Which is true about a foreign key?',
                        'type' => 'quiz',
                        'choices' => [
                            'It uniquely identifies a row within a table',
                            'It enforces a relationship between two tables',
                            'It stores multiple values in a single column',
                            'It can only reference the same table'
                        ],
                        'correct_answer' => 'It enforces a relationship between two tables'
                    ],
                ],
                'SQL Queries' => [
                    [
                        'question' => 'Which SQL command is used to retrieve data from a database?',
                        'type' => 'quiz',
                        'choices' => ['UPDATE', 'INSERT', 'SELECT', 'DELETE'],
                        'correct_answer' => 'SELECT'
                    ],
                    [
                        'question' => 'Which SQL clause is used to filter results?',
                        'type' => 'quiz',
                        'choices' => ['ORDER BY', 'WHERE', 'GROUP BY', 'HAVING'],
                        'correct_answer' => 'WHERE'
                    ],
                    [
                        'question' => 'Which SQL command is used to remove all rows from a table but keep the structure?',
                        'type' => 'quiz',
                        'choices' => ['DELETE', 'DROP', 'TRUNCATE', 'REMOVE'],
                        'correct_answer' => 'TRUNCATE'
                    ],
                    [
                        'question' => 'What will the following SQL query return? SELECT COUNT(*) FROM Students;',
                        'type' => 'quiz',
                        'choices' => [
                            'The number of columns in Students table',
                            'The number of rows in Students table',
                            'The list of all student names',
                            'The highest student ID'
                        ],
                        'correct_answer' => 'The number of rows in Students table'
                    ],
                ],
                'Database Design and Normalization' => [
                    [
                        'question' => 'The process of removing data redundancy and improving data integrity is called:',
                        'type' => 'quiz',
                        'choices' => ['Normalization', 'Denormalization', 'Indexing', 'Aggregation'],
                        'correct_answer' => 'Normalization'
                    ],
                    [
                        'question' => 'Which of the following is a feature of 1st Normal Form (1NF)?',
                        'type' => 'quiz',
                        'choices' => [
                            'Each table must have a primary key',
                            'Each field must contain atomic (indivisible) values',
                            'No transitive dependencies',
                            'No repeating groups'
                        ],
                        'correct_answer' => 'Each field must contain atomic (indivisible) values'
                    ],
                    [
                        'question' => 'Which of the following improves query performance in large tables?',
                        'type' => 'quiz',
                        'choices' => ['Normalization', 'Foreign keys', 'Indexing', 'Triggers'],
                        'correct_answer' => 'Indexing'
                    ],
                    [
                        'question' => 'You need to store student grades with subject names and scores. Which design is best?',
                        'type' => 'quiz',
                        'choices' => [
                            'Store all grades in a single column separated by commas',
                            'Create a separate Grades table with foreign key to Students table',
                            'Put grades directly inside the Students table as multiple columns',
                            'Save grades in a text file instead of a database'
                        ],
                        'correct_answer' => 'Create a separate Grades table with foreign key to Students table'
                    ],
                    [
                        'question' => 'Which of the following is an advantage of using JOIN in SQL?',
                        'type' => 'quiz',
                        'choices' => [
                            'It merges two or more databases into one',
                            'It combines rows from two or more tables based on related columns',
                            'It creates a backup of the database',
                            'It permanently deletes duplicates'
                        ],
                        'correct_answer' => 'It combines rows from two or more tables based on related columns'
                    ],
                ],
            ],
            'System and Software Development' => [
                'Software Development Life Cycle' => [
                    [
                        'question' => 'Which of the following is the correct order of the traditional SDLC phases?',
                        'type' => 'quiz',
                        'choices' => [
                            'Design -> Implementation -> Testing -> Deployment -> Requirement Analysis -> Maintenance',
                            'Requirement Analysis -> Design -> Implementation -> Testing -> Deployment -> Maintenance',
                            'Testing -> Deployment -> Requirement Analysis -> Design -> Implementation -> Maintenance',
                            'Deployment -> Maintenance -> Design -> Implementation -> Testing -> Requirement Analysis'
                        ],
                        'correct_answer' => 'Requirement Analysis -> Design -> Implementation -> Testing -> Deployment -> Maintenance'
                    ],
                    [
                        'question' => 'Which SDLC model delivers software in small increments or iterations?',
                        'type' => 'quiz',
                        'choices' => ['Waterfall', 'Spiral', 'Agile', 'V-Model'],
                        'correct_answer' => 'Agile'
                    ],
                    [
                        'question' => 'The main advantage of the Agile methodology is:',
                        'type' => 'quiz',
                        'choices' => [
                            'Strict step-by-step execution',
                            'Faster adaptability to change and collaboration',
                            'Complete documentation before coding',
                            'No need for user involvement'
                        ],
                        'correct_answer' => 'Faster adaptability to change and collaboration'
                    ],
                ],
                'System Development Concepts' => [
                    [
                        'question' => 'What is feasibility analysis in system development?',
                        'type' => 'quiz',
                        'choices' => [
                            'Testing the speed of the system',
                            'Checking whether the project is financially, technically, and operationally possible',
                            'Writing code for the system',
                            'Designing the database schema'
                        ],
                        'correct_answer' => 'Checking whether the project is financially, technically, and operationally possible'
                    ],
                    [
                        'question' => 'Which of the following is a non-functional requirement?',
                        'type' => 'quiz',
                        'choices' => [
                            'The system must allow students to register online',
                            'The system must process 1,000 requests per second',
                            'The system must allow teachers to upload grades',
                            'The system must provide login access'
                        ],
                        'correct_answer' => 'The system must process 1,000 requests per second'
                    ],
                ],
                'Software Engineering Practices' => [
                    [
                        'question' => 'Which practice ensures quality and correctness of software before release?',
                        'type' => 'quiz',
                        'choices' => ['Prototyping', 'Testing', 'Refactoring', 'Documentation'],
                        'correct_answer' => 'Testing'
                    ],
                    [
                        'question' => 'What does refactoring mean in software development?',
                        'type' => 'quiz',
                        'choices' => [
                            'Adding more features without changing existing code',
                            'Restructuring existing code to improve readability and maintainability without changing behavior',
                            'Removing unused functions permanently',
                            'Debugging syntax errors'
                        ],
                        'correct_answer' => 'Restructuring existing code to improve readability and maintainability without changing behavior'
                    ],
                    [
                        'question' => 'Which of the following is a version control system used in software development?',
                        'type' => 'quiz',
                        'choices' => ['Docker', 'Git', 'Jenkins', 'VS Code'],
                        'correct_answer' => 'Git'
                    ],
                    [
                        'question' => 'Your team is building an online library system. The client suddenly changes a major requirement. Which development approach is best?',
                        'type' => 'quiz',
                        'choices' => ['Waterfall', 'Agile', 'Prototype-only model', 'Big Bang'],
                        'correct_answer' => 'Agile'
                    ],
                ],
            ],
            'Web Development' => [
                'HTML and CSS' => [
                    [
                        'question' => 'Which HTML tag is used to create a hyperlink?',
                        'type' => 'quiz',
                        'choices' => ['<a>', '<linl>', '<href>', '<url>'],
                        'correct_answer' => '<a>'
                    ],
                    [
                        'question' => 'What does the <div> tag in HTML represent?',
                        'type' => 'quiz',
                        'choices' => [
                            'A predefined style for text',
                            'A block-level container for grouping content',
                            'A hyperlink to another page',
                            'A metadata description'
                        ],
                        'correct_answer' => 'A block-level container for grouping content'
                    ],
                    [
                        'question' => 'Which of the following is the correct way to apply CSS internally in an HTML page?',
                        'type' => 'quiz',
                        'choices' => [
                            '<style> body {color: blue;} </style> inside <head>',
                            'css {color: blue;} inside<body>',
                            '<css>body {color: blue;} </css>inside <head>',
                            'style="body {color: blue;}" inside<body>'
                        ],
                        'correct_answer' => '<style> body {color: blue;} </style> inside <head>'
                    ],
                ],
                'JavaScript and Client-Side Scripting' => [
                    [
                        'question' => 'Which JavaScript statement is used to declare a variable?',
                        'type' => 'quiz',
                        'choices' => ['var, let, or const', 'declare', 'define', 'variable'],
                        'correct_answer' => 'var, let, or const'
                    ],
                    [
                        'question' => 'What will console.log(typeof null) output in JavaScript?',
                        'type' => 'quiz',
                        'choices' => ['"null"', '"undefined"', '"object"', '"number"'],
                        'correct_answer' => '"object"'
                    ],
                    [
                        'question' => 'Which of the following is true about JavaScript?',
                        'type' => 'quiz',
                        'choices' => [
                            'It is a server-side only language',
                            'It is a styling language',
                            'It can manipulate HTML and CSS dynamically',
                            'It replaces HTML completely'
                        ],
                        'correct_answer' => 'It can manipulate HTML and CSS dynamically'
                    ],
                ],
                'Backend Development and Databases' => [
                    [
                        'question' => 'Which protocol is primarily used for communication between browsers and servers in web development?',
                        'type' => 'quiz',
                        'choices' => ['FTP', 'SMTP', 'HTTP/HTTPS', 'TCP/IP only'],
                        'correct_answer' => 'HTTP/HTTPS'
                    ],
                    [
                        'question' => 'Which of the following is NOT a backend technology?',
                        'type' => 'quiz',
                        'choices' => ['Node.js', 'PHP', 'Python (Flask/Django)', 'CSS'],
                        'correct_answer' => 'CSS'
                    ],
                    [
                        'question' => 'Which of the following SQL commands is used to fetch data from a database?',
                        'type' => 'quiz',
                        'choices' => ['GET', 'SELECT', 'EXTRACT', 'FETCH'],
                        'correct_answer' => 'SELECT'
                    ],
                    [
                        'question' => 'What does responsive web design mean?',
                        'type' => 'quiz',
                        'choices' => [
                            'The website responds only to mouse clicks',
                            'The website adapts to different devices and screen sizes',
                            'The website reloads after every user input',
                            'The website responds only to voice commands'
                        ],
                        'correct_answer' => 'The website adapts to different devices and screen sizes'
                    ],
                ],
            ],
            'Python Programming' => [
                'Python Basics' => [
                    [
                        'question' => 'Which of the following is the correct way to print "Hello World" in Python?',
                        'type' => 'quiz',
                        'choices' => ['echo("Hello World")', 'printf("Hello World")', 'print("Hello World")', 'cout << "Hello World"'],
                        'correct_answer' => 'print("Hello World")'
                    ],
                    [
                        'question' => 'Python is considered a:',
                        'type' => 'quiz',
                        'choices' => ['Low-level language', 'Markup language', 'High-level, interpreted language', 'Machine-dependent language'],
                        'correct_answer' => 'High-level, interpreted language'
                    ],
                    [
                        'question' => 'Which symbol is used for comments in Python?',
                        'type' => 'quiz',
                        'choices' => ['//', '<!--  -->', '#', '/* */'],
                        'correct_answer' => '#'
                    ],
                ],
                'Variables and Data Types' => [
                    [
                        'question' => 'What is the output of the following code? x = 5; y = "5"; print(type(x), type(y))',
                        'type' => 'quiz',
                        'choices' => [
                            '<class \'int\'> <class \'int\'>',
                            '<class \'string\'> <class \'int\'>',
                            '<class \'int\'> <class \'str\'>',
                            'Error'
                        ],
                        'correct_answer' => '<class \'int\'> <class \'str\'>'
                    ],
                    [
                        'question' => 'Which of the following is mutable in Python?',
                        'type' => 'quiz',
                        'choices' => ['Tuple', 'String', 'List', 'Integer'],
                        'correct_answer' => 'List'
                    ],
                    [
                        'question' => 'Which of the following is a valid variable name in Python?',
                        'type' => 'quiz',
                        'choices' => ['2value', 'value_2', 'value-2', '@value2'],
                        'correct_answer' => 'value_2'
                    ],
                ],
                'Control Structures' => [
                    [
                        'question' => 'What is the output of the following code? for i in range(3): print(i)',
                        'type' => 'quiz',
                        'choices' => ['1 2 3', '0 1 2', '0 1 2 3', 'Error'],
                        'correct_answer' => '0 1 2'
                    ],
                    [
                        'question' => 'Which of the following loop runs at least once in Python?',
                        'type' => 'quiz',
                        'choices' => ['for loop', 'while loop', 'Both may not run if conditions fail', 'Python does not support loops'],
                        'correct_answer' => 'while loop'
                    ],
                ],
                'Functions and OOP' => [
                    [
                        'question' => 'How do you define a function in Python?',
                        'type' => 'quiz',
                        'choices' => ['function myFunc():', 'def myFunc():', 'func myFunc():', 'create myFunc():'],
                        'correct_answer' => 'def myFunc():'
                    ],
                    [
                        'question' => 'What is inheritance in Python OOP?',
                        'type' => 'quiz',
                        'choices' => [
                            'The ability to reuse existing code by deriving new classes from old ones',
                            'Writing multiple functions with the same name',
                            'Hiding implementation details of a class',
                            'Using one function for multiple purposes'
                        ],
                        'correct_answer' => 'The ability to reuse existing code by deriving new classes from old ones'
                    ],
                    [
                        'question' => 'Which keyword is used to create a class in Python?',
                        'type' => 'quiz',
                        'choices' => ['define', 'struct', 'class', 'object'],
                        'correct_answer' => 'class'
                    ],
                    [
                        'question' => 'Which keyword is used to call the constructor in Python classes?',
                        'type' => 'quiz',
                        'choices' => ['init()', '__init__()', 'constructor()', 'new()'],
                        'correct_answer' => '__init__()'
                    ],
                ],
            ],
            'Java' => [
                'Java Syntax and Data Types' => [
                    [
                        'question' => 'Which keyword is used to define a class in Java?',
                        'type' => 'quiz',
                        'choices' => ['define', 'new', 'class', 'object'],
                        'correct_answer' => 'class'
                    ],
                    [
                        'question' => 'Which of the following is not a primitive data type in Java?',
                        'type' => 'quiz',
                        'choices' => ['int', 'boolean', 'String', 'double'],
                        'correct_answer' => 'String'
                    ],
                    [
                        'question' => 'What is the correct way to declare a constant variable in Java?',
                        'type' => 'quiz',
                        'choices' => ['constant int MAX = 100;', 'final int MAX = 100;', 'const int MAX = 100;', 'static int MAX = 100;'],
                        'correct_answer' => 'final int MAX = 100;'
                    ],
                ],
                'Operators and Program Flow' => [
                    [
                        'question' => 'What will the following code output? int x = 5; System.out.println(x++);',
                        'type' => 'quiz',
                        'choices' => ['5', '6', 'Error', '0'],
                        'correct_answer' => '5'
                    ],
                    [
                        'question' => 'Which method is the entry point of every Java application?',
                        'type' => 'quiz',
                        'choices' => [
                            'public static void main(String[] args)',
                            'public static void start(String[] args)',
                            'public void run()',
                            'public static void execute(String[] args)'
                        ],
                        'correct_answer' => 'public static void main(String[] args)'
                    ],
                ],
            ],
            'Soft Skills' => [
                'Communication Skills Assessment' => [
                    [
                        'question' => 'During a project presentation, what is the best way to keep your audience engaged?',
                        'type' => 'quiz',
                        'choices' => [
                            'Speak quickly to cover more points',
                            'Maintain eye contact and use clear, confident tone',
                            'Read directly from the slides',
                            'Avoid questions from the audience'
                        ],
                        'correct_answer' => 'Maintain eye contact and use clear, confident tone'
                    ],
                    [
                        'question' => 'Which of the following is the most professional way to start an email to your project mentor?',
                        'type' => 'quiz',
                        'choices' => ['Hey there!', 'Good day Sir/Ma\'am,', 'What\'s up!', 'Hi mentor,'],
                        'correct_answer' => 'Good day Sir/Ma\'am,'
                    ],
                    [
                        'question' => 'When a teammate is explaining their idea, what should you do to show active listening?',
                        'type' => 'quiz',
                        'choices' => [
                            'Interrupt to share your own thoughts',
                            'Look at your phone while they talk',
                            'Nod, take notes, and ask clarifying questions',
                            'Wait silently until they finish without any reaction'
                        ],
                        'correct_answer' => 'Nod, take notes, and ask clarifying questions'
                    ],
                    [
                        'question' => 'If a team member disagrees with your idea, what is the most appropriate response?',
                        'type' => 'quiz',
                        'choices' => [
                            'Argue until they agree with you',
                            'Ignore their feedback and continue',
                            'Listen, discuss the reasons, and find a compromise',
                            'Leave the conversation'
                        ],
                        'correct_answer' => 'Listen, discuss the reasons, and find a compromise'
                    ],
                    [
                        'question' => 'In a client meeting, what should you do if you don\'t understand a question?',
                        'type' => 'quiz',
                        'choices' => [
                            'Pretend you understand and answer anyway',
                            'Stay silent',
                            'Politely ask for clarification before responding',
                            'Change the topic'
                        ],
                        'correct_answer' => 'Politely ask for clarification before responding'
                    ],
                ],
                'Problem-Solving and Analytical Skills Assessment' => [
                    [
                        'question' => 'Which of the following best describes the purpose of an algorithm?',
                        'type' => 'quiz',
                        'choices' => [
                            'To make the program run faster',
                            'To provide a step-by-step solution to a problem',
                            'To compile code into an executable file',
                            'To design the user interface'
                        ],
                        'correct_answer' => 'To provide a step-by-step solution to a problem'
                    ],
                    [
                        'question' => 'If a system suddenly starts producing incorrect output after an update, what should be your first step as a developer?',
                        'type' => 'quiz',
                        'choices' => [
                            'Reinstall the entire system',
                            'Check the new code changes for logical errors',
                            'Blame the database administrator',
                            'Restart the computer'
                        ],
                        'correct_answer' => 'Check the new code changes for logical errors'
                    ],
                    [
                        'question' => 'You are given two algorithms: Algorithm A: completes in 5 seconds but uses more memory. Algorithm B: completes in 10 seconds but uses less memory. Which should you choose if system resources are limited?',
                        'type' => 'quiz',
                        'choices' => ['Algorithm A', 'Algorithm B', 'Either, since both give the same output', 'None, both are inefficient'],
                        'correct_answer' => 'Algorithm B'
                    ],
                    [
                        'question' => 'A loop runs infinitely even though the condition should stop it. What is the most likely cause?',
                        'type' => 'quiz',
                        'choices' => [
                            'The variable in the condition is not being updated correctly',
                            'The compiler is outdated',
                            'The program lacks comments',
                            'The syntax of the loop is correct'
                        ],
                        'correct_answer' => 'The variable in the condition is not being updated correctly'
                    ],
                    [
                        'question' => 'When solving a complex programming problem, which is the best initial approach?',
                        'type' => 'quiz',
                        'choices' => [
                            'Start coding immediately',
                            'Break the problem into smaller, manageable parts',
                            'Search for ready-made code online',
                            'Wait for someone else to solve it first'
                        ],
                        'correct_answer' => 'Break the problem into smaller, manageable parts'
                    ],
                ],
                'Time Management Skills Assessment' => [
                    [
                        'question' => 'You have three tasks due this week: a short quiz, a major project, and a group report. What should you do first?',
                        'type' => 'quiz',
                        'choices' => [
                            'Start with the easiest task to finish quickly',
                            'Work on the major project because it takes the most time',
                            'Do tasks randomly as you feel like it',
                            'Wait until the deadline is near'
                        ],
                        'correct_answer' => 'Work on the major project because it takes the most time'
                    ],
                    [
                        'question' => 'What is the main benefit of setting SMART goals (Specific, Measurable, Achievable, Relevant, Time-bound)?',
                        'type' => 'quiz',
                        'choices' => [
                            'It guarantees success',
                            'It helps organize tasks clearly and track progress effectively',
                            'It makes the work harder',
                            'It removes the need for deadlines'
                        ],
                        'correct_answer' => 'It helps organize tasks clearly and track progress effectively'
                    ],
                    [
                        'question' => 'How can you effectively manage your time during multiple deadlines?',
                        'type' => 'quiz',
                        'choices' => [
                            'Create a daily or weekly schedule to allocate time for each task',
                            'Focus only on one subject and ignore others',
                            'Multitask on everything at the same time',
                            'Skip rest periods to gain more hours'
                        ],
                        'correct_answer' => 'Create a daily or weekly schedule to allocate time for each task'
                    ],
                    [
                        'question' => 'When you feel like procrastinating, what\'s the best strategy to stay productive?',
                        'type' => 'quiz',
                        'choices' => [
                            'Wait until you feel motivated',
                            'Break large tasks into smaller, easier parts',
                            'Watch a show to relax longer',
                            'Do something else unrelated'
                        ],
                        'correct_answer' => 'Break large tasks into smaller, easier parts'
                    ],
                    [
                        'question' => 'You realize you might miss a project deadline. What should you do?',
                        'type' => 'quiz',
                        'choices' => [
                            'Keep quiet and submit late',
                            'Inform your instructor or supervisor early and request an extension',
                            'Blame your teammates for the delay',
                            'Skip submitting entirely'
                        ],
                        'correct_answer' => 'Inform your instructor or supervisor early and request an extension'
                    ],
                ],
                'Workplace Ethics and Professionalism Assessment' => [
                    [
                        'question' => 'You discover that a teammate copied code from the internet without proper credit. What should you do?',
                        'type' => 'quiz',
                        'choices' => [
                            'Ignore it since the work was completed',
                            'Report it to your team leader or instructor',
                            'Use the same code yourself',
                            'Remove their name from the project'
                        ],
                        'correct_answer' => 'Report it to your team leader or instructor'
                    ],
                    [
                        'question' => 'You made a mistake that caused an error in a client project. What is the most professional response?',
                        'type' => 'quiz',
                        'choices' => [
                            'Hide the mistake and hope no one notices',
                            'Blame someone else',
                            'Admit the mistake, fix it, and learn from it',
                            'Delete the project to avoid issues'
                        ],
                        'correct_answer' => 'Admit the mistake, fix it, and learn from it'
                    ],
                    [
                        'question' => 'A coworker or classmate shares an idea during a meeting that you don\'t agree with. What\'s the best way to respond?',
                        'type' => 'quiz',
                        'choices' => [
                            'Interrupt and say it won\'t work',
                            'Stay quiet and ignore the idea',
                            'Listen respectfully and offer constructive feedback',
                            'Laugh and make a joke about it'
                        ],
                        'correct_answer' => 'Listen respectfully and offer constructive feedback'
                    ],
                    [
                        'question' => 'Your supervisor shares confidential project information with you. What should you do?',
                        'type' => 'quiz',
                        'choices' => [
                            'Post about it on social media to share your excitement',
                            'Tell your classmates about the project',
                            'Keep it private and discuss it only with authorized people',
                            'Use it for your personal school project'
                        ],
                        'correct_answer' => 'Keep it private and discuss it only with authorized people'
                    ],
                    [
                        'question' => 'Which of the following demonstrates professionalism in the workplace?',
                        'type' => 'quiz',
                        'choices' => [
                            'Arriving late but staying online the whole day',
                            'Following company policies and meeting deadlines',
                            'Ignoring emails and messages',
                            'Complaining about coworkers publicly'
                        ],
                        'correct_answer' => 'Following company policies and meeting deadlines'
                    ],
                ],
            ],
        ];

        // Now iterate through the structure to create categories, subcategories, questions, and choices
        foreach ($categories as $categoryName => $subCategories) {
            // Create or retrieve the category
            $category = Category::firstOrCreate(['category_name' => $categoryName]);

            foreach ($subCategories as $subCategoryName => $questions) {
                // Create or retrieve the subcategory
                $subCategory = SubCategory::firstOrCreate([
                    'subcategory_name' => $subCategoryName,
                    'category_id' => $category->id,
                ]);

                foreach ($questions as $questionData) {
                    // Handle both structured quiz questions and simple text questions
                    if (is_array($questionData)) {
                        // Quiz question with choices
                        $question = Question::firstOrCreate([
                            'question' => $questionData['question'],
                            'subcategory_id' => $subCategory->id,
                        ], [
                            'question_type' => $questionData['type'] ?? 'rating',
                            'is_active' => true
                        ]);

                        // Create choices for quiz questions
                        if (isset($questionData['choices']) && $questionData['type'] === 'quiz') {
                            foreach ($questionData['choices'] as $choiceText) {
                                Choice::firstOrCreate([
                                    'question_id' => $question->id,
                                    'choice_text' => $choiceText,
                                ], [
                                    'is_correct' => ($choiceText === $questionData['correct_answer'] ?? ''),
                                ]);
                            }
                        }
                    } else {
                        // Simple rating question (backward compatible)
                        Question::firstOrCreate([
                            'question' => $questionData,
                            'subcategory_id' => $subCategory->id,
                        ], [
                            'question_type' => 'rating',
                            'is_active' => true
                        ]);
                    }
                }
            }
        }
    }
}
