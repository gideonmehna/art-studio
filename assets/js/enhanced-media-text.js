(function() {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls, MediaUpload, MediaUploadCheck, InnerBlocks, BlockControls, MediaReplaceFlow } = wp.blockEditor;
    const { PanelBody, Button, SelectControl, ToggleControl, ToolbarGroup, ToolbarButton } = wp.components;
    const { Fragment, useState, createElement } = wp.element;
    const { __ } = wp.i18n;

    const ALLOWED_BLOCKS = [
        'core/paragraph',
        'core/heading',
        'core/list',
        'core/button',
        'core/buttons',
        'core/image',
        'core/quote',
        'core/spacer',
        'art-studio/custom-button'
    ];

    const TEMPLATE = [
        ['core/paragraph', { placeholder: 'Add your content here...' }]
    ];

    registerBlockType('custom/enhanced-media-text', {
        title: __('Enhanced Media Text'),
        description: __('A media and text block with an additional third image.'),
        category: 'art-blocks',
        icon: 'align-pull-left',
        keywords: [__('media'), __('text'), __('image')],
        supports: {
            align: ['wide', 'full'],
            html: false,
            color: {
                gradients: true,
                link: true,
                __experimentalDefaultControls: {
                    background: true,
                    text: true,
                }
            },
            spacing: {
                margin: true,
                padding: true,
            },
            typography: {
                fontSize: true,
                lineHeight: true,
                __experimentalFontFamily: true,
                __experimentalFontWeight: true,
                __experimentalFontStyle: true,
                __experimentalTextTransform: true,
                __experimentalTextDecoration: true,
                __experimentalLetterSpacing: true,
                __experimentalDefaultControls: {
                    fontSize: true
                }
            }
        },
        attributes: {
            mediaId: {
                type: 'number',
                default: 0,
            },
            mediaUrl: {
                type: 'string',
                default: '',
            },
            mediaAlt: {
                type: 'string',
                default: '',
            },
            thirdImageId: {
                type: 'number',
                default: 0,
            },
            thirdImageUrl: {
                type: 'string',
                default: '',
            },
            thirdImageAlt: {
                type: 'string',
                default: '',
            },
            thirdImagePosition: {
                type: 'string',
                default: 'top',
            },
            mediaPosition: {
                type: 'string',
                default: 'left',
            },
            verticalAlignment: {
                type: 'string',
                default: 'center',
            },
        },

        edit: function(props) {
            const { attributes, setAttributes, className } = props;
            const { 
                mediaId, 
                mediaUrl, 
                mediaAlt, 
                thirdImageId, 
                thirdImageUrl, 
                thirdImageAlt, 
                thirdImagePosition, 
                mediaPosition, 
                verticalAlignment 
            } = attributes;

            const onSelectMedia = function(media) {
                setAttributes({
                    mediaId: media.id,
                    mediaUrl: media.url,
                    mediaAlt: media.alt,
                });
            };

            const onSelectThirdImage = function(media) {
                setAttributes({
                    thirdImageId: media.id,
                    thirdImageUrl: media.url,
                    thirdImageAlt: media.alt,
                });
            };

            const removeMedia = function() {
                setAttributes({
                    mediaId: 0,
                    mediaUrl: '',
                    mediaAlt: '',
                });
            };

            const removeThirdImage = function() {
                setAttributes({
                    thirdImageId: 0,
                    thirdImageUrl: '',
                    thirdImageAlt: '',
                });
            };

            var blockClasses = [
                className,
                'wp-block-custom-enhanced-media-text',
                'has-media-on-the-' + mediaPosition,
                'is-vertically-aligned-' + verticalAlignment,
                'third-image-' + thirdImagePosition,
            ];

            if (thirdImageUrl) {
                blockClasses.push('has-third-image');
            }

            // Helper for rendering third image
            function renderThirdImage(positionClass) {
                return createElement(
                    'div',
                    { className: 'wp-block-custom-enhanced-media-text__third-image ' + positionClass },
                    createElement('img', { src: thirdImageUrl, alt: thirdImageAlt })
                );
            }

            // Helper for rendering media upload button
            function renderMediaUpload(mediaId, mediaUrl, mediaAlt, onSelect, removeHandler, setLabel) {
                return [
                    createElement(
                        MediaUploadCheck,
                        {},
                        createElement(MediaUpload, {
                            onSelect: onSelect,
                            allowedTypes: ['image'],
                            value: mediaId,
                            render: function(obj) {
                                return createElement(
                                    Button,
                                    {
                                        className: mediaId === 0 ? 'editor-post-featured-image__toggle' : 'editor-post-featured-image__preview',
                                        onClick: obj.open
                                    },
                                    mediaId === 0 ? setLabel : (mediaUrl ? createElement('img', { src: mediaUrl, alt: mediaAlt }) : null)
                                );
                            }
                        })
                    ),
                    mediaId !== 0 && createElement(
                        Button,
                        {
                            onClick: removeHandler,
                            isDestructive: true,
                            className: "remove-media-button"
                        },
                        __('Remove Media')
                    )
                ];
            }

            // Helper for rendering third image upload button
            function renderThirdImageUpload() {
                return [
                    createElement(
                        MediaUploadCheck,
                        {},
                        createElement(MediaUpload, {
                            onSelect: onSelectThirdImage,
                            allowedTypes: ['image'],
                            value: thirdImageId,
                            render: function(obj) {
                                return createElement(
                                    Button,
                                    {
                                        className: thirdImageId === 0 ? 'editor-post-featured-image__toggle' : 'editor-post-featured-image__preview',
                                        onClick: obj.open
                                    },
                                    thirdImageId === 0 ? __('Set Third Image') : (thirdImageUrl ? createElement('img', { src: thirdImageUrl, alt: thirdImageAlt }) : null)
                                );
                            }
                        })
                    ),
                    thirdImageId !== 0 && createElement(
                        Button,
                        {
                            onClick: removeThirdImage,
                            isDestructive: true
                        },
                        __('Remove Third Image')
                    )
                ];
            }

            return createElement(
                Fragment,
                {},
                createElement(
                    BlockControls,
                    {},
                    createElement(
                        ToolbarGroup,
                        {},
                        createElement(
                            ToolbarButton,
                            {
                                icon: "align-pull-left",
                                title: __('Media on left'),
                                isActive: mediaPosition === 'left',
                                onClick: function() { setAttributes({ mediaPosition: 'left' }); }
                            }
                        ),
                        createElement(
                            ToolbarButton,
                            {
                                icon: "align-pull-right",
                                title: __('Media on right'),
                                isActive: mediaPosition === 'right',
                                onClick: function() { setAttributes({ mediaPosition: 'right' }); }
                            }
                        )
                    )
                ),
                createElement(
                    InspectorControls,
                    {},
                    createElement(
                        PanelBody,
                        { title: __('Media Settings'), initialOpen: true },
                        createElement(
                            SelectControl,
                            {
                                label: __('Vertical Alignment'),
                                value: verticalAlignment,
                                options: [
                                    { label: __('Top'), value: 'top' },
                                    { label: __('Center'), value: 'center' },
                                    { label: __('Bottom'), value: 'bottom' },
                                ],
                                onChange: function(value) { setAttributes({ verticalAlignment: value }); }
                            }
                        )
                    ),
                    createElement(
                        PanelBody,
                        { title: __('Third Image Settings'), initialOpen: true },
                        createElement(
                            SelectControl,
                            {
                                label: __('Third Image Position'),
                                value: thirdImagePosition,
                                options: [
                                    { label: __('Top Right'), value: 'top' },
                                    { label: __('Middle Right'), value: 'middle' },
                                    { label: __('Bottom Right'), value: 'bottom' },
                                ],
                                onChange: function(value) { setAttributes({ thirdImagePosition: value }); }
                            }
                        ),
                        renderThirdImageUpload()
                    )
                ),
                createElement(
                    'div',
                    { className: blockClasses.join(' ') },
                    mediaPosition === 'left' &&
                        createElement(
                            'div',
                            { className: "wp-block-custom-enhanced-media-text__media" },
                            renderMediaUpload(mediaId, mediaUrl, mediaAlt, onSelectMedia, removeMedia, __('Set Media'))
                        ),
                    createElement(
                        'div',
                        { className: "wp-block-custom-enhanced-media-text__content" },
                        thirdImageUrl && thirdImagePosition === 'top' && renderThirdImage('third-image-top'),
                        createElement(
                            'div',
                            { className: "wp-block-custom-enhanced-media-text__text" },
                            createElement(InnerBlocks, {
                                allowedBlocks: ALLOWED_BLOCKS,
                                template: TEMPLATE
                            })
                        ),
                        thirdImageUrl && thirdImagePosition === 'bottom' && renderThirdImage('third-image-bottom'),
                        thirdImageUrl && thirdImagePosition === 'middle' && renderThirdImage('third-image-middle')
                    ),
                    mediaPosition === 'right' &&
                        createElement(
                            'div',
                            { className: "wp-block-custom-enhanced-media-text__media" },
                            renderMediaUpload(mediaId, mediaUrl, mediaAlt, onSelectMedia, removeMedia, __('Set Media'))
                        )
                )
            );
        },

        save: function() {
            return createElement(InnerBlocks.Content, null);
        },
    });
})();