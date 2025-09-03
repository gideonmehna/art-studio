(function() {
    let { registerBlockType } = wp.blocks;
    let { createElement, Fragment } = wp.element;
    let { InspectorControls } = wp.blockEditor;
    let { PanelBody, RangeControl, ToggleControl, TextControl } = wp.components;

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
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { numberOfItems, showTitle, title } = attributes;
            
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
                            `Displaying ${numberOfItems} items • Preview available on frontend`
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