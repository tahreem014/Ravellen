
<?php if (! $__env->hasRenderedOnce('794c31fe-d592-44ee-b12f-42e76e1c1139')): $__env->markAsRenderedOnce('794c31fe-d592-44ee-b12f-42e76e1c1139'); ?>
<script>
window.LocationPicker = (function () {
    const CITIES_URL = <?php echo json_encode(route('locations.cities', ['code' => 'XX']), 512) ?>;
    const loaded = {};   // country code → Promise<string[]>, so each country is fetched once per page

    function cities(code) {
        if (!loaded[code]) {
            loaded[code] = fetch(CITIES_URL.replace('XX', code), { headers: { 'Accept': 'application/json' } })
                .then(res => res.ok ? res.json() : { cities: [] })
                .then(data => data.cities || [])
                .catch(() => { delete loaded[code]; return []; });   // offline: try again next time, typing still works
        }
        return loaded[code];
    }

    /**
     * @param {HTMLSelectElement} countrySelect
     * @param {HTMLSelectElement} citySelect  starts with just its "Select a city" option
     * @param {HTMLInputElement}  otherInput  text box (no name) shown when "Other" is chosen
     * @param {(country: string, city: string) => void} [onChange] called after the customer picks or types a place
     */
    function bind(countrySelect, citySelect, otherInput, onChange) {
        let timer;
        const notify     = () => { if (onChange) onChange(countrySelect.value, citySelect.value.trim()); };
        const notifySoon = () => { clearTimeout(timer); timer = setTimeout(notify, 350); };

        const otherOption = () => citySelect.querySelector('option[data-other]');
        const showOther   = (show) => { otherInput.style.display = show ? '' : 'none'; if (!show) otherInput.value = ''; };

        /* Select a city by name, falling back to "Other" (with the name typed in) when the list doesn't have it. */
        function selectCity(city) {
            const wanted = (city || '').trim().toLowerCase();
            const match  = wanted && Array.from(citySelect.options).find(o => !o.hasAttribute('data-other') && o.value.toLowerCase() === wanted);

            if (match) {
                match.selected = true;
                showOther(false);
            } else if (wanted && otherOption()) {
                otherOption().selected = true;
                otherOption().value    = city.trim();
                otherInput.value       = city.trim();
                showOther(true);
            } else {
                citySelect.selectedIndex = 0;
                showOther(false);
            }
        }

        /* Fill the dropdown with the cities of whichever country is selected now. */
        async function refresh(preferredCity) {
            const chosen = countrySelect.selectedOptions[0];
            const code   = chosen && chosen.dataset.code;

            citySelect.replaceChildren(new Option(code ? 'Loading cities…' : 'Select a country first', ''));
            showOther(false);
            if (!code) return;

            const names = await cities(code);
            if (countrySelect.selectedOptions[0] !== chosen) return;   // they picked another country meanwhile

            const fragment = document.createDocumentFragment();
            fragment.appendChild(new Option('Select a city', ''));
            names.forEach(name => fragment.appendChild(new Option(name)));
            const other = new Option('Other (type your city)…', '');
            other.setAttribute('data-other', '');
            fragment.appendChild(other);
            citySelect.replaceChildren(fragment);

            selectCity(preferredCity);

            // nothing to pick from (list unavailable): go straight to typing
            if (!names.length && !preferredCity) { other.selected = true; showOther(true); }
        }

        countrySelect.addEventListener('change', () => { refresh(); notify(); });

        citySelect.addEventListener('change', () => {
            const other = otherOption();
            const isOther = other && citySelect.selectedOptions[0] === other;
            showOther(isOther);
            if (isOther) { other.value = ''; otherInput.focus(); }
            notify();
        });

        otherInput.addEventListener('input', () => {
            if (otherOption()) otherOption().value = otherInput.value.trim();
            notifySoon();
        });

        refresh();

        return {
            refresh,
            /* Show a saved place, e.g. when editing an address. */
            async setLocation(country, city) {
                const wanted = (country || '').trim().toLowerCase();
                const match  = Array.from(countrySelect.options).find(o => o.value && o.value.toLowerCase() === wanted);

                countrySelect.value = match ? match.value : '';
                await refresh(city);
                notify();
            },
        };
    }

    return { bind };
})();
</script>
<?php endif; ?>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/components/location-picker-js.blade.php ENDPATH**/ ?>