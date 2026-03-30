# Debug Session Prompt

**Copy everything below the line and paste as your first message in a new CC session.**

---

Load skills: `/frontend-design` and `/using-superpowers`

Read `backend/Z_MENTOR_BRIEFING_FOR_NEW_CC.md` before doing anything — it has critical safety rules (no git commands, no migrate:fresh, commit reminders, etc).

I have 3 tasks. **Do them one at a time. After each task, tell me what changed and suggest a commit message. Wait for me to confirm the commit before starting the next task.**

---

## Task 1: Fix dropdown component sending label instead of key for numeric keys

**The bug:** `resources/views/components/dropdown/select.blade.php` line 22:

```php
$actualValue = is_numeric($optValue) ? $optLabel : $optValue;
```

When options are passed as `[1 => 'Starbucks', 2 => 'Chagee']` (numeric keys), the component uses the **label** as the form value instead of the **key**. This breaks the bins index outlet filter — it sends `outlet=Starbucks+...` in the URL instead of `outlet=1`, so the controller's `where('outlet_id', ...)` query finds nothing.

**The fix:** Change line 22 to always use `$optValue` as the value:

```php
$actualValue = $optValue;
```

Then also fix the JS `selectOption` function lower in the file — it has the same `is_numeric` logic where it decides what value to set on the hidden input. Find it and make it always use the key, not the label.

**Also fix the JS rendering of option items** — the `data-value` or equivalent attribute on each option `<li>` must use the key, not the label.

Read the full component file, understand how it works, then fix all instances of this pattern. Test by visiting `http://127.0.0.1:8000/admin/bins` and filtering by outlet — the URL should show `outlet=1` (a number), not `outlet=Starbucks...`.

Run `vendor/bin/pint --dirty` when done.

---

## Task 2: Make the Outlets filter dropdown wider on the bins index page

**File:** `resources/views/admin/bins/index.blade.php`

Find the outlet dropdown wrapper `<div>` (it probably has `min-w-[160px]` or similar). Increase the minimum width so that long outlet names like "Starbucks - All Seasons Place" don't get ellipsized. Try `min-w-[260px]` or `min-w-[280px]`.

Also check that the dropdown's **popup/options list** is wide enough — look at the component's CSS for the options container and make sure it has `min-w-full` or `w-max` so options aren't truncated.

---

## Task 3: Replace native assignment dropdown on bin show page with searchable custom dropdown

**Route:** `http://127.0.0.1:8000/admin/bins/79`

**File:** `resources/views/admin/bins/show.blade.php`

Find the outlet assignment section — it currently uses a native `<select>` dropdown for choosing which outlet to assign the bin to. Replace it with the custom `<x-dropdown.select>` component:

```blade
<x-dropdown.select
    name="outlet_id"
    placeholder="Select outlet"
    :options="$outlets->pluck('name', 'id')->toArray()"
    :selected="$bin->currentAssignment?->outlet_id"
    :searchable="true"
    searchPlaceholder="Search outlets..."
/>
```

Make sure the wrapper div is wide enough (at least `min-w-[280px]` or `w-full`) so outlet names aren't truncated.

Read the file first to understand the current assignment form structure before making changes.

Run `vendor/bin/pint --dirty` when done.
