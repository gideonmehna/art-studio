(function(blocks, element, components, data) {
    var el = element.createElement;
    var Fragment = element.Fragment;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var ToggleControl = components.ToggleControl;
    var SelectControl = components.SelectControl;
    var ServerSideRender = wp.serverSideRender;

    blocks.registerBlockType('custom/creative-art-emotions', {
        title: 'Creative Art Emotions',
        description: 'Display art emotions in a creative, scattered layout',
        icon: 'art',
        category: 'art-blocks',
        keywords: ['art', 'emotions', 'creative', 'gallery'],
        
        attributes: {
            showTitle: {
                type: 'boolean',
                default: true,
            },
            imageSize: {
                type: 'string',
                default: 'medium',
            },
            orderBy: {
                type: 'string',
                default: 'name',
            },
            order: {
                type: 'string',
                default: 'ASC',
            },
            showCount: {
                type: 'boolean',
                default: false,
            },
            animationStyle: {
                type: 'string',
                default: 'fade-up',
            }
        },

        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            return el(Fragment, {},
                el(InspectorControls, {},
                    el(PanelBody, { 
                        title: 'Display Settings',
                        initialOpen: true 
                    },
                        el(ToggleControl, {
                            label: 'Show Titles',
                            checked: attributes.showTitle,
                            onChange: function(value) {
                                setAttributes({ showTitle: value });
                            }
                        }),
                        
                        el(ToggleControl, {
                            label: 'Show Post Count',
                            checked: attributes.showCount,
                            onChange: function(value) {
                                setAttributes({ showCount: value });
                            }
                        }),

                        el(SelectControl, {
                            label: 'Image Size',
                            value: attributes.imageSize,
                            options: [
                                { label: 'Thumbnail', value: 'thumbnail' },
                                { label: 'Medium', value: 'medium' },
                                { label: 'Large', value: 'large' },
                                { label: 'Full', value: 'full' }
                            ],
                            onChange: function(value) {
                                setAttributes({ imageSize: value });
                            }
                        }),

                        el(SelectControl, {
                            label: 'Order By',
                            value: attributes.orderBy,
                            options: [
                                { label: 'Name', value: 'name' },
                                { label: 'Count', value: 'count' },
                                { label: 'Term ID', value: 'term_id' }
                            ],
                            onChange: function(value) {
                                setAttributes({ orderBy: value });
                            }
                        }),

                        el(SelectControl, {
                            label: 'Order',
                            value: attributes.order,
                            options: [
                                { label: 'Ascending', value: 'ASC' },
                                { label: 'Descending', value: 'DESC' }
                            ],
                            onChange: function(value) {
                                setAttributes({ order: value });
                            }
                        })
                    ),

                    el(PanelBody, { 
                        title: 'Animation Settings',
                        initialOpen: false 
                    },
                        el(SelectControl, {
                            label: 'Animation Style',
                            value: attributes.animationStyle,
                            options: [
                                { label: 'Fade Up', value: 'fade-up' },
                                { label: 'Bounce In', value: 'bounce' }
                            ],
                            onChange: function(value) {
                                setAttributes({ animationStyle: value });
                            }
                        })
                    )
                ),

                el('div', { className: 'creative-art-emotions-preview' },
                    el(ServerSideRender, {
                        block: 'custom/creative-art-emotions',
                        attributes: attributes
                    })
                )
            );
        },

        save: function() {
            return null; // Server-side rendered
        }
    });

    // Also update the original art-emotions block to include custom link settings
    if (wp.blocks.getBlockType('custom/art-emotions')) {
        // Add a notice for the original block about custom links
        wp.hooks.addFilter(
            'editor.BlockEdit',
            'custom/add-custom-link-notice',
            function(BlockEdit) {
                return function(props) {
                    if (props.name === 'custom/art-emotions') {
                        return el(Fragment, {},
                            el('div', { 
                                className: 'components-notice is-info',
                                style: { marginBottom: '16px', padding: '8px 12px' }
                            }, 'Custom links can now be configured for each emotion in the taxonomy settings.'),
                            el(BlockEdit, props)
                        );
                    }
                    return el(BlockEdit, props);
                };
            }
        );
    }

})(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.data
);