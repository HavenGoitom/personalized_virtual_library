# Personal Virtual Library (HTML/CSS/JS)

A simple, fast, local-storage-powered virtual library app. Browse books from the main library, view real summaries from the books themselves (not generic outlines), and add any book to **your personal shelf** with one click.

No backend. No database. Everything runs in the browser.

---

## 🚀 Features

* **Library View:** See all available books with real content previews.
* **Add to Shelf:** Click **Add to Shelf** on any book to save it to your personal collection.
* **Personal Shelf:** View your saved books anytime.
* **Edit Book Info:** Update title, author, or notes for shelf books.
* **Delete Books:** Remove books from your personal shelf.
* **Persistent Data:** Stored fully in **LocalStorage**.

---

## 🛠 Tech Stack

* **HTML** – Structure
* **CSS** – Layout & UI styling
* **JavaScript** – App logic + LocalStorage CRUD

---

## 📦 How It Works

### Library → Shelf Flow

1. App loads the full library from a JSON file or static JS list.
2. User clicks **Add to Shelf**.
3. JS saves the book to LocalStorage under `userShelf`.
4. Personal Shelf automatically displays saved books.

### Shelf Editing

* Edit: Updates LocalStorage entry.
* Delete: Removes the entry.

---

## 📁 File Structure

```
/project
│ index.html       → main UI
│ style.css        → styling
│ script.js        → logic + LocalStorage
```

---

## ▶️ Running the App

Just open **index.html** in any browser.
Nothing to install.

---

## 🔮 Future Ideas

* Search bar for books
* Sort by genre or author
* Dark/light mode

---

Enjoy your personal virtual library! 📚
