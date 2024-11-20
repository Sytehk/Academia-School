{{--
    System-wide class → section → group cascading dropdowns.

    Auto-binds every form (or card-body) that contains select[name="class_id"]
    together with select[name="section_id"] and optional select[name="group_id"].

    Also exposes window.getData(classId) for legacy onChange="getData(this.value)" handlers.
--}}
<script>
    (function () {
        if (window.__classSectionCascadeInitialized) {
            return;
        }
        window.__classSectionCascadeInitialized = true;

        var sectionUrl = @json(url('sections-section-group-wise'));
        var groupUrl = @json(url('groups-class-section-wise'));
        var csrf = @json(csrf_token());
        var selectClassFirst = @json(_lang('Select Class First'));
        var selectSectionFirst = @json(_lang('Select Section First'));
        var unableSections = @json(_lang('Unable to load sections'));
        var unableGroups = @json(_lang('Unable to load groups'));
        var lastClassSelect = null;

        function containerFor(select) {
            return select.closest('form')
                || select.closest('.card-body')
                || select.closest('.modal-body')
                || select.closest('.row')
                || document;
        }

        function sectionSelectFor(classSelect) {
            if (!classSelect) {
                return null;
            }

            var container = containerFor(classSelect);

            if (classSelect.id === 'class_id_select') {
                var byId = container.querySelector('#section_id_select');
                if (byId) {
                    return byId;
                }
            }

            if (classSelect.name === 'class') {
                return container.querySelector('select[name="section"]')
                    || container.querySelector('#section');
            }

            return container.querySelector('select[name="section_id"]');
        }

        function groupSelectFor(classSelect) {
            if (!classSelect) {
                return null;
            }

            var container = containerFor(classSelect);

            if (classSelect.id === 'class_id_select') {
                var byId = container.querySelector('#group_id_select');
                if (byId) {
                    return byId;
                }
            }

            return container.querySelector('select[name="group_id"]');
        }

        function refreshSelect2(select) {
            if (!select || !window.jQuery) {
                return;
            }

            var $select = window.jQuery(select);
            if ($select.data('select2')) {
                $select.trigger('change.select2');
            }
        }

        function selectedSectionId(sectionSelect) {
            if (!sectionSelect) {
                return '';
            }

            return sectionSelect.dataset.selected
                || sectionSelect.getAttribute('data-selected')
                || sectionSelect.value
                || '';
        }

        function selectedGroupId(groupSelect) {
            if (!groupSelect) {
                return '';
            }

            return groupSelect.dataset.selected
                || groupSelect.getAttribute('data-selected')
                || groupSelect.value
                || '';
        }

        function setLoading(select, loading) {
            if (select) {
                select.disabled = !!loading;
            }
        }

        function loadGroups(classSelect, sectionId, selectedGroup, callback) {
            var groupSelect = groupSelectFor(classSelect);

            if (!groupSelect) {
                if (typeof callback === 'function') {
                    callback();
                }
                return;
            }

            var classId = classSelect ? classSelect.value : '';

            if (!classId) {
                groupSelect.innerHTML = '<option value="">' + selectClassFirst + '</option>';
                refreshSelect2(groupSelect);
                if (typeof callback === 'function') {
                    callback();
                }
                return;
            }

            setLoading(groupSelect, true);

            var params = new URLSearchParams({
                class_id: classId,
                section_id: sectionId || '',
                selected_group_id: selectedGroup || ''
            });

            fetch(groupUrl + '?' + params.toString(), {
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) { return response.text(); })
                .then(function (html) {
                    groupSelect.innerHTML = html;
                    setLoading(groupSelect, false);
                    refreshSelect2(groupSelect);
                    if (typeof callback === 'function') {
                        callback();
                    }
                })
                .catch(function () {
                    groupSelect.innerHTML = '<option value="">' + unableGroups + '</option>';
                    setLoading(groupSelect, false);
                    refreshSelect2(groupSelect);
                });
        }

        function loadSections(classSelect, selectedSection, callback) {
            var sectionSelect = sectionSelectFor(classSelect);
            var groupSelect = groupSelectFor(classSelect);
            var classId = classSelect ? classSelect.value : '';

            if (!sectionSelect) {
                if (typeof callback === 'function') {
                    callback();
                }
                return;
            }

            if (!classId) {
                sectionSelect.innerHTML = '<option value="">' + selectClassFirst + '</option>';
                if (groupSelect) {
                    groupSelect.innerHTML = '<option value="">' + selectSectionFirst + '</option>';
                }
                refreshSelect2(sectionSelect);
                refreshSelect2(groupSelect);
                if (typeof callback === 'function') {
                    callback();
                }
                return;
            }

            setLoading(sectionSelect, true);

            fetch(sectionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    class_id: classId,
                    selected_section_id: selectedSection || ''
                })
            })
                .then(function (response) { return response.text(); })
                .then(function (html) {
                    sectionSelect.innerHTML = html;
                    setLoading(sectionSelect, false);
                    refreshSelect2(sectionSelect);

                    if (groupSelect) {
                        loadGroups(classSelect, sectionSelect.value, selectedGroupId(groupSelect), callback);
                    } else if (typeof callback === 'function') {
                        callback();
                    }
                })
                .catch(function () {
                    sectionSelect.innerHTML = '<option value="">' + unableSections + '</option>';
                    setLoading(sectionSelect, false);
                    refreshSelect2(sectionSelect);
                });
        }

        function bindClassSelect(classSelect) {
            if (!classSelect || classSelect.dataset.sectionCascadeBound === '1') {
                return;
            }

            if (classSelect.closest('#kt_app_header') || classSelect.classList.contains('select_class')) {
                return;
            }

            if (classSelect.dataset.noSectionCascade === '1') {
                return;
            }

            classSelect.dataset.sectionCascadeBound = '1';

            var sectionSelect = sectionSelectFor(classSelect);
            var groupSelect = groupSelectFor(classSelect);

            if (classSelect.getAttribute('onchange')) {
                if (classSelect.value) {
                    loadSections(classSelect, selectedSectionId(sectionSelect));
                } else if (sectionSelect) {
                    sectionSelect.innerHTML = '<option value="">' + selectClassFirst + '</option>';
                    if (groupSelect) {
                        groupSelect.innerHTML = '<option value="">' + selectSectionFirst + '</option>';
                    }
                    refreshSelect2(sectionSelect);
                    refreshSelect2(groupSelect);
                }

                return;
            }

            classSelect.addEventListener('change', function () {
                lastClassSelect = classSelect;
                loadSections(classSelect, '');
            });

            if (sectionSelect && groupSelect && sectionSelect.dataset.sectionCascadeBound !== '1') {
                sectionSelect.dataset.sectionCascadeBound = '1';
                sectionSelect.addEventListener('change', function () {
                    loadGroups(classSelect, sectionSelect.value, '');
                });
            }

            if (classSelect.value) {
                loadSections(classSelect, selectedSectionId(sectionSelect));
            } else if (sectionSelect) {
                sectionSelect.innerHTML = '<option value="">' + selectClassFirst + '</option>';
                if (groupSelect) {
                    groupSelect.innerHTML = '<option value="">' + selectSectionFirst + '</option>';
                }
                refreshSelect2(sectionSelect);
                refreshSelect2(groupSelect);
            }
        }

        function initAll() {
            document.querySelectorAll('select[name="class_id"], select[name="class"]').forEach(bindClassSelect);
        }

        window.getData = function (classId) {
            var classSelect = lastClassSelect;

            if (!classSelect) {
                classSelect = document.querySelector('select[name="class_id"]');
            }

            if (classSelect && classId !== undefined && classId !== null && classId !== '') {
                classSelect.value = classId;
            }

            loadSections(classSelect, '');
        };

        window.loadClassSections = function (classSelect, selectedSection, callback) {
            if (typeof classSelect === 'string' || typeof classSelect === 'number') {
                loadSections(document.getElementById('class_id_select'), classSelect, callback);
                return;
            }

            loadSections(classSelect, selectedSection || '', callback);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAll);
        } else {
            initAll();
        }

        document.addEventListener('change', function (event) {
            if (event.target && event.target.name === 'class_id') {
                lastClassSelect = event.target;
            }
        });
    })();
</script>
