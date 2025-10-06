# Test Report System Documentation

## Overview

The Test Report System provides comprehensive testing capabilities for all report types in the BULSU InternConnect system. It includes role-based access control, test data generation, and multiple output formats (PDF, Excel, JSON preview).

## Features

### ✅ **Complete Report Coverage**
- Tests all 10+ report types defined in ReportService
- Role-based access control (Admin, Adviser, HTE, Student)
- Parameter-based test data generation
- Multiple output formats (PDF, Excel, JSON)

### ✅ **Role-Based Access**
- **Admin**: Access to all report types
- **Adviser**: Access to student and placement reports
- **HTE**: Access to HTE-specific reports
- **Student**: Limited read-only access

### ✅ **Test Data Generation**
- Realistic test data for each report type
- Configurable parameters (sections, HTEs, internships)
- Fallback generic data for unsupported reports

## Report Types Available

| Report Type | Description | Roles | Requirements |
|-------------|-------------|-------|--------------|
| **comprehensive** | Complete system overview | Admin | None |
| **hte-performance** | HTE performance analytics | Admin | None |
| **performance-analysis** | Student performance analysis | Admin, Adviser | Section |
| **hte-profile** | HTE company profile | Admin, HTE | HTE |
| **hte-internship-slots** | Internship slot utilization | Admin, HTE | Internship |
| **hte-placed-students** | Students placed in HTE | Admin, HTE | Internship |
| **adviser-performance** | Adviser performance metrics | Admin | None |
| **student-list** | Complete student listing | Admin, Adviser | Section |
| **placed-students** | Successfully placed students | Admin, Adviser, HTE | Section |
| **endorsed-students** | Endorsed for placement | Admin, Adviser | Section |
| **student-assessment** | Student assessment details | Admin, Adviser | Section |

## Routes Structure

### **General Test Routes** (All Authenticated Users)
```
GET /test-reports                    # Test dashboard
GET /test-reports/pdf/{reportType}   # Generate test PDF
GET /test-reports/excel/{reportType} # Generate test Excel
GET /test-reports/preview/{reportType} # Preview test data (JSON)
```

### **Role-Specific Routes**

#### **Admin Routes**
```
GET /admin/test-reports                    # Admin test dashboard
GET /admin/test-reports/pdf/{reportType}   # Admin test PDF
GET /admin/test-reports/excel/{reportType} # Admin test Excel
GET /admin/test-reports/preview/{reportType} # Admin test preview
```

#### **Adviser Routes**
```
GET /adviser/test-reports                    # Adviser test dashboard
GET /adviser/test-reports/pdf/{reportType}   # Adviser test PDF
GET /adviser/test-reports/excel/{reportType} # Adviser test Excel
GET /adviser/test-reports/preview/{reportType} # Adviser test preview
```

#### **HTE Routes**
```
GET /hte/test-reports                    # HTE test dashboard
GET /hte/test-reports/pdf/{reportType}   # HTE test PDF
GET /hte/test-reports/excel/{reportType} # HTE test Excel
GET /hte/test-reports/preview/{reportType} # HTE test preview
```

#### **Student Routes** (Limited Access)
```
GET /student/test-reports                    # Student test dashboard
GET /student/test-reports/preview/{reportType} # Student test preview (read-only)
```

## Usage Examples

### **1. Access Test Dashboard**
```bash
# For admin users
GET /admin/test-reports

# For adviser users  
GET /adviser/test-reports

# For HTE users
GET /hte/test-reports
```

### **2. Generate Test PDF**
```bash
# Basic PDF generation
GET /test-reports/pdf/comprehensive

# With parameters
GET /test-reports/pdf/student-list?section_id=1

# With multiple parameters
GET /test-reports/pdf/hte-placed-students?hte_id=1&internship_id=2
```

### **3. Generate Test Excel**
```bash
# Basic Excel generation
GET /test-reports/excel/hte-performance

# With parameters
GET /test-reports/excel/placed-students?section_id=1
```

### **4. Preview Test Data**
```bash
# JSON preview
GET /test-reports/preview/student-assessment?section_id=1
```

## Test Data Generation

### **Comprehensive Report**
```json
{
  "summary": {
    "total_students": 150,
    "total_htes": 25,
    "total_internships": 45,
    "placed_students": 120,
    "placement_rate": 80.0
  },
  "sections": [...],
  "htes": [...],
  "generated_at": "2024-01-15T10:30:00Z"
}
```

### **Student List Report**
```json
{
  "students": [
    {
      "id": 1,
      "student_number": "2024-001",
      "first_name": "Student",
      "last_name": "Name1",
      "section": "CS-3A",
      "specialization": "Web Development",
      "is_active": true
    }
  ],
  "section": "CS-3A",
  "total_count": 30
}
```

### **HTE Performance Report**
```json
{
  "htes": [
    {
      "id": 1,
      "company_name": "TechCorp Inc.",
      "total_internships": 5,
      "total_slots": 15,
      "filled_slots": 14,
      "utilization_rate": 93.3,
      "avg_compatibility": 85.2,
      "response_time": "2.5 days"
    }
  ],
  "summary": {
    "total_htes": 25,
    "avg_utilization": 78.5,
    "avg_response_time": "2.8 days"
  }
}
```

## Test Templates

### **Available Templates**
- `reports/test/comprehensive.blade.php` - Comprehensive report layout
- `reports/test/student-list.blade.php` - Student list layout
- `reports/test/hte-performance.blade.php` - HTE performance layout
- `reports/test/generic.blade.php` - Fallback generic layout

### **Template Features**
- Professional BULSU branding
- Test data indicators
- Responsive design
- Consistent styling
- Timestamp information

## Security & Access Control

### **Middleware Stack**
```php
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    // Admin-only routes
});
```

### **Permission Checks**
```php
// Check if user can access specific report
if (!$this->reportService->canAccessReport($userRole, $reportType)) {
    abort(403, 'You do not have permission to access this report.');
}
```

### **Role Permissions Matrix**
| Role | Comprehensive | HTE Performance | Student List | HTE Profile | Adviser Performance |
|------|---------------|-----------------|--------------|-------------|-------------------|
| **Admin** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Adviser** | ❌ | ❌ | ✅ | ❌ | ❌ |
| **HTE** | ❌ | ❌ | ❌ | ✅ | ❌ |
| **Student** | ❌ | ❌ | ❌ | ❌ | ❌ |

## Error Handling

### **Common Error Scenarios**
1. **Invalid Report Type**: Returns 404 with descriptive message
2. **Permission Denied**: Returns 403 with role-based message
3. **Template Missing**: Falls back to generic template
4. **Data Generation Failure**: Logs error and returns 500

### **Error Response Format**
```json
{
  "success": false,
  "error": "Failed to generate test data: [specific error message]",
  "reportType": "comprehensive",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

## Development & Testing

### **Adding New Report Types**
1. Add configuration to `ReportService::$reportConfigs`
2. Create test data generation method in `TestReportController`
3. Add template if needed (falls back to generic)
4. Update role permissions if required

### **Testing Workflow**
1. Access appropriate role-based test dashboard
2. Select test parameters (if required)
3. Generate PDF/Excel for verification
4. Use JSON preview for data structure validation
5. Verify role-based access restrictions

### **File Naming Convention**
- PDF: `test_{reportType}_{timestamp}.pdf`
- Excel: `test_{reportType}_{timestamp}.xlsx`
- Example: `test_comprehensive_2024-01-15_10-30-45.pdf`

## Integration with Main Report System

### **Shared Components**
- Uses same `ReportService` for configuration
- Same role-based permission system
- Consistent data structure patterns
- Compatible with existing report templates

### **Benefits**
- **Development**: Test reports without real data
- **Demo**: Show report capabilities to stakeholders
- **QA**: Verify report generation logic
- **Training**: Safe environment for user training

## Best Practices

### **For Developers**
1. Always test with role-based access
2. Verify parameter requirements
3. Check template fallbacks
4. Validate data structure consistency

### **For Users**
1. Use appropriate role-based dashboard
2. Select relevant test parameters
3. Verify output format and content
4. Report any template or data issues

### **For QA Testing**
1. Test all report types per role
2. Verify parameter validation
3. Check error handling scenarios
4. Validate output file formats

## Troubleshooting

### **Common Issues**

#### **"Report type not found"**
- Check if report type exists in `ReportService::$reportConfigs`
- Verify spelling and case sensitivity

#### **"Permission denied"**
- Verify user role and report permissions
- Check middleware configuration

#### **"Template not found"**
- System falls back to generic template
- Create specific template if needed

#### **"Failed to generate test data"**
- Check server logs for specific error
- Verify data generation method exists
- Check parameter validation

### **Debug Mode**
Enable detailed logging by setting `LOG_LEVEL=debug` in `.env` file.

## Future Enhancements

### **Planned Features**
- [ ] Bulk report generation
- [ ] Scheduled test report generation
- [ ] Custom test data sets
- [ ] Report comparison tools
- [ ] Performance metrics
- [ ] Automated testing integration

### **Integration Opportunities**
- [ ] CI/CD pipeline integration
- [ ] Automated regression testing
- [ ] Performance benchmarking
- [ ] User acceptance testing tools
