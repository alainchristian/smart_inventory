# Translations

`rw.json` holds Kinyarwanda translations, keyed by the literal English string
as it appears in the Blade source (Laravel's JSON translation file format).
English needs no file — `__('...')` returns the key verbatim when no
translation exists.

**Kinyarwanda text in `rw.json` is a best-effort machine draft, not a
certified translation.** It has not been reviewed by a native Kinyarwanda
speaker. Do not treat it as production-accurate for staff-facing use until
someone fluent in Kinyarwanda (ideally familiar with retail/financial
terminology — "outstanding balance," "price override," etc.) has reviewed it.

Carbon's Kinyarwanda locale (`rw`) is fully supported out of the box (verified
via `Carbon::getAvailableLocales()`), so dates use `->translatedFormat()`
rather than a numeric-only fallback.
