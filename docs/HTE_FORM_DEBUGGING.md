# HTE Form Submission Debugging Guide

## Issue: Form submission not working when clicking submit

### Debugging Steps:

#### 1. Check Browser Console
- Open browser developer tools (F12)
- Go to Console tab
- Try submitting the form and look for any JavaScript errors
- Check for any network errors in the Network tab

#### 2. Check Laravel Logs
- Check `storage/logs/laravel.log` for any PHP errors
- Look for the debug logs we added to the HTEController

#### 3. Verify Form Data
- The form should collect:
  - Basic Information: companyName, contactPerson, email, phone, address
  - Internship Details: position, department, numberOfInterns, duration, startDate, endDate
  - Weights: subcategoryWeights (optional)

#### 4. Test Routes
- Visit `/hte/test` to verify the route is accessible
- Check if you're logged in with HTE role

#### 5. Common Issues and Solutions:

**Issue 1: Form validation failing**
- The form might not be collecting all required fields
- Check if all form steps are completed
- Verify that weights are being set in the criteria step

**Issue 2: CSRF token missing**
- Laravel requires CSRF tokens for POST requests
- Check if the form includes the CSRF token

**Issue 3: JavaScript errors**
- Check browser console for any JavaScript errors
- Verify that all required JavaScript files are loading

**Issue 4: Route/middleware issues**
- Ensure you're logged in with the correct role
- Check if the route is accessible

### Temporary Debugging Steps:

1. **Add console.log to form submission:**
   ```javascript
   function onSubmit(values: FormData) {
       console.log('Form submitted with values:', values);
       // ... rest of the function
   }
   ```

2. **Check form validation:**
   - Try submitting with minimal data first
   - Check if the form validation is preventing submission

3. **Test with Postman/curl:**
   ```bash
   curl -X POST http://localhost:8000/hte/submit \
     -H "Content-Type: application/json" \
     -H "X-CSRF-TOKEN: [your-csrf-token]" \
     -d '{
       "companyName": "Test Company",
       "contactPerson": "Test Person",
       "email": "test@example.com",
       "phone": "1234567890",
       "address": "Test Address",
       "position": "Test Position",
       "department": "Test Department",
       "numberOfInterns": "1",
       "duration": "3 months",
       "startDate": "2024-01-01",
       "endDate": "2024-04-01",
               "subcategoryWeights": {}
     }'
   ```

### Expected Behavior:

1. **Form fills out correctly** - All steps should work
2. **Submit button is enabled** - Should be clickable on final step
3. **Form submits** - Should show "Submitting..." then success message
4. **Data saved to database** - Check database tables for new records
5. **Success message shown** - Should redirect to success state

### Database Tables to Check:

- `hte` - Should have new HTE record
- `internships` - Should have new internship record
- `subcategory_weights` - Should have weight records (if weights were set)

### Next Steps:

1. Try the debugging steps above
2. Check browser console for errors
3. Check Laravel logs for errors
4. Test with minimal form data
5. Verify all form steps are completed before submission
