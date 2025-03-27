const { registerBlockType } = wp.blocks;
const { InspectorControls, BlockControls } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl, RangeControl, ToggleControl, ToolbarGroup, ToolbarButton } = wp.components;
const { __ } = wp.i18n;

// Common block settings
const commonSettings = {
    supports: {
        html: false,
        reusable: true,
        multiple: true,
        align: ['wide', 'full'],
    },
};

// Register Top Search Block
registerBlockType('tour-booking-manager/top-search', {
    ...commonSettings,
    title: __('Tour Top Search', 'tour-booking-manager'),
    icon: 'search',
    category: 'tour-booking-manager',
    edit: function(props) {
        const { isSelected } = props;
        return (
            <div className={props.className}>
                {isSelected && (
                    <BlockControls>
                        <ToolbarGroup>
                            <ToolbarButton
                                icon="trash"
                                label={__('Delete block', 'tour-booking-manager')}
                                onClick={() => props.onReplace([])}
                            />
                        </ToolbarGroup>
                    </BlockControls>
                )}
                <div className="ttbm-block-preview">
                    {__('Tour Top Search Filter', 'tour-booking-manager')}
                </div>
            </div>
        );
    },
    save: function() {
        return null; // Dynamic block
    }
});

// Register Travel List Block
registerBlockType('tour-booking-manager/travel-list', {
    ...commonSettings,
    title: __('Tour List', 'tour-booking-manager'),
    icon: 'list-view',
    category: 'tour-booking-manager',
    attributes: {
        style: {
            type: 'string',
            default: 'modern'
        },
        show: {
            type: 'number',
            default: 12
        },
        pagination: {
            type: 'string',
            default: 'yes'
        },
        'sidebar-filter': {
            type: 'string',
            default: 'yes'
        },
        column: {
            type: 'number',
            default: 3
        }
    },
    edit: function(props) {
        const { attributes, setAttributes, isSelected } = props;

        return (
            <div className={props.className}>
                {isSelected && (
                    <BlockControls>
                        <ToolbarGroup>
                            <ToolbarButton
                                icon="trash"
                                label={__('Delete block', 'tour-booking-manager')}
                                onClick={() => props.onReplace([])}
                            />
                        </ToolbarGroup>
                    </BlockControls>
                )}
                <InspectorControls>
                    <PanelBody title={__('Tour List Settings', 'tour-booking-manager')}>
                        <SelectControl
                            label={__('Style', 'tour-booking-manager')}
                            value={attributes.style}
                            options={[
                                { label: 'Modern', value: 'modern' },
                                { label: 'Grid', value: 'grid' },
                                { label: 'List', value: 'list' }
                            ]}
                            onChange={(value) => setAttributes({ style: value })}
                        />
                        <RangeControl
                            label={__('Show', 'tour-booking-manager')}
                            value={attributes.show}
                            onChange={(value) => setAttributes({ show: value })}
                            min={1}
                            max={24}
                        />
                        <ToggleControl
                            label={__('Pagination', 'tour-booking-manager')}
                            checked={attributes.pagination === 'yes'}
                            onChange={(value) => setAttributes({ pagination: value ? 'yes' : 'no' })}
                        />
                        <ToggleControl
                            label={__('Sidebar Filter', 'tour-booking-manager')}
                            checked={attributes['sidebar-filter'] === 'yes'}
                            onChange={(value) => setAttributes({ 'sidebar-filter': value ? 'yes' : 'no' })}
                        />
                        <RangeControl
                            label={__('Columns', 'tour-booking-manager')}
                            value={attributes.column}
                            onChange={(value) => setAttributes({ column: value })}
                            min={1}
                            max={4}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className="ttbm-block-preview">
                    {__('Tour List Display', 'tour-booking-manager')}
                </div>
            </div>
        );
    },
    save: function() {
        return null; // Dynamic block
    }
});

// Register Top Filter Block
registerBlockType('tour-booking-manager/top-filter', {
    ...commonSettings,
    title: __('Tour Top Filter', 'tour-booking-manager'),
    icon: 'filter',
    category: 'tour-booking-manager',
    attributes: {
        show: {
            type: 'number',
            default: 12
        },
        pagination: {
            type: 'string',
            default: 'yes'
        },
        'search-filter': {
            type: 'string',
            default: 'yes'
        }
    },
    edit: function(props) {
        const { attributes, setAttributes, isSelected } = props;

        return (
            <div className={props.className}>
                {isSelected && (
                    <BlockControls>
                        <ToolbarGroup>
                            <ToolbarButton
                                icon="trash"
                                label={__('Delete block', 'tour-booking-manager')}
                                onClick={() => props.onReplace([])}
                            />
                        </ToolbarGroup>
                    </BlockControls>
                )}
                <InspectorControls>
                    <PanelBody title={__('Top Filter Settings', 'tour-booking-manager')}>
                        <RangeControl
                            label={__('Show', 'tour-booking-manager')}
                            value={attributes.show}
                            onChange={(value) => setAttributes({ show: value })}
                            min={1}
                            max={24}
                        />
                        <ToggleControl
                            label={__('Pagination', 'tour-booking-manager')}
                            checked={attributes.pagination === 'yes'}
                            onChange={(value) => setAttributes({ pagination: value ? 'yes' : 'no' })}
                        />
                        <ToggleControl
                            label={__('Search Filter', 'tour-booking-manager')}
                            checked={attributes['search-filter'] === 'yes'}
                            onChange={(value) => setAttributes({ 'search-filter': value ? 'yes' : 'no' })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className="ttbm-block-preview">
                    {__('Tour Top Filter Display', 'tour-booking-manager')}
                </div>
            </div>
        );
    },
    save: function() {
        return null; // Dynamic block
    }
});

// Register Location List Block
registerBlockType('tour-booking-manager/location-list', {
    ...commonSettings,
    title: __('Tour Location List', 'tour-booking-manager'),
    icon: 'location',
    category: 'tour-booking-manager',
    edit: function(props) {
        const { isSelected } = props;
        return (
            <div className={props.className}>
                {isSelected && (
                    <BlockControls>
                        <ToolbarGroup>
                            <ToolbarButton
                                icon="trash"
                                label={__('Delete block', 'tour-booking-manager')}
                                onClick={() => props.onReplace([])}
                            />
                        </ToolbarGroup>
                    </BlockControls>
                )}
                <div className="ttbm-block-preview">
                    {__('Tour Location List Display', 'tour-booking-manager')}
                </div>
            </div>
        );
    },
    save: function() {
        return null; // Dynamic block
    }
});

// Register Search Result Block
registerBlockType('tour-booking-manager/search-result', {
    ...commonSettings,
    title: __('Tour Search Result', 'tour-booking-manager'),
    icon: 'search',
    category: 'tour-booking-manager',
    edit: function(props) {
        const { isSelected } = props;
        return (
            <div className={props.className}>
                {isSelected && (
                    <BlockControls>
                        <ToolbarGroup>
                            <ToolbarButton
                                icon="trash"
                                label={__('Delete block', 'tour-booking-manager')}
                                onClick={() => props.onReplace([])}
                            />
                        </ToolbarGroup>
                    </BlockControls>
                )}
                <div className="ttbm-block-preview">
                    {__('Tour Search Result Display', 'tour-booking-manager')}
                </div>
            </div>
        );
    },
    save: function() {
        return null; // Dynamic block
    }
});

// Register Hotel List Block
registerBlockType('tour-booking-manager/hotel-list', {
    ...commonSettings,
    title: __('Tour Hotel List', 'tour-booking-manager'),
    icon: 'building',
    category: 'tour-booking-manager',
    attributes: {
        show: {
            type: 'number',
            default: 12
        },
        pagination: {
            type: 'string',
            default: 'yes'
        }
    },
    edit: function(props) {
        const { attributes, setAttributes, isSelected } = props;

        return (
            <div className={props.className}>
                {isSelected && (
                    <BlockControls>
                        <ToolbarGroup>
                            <ToolbarButton
                                icon="trash"
                                label={__('Delete block', 'tour-booking-manager')}
                                onClick={() => props.onReplace([])}
                            />
                        </ToolbarGroup>
                    </BlockControls>
                )}
                <InspectorControls>
                    <PanelBody title={__('Hotel List Settings', 'tour-booking-manager')}>
                        <RangeControl
                            label={__('Show', 'tour-booking-manager')}
                            value={attributes.show}
                            onChange={(value) => setAttributes({ show: value })}
                            min={1}
                            max={24}
                        />
                        <ToggleControl
                            label={__('Pagination', 'tour-booking-manager')}
                            checked={attributes.pagination === 'yes'}
                            onChange={(value) => setAttributes({ pagination: value ? 'yes' : 'no' })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className="ttbm-block-preview">
                    {__('Tour Hotel List Display', 'tour-booking-manager')}
                </div>
            </div>
        );
    },
    save: function() {
        return null; // Dynamic block
    }
});

// Register Registration Block
registerBlockType('tour-booking-manager/registration', {
    ...commonSettings,
    title: __('Tour Registration', 'tour-booking-manager'),
    icon: 'clipboard',
    category: 'tour-booking-manager',
    attributes: {
        ttbm_id: {
            type: 'string',
            default: ''
        }
    },
    edit: function(props) {
        const { attributes, setAttributes, isSelected } = props;

        return (
            <div className={props.className}>
                {isSelected && (
                    <BlockControls>
                        <ToolbarGroup>
                            <ToolbarButton
                                icon="trash"
                                label={__('Delete block', 'tour-booking-manager')}
                                onClick={() => props.onReplace([])}
                            />
                        </ToolbarGroup>
                    </BlockControls>
                )}
                <InspectorControls>
                    <PanelBody title={__('Registration Settings', 'tour-booking-manager')}>
                        <TextControl
                            label={__('Tour ID', 'tour-booking-manager')}
                            value={attributes.ttbm_id}
                            onChange={(value) => setAttributes({ ttbm_id: value })}
                            help={__('Leave empty to use current tour', 'tour-booking-manager')}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className="ttbm-block-preview">
                    {__('Tour Registration Form', 'tour-booking-manager')}
                </div>
            </div>
        );
    },
    save: function() {
        return null; // Dynamic block
    }
});

// Register Related Block
registerBlockType('tour-booking-manager/related', {
    ...commonSettings,
    title: __('Related Tours', 'tour-booking-manager'),
    icon: 'admin-links',
    category: 'tour-booking-manager',
    attributes: {
        ttbm_id: {
            type: 'string',
            default: ''
        },
        show: {
            type: 'number',
            default: 4
        }
    },
    edit: function(props) {
        const { attributes, setAttributes, isSelected } = props;

        return (
            <div className={props.className}>
                {isSelected && (
                    <BlockControls>
                        <ToolbarGroup>
                            <ToolbarButton
                                icon="trash"
                                label={__('Delete block', 'tour-booking-manager')}
                                onClick={() => props.onReplace([])}
                            />
                        </ToolbarGroup>
                    </BlockControls>
                )}
                <InspectorControls>
                    <PanelBody title={__('Related Tours Settings', 'tour-booking-manager')}>
                        <TextControl
                            label={__('Tour ID', 'tour-booking-manager')}
                            value={attributes.ttbm_id}
                            onChange={(value) => setAttributes({ ttbm_id: value })}
                            help={__('Leave empty to use current tour', 'tour-booking-manager')}
                        />
                        <RangeControl
                            label={__('Show', 'tour-booking-manager')}
                            value={attributes.show}
                            onChange={(value) => setAttributes({ show: value })}
                            min={1}
                            max={12}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className="ttbm-block-preview">
                    {__('Related Tours Display', 'tour-booking-manager')}
                </div>
            </div>
        );
    },
    save: function() {
        return null; // Dynamic block
    }
}); 