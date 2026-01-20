Yes — that’s actually the **best** way to do it 👍
We’ll do this **iteratively**, and I’ll help you *compress thinking into a clean Claude Code prompt* instead of you over-engineering it.

Here’s how we’ll run this so it’s fast and not messy.

---

## How this back-and-forth will work

I’ll **ask you very targeted questions**, one chunk at a time.
You answer **short + rough** (bullet points / half sentences is fine).
I’ll:

1. Normalize your answers
2. Turn them into **Claude-ready instruction blocks**
3. Keep a running “final prompt” draft
4. Refine it until it’s something you can reuse forever

No fluff, no buzzwords.

---

## End goal (what you’ll get)

By the end, you’ll have **one clean Claude Code prompt** that:

* Plans your app architecture
* Knows what stack you’re using
* Knows what NOT to build
* Produces files, folders, and next steps
* Works well with your **top-level mono-repo setup** (Laravel + Flutter / web)

Think: *“Senior engineer briefing another senior engineer.”*

---

## Round 1 — Core framing (answer only these)

Reply by **copy-pasting this and filling it**.
Short answers only.

### 1️⃣ What is this app **in one sentence**?

> Example: “A smart recycling ecosystem that tracks cup usage and optimizes collection routes for beverage outlets.”

Your answer:

```
A smart recycling bin ecosystem that tracks cup usage and optimizes collection routes for beverage outlets.
```

---

### 2️⃣ Who is the **primary user**?

Pick ONE main user first.

```
Administrator (Internal)
```

---

### 3️⃣ Platform(s)?

Tick all that apply:

- [x] Web (Laravel)
- [ ] Mobile (Flutter)
- [x] Admin dashboard
- [x] API only

---

### 4️⃣ Core actions (max 5)

What are the **non-negotiable things** the app must do?

```
1.
2.
3.
4.
5.
```

---

### 5️⃣ What should Claude **NOT overbuild**?

Very important — list things you DON’T want.

```
no microservices, no over-abstracted patterns, no premature optimization
```

---

Reply with just those answers.
Once you do, I’ll:

* Clean it up
* Draft **Prompt v1**
* Then we move to **data model + file structure** next

We’ll get you unstuck fast.
