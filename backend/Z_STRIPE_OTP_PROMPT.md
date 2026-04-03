btwso in /Users/danieltan/mobius-schema/VIVA_FIXES.md, whats not done is OTP and most likely stripe cuz the landing page for getting to subscribe
is actually factually logically wrong currently, im hoping it gets done but lets say these 2 tasks arent done, can you help craft a prompt that
i will copy and paste into a file for later use which can have CC PLAN AS MUCH AS POSSIBLE USING SUPERPOWERS to get that shit done asap and
not hallucinate and read all its markdown files by daddy?

but we also need records in our domain tables for the admin dashboard to show.

## TASK 2: OTP Phone Verification (Viva Fix #9)

### What the schema has:
- `users.phone` — phone number field
- `users.phone_verified_at` — nullable timestamp (null = not verified)

### What needs to be built:
- An OTP verification flow: user enters phone → system sends 6-digit OTP → user enters OTP → phone_verified_at set
- For the demo/FYP, use a SIMULATED OTP approach — don't spend money on Twilio:
- Option A: Log the OTP to Laravel's log file (`storage/logs/laravel.log`) and display a flash message saying "OTP sent to your phone" — the
"OTP" is in the log for demo purposes
- Option B: Use Mailtrap (already configured in .env) to send the OTP via email instead of SMS — explain to lecturer that "in production this
would be SMS via Twilio, we're using email for the demo"
- Option C: Show the OTP directly on screen in a dismissable alert (fastest for demo)
- Needs: a controller, a simple Blade form (enter phone → enter OTP), rate limiting, and the database update
- Should be accessible from the user's profile page
- Brand owners specifically need phone_verified_at to be set (per DOMAIN.md)

## APPROACH

1. Read ALL the files listed above first
2. Check the current state of the codebase — run `git log --oneline -5`, check what routes exist, what views exist
3. Plan using superpowers before touching any code
4. For Stripe: test the existing flow first, then fix what's broken
5. For OTP: use Option A or B (simulated), don't overcomplicate
6. After each task, run `vendor/bin/pint --dirty` and `php artisan test --compact` if tests exist
7. Tell Daniel to commit after each working checkpoint

Save this to a file like Z_STRIPE_OTP_PROMPT.md in the backend or schema directory. When you start a fresh CC session, paste the entire thing as
your first message.