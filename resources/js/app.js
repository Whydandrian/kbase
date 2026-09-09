/**
 * IT Knowledge Base — client interactivity.
 *   - global search (Ctrl / Cmd + K) across domains, documents, and theses
 *   - domain detail: category filter, live search, document modal
 *   - mobile navigation drawer
 *   - archive: thesis form toggle
 */
document.addEventListener('DOMContentLoaded', () => {
    const app = document.getElementById('app');
    if (!app) {
        return;
    }

    const isMac = /Mac|iPhone|iPad|iPod/i.test(navigator.platform || navigator.userAgent);

    /* ---------- Global search (Ctrl / Cmd + K) ---------- */
    const searchModal = app.querySelector('[data-search-modal]');
    const searchPanel = app.querySelector('[data-search-panel]');
    const searchInput = app.querySelector('[data-search-input]');
    const searchResults = app.querySelector('[data-search-results]');
    const searchIndexEl = app.querySelector('[data-search-index]');

    let searchIndex = [];
    try {
        searchIndex = JSON.parse(searchIndexEl?.textContent || '[]');
    } catch {
        searchIndex = [];
    }

    // Localise the keyboard hint (⌘ K on mac, Ctrl K elsewhere).
    app.querySelectorAll('[data-search-hint]').forEach((el) => {
        el.textContent = isMac ? '⌘ K' : 'Ctrl K';
    });

    const openSearch = () => {
        if (!searchModal) {
            return;
        }
        searchModal.hidden = false;
        searchInput.value = '';
        renderSearch('');
        window.requestAnimationFrame(() => searchInput?.focus());
    };
    const closeSearch = () => {
        if (searchModal) {
            searchModal.hidden = true;
        }
    };

    const escapeHtml = (value) =>
        String(value).replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));

    const renderSearch = (query) => {
        if (!searchResults) {
            return;
        }
        const q = query.trim().toLowerCase();
        if (!q) {
            searchResults.innerHTML = '<p class="search-panel-hint">Ketik untuk mulai mencari.</p>';
            return;
        }

        const matches = searchIndex
            .filter((item) =>
                item.label.toLowerCase().includes(q) || item.meta.toLowerCase().includes(q))
            .slice(0, 20);

        if (matches.length === 0) {
            searchResults.innerHTML = '<p class="search-panel-hint">Tidak ada hasil untuk "' + escapeHtml(query) + '".</p>';
            return;
        }

        searchResults.innerHTML = matches
            .map((item) =>
                '<a class="search-result" href="' + escapeHtml(item.url) + '">' +
                    '<span class="search-result-group">' + escapeHtml(item.group) + '</span>' +
                    '<span class="search-result-label">' + escapeHtml(item.label) + '</span>' +
                    '<span class="search-result-meta">' + escapeHtml(item.meta) + '</span>' +
                '</a>')
            .join('');
    };

    app.querySelectorAll('[data-search-open]').forEach((el) => el.addEventListener('click', openSearch));
    searchInput?.addEventListener('input', (event) => renderSearch(event.target.value));
    searchModal?.addEventListener('click', (event) => {
        if (event.target === searchModal) {
            closeSearch();
        }
    });
    searchPanel?.addEventListener('click', (event) => event.stopPropagation());

    document.addEventListener('keydown', (event) => {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            searchModal?.hidden ? openSearch() : closeSearch();
        }
        if (event.key === 'Escape') {
            closeSearch();
            closeModal();
            closeNav();
            app.querySelectorAll('.nav-dropdown.is-open').forEach((d) => {
                d.classList.remove('is-open');
                d.querySelector('[data-dropdown-toggle]')?.setAttribute('aria-expanded', 'false');
            });
        }
    });

    /* ---------- Mobile navigation (domain detail) ---------- */
    const sidebar = app.querySelector('[data-sidebar]');
    const sidebarOverlay = app.querySelector('.sidebar-overlay');
    const openNav = () => {
        sidebar?.classList.add('is-open');
        if (sidebarOverlay) {
            sidebarOverlay.hidden = false;
        }
    };
    const closeNav = () => {
        sidebar?.classList.remove('is-open');
        if (sidebarOverlay) {
            sidebarOverlay.hidden = true;
        }
    };
    app.querySelectorAll('[data-nav-open]').forEach((el) => el.addEventListener('click', openNav));
    app.querySelectorAll('[data-nav-close]').forEach((el) => el.addEventListener('click', closeNav));

    /* ---------- Category filter + document search ---------- */
    const docSearch = app.querySelector('[data-search]');
    const documentRows = Array.from(app.querySelectorAll('[data-document]'));
    const emptyState = app.querySelector('[data-empty-state]');
    const navItems = Array.from(app.querySelectorAll('.nav-item[data-filter]'));
    const categoryBlocks = Array.from(app.querySelectorAll('[data-category-block]'));
    let activeFilter = 'all';

    const applyFilter = () => {
        const query = (docSearch?.value || '').trim().toLowerCase();

        documentRows.forEach((row) => {
            const matchesFilter = activeFilter === 'all' || row.dataset.category === activeFilter;
            const matchesSearch = !query || row.dataset.haystack.includes(query);
            row.hidden = !(matchesFilter && matchesSearch);
        });

        let anyVisible = false;
        categoryBlocks.forEach((block) => {
            const rows = Array.from(block.querySelectorAll('[data-document]'));
            const visible = rows.some((row) => !row.hidden);
            block.hidden = !visible;
            if (visible) {
                anyVisible = true;
            }
        });

        if (emptyState) {
            emptyState.hidden = anyVisible || documentRows.length === 0;
        }
    };

    navItems.forEach((el) => {
        el.addEventListener('click', () => {
            activeFilter = el.dataset.filter;
            navItems.forEach((item) => item.classList.toggle('active', item === el));
            applyFilter();
            closeNav();
        });
    });
    docSearch?.addEventListener('input', applyFilter);

    /* ---------- Document modal ---------- */
    const modal = app.querySelector('[data-modal]');
    const modalCard = app.querySelector('[data-modal-card]');
    const modalIcon = app.querySelector('[data-modal-icon]');
    const iconTemplates = document.getElementById('icon-templates');

    const setText = (selector, value) => {
        const el = app.querySelector(selector);
        if (el) {
            el.textContent = value || '';
        }
    };

    const modalPdf = app.querySelector('[data-modal-pdf]');
    const modalIframe = app.querySelector('[data-modal-iframe]');
    const modalDownload = app.querySelector('[data-modal-download]');
    const modalUrl = app.querySelector('[data-modal-url]');

    const openModal = (data) => {
        if (!modal) {
            return;
        }
        setText('[data-modal-category]', data.category);
        setText('[data-modal-title]', data.title);
        setText('[data-modal-description]', data.description);
        setText('[data-modal-owner]', data.owner);

        // Reset the two source presentations.
        if (modalPdf) {
            modalPdf.hidden = true;
        }
        if (modalIframe) {
            modalIframe.src = 'about:blank';
        }
        if (modalUrl) {
            modalUrl.hidden = true;
        }

        if (data.mode === 'pdf' && data.file) {
            if (modalIframe) {
                modalIframe.src = data.file;
            }
            if (modalDownload) {
                modalDownload.href = data.file;
            }
            if (modalPdf) {
                modalPdf.hidden = false;
            }
        } else if (data.mode === 'url' && data.url && data.url !== '#') {
            if (modalUrl) {
                modalUrl.href = data.url;
                modalUrl.hidden = false;
            }
        }

        if (modalIcon) {
            modalIcon.innerHTML = '';
            const tpl = iconTemplates?.querySelector(`[data-icon-template="${data.icon}"]`);
            if (tpl) {
                modalIcon.appendChild(tpl.content.cloneNode(true));
            }
        }

        modal.hidden = false;
    };

    const closeModal = () => {
        if (modal) {
            modal.hidden = true;
        }
        if (modalIframe) {
            modalIframe.src = 'about:blank';
        }
    };

    app.querySelectorAll('[data-doc-open]').forEach((el) => {
        el.addEventListener('click', () => {
            const row = el.closest('[data-document]');
            if (!row) {
                return;
            }
            openModal({
                title: row.dataset.title,
                description: row.dataset.description,
                category: row.dataset.docCategory,
                owner: row.dataset.owner,
                icon: row.dataset.icon,
                mode: row.dataset.mode,
                url: row.dataset.url,
                file: row.dataset.file,
            });
        });
    });

    app.querySelectorAll('[data-modal-close]').forEach((el) => el.addEventListener('click', closeModal));
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });
    modalCard?.addEventListener('click', (event) => event.stopPropagation());

    /* ---------- Admin: document add/edit form modal ---------- */
    const docFormModal = app.querySelector('[data-doc-form-modal]');
    const docForm = app.querySelector('[data-doc-form]');
    const openDocForm = () => {
        if (docFormModal) {
            docFormModal.hidden = false;
        }
    };
    const closeDocForm = () => {
        if (docFormModal) {
            docFormModal.hidden = true;
        }
    };

    const setFormField = (selector, value) => {
        const el = docForm?.querySelector(selector);
        if (el) {
            el.value = value ?? '';
        }
    };

    app.querySelectorAll('[data-doc-add]').forEach((el) => {
        el.addEventListener('click', () => {
            if (!docForm) {
                return;
            }
            docForm.action = el.dataset.action;
            docForm.querySelector('[data-form-method]').value = 'POST';
            app.querySelector('[data-form-heading]').textContent = 'Tambah Dokumen';
            setFormField('[data-form-title]', '');
            setFormField('[data-form-description]', '');
            setFormField('[data-form-doc-category]', '');
            setFormField('[data-form-owner]', '');
            setFormField('[data-form-url]', '');
            const active = docForm.querySelector('[data-form-active]');
            if (active) {
                active.checked = true;
            }
            const note = docForm.querySelector('[data-form-file-note]');
            if (note) {
                note.hidden = true;
            }
            openDocForm();
        });
    });

    app.querySelectorAll('[data-doc-edit]').forEach((el) => {
        el.addEventListener('click', () => {
            if (!docForm) {
                return;
            }
            docForm.action = el.dataset.action;
            docForm.querySelector('[data-form-method]').value = 'PUT';
            app.querySelector('[data-form-heading]').textContent = 'Edit Dokumen';
            setFormField('[data-form-title]', el.dataset.title);
            setFormField('[data-form-description]', el.dataset.description);
            setFormField('[data-form-doc-category]', el.dataset.docCategory);
            setFormField('[data-form-owner]', el.dataset.owner);
            setFormField('[data-form-url]', el.dataset.url);
            const active = docForm.querySelector('[data-form-active]');
            if (active) {
                active.checked = el.dataset.active === '1';
            }
            const note = docForm.querySelector('[data-form-file-note]');
            if (note) {
                note.hidden = el.dataset.hasFile !== '1';
            }
            openDocForm();
        });
    });

    app.querySelectorAll('[data-doc-form-close]').forEach((el) => el.addEventListener('click', closeDocForm));
    docFormModal?.addEventListener('click', (event) => {
        if (event.target === docFormModal) {
            closeDocForm();
        }
    });

    // Re-open the form automatically when the server returned validation errors.
    if (docFormModal && docFormModal.dataset.hasErrors === '1') {
        openDocForm();
    }

    /* ---------- Nav dropdown toggle ---------- */
    app.querySelectorAll('[data-dropdown-toggle]').forEach((trigger) => {
        const dropdown = trigger.closest('.nav-dropdown');
        if (!dropdown) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = dropdown.classList.contains('is-open');
            // Close all others first.
            app.querySelectorAll('.nav-dropdown.is-open').forEach((d) => d.classList.remove('is-open'));
            if (!isOpen) {
                dropdown.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
            } else {
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    });

    document.addEventListener('click', () => {
        app.querySelectorAll('.nav-dropdown.is-open').forEach((d) => {
            d.classList.remove('is-open');
            d.querySelector('[data-dropdown-toggle]')?.setAttribute('aria-expanded', 'false');
        });
    });

    /* ---------- Archive: thesis form toggle ---------- */
    const thesisForm = app.querySelector('[data-thesis-form]');
    app.querySelectorAll('[data-thesis-form-toggle]').forEach((el) => {
        el.addEventListener('click', () => {
            if (thesisForm) {
                thesisForm.hidden = !thesisForm.hidden;
                if (!thesisForm.hidden) {
                    thesisForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });

    /* ---------- Admin: user form toggle ---------- */
    const userForm = app.querySelector('[data-user-form]');
    app.querySelectorAll('[data-user-form-toggle]').forEach((el) => {
        el.addEventListener('click', () => {
            if (userForm) {
                userForm.hidden = !userForm.hidden;
                if (!userForm.hidden) {
                    userForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });

    /* ---------- SOP form: repeatable rows (items & flow steps) ---------- */
    let repeatCounter = Date.now();

    const bindRemove = (row) => {
        row.querySelector('[data-repeat-remove]')?.addEventListener('click', () => row.remove());
    };

    // Wire up rows that were rendered server-side.
    app.querySelectorAll('.repeat-row').forEach(bindRemove);

    app.querySelectorAll('[data-repeat-add]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const kind = btn.dataset.repeatAdd; // 'item' | 'step'
            const type = btn.dataset.type || '';
            const tpl = document.getElementById(kind === 'item' ? 'tpl-item-row' : 'tpl-step-row');
            if (!tpl) {
                return;
            }

            const key = (kind === 'item' ? `${type}_` : '') + repeatCounter++;
            const fragment = tpl.content.cloneNode(true);

            // Convert data-name -> name with the unique key (and type) substituted.
            fragment.querySelectorAll('[data-name]').forEach((input) => {
                input.setAttribute('name', input.dataset.name.replace('__KEY__', key).replace('__TYPE__', type));
                if (input.value) {
                    input.value = input.value.replace('__TYPE__', type);
                }
                input.removeAttribute('data-name');
            });

            const list = btn.parentElement.querySelector('[data-repeat]');
            const row = fragment.firstElementChild;
            list.appendChild(fragment);
            bindRemove(list.lastElementChild);
        });
    });

    applyFilter();
});
