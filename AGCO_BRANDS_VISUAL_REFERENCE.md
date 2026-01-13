# AGCO Brands - Visual Layout Reference

## 📱 User Dashboard Layout (Mobile View)

```
┌─────────────────────────────────────────────┐
│  📱 AGCO Mobile Dashboard                   │
├─────────────────────────────────────────────┤
│                                             │
│  [Header Slide Banner]                      │
│  ┌──────────────────────────────────────┐  │
│  │      Promotional Banner Image        │  │
│  └──────────────────────────────────────┘  │
│                                             │
│  [User Greeting & Balance]                  │
│  Hi, Username                               │
│  ৳ 1,234.56  [History] [Withdraw] [Deposit]│
│                                             │
│  [Stats Widgets]                            │
│  ┌─────────────┬─────────────┐             │
│  │Total Clicks │ Daily Limit │             │
│  └─────────────┴─────────────┘             │
│                                             │
│  [Daily Spotlights]                         │
│  🎯 দৈনিক স্পটলাইট                        │
│  [Spotlight 1] [Spotlight 2] → scroll       │
│                                             │
│  [Services Grid]                            │
│  [Click & Earn] [My Clicks] [Referrals]    │
│                                             │
├─────────────────────────────────────────────┤
│  🎬 ভিডিও টিউটোরিয়াল           সব দেখুন│
│  ┌─────────┬─────────┬─────────┐           │
│  │Tutorial │Tutorial │Tutorial │→ scroll   │
│  │   #1    │   #2    │   #3    │           │
│  └─────────┴─────────┴─────────┘           │
├─────────────────────────────────────────────┤
│  🏭 AGCO পরিবারের ব্র্যান্ড                │  ← NEW SECTION
│  ┌────────┬────────┬────────┬────────┐     │
│  │ AGCO   │Massey  │ Fendt  │Valtra  │→   │
│  │  Logo  │Ferguson│  Logo  │ Logo   │     │
│  └────────┴────────┴────────┴────────┘     │
│             ← Horizontal Scroll →          │
├─────────────────────────────────────────────┤
│                                             │
│  [Other Dashboard Sections...]              │
│                                             │
└─────────────────────────────────────────────┘
```

---

## 🎨 Brand Section Design Details

### Section Header:
```
┌─────────────────────────────────────┐
│  🏭 AGCO পরিবারের ব্র্যান্ড        │
└─────────────────────────────────────┘
```
- Emoji: 🏭 (factory)
- Text: "AGCO পরিবারের ব্র্যান্ড" (Bengali)
- Translation: "Brands from the AGCO Family"

### Brand Cards:
```
┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐
│         │  │         │  │         │  │         │
│  AGCO   │  │ Massey  │  │  Fendt  │  │ Valtra  │
│  LOGO   │  │Ferguson │  │  LOGO   │  │  LOGO   │
│         │  │  LOGO   │  │         │  │         │
└─────────┘  └─────────┘  └─────────┘  └─────────┘
  140×80px     140×80px     140×80px     140×80px
```

Each Brand Card:
- Width: 140px
- Height: 80px
- Background: Light gray (#f8f9fa)
- Border Radius: 12px
- Padding: 12px
- Border: 1px solid #e5e7eb
- Hover Effect: Slight lift up

### Scrolling Behavior:
- **Type**: Horizontal scroll (landscape)
- **Auto-scroll**: Native touch scrolling
- **Scroll Bar**: Hidden (webkit-scrollbar: display none)
- **Touch**: Optimized for mobile (-webkit-overflow-scrolling: touch)
- **Gap**: 16px between cards

---

## 🎯 Positioning Details

### Before Brand Section:
```html
<!-- Video Tutorials Section -->
<div class="tutorials-section">
    <!-- Tutorial cards here -->
</div>
```

### Brand Section:
```html
<!-- AGCO Family Brands Section -->
<div class="brands-section">
    <div class="section-head-new">
        <span class="sh-emoji">🏭</span>
        <h3>AGCO পরিবারের ব্র্যান্ড</h3>
    </div>
    <div class="brands-scroll">
        <!-- Brand cards here -->
    </div>
</div>
```

### After Brand Section:
```html
<!-- All Services Modal -->
<div class="modal fade" id="allServicesModal">
    <!-- Modal content -->
</div>
```

---

## 📐 CSS Specifications

### Section Container:
```css
.brands-section {
    margin-bottom: 16px;      /* Space below section */
    background: #fff;          /* White background */
    padding: 14px 0;           /* Top/bottom padding */
}
```

### Scroll Container:
```css
.brands-scroll {
    display: flex;             /* Flex layout */
    gap: 16px;                 /* Space between cards */
    overflow-x: auto;          /* Horizontal scroll */
    padding: 0 16px 12px;      /* Side padding */
    -webkit-overflow-scrolling: touch;  /* Smooth iOS scroll */
}
```

### Brand Card:
```css
.brand-card-new {
    flex-shrink: 0;            /* Prevent shrinking */
    width: 140px;              /* Fixed width */
    height: 80px;              /* Fixed height */
    background: #f8f9fa;       /* Light gray */
    border-radius: 12px;       /* Rounded corners */
    padding: 12px;             /* Inner padding */
    display: flex;             /* Flex for centering */
    align-items: center;       /* Vertical center */
    justify-content: center;   /* Horizontal center */
    border: 1px solid #e5e7eb; /* Subtle border */
    transition: all 0.3s;      /* Smooth animations */
}
```

### Hover Effect:
```css
.brand-card-new:hover {
    transform: translateY(-2px);  /* Lift up */
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);  /* Shadow */
}
```

### Image Styling:
```css
.brand-card-new img {
    max-width: 100%;           /* Responsive width */
    max-height: 100%;          /* Responsive height */
    object-fit: contain;       /* Maintain aspect ratio */
    filter: grayscale(0);      /* No grayscale */
    transition: all 0.3s;      /* Smooth transition */
}
```

---

## 📱 Responsive Behavior

### Mobile (< 768px):
- Full width section
- Horizontal scroll
- Touch-optimized
- 16px side padding

### Tablet (768px - 1024px):
- Same layout as mobile
- Larger touch targets

### Desktop (> 1024px):
- Same layout (mobile-first design)
- Mouse scroll support

---

## 🎨 Color Scheme

### AGCO Brand Colors:
- **Primary Green**: `#0F743C`
- **Warning Orange**: `#F99E2B`
- **Text Primary**: `#1f2937`
- **Background**: `#ffffff`
- **Card Background**: `#f8f9fa`
- **Border**: `#e5e7eb`

### Used in Brands Section:
- Background: White (#fff)
- Card Background: Light Gray (#f8f9fa)
- Border: Gray (#e5e7eb)
- Hover Shadow: rgba(0,0,0,0.08)

---

## 📊 Brand Logo Guidelines

### Image Requirements:
- **Recommended Size**: 300×100 pixels
- **Aspect Ratio**: 3:1 (landscape)
- **Format**: PNG (for transparency) or JPG
- **Max File Size**: 2MB
- **Color Mode**: RGB

### Logo Design Tips:
1. Use transparent background (PNG)
2. Include padding in logo design
3. High contrast for visibility
4. Vector-based logos scale better
5. Test on light gray background

### Example Dimensions:
```
┌────────────────────────────┐
│                            │  ← 100px height
│        BRAND LOGO          │
│                            │
└────────────────────────────┘
         ← 300px width →
```

---

## 🔄 Display Order Logic

Brands are displayed in ascending order based on the `order` field:

```
Order 0  →  Order 1  →  Order 2  →  Order 3
┌──────┐   ┌──────┐   ┌──────┐   ┌──────┐
│ AGCO │   │Massey│   │Fendt │   │Valtra│
└──────┘   └──────┘   └──────┘   └──────┘
 First     Second      Third      Fourth
```

### Setting Order:
- Edit brand in admin panel
- Set `order` field value
- Lower numbers appear first
- Default: 0

---

## 🎭 Animation Details

### Card Hover Animation:
```
Normal State  →  Hover State
┌────────┐       ┌────────┐
│  LOGO  │       │  LOGO  │ ← 2px up
└────────┘       └────────┘
No shadow        With shadow
```

- **Duration**: 0.3s
- **Easing**: Default (ease)
- **Transform**: translateY(-2px)
- **Shadow**: 0 4px 12px rgba(0,0,0,0.08)

### Image Transition:
- **Duration**: 0.3s
- **Effect**: Brightness increase on hover
- **Filter**: grayscale(0) brightness(1.1)

---

## 📲 User Interaction Flow

1. **User scrolls down dashboard**
   ↓
2. **Sees video tutorials section**
   ↓
3. **Continues scrolling**
   ↓
4. **Brands section appears**
   ↓
5. **User can swipe/scroll horizontally to view all brands**
   ↓
6. **Each brand logo is displayed in order**

---

## 🔍 Code Location Reference

### PHP Query (Line ~21):
```php
$brands = \App\Models\AgcoBrand::active()->ordered()->get();
```

### HTML Section (After tutorials ~line 330):
```blade
@if($brands->count() > 0)
<div class="brands-section">
    <!-- Brand cards -->
</div>
@endif
```

### CSS Styles (After tutorials CSS ~line 2020):
```css
.brands-section { ... }
.brands-scroll { ... }
.brand-card-new { ... }
```

---

## ✅ Verification Checklist

After implementation, verify:
- [ ] Brands appear after video tutorials
- [ ] Horizontal scrolling works
- [ ] Brand logos display correctly
- [ ] Hover effects work
- [ ] Only active brands show
- [ ] Brands in correct order
- [ ] Responsive on mobile
- [ ] Bengali text displays properly
- [ ] Images lazy load
- [ ] No scrollbar visible

---

## 🎬 Before & After

### BEFORE:
```
[Video Tutorials Section]
↓
[All Services Modal]
```

### AFTER:
```
[Video Tutorials Section]
↓
[AGCO Brands Section] ← NEW!
↓
[All Services Modal]
```

---

**Reference**: `dashboard_mobile.blade.php` (Lines 303-348)  
**CSS**: Lines 1975-2020  
**Status**: ✅ Implemented
