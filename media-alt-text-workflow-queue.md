# Media Alt Text Workflow Queue

**Author:** Jesse Jaeger  
**Website:** https://jessejaeger.com  
**Requires:** WordPress 6.0+ (tested up to latest)  
**PHP:** 7.4+  
**License:** GPLv2 or later

## One-line purpose
A simple, task-oriented workflow that helps editors add missing image alt text — with a red/orange **admin badge** showing how many images still need attention.

## Tagline
**“Make your media library accessible, one image at a time.”**

---

## Audience
- **Marketing teams & content editors** who need a clear, repeatable process.
- **Blog/site owners** who want a friendly way to get compliant with alt text.
- Anyone who wants a quick, distraction-free **queue** to finish alt text without bouncing around the Media Library.

---

## Core Philosophy
- **Task-first UX**: one image at a time, focused, fast.
- **No over-engineering**: use core WordPress meta & options, no custom DB tables.
- **Zero-learning curve**: familiar WP UI where it makes sense; pretty, focused task screen where it helps.
- **Workflow visibility**: admin-menu **badge count** of missing alt text; **progress** of this session’s queue.
- **Secure & role-aware**: capability checks, nonces, sanitization.

---

## MVP Feature Set (v1.0)
- Detect images with **missing or empty** alt text.
- **Queue screen**: presents a single image at a time with inline alt-text input.
  - Actions: **Save & Next**, **Skip**, **Previous** (optional), **End Session**.
  - **Progress indicator** (completed + skipped / total).
  - Skipped items still advance progress; at the end you can **restart** to revisit skips.
- **List screen**: table of all images missing alt text.
  - Columns: **Preview**, **Filename**, **Where It’s Used**, **Upload Date**.
  - Filters: **Upload date**, **Mime type**, **Attached/Unattached**, **Search** by filename.
  - Sorting by filename/date.
  - Quick action to jump any row into the **Queue** at that item.
- **Admin menu badge**: count of images missing alt text, shown next to the submenu item under **Media**.
- **Roles/Capabilities**: restricted to Editors and Admins by default (custom capability).
- **Security**: nonce-protected actions, sanitized inputs, strict capability checks.
- **Feedback**: on-screen success/error messages.

---

## What v1.0 intentionally does **not** include
- AI generation of alt text.  
- Custom DB tables.  
- Multisite/network management.  
- Heavy reporting/assignments/notifications.  

These belong to the **Future Roadmap**.

---

## Information Architecture

### Admin Navigation
- **Media → Alt Text Queue** (badge shows global missing count)
  - **Queue** (default tab) — one-by-one fix flow.
  - **List** — table view with filters/sorting.
  - **Settings** — simple controls (below).

### Settings (MVP)
- **Items per queue session** (default: 50; “All” available).  
- **Filters to include in session** (date range, mime type, attached/unattached).  
- **Restart session** button (clears *skipped* for your user).  
- **Capability** setting (choose which roles can use it) — optional, default to `edit_others_posts` or custom capability.

---

## Data Model (no custom tables)
- **Alt text**: uses core meta `_wp_attachment_image_alt`.  
- **Skipped list (per user / per session)**: `user_meta('matwq_skipped_ids')` as an array (or transient keyed by user ID).  
- **Session context (per user)**: `user_meta('matwq_session_query')` to store current filter parameters & item IDs for progress; resets on **Restart session**.  
- **Settings**: `options('matwq_settings')`.

> Skipped items do **not** write any meta on attachments. They’re only excluded for the current user’s session. The global badge still counts them as missing.

---

## “Where it’s used” (finding usage)
We surface pages/posts where an image appears by searching `post_content` for:
- The file URL (from `wp_get_attachment_url($id)`).
- The Gutenberg class pattern `wp-image-{$id}`.
- Block JSON references like `"id": {$id}` within block comments.

Implementation detail:
- Build an indexed helper that queries `posts` with `LIKE` on:
  - `%/uploads/.../filename.ext%`
  - `%wp-image-{$id}%`
  - `%"id":{$id}%`
- Limit to public post types. Cache results per attachment (transient, e.g., 12 hours) and bust cache when the image is re-attached/edited.

> This is best-effort; we clearly label “found on” URLs and provide a **“Re-scan usage”** link per image to refresh the cache.

---

## UX Flows

### Queue Flow
1. User opens **Media → Alt Text Queue**.
2. First item loads: shows **thumbnail**, **filename**, **where used** (up to 3 URLs + “show all”).
3. Input field + **Save & Next** / **Skip**.
4. On **Save & Next**:
   - Sanitize text → update `_wp_attachment_image_alt` → success notice → next image.
5. On **Skip**:
   - Add ID to `matwq_skipped_ids` for this user/session → next image.
6. **Progress bar** updates each action (completed + skipped / total in session).
7. At end: **You’re done!** with options to **Restart** (clears skipped) or **Go to List**.

### List Flow
- Defaults to all missing-alt images matching filter set.
- Columns:
  - **Preview** (80x)  
  - **Filename**  
  - **Where Used** (first 2 URLs + “+N more”)  
  - **Uploaded** (date)  
  - **Actions**: “Open in Queue here”
- Filters:
  - Date range, mime type (image/jpeg/png/webp/svg if allowed), attached/unattached.
- Sorting: filename, date.

---

## Admin Badge Count
- Hook into `admin_menu` to add submenu with a `<span class="awaiting-mod">X</span>` bubble in the menu label.  
- Count = **global** number of attachments with **missing or empty** `_wp_attachment_image_alt`.  
- Cached per admin request (e.g., transient 60s) to keep menus snappy.

---

## Capabilities & Security
- Default required capability: `edit_others_posts` (Editors/Admins).  
  - Expose a filter `matwq_required_capability` for customization.
- All write actions check capability, verify nonce, and sanitize input via `sanitize_text_field`.
- Nonces:
  - Queue save: `matwq_save_alt_{attachment_id}`
  - Session actions (skip/restart): `matwq_session_action`
- Escaping: all outputs escaped with `esc_html`, `esc_attr`, `esc_url`.

---

## Performance & Scale
- **Queries**:
  - Missing-alt query uses `WP_Query` on `attachment` with `post_mime_type=image` and `meta_query` for `_wp_attachment_image_alt` nonexistent or empty.
  - Use pagination and `fields => ids` for fast lists.
- **Usage detection**:
  - Results cached per attachment; batch warm on first list render (with soft timeouts to avoid TTFB spikes).
- **Assets**:
  - Load minimal CSS/JS **only** on plugin pages.
- **Counts**:
  - Badge count cached briefly; full recount on Settings save.

---

## Namespacing & Structure

**Namespace:** `JJ\AltTextWorkflowQueue`

**File layout**
```plaintext
media-alt-text-workflow-queue/
├─ media-alt-text-workflow-queue.php   # Plugin bootstrap
├─ readme.txt                          # For wp.org (generated later)
├─ src/
│  ├─ Plugin.php                       # Service container / boot
│  ├─ Admin/
│  │  ├─ Menu.php                      # Submenu + badge
│  │  ├─ Screens/
│  │  │  ├─ QueueScreen.php
│  │  │  └─ ListScreen.php
│  │  └─ Settings.php
│  ├─ Services/
│  │  ├─ Finder.php                    # Missing-alt finder
│  │  ├─ UsageLocator.php              # “Where used” detection + cache
│  │  ├─ Session.php                   # Per-user session state (skips, filters)
│  │  └─ Capability.php
│  └─ Utils/
│     ├─ Html.php
│     └─ Sanitization.php
├─ assets/
│  ├─ css/
│  │  ├─ queue.css
│  │  └─ list.css
│  └─ js/
│     ├─ queue.js
│     └─ list.js
└─ languages/                          # i18n (later)
```

---

## Future Roadmap (not in v1 — for later/Pro)
- **Team workflows**: assignments, status (“needs review”), comments, due dates.
- **Reports**: site-wide accessibility report (export CSV/PDF) to show compliance improvements over time.
- **Notifications**: weekly digest for outstanding items (per role).
- **AI assist (opt-in)**: suggestion button (user reviews before saving).
- **Multisite support**: network overview and per-site queues.
- **White-label mode**: agency branding, hide plugin name, custom capability map.
- **Performance mode**: optional custom table indices for very large libraries (50k+ images).
- **CLI**: `wp altwq report`, `wp altwq warm-usage`.

---

## Milestone Plan

**M1 — MVP (public)**
- Queue, List, Settings screens.
- Missing-alt detection, save/skip, progress.
- Admin badge count.
- Basic usage locator w/ caching.
- Capability check + nonces.
- Readme + first screenshots.

**M2 — UX Polish & QA**
- Better empty states (no images left).
- Keyboard flows and ARIA improvements.
- Settings validation + reset.
- i18n groundwork.

**M3 — Light Reporting**
- Count by month, % complete, export CSV (missing list).

---

## Monetization Ideas (later)
- **Pro**: Team workflows, reports, notifications, priority support.
- **Pro**: AI assist (token passthrough or bundled tiers).
- **Agency**: White-label, multisite report dashboards.
- **Sponsorships**: “Built by Jesse Jaeger” + consulting links.

---

## Quick Dev Checklist
- [ ] Namespace scaffolding + autoload (simple `spl_autoload_register` or Composer later).
- [ ] Admin submenu + badge bubble.
- [ ] Finder service for missing-alt images (+ unit testable).
- [ ] Session service (ids snapshot, skips per user).
- [ ] Queue UI (nonce, save/skip, progress).
- [ ] List UI (filters, sort, jump-to-queue).
- [ ] Usage locator (+ transient cache).
- [ ] Settings (items per session, filters, restart).
- [ ] Sanitization + escaping audit.
- [ ] Readme + screenshots.
