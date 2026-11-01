{{-- Behaviour for the applicant form: dependent address selects, AD/BS conversion, add/remove rows. --}}
@push('scripts')
    <script>
        (function () {
            const convertUrl = @json(route('lookup.date.convert'));
            const text = {
                converting: @json(__('Converting…')),
                invalid: @json(__('This date could not be converted. Check it and try again.')),
                hint: @json(__('Enter either date and the other is filled in automatically.')),
                confirmRemove: @json(__('Remove this entry? Its saved details and files are deleted when you save.')),
            };
            const BS_PATTERN = /^\d{4}-\d{2}-\d{2}$/;
            const toAsciiDigits = (value) => value.replace(/[०-९]/g, (d) => '०१२३४५६७८९'.indexOf(d));

            // ---------- District -> municipality -> ward ----------
            function resetSelect(select) {
                if (!select) return;
                select.innerHTML = '';
                select.add(new Option(select.dataset.placeholder || '', ''));
                select.disabled = true;
            }

            async function loadChildren(parent, child) {
                resetSelect(child);
                if (!parent.value) return;

                const response = await fetch(parent.dataset.childrenUrl.replace('__ID__', encodeURIComponent(parent.value)), {
                    headers: { Accept: 'application/json' },
                });
                if (!response.ok) return;

                (await response.json()).forEach((item) => child.add(new Option(item.name, item.id)));
                child.disabled = false;
            }

            document.addEventListener('change', (event) => {
                const select = event.target.closest('[data-location]');
                if (!select) return;

                const group = select.closest('[data-location-group]');
                const municipality = group.querySelector('[data-location="municipality"]');
                const ward = group.querySelector('[data-location="ward"]');

                if (select.dataset.location === 'district') {
                    resetSelect(ward);
                    loadChildren(select, municipality);
                } else if (select.dataset.location === 'municipality') {
                    loadChildren(select, ward);
                }
            });

            // ---------- A.D. <-> B.S. ----------
            async function convert(input) {
                const pair = input.closest('[data-date-pair]');
                const from = input.dataset.calendar;
                const other = pair.querySelector(`[data-calendar="${from === 'ad' ? 'bs' : 'ad'}"]`);
                const status = pair.querySelector('[data-date-status]');
                const value = from === 'bs' ? toAsciiDigits(input.value.trim()) : input.value;

                status.classList.remove('text-danger');
                if (!value) {
                    status.textContent = text.hint;
                    return;
                }
                if (from === 'bs') {
                    if (!BS_PATTERN.test(value)) return;
                    input.value = value;
                }

                status.textContent = text.converting;
                try {
                    const response = await fetch(`${convertUrl}?${from}=${encodeURIComponent(value)}`, {
                        headers: { Accept: 'application/json' },
                    });
                    if (!response.ok) throw new Error();
                    const data = await response.json();
                    other.value = data[from === 'ad' ? 'bs' : 'ad'];
                    other.classList.remove('is-invalid');
                    input.classList.remove('is-invalid');
                    status.textContent = text.hint;
                } catch {
                    status.textContent = text.invalid;
                    status.classList.add('text-danger');
                }
            }

            document.addEventListener('change', (event) => {
                if (event.target.matches('[data-calendar]')) convert(event.target);
            });
            // B.S. converts as soon as a full date is typed, without waiting for the field to lose focus
            document.addEventListener('input', (event) => {
                if (event.target.matches('[data-calendar="bs"]') && BS_PATTERN.test(toAsciiDigits(event.target.value.trim()))) {
                    convert(event.target);
                }
            });

            // ---------- Repeatable rows ----------
            let nextIndex = Date.now();

            document.addEventListener('click', (event) => {
                const add = event.target.closest('[data-add-row]');
                if (add) {
                    const template = document.getElementById(add.dataset.addRow);
                    const rows = document.getElementById(add.dataset.rows);
                    const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
                    rows.insertAdjacentHTML('beforeend', html);
                    rows.lastElementChild.querySelector('input:not([type=hidden]), select')?.focus();
                    return;
                }

                const remove = event.target.closest('[data-remove-row]');
                if (remove) {
                    const row = remove.closest('[data-row]');
                    const saved = row.querySelector('input[type=hidden][name$="[id]"]');
                    if (saved && !window.confirm(text.confirmRemove)) return;
                    row.remove();
                }
            });
        })();
    </script>
@endpush
