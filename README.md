# 📘 RPG Maker Interview Form Plugin

A lightweight, interactive RPG-style interview plugin for WordPress. Designed for research, surveys, or onboarding workflows that simulate a conversation with a character.

---

## 🎯 Features

* Custom Post Type: `Interview Chapter`
* Question builder: Add multiple questions per chapter
* Supports text and multiple choice answers
* Frontend RPG-style UI (single character asks one question at a time)
* User inputs are sent to Google Sheets via Apps Script
* Token-based ref validation (1 user per ref)
* REST API ready: questions loaded dynamically via `?chapter=...&ref=...`

---

## 🚀 Getting Started

### 1. Installation

Upload the plugin to `/wp-content/plugins/` or install via WordPress admin.

### 2. Create Interview Chapters

* Go to **Interview Chapters** menu in WordPress admin
* Click **Add Chapter**, fill in the title
* Add questions (text or choice)
* Add whitelist tokens (ref values) — one per line

### 3. Embed on a Page

* Use shortcode: `[rpg_form_game]`
* Access the page with URL parameters:

  ```
  https://yourdomain.com/your-form-page/?chapter=chapter-slug&ref=user-xyz
  ```

---

## 📡 Google Sheets Integration

Use the provided Google Apps Script (linked separately) to receive submissions. Each answer is submitted individually with:

* Timestamp
* Chapter
* Ref
* Question
* Answer

Ensure your script validates secret + ref against whitelist in Sheet2.

---

## 🧪 REST API Endpoint

List of chapters:

```
GET /wp-json/wp/v2/rpg_chapter
```

By slug:

```
GET /wp-json/wp/v2/rpg_chapter?slug=your-slug
```

---

## 🛡 Security & Notes

* Ref token must match whitelist in the chapter meta
* Ref is only valid for single use (marked used after submit)
* Only one visual character is used
* No scoring or branching logic (submission only)

---

## 📦 GitHub Integration (Optional)

This plugin supports auto-updating via [GitHub Updater](https://github.com/afragen/github-updater):

* Add repo URL to plugin header
* Tag your versions (e.g. v2.0.1)
* Push with `git push origin main --tags`

---

## 📜 License

GPL v2 or later.

---

## 🙌 Credits

Made with ❤️ by Lanang & ChatGPT.
