(function() {
    let { registerBlockType } = wp.blocks;
    let { createElement } = wp.element;

    registerBlockType('custom/art-gallery', {
        title: 'Art Gallery',
        icon: 'images-alt2',
        category: 'art-blocks',
        description: 'Interactive art gallery with filtering and load more functionality',
        
        edit: function(props) {
            return createElement(
                'div',
                {
                    className: 'art-gallery-block-editor',
                    style: {
                        padding: '20px',
                        border: '2px dashed #ccc',
                        borderRadius: '8px',
                        textAlign: 'center',
                        backgroundColor: '#f9f9f9'
                    }
                },
                createElement('h3', null, 'Art Gallery Block'),
                createElement('p', null, 'This block will display an interactive art gallery with filtering options.'),
                createElement('p', { style: { fontSize: '14px', color: '#666' } }, 'Preview only available on the frontend.')
            );
        },
        
        save: function() {
            return null; // Dynamic block, rendered by PHP
        }
    });
})();