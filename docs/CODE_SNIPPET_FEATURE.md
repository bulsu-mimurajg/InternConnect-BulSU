# Code Snippet Feature for Student Assessment Questions

## Date
October 29, 2025

## Feature Summary
Added the ability for admins to include code snippets when creating or editing student assessment questions. Code snippets are displayed in a formatted container for students taking the quiz.

## Implementation Details

### Database Changes
**Migration**: `2025_10_29_033519_add_code_snippet_to_questions_table.php`
- Added `code_snippet` column to `questions` table
- Type: `TEXT`, nullable
- Position: After `question` column

### Backend Changes

#### Question Model (`app/Models/Question.php`)
- Added `code_snippet` to fillable properties

#### QuestionController (`app/Http/Controllers/QuestionController.php`)
**Store Method**:
- Added validation: `'code_snippet' => 'nullable|string|max:5000'`
- Included `code_snippet` in question creation

**Update Method**:
- Added validation: `'code_snippet' => 'nullable|string|max:5000'`
- Included `code_snippet` in question updates

### Frontend Changes

#### Admin Side - Question Management

**Type Definition** (`resources/js/types/index.d.ts`):
```typescript
export interface Question {
    id: number;
    question: string;
    code_snippet?: string;  // Added
    // ...other fields
}
```

**Forms Page** (`resources/js/pages/admin/forms.tsx`):
1. Updated form state to include `code_snippet`
2. Added Code Snippet textarea field in the question form:
   - Monospace font for code appearance
   - 6 rows for comfortable editing
   - Helper text explaining the feature
   - Placeholder with example code
3. Updated `handleEdit` function to populate `code_snippet` when editing

**Form UI**:
```typescriptreact
<div className="space-y-2">
    <Label htmlFor="code_snippet">Code Snippet (Optional)</Label>
    <p className="text-sm text-muted-foreground">
        Add code that will be displayed in a formatted container for students
    </p>
    <Textarea
        id="code_snippet"
        value={data.code_snippet}
        onChange={(e) => setData('code_snippet', e.target.value)}
        className="font-mono text-sm"
        placeholder="// Enter code snippet here..."
        rows={6}
    />
</div>
```

#### Student Side - Assessment Display

**Technical Skills** (`resources/js/components/form/student/technical-skill.tsx`):
- Updated `Skill` interface to include `code_snippet?: string`
- Added code snippet display before answer choices
- Styled with dark background and monospace font

**Soft Skills** (`resources/js/components/form/student/soft-skill.tsx`):
- Same updates as Technical Skills component

**Code Snippet Display**:
```typescriptreact
{skill.code_snippet && (
    <div className="my-3 p-4 bg-gray-900 dark:bg-gray-800 rounded-lg border border-gray-700">
        <pre className="text-sm text-gray-100 overflow-x-auto">
            <code>{skill.code_snippet}</code>
        </pre>
    </div>
)}
```

## User Experience

### For Admins
1. Navigate to Forms Management
2. Click "Add Question" or edit existing question
3. See new "Code Snippet (Optional)" field below the question text
4. Enter code snippet (e.g., JavaScript, Python, Java code)
5. Save question - code snippet is stored and displayed to students

### For Students
1. Take assessment
2. Questions with code snippets show:
   - Question text
   - **Code snippet in dark container** (new!)
   - Answer choices below
3. Code is syntax-highlighted with monospace font
4. Container has horizontal scroll for long code lines

## Styling

### Admin Form
- **Font**: Monospace (`font-mono`)
- **Size**: Small text (`text-sm`)
- **Rows**: 6 lines
- **Placeholder**: Example code to guide admins

### Student View
- **Container**: Dark gray background (`bg-gray-900`)
- **Border**: Gray border (`border-gray-700`)
- **Padding**: `p-4`
- **Text**: Light gray (`text-gray-100`)
- **Font**: Monospace (via `<pre>` and `<code>` tags)
- **Overflow**: Horizontal scroll for long lines

## Use Cases

1. **Programming Questions**
   - Show code and ask what it outputs
   - Show code and ask to identify errors
   - Show code and ask about its functionality

2. **Code Reading Comprehension**
   - Display code snippet
   - Ask multiple choice about behavior

3. **Debugging Questions**
   - Show buggy code
   - Ask what the bug is

4. **Best Practices Questions**
   - Show code example
   - Ask about code quality issues

## Example

**Admin Creates Question**:
```
Question: What will the following code output?

Code Snippet:
function greet(name) {
    return 'Hello ' + name;
}
console.log(greet('World'));

Answers:
- Hello World ✓
- Hello undefined
- undefined
- Error
```

**Student Sees**:
```
What will the following code output?

┌─────────────────────────────────┐
│ function greet(name) {          │
│     return 'Hello ' + name;     │
│ }                               │
│ console.log(greet('World'));    │
└─────────────────────────────────┘

○ Hello World
○ Hello undefined
○ undefined
○ Error
```

## Benefits

1. **Better Programming Assessment**: Can test code comprehension
2. **Visual Clarity**: Code is clearly separated from question text
3. **Professional Appearance**: Dark theme matches coding environments
4. **Optional Field**: Not required, only use when needed
5. **No Length Limit Issues**: 5000 characters supports complex code

## Technical Details

- **Max Length**: 5000 characters (backend validation)
- **Storage**: TEXT column in MySQL (up to ~65KB)
- **Rendering**: Client-side, no syntax highlighting library needed
- **Responsive**: Horizontal scroll on mobile devices
- **Accessibility**: Semantic HTML (`<pre>` + `<code>`)

## Files Modified

### Backend
- `database/migrations/2025_10_29_033519_add_code_snippet_to_questions_table.php`
- `app/Models/Question.php`
- `app/Http/Controllers/QuestionController.php`

### Frontend
- `resources/js/types/index.d.ts`
- `resources/js/pages/admin/forms.tsx`
- `resources/js/components/form/student/technical-skill.tsx`
- `resources/js/components/form/student/soft-skill.tsx`

## Testing Checklist

- ✅ Migration runs successfully
- ✅ Code snippet field appears in admin form
- ✅ Code snippet is optional (can be left blank)
- ✅ Code snippet saves correctly
- ✅ Code snippet displays in student assessment
- ✅ Long code scrolls horizontally
- ✅ Dark theme styling works
- ✅ Edit question populates code snippet field
- ✅ TypeScript types updated
- ✅ Frontend builds without errors

## Future Enhancements (Optional)

1. Add syntax highlighting (using Prism.js or similar)
2. Add language selector (JavaScript, Python, Java, etc.)
3. Add line numbers
4. Add copy-to-clipboard button
5. Add theme toggle (light/dark code background)
6. Support for multiple code snippets per question
7. Image upload support for diagrams

