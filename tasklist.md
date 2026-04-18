> **Repository:** github.com/sakhileb/mines
> **Stack:** Laravel 11, Livewire 3, PostgreSQL, TailwindCSS, Vite
> **Version:** 2.0 | Updated: April 2026

---

## Table of Contents

1. [Feed Feature — Build Task List](#1-feed-feature--build-task-list)
   - 1.1 Database / Data Model
   - 1.2 Backend — API Endpoints
   - 1.3 Real-time — WebSocket / SSE
   - 1.4 Post Categories
   - 1.5 Frontend — Feed Timeline View
   - 1.6 Frontend — Compose Post
   - 1.7 Frontend — Comments & Interactions
   - 1.8 Phase 2 — Shift Templates & Notifications
   - 1.9 Phase 3 — Analytics & Reporting
   - 1.10 Phase 4 — Polish & Migration
2. [Platform Improvement Commands](#2-platform-improvement-commands)
   - Category A — Fix AVA's gaps
   - Category B — Match AVA's strengths
   - Category C — Infrastructure & quality
   - Category D — Competitive positioning
3. [Consolidated Implementation Order](#3-consolidated-implementation-order)

---

# 1. Feed Feature — Build Task List

> Replace WhatsApp channels with a structured, real-time activity stream for mine operations.

---

## Phase 1 — MVP: Core Feed

### 1.1 Database / Data Model

- [x] Create `feed_posts` table with fields: `id`, `author_id`, `role`, `mine_id`, `section`, `shift` (A/B/C), `category`, `body`, `priority` (normal/high/critical), `created_at`
- [x] Create `feed_acknowledgements` table: `post_id`, `user_id`, `acknowledged_at`
- [x] Create `feed_attachments` table: `post_id`, `file_url`, `file_type`, `uploaded_at`
- [x] Create `feed_comments` table: `id`, `post_id`, `parent_comment_id` (for nested replies), `author_id`, `role`, `body`, `created_at`, `is_edited`
- [x] Create `feed_likes` table: `post_id`, `user_id`, `liked_at`
- [x] Create `feed_approvals` table: `post_id`, `approver_id`, `status` (approved/rejected), `reason` (if rejected), `reviewed_at`
- [x] Add database indexes on `mine_id`, `section`, `category`, `created_at` for fast filtering
- [x] Write and run migrations

### 1.2 Backend — API Endpoints

- [x] `GET /api/feed` — paginated list of posts, filterable by `mine_id`, `section`, `category`, `shift`, `date`
- [x] `POST /api/feed` — create a new feed post (validate required fields per category)
- [x] `DELETE /api/feed/:id` — soft-delete a post (author or admin only)
- [x] `POST /api/feed/:id/acknowledge` — mark post as acknowledged by current user
- [x] `GET /api/feed/:id/acknowledgements` — list users who acknowledged a post
- [x] `POST /api/feed/:id/attachments` — upload photo/voice note to a post
- [x] `POST /api/feed/:id/comments` — add a comment or reply to a post
- [x] `PUT /api/feed/comments/:comment_id` — edit own comment (within time window)
- [x] `DELETE /api/feed/comments/:comment_id` — delete own comment or admin delete
- [x] `GET /api/feed/:id/comments` — fetch comments with nested replies
- [x] `POST /api/feed/:id/like` — like/unlike a post
- [x] `GET /api/feed/:id/likes` — list users who liked a post
- [x] `POST /api/feed/:id/approve` — approve a post (supervisor/manager/safety officer only)
- [x] `POST /api/feed/:id/reject` — reject a post with required reason
- [x] Add role-based access control: operators see their section only, supervisors see full mine + can approve/reject, admins see all mines + can approve/reject

### 1.3 Real-time — WebSocket / SSE

- [x] Set up a WebSocket server (or Server-Sent Events endpoint)
- [x] Scope connections by `mine_id` and `section` — users only receive events relevant to them
- [x] Broadcast a `new_post` event to connected clients when a post is created
- [x] Broadcast an `acknowledgement_updated` event when someone acks a post
- [x] Broadcast `new_comment`, `edited_comment`, `deleted_comment` events
- [x] Broadcast `post_liked` event (with updated like count)
- [x] Broadcast `post_approved` or `post_rejected` event
- [x] Handle reconnection and missed events (send last N posts/comments on reconnect)

### 1.4 Post Categories

- [x] Define and enforce the 5 categories: `breakdown`, `shift_update`, `safety_alert`, `production`, `general`
- [x] Set `priority: critical` automatically for `safety_alert` posts
- [x] Validate that breakdown posts include: machine ID, failure type, estimated downtime
- [x] Validate that shift update posts include: section, shift, loads per hour

### 1.5 Frontend — Feed Timeline View

- [x] Build feed timeline page at `/feed` (or inside the mine dashboard)
- [x] Display posts in reverse-chronological order, live-updating via WebSocket
- [x] Show on each post: author name, role, section, shift, timestamp, category badge, body text
- [x] Colour-code category badges: breakdown (red), shift update (amber), safety alert (red + pinned), production (green), general (gray)
- [x] Pin `critical` posts to the top of the feed
- [x] Show acknowledgement count and "Ack" button on each post
- [x] Show like button with count, like/unlike optimistically
- [x] Show comment count + expandable comments section (with nested replies)
- [x] Show approval badge (approved/pending/rejected) if current user has permission to see it
- [x] Show "Approve" / "Reject" buttons for authorized roles (supervisors, safety officers, managers)
- [x] When rejecting, open modal to enter reason
- [x] Mark post as acknowledged in UI immediately on click (optimistic update)
- [x] Add filter bar: filter by category, section, shift, date range, approval status (pending/approved/rejected)
- [x] Add infinite scroll / load more pagination

### 1.6 Frontend — Compose Post

- [x] Build "New Post" button / modal accessible from the feed
- [x] Show category selector as first step (breakdown, shift update, safety alert, production, general)
- [x] Render dynamic form fields based on selected category
- [x] Add free-text body field (required)
- [x] Add photo/file attachment input (optional)
- [x] Show mine and section pre-filled from the logged-in user's profile
- [x] Show shift selector (A / B / C) with current shift pre-selected
- [x] Submit post and show it immediately in the feed on success

### 1.7 Frontend — Comments & Interactions

- [x] Build comment input on each post (with @mention support)
- [x] Show existing comments in chronological or threaded order
- [x] Allow replying to a specific comment (nested replies, 1 level deep)
- [x] Allow editing own comment within 5 minutes (show "(edited)")
- [x] Allow deleting own comment (or admin delete)
- [x] Show comment author name, role, timestamp
- [x] Real-time comment updates via WebSocket

---

## Phase 2 — Shift Templates & Notifications

### 2.1 Shift Templates

- [x] Create `shift_templates` table: `mine_id`, `category`, `template_body`, `required_fields`
- [x] Build template management UI for admins (create, edit, delete templates)
- [x] In the compose modal, offer "Use template" shortcut per category
- [x] Pre-fill common breakdown post with: machine ID field, dropdown for failure type, downtime estimate field
- [x] Pre-fill shift update post with: loads/hour field, tonnage vs target field, headcount field

### 2.2 Push Notifications

- [x] Implement push notification support (web push or in-app)
- [x] Send push to all users in a mine when a `critical` priority post is created
- [x] Send push to maintenance role users when a `breakdown` post is created
- [x] Send push to safety officers when a `safety_alert` post is created
- [x] Send push to post author when someone comments on their post
- [x] Send push when a post you commented on gets a reply
- [x] Send push when a post you liked gets approved/rejected (if relevant)
- [x] Allow users to configure notification preferences per category in their profile settings

### 2.3 Email Digest

- [x] Build shift summary email: aggregates all posts from a completed shift
- [x] Schedule digest to send at end of each shift (e.g. 06:00, 14:00, 22:00)
- [x] Include: post count by category, any unacknowledged critical posts, breakdown summary
- [x] Include most liked posts and most commented posts for that shift
- [x] Include pending approvals summary for supervisors/managers
- [x] Allow supervisors and managers to opt in/out of digests per mine

### 2.4 Mentions

- [x] Add `@mention` support in post body (mention a user by name/role)
- [x] Add `@mention` support in comments as well
- [x] Notify mentioned users in-app and via push
- [x] Highlight mentions in the feed post body and comments

---

## Phase 3 — Analytics & Reporting (Modify the reports page)

### 3.1 Shift Reports

- [x] Build shift report view: summary of all feed activity for a given shift
- [x] Show total posts by category, total acknowledgements, any unresolved breakdowns
- [x] Show engagement metrics: total likes, total comments, posts with most interactions
- [x] Show approval stats: approved vs rejected posts by role/section
- [x] Allow export to PDF or CSV

### 3.2 Machine Breakdown Analytics

- [x] Track breakdown posts per machine ID over time
- [x] Calculate mean time to resolution (MTTR) — time from breakdown post to resolution post
- [x] Build a chart showing breakdown frequency per section / per machine

### 3.3 Production Analytics

- [x] Aggregate loads-per-hour data from shift update posts over time
- [x] Chart actual production vs target per shift, per section
- [x] Show trend: week-on-week, month-on-month

### 3.4 Historical Log

- [x] Build searchable archive of all feed posts (full-text search on body + comments)
- [x] Filter by mine, section, category, date range, author, shift, approval status
- [x] Ensure log is immutable — soft deletes only, original content preserved

---

## Phase 4 — Polish & Migration

### 4.1 Admin Controls

- [x] Allow admins to pin any post to the top of the feed
- [x] Allow admins to delete any post with an audit trail
- [x] Allow admins to manage which sections and shifts are active per mine
- [x] Allow admins to override approvals/rejections and see audit log of who approved/rejected what

### 4.2 Onboarding

- [x] Build onboarding guide explaining the feed to existing users (5-step Alpine.js overlay)
- [x] Add tooltip walkthroughs on first visit to the feed (shown when `seen_onboarding_at` is null)
- [x] Create a "getting started" post that auto-posts to the feed when a mine is set up

### 4.3 WhatsApp Migration

- [x] Communicate to all mine users that WhatsApp channels are being replaced (onboarding invite email)
- [x] Send onboarding invites to all users who need platform access (`/feed/migration` page + mailable)
- [x] Set a go-live date and decommission the WhatsApp channels (`feed_go_live_at` on teams table)

---

### Feed Technical Notes

- Real-time: prefer **WebSockets** (Socket.io or native WS) for bi-directional; use **SSE** if you only need server → client push
- Attachments: store files in object storage (S3 / Cloudflare R2), save URL in `feed_attachments`
- Auth: all feed endpoints must require authentication; enforce `mine_id` scoping at the query level, never trust client-supplied mine ID alone
- Offline support (Phase 2+): consider queuing posts locally when a user is underground with no signal, and syncing when they reconnect
- Social interactions: comments, likes, and approvals are separate database tables to keep the main feed fast. Use denormalised counts (`like_count`, `comment_count`) on `feed_posts` for efficient sorting and display.


