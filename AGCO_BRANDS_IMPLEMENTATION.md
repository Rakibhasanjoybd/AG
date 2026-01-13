# AGCO Family Brands CRUD Implementation Guide

## Overview
Complete CRUD system for managing AGCO family brand logos displayed on the user dashboard after the video tutorials section.

## Implementation Status: ✅ COMPLETE

---

## 1. Database Migration

### File: `create_agco_brands_table.sql`
- **Location**: `d:\xampp\htdocs\AGCO\create_agco_brands_table.sql`
- **Status**: ✅ Created
- **Description**: SQL migration to create the `agco_brands` table
- **Table Structure**:
  - `id` - Primary key
  - `name` - Brand name
  - `image` - Image filename
  - `url` - Optional brand website URL
  - `order` - Display order position
  - `status` - Active/Inactive (1/0)
  - `created_at`, `updated_at` - Timestamps

### Sample Data Included:
1. AGCO
2. Massey Ferguson
3. Fendt
4. Valtra
5. Challenger
6. GSI

### To Run Migration:
```bash
# Option 1: Using MySQL command line
mysql -u root agcoweb < create_agco_brands_table.sql

# Option 2: Import via phpMyAdmin
# 1. Open phpMyAdmin
# 2. Select 'agcoweb' database
# 3. Go to Import tab
# 4. Choose file and execute
```

---

## 2. Eloquent Model

### File: `AgcoBrand.php`
- **Location**: `d:\xampp\htdocs\AGCO\core\app\Models\AgcoBrand.php`
- **Status**: ✅ Created
- **Features**:
  - Mass assignment protection
  - Scopes for active brands
  - Ordered scope for display sequence
  - Automatic timestamp management

### Model Code:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgcoBrand extends Model
{
    protected $table = 'agco_brands';

    protected $fillable = [
        'name',
        'image',
        'url',
        'order',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}
```

---

## 3. Admin Controller

### File: `ManageAgcoBrandController.php`
- **Location**: `d:\xampp\htdocs\AGCO\core\app\Http\Controllers\Admin\ManageAgcoBrandController.php`
- **Status**: ✅ Created
- **Methods**:
  - `index()` - Display all brands with pagination
  - `store()` - Create new brand with image upload
  - `update()` - Update existing brand
  - `delete()` - Delete brand and remove image
  - `status()` - Toggle brand active/inactive status

### Features:
- Image upload handling
- File validation (jpeg, png, jpg)
- Automatic old image removal on update/delete
- Status notifications
- Error handling

---

## 4. Admin Views

### File: `index.blade.php`
- **Location**: `d:\xampp\htdocs\AGCO\core\resources\views\admin\agco_brand\index.blade.php`
- **Status**: ✅ Created
- **Features**:
  - List view with data table
  - Search functionality
  - Add/Edit modals
  - Image preview
  - Status toggle buttons
  - Delete confirmation
  - Responsive design

### UI Components:
- Data table with sorting
- Modal forms for add/edit
- Image upload with preview
- Status badges (Active/Inactive)
- Action buttons (Edit, Delete, Status)

---

## 5. Routes

### File: `admin.php`
- **Location**: `d:\xampp\htdocs\AGCO\core\routes\admin.php`
- **Status**: ✅ Updated
- **Routes Added**:

```php
// AGCO Brand Management
Route::controller('ManageAgcoBrandController')->name('agco-brand.')->prefix('agco-brand')->group(function(){
    Route::get('/', 'index')->name('index');
    Route::post('/store', 'store')->name('store');
    Route::post('/update/{id}', 'update')->name('update');
    Route::post('/delete/{id}', 'delete')->name('delete');
    Route::post('/status/{id}', 'status')->name('status');
});
```

### Available Routes:
- `admin.agco-brand.index` - List all brands
- `admin.agco-brand.store` - Create new brand
- `admin.agco-brand.update` - Update existing brand
- `admin.agco-brand.delete` - Delete brand
- `admin.agco-brand.status` - Toggle status

---

## 6. Admin Sidebar Navigation

### File: `sidenav.blade.php`
- **Location**: `d:\xampp\htdocs\AGCO\core\resources\views\admin\partials\sidenav.blade.php`
- **Status**: ✅ Updated
- **Menu Item Added**: "AGCO Brands" (after Video Tutorials)

### Navigation Structure:
```html
<li class="sidebar-menu-item {{ menuActive('admin.agco-brand*') }}">
    <a href="{{ route('admin.agco-brand.index') }}" class="nav-link">
        <i class="menu-icon las la-industry"></i>
        <span class="menu-title">@lang('AGCO Brands')</span>
    </a>
</li>
```

---

## 7. File Upload Configuration

### File: `FileInfo.php`
- **Location**: `d:\xampp\htdocs\AGCO\core\app\Traits\FileInfo.php`
- **Status**: ✅ Updated
- **Configuration Added**:

```php
$data['brand'] = [
    'path' => 'assets/images/brands',
    'size' => '300x100',
];
```

### Image Specifications:
- **Path**: `assets/images/brands/`
- **Recommended Size**: 300×100 pixels
- **Formats**: JPEG, PNG, JPG
- **Max Size**: 2MB

---

## 8. User Dashboard Integration

### File: `dashboard_mobile.blade.php`
- **Location**: `d:\xampp\htdocs\AGCO\core\resources\views\templates\basic\user\dashboard_mobile.blade.php`
- **Status**: ✅ Updated

### Changes Made:

#### 1. Added Brands Query (Line ~21)
```php
$brands = \App\Models\AgcoBrand::active()->ordered()->get();
```

#### 2. Added Brands Section HTML (After tutorials section)
```html
<!-- AGCO Family Brands -->
@if($brands->count() > 0)
<div class="brands-section">
    <div class="section-head-new">
        <div class="sh-left">
            <span class="sh-emoji">🏭</span>
            <h3>AGCO পরিবারের ব্র্যান্ড</h3>
        </div>
    </div>
    <div class="brands-scroll">
        @foreach($brands as $brand)
        <div class="brand-card-new">
            <img src="{{ getImage(getFilePath('brand').'/'.$brand->image, '300x100') }}" 
                 alt="{{ $brand->name }}" 
                 loading="lazy">
        </div>
        @endforeach
    </div>
</div>
@endif
```

#### 3. Added Brands CSS Styles
```css
/* AGCO Family Brands Section */
.brands-section {
    margin-bottom: 16px;
    background: #fff;
    padding: 14px 0;
}
.brands-scroll {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    padding: 0 16px 12px;
    -webkit-overflow-scrolling: touch;
}
.brand-card-new {
    flex-shrink: 0;
    width: 140px;
    height: 80px;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e5e7eb;
}
```

### Features:
- Auto landscape scrolling (horizontal scroll)
- Grid view layout
- Lazy loading for performance
- Responsive design
- Bengalish text support: "AGCO পরিবারের ব্র্যান্ড"
- Display order support
- Only shows active brands

---

## 9. Directory Structure

### Required Directories:
```
assets/images/brands/     ← Brand logo images storage
```

### Create Directory:
```bash
# Create the brands directory
mkdir -p assets/images/brands
chmod 755 assets/images/brands
```

---

## 10. Usage Instructions

### For Admin Panel:

1. **Access Admin Panel**:
   - Navigate to: `/admin/agco-brand`
   - Or click "AGCO Brands" in sidebar

2. **Add New Brand**:
   - Click "Add New" button
   - Fill in brand details:
     - Name (required)
     - Upload logo image (required)
     - Website URL (optional)
     - Display order (default: 0)
     - Status (Active/Inactive)
   - Click "Submit"

3. **Edit Brand**:
   - Click edit icon on brand row
   - Modify details in modal
   - Upload new image (optional)
   - Click "Update"

4. **Delete Brand**:
   - Click delete icon on brand row
   - Confirm deletion

5. **Toggle Status**:
   - Click status badge to toggle Active/Inactive

### For Users:

1. **View Brands on Dashboard**:
   - Login to user account
   - Scroll down to video tutorials section
   - Brand logos appear immediately after tutorials
   - Scroll horizontally to view all brands

---

## 11. File Checklist

✅ **Database**:
- [x] `create_agco_brands_table.sql` - Migration file

✅ **Backend**:
- [x] `core/app/Models/AgcoBrand.php` - Model
- [x] `core/app/Http/Controllers/Admin/ManageAgcoBrandController.php` - Controller
- [x] `core/app/Traits/FileInfo.php` - File path configuration

✅ **Routes**:
- [x] `core/routes/admin.php` - Admin routes

✅ **Views**:
- [x] `core/resources/views/admin/agco_brand/index.blade.php` - Admin CRUD view
- [x] `core/resources/views/admin/partials/sidenav.blade.php` - Sidebar menu
- [x] `core/resources/views/templates/basic/user/dashboard_mobile.blade.php` - User dashboard

✅ **Assets**:
- [ ] `assets/images/brands/` - Directory (needs to be created)

---

## 12. Testing Checklist

### Admin Panel Tests:
- [ ] Navigate to admin brands page
- [ ] Create new brand with image
- [ ] Edit existing brand
- [ ] Update brand image
- [ ] Toggle brand status
- [ ] Delete brand
- [ ] Verify image upload works
- [ ] Check display order sorting

### User Dashboard Tests:
- [ ] View brands section on dashboard
- [ ] Verify horizontal scrolling works
- [ ] Check brand images display correctly
- [ ] Test on mobile devices
- [ ] Verify only active brands show
- [ ] Check brands are in correct order

### Database Tests:
- [ ] Run migration successfully
- [ ] Verify sample data inserted
- [ ] Check all columns created correctly
- [ ] Test foreign key constraints

---

## 13. Next Steps

1. **Run Database Migration**:
   ```bash
   mysql -u root agcoweb < create_agco_brands_table.sql
   ```

2. **Create Image Directory**:
   ```bash
   mkdir -p assets/images/brands
   chmod 755 assets/images/brands
   ```

3. **Upload Brand Logos**:
   - Access admin panel
   - Go to AGCO Brands section
   - Upload real brand logos (300×100 recommended)

4. **Test User Dashboard**:
   - Login as user
   - View dashboard
   - Verify brands appear after tutorials

5. **Customize Content**:
   - Adjust brand section title if needed
   - Modify display order
   - Update brand URLs

---

## 14. Maintenance

### Adding New Brands:
1. Go to admin panel → AGCO Brands
2. Click "Add New"
3. Fill details and upload logo
4. Set appropriate display order

### Removing Brands:
1. Go to admin panel → AGCO Brands
2. Toggle status to "Inactive" (temporary)
   OR
3. Click delete icon (permanent)

### Reordering Brands:
1. Edit each brand
2. Set `order` field value
3. Lower numbers appear first (0, 1, 2, 3...)

---

## 15. Troubleshooting

### Issue: Brands not showing on dashboard
**Solutions**:
- Check brand status is "Active"
- Verify migration ran successfully
- Check brand images uploaded correctly
- Confirm file path in FileInfo.php

### Issue: Images not displaying
**Solutions**:
- Verify `assets/images/brands/` directory exists
- Check file permissions (755 for directory)
- Confirm image files uploaded successfully
- Check image paths in database

### Issue: Upload fails
**Solutions**:
- Check max file size (2MB limit)
- Verify file format (jpeg, png, jpg only)
- Check directory write permissions
- Confirm GD library installed for image processing

---

## 16. Technical Notes

### Performance Considerations:
- Images are lazy loaded on dashboard
- Only active brands queried from database
- Ordered query uses indexed column
- Horizontal scroll optimized for mobile

### Security Features:
- Mass assignment protection
- File upload validation
- CSRF protection on forms
- Authorization middleware on routes

### Accessibility:
- Alt text for all brand images
- Keyboard navigation support
- Screen reader friendly
- Touch-friendly controls

---

## 17. Support

For issues or questions:
1. Check troubleshooting section
2. Review implementation checklist
3. Verify all files created correctly
4. Test with sample data first

---

**Implementation Date**: January 2025  
**Status**: Complete and Ready for Testing  
**Version**: 1.0

---
