(function() {
    const { createElement, Fragment } = wp.element;
    const { __ } = wp.i18n;
    const { MediaUpload, MediaUploadCheck } = wp.blockEditor;
    const { Button, PanelBody, PanelRow } = wp.components;
    const { PluginDocumentSettingPanel } = wp.editPost;
    const { registerPlugin } = wp.plugins;
    const { withSelect, withDispatch } = wp.data;
    const { compose } = wp.compose;

    // Art Emotion Featured Image Component
    const ArtEmotionFeaturedImage = compose(
        withSelect((select) => {
            const { getEditedPostAttribute } = select('core/editor');
            const { getEntityRecord } = select('core');
            
            const postType = select('core/editor').getCurrentPostType();
            if (postType !== 'art_piece') return {};
            
            const artEmotions = getEditedPostAttribute('art_emotion') || [];
            let emotionData = null;
            
            if (artEmotions.length > 0) {
                emotionData = getEntityRecord('taxonomy', 'art_emotion', artEmotions[0]);
            }
            
            return {
                artEmotions,
                emotionData
            };
        }),
        withDispatch((dispatch) => {
            return {
                updateEmotion: (termId, meta) => {
                    dispatch('core').saveEntityRecord('taxonomy', 'art_emotion', termId, meta);
                }
            };
        })
    )(({ artEmotions, emotionData, updateEmotion }) => {
        if (!artEmotions || artEmotions.length === 0) {
            return createElement(
                PanelBody,
                {
                    title: __('Art Emotion Featured Image', 'textdomain'),
                    initialOpen: false
                },
                createElement(
                    'p',
                    { style: { fontStyle: 'italic', color: '#666' } },
                    __('Please select an art emotion first to set a featured image.', 'textdomain')
                )
            );
        }

        const currentImageId = emotionData?.meta?.featured_image || null;
        const currentImageUrl = emotionData?.featured_image_data?.thumbnail || null;

        return createElement(
            PanelBody,
            {
                title: __('Art Emotion Featured Image', 'textdomain'),
                initialOpen: false
            },
            createElement(
                PanelRow,
                null,
                createElement(
                    'div',
                    { style: { width: '100%' } },
                    currentImageUrl && createElement(
                        'div',
                        { style: { marginBottom: '10px' } },
                        createElement('img', {
                            src: currentImageUrl,
                            style: { maxWidth: '100%', height: 'auto', maxHeight: '150px' }
                        })
                    ),
                    createElement(
                        MediaUploadCheck,
                        null,
                        createElement(
                            MediaUpload,
                            {
                                onSelect: (media) => {
                                    updateEmotion(artEmotions[0], {
                                        featured_image: media.id
                                    });
                                },
                                allowedTypes: ['image'],
                                value: currentImageId,
                                render: ({ open }) => createElement(
                                    Button,
                                    {
                                        onClick: open,
                                        isPrimary: !currentImageId,
                                        isSecondary: !!currentImageId,
                                        style: { marginRight: '10px' }
                                    },
                                    currentImageId ? __('Change Featured Image', 'textdomain') : __('Set Featured Image', 'textdomain')
                                )
                            }
                        )
                    ),
                    currentImageId && createElement(
                        Button,
                        {
                            onClick: () => {
                                updateEmotion(artEmotions[0], {
                                    featured_image: null
                                });
                            },
                            isDestructive: true,
                            variant: 'secondary'
                        },
                        __('Remove Featured Image', 'textdomain')
                    ),
                    createElement(
                        'p',
                        { 
                            style: { 
                                fontSize: '12px', 
                                color: '#666', 
                                marginTop: '10px',
                                fontStyle: 'italic'
                            } 
                        },
                        __('This image represents the emotion category and will be displayed alongside art pieces in this emotion.', 'textdomain')
                    )
                )
            )
        );
    });

    // Register the plugin
    registerPlugin('art-emotion-featured-image', {
        render: () => createElement(
            Fragment,
            null,
            createElement(
                PluginDocumentSettingPanel,
                {
                    name: 'art-emotion-featured-image',
                    title: __('Art Emotion Featured Image', 'textdomain'),
                    className: 'art-emotion-featured-image-panel'
                },
                createElement(ArtEmotionFeaturedImage)
            )
        )
    });
})();