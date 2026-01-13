# AGCO Brands - Quick Start Guide

## 🚀 Quick Setup (3 Steps)

### Step 1: Run Database Migration
```bash
# Open Command Prompt or Terminal
cd d:\xampp\htdocs\AGCO
mysql -u root agcoweb < create_agco_brands_table.sql
```

Or import via phpMyAdmin:
1. Open phpMyAdmin → Select `agcoweb` database
2. Click "Import" → Choose `create_agco_brands_table.sql`
3. Click "Go"

### Step 2: Verify Installation
✅ Directory created: `assets/images/brands/` (Done!)
✅ Migration file: `create_agco_brands_table.sql`
✅ Model: `core/app/Models/AgcoBrand.php`
✅ Controller: `core/app/Http/Controllers/Admin/ManageAgcoBrandController.php`
✅ Admin View: `core/resources/views/admin/agco_brand/index.blade.php`
✅ Routes: Added to `core/routes/admin.php`
✅ Sidebar: Updated in `core/resources/views/admin/partials/sidenav.blade.php`
✅ Dashboard: Updated in `core/resources/views/templates/basic/user/dashboard_mobile.blade.php`

### Step 3: Access Admin Panel
1. Login to admin panel
2. Look for "AGCO Brands" in sidebar (after "Video Tutorials")
3. Click to manage brands

---

## 📋 What Was Created?

### 1. Database Table: `agco_brands`
Columns:
- `id` - Auto increment ID
- `name` - Brand name
- `image` - Logo filename
- `url` - Brand website (optional)
- `order` - Display order
- `status` - Active/Inactive
- `created_at`, `updated_at`

### 2. Admin Features
✅ Full CRUD functionality
✅ Image upload with validation
✅ Status toggle (Active/Inactive)
✅ Display order management
✅ Search and pagination

### 3. User Dashboard
✅ Brand section appears after video tutorials
✅ Horizontal scrolling (landscape auto-scroll)
✅ Shows only active brands
✅ Ordered display
✅ Bengali text: "AGCO পরিবারের ব্র্যান্ড"

---

## 🎯 How to Use

### Add Brand:
1. Admin → AGCO Brands → Click "Add New"
2. Fill form:
   - **Name**: Brand name (e.g., "AGCO")
   - **Image**: Upload logo (300×100px recommended)
   - **URL**: Website (optional)
   - **Order**: Display position (0=first, 1=second, etc.)
   - **Status**: Active
3. Click "Submit"

### Edit Brand:
1. Click edit icon (✏️) on any brand
2. Modify details
3. Upload new image (optional)
4. Click "Update"

### Delete Brand:
1. Click delete icon (🗑️)
2. Confirm deletion

### Toggle Status:
- Click status badge to switch Active/Inactive

---

## 📱 User View

After video tutorials section on dashboard:
```
┌─────────────────────────────┐
│  🎬 ভিডিও টিউটোরিয়াল      │
│  [Tutorial 1] [Tutorial 2]  │
└─────────────────────────────┘
         ↓
┌─────────────────────────────┐
│  🏭 AGCO পরিবারের ব্র্যান্ড │
│  [AGCO] [Massey] [Fendt] →  │
│   ← Scroll horizontally      │
└─────────────────────────────┘
```

---

## 🖼️ Image Specifications

- **Size**: 300×100 pixels (recommended)
- **Formats**: JPG, JPEG, PNG
- **Max Size**: 2MB
- **Location**: `assets/images/brands/`
- **Background**: Transparent or white recommended

---

## 🔧 Troubleshooting

### Brands not showing on dashboard?
1. Check brand status = "Active"
2. Verify migration ran successfully
3. Check images uploaded correctly

### Can't upload image?
1. Check file size < 2MB
2. Use JPG, PNG, or JPEG format only
3. Verify `assets/images/brands/` directory exists

### Wrong display order?
- Edit brands and set order: 0, 1, 2, 3... (ascending)

---

## 📁 Files Modified/Created

### Created:
1. `create_agco_brands_table.sql`
2. `core/app/Models/AgcoBrand.php`
3. `core/app/Http/Controllers/Admin/ManageAgcoBrandController.php`
4. `core/resources/views/admin/agco_brand/index.blade.php`
5. `assets/images/brands/` (directory)
6. `AGCO_BRANDS_IMPLEMENTATION.md` (documentation)
7. `AGCO_BRANDS_QUICK_START.md` (this file)

### Modified:
1. `core/routes/admin.php` - Added brand routes
2. `core/app/Traits/FileInfo.php` - Added brand file path
3. `core/resources/views/admin/partials/sidenav.blade.php` - Added menu item
4. `core/resources/views/templates/basic/user/dashboard_mobile.blade.php` - Added brand section

---

## ✅ Next Steps

1. [x] Run database migration
2. [x] Create brands directory
3. [ ] Access admin panel
4. [ ] Add brand logos
5. [ ] View on user dashboard

---

## 📞 Need Help?

Refer to full documentation: `AGCO_BRANDS_IMPLEMENTATION.md`

---

**Status**: ✅ Ready to Use  
**Date**: January 2025
