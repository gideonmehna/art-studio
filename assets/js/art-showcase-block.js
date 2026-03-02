(function() {
    let { registerBlockType } = wp.blocks;
    let { createElement, Fragment, useState, useEffect } = wp.element;
    let { InspectorControls } = wp.blockEditor;
    let { PanelBody, RangeControl, ToggleControl, TextControl, SelectControl } = wp.components;

    registerBlockType('custom/art-showcase', {
        title: 'Art Showcase',
        icon: 'images-alt',
        category: 'art-blocks',
        description: 'Horizontal scrolling gallery showcasing featured artwork',

        attributes: {
            numberOfItems: {
                type: 'number',
                default: 8
            },
            showTitle: {
                type: 'boolean',
                default: true
            },
            title: {
                type: 'string',
                default: 'Featured Artwork'
            },
            artCategory: {
                type: 'string',
                default: ''
            }
        },

        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { numberOfItems, showTitle, title, artCategory } = attributes;
            const [ categoryOptions, setCategoryOptions ] = useState([
                { label: 'All Categories (no filter)', value: '' }
            ]);

            // Fetch art_category terms from REST API on mount
            useEffect(function() {
                wp.apiFetch({ path: '/wp/v2/art-categories?per_page=100' })
                    .then(function(terms) {
                        const options = [{ label: 'All Categories (no filter)', value: '' }];
                        terms.forEach(function(term) {
                            options.push({ label: term.name, value: term.slug });
                        });
                        setCategoryOptions(options);
                    })
                    .catch(function(err) {
                        console.warn('Art Showcase: could not load art categories', err);
                    });
            }, []);

            return createElement(Fragment, null,
                createElement(InspectorControls, null,
                    createElement(PanelBody, { title: 'Showcase Settings' },
                        createElement(ToggleControl, {
                            label: 'Show Title',
                            checked: showTitle,
                            onChange: (value) => setAttributes({ showTitle: value })
                        }),
                        showTitle && createElement(TextControl, {
                            label: 'Title',
                            value: title,
                            onChange: (value) => setAttributes({ title: value })
                        }),
                        createElement(RangeControl, {
                            label: 'Number of Items',
                            value: numberOfItems,
                            onChange: (value) => setAttributes({ numberOfItems: value }),
                            min: 4,
                            max: 20,
                            step: 1
                        }),
                        createElement(SelectControl, {
                            label: 'Category Filter',
                            value: artCategory,
                            options: categoryOptions,
                            onChange: (slug) => setAttributes({ artCategory: slug }),
                            help: 'Restrict this showcase to one category. Leave empty to show all.'
                        })
                    )
                ),
                createElement('div', {
                    className: 'art-showcase-block-editor'
                },
                    createElement('div', { className: 'art-showcase-preview' },
                        createElement('h3', null, '🎨 Art Showcase'),
                        showTitle && createElement('h4', null, title),
                        createElement('div', { className: 'art-showcase-preview-grid' },
                            // Create preview items
                            Array.from({ length: Math.min(numberOfItems, 4) }, (_, i) =>
                                createElement('div', {
                                    key: i,
                                    className: 'art-showcase-preview-item'
                                },
                                    createElement('div', { className: 'art-showcase-preview-image' }),
                                    createElement('div', { className: 'art-showcase-preview-text' },
                                        createElement('div', { className: 'art-showcase-preview-title' }),
                                        createElement('div', { className: 'art-showcase-preview-artist' })
                                    )
                                )
                            )
                        ),
                        createElement('p', { className: 'art-showcase-preview-note' },
                            `Displaying ${numberOfItems} items` +
                            (artCategory ? ` • Category: ${artCategory}` : '') +
                            ' • Preview available on frontend'
                        )
                    )
                )
            );
        },
        
        save: function() {
            return null; // Dynamic block, rendered by PHP
        }
    });
})();