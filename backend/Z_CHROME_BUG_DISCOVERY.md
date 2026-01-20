# Bug Discovery Task - Claude Chrome Extension

You are inspecting a Laravel CRUD application served at `http://127.0.0.1:8000/`. Your task is to systematically test all pages and interactions, document any bugs you find, and provide detailed reports.

---

## Application Overview

**Mobius** - A Laravel 12 demo app for managing "Person" records with addresses.

**Tech Stack:**
- Laravel 12, PHP 8.4
- Blade templates
- Tailwind CSS v4
- **HTMX** (CDN) with `hx-boost="true"` on body for SPA-like navigation
- Alpine.js (via Vite)
- Native browser `confirm()` dialogs for confirmations

---

## Pages to Test

| Route | Page | Description |
|-------|------|-------------|
| `/` | Home | Landing page |
| `/persons` | List | Grid of person cards |
| `/persons/create` | Create | Form to add new person |
| `/persons/{id}` | Detail | View single person |
| `/persons/{id}/edit` | Edit | Form to edit person |

---

## Known Bug Area (Highest Priority)

### Edit Page (`/persons/{id}/edit`)

There is a **suspected bug** with the change confirmation dialog:

**Expected behavior:**
1. User makes changes to form fields
2. User clicks "Save Changes"
3. Browser shows `confirm()` dialog listing changes
4. If user clicks **Cancel** - form should NOT submit, stay on edit page
5. If user clicks **OK** - form submits, redirects to detail page

**Suspected bug:**
- When user clicks **Cancel** on the confirm dialog, the form STILL submits
- The changes are saved even though user cancelled

**Suspected cause:**
- HTMX's `hx-boost` intercepts form submissions
- The `onsubmit="return false"` may not prevent HTMX from processing the form

**How to test:**
1. Go to `/persons` (create a person first if empty)
2. Click on a person card to view details
3. Click "Edit" button
4. Change any field (e.g., change the name)
5. Click "Save Changes"
6. When the confirm dialog appears, click **Cancel**
7. Check if:
   - You stay on the edit page (expected)
   - OR you get redirected (bug)
   - The data in database changed (bug)

---

## Full Test Checklist

### Navigation Tests
- [ ] Home page loads without errors
- [ ] Can navigate to /persons list
- [ ] HTMX navigation feels smooth (no full page reloads visible)
- [ ] Back buttons work correctly
- [ ] Page transitions don't glitch

### Create Flow (`/persons/create`)
- [ ] Form displays all fields
- [ ] Required field validation works
- [ ] Phone format validation works (Malaysian: 01X-XXX XXXX)
- [ ] State dropdown has all Malaysian states
- [ ] Successful submit redirects to detail page
- [ ] New person appears in list

### Detail Page (`/persons/{id}`)
- [ ] Shows correct person data
- [ ] Shows address information
- [ ] Edit button navigates to edit page
- [ ] Delete button shows confirmation
- [ ] Delete confirmation: Cancel does NOT delete
- [ ] Delete confirmation: OK deletes and redirects to list

### Edit Flow (`/persons/{id}/edit`) - FOCUS HERE
- [ ] Form pre-fills with existing data
- [ ] "Was: {value}" hints show original values
- [ ] Changing a field and submitting works
- [ ] **CRITICAL: Cancel on confirm dialog prevents submission**
- [ ] No changes + submit = redirects back without dialog
- [ ] Validation errors display correctly

### Console Errors
- [ ] No JavaScript errors in console
- [ ] No network errors (4xx, 5xx responses)
- [ ] No HTMX-related warnings

---

## How to Report Bugs

For each bug found, document:

```markdown
### Bug: [Short description]

**Page:** /route/path
**Steps to reproduce:**
1. Step one
2. Step two
3. ...

**Expected:** What should happen
**Actual:** What actually happens

**Console errors (if any):**
```
paste any JS errors
```

**Network tab (if relevant):**
- Request URL:
- Response status:
- Response body:

**Suspected cause:**
Your analysis of why this might be happening

**Screenshot/Evidence:**
[Describe what you see on screen]
```

---

## Special Things to Check

### HTMX + Form Interactions
- Does `hx-boost` respect `onsubmit` return values?
- Are forms being double-submitted?
- Do confirm dialogs interrupt HTMX properly?

### JavaScript Loading
- Does `window.confirmChanges` function exist? (check in console)
- Is it being called when form submits?
- What does it return?

### Event Timing
- Is HTMX capturing the submit event before the onsubmit handler runs?
- Are there race conditions?

---

## Debugging Commands (Run in DevTools Console)

```javascript
// Check if confirmChanges function exists
console.log(typeof window.confirmChanges);

// Test the function manually (copy form reference first)
const form = document.querySelector('form');
const original = { name: 'Test', phone: '012-345 6789' };
const labels = { name: 'Name', phone: 'Phone' };
console.log(window.confirmChanges(form, original, labels, '/persons/1'));

// Check HTMX config
console.log(htmx.config);

// Monitor form submissions
document.querySelector('form')?.addEventListener('submit', e => {
    console.log('Submit event fired', e.defaultPrevented);
});
```

---

## Output Format

After testing, provide a report with:

1. **Summary** - Overview of what works and what doesn't
2. **Bug List** - All bugs found with full details
3. **Console Log** - Any errors or warnings from DevTools
4. **Recommendations** - Suggested fixes or areas for investigation

---

## Notes from Previous Session

From the handoff document:
> "The edit page confirm dialog may have issues - needs testing"
> "When you press submit, and then you cancel on the chrome dialog confirm box, it still passes the changes"

This strongly suggests the HTMX + onsubmit conflict hypothesis is correct. Your testing will confirm this.

---

Good luck! Report back with your findings.
