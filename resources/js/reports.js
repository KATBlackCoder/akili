/**
 * Alpine.js component for Type 1 (daily) report.
 * Priority: localStorage > server draft.
 */
function reportType1({ formId, formAssignmentId, userId, initialDraft }) {
    return {
        rowCount: 0,
        draftKey: `report_type1_${formId}_${userId}_${formAssignmentId}`,

        init() {
            const local = localStorage.getItem(this.draftKey);
            if (local) {
                try {
                    this.restoreFromData(JSON.parse(local));
                } catch {
                    localStorage.removeItem(this.draftKey);
                }
            } else if (initialDraft && Object.keys(initialDraft).length > 0) {
                this.restoreFromData(initialDraft);
                localStorage.setItem(this.draftKey, JSON.stringify(initialDraft));
            }
        },

        saveToLocalStorage() {
            const data = this.getDraftData();
            localStorage.setItem(this.draftKey, JSON.stringify(data));
        },

        getDraftData() {
            const rows = document.querySelectorAll('[data-row-index]');
            const data = {};
            rows.forEach((row) => {
                const idx = row.dataset.rowIndex;
                data[idx] = {};
                row.querySelectorAll('input, select, textarea').forEach((input) => {
                    if (input.name) {
                        data[idx][input.name] = input.value;
                    }
                });
            });
            return data;
        },

        restoreFromData(data) {
            this.$nextTick(() => {
                Object.entries(data).forEach(([rowIdx, fields]) => {
                    Object.entries(fields).forEach(([name, value]) => {
                        const input = document.querySelector(
                            `[data-row-index="${rowIdx}"] [name="${name}"]`
                        );
                        if (input) {
                            input.value = value;
                        }
                    });
                });
            });
        },

        clearLocalStorage() {
            localStorage.removeItem(this.draftKey);
        },

        confirmSubmit() {
            this.buildHiddenInputs();
            document.getElementById('confirm-submit-modal').showModal();
        },

        buildHiddenInputs() {
            const container = document.getElementById('hidden-rows-container');
            if (!container) return;

            container.innerHTML = '';
            const data = this.getDraftData();

            Object.entries(data).forEach(([rowIdx, fields]) => {
                Object.entries(fields).forEach(([name, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    container.appendChild(input);
                });
            });
        },
    };
}

window.reportType1 = reportType1;
