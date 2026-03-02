// (function() {
//     const { registerBlockType } = wp.blocks;
//     const { InspectorControls } = wp.blockEditor;
//     const { PanelBody, TextControl } = wp.components;

//     registerBlockType('custom/art-gallery', {
//         title: 'Art Gallery',
//         icon: 'format-gallery',
//         category: 'art-blocks',

//         attributes: {
//             uploadUrl: {
//                 type: 'string',
//                 default: ''
//             }
//         },

//         edit: function(props) {
//             const { attributes, setAttributes } = props;

//             return [
//                 <InspectorControls>
//                     <PanelBody title="Gallery Settings">
//                         <TextControl
//                             label="Upload Button URL"
//                             value={attributes.uploadUrl}
//                             onChange={(url) => setAttributes({ uploadUrl: url })}
//                             help="Enter the URL where users will be directed to upload artwork"
//                         />
//                     </PanelBody>
//                 </InspectorControls>,
//                 <div className="art-gallery-block-editor">
//                     <div className="art-gallery-placeholder">
//                         <p>Art Gallery Block</p>
//                         {attributes.uploadUrl &&
//                             <p>Upload URL: {attributes.uploadUrl}</p>
//                         }
//                     </div>
//                 </div>
//             ];
//         },

//         save: function() {
//             return null;
//         }
//     });
// })();

(function() {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, TextControl, SelectControl } = wp.components;
    const { createElement, useState, useEffect } = wp.element;

    registerBlockType('custom/art-gallery', {
        title: 'Art Gallery',
        icon: 'format-gallery',
        category: 'art-blocks',

        attributes: {
            uploadUrl: {
                type: 'string',
                default: ''
            },
            artCategory: {
                type: 'string',
                default: ''
            }
        },

        edit: function(props) {
            const { attributes, setAttributes } = props;
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
                        console.warn('Art Gallery: could not load art categories', err);
                    });
            }, []);

            return createElement('div', null, [
                createElement(InspectorControls, { key: 'inspector' },
                    createElement(PanelBody, { title: 'Gallery Settings' },
                        createElement(TextControl, {
                            label: 'Upload Button URL',
                            value: attributes.uploadUrl,
                            onChange: (url) => setAttributes({ uploadUrl: url }),
                            help: 'Enter the URL where users will be directed to upload artwork'
                        }),
                        createElement(SelectControl, {
                            label: 'Category Filter',
                            value: attributes.artCategory,
                            options: categoryOptions,
                            onChange: (slug) => setAttributes({ artCategory: slug }),
                            help: 'Restrict this gallery to one category. Leave empty to show all.'
                        })
                    )
                ),
                createElement('div',
                    { key: 'editor', className: 'art-gallery-block-editor' },
                    createElement('div',
                        { className: 'art-gallery-placeholder' },
                        [
                            createElement('p', { key: 'title' }, 'Art Gallery Block'),
                            attributes.uploadUrl &&
                                createElement('p', { key: 'url' }, `Upload URL: ${attributes.uploadUrl}`),
                            attributes.artCategory &&
                                createElement('p', { key: 'cat' }, `Category: ${attributes.artCategory}`)
                        ]
                    )
                )
            ]);
        },

        save: function() {
            return null;
        }
    });
})();
