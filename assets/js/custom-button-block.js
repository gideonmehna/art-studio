// js/custom-button-block.js
(function(blocks, editor, components, element) {
    var el = element.createElement;
    var registerBlockType = blocks.registerBlockType;
    var RichText = editor.RichText;
    var URLInput = editor.URLInput;
    var InspectorControls = editor.InspectorControls;
    var PanelBody = components.PanelBody;
    var ToggleControl = components.ToggleControl;
    var TextControl = components.TextControl;

    registerBlockType('art-studio/custom-button', {
        title: 'Custom Button',
        icon: 'button',
        category: 'art-blocks',
        attributes: {
            buttonText: {
                type: 'string',
                default: 'Learn more'
            },
            buttonUrl: {
                type: 'string',
                default: '#'
            },
            showArrow: {
                type: 'boolean',
                default: true
            },
            underline: {
                type: 'boolean',
                default: true
            },
            openInNewTab: {
                type: 'boolean',
                default: false
            }
        },

        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            function onChangeButtonText(newText) {
                setAttributes({ buttonText: newText });
            }

            function onChangeButtonUrl(newUrl) {
                setAttributes({ buttonUrl: newUrl });
            }

            function onToggleArrow(newValue) {
                setAttributes({ showArrow: newValue });
            }
            function onToggleUnderline(newValue) {
                setAttributes({ underline: newValue });
            }

            function onToggleNewTab(newValue) {
                setAttributes({ openInNewTab: newValue });
            }

            // Arrow SVG
            var arrowSvg = el('svg', {
                className: 'art-studio-button-arrow',
                width: 44,
                height: 16,
                viewBox: '0 0 44 16',
                fill: 'none'
            }, el('path', {
                d: 'M1 8H30M30 8L23 1M30 8L23 15',
                stroke: 'currentColor',
                strokeWidth: 1.5,
                strokeLinecap: 'round',
                strokeLinejoin: 'round'
            }));

            return el('div', {}, [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Button Settings', initialOpen: true }, [
                        el(TextControl, {
                            label: 'Button URL',
                            value: attributes.buttonUrl,
                            onChange: onChangeButtonUrl,
                            placeholder: 'https://example.com'
                        }),
                        el(ToggleControl, {
                            label: 'Show Arrow',
                            checked: attributes.showArrow,
                            onChange: onToggleArrow
                        }),
                        el(ToggleControl, {
                            label: 'Underline',
                            checked: attributes.underline,
                            onChange: onToggleUnderline
                        }),
                        el(ToggleControl, {
                            label: 'Open in New Tab',
                            checked: attributes.openInNewTab,
                            onChange: onToggleNewTab
                        })
                    ])
                ),
                el('div', { className: 'art-studio-custom-button-wrapper' },
                    el('div', { className: 'art-studio-custom-button' + (attributes.underline ? ' art-studio-button-underline' : '')
                        
                    }, [
                        el(RichText, {
                            tagName: 'span',
                            className: 'art-studio-button-text',
                            value: attributes.buttonText,
                            onChange: onChangeButtonText,
                            placeholder: 'Enter button text...',
                            allowedFormats: []
                        }),
                        attributes.showArrow && arrowSvg
                    ])
                )
            ]);
        },

        save: function(props) {
            var attributes = props.attributes;

            // Arrow SVG
            var arrowSvg = el('svg', {
                className: 'art-studio-button-arrow',
                width: 44,
                height: 16,
                viewBox: '0 0 44 16',
                fill: 'none'
            }, el('path', {
                d: 'M1 8H30M30 8L23 1M30 8L23 15',
                stroke: 'currentColor',
                strokeWidth: 1.5,
                strokeLinecap: 'round',
                strokeLinejoin: 'round'
            }));

            var target = attributes.openInNewTab ? '_blank' : null;
            var rel = attributes.openInNewTab ? 'noopener noreferrer' : null;

            return el('div', { className: 'art-studio-custom-button-wrapper' },
                el('a', {
                    className: 'art-studio-custom-button'  + (attributes.underline ? ' art-studio-button-underline' : ''),
                    href: attributes.buttonUrl,
                    target: target,
                    rel: rel
                }, [
                    el('span', { className: 'art-studio-button-text' }, attributes.buttonText),
                    attributes.showArrow && arrowSvg
                ])
            );
        }
    });
})(
    window.wp.blocks,
    window.wp.editor,
    window.wp.components,
    window.wp.element
);