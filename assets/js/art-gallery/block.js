(function() {
    var registerBlockType  = wp.blocks.registerBlockType;
    var InspectorControls  = wp.blockEditor.InspectorControls;
    var el                 = wp.element.createElement;
    var Fragment           = wp.element.Fragment;
    var useState           = wp.element.useState;
    var useEffect          = wp.element.useEffect;
    var PanelBody          = wp.components.PanelBody;
    var TextControl        = wp.components.TextControl;
    var CheckboxControl    = wp.components.CheckboxControl;
    var ToggleControl      = wp.components.ToggleControl;

    registerBlockType('custom/art-gallery', {
        title:    'Art Gallery',
        icon:     'format-gallery',
        category: 'art-blocks',

        attributes: {
            uploadUrl: {
                type:    'string',
                default: ''
            },
            artCategory: {          // legacy single-slug — never written by new editor
                type:    'string',
                default: ''
            },
            artCategories: {        // multi-slug array
                type:    'array',
                default: [],
                items:   { type: 'string' }
            },
            allowedEmotions: {      // emotion sidebar whitelist
                type:    'array',
                default: [],
                items:   { type: 'string' }
            }
        },

        edit: function(props) {
            var attributes    = props.attributes;
            var setAttributes = props.setAttributes;

            var categoryTermsState   = useState([]);
            var categoryTerms        = categoryTermsState[0];
            var setCategoryTerms     = categoryTermsState[1];

            var emotionTermsState    = useState([]);
            var emotionTerms         = emotionTermsState[0];
            var setEmotionTerms      = emotionTermsState[1];

            // Toggle starts ON if there are already saved allowed emotions
            var filterEmotionsState  = useState(attributes.allowedEmotions.length > 0);
            var filterEmotionsOn     = filterEmotionsState[0];
            var setFilterEmotionsOn  = filterEmotionsState[1];

            // Fetch both category and emotion terms in parallel on mount
            useEffect(function() {
                wp.apiFetch({ path: '/wp/v2/art-categories?per_page=100' })
                    .then(function(terms) { setCategoryTerms(terms); })
                    .catch(function(err)  { console.warn('Art Gallery: could not load art categories', err); });

                wp.apiFetch({ path: '/wp/v2/art-emotions?per_page=100' })
                    .then(function(terms) { setEmotionTerms(terms); })
                    .catch(function(err)  { console.warn('Art Gallery: could not load art emotions', err); });
            }, []);

            // Toggle a slug in/out of an array attribute
            function toggleItem(attrName, slug, isChecked) {
                var current = attributes[attrName].slice();
                if (isChecked) {
                    if (current.indexOf(slug) === -1) { current.push(slug); }
                } else {
                    current = current.filter(function(s) { return s !== slug; });
                }
                var update = {};
                update[attrName] = current;
                setAttributes(update);
            }

            // --- Category checkboxes ---
            var categoryCheckboxes;
            if (categoryTerms.length === 0) {
                categoryCheckboxes = el('p', {
                    style: { fontSize: '12px', color: '#757575', margin: '4px 0' }
                }, 'Loading categories…');
            } else {
                categoryCheckboxes = categoryTerms.map(function(term) {
                    return el(CheckboxControl, {
                        key:      term.id,
                        label:    term.name,
                        checked:  attributes.artCategories.indexOf(term.slug) !== -1,
                        onChange: function(checked) { toggleItem('artCategories', term.slug, checked); }
                    });
                });
            }

            // --- Emotion checkboxes (shown only when toggle is on) ---
            var emotionCheckboxes;
            if (emotionTerms.length === 0) {
                emotionCheckboxes = el('p', {
                    style: { fontSize: '12px', color: '#757575', margin: '4px 0' }
                }, 'Loading emotions…');
            } else {
                emotionCheckboxes = emotionTerms.map(function(term) {
                    return el(CheckboxControl, {
                        key:      term.id,
                        label:    term.name,
                        checked:  attributes.allowedEmotions.indexOf(term.slug) !== -1,
                        onChange: function(checked) { toggleItem('allowedEmotions', term.slug, checked); }
                    });
                });
            }

            // --- Emotion sidebar toggle ---
            var emotionToggle = el(ToggleControl, {
                label:    'Filter emotion sidebar',
                help:     filterEmotionsOn
                              ? 'Only checked emotions will appear in the gallery sidebar.'
                              : 'All emotions are shown in the sidebar.',
                checked:  filterEmotionsOn,
                onChange: function(val) {
                    setFilterEmotionsOn(val);
                    if (!val) {
                        // Toggled off — clear the list so PHP shows all emotions
                        setAttributes({ allowedEmotions: [] });
                    }
                }
            });

            // --- Editor preview summary ---
            var previewLines = [
                el('p', { key: 'title', style: { fontWeight: 'bold' } }, 'Art Gallery Block')
            ];
            if (attributes.uploadUrl) {
                previewLines.push(el('p', { key: 'url', style: { fontSize: '12px' } },
                    'Upload URL: ' + attributes.uploadUrl));
            }
            if (attributes.artCategories.length > 0) {
                previewLines.push(el('p', { key: 'cats', style: { fontSize: '12px' } },
                    'Categories: ' + attributes.artCategories.join(', ')));
            } else if (attributes.artCategory) {
                previewLines.push(el('p', { key: 'cat-legacy', style: { fontSize: '12px', color: '#999' } },
                    'Category (legacy): ' + attributes.artCategory));
            }
            if (attributes.allowedEmotions.length > 0) {
                previewLines.push(el('p', { key: 'emotions', style: { fontSize: '12px' } },
                    'Emotions: ' + attributes.allowedEmotions.join(', ')));
            }

            return el(Fragment, null,

                el(InspectorControls, null,

                    el(PanelBody, { title: 'Gallery Settings', initialOpen: true },
                        el(TextControl, {
                            label:    'Upload Button URL',
                            value:    attributes.uploadUrl,
                            onChange: function(url) { setAttributes({ uploadUrl: url }); },
                            help:     'URL for the Upload Artwork button'
                        })
                    ),

                    el(PanelBody, { title: 'Category Filter', initialOpen: true },
                        el('p', { style: { fontSize: '12px', color: '#757575', marginBottom: '8px' } },
                            'Check categories to show in this gallery. Leave all unchecked to show everything.'),
                        categoryCheckboxes
                    ),

                    el(PanelBody, { title: 'Emotion Sidebar', initialOpen: false },
                        emotionToggle,
                        filterEmotionsOn ? el(Fragment, null, emotionCheckboxes) : null
                    )
                ),

                el('div', { className: 'art-gallery-block-editor' },
                    el('div', { className: 'art-gallery-placeholder' },
                        previewLines
                    )
                )
            );
        },

        save: function() {
            return null; // Dynamic block — rendered by PHP
        }
    });
})();
