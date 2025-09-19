(function() {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, TextControl } = wp.components;

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
            
            return [
                <InspectorControls>
                    <PanelBody title="Gallery Settings">
                        <TextControl
                            label="Upload Button URL"
                            value={attributes.uploadUrl}
                            onChange={(url) => setAttributes({ uploadUrl: url })}
                            help="Enter the URL where users will be directed to upload artwork"
                        />
                    </PanelBody>
                </InspectorControls>,
                <div className="art-gallery-block-editor">
                    <div className="art-gallery-placeholder">
                        <p>Art Gallery Block</p>
                        {attributes.uploadUrl && 
                            <p>Upload URL: {attributes.uploadUrl}</p>
                        }
                    </div>
                </div>
            ];
        },
        
        save: function() {
            return null;
        }
    });
})();