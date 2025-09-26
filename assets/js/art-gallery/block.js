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
    const { PanelBody, TextControl } = wp.components;
    const { createElement } = wp.element; // Add this

    registerBlockType('custom/art-gallery', {
        title: 'Art Gallery',
        icon: 'format-gallery',
        category: 'art-blocks',
        
        attributes: {
            uploadUrl: {
                type: 'string',
                default: ''
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            
            return createElement('div', null, [
                createElement(InspectorControls, { key: 'inspector' },
                    createElement(PanelBody, { title: 'Gallery Settings' },
                        createElement(TextControl, {
                            label: 'Upload Button URL',
                            value: attributes.uploadUrl,
                            onChange: (url) => setAttributes({ uploadUrl: url }),
                            help: 'Enter the URL where users will be directed to upload artwork'
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
                                createElement('p', { key: 'url' }, `Upload URL: ${attributes.uploadUrl}`)
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