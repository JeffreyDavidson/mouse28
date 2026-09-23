const accessibleSelectSelector = '[data-mouse28-accessible-select]';
const observedSelects = new WeakSet();
const observedDropdowns = new WeakSet();

function repairSelectListbox(select) {
    if (!select.isConnected) {
        return;
    }

    const dropdown = select.querySelector('.fi-select-input-ctn > .fi-dropdown-panel');
    const combobox = select.querySelector('.fi-select-input-btn[role="combobox"]');

    if (!dropdown || !combobox) {
        return;
    }

    const listbox = dropdown.querySelector(':scope > ul.fi-dropdown-list');
    const isMultiSelectable = dropdown.getAttribute('aria-multiselectable');

    dropdown.removeAttribute('role');
    dropdown.removeAttribute('aria-multiselectable');

    if (!listbox) {
        combobox.removeAttribute('aria-controls');

        return;
    }

    listbox.id = `${dropdown.id}-options`;
    listbox.setAttribute('role', 'listbox');

    if (isMultiSelectable) {
        listbox.setAttribute('aria-multiselectable', isMultiSelectable);
    }

    combobox.setAttribute('aria-controls', listbox.id);

    if (observedDropdowns.has(dropdown)) {
        return;
    }

    let synchronizedActiveOptionId = null;

    const activeOptionObserver = new MutationObserver(() => {
        if (!dropdown.isConnected) {
            activeOptionObserver.disconnect();

            return;
        }

        const activeOptionId = dropdown.getAttribute('aria-activedescendant');

        if (activeOptionId) {
            listbox.setAttribute('aria-activedescendant', activeOptionId);
            synchronizedActiveOptionId = activeOptionId;
            dropdown.removeAttribute('aria-activedescendant');

            return;
        }

        if (listbox.getAttribute('aria-activedescendant') === synchronizedActiveOptionId) {
            synchronizedActiveOptionId = null;

            return;
        }

        listbox.removeAttribute('aria-activedescendant');
    });

    activeOptionObserver.observe(dropdown, {
        attributes: true,
        attributeFilter: ['aria-activedescendant'],
    });

    observedDropdowns.add(dropdown);
}

function observeSelect(select) {
    if (observedSelects.has(select)) {
        return;
    }

    const observer = new MutationObserver(() => {
        if (!select.isConnected) {
            observer.disconnect();

            return;
        }

        repairSelectListbox(select);
    });

    observer.observe(select, { childList: true, subtree: true });
    observedSelects.add(select);
    repairSelectListbox(select);
}

function observeAddedSelects(records) {
    records.forEach((record) => {
        record.addedNodes.forEach((node) => {
            if (!(node instanceof Element)) {
                return;
            }

            if (node.matches(accessibleSelectSelector)) {
                observeSelect(node);
            }

            node.querySelectorAll(accessibleSelectSelector).forEach(observeSelect);
        });
    });
}

if (!window.mouse28SelectAccessibilityLoaded) {
    window.mouse28SelectAccessibilityLoaded = true;
    document.querySelectorAll(accessibleSelectSelector).forEach(observeSelect);

    new MutationObserver(observeAddedSelects).observe(document.body, {
        childList: true,
        subtree: true,
    });

    document.addEventListener('livewire:navigated', () => {
        document.querySelectorAll(accessibleSelectSelector).forEach(observeSelect);
    });
}
