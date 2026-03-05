# Mission: Font Sizes + Responsiveness — All Transfer Pages

> Open Antigravity → Agent Manager → New Agent → paste:
> **"Read GEMINI.md and execute both missions in order."**

---

## Scope — Files to Edit

Find and edit ALL of the following blade files. If a file doesn't exist, skip it silently.

### Shop Manager
- `resources/views/livewire/shop/transfers/transfers-list.blade.php`
- `resources/views/livewire/inventory/transfers/request-transfer.blade.php`
- `resources/views/shop/transfers/index.blade.php`
- `resources/views/shop/transfers/show.blade.php`
- `resources/views/shop/transfers/request.blade.php`
- `resources/views/shop/transfers/receive.blade.php`

### Warehouse Manager
- `resources/views/livewire/warehouse-manager/transfers/transfers-list.blade.php`
- `resources/views/warehouse-manager/transfers/index.blade.php`
- `resources/views/warehouse-manager/transfers/show.blade.php`
- `resources/views/warehouse-manager/transfers/pack.blade.php`
- `resources/views/warehouse-manager/transfers/scan-out.blade.php`

### Shared / Inventory
- `resources/views/livewire/inventory/transfers/request-transfer.blade.php`
- `resources/views/livewire/inventory/transfers/receive-transfer.blade.php`

> **Before editing:** run `find resources/views -path "*transfer*" -name "*.blade.php"` in the
> terminal to get the exact list of all transfer blade files in this project. Edit every one found.

---

## The Change — Font Size Scale

Apply this font size increase consistently across every transfer blade file found.

### Rule: multiply every font-size value by 1.2 (round to nearest whole px)

Use this reference table:

| Current | New    |
|---------|--------|
| 9px     | 11px   |
| 10px    | 12px   |
| 11px    | 13px   |
| 12px    | 14px   |
| 13px    | 16px   |
| 14px    | 17px   |
| 15px    | 18px   |
| 16px    | 19px   |
| 18px    | 22px   |
| 20px    => 24px   |
| 22px    => 26px   |
| 24px    => 29px   |
| 26px    => 31px   |
| 28px    => 34px   |

### What to change

Update font sizes in **all** of these locations within each file:

1. **Inline `style=` attributes** — e.g. `style="font-size:13px"` → `style="font-size:16px"`
2. **`<style>` blocks** — all `font-size:` declarations inside `<style>...</style>` tags
3. **Tailwind font classes** — replace with the next size up:
   - `text-xs` → `text-sm`
   - `text-sm` → `text-base`
   - `text-base` → `text-lg`
   - `text-lg` → `text-xl`
   - `text-xl` → `text-2xl`

### What NOT to change

- Do not change any `wire:` bindings, PHP logic, or route names
- Do not change `border-radius`, `padding`, `margin`, `width`, `height` values
- Do not change `letter-spacing` or `line-height` values
- Do not change font-weight values
- Do not touch any `.php` files

---

## ✅ Mission 1 Execution — Font Sizes

1. **Terminal:** `find resources/views -path "*transfer*" -name "*.blade.php"` — discover all files
2. **For each file found:** read it, apply every font size change from the table above, save
3. **Terminal:** `php artisan view:clear`
4. **Browser:** Screenshot each page at desktop width (1280px) confirming larger text:
   - `http://localhost:8000/shop/transfers`
   - `http://localhost:8000/shop/transfers/request`
   - `http://localhost:8000/warehouse/transfers`
5. **Artifact:** `mission-1-fonts.md` — list every file changed + count of values updated

---

## 📱 Mission 2 — Responsiveness

Apply responsive layout fixes to every transfer blade file discovered in Mission 1.
The goal: all transfer pages must work cleanly on **mobile (360px), tablet (768px), and desktop (1280px)**.

### Breakpoints to use

Since the design system uses inline styles (CSS variables, no Tailwind grid),
add `@media` rules inside each file's `<style>` block.

```
Mobile  : max-width 600px
Tablet  : max-width 900px
Desktop : default (no media query needed)
```

---

### 2A — Transfer List Pages (shop + warehouse)

**Problems to fix:**

#### Pipeline strip (`.tl-pipeline`)
```css
/* Current — 5 columns, breaks on mobile */
.tl-pipeline { grid-template-columns: repeat(5, 1fr); }

/* Fix */
@media(max-width:900px) {
    .tl-pipeline { grid-template-columns: repeat(3, 1fr); }
}
@media(max-width:600px) {
    .tl-pipeline { grid-template-columns: repeat(2, 1fr); gap:0; }
    .tl-pipeline-step { padding:10px 12px; }
    .tl-step-num  { font-size:20px; }
    .tl-step-sub  { display:none; }
}
```

#### Transfer card top row (`.tl-card-top`)
```css
/* Current — horizontal flex, stats panel breaks on small screens */
@media(max-width:600px) {
    .tl-card-top    { flex-direction:column; padding:0 14px; }
    .tl-card-stats  { border-left:none; border-top:1px solid var(--border);
                      margin:0 0 8px; flex-wrap:wrap; }
    .tl-stat        { padding:8px 14px; flex:1; min-width:80px; }
}
```

#### Filter bar (`.tl-bar`)
```css
@media(max-width:600px) {
    .tl-bar         { gap:4px; padding:8px 10px; }
    .tl-chip        { padding:4px 10px; font-size:11px; }
    .tl-search      { width:100%; margin-left:0; margin-top:6px; }
    .tl-search input{ width:100%; }
}
```

#### Route line (`.tl-route-dash-line`)
```css
@media(max-width:600px) {
    .tl-route-dash-line { width:20px; }
}
```

#### Card footer (`.tl-card-foot`)
```css
@media(max-width:600px) {
    .tl-card-foot   { flex-wrap:wrap; gap:6px; }
    .tl-action      { flex:1; justify-content:center; }
    .tl-foot-time   { width:100%; text-align:center; margin-left:0; }
}
```

#### Page header (`.tl-page-header`)
```css
@media(max-width:600px) {
    .tl-page-header         { flex-direction:column; align-items:flex-start; }
    .tl-page-header-left h1 { font-size:20px; }
    .tl-new-btn             { width:100%; justify-content:center; }
}
```

---

### 2B — Request Transfer Form (`.rf-*`)

**Problems to fix:**

#### Two-column layout (`.rf-layout`)
Already has a `@media(max-width:860px)` rule — verify it exists, add if missing:
```css
@media(max-width:860px) {
    .rf-layout { grid-template-columns:1fr; }
}
```

#### Sticky summary panel (`.rf-summary`)
```css
@media(max-width:860px) {
    .rf-summary { position:static; }  /* remove sticky on tablet/mobile */
}
```

#### Two-column form fields (`.rf-row2`)
```css
@media(max-width:600px) {
    .rf-row2 { grid-template-columns:1fr; }
}
```

#### Product rows (`.rf-prod-row`)
```css
@media(max-width:600px) {
    .rf-prod-row    { flex-wrap:wrap; gap:8px; }
    .rf-prod-info   { width:100%; }
    .rf-stock       { align-items:flex-start; }
    .rf-add-btn     { width:100%; justify-content:center; }
}
```

#### Quantity controls (`.rf-qty-ctrl`)
```css
@media(max-width:600px) {
    .rf-item-top    { flex-wrap:wrap; }
    .rf-qty-ctrl    { width:100%; justify-content:space-between; }
}
```

---

### 2C — General rules for ALL transfer pages

Add to the `<style>` block of every file:

```css
/* Responsive base — applied to all transfer pages */
@media(max-width:600px) {
    /* Cards */
    .tl-card, .rf-card {
        border-radius:var(--rsm, 8px);
    }
    /* Tables inside cards — make them scroll horizontally */
    table {
        display:block;
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
        white-space:nowrap;
    }
    /* Prevent text overflow on narrow screens */
    .tl-num, .rf-prod-name, .tl-route-node {
        max-width:140px;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    /* Badges wrap instead of overflow */
    .tl-card-meta, .tl-dates {
        flex-wrap:wrap;
        gap:4px;
    }
}
```

---

### What NOT to change in Mission 2

- Do not change any `wire:` bindings, PHP logic, or route names
- Do not change desktop styles that are already working
- Do not touch any `.php` files
- Do not remove or alter existing `@media` rules — only add new ones or extend them

---

## ✅ Mission 2 Execution — Responsiveness

1. **For each transfer blade file:** add all applicable `@media` rules from sections 2A, 2B, 2C above
2. **Terminal:** `php artisan view:clear`
3. **Browser — mobile view (375px):** Use browser DevTools mobile emulation. Screenshot:
   - `http://localhost:8000/shop/transfers` at 375px width
   - `http://localhost:8000/shop/transfers/request` at 375px width
   - `http://localhost:8000/warehouse/transfers` at 375px width
4. **Browser — tablet view (768px):** Screenshot same three pages
5. **Artifact:** `mission-2-responsive.md` — list every file changed + breakpoints added

---

## 📸 Final Artifact Checklist

| Artifact | Contents |
|----------|----------|
| `mission-1-fonts.md` | Files changed, font-size values updated per file |
| `mission-2-responsive.md` | Files changed, media queries added per file |
| `desktop-shop-transfers.png` | Desktop screenshot — shop transfers list |
| `desktop-request-form.png` | Desktop screenshot — request transfer form |
| `desktop-warehouse-transfers.png` | Desktop screenshot — warehouse transfers list |
| `mobile-shop-transfers.png` | 375px screenshot — shop transfers list |
| `mobile-request-form.png` | 375px screenshot — request transfer form |
| `mobile-warehouse-transfers.png` | 375px screenshot — warehouse transfers list |

---

## 🔐 Security Policy

```
Terminal ALLOW: find, php artisan view:clear, php artisan cache:clear
Terminal DENY:  migrate, db:seed, git push, npm run build, rm
Browser: localhost:8000 only — desktop + mobile emulation
```
