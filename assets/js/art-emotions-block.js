(function() {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, RangeControl, ToggleControl, SelectControl } = wp.components;
    const { createElement, Fragment } = wp.element;
    const { __ } = wp.i18n;
    const { withSelect } = wp.data;

    // Register the Art Emotions block
    registerBlockType('custom/art-emotions', {
        title: __('Art Emotions Grid', 'textdomain'),
        description: __('Display art emotions with featured images in a grid layout.', 'textdomain'),
        icon: 'art',
        category: 'art-blocks',
        attributes: {
            columns: {
                type: 'number',
                default: 3,
            },
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
        },

        edit: withSelect((select) => {
            const { getEntityRecords } = select('core');
            return {
                emotions: getEntityRecords('taxonomy', 'art_emotion', { per_page: -1 }) || []
            };
        })(function({ attributes, setAttributes, emotions }) {
            const { columns, showTitle, imageSize, orderBy, order, showCount } = attributes;

            // Preview component
            const EmotionPreview = ({ emotion }) => {
                const featuredImageUrl = emotion.meta?.featured_image ? 
                    `Featured image (ID: ${emotion.meta.featured_image})` : 
                    emotion.name.charAt(0);

                return createElement(
                    'div',
                    { className: 'art-emotion-item-preview' },
                    createElement(
                        'div',
                        { className: 'art-emotion-image-preview' },
                        emotion.meta?.featured_image ? 
                            createElement('span', { className: 'has-image' }, '🖼️') :
                            createElement('span', { className: 'emotion-initial' }, featuredImageUrl)
                    ),
                    (showTitle || showCount) && createElement(
                        'div',
                        { className: 'art-emotion-overlay-preview' },
                        showTitle && createElement('h3', null, emotion.name),
                        showCount && createElement('span', { className: 'count' }, `${emotion.count} pieces`)
                    )
                );
            };

            return createElement(
                Fragment,
                null,
                createElement(
                    InspectorControls,
                    null,
                    createElement(
                        PanelBody,
                        { title: __('Layout Settings', 'textdomain') },
                        createElement(RangeControl, {
                            label: __('Columns', 'textdomain'),
                            value: columns,
                            onChange: (value) => setAttributes({ columns: value }),
                            min: 1,
                            max: 6,
                        }),
                        createElement(SelectControl, {
                            label: __('Image Size', 'textdomain'),
                            value: imageSize,
                            options: [
                                { label: __('Thumbnail', 'textdomain'), value: 'thumbnail' },
                                { label: __('Medium', 'textdomain'), value: 'medium' },
                                { label: __('Large', 'textdomain'), value: 'large' },
                                { label: __('Full', 'textdomain'), value: 'full' },
                            ],
                            onChange: (value) => setAttributes({ imageSize: value }),
                        })
                    ),
                    createElement(
                        PanelBody,
                        { title: __('Display Settings', 'textdomain') },
                        createElement(ToggleControl, {
                            label: __('Show Emotion Names', 'textdomain'),
                            checked: showTitle,
                            onChange: (value) => setAttributes({ showTitle: value }),
                        }),
                        createElement(ToggleControl, {
                            label: __('Show Art Piece Count', 'textdomain'),
                            checked: showCount,
                            onChange: (value) => setAttributes({ showCount: value }),
                        })
                    ),
                    createElement(
                        PanelBody,
                        { title: __('Sorting', 'textdomain') },
                        createElement(SelectControl, {
                            label: __('Order By', 'textdomain'),
                            value: orderBy,
                            options: [
                                { label: __('Name', 'textdomain'), value: 'name' },
                                { label: __('Count', 'textdomain'), value: 'count' },
                                { label: __('Term ID', 'textdomain'), value: 'term_id' },
                            ],
                            onChange: (value) => setAttributes({ orderBy: value }),
                        }),
                        createElement(SelectControl, {
                            label: __('Order', 'textdomain'),
                            value: order,
                            options: [
                                { label: __('Ascending', 'textdomain'), value: 'ASC' },
                                { label: __('Descending', 'textdomain'), value: 'DESC' },
                            ],
                            onChange: (value) => setAttributes({ order: value }),
                        })
                    )
                ),
                createElement(
                    'div',
                    { 
                        className: 'art-emotions-grid-preview',
                        style: { '--columns': columns }
                    },
                    emotions.length > 0 ? 
                        emotions.map(emotion => createElement(EmotionPreview, { 
                            key: emotion.id, 
                            emotion: emotion 
                        })) :
                        createElement('p', null, __('Loading art emotions...', 'textdomain'))
                )
            );
        }),

        save: function() {
            // Dynamic block, so save returns null
            return null;
        },
    });
})();