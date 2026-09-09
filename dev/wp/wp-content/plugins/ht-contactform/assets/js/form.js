'use strict';

const { __ } = wp.i18n;

/**
 * File type configurations for form uploads
 */
const HTFORM_FILE_TYPES = {
    image: ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'],
    audio: ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/oga', 'audio/wma', 'audio/mka', 'audio/m4a', 'audio/ra', 'audio/mid', 'audio/midi'],
    video: ['video/mp4', 'video/mpeg', 'video/ogg', 'video/avi', 'video/divx', 'video/flv', 'video/mov', 'video/ogv', 'video/mkv', 'video/m4v', 'video/divx', 'video/mpg', 'video/mpeg', 'video/mpe'],
    pdf: ['application/pdf'],
    doc: ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/plain'],
    zip: ['application/zip', 'application/x-zip-compressed', 'application/x-rar-compressed', 'application/rar', 'application/x-7z-compressed', 'application/7z', 'application/gzip', 'application/x-gzip'],
    exe: ['application/exe', 'application/x-exe'],
    csv: ['text/csv']
};

/**
 * Configuration constants
 */
const HTFORM_CONFIG = {
    FORM_SELECTOR: '.ht-form',
    FIELD_SELECTOR: 'input, select, textarea',
    ERROR_CLASS: 'error',
    MESSAGE_DISPLAY_TIME: 5000,
    SCROLL_BEHAVIOR: { behavior: 'smooth', block: 'center' }
};

/**
 * Utility functions module
 */
const HTFormUtils = {
    /**
     * Remove URL parameters and update browser history
     * @param {string[]} params - Parameters to remove
     */
    removeUrlParams(params) {
        if (!window.location.search) return;
        
        const urlParams = new URLSearchParams(window.location.search);
        let hasChanges = false;
        
        params.forEach(param => {
            if (urlParams.has(param)) {
                urlParams.delete(param);
                hasChanges = true;
            }
        });
        
        if (hasChanges) {
            const newUrl = urlParams.toString() ? 
                `${window.location.pathname}?${urlParams.toString()}` : 
                window.location.pathname;
            window.history.replaceState({}, '', decodeURIComponent(newUrl));
        }
    },

    /**
     * Scroll element into view with smooth behavior
     * @param {HTMLElement} element - Element to scroll to
     * @param {Object} options - Scroll options
     */
    scrollToElement(element, options = HTFORM_CONFIG.SCROLL_BEHAVIOR) {
        if (element) {
            element.scrollIntoView(options);
        }
    },

    /**
     * Debounce function calls
     * @param {Function} func - Function to debounce
     * @param {number} wait - Wait time in milliseconds
     * @returns {Function} Debounced function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

/**
 * Message handling module
 */
const HTFormMessageHandler = {
    /**
     * Show success message
     * @param {HTMLFormElement} form - Form element
     * @param {string} message - Success message
     */
    showSuccess(form, message) {
        this._showMessage(form, message, 'ht-form-success');
    },

    /**
     * Show error message
     * @param {HTMLFormElement} form - Form element
     * @param {string} message - Error message
     */
    showError(form, message) {
        this._showMessage(form, message, 'ht-form-error');
    },

    /**
     * Show message with specified type
     * @private
     * @param {HTMLFormElement} form - Form element
     * @param {string} message - Message text
     * @param {string} className - CSS class for message type
     */
    _showMessage(form, message, className) {
        let messageContainer = form.querySelector('.ht-form-message');
        
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.className = 'ht-form-message';
            form.prepend(messageContainer);
        }
        
        messageContainer.innerHTML = `<div class="${className}">${message}</div>`;
        messageContainer.style.display = 'block';
        
        HTFormUtils.scrollToElement(messageContainer, { behavior: 'smooth', block: 'start' });
        
        setTimeout(() => {
            HTFormUtils.removeUrlParams(['form_success', 'form_error']);
            messageContainer.style.display = 'none';
        }, HTFORM_CONFIG.MESSAGE_DISPLAY_TIME);
    },

    /**
     * Auto-hide existing messages
     * @param {HTMLFormElement} form - Form element
     */
    autoHideMessages(form) {
        const messages = form.querySelectorAll('.ht-form-message');
        messages.forEach(message => {
            setTimeout(() => {
                HTFormUtils.removeUrlParams(['form_success', 'form_error']);
                message.style.display = 'none';
            }, HTFORM_CONFIG.MESSAGE_DISPLAY_TIME);
        });
    }
};

/**
 * Form validation module
 */
const HTFormValidator = {
    messages: window?.ht_form?.i18n || {},

    /**
     * Validate entire form
     * @param {HTMLFormElement} form - Form to validate
     * @returns {boolean} True if form is valid
     */
    validateForm(form) {
        let isValid = true;
        
        this.clearAllErrors(form);
        
        form.querySelectorAll(HTFORM_CONFIG.FIELD_SELECTOR).forEach(field => {
            if (!this.validateField(field, form)) {
                isValid = false;
            }
        });
        
        this._resetValidationFlags(form);
        return isValid;
    },

    /**
     * Validate single field
     * @param {HTMLElement} field - Field to validate
     * @param {HTMLFormElement} form - Parent form
     * @returns {boolean} True if field is valid
     */
    validateField(field, form) {
        const fieldContainer = field.closest('.ht-form-elem');
        if (!fieldContainer) return true;
        
        const errorElement = fieldContainer.querySelector('.ht-form-elem-error');
        if (!errorElement) return true;
        
        // Handle checkbox/radio groups
        if ((field.type === 'checkbox' || field.type === 'radio' || field.type === 'ratings') && field.hasAttribute('required')) {
            return this._validateCheckboxRadioGroup(field, form, fieldContainer, errorElement);
        }

        // Rich Text required validation - check hidden input value
        if (field.classList.contains('ht-form-elem-richtext-value') && field.hasAttribute('required')) {
            if (!field.value.trim()) {
                this._showFieldError(field, fieldContainer, errorElement, 'required');
                return false;
            }
            return true; // Skip other validations for richtext hidden input
        }

        // Signature required validation - check if signature was drawn
        if (field.classList.contains('ht-form-elem-signature-value') && field.hasAttribute('required')) {
            const container = field.closest('.ht-form-elem-signature');
            const signaturePad = container?._signaturePad;
            if (!signaturePad || signaturePad.isEmpty()) {
                this._showFieldError(field, fieldContainer, errorElement, 'required');
                return false;
            }
            return true; // Skip other validations for signature hidden input
        }

        // Chained Select required validation - validate container level
        if (field.classList.contains('ht-form-elem-chained-select') && field.hasAttribute('data-required')) {
            const selects = field.querySelectorAll('select');
            for (const select of selects) {
                if (!select.value) {
                    this._showFieldError(select, fieldContainer, errorElement, 'required');
                    return false;
                }
            }
        }

        // Required field validation
        if (field.hasAttribute('required') && !field.value.trim()) {
            this._showFieldError(field, fieldContainer, errorElement, 'required');
            return false;
        }

        // Input mask validation
        if (field.getAttribute('data-mask')) {
            const maskFormat = field.getAttribute('data-mask');
            const value = field.value.trim();
            
            if (value && !this._validateMaskedInput(field, maskFormat)) {
                this._showFieldError(field, fieldContainer, errorElement, 'format');
                return false;
            }
        }
        
        // Phone validation
        if (field.type === 'tel' && field.getAttribute('data-validation')) {
            const iti = window.intlTelInput?.getInstance(field);
            if (iti && !iti.isValidNumber()) {
                this._showFieldError(field, fieldContainer, errorElement, 'phone');
                return false;
            }
        }
        
        // Email validation
        if (field.type === 'email' && field.getAttribute('data-email-validation') && field.value.trim()) {
            if (!this._validateEmail(field.value.trim())) {
                this._showFieldError(field, fieldContainer, errorElement, 'email');
                return false;
            }
        }
        
        // URL validation
        if (field.type === 'url' && field.getAttribute('data-validate') && field.value.trim()) {
            if (!this._validateUrl(field.value.trim())) {
                this._showFieldError(field, fieldContainer, errorElement, 'url');
                return false;
            }
        }

        // Number min/max validation
        if (field.type === 'number') {
            const value = parseInt(field.value);
            const min = field.getAttribute('min');
            const max = field.getAttribute('max');
            
            if (min && value < parseInt(min)) {
                this._showFieldError(field, fieldContainer, errorElement, 'min');
                return false;
            }
            
            if (max && value > parseInt(max)) {
                this._showFieldError(field, fieldContainer, errorElement, 'max');
                return false;
            }
        }
        
        return true;
    },

    /**
     * Clear field error state
     * @param {HTMLElement} field - Field to clear
     * @param {HTMLFormElement} form - Parent form
     */
    clearFieldError(field, form) {
        const fieldContainer = field.closest('.ht-form-elem');
        if (!fieldContainer) return;
        
        field.classList.remove(HTFORM_CONFIG.ERROR_CLASS);
        
        // Clear Choices.js error state
        const choicesContainer = fieldContainer.querySelector('.choices');
        if (choicesContainer) {
            choicesContainer.classList.remove(HTFORM_CONFIG.ERROR_CLASS);
        }
        
        const errorElement = fieldContainer.querySelector('.ht-form-elem-error');
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }
    },

    /**
     * Clear all form errors
     * @param {HTMLFormElement} form - Form to clear
     */
    clearAllErrors(form) {
        form.querySelectorAll('.ht-form-elem-error').forEach(errorEl => {
            errorEl.textContent = '';
            errorEl.style.display = 'none';
        });
        
        form.querySelectorAll(`.${HTFORM_CONFIG.ERROR_CLASS}`).forEach(el => {
            el.classList.remove(HTFORM_CONFIG.ERROR_CLASS);
        });
    },

    /**
     * Scroll to first error in form
     * @param {HTMLFormElement} form - Form element
     */
    scrollToFirstError(form) {
        const firstError = form.querySelector(`.${HTFORM_CONFIG.ERROR_CLASS}`);
        if (firstError) {
            const fieldContainer = firstError.closest('.ht-form-elem');
            HTFormUtils.scrollToElement(fieldContainer);
        }
    },

    // Private methods
    _validateCheckboxRadioGroup(field, form, fieldContainer, errorElement) {
        const name = field.getAttribute('name');
        
        if (field.dataset.validationProcessed === 'true') return true;
        field.dataset.validationProcessed = 'true';
        
        const groupInputs = form.querySelectorAll(`input[name="${name}"]`);
        const isAnyChecked = Array.from(groupInputs).some(input => input.checked);
        
        if (!isAnyChecked) {
            groupInputs.forEach(input => input.classList.add(HTFORM_CONFIG.ERROR_CLASS));
            
            const fieldMessage = field.getAttribute('data-required-message') || fieldContainer.getAttribute('data-required-message');
            errorElement.textContent = fieldMessage || this.messages.required;
            errorElement.style.display = 'block';
            
            return false;
        }
        
        return true;
    },

    _showFieldError(field, fieldContainer, errorElement, errorType) {
        field.classList.add(HTFORM_CONFIG.ERROR_CLASS);
        
        if (field.tagName.toLowerCase() === 'select') {
            const choicesContainer = fieldContainer.querySelector('.choices');
            if (choicesContainer) {
                choicesContainer.classList.add(HTFORM_CONFIG.ERROR_CLASS);
            }
        }
        
        const errorMessages = {
            required: field.getAttribute('data-required-message') || this.messages.required,
            email: field.getAttribute('data-email-validation-message') || this.messages.email,
            format: field.getAttribute('data-format-message') || this.messages.input_mask?.replace('{format}', field.getAttribute('data-mask')),
            min: this.messages.minimum_number?.replace('{min}', field.getAttribute('min')),
            max: this.messages.maximum_number?.replace('{max}', field.getAttribute('max')),
            phone: field.getAttribute('data-validation-message') || this.messages.phone,
            url: field.getAttribute('data-validation-message') || this.messages.url
        };
        
        errorElement.textContent = errorMessages[errorType] || 'Validation error';
        errorElement.style.display = 'block';
    },

    _validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    _validateUrl(url) {
        return /^(https?:\/\/)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)$/.test(url);
    },

    _validateMaskedInput(field, maskFormat) {
        const value = field.value.trim();
        if (!value) return true;
        
        if (field.inputmask) {
            return field.inputmask.isComplete();
        }
        
        const patterns = {
            'MM/DD/YYYY': /^(0[1-9]|1[0-2])\/([0-2][0-9]|3[0-1])\/\d{4}$/,
            'HH:MM': /^([0-1][0-9]|2[0-3]):([0-5][0-9])$/,
            '9999 9999 9999 9999': /^\d{4}\s\d{4}\s\d{4}\s\d{4}$/,
            '$999.99': /^\$\d+\.\d{2}$/,
            '(999) 999-9999': /^\(\d{3}\)\s\d{3}-\d{4}$/,
            '999-99-9999': /^\d{3}-\d{2}-\d{4}$/,
            '99999-9999': /^\d{5}-\d{4}$/
        };
        
        return patterns[maskFormat] ? patterns[maskFormat].test(value) : true;
    },

    _resetValidationFlags(form) {
        form.querySelectorAll('input[data-validation-processed]').forEach(field => {
            delete field.dataset.validationProcessed;
        });
    }
};

/**
 * Event handler module
 */
const HTFormEventHandlers = {
    /**
     * Setup field event listeners
     * @param {HTMLFormElement} form - Form element
     */
    setupFieldListeners(form) {
        // Handle Input Fields
        form.querySelectorAll(HTFORM_CONFIG.FIELD_SELECTOR).forEach(field => {
            field.addEventListener('input', () => {
                HTFormValidator.clearFieldError(field, form);
                HTFormConditionalLogic.check(form);
            });
            
            field.addEventListener('change', () => {
                HTFormValidator.clearFieldError(field, form);
                HTFormConditionalLogic.check(form);
                
                if (field.type === 'checkbox' || field.type === 'radio') {
                    this._handleCheckboxRadioChange(field, form);
                }
            });
        });

        // Handle Ratings
        document.querySelectorAll('.ht-form-elem-ratings').forEach(ratingsContainer => {
            ratingsContainer.querySelectorAll('label').forEach(label => {
                label.addEventListener('mouseover', () => {
                    this._handleRatingsHover(label, form);
                });
            });

            ratingsContainer.addEventListener('mouseleave', () => {
                this._resetRatingsToCheckedState(ratingsContainer);
            });
        });

        // Handle Color Picker
        form.querySelectorAll('.ht-form-elem-color-input').forEach(colorInput => {
            const colorValue = colorInput.parentElement.querySelector('.ht-form-elem-color-value');
            if (colorValue) {
                colorInput.addEventListener('input', (e) => {
                    colorValue.textContent = e.target.value.toUpperCase();
                });
            }
        });

        // Handle Rich Text Editor (Quill)
        if (typeof Quill !== 'undefined') {
            form.querySelectorAll('.ht-form-elem-richtext').forEach(container => {
                const editorEl = container.querySelector('.ht-form-elem-richtext-editor');
                const hiddenInput = container.querySelector('.ht-form-elem-richtext-value');
                if (!editorEl || !hiddenInput) return;

                const toolbar = container.dataset.toolbar || 'basic';
                const placeholder = container.dataset.placeholder || '';
                const maxLength = parseInt(container.dataset.maxLength) || 0;

                // Define toolbar configurations
                const toolbarConfigs = {
                    minimal: [['bold', 'italic', 'link']],
                    basic: [
                        [{ 'header': [1, 2, 3, 4, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        ['link'],
                        ['clean']
                    ],
                    full: [
                        [{ 'header': [1, 2, 3, 4, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        [{ 'align': [] }],
                        ['link', 'blockquote', 'code-block'],
                        ['clean']
                    ]
                };

                const quill = new Quill(editorEl, {
                    theme: 'snow',
                    placeholder: placeholder,
                    modules: {
                        toolbar: toolbarConfigs[toolbar] || toolbarConfigs.basic
                    }
                });

                // Store Quill instance on container for later access (e.g., resume)
                container._quill = quill;

                // Sync content to hidden input
                quill.on('text-change', () => {
                    const html = quill.root.innerHTML;
                    // Check if content is empty (various Quill empty states)
                    const isEmptyContent = /^(<p><br\s*\/?><\/p>|<p>\s*<\/p>|<p><\/p>|\s*)$/i.test(html.trim());
                    hiddenInput.value = isEmptyContent ? '' : html;

                    // Max length validation
                    if (maxLength > 0) {
                        const text = quill.getText();
                        if (text.length > maxLength) {
                            quill.deleteText(maxLength, text.length - 1);
                        }
                    }
                });
            });
        }

        // Handle Signature Pad
        if (typeof SignaturePad !== 'undefined') {
            form.querySelectorAll('.ht-form-elem-signature').forEach(container => {
                const canvas = container.querySelector('.ht-form-elem-signature-canvas');
                const hiddenInput = container.querySelector('.ht-form-elem-signature-value');
                const clearBtn = container.querySelector('.ht-form-elem-signature-clear');
                if (!canvas || !hiddenInput) return;

                // Get the form element to read CSS variables from styler
                const formElem = container.closest('.ht-form-elem');
                const computedStyle = formElem ? getComputedStyle(formElem) : null;

                // Check for styler CSS variable first, then fall back to data attribute
                const stylerPenColor = computedStyle ? computedStyle.getPropertyValue('--ht-signature-pen-color').trim() : '';
                const stylerBgColor = computedStyle ? computedStyle.getPropertyValue('--ht-signature-bg-color').trim() : '';

                const penColor = stylerPenColor || container.dataset.penColor || '#000000';
                const backgroundColor = stylerBgColor || container.dataset.backgroundColor || '#ffffff';
                const penWidth = parseFloat(container.dataset.penWidth) || 2;

                const signaturePad = new SignaturePad(canvas, {
                    penColor: penColor,
                    backgroundColor: backgroundColor,
                    minWidth: penWidth * 0.5,
                    maxWidth: penWidth * 1.5,
                });

                // Store SignaturePad instance on container for validation access
                container._signaturePad = signaturePad;

                // Handle canvas resize with cleanup check
                const resizeCanvas = () => {
                    // Check if canvas is still in DOM (prevents memory leak)
                    if (!document.body.contains(canvas)) {
                        window.removeEventListener('resize', resizeCanvas);
                        return;
                    }

                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    const rect = canvas.getBoundingClientRect();
                    canvas.width = rect.width * ratio;
                    canvas.height = rect.height * ratio;
                    canvas.getContext('2d').scale(ratio, ratio);

                    // Fill with background color
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = backgroundColor;
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    // Restore signature if exists
                    if (hiddenInput.value) {
                        try {
                            signaturePad.fromDataURL(hiddenInput.value);
                        } catch (e) {
                            // Handle corrupted signature data
                            hiddenInput.value = '';
                        }
                    }
                };

                resizeCanvas();
                window.addEventListener('resize', resizeCanvas);

                // Sync signature to hidden input
                signaturePad.addEventListener('endStroke', () => {
                    if (!signaturePad.isEmpty()) {
                        hiddenInput.value = signaturePad.toDataURL('image/png');
                    } else {
                        hiddenInput.value = '';
                    }
                });

                // Handle clear button
                if (clearBtn) {
                    clearBtn.addEventListener('click', () => {
                        signaturePad.clear();
                        // Fill with background color after clear
                        const ctx = canvas.getContext('2d');
                        ctx.fillStyle = backgroundColor;
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        hiddenInput.value = '';
                    });
                }
            });
        }

        // Handle Chained Select (supports unlimited levels)
        form.querySelectorAll('.ht-form-elem-chained-select').forEach(container => {
            const dataScript = container.querySelector('.ht-form-chained-data');
            const rawDataScript = container.querySelector('.ht-form-chained-raw-data');
            const levelCount = parseInt(container.dataset.levelCount) || 2;

            if (!dataScript) return;

            let chainedData = [];
            let rawData = [];

            try {
                chainedData = JSON.parse(dataScript.textContent);
                if (rawDataScript) {
                    rawData = JSON.parse(rawDataScript.textContent);
                }
            } catch (e) {
                console.error('Failed to parse chained data:', e);
                return;
            }

            // Get all select elements
            const selects = [];
            for (let i = 1; i <= levelCount; i++) {
                const select = container.querySelector('.ht-form-elem-chained-level-' + i);
                if (select) {
                    selects.push(select);
                }
            }

            if (selects.length < 2) return;

            // Reset a select to its placeholder
            const resetSelect = (select) => {
                const placeholder = select.dataset.placeholder || 'Select...';
                select.innerHTML = '<option value="">' + placeholder + '</option>';
                select.disabled = true;
            };

            // Get unique values for a level based on current selections
            const getOptionsForLevel = (level) => {
                if (level === 1) {
                    // Level 1 options come from hierarchical data
                    return chainedData.map(item => ({ value: item.value, label: item.label }));
                }

                if (level === 2) {
                    // Level 2 options come from hierarchical data children
                    const level1Value = selects[0].value;
                    const parent = chainedData.find(item => item.value === level1Value);
                    if (parent && parent.children) {
                        return parent.children.map(item => ({ value: item.value, label: item.label }));
                    }
                    return [];
                }

                // Level 3+ - filter raw data based on all previous selections
                const filters = {};
                for (let i = 0; i < level - 1; i++) {
                    filters['level_' + (i + 1)] = selects[i].value;
                }

                // Filter raw data and get unique values for this level
                const seen = new Set();
                const options = [];

                rawData.forEach(row => {
                    // Check if row matches all filters
                    let matches = true;
                    for (const [key, value] of Object.entries(filters)) {
                        if (row[key] !== value) {
                            matches = false;
                            break;
                        }
                    }

                    if (matches) {
                        const levelValue = row['level_' + level];
                        if (levelValue && !seen.has(levelValue)) {
                            seen.add(levelValue);
                            options.push({ value: levelValue, label: levelValue });
                        }
                    }
                });

                return options;
            };

            // Handle change event for each level
            selects.forEach((select, index) => {
                const currentLevel = index + 1;

                select.addEventListener('change', () => {
                    const selectedValue = select.value;

                    // Reset all subsequent levels
                    for (let i = currentLevel; i < selects.length; i++) {
                        resetSelect(selects[i]);
                    }

                    // If no value selected, we're done
                    if (!selectedValue) {
                        return;
                    }

                    // Populate next level if it exists
                    if (currentLevel < selects.length) {
                        const nextSelect = selects[currentLevel];
                        const options = getOptionsForLevel(currentLevel + 1);

                        if (options.length > 0) {
                            options.forEach(opt => {
                                const optionEl = document.createElement('option');
                                optionEl.value = opt.value;
                                optionEl.textContent = opt.label;
                                nextSelect.appendChild(optionEl);
                            });
                            nextSelect.disabled = false;
                        }
                    }
                });
            });
        });

        // Handle Dynamic Fields
        document.querySelectorAll('.ht-form-elem-dynamic').forEach(elem => {
            const ref = elem.dataset.ref;
            
            // Check if this is a parent field reference (without brackets)
            if (!ref.includes('[') && !ref.includes(']')) {
                // Look for child fields that start with this parent name
                const childFields = form.querySelectorAll(`[name^="${ref}["]`);
                if (childFields.length > 0) {
                    // For parent fields, we'll combine values of children
                    const updateParentContent = () => {
                        const values = [];
                        childFields.forEach(field => {
                            if ((field.type === 'checkbox' || field.type === 'radio') && !field.checked) {
                                return;
                            }
                            if (field.value.trim()) {
                                values.push(field.value);
                            }
                        });
                        elem.textContent = values.join(' ');
                    };
                    // Initial update
                    updateParentContent();
                    // Add listeners to all child fields
                    ['input', 'change'].forEach(event => {
                        childFields.forEach(field => {
                            field.addEventListener(event, updateParentContent);
                        });
                    });
                    return; // Skip the regular field handling below
                }
            }
            
            // Regular handling for direct field references
            const field = form.querySelectorAll(`[name="${ref}"]`);
            
            if (field.length) {
                const updateContent = (e) => {
                    elem.textContent = e.target.value;
                };
                field.forEach(f => {
                    let value = f.value;
                    if(f.type === 'checkbox' || f.type === 'radio') {
                        if(f.checked) {
                            value = f.value;
                            updateContent({target: {value}});
                            return;
                        }
                        return;
                    }
                    updateContent({target: {value}});
                });
                ['input', 'change'].forEach(event => {
                    field.forEach(f => {
                        f.addEventListener(event, updateContent);
                    });
                });
            }
        });
    },

    /**
     * Setup form submit listener
     * @param {HTMLFormElement} form - Form element
     */
    setupSubmitListener(form) {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            HTFormUtils.removeUrlParams(['form_success', 'form_error']);

            if (form.getAttribute('data-ajax-enabled') === 'true') {
                this._handleAjaxSubmission(form);
            } else {
                this._handleStandardSubmission(form);
            }
        });
    },

    // Private methods
    _handleCheckboxRadioChange(field, form) {

        if(field.closest('.ht-form-elem-ratings')) {
            const field_id = field.id;
            const ratingsContainer = field.closest('.ht-form-elem-ratings');
            const labels = Array.from(ratingsContainer.querySelectorAll('label'));
            
            // Find the index of the clicked label
            const clickedIndex = labels.findIndex(label => label.htmlFor === field_id);
            
            // Add 'active' class to current and all previous labels
            labels.forEach((label, index) => {
                if (index <= clickedIndex) {
                    label.classList.add('active');
                } else {
                    label.classList.remove('active');
                }
            });
        }

        if (field.type === 'checkbox') {
            const container = field.closest('.ht-form-elem-checkbox');
            if (container) {
                container.classList.toggle('checked', field.checked);
            }
        }
        if (field.type === 'radio') {
            const container = field.closest('.ht-form-elem-radio-item');
            if (container) {
                container?.closest('.ht-form-elem-radios')?.querySelectorAll('.ht-form-elem-radio-item')?.forEach(function(item) {
                    item.classList.remove('checked');
                })
                container.classList.add('checked');
            }
        }

        const name = field.getAttribute('name');
        if (!name) return;
        
        const fieldContainer = field.closest('.ht-form-elem');
        if (!fieldContainer) return;
        
        const groupInputs = form.querySelectorAll(`input[name="${name}"]`);
        const isAnyChecked = Array.from(groupInputs).some(input => input.checked);
        
        if (isAnyChecked) {
            groupInputs.forEach(input => input.classList.remove(HTFORM_CONFIG.ERROR_CLASS));
            
            const errorElement = fieldContainer.querySelector('.ht-form-elem-error');
            if (errorElement) {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
            }
        }
    },

    _handleRatingsHover(label, form) {
        const field_id = label.htmlFor;
        const ratingsContainer = label.closest('.ht-form-elem-ratings');
        const labels = Array.from(ratingsContainer.querySelectorAll('label'));
        
        // Find the index of the hovered label
        const hoveredIndex = labels.findIndex(label => label.htmlFor === field_id);
        
        // Add 'active' class to current and all previous labels
        labels.forEach((label, index) => {
            if (index <= hoveredIndex) {
                label.classList.add('active');
            } else {
                label.classList.remove('active');
            }
        });
    },
    
    _resetRatingsToCheckedState(ratingsContainer) {
        const labels = Array.from(ratingsContainer.querySelectorAll('label'));
        const inputs = Array.from(ratingsContainer.querySelectorAll('input[type="radio"]'));
        
        // Find the checked input
        const checkedInput = inputs.find(input => input.checked);
        
        // Reset all labels to inactive first
        labels.forEach(label => {
            label.classList.remove('active');
        });
        
        if (checkedInput) {
            // Find the index of the checked input
            const checkedIndex = labels.findIndex(label => label.htmlFor === checkedInput.id);
            
            // Add 'active' class to checked and all previous labels
            labels.forEach((label, index) => {
                if (index <= checkedIndex) {
                    label.classList.add('active');
                }
            });
        }
    },

    _handleAjaxSubmission(form) {
        HTFormCaptchaHandler.handle(form)
            .then(token => HTFormAjaxSubmitter.submit(form, token))
            .catch(error => HTFormCaptchaHandler.showError(form, error));
    },

    _handleStandardSubmission(form) {
        const submitButton = form.querySelector('[type="submit"]');
        const originalText = submitButton?.innerHTML || '';
        
        if (submitButton) {
            HTFormButtonState.setLoading(submitButton, originalText);
        }
        
        HTFormCaptchaHandler.handle(form)
            .then(() => {
                if (HTFormValidator.validateForm(form)) {
                    const formId = form.querySelector('[name="ht_form_id"]')?.value || form.dataset.formId;

                    // Dispatch custom event for third-party integrations
                    document.dispatchEvent(new CustomEvent('htform:before_submit', {
                        detail: {
                            formId: formId,
                            formElement: form
                        }
                    }));

                    form.submit();
                } else {
                    HTFormValidator.scrollToFirstError(form);
                    if (submitButton) {
                        HTFormButtonState.restore(submitButton, originalText);
                    }
                }
            })
            .catch(error => {
                alert(error.message || __('Captcha verification failed. Please try again.', 'ht-contactform'));
                if (submitButton) {
                    HTFormButtonState.restore(submitButton, originalText);
                }
            });
    }
};

/**
 * Button state management
 */
const HTFormButtonState = {
    /**
     * Set button to loading state
     * @param {HTMLButtonElement} button - Button element
     * @param {string} originalText - Original button text
     */
    setLoading(button, originalText) {
        button.disabled = true;
        button.classList.add('loading');
        button.innerHTML = '<span class="ht-form-loader"></span>' + originalText;
    },

    /**
     * Restore button to normal state
     * @param {HTMLButtonElement} button - Button element
     * @param {string} originalText - Original button text
     */
    restore(button, originalText) {
        button.disabled = false;
        button.classList.remove('loading');
        button.innerHTML = originalText;
    }
};

/**
 * reCAPTCHA handling module
 */
const HTFormRecaptchaHandler = {
    /**
     * Handle reCAPTCHA verification
     * @param {HTMLFormElement} form - Form element
     * @returns {Promise<string|null>} reCAPTCHA token or null
     */
    handle(form) {
        return new Promise((resolve, reject) => {
            const recaptchaField = form.querySelector('input[name="g-recaptcha-response"]');
            if (!recaptchaField) {
                resolve(null);
                return;
            }

            if (!this._isRecaptchaLoaded()) {
                reject({ message: __('reCAPTCHA is not properly configured', 'ht-contactform') });
                return;
            }

            const activeVersion = ht_form?.captcha?.recaptcha_active_version;

            if (activeVersion === 'v3') {
                this._handleV3(recaptchaField, resolve, reject);
            } else if (activeVersion === 'v2') {
                this._handleV2(recaptchaField, resolve, reject);
            } else {
                // reCAPTCHA is disabled or not configured
                reject({ message: __('reCAPTCHA is not properly configured', 'ht-contactform') });
            }
        });
    },

    /**
     * Show reCAPTCHA error
     * @param {HTMLFormElement} form - Form element
     * @param {Object} error - Error object
     */
    showError(form, error) {
        const fieldContainer = form.querySelector('.ht-form-elem-recaptcha-field');
        if (fieldContainer) {
            const errorElement = fieldContainer.querySelector('.ht-form-elem-error');
            if (errorElement) {
                errorElement.textContent = error?.message || __('reCAPTCHA verification failed', 'ht-contactform');
                errorElement.style.display = 'block';
                HTFormUtils.scrollToElement(fieldContainer, { behavior: 'smooth', block: 'start' });
                return;
            }
        }

        // Log warning when falling back to alert (form element not found)
        console.warn('reCAPTCHA error display: form element not found, using alert fallback', error);
        alert(error?.message || __('reCAPTCHA verification failed. Please try again.', 'ht-contactform'));
    },

    // Private methods
    _isRecaptchaLoaded() {
        return typeof grecaptcha !== 'undefined' &&
               typeof grecaptcha.ready === 'function' &&
               typeof grecaptcha.execute === 'function' &&
               typeof grecaptcha.getResponse === 'function';
    },

    _handleV3(recaptchaField, resolve, reject) {
        // Validate site key exists before executing
        if (!ht_form?.captcha?.recaptcha_site_key) {
            reject({ message: __('reCAPTCHA site key is not configured', 'ht-contactform') });
            return;
        }

        try {
            grecaptcha.ready(() => {
                grecaptcha.execute(ht_form.captcha.recaptcha_site_key, { action: 'submit' })
                    .then(token => {
                        recaptchaField.value = token;
                        resolve(token);
                    })
                    .catch(() => {
                        reject({ message: __('reCAPTCHA v3 execution failed', 'ht-contactform') });
                    });
            });
        } catch (error) {
            reject({ message: __('reCAPTCHA v3 is not properly configured', 'ht-contactform') });
        }
    },

    _handleV2(recaptchaField, resolve, reject) {
        const token = grecaptcha.getResponse();
        if (token) {
            recaptchaField.value = token;
            resolve(token);
        } else {
            reject({ message: __('Please complete the reCAPTCHA verification', 'ht-contactform') });
        }
    }
};

/**
 * hCaptcha handling module
 */
const HTFormHcaptchaHandler = {
    /**
     * Handle hCaptcha verification
     * @param {HTMLFormElement} form - Form element
     * @returns {Promise<string|null>} hCaptcha token or null
     */
    handle(form) {
        return new Promise((resolve, reject) => {
            const hcaptchaField = form.querySelector('input[name="h-captcha-response"]');
            if (!hcaptchaField) {
                resolve(null);
                return;
            }

            if (typeof hcaptcha === 'undefined') {
                reject({ message: __('hCaptcha is not properly configured', 'ht-contactform') });
                return;
            }

            const token = hcaptcha.getResponse();
            if (token) {
                hcaptchaField.value = token;
                resolve(token);
            } else {
                reject({ message: __('Please complete the hCaptcha verification', 'ht-contactform') });
            }
        });
    },

    /**
     * Show hCaptcha error
     * @param {HTMLFormElement} form - Form element
     * @param {Object} error - Error object
     */
    showError(form, error) {
        // Reset hCaptcha widget so user can try again
        if (typeof hcaptcha !== 'undefined') {
            try {
                hcaptcha.reset();
            } catch (e) {
                console.warn('Could not reset hCaptcha:', e);
            }
        }

        const fieldContainer = form.querySelector('.ht-form-elem-hcaptcha-field');
        if (fieldContainer) {
            const errorElement = fieldContainer.querySelector('.ht-form-elem-error');
            if (errorElement) {
                errorElement.textContent = error?.message || __('hCaptcha verification failed', 'ht-contactform');
                errorElement.style.display = 'block';
                HTFormUtils.scrollToElement(fieldContainer, { behavior: 'smooth', block: 'start' });
                return;
            }
        }

        alert(error?.message || __('hCaptcha verification failed. Please try again.', 'ht-contactform'));
    }
};

/**
 * Unified captcha handler - delegates to appropriate handler
 */
const HTFormCaptchaHandler = {
    handle(form) {
        // Check for hCaptcha field first
        const hcaptchaField = form.querySelector('input[name="h-captcha-response"]');
        if (hcaptchaField) {
            return HTFormHcaptchaHandler.handle(form);
        }

        // Check for reCAPTCHA field
        const recaptchaField = form.querySelector('input[name="g-recaptcha-response"]');
        if (recaptchaField) {
            return HTFormRecaptchaHandler.handle(form);
        }

        // No captcha present
        return Promise.resolve(null);
    },

    showError(form, error) {
        // Check which captcha is present and show appropriate error
        const hcaptchaField = form.querySelector('input[name="h-captcha-response"]');
        if (hcaptchaField) {
            return HTFormHcaptchaHandler.showError(form, error);
        }

        const recaptchaField = form.querySelector('input[name="g-recaptcha-response"]');
        if (recaptchaField) {
            return HTFormRecaptchaHandler.showError(form, error);
        }

        // Generic error
        alert(error?.message || __('Captcha verification failed. Please try again.', 'ht-contactform'));
    }
};

/**
 * AJAX form submission module
 */
const HTFormAjaxSubmitter = {
    /**
     * Submit form via AJAX
     * @param {HTMLFormElement} form - Form element
     * @param {string|null} recaptchaToken - reCAPTCHA token
     */
    submit(form, recaptchaToken) {
        if (!HTFormValidator.validateForm(form)) {
            HTFormValidator.scrollToFirstError(form);
            return;
        }

        const submitButton = form.querySelector('[type="submit"]');
        const originalText = submitButton?.innerHTML || '';
        
        if (submitButton) {
            HTFormButtonState.setLoading(submitButton, originalText);
        }

        const formData = this._buildFormData(form);

        if (form.id) {
            formData.append('form_id', form.id);
        }

        // Include draft_key if form was resumed (for file upload handling)
        if (form.dataset.draftKey) {
            formData.append('ht_form_draft_key', form.dataset.draftKey);
        }

        axios({
            method: 'post',
            url: `${ht_form.rest_url}ht-form/v1/submission`,
            data: formData,
            headers: { 
                'Content-Type': 'multipart/form-data',
                'X-WP-Nonce': ht_form.rest_nonce || '' 
            },
            withCredentials: true
        })
        .then(response => this._handleSuccess(form, response.data))
        .catch(error => this._handleError(form, error))
        .finally(() => {
            if (submitButton) {
                HTFormButtonState.restore(submitButton, originalText);
            }
        });
    },

    // Private methods
    _buildFormData(form) {
        const formData = new FormData();
        const inputs = form.querySelectorAll('[name]');

        inputs.forEach(input => {
            if (input.type === 'file') {
                if (input.files.length > 0) {
                    for (let i = 0; i < input.files.length; i++) {
                        formData.append(input.name, input.files[i]);
                    }
                }
            } else if (input.type === 'checkbox' || input.type === 'radio') {
                if (input.checked) {
                    formData.append(input.name, input.value);
                }
            } else if (input.tagName === 'SELECT' && input.multiple) {
                Array.from(input.selectedOptions).forEach(option => {
                    formData.append(`${input.name}[]`, option.value);
                });
            } else {
                formData.append(input.name, input.value);
            }
        });

        return formData;
    },

    _handleSuccess(form, data) {
        const formId = form.querySelector('[name="ht_form_id"]')?.value || form.dataset.formId;

        // Dispatch custom event for third-party integrations (Meta Pixel, GTM, etc.)
        document.dispatchEvent(new CustomEvent('htform:submitted', {
            detail: {
                formId: formId,
                formElement: form,
                response: data
            }
        }));

        const confirmation = data.confirmation;
        if (!confirmation) return;

        switch (confirmation.type) {
            case 'message':
                HTFormMessageHandler.showSuccess(form, confirmation.message);
                break;
            case 'redirect':
            case 'page':
                const url = confirmation.redirect || confirmation.page;
                if (confirmation.newTab) {
                    window.open(url, '_blank');
                } else {
                    window.location.href = url;
                }
                break;
        }
    },

    _handleError(form, error) {
        const formId = form.querySelector('[name="ht_form_id"]')?.value || form.dataset.formId;

        // Dispatch custom event for third-party integrations
        document.dispatchEvent(new CustomEvent('htform:error', {
            detail: {
                formId: formId,
                formElement: form,
                error: error.response?.data || error.message
            }
        }));

        let errorMessage = 'Form submission failed. Please try again.';
        
        if (error.response?.data) {
            if (error.response.data.message) {
                errorMessage = error.response.data.message;
            } else if (error.response.data.code === 'submission_too_quick') {
                errorMessage = __('Please wait a moment before submitting the form.', 'ht-contactform');
            }
        }
        
        HTFormMessageHandler.showError(form, errorMessage);
    }
};

/**
 * Conditional logic module
 */
const HTFormConditionalLogic = {
    /**
     * Check conditional logic for all fields
     * @param {HTMLFormElement} form - Form element
     */
    check(form) {
        form.querySelectorAll('.ht-form-elem[data-condition-match][data-condition-logic]').forEach(field => {
            const match = field.getAttribute('data-condition-match');
            const logic = field.getAttribute('data-condition-logic');
            
            try {
                const conditions = JSON.parse(logic);
                let isValid = false;
                
                if (match === 'any') {
                    isValid = conditions?.some(condition => this._evaluateCondition(condition, form));
                } else if (match === 'all') {
                    isValid = conditions?.every(condition => this._evaluateCondition(condition, form));
                }
                
                field.style.display = isValid ? 'block' : 'none';
            } catch (error) {
                console.error('Error parsing conditional logic:', error);
            }
        });
    },

    // Private methods
    _evaluateCondition(condition, form) {
        const { field, operator, value } = condition;
        const fieldElement = form.querySelector(`[name="${field}"]`);
        
        if (!fieldElement) return false;
        
        const fieldValue = fieldElement.value;
        
        const operators = {
            'is': () => fieldValue === value,
            '==': () => fieldValue === value,
            'is_not': () => fieldValue !== value,
            '!=': () => fieldValue !== value,
            'contains': () => fieldValue.includes(value),
            'does_not_contain': () => !fieldValue.includes(value),
            'doNotContains': () => !fieldValue.includes(value),
            'greater_than': () => +fieldValue > +value,
            '>': () => +fieldValue > +value,
            'less_than': () => +fieldValue < +value,
            '<': () => +fieldValue < +value,
            '>=': () => +fieldValue >= +value,
            '<=': () => +fieldValue <= +value,
            'startsWith': () => fieldValue.startsWith(value),
            'endsWith': () => fieldValue.endsWith(value),
            'test_regex': () => {
                try {
                    return new RegExp(value).test(fieldValue);
                } catch (e) {
                    console.error('Invalid regex pattern:', value);
                    return false;
                }
            }
        };
        
        return operators[operator] ? operators[operator]() : false;
    }
};

/**
 * Field components initialization
 */
const HTFormFieldComponents = {
    /**
     * Initialize all field components
     */
    initAll() {
        this.initRangeSliders();
        this.initInputMasks();
        this.initTelFields();
        this.initCountryFields();
        this.initDateTimeFields();
        this.initSelectFields();
        this.initFileUploads();
        this.initRepeaters();
        // Note: initSaveResume() is called separately after HTForm.init()
        // so Quill and SignaturePad are initialized first
    },

    initRangeSliders() {
        document.querySelectorAll('.ht-form-elem-range').forEach(range => {
            range.addEventListener('input', () => {
                const amountEl = range.nextElementSibling?.querySelector('.ht-form-elem-range-amount');
                if (amountEl) {
                    amountEl.textContent = range.value;
                }
            });
        });
    },

    initInputMasks() {
        if (typeof Inputmask !== 'function') {
            return;
        }
        document.querySelectorAll('.ht-form-elem-input-mask[data-mask]').forEach(input => {
            const maskFormat = input.getAttribute('data-mask');
            if (!maskFormat) return;

            const maskOptions = this._getMaskOptions(maskFormat);
            const im = new Inputmask(maskOptions);
            im.mask(input);
        });
    },

    initTelFields() {
        if (typeof window.intlTelInput !== 'function') {
            return;
        }
        document.querySelectorAll('.ht-form-elem-input-tel').forEach(input => {
            const config = this._getTelConfig(input);
            const iti = window.intlTelInput(input, config);
            // Store instance for later access (e.g., resume)
            input._intlTelInput = iti;
            this._setTelFlags(input);
        });
    },

    initCountryFields() {
        if (typeof jQuery?.fn?.countrySelect !== 'function') {
            return;
        }
        document.querySelectorAll('.ht-form-elem-input-country').forEach(input => {
            const config = this._getCountryConfig(input);
            jQuery(input).countrySelect(config);
        });
    },

    initDateTimeFields() {
        if (typeof flatpickr !== 'function') {
            return;
        }
        document.querySelectorAll('.ht-form-elem-datetime').forEach(input => {
            const config = this._getDateTimeConfig(input);
            const fp = flatpickr(input, config);
            // Store instance for later access (e.g., resume)
            input._flatpickr = fp;
        });
    },

    initSelectFields() {
        // Check if Choices is available
        if (typeof Choices === 'undefined') {
            return;
        }

        document.querySelectorAll('[data-ht-select]').forEach(select => {
            // Skip if already initialized
            if (select.choicesInstance) {
                return;
            }
            const config = this._getSelectConfig(select);
            const choicesInstance = new Choices(select, config);
            // Store instance on the element for later access
            select.choicesInstance = choicesInstance;
        });
    },

    initFileUploads() {
        document.querySelectorAll('.ht-form-elem-file-upload, .ht-form-elem-image-upload').forEach(fileUpload => {
            this._initFilePond(fileUpload);
        });
    },

    // Private helper methods
    _getMaskOptions(maskFormat) {
        const formats = {
            'MM/DD/YYYY': { alias: 'datetime', inputFormat: 'MM/DD/YYYY' },
            'HH:MM': { alias: 'datetime', inputFormat: 'HH:mm', placeholder: 'HH:MM' },
            '9999 9999 9999 9999': { mask: '9999 9999 9999 9999' },
            '$999.99': { alias: 'numeric', groupSeparator: '', digits: 2, digitsOptional: false, prefix: '$', rightAlign: false, allowMinus: false },
            '(999) 999-9999': { mask: '(999) 999-9999' },
            '999-99-9999': { mask: '999-99-9999' },
            '99999-9999': { mask: '99999-9999' }
        };
        
        return formats[maskFormat] || { mask: maskFormat };
    },

    _getTelConfig(input) {
        return {
            loadUtils: () => import(`${ht_form.plugin_url}assets/lib/intl-tel-input/utils.min.js`),
            initialCountry: input.getAttribute('data-initial-country'),
            excludeCountries: input.getAttribute('data-exclude-countries')?.split(',') || [],
            onlyCountries: input.getAttribute('data-only-countries')?.split(',') || [],
            hiddenInput: (input) => ({
                phone: 'phone',
            }),
            customPlaceholder: (selectedCountryPlaceholder) => "e.g. " + selectedCountryPlaceholder
        };
    },

    _setTelFlags(input) {
        const iti = input.closest('.ht-form-elem-content')?.querySelector('.iti');
        if (iti) {
            iti.style.cssText = `
                --iti-path-flags-1x: url(${ht_form.plugin_url}assets/images/intl-tel-input/flags.webp);
                --iti-path-flags-2x: url(${ht_form.plugin_url}assets/images/intl-tel-input/flags@2x.webp);
                --iti-path-globe-1x: url(${ht_form.plugin_url}assets/images/intl-tel-input/globe.webp);
                --iti-path-globe-2x: url(${ht_form.plugin_url}assets/images/intl-tel-input/globe@2x.webp);
            `;
        }
    },

    _getCountryConfig(input) {
        return {
            defaultCountry: input.getAttribute('data-initial-country'),
            excludeCountries: input.getAttribute('data-exclude-countries')?.split(',') || [],
            onlyCountries: input.getAttribute('data-only-countries')?.split(',') || [],
            preferredCountries: [],
            responsiveDropdown: true
        };
    },

    _getDateTimeConfig(input) {
        const format = input.getAttribute('data-format') || 'Y-m-d';
        const range = input.getAttribute('data-range') === '1';
        const multiple = input.getAttribute('data-multiple') === '1';
        const enableTime = format?.includes('H') || format?.includes('h');
        const noDate = !format?.includes('Y');

        let mode = 'single';
        if (!enableTime) {
            if (range) mode = 'range';
            if (multiple) mode = 'multiple';
        }

        return {
            enableTime,
            noCalendar: noDate,
            dateFormat: format,
            mode,
            static: true,
            time_24hr: enableTime && format?.includes('H')
        };
    },

    _getSelectConfig(select) {
        return {
            searchEnabled: select.getAttribute('data-searchable') === '1',
            itemSelectText: '',
            maxItemCount: parseInt(select.getAttribute('data-maxselect')) || -1,
            removeItemButton: true,
            placeholder: true,
            placeholderValue: '',
            shouldSort: false
        };
    },

    _initFilePond(fileUpload) {
        const input = fileUpload.querySelector('input[type="file"]');
        const config = this._getFilePondConfig(input);
        
        FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginFileValidateSize, FilePondPluginFileValidateType);
        
        const pond = FilePond.create(input, config);
        fileUpload.filepond = pond;
    },

    _getFilePondConfig(input) {
        const maxFileCount = parseInt(input.getAttribute('data-max-files'), 10) || null;
        const maxFileSize = parseInt(input.getAttribute('data-max-file-size'), 10) || 10;
        
        return {
            allowMultiple: maxFileCount > 1,
            maxFiles: maxFileCount,
            maxFileSize: maxFileSize * 1024 * 1024,
            fileValidateTypeLabelExpectedTypes: 'Expects {allTypes}',
            acceptedFileTypes: input.accept ? input.accept.split(',') : null,
            fileSizeBase: 1024,
            server: this._getFilePondServerConfig(),
            labelIdle: this._getFilePondLabel(maxFileCount, maxFileSize),
            credits: false
        };
    },

    _getFilePondServerConfig() {
        return {
            process: (fieldName, file, metadata, load, error, progress, abort) => {
                const formData = new FormData();
                formData.append('action', 'ht_form_temp_file_upload');
                formData.append('_wpnonce', ht_form.nonce);
                formData.append('ht_form_file', file);

                const source = axios.CancelToken.source();

                axios.post(ht_form.ajaxurl, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                    cancelToken: source.token,
                    onUploadProgress: (e) => progress(e.lengthComputable, e.loaded, e.total)
                })
                .then(response => {
                    const data = response.data;
                    if (data.success) {
                        load(data.data.file_id);
                    } else {
                        error(data.data || 'Upload failed');
                    }
                })
                .catch(err => {
                    if (axios.isCancel(err)) {
                        error('Upload cancelled');
                    } else {
                        error('Upload failed: ' + (err.message || 'Unknown error'));
                    }
                });

                return {
                    abort: () => {
                        source.cancel('Upload aborted by user');
                        abort();
                    }
                };
            },
            
            revert: (uniqueFileId, load, error) => {
                const formData = new FormData();
                formData.append('action', 'ht_form_temp_file_delete');
                formData.append('_wpnonce', ht_form.nonce);
                formData.append('ht_form_file_id', uniqueFileId);

                axios.post(ht_form.ajaxurl, formData)
                    .then(response => {
                        const data = response.data;
                        if (data.success) {
                            load();
                        } else {
                            error(data.data || 'Delete failed');
                        }
                    })
                    .catch(err => {
                        error('Delete failed: ' + (err.message || 'Unknown error'));
                    });
            },

            // Load files from draft storage (for Save & Resume)
            load: (source, load, error, progress, abort, headers) => {
                // source is the URL to the file
                const request = new XMLHttpRequest();
                request.open('GET', source);
                request.responseType = 'blob';

                request.onload = () => {
                    if (request.status >= 200 && request.status < 300) {
                        load(request.response);
                    } else {
                        error('Could not load file');
                    }
                };

                request.onerror = () => {
                    error('Network error while loading file');
                };

                request.onprogress = (e) => {
                    progress(e.lengthComputable, e.loaded, e.total);
                };

                request.send();

                return {
                    abort: () => {
                        request.abort();
                        abort();
                    }
                };
            }
        };
    },

    _getFilePondLabel(maxFileCount, maxFileSize) {
        let label = `<span class="filepond--label-action">Browse</span> or drag and drop your files.`;

        if (maxFileCount && maxFileCount > 1 && maxFileSize) {
            label += `<br/> <span class="filepond-extra-info">Max files: ${maxFileCount} and Max size: ${maxFileSize}MB</span>`;
        } else if (maxFileCount && maxFileCount > 1) {
            label += `<br/> <span class="filepond-extra-info">Max files: ${maxFileCount}</span>`;
        } else if (maxFileSize) {
            label += `<br/> <span class="filepond-extra-info">Max size: ${maxFileSize}MB</span>`;
        }

        return label;
    },

    /**
     * Initialize repeater fields
     */
    initRepeaters() {
        document.querySelectorAll('.ht-form-repeater-wrapper').forEach(wrapper => {
            const rowLabel = wrapper.dataset.rowLabel || 'Row {n}';
            const nameAttribute = wrapper.dataset.name || '';
            const removeText = wrapper.dataset.removeText || __('Remove', 'ht-contactform');

            const rowsContainer = wrapper.querySelector('.ht-form-repeater-rows');

            if (!rowsContainer) return;

            // Add row click handlers (using event delegation)
            rowsContainer.addEventListener('click', (e) => {
                const addButton = e.target.closest('.ht-form-repeater-add-btn');
                if (addButton) {
                    e.preventDefault();

                    const currentRowCount = rowsContainer.querySelectorAll('.ht-form-repeater-row').length;

                    // Clone the last row
                    const lastRow = rowsContainer.querySelector('.ht-form-repeater-row:last-child');
                    if (!lastRow) return;

                    const newRow = lastRow.cloneNode(true);
                    const newIndex = currentRowCount;

                    // Update row index attribute
                    newRow.dataset.rowIndex = newIndex;

                    // Update remove button data-row-index
                    const removeBtn = newRow.querySelector('.ht-form-repeater-remove-btn');
                    if (removeBtn) {
                        removeBtn.dataset.rowIndex = newIndex;
                    }

                    // Handle Choices select fields - clean up cloned elements
                    // Find all .choices wrappers in the cloned row (created by Choices.js)
                    newRow.querySelectorAll('.choices').forEach(choicesWrapper => {
                        // Find the original select element inside the Choices wrapper
                        const select = choicesWrapper.querySelector('select[data-ht-select]');

                        if (select) {
                            // Reset the select element to its original state
                            select.classList.remove('choices__input', 'choices__input--cloned');
                            select.removeAttribute('hidden');
                            select.removeAttribute('aria-hidden');
                            select.removeAttribute('data-choice');
                            select.removeAttribute('tabindex');
                            select.removeAttribute('style');

                            // Clear selected value
                            select.selectedIndex = 0;

                            // Insert the select element before the Choices wrapper
                            choicesWrapper.parentNode.insertBefore(select, choicesWrapper);

                            // Remove the Choices wrapper
                            choicesWrapper.remove();
                        }
                    });

                    // Clear field values and update names
                    newRow.querySelectorAll('input, select, textarea').forEach(field => {
                        // Clear value
                        if (field.type === 'checkbox' || field.type === 'radio') {
                            field.checked = false;
                        } else {
                            field.value = '';
                        }

                        // Update name attribute to include new index
                        const currentName = field.name;
                        if (currentName) {
                            const updatedName = currentName.replace(/\[\d+\]/, `[${newIndex}]`);
                            field.name = updatedName;
                            field.id = updatedName.replace(/\[/g, '_').replace(/\]/g, '');
                        }
                    });

                    // Add the new row
                    rowsContainer.appendChild(newRow);

                    // Reinitialize Choices on the new row's select fields
                    if (typeof Choices !== 'undefined') {
                        newRow.querySelectorAll('[data-ht-select]').forEach(select => {
                            // Make sure the select is visible and ready
                            if (select && !select.choicesInstance) {
                                const config = this._getSelectConfig(select);
                                const choicesInstance = new Choices(select, config);
                                // Store instance on the element for later access
                                select.choicesInstance = choicesInstance;
                            }
                        });
                    }

                    // Update button states
                    this._updateRepeaterButtonStates(rowsContainer);
                    return;
                }

                // Remove row click handlers
                const removeButton = e.target.closest('.ht-form-repeater-remove-btn');
                if (removeButton) {
                    e.preventDefault();

                    const currentRowCount = rowsContainer.querySelectorAll('.ht-form-repeater-row').length;

                    // Prevent removing the last row (button should be disabled, but double check)
                    if (currentRowCount <= 1) {
                        return;
                    }

                    // Remove the row
                    const row = removeButton.closest('.ht-form-repeater-row');
                    if (row) {
                        row.remove();

                        // Re-index remaining rows
                        this._reindexRepeaterRows(rowsContainer, nameAttribute, rowLabel);

                        // Update button states
                        this._updateRepeaterButtonStates(rowsContainer);
                    }
                }
            });

            // Update button states on initial load
            this._updateRepeaterButtonStates(rowsContainer);
        });
    },

    /**
     * Re-index repeater rows after add/remove
     * @private
     */
    _reindexRepeaterRows(rowsContainer, nameAttribute, rowLabel) {
        const rows = rowsContainer.querySelectorAll('.ht-form-repeater-row');
        rows.forEach((row, index) => {
            // Update row index attribute
            row.dataset.rowIndex = index;

            // Update remove button data-row-index
            const removeBtn = row.querySelector('.ht-form-repeater-remove-btn');
            if (removeBtn) {
                removeBtn.dataset.rowIndex = index;
            }

            // Update row label
            const rowLabelEl = row.querySelector('.ht-form-repeater-row-label');
            if (rowLabelEl) {
                rowLabelEl.textContent = rowLabel.replace('{n}', index + 1);
            }

            // Update field names
            row.querySelectorAll('input, select, textarea').forEach(field => {
                const currentName = field.name;
                if (currentName && currentName.includes('[')) {
                    // Replace the index in the name
                    const updatedName = currentName.replace(/\[\d+\]/, `[${index}]`);
                    field.name = updatedName;
                    field.id = updatedName.replace(/\[/g, '_').replace(/\]/g, '');
                }
            });
        });
    },

    /**
     * Update repeater button states based on row count
     * @private
     */
    _updateRepeaterButtonStates(rowsContainer) {
        const rows = rowsContainer.querySelectorAll('.ht-form-repeater-row');
        const rowCount = rows.length;

        // Disable all remove buttons if only 1 row exists
        rows.forEach(row => {
            const removeBtn = row.querySelector('.ht-form-repeater-remove-btn');
            if (removeBtn) {
                if (rowCount <= 1) {
                    removeBtn.disabled = true;
                } else {
                    removeBtn.disabled = false;
                }
            }
        });
    },

    /**
     * Initialize Save & Resume functionality
     */
    initSaveResume() {
        // Check if we're resuming from a saved draft
        this._checkForResume();

        // Initialize save buttons
        document.querySelectorAll('.ht-form-save-resume-wrapper').forEach(wrapper => {
            const saveBtn = wrapper.querySelector('.ht-form-save-btn');
            const modal = wrapper.querySelector('.ht-form-save-modal');

            if (!saveBtn || !modal) return;

            // Save button click handler
            saveBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                await this._handleSave(wrapper, saveBtn, modal);
            });

            // Modal close handlers
            const closeBtn = modal.querySelector('.ht-form-save-modal-close');
            const overlay = modal.querySelector('.ht-form-save-modal-overlay');

            if (closeBtn) {
                closeBtn.addEventListener('click', () => this._closeModal(modal));
            }
            if (overlay) {
                overlay.addEventListener('click', () => this._closeModal(modal));
            }

            // Copy link handler
            const copyBtn = modal.querySelector('.ht-form-save-modal-copy-btn');
            if (copyBtn) {
                copyBtn.addEventListener('click', () => this._handleCopyLink(modal));
            }

            // Email handler
            const emailBtn = modal.querySelector('.ht-form-save-modal-email-btn');
            if (emailBtn) {
                emailBtn.addEventListener('click', () => this._handleEmailLink(wrapper, modal));
            }
        });
    },

    /**
     * Check URL for resume key and populate form
     * @private
     */
    _checkForResume() {
        const urlParams = new URLSearchParams(window.location.search);
        const resumeKey = urlParams.get('ht_form_resume');
        const resumeToken = urlParams.get('ht_form_token');

        if (!resumeKey) return;

        // Fetch draft data and populate form
        this._loadDraft(resumeKey, resumeToken);
    },

    /**
     * Load draft data from server
     * @private
     */
    async _loadDraft(draftKey, accessToken) {
        try {
            // Fall back to this browser's own stored token for same-device resume.
            const token = accessToken || '';
            const url = `${ht_form.rest_url}ht-form/v1/draft/${draftKey}`
                + (token ? `?token=${encodeURIComponent(token)}` : '');

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': ht_form.rest_nonce,
                }
            });

            const result = await response.json();

            if (!response.ok) {
                console.warn('Draft not found or expired');
                HTFormUtils.removeUrlParams(['ht_form_resume', 'ht_form_token']);
                return;
            }

            // Find the form with matching form_id
            const form = document.querySelector(`.ht-form[data-form-id="${result.form_id}"]`);
            if (!form) {
                console.warn('Form not found for draft');
                // Show user-friendly error message
                alert(__('This resume link is for a different form. Please use the correct form page.', 'ht-contactform'));
                HTFormUtils.removeUrlParams(['ht_form_resume', 'ht_form_token']);
                return;
            }

            // Store draft key + access token BEFORE populating fields (needed
            // for file restore and subsequent save/update/email calls)
            form.dataset.draftKey = draftKey;
            form.dataset.accessToken = token || '';
            if (token) {
                this._setStoredDraft(result.form_id, draftKey, token);
            }

            // Show resume notice
            const wrapper = form.querySelector('.ht-form-save-resume-wrapper');
            if (wrapper) {
                const noticeText = wrapper.dataset.resumeNoticeText || __('Resuming your saved progress...', 'ht-contactform');
                this._showResumeNotice(form, noticeText);
            }

            // Populate form fields
            this._populateFormFields(form, result.form_data);

            // Clean up URL
            HTFormUtils.removeUrlParams(['ht_form_resume', 'ht_form_token']);

        } catch (error) {
            console.error('Failed to load draft:', error);
        }
    },

    /**
     * Populate form fields with draft data
     * @private
     */
    _populateFormFields(form, data) {
        if (!data || typeof data !== 'object') return;

        Object.entries(data).forEach(([fieldName, value]) => {
            // Skip internal fields
            if (fieldName.startsWith('_') || fieldName === 'form_id' || fieldName === 'ht_form_id') return;

            const field = form.querySelector(`[name="${fieldName}"]`);
            if (!field) return;

            // Handle Rich Text Editor (Quill)
            if (field.classList.contains('ht-form-elem-richtext-value')) {
                const container = field.closest('.ht-form-elem-richtext');
                if (container && container._quill && value) {
                    container._quill.root.innerHTML = value;
                    field.value = value;
                }
                return;
            }

            // Handle Signature field - restore from data URL
            if (field.classList.contains('ht-form-elem-signature-value')) {
                const container = field.closest('.ht-form-elem-signature');
                if (container && container._signaturePad && value) {
                    try {
                        container._signaturePad.fromDataURL(value);
                        field.value = value;
                    } catch (e) {
                        console.warn('Could not restore signature:', e);
                        // Clear corrupted data
                        field.value = '';
                        container._signaturePad.clear();
                    }
                }
                return;
            }

            // Handle DateTime (flatpickr)
            if (field.classList.contains('ht-form-elem-datetime') && field._flatpickr && value) {
                field._flatpickr.setDate(value, true);
                return;
            }

            // Handle Phone (intlTelInput)
            if (field.classList.contains('ht-form-elem-input-tel') && field._intlTelInput && value) {
                field._intlTelInput.setNumber(value);
                return;
            }

            // Handle Range/Slider - update display value
            if (field.classList.contains('ht-form-elem-range') && value) {
                field.value = value;
                const amountEl = field.nextElementSibling?.querySelector('.ht-form-elem-range-amount');
                if (amountEl) {
                    amountEl.textContent = value;
                }
                return;
            }

            // Handle Color picker - update display value
            if (field.classList.contains('ht-form-elem-color-input') && value) {
                field.value = value;
                const colorValue = field.parentElement?.querySelector('.ht-form-elem-color-value');
                if (colorValue) {
                    colorValue.textContent = value.toUpperCase();
                }
                return;
            }

            // Handle Choices.js enhanced selects
            if (field.choicesInstance && value) {
                field.choicesInstance.setChoiceByValue(value);
                return;
            }

            if (field.type === 'checkbox') {
                field.checked = Boolean(value);
            } else if (field.type === 'radio') {
                const radio = form.querySelector(`[name="${fieldName}"][value="${value}"]`);
                if (radio) {
                    radio.checked = true;
                    // Handle Ratings field visual update
                    const ratingsContainer = radio.closest('.ht-form-elem-ratings');
                    if (ratingsContainer) {
                        HTFormEventHandlers._resetRatingsToCheckedState(ratingsContainer);
                    }
                }
            } else if (field.tagName === 'SELECT') {
                field.value = value;
                // Trigger change for dependent fields
                field.dispatchEvent(new Event('change', { bubbles: true }));
            } else {
                field.value = value;
            }
        });

        // Handle array fields (checkboxes with same name[], nested fields, multi-select)
        Object.entries(data).forEach(([fieldName, value]) => {
            if (Array.isArray(value)) {
                // Check if this is a multi-select with Choices.js
                const multiSelect = form.querySelector(`select[name="${fieldName}"][multiple]`);
                if (multiSelect && multiSelect.choicesInstance) {
                    // Choices.js can handle array of values for multi-select
                    multiSelect.choicesInstance.setChoiceByValue(value);
                    return;
                }

                // Check if this is file upload data (array with file_id property)
                if (value.length > 0 && value[0]?.file_id) {
                    // This is file upload data - restore FilePond
                    this._restoreFilePondFiles(form, fieldName, value);
                    return;
                }

                // Check if this is a repeater field (array of objects)
                const repeaterWrapper = form.querySelector(`.ht-form-repeater-wrapper[data-name="${fieldName}"]`);
                if (repeaterWrapper && value.length > 0 && typeof value[0] === 'object') {
                    // This is a repeater field - populate rows with indexed pattern
                    this._populateRepeaterField(form, repeaterWrapper, fieldName, value);
                } else {
                    // Standard array handling
                    value.forEach((item, index) => {
                        if (typeof item === 'object' && item !== null) {
                            // Nested field (name, address) - single object
                            Object.entries(item).forEach(([subKey, subValue]) => {
                                const subField = form.querySelector(`[name="${fieldName}[${subKey}]"]`);
                                if (subField) subField.value = subValue;
                            });
                        } else {
                            // Checkbox array
                            const checkbox = form.querySelector(`[name="${fieldName}[]"][value="${item}"]`);
                            if (checkbox) checkbox.checked = true;
                        }
                    });
                }
            } else if (typeof value === 'object' && value !== null) {
                // Check if this is a chained select (has level_1, level_2, etc.)
                const isChainedSelect = Object.keys(value).some(key => key.startsWith('level_'));

                if (isChainedSelect) {
                    // Handle chained select - set values sequentially with change events
                    this._populateChainedSelect(form, fieldName, value);
                } else {
                    // Object field (name, address)
                    Object.entries(value).forEach(([subKey, subValue]) => {
                        const subField = form.querySelector(`[name="${fieldName}[${subKey}]"]`);
                        if (subField) subField.value = subValue;
                    });
                }
            }
        });
    },

    /**
     * Wait for select options to be populated
     * @private
     */
    _waitForOptions(select, timeout = 2000) {
        return new Promise(resolve => {
            // If options already loaded (more than just placeholder), resolve immediately
            if (select.options.length > 1) {
                resolve();
                return;
            }
            // Otherwise wait for options to populate using MutationObserver
            const observer = new MutationObserver((mutations, obs) => {
                if (select.options.length > 1) {
                    obs.disconnect();
                    resolve();
                }
            });
            observer.observe(select, { childList: true });
            // Timeout fallback
            setTimeout(() => {
                observer.disconnect();
                resolve();
            }, timeout);
        });
    },

    /**
     * Populate chained select fields sequentially
     * @private
     */
    async _populateChainedSelect(form, fieldName, values) {
        // Get all level keys sorted (level_1, level_2, level_3, ...)
        const levelKeys = Object.keys(values)
            .filter(key => key.startsWith('level_'))
            .sort((a, b) => {
                const numA = parseInt(a.replace('level_', ''));
                const numB = parseInt(b.replace('level_', ''));
                return numA - numB;
            });

        // Set each level sequentially, waiting for options to load
        for (const levelKey of levelKeys) {
            const levelValue = values[levelKey];
            if (!levelValue) continue;

            const select = form.querySelector(`[name="${fieldName}[${levelKey}]"]`);
            if (!select) continue;

            // Wait for options to be populated before setting value
            await this._waitForOptions(select);
            select.value = levelValue;
            // Trigger change to populate next level's options
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    },

    /**
     * Populate repeater field rows with saved data
     * @private
     */
    _populateRepeaterField(form, repeaterWrapper, fieldName, rowsData) {
        const rowsContainer = repeaterWrapper.querySelector('.ht-form-repeater-rows');
        if (!rowsContainer) return;

        const existingRows = rowsContainer.querySelectorAll('.ht-form-repeater-row');
        const neededRows = rowsData.length;

        // Add additional rows if needed (by clicking the add button)
        const addBtn = repeaterWrapper.querySelector('.ht-form-repeater-add-btn');
        if (addBtn && neededRows > existingRows.length) {
            for (let i = existingRows.length; i < neededRows; i++) {
                addBtn.click();
            }
        }

        // Wait a tick for DOM to update after adding rows
        setTimeout(() => {
            // Populate each row's fields
            rowsData.forEach((rowData, rowIndex) => {
                if (!rowData || typeof rowData !== 'object') return;

                Object.entries(rowData).forEach(([subKey, subValue]) => {
                    // Use the indexed pattern: fieldname[rowIndex][subfield]
                    const field = form.querySelector(`[name="${fieldName}[${rowIndex}][${subKey}]"]`);
                    if (field) {
                        if (field.type === 'checkbox') {
                            field.checked = Boolean(subValue);
                        } else if (field.type === 'radio') {
                            const radio = form.querySelector(`[name="${fieldName}[${rowIndex}][${subKey}]"][value="${subValue}"]`);
                            if (radio) radio.checked = true;
                        } else if (field.tagName === 'SELECT') {
                            field.value = subValue;
                            // Handle Choices.js enhanced selects
                            if (field.choicesInstance) {
                                field.choicesInstance.setChoiceByValue(subValue);
                            }
                        } else {
                            field.value = subValue;
                        }
                    }
                });
            });
        }, 50);
    },

    /**
     * Restore FilePond files from draft data
     * @private
     */
    _restoreFilePondFiles(form, fieldName, filesData) {
        // Find the file upload wrapper
        const wrapper = form.querySelector(
            `.ht-form-elem-file-upload input[name="${fieldName}[]"],
             .ht-form-elem-image-upload input[name="${fieldName}[]"]`
        )?.closest('.ht-form-elem-file-upload, .ht-form-elem-image-upload');

        if (!wrapper) return;

        // FilePond instance is stored on the wrapper element as .filepond
        const pond = wrapper.filepond;
        if (!pond) return;

        const draftKey = form.dataset.draftKey;

        if (!draftKey) {
            console.warn('No draft key found, cannot restore files');
            return;
        }

        // Track failed file restorations
        const failedFiles = [];
        const restorePromises = [];

        // Add each file to FilePond
        filesData.forEach(fileData => {
            // Create a file source URL for the draft file
            const fileUrl = `${ht_form.upload_url}/ht_form/drafts/${draftKey}/${fileData.file_id}`;

            // Add the file to FilePond as a server file (type: 'local' triggers load function)
            const promise = pond.addFile(fileUrl, {
                type: 'local'
            }).catch(error => {
                console.warn('Failed to restore file:', fileData.file_name, error);
                failedFiles.push(fileData.file_name);
            });

            restorePromises.push(promise);
        });

        // After all files attempted, show warning if any failed
        Promise.all(restorePromises).then(() => {
            if (failedFiles.length > 0) {
                setTimeout(() => {
                    this._showWarningNotice(form,
                        __('Some files could not be restored and may need to be re-uploaded.', 'ht-contactform')
                    );
                }, 500); // Delay to allow FilePond UI to settle
            }
        });
    },

    /**
     * Show resume notice
     * @private
     */
    _showResumeNotice(form, message) {
        const notice = document.createElement('div');
        notice.className = 'ht-form-resume-notice';
        notice.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>${message}</span>
        `;
        form.insertBefore(notice, form.firstChild);

        // Auto-hide after 5 seconds
        setTimeout(() => {
            notice.classList.add('ht-form-resume-notice-fade');
            setTimeout(() => notice.remove(), 300);
        }, 5000);
    },

    /**
     * Show warning notice on form
     * @private
     */
    _showWarningNotice(form, message) {
        const notice = document.createElement('div');
        notice.className = 'ht-form-resume-notice ht-form-warning-notice';
        notice.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <span>${message}</span>
        `;
        form.insertBefore(notice, form.firstChild);

        // Auto-hide after 8 seconds (longer for warnings)
        setTimeout(() => {
            notice.classList.add('ht-form-resume-notice-fade');
            setTimeout(() => notice.remove(), 300);
        }, 8000);
    },

    /**
     * Handle save button click
     * @private
     */
    async _handleSave(wrapper, saveBtn, modal) {
        const form = wrapper.closest('.ht-form');
        if (!form) return;

        // Show loading state
        const btnText = saveBtn.querySelector('.ht-form-save-btn-text');
        const btnLoading = saveBtn.querySelector('.ht-form-save-btn-loading');
        if (btnText) btnText.style.display = 'none';
        if (btnLoading) btnLoading.style.display = 'inline-flex';
        saveBtn.disabled = true;

        try {
            const formData = this._collectFormData(form);
            const expiryDays = parseInt(wrapper.dataset.expiry || '30', 10);
            const formId = form.querySelector('[name="ht_form_id"]')?.value || form.dataset.formId;

            // Reuse this browser's own draft (key + access token) if it saved
            // this form before, so re-saving updates the same record instead of
            // piling up drafts. The access token proves ownership server-side.
            const stored = this._getStoredDraft(formId);

            const response = await fetch(`${ht_form.rest_url}ht-form/v1/draft/save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': ht_form.rest_nonce,
                },
                body: JSON.stringify({
                    form_id: formId,
                    form_data: formData,
                    expiry_days: expiryDays,
                    page_url: window.location.href.split('?')[0],
                    draft_key: form.dataset.draftKey || stored.draft_key || '',
                    access_token: form.dataset.accessToken || stored.access_token || '',
                }),
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || __('Failed to save progress', 'ht-contactform'));
            }

            // Store the draft key, access token and resume URL
            form.dataset.draftKey = result.draft_key;
            form.dataset.accessToken = result.access_token || '';
            this._setStoredDraft(formId, result.draft_key, result.access_token);
            modal.dataset.resumeUrl = result.resume_url;

            // Move uploaded files from temp to draft storage
            await this._moveFilesToDraft(form, result.draft_key);

            // Populate the link input
            const linkInput = modal.querySelector('.ht-form-save-modal-link-input');
            if (linkInput) {
                linkInput.value = result.resume_url;
            }

            // Show the modal
            this._openModal(modal);

        } catch (error) {
            console.error('Save failed:', error);
            HTFormMessageHandler.showError(form, error.message || __('Failed to save progress. Please try again.', 'ht-contactform'));
        } finally {
            // Reset button state
            if (btnText) btnText.style.display = 'inline';
            if (btnLoading) btnLoading.style.display = 'none';
            saveBtn.disabled = false;
        }
    },

    /**
     * Collect all form field data
     * @private
     */
    _collectFormData(form) {
        const formData = {};
        const formElements = form.querySelectorAll('input, select, textarea');

        formElements.forEach(field => {
            const name = field.name;
            if (!name || name.startsWith('_')) return;

            // Skip hidden system fields and reCAPTCHA tokens
            if (field.type === 'hidden' && (name === 'ht_form_id' || name.includes('nonce') || name.includes('g-recaptcha'))) {
                return;
            }

            // Handle different field types
            if (field.type === 'checkbox') {
                if (name.endsWith('[]')) {
                    // Multiple checkboxes
                    const baseName = name.slice(0, -2);
                    if (!formData[baseName]) formData[baseName] = [];
                    if (field.checked) formData[baseName].push(field.value);
                } else {
                    formData[name] = field.checked;
                }
            } else if (field.type === 'radio') {
                if (field.checked) {
                    formData[name] = field.value;
                }
            } else if (field.type === 'file') {
                // Skip file fields - they can't be restored
            } else if (name.includes('[')) {
                // Check for repeater pattern: fieldname[index][subfield]
                const repeaterMatch = name.match(/^([^\[]+)\[(\d+)\]\[([^\]]+)\]$/);
                if (repeaterMatch) {
                    // Repeater field (contacts[0][name], contacts[1][email], etc.)
                    const [, baseName, rowIndex, subKey] = repeaterMatch;
                    const index = parseInt(rowIndex);
                    if (!formData[baseName]) formData[baseName] = [];
                    if (!formData[baseName][index]) formData[baseName][index] = {};
                    formData[baseName][index][subKey] = field.value;
                } else {
                    // Simple nested fields (name[first], address[city], etc.)
                    const match = name.match(/^([^\[]+)\[([^\]]+)\]/);
                    if (match) {
                        const [, baseName, subKey] = match;
                        if (!formData[baseName]) formData[baseName] = {};
                        formData[baseName][subKey] = field.value;
                    }
                }
            } else {
                formData[name] = field.value;
            }
        });

        // Collect FilePond file upload data
        const fileUploads = form.querySelectorAll('.ht-form-elem-file-upload, .ht-form-elem-image-upload');
        fileUploads.forEach(wrapper => {
            // FilePond instance is stored on the wrapper element as .filepond
            const pond = wrapper.filepond;
            if (!pond) return;

            // FilePond replaces the original input, so get name from pond.name or wrapper's for attribute
            let fieldName = pond.name;
            if (!fieldName) {
                // Fallback: use the wrapper's 'for' attribute (which is the original field ID)
                fieldName = wrapper.getAttribute('for');
            }
            // Remove [] suffix if present
            fieldName = fieldName?.replace('[]', '');
            if (!fieldName) return;

            const files = pond.getFiles()
                .filter(f => f.serverId && f.status === 5) // Only files with PROCESSING_COMPLETE status
                .map(f => {
                    // serverId could be a full URL (from restored files) or just filename (from new uploads)
                    // Extract just the filename if it's a URL
                    let fileId = f.serverId;
                    if (fileId.startsWith('http://') || fileId.startsWith('https://')) {
                        try {
                            // Use URL API to properly handle query params and fragments
                            const url = new URL(fileId);
                            fileId = decodeURIComponent(url.pathname.split('/').pop());
                        } catch (e) {
                            // Fallback for malformed URLs
                            fileId = fileId.split('/').pop().split('?')[0].split('#')[0];
                        }
                    }
                    return {
                        file_id: fileId,
                        file_name: f.file.name,
                        file_size: f.file.size,
                        file_type: f.file.type
                    };
                });

            if (files.length > 0) {
                formData[fieldName] = files;
            }
        });

        return formData;
    },

    /**
     * Move uploaded files from temp to draft storage
     * @private
     */
    /**
     * Read this browser's stored draft (key + access token) for a form.
     * @private
     */
    _getStoredDraft(formId) {
        try {
            const raw = window.localStorage.getItem(`ht_form_draft_${formId}`);
            return raw ? JSON.parse(raw) : {};
        } catch (e) {
            return {};
        }
    },

    /**
     * Persist this browser's draft key + access token for a form.
     * @private
     */
    _setStoredDraft(formId, draftKey, accessToken) {
        try {
            window.localStorage.setItem(
                `ht_form_draft_${formId}`,
                JSON.stringify({ draft_key: draftKey, access_token: accessToken || '' })
            );
        } catch (e) {
            /* storage unavailable — non-fatal */
        }
    },

    async _moveFilesToDraft(form, draftKey) {
        // Collect all file IDs from FilePond instances
        const fileIds = [];
        const fileUploads = form.querySelectorAll('.ht-form-elem-file-upload, .ht-form-elem-image-upload');

        fileUploads.forEach(wrapper => {
            // FilePond instance is stored on the wrapper element as .filepond
            const pond = wrapper.filepond;
            if (!pond) return;

            pond.getFiles().forEach(f => {
                if (f.serverId) {
                    fileIds.push(f.serverId);
                }
            });
        });

        // If no files, nothing to do
        if (fileIds.length === 0) return;

        try {
            const response = await fetch(`${ht_form.rest_url}ht-form/v1/draft/files`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': ht_form.rest_nonce,
                },
                body: JSON.stringify({
                    draft_key: draftKey,
                    access_token: form.dataset.accessToken || '',
                    file_ids: fileIds
                })
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok || !result.success) {
                console.warn('Failed to move files to draft storage');
                this._showWarningNotice(form,
                    __('Some files could not be saved. They may need to be re-uploaded.', 'ht-contactform')
                );
            }
        } catch (error) {
            console.warn('Error moving files to draft storage:', error);
            this._showWarningNotice(form,
                __('Some files could not be saved. They may need to be re-uploaded.', 'ht-contactform')
            );
        }
    },

    /**
     * Open modal
     * @private
     */
    _openModal(modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setTimeout(() => modal.classList.add('ht-form-save-modal-open'), 10);
    },

    /**
     * Close modal
     * @private
     */
    _closeModal(modal) {
        modal.classList.remove('ht-form-save-modal-open');
        document.body.style.overflow = '';
        setTimeout(() => modal.style.display = 'none', 200);
    },

    /**
     * Handle copy link button
     * @private
     */
    _handleCopyLink(modal) {
        const linkInput = modal.querySelector('.ht-form-save-modal-link-input');
        const copyBtn = modal.querySelector('.ht-form-save-modal-copy-btn');
        if (!linkInput) return;

        navigator.clipboard.writeText(linkInput.value).then(() => {
            // Show copied feedback
            const originalText = copyBtn.querySelector('span').textContent;
            copyBtn.querySelector('span').textContent = __('Copied!', 'ht-contactform');
            copyBtn.classList.add('ht-form-save-modal-copy-btn-success');

            setTimeout(() => {
                copyBtn.querySelector('span').textContent = originalText;
                copyBtn.classList.remove('ht-form-save-modal-copy-btn-success');
            }, 2000);
        }).catch(err => {
            // Fallback for older browsers
            linkInput.select();
            document.execCommand('copy');
        });
    },

    /**
     * Handle email link button
     * @private
     */
    async _handleEmailLink(wrapper, modal) {
        const emailInput = modal.querySelector('.ht-form-save-modal-email-input');
        const emailBtn = modal.querySelector('.ht-form-save-modal-email-btn');
        const statusEl = modal.querySelector('.ht-form-save-modal-email-status');
        const form = wrapper.closest('.ht-form');

        if (!emailInput || !emailBtn || !form) return;

        const email = emailInput.value.trim();
        if (!email) {
            statusEl.textContent = __('Please enter an email address', 'ht-contactform');
            statusEl.className = 'ht-form-save-modal-email-status ht-form-save-modal-email-status-error';
            return;
        }

        // Show loading
        const btnText = emailBtn.querySelector('.ht-form-btn-text');
        const btnLoading = emailBtn.querySelector('.ht-form-btn-loading');
        if (btnText) btnText.style.display = 'none';
        if (btnLoading) btnLoading.style.display = 'inline-flex';
        emailBtn.disabled = true;

        try {
            const draftKey = form.dataset.draftKey;
            if (!draftKey) {
                throw new Error(__('No draft found', 'ht-contactform'));
            }

            const response = await fetch(`${ht_form.rest_url}ht-form/v1/draft/email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': ht_form.rest_nonce,
                },
                body: JSON.stringify({
                    draft_key: draftKey,
                    access_token: form.dataset.accessToken || '',
                    email: email,
                    page_url: window.location.href.split('?')[0],
                }),
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || __('Failed to send email', 'ht-contactform'));
            }

            statusEl.textContent = result.message || __('Email sent successfully!', 'ht-contactform');
            statusEl.className = 'ht-form-save-modal-email-status ht-form-save-modal-email-status-success';
            emailInput.value = '';

        } catch (error) {
            statusEl.textContent = error.message;
            statusEl.className = 'ht-form-save-modal-email-status ht-form-save-modal-email-status-error';
        } finally {
            if (btnText) btnText.style.display = 'inline';
            if (btnLoading) btnLoading.style.display = 'none';
            emailBtn.disabled = false;
        }
    }
};

/**
 * Main form validation controller
 */
const HTForm = {
    /**
     * Initialize form validation system
     */
    init() {
        const forms = document.querySelectorAll(HTFORM_CONFIG.FORM_SELECTOR);
        
        forms.forEach(form => {
            HTFormEventHandlers.setupFieldListeners(form);
            HTFormEventHandlers.setupSubmitListener(form);
            HTFormConditionalLogic.check(form);
            HTFormMessageHandler.autoHideMessages(form);
        });
    }
};

/**
 * Initialize everything when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    HTFormFieldComponents.initAll();
    HTForm.init();
    // Initialize save/resume AFTER HTForm.init() so Quill and SignaturePad are ready
    HTFormFieldComponents.initSaveResume();
});