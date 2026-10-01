import './bootstrap';

// Self-hosted fonts (were Google Fonts): same origin, cached with /build
import '@fontsource/dm-sans/300.css';
import '@fontsource/dm-sans/400.css';
import '@fontsource/dm-sans/500.css';
import '@fontsource/dm-sans/600.css';
import '@fontsource/dm-sans/700.css';
import '@fontsource/dm-sans/800.css';
import '@fontsource/dm-mono/400.css';
import '@fontsource/dm-mono/500.css';

// Drag-to-reorder (report builder). Bundled instead of loaded from jsdelivr.
import Sortable from 'sortablejs';
window.Sortable = Sortable;

// Wait for Alpine (bundled with Livewire) to be available
document.addEventListener('alpine:init', () => {
    // Get the Alpine instance from Livewire
    const Alpine = window.Alpine;

    // Initialize theme store (light theme only)
    Alpine.store('theme', {
        current: 'light'
    });

    // <x-number-input> / <x-money-input>: right-aligned numbers with thousand
    // separators while typing. The box shows "12,500"; the bound model (via
    // x-modelable) gets "12500" ('' when empty), the same string a
    // type="number" input sent, so server-side casts and rules are unchanged.
    // Options: decimals (default 0), signed (allow "-"), max (clamped while
    // typing), live (ms: commit to Livewire after typing pauses).
    const groupDigits = (digits) => digits.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    // fromModel: a value from PHP/Alpine ("12500.00", "-3.5"), where "." is
    // the decimal point and there are no commas. Otherwise it's typed text.
    const parseNumber = (value, opts, fromModel) => {
        let s = value === null || value === undefined ? '' : String(value).trim();
        const negative = opts.signed && s.startsWith('-');
        let int, frac = null;
        if (opts.decimals > 0 && s.includes('.')) {
            const dot = s.indexOf('.');
            int = s.slice(0, dot).replace(/\D/g, '');
            frac = s.slice(dot + 1).replace(/\D/g, '').slice(0, opts.decimals);
        } else {
            // Integer fields drop a decimal part coming from the model
            // ("12500.00" from decimal casts); a typed "." is just ignored.
            int = (fromModel ? s.split('.')[0] : s).replace(/\D/g, '');
        }
        int = int.replace(/^0+(?=\d)/, '').slice(0, 12);
        if (frac !== null && int === '') int = '0';

        if (opts.max !== null && int !== '' && Number(int + (frac ? '.' + frac : '')) > opts.max) {
            [int, frac] = [String(Math.trunc(opts.max)), null];
        }
        const sign = negative ? '-' : '';
        return {
            text: sign + groupDigits(int) + (frac !== null ? '.' + frac : ''),
            value: int === '' ? '' : sign + int + (frac ? '.' + frac : ''),
        };
    };

    Alpine.data('numberInput', (options = {}) => ({
        numValue: '',
        numOpts: { decimals: 0, signed: false, max: null, live: 0, ...options },
        numTimer: null,

        init() {
            const el = this.$el;
            // Without a model (value="…" + @change) start from the markup's value.
            if (el.value !== '') this.numValue = parseNumber(el.value, this.numOpts, true).value;

            // x-model re-renders the raw value ("312000") into the box on every
            // model change, which then gets re-formatted and loses the caret.
            // Route that hook through our formatter; it writes nothing when
            // the text is already right.
            Object.defineProperty(el, '_x_forceModelUpdate', {
                configurable: true,
                get: () => (value) => this.numShow(value),
                set: () => {},
            });
            el.addEventListener('input', () => this.numTyped());

            // Browsers skip the native `change` event once a script has
            // rewritten the value, which wire:model.lazy / .change rely on.
            // Fire it ourselves on blur when the text changed since focus.
            let atFocus = null;
            let keepSelection = false;
            el.addEventListener('focus', () => {
                atFocus = el.value;
                // A lone 0 (int properties default to it) gets replaced, not
                // appended to. The click's mouseup would undo the selection.
                if (el.value === '0') {
                    el.select();
                    keepSelection = true;
                }
            });
            el.addEventListener('mouseup', (e) => {
                if (keepSelection) e.preventDefault();
                keepSelection = false;
            });
            el.addEventListener('keydown', () => { keepSelection = false; });
            el.addEventListener('change', () => { atFocus = el.value; });
            el.addEventListener('blur', () => {
                this.numShow(this.numValue); // tidy a half-typed "12." to "12"
                if (atFocus !== null && el.value !== atFocus) {
                    atFocus = el.value;
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            this.$watch('numValue', (value) => this.numShow(value));
            this.numShow(this.numValue);
        },

        numShow(value) {
            const { text } = parseNumber(value, this.numOpts, true);
            // Keep a half-typed "12." / "-" while the model still reads "12" / "".
            if (document.activeElement === this.$el
                && parseNumber(this.$el.value, this.numOpts, false).value === parseNumber(value, this.numOpts, true).value) return;
            if (this.$el.value !== text) this.$el.value = text;
        },

        numTyped() {
            const el = this.$el;
            const raw = el.value;
            const caret = el.selectionStart ?? raw.length;
            const { text, value } = parseNumber(raw, this.numOpts, false);
            // The caret keeps the same number of digits (and ".") to its right.
            const significant = /[\d.]/;
            const countRight = (str) => [...str].filter((c) => significant.test(c)).length;
            const right = Math.min(countRight(raw.slice(caret)), countRight(text));

            el.value = text;
            let pos = text.length;
            for (let seen = 0; pos > 0 && seen < right; pos--) {
                if (significant.test(text[pos - 1])) seen++;
            }
            // Don't leave the caret just after a comma it can't type past.
            while (pos > 0 && text[pos - 1] === ',') pos--;
            if (document.activeElement === el) el.setSelectionRange(pos, pos);

            if (value === parseNumber(this.numValue, this.numOpts, true).value) return;
            this.numValue = value;

            // wire:model.live.debounce: the component set the model without
            // .live; send it once typing pauses. The value is already on $wire,
            // so an action fired meanwhile (Save) still sees it.
            if (this.numOpts.live > 0) {
                clearTimeout(this.numTimer);
                this.numTimer = setTimeout(() => this.$wire.$commit(), this.numOpts.live);
            }
        },
    }));

    // <x-signature-pad model="prop">: draw with a finger / mouse; the PNG data
    // URL goes to the Livewire property (cleared → ''). The canvas sits in a
    // wire:ignore block so re-renders don't wipe the drawing.
    Alpine.data('signaturePad', (model) => ({
        empty: true,
        drawing: false,
        last: null,

        init() {
            const c = this.$refs.canvas;
            const ctx = c.getContext('2d');
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = getComputedStyle(document.documentElement).getPropertyValue('--text').trim() || '#1a1f36';
            this.ctx = ctx;
        },
        pos(e) {
            const r = this.$refs.canvas.getBoundingClientRect();
            return { x: (e.clientX - r.left) * (this.$refs.canvas.width / r.width), y: (e.clientY - r.top) * (this.$refs.canvas.height / r.height) };
        },
        start(e) {
            this.drawing = true;
            this.last = this.pos(e);
            this.$refs.canvas.setPointerCapture(e.pointerId);
        },
        move(e) {
            if (!this.drawing) return;
            const p = this.pos(e);
            this.ctx.beginPath();
            this.ctx.moveTo(this.last.x, this.last.y);
            this.ctx.lineTo(p.x, p.y);
            this.ctx.stroke();
            this.last = p;
            this.empty = false;
        },
        end() {
            if (!this.drawing) return;
            this.drawing = false;
            if (!this.empty) this.$wire.set(model, this.$refs.canvas.toDataURL('image/png'));
        },
        clear() {
            this.ctx.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height);
            this.empty = true;
            this.$wire.set(model, '');
        },
    }));

    // Set light theme on page load
    document.documentElement.setAttribute('data-theme', 'light');
    localStorage.setItem('theme', 'light');
});
