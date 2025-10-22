# Hide Availability Column Feature

## Overview
This feature adds the ability to hide the availability column in both ticket and extra service tables, providing a cleaner and simplified booking interface for users.

## Feature Details

### What it does
- Adds a global setting to control the visibility of the availability column
- Applies to both main ticket tables and extra service tables
- Maintains proper table layout when column is hidden
- Uses global settings for consistent behavior across all tours

### Files Modified

#### 1. `admin/TTBM_Settings_Global.php`
- Added new setting option: `ttbm_hide_availability_column`
- Setting location: General Settings section
- Options: "Show Availability Column" (default) / "Hide Availability Column"

#### 2. `templates/ticket/regular_ticket.php`
- Added conditional logic to hide availability column header and data
- Adjusted table colspan for hidden input rows (3 columns instead of 4 when hidden)
- Uses global setting: `TTBM_Function::get_general_settings('ttbm_hide_availability_column', 'off')`

#### 3. `templates/ticket/extra_service.php`
- Added conditional logic to hide availability column header and data
- Adjusted table colspan for hidden input rows (3 columns instead of 4 when hidden)
- Uses global setting: `TTBM_Function::get_general_settings('ttbm_hide_availability_column', 'off')`

## How to Use

### For Administrators
1. Navigate to **Tour Booking Manager → Settings → General Settings**
2. Find the **"Hide Availability Column"** option
3. Select **"Hide Availability Column"** to hide the column
4. Select **"Show Availability Column"** to display it (default)
5. Save settings

### For Users
When the availability column is hidden:
- The booking interface becomes cleaner and more focused
- Users see only: Ticket Type, Price, and Quantity columns
- All functionality remains intact
- Layout automatically adjusts without design issues

## Technical Implementation

### Setting Structure
```php
array(
    'name' => 'ttbm_hide_availability_column',
    'label' => esc_html__('Hide Availability Column', 'tour-booking-manager'),
    'desc' => esc_html__('Hide the availability column in ticket and extra service tables to simplify the booking interface.', 'tour-booking-manager'),
    'type' => 'select',
    'default' => 'off',
    'options' => array(
        'off' => esc_html__('Show Availability Column', 'tour-booking-manager'),
        'on' => esc_html__('Hide Availability Column', 'tour-booking-manager')
    )
)
```

### Template Logic
```php
// Check if availability column should be hidden (global setting)
$hide_availability_column = TTBM_Function::get_general_settings('ttbm_hide_availability_column', 'off');

// Conditional display
<?php if ($hide_availability_column !== 'on') { ?>
    <th class="ttbm_availability_info"><?php esc_html_e('Availability', 'tour-booking-manager'); ?></th>
<?php } ?>

// Dynamic colspan
<td colspan="<?php echo $hide_availability_column === 'on' ? '3' : '4'; ?>">
```

## Benefits

1. **Cleaner Interface**: Removes clutter from booking tables
2. **Better UX**: Focuses user attention on essential booking information
3. **Flexible**: Administrators can choose to show or hide based on their needs
4. **Consistent**: Global setting ensures uniform behavior across all tours
5. **No Layout Issues**: Proper colspan adjustments maintain table integrity

## Compatibility

- ✅ Works with all existing ticket types
- ✅ Compatible with extra services
- ✅ Maintains all booking functionality
- ✅ No impact on existing data or settings
- ✅ Backward compatible (default shows availability column)

## Testing

### Test Cases
1. **Default State**: Verify availability column is shown by default
2. **Hidden State**: Verify column is hidden when setting is enabled
3. **Layout**: Ensure table layout remains proper in both states
4. **Functionality**: Confirm booking process works in both modes
5. **Extra Services**: Test with extra service tables
6. **Multiple Tours**: Verify setting applies globally

### Browser Compatibility
- ✅ Chrome
- ✅ Firefox  
- ✅ Safari
- ✅ Edge
- ✅ Mobile browsers

## Future Enhancements

Potential future improvements:
- Per-tour setting option (currently global only)
- Custom column visibility options
- Additional column hiding options (price, quantity, etc.)
- Admin preview of hidden columns

## Changelog

### Version 1.0.0
- Initial implementation of hide availability column feature
- Global setting added to General Settings
- Template modifications for both ticket and extra service tables
- Proper layout handling with dynamic colspan adjustments

---

**Branch**: `feature/hide-availability-column`  
**Commit**: `61c5d9c`  
**Date**: January 2025  
**Author**: AI Assistant
