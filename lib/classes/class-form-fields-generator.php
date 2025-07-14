<?php
if ( ! defined('ABSPATH')) exit;  // if direct access 
// phpcs:disable

/*Input fields
*  Text
*  Select
*  Checkbox
*  Checkbox Multi
*  Radio
*  Textarea
*  Number
*  Hidden
*  Range
*  Color
*  Email
*  URL
*  Tel
*  Search
*  Month
*  Week
*  Date
*  Time
*  Submit
 *
 *
*  Text multi
*  Select multi
*  Select2
*  Range with input
*  Color picker
*  Datepicker
*  Media
*  Media Gallery
*  Switcher
*  Switch
*  Switch multi
*  Switch image
*  Dimensions (width, height, custom)
*  WP Editor
*  Code Editor
*  Link Color
*  Repeatable
*  Icon
*  Icon multi
*  Date format
*  Time format
*  FAQ
*  Grid
*  Custom_html
*  Color palette
*  Color palette multi
 * Color set
*  User select
*  Color picker multi
*  Google reCaptcha
*  Nonce
*  Border
*  Margin
*  Padding
*  Google Map
*  Image Select
 *
 *
 * Background
 *
 * Typography
 * Spinner


*/






if( ! class_exists( 'FormFieldsGenerator' ) ) {

    class FormFieldsGenerator {




        public function field_post_objects( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $sortable 	    = isset( $option['sortable'] ) ? $option['sortable'] : true;
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : array();

            $values 	    = !empty( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($values) ? $values : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            if(!empty($values)):

                foreach ($values as $value):
                    $values_sort[$value] = $value;
                endforeach;
                $args = array_replace($values_sort, $args);
            endif;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';
            endif;
            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field';
                    ?> field-wrapper field-post-objects-wrapper field-post-objects-wrapper-<?php echo esc_attr($field_id); ?>">
                <div class="field-list <?php if($sortable){ echo 'sortable'; }?>" id="<?php echo esc_attr($field_id); ?>">
                    <?php
                    if(!empty($args)):
                        foreach ($args as $argsKey=>$arg):
                            ?>
                            <div class="item">
                                <?php if($sortable):?>
                                    <span class="ppof-button sort"><i class="fas fa-arrows-alt"></i></span>
                                <?php endif; ?>
                                <label>
                                    <input type="checkbox" <?php if(in_array($argsKey,$values)) echo 'checked';?>  value="<?php
                                    echo esc_attr($argsKey); ?>" name="<?php echo esc_attr($field_name); ?>[]">
                                    <span><?php echo esc_attr($arg); ?></span>
                                </label>
                            </div>
                        <?php
                        endforeach;
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>

            </div>
            <?php
            return ob_get_clean();
        }

        public function field_switcher( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $checked = !empty($value) ? 'checked':'';
            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switcher-wrapper
            field-switcher-wrapper-<?php echo esc_attr($id); ?>">
                <label class="switcher <?php echo esc_attr($checked); ?>">
                    <input type="checkbox" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($value); ?>"
                           name="<?php echo esc_attr($field_name); ?>" <?php echo esc_attr($checked); ?>>
                    <span class="layer"></span>
                    <span class="slider"></span>
                    <?php
                    if(!empty($args))
                    foreach ($args as $index=>$arg):
                        ?>
                        <span  unselectable="on" class="switcher-text <?php echo esc_attr($index); ?>"><?php echo esc_html($arg);
                        ?></span>
                    <?php
                    endforeach;
                    ?>
                </label>
                <div class="error-mgs"></div>
            </div>




            <?php
            return ob_get_clean();
        }




        public function field_google_map( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $preview 	        = isset( $option['preview'] ) ? $option['preview'] : false;
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            $lat  = isset($values['lat']) ? $values['lat'] : '';
            $lng   = isset($values['lng']) ? $values['lng'] :'';
            $zoom  = isset($values['zoom']) ? $values['zoom'] : '';
            $title  = isset($values['title']) ? $values['title'] : '';
            $apikey  = isset($values['apikey']) ? $values['apikey'] : '';


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($args)):
                ?>

                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                        id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-google-map-wrapper
                field-google-map-wrapper-<?php echo esc_attr($id); ?>">
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$name):
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_html($name); ?></span>
                                <span class="input-wrapper"><input type='text' name='<?php echo esc_attr($field_name);?>[<?php
                                    echo esc_attr($index); ?>]' value='<?php
                                    echo esc_attr($values[$index]); ?>' /></span>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>

                <?php
                if($preview):
                    ?>
                    <div id="map-<?php echo esc_attr($field_id); ?>"></div>
                    <script>
                        function initMap() {
                            var myLatLng = {lat: <?php echo esc_html($lat); ?>, lng: <?php echo esc_html($lng); ?>};
                            var map = new google.maps.Map(document.getElementById('map-<?php echo esc_html($field_id); ?>'), {
                                zoom: <?php echo esc_html($zoom); ?>,
                                center: myLatLng
                            });
                            var marker = new google.maps.Marker({
                                position: myLatLng,
                                map: map,
                                title: '<?php echo esc_html($title); ?>'
                            });
                        }
                    </script>
                    <script async defer
                            src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_html($apikey); ?>&callback=initMap">
                    </script>
                    <?php
                endif;
            endif;
            return ob_get_clean();
        }



        public function field_border( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            $width  = $values['width'];
            $unit   = $values['unit'];
            $style  = $values['style'];
            $color  = $values['color'];



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-border-wrapper
            field-border-wrapper-<?php echo esc_attr($id); ?>">
                <div class="item-list">
                        <div class="item">
                            <span class="field-title">Width</span>
                            <span class="input-wrapper">
                                <input type='number' name='<?php echo esc_attr($field_name);?>[width]' value='<?php  echo esc_attr($width); ?>' />
                            </span>
                            <select name="<?php echo esc_attr($field_name);?>[unit]">
                                <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                            </select>
                        </div>
                        <div class="item">
                            <span class="field-title">Style</span>
                            <select name="<?php echo esc_attr($field_name);?>[style]">
                                <option <?php if($style == 'dotted') echo 'selected'; ?> value="dotted">dotted</option>
                                <option <?php if($style == 'dashed') echo 'selected'; ?> value="dashed">dashed</option>
                                <option <?php if($style == 'solid') echo 'selected'; ?> value="solid">solid</option>
                                <option <?php if($style == 'double') echo 'selected'; ?> value="double">double</option>
                                <option <?php if($style == 'groove') echo 'selected'; ?> value="groove">groove</option>
                                <option <?php if($style == 'ridge') echo 'selected'; ?> value="ridge">ridge</option>
                                <option <?php if($style == 'inset') echo 'selected'; ?> value="inset">inset</option>
                                <option <?php if($style == 'outset') echo 'selected'; ?> value="outset">outset</option>
                                <option <?php if($style == 'none') echo 'selected'; ?> value="none">none</option>
                            </select>
                        </div>
                    <div class="item">
                        <span class="field-title">Color</span>
                        <span class="input-wrapper"><input class="colorpicker" type='text' name='<?php echo esc_attr($field_name);
                        ?>[color]' value='<?php echo esc_attr($color); ?>' /></span>
                    </div>
                </div>
                <div class="error-mgs"></div>
            </div>


            <?php
            return ob_get_clean();
        }



        public function field_dimensions( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($args)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-margin-wrapper
                field-margin-wrapper-<?php echo esc_attr($id); ?>">
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$arg):
                            $name = $arg['name'];
                            $unit = $values[$index]['unit'];
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_html($name); ?></span>
                                <span class="input-wrapper"><input type='number' name='<?php echo esc_attr($field_name);?>[<?php
                                    echo esc_attr($index); ?>][val]' value='<?php
                                    echo esc_attr($values[$index]['val']); ?>' /></span>
                                <select name="<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][unit]">
                                    <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                    <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                    <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                    <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                    <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                    <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                    <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                    <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                    <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                                </select>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>

            <?php
            endif;
            return ob_get_clean();
        }



        public function field_padding( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($args)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-padding-wrapper
                field-padding-wrapper-<?php echo esc_attr($id); ?>">
                    <label><input type="checkbox" class="change-together">Apply for all</label>
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$arg):
                            $name = $arg['name'];
                            $unit = $values[$index]['unit'];
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_html($name); ?></span>
                                <span class="input-wrapper">
                                    <input type='number' name='<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][val]' value='<?php echo esc_attr($values[$index]['val']); ?>' />
                                </span>
                                <select name="<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][unit]">
                                    <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                    <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                    <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                    <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                    <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                    <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                    <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                    <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                    <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                                </select>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>

                <script>
                    jQuery(document).ready(function($) {
                        jQuery(document).on('keyup change', '.field-padding-wrapper-<?php echo esc_attr($id); ?>  input[type="number"]',
                            function() {
                                is_checked = jQuery('.field-padding-wrapper-<?php echo esc_attr($id); ?> .change-together').attr('checked');
                                if(is_checked == 'checked'){
                                    val = jQuery(this).val();
                                    i = 0;
                                    $('.field-padding-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                        if(i > 0){
                                            jQuery(this).val(val);
                                        }
                                        i++;
                                    });
                                }
                            })
                        jQuery(document).on('click', '.field-padding-wrapper-<?php echo esc_attr($id); ?> .change-together', function() {
                            is_checked = this.checked;
                            if(is_checked){
                                i = 0;
                                $('.field-padding-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                    if(i > 0){
                                        jQuery(this).attr('readonly','readonly');
                                    }
                                    i++;
                                });
                                i = 0;
                                $('.field-padding-wrapper-<?php echo esc_attr($id); ?> select').each(function( index ) {
                                    if(i > 0){
                                        //jQuery(this).attr('disabled','disabled');
                                    }
                                    i++;
                                });
                            }else{
                                jQuery('.field-padding-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').removeAttr('readonly');
                                //jQuery('.field-margin-wrapper-<?php echo esc_attr($id); ?> select').removeAttr('disabled');
                            }
                        })
                    })
                </script>
            <?php
            endif;
            return ob_get_clean();
        }



        public function field_margin( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            if(!empty($args)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-margin-wrapper field-margin-wrapper-<?php echo esc_attr($id); ?>">
                    <label><input type="checkbox" class="change-together">Apply for all</label>
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$arg):
                            $name = $arg['name'];
                            $unit = $values[$index]['unit'];
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_attr($name); ?></span>
                                <span class="input-wrapper">
                                    <input class="<?php echo esc_attr($index); ?>" type='number' name='<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][val]' value='<?php echo esc_attr($values[$index]['val']); ?>' />
                                </span>
                                <select name="<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][unit]">
                                    <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                    <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                    <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                    <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                    <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                    <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                    <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                    <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                    <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                                </select>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>
                <script>
                    jQuery(document).ready(function($) {
                        jQuery(document).on('keyup change', '.field-margin-wrapper-<?php echo esc_attr($id); ?>  input[type="number"]',
                            function() {
                                is_checked = jQuery('.field-margin-wrapper-<?php echo esc_attr($id); ?> .change-together').attr('checked');
                                if(is_checked == 'checked'){
                                    val = jQuery(this).val();
                                    i = 0;
                                    $('.field-margin-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                        if(i > 0){
                                            jQuery(this).val(val);
                                        }
                                        i++;
                                    });
                                }
                            })
                        jQuery(document).on('click', '.field-margin-wrapper-<?php echo esc_attr($id); ?> .change-together', function() {
                            is_checked = this.checked;
                            if(is_checked){
                                i = 0;
                                $('.field-margin-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                    if(i > 0){
                                        jQuery(this).attr('readonly','readonly');
                                    }
                                    i++;
                                });
                                i = 0;
                                $('.field-margin-wrapper-<?php echo esc_attr($id); ?> select').each(function( index ) {
                                    if(i > 0){
                                        //jQuery(this).attr('disabled','disabled');
                                    }
                                    i++;
                                });
                            }else{
                                jQuery('.field-margin-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').removeAttr('readonly');
                                //jQuery('.field-margin-wrapper-<?php echo esc_attr($id); ?> select').removeAttr('disabled');
                            }
                        })
                    })
                </script>

            <?php
            endif;
            return ob_get_clean();
        }



        public function field_google_recaptcha( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $secret_key 	= isset( $option['secret_key'] ) ? $option['secret_key'] : "";
            $site_key 	    = isset( $option['site_key'] ) ? $option['site_key'] : "";
            $version 	    = isset( $option['version'] ) ? $option['version'] : "";
            $action_name 	= isset( $option['action_name'] ) ? $option['action_name'] : "action_name";

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-google-recaptcha-wrapper
            field-google-recaptcha-wrapper-<?php echo esc_attr($id);
            ?>">
                <?php if($version == 'v2'):?>
                    <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($site_key); ?>"></div>
                    <script src='https://www.google.com/recaptcha/api.js'></script>
            <?php elseif($version == 'v3'):?>
                    <script src='https://www.google.com/recaptcha/api.js?render=<?php echo esc_attr($site_key); ?>'></script>
                    <script>
                        grecaptcha.ready(function() {
                            grecaptcha.execute('<?php echo esc_attr($site_key); ?>', {action: '<?php echo esc_attr($action_name); ?>'})
                                .then(function(token) {
// Verify the token on the server.
                                });
                        });
                    </script>

                <?php endif;?>
                <div class="error-mgs"></div>
            </div>


            <?php

            return ob_get_clean();
        }


        public function field_img_select( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $width			= isset( $option['width'] ) ? $option['width'] : "";
            $height			= isset( $option['height'] ) ? $option['height'] : "";
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-img-select-wrapper
            field-img-select-wrapper-<?php echo esc_attr($id); ?>">
                <div class="img-list">
                    <?php
                    foreach( $args as $key => $arg ):
                        $checked = ( $arg == $value ) ? "checked" : "";
                        ?><label class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><img data-id="<?php echo esc_attr($id); ?>" src="<?php echo esc_attr($arg); ?>"> </span></label><?php

                    endforeach;
                    ?>
                </div>
                <div class="img-val">
                    <input type="text" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($value); ?>">
                </div>
                <div class="error-mgs"></div>
            </div>


            <?php
            return ob_get_clean();

        }





        public function field_submit( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-submit-wrapper
            field-submit-wrapper-<?php echo esc_attr($id); ?>">
                <input type='submit' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_nonce( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $action_name 	    = isset( $option['action_name'] ) ? $option['action_name'] : "";

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-nonce-wrapper
            field-nonce-wrapper-<?php echo esc_attr($id); ?>">
                <?php wp_nonce_field( $action_name, $field_name ); ?>
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }



        public function field_color( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-wrapper
            field-color-wrapper-<?php echo esc_attr($id); ?>">
                <input type='color' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }




        public function field_email( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-email-wrapper
            field-email-wrapper-<?php echo esc_attr($id); ?>">
                <input type='email' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }


        public function field_password( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $password_meter = isset( $option['password_meter'] ) ? $option['password_meter'] : true;
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-password-wrapper
            field-password-wrapper-<?php echo esc_attr($id); ?>">
                <input type='password' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <?php if($password_meter): ?>
                <div class="scorePassword"></div>
                <div class="scoreText"></div>
                <?php endif; ?>
                <div class="error-mgs"></div>
            </div>


            <?php

            return ob_get_clean();
        }

        public function field_search( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-search-wrapper
            field-search-wrapper-<?php echo esc_attr($id); ?>">
                <input type='search' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }

        public function field_month( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-month-wrapper
            field-month-wrapper-<?php echo esc_attr($id); ?>">
                <input type='time' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }

        public function field_date( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-date-wrapper
            field-date-wrapper-<?php echo esc_attr($id); ?>">
                <input type='date' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function field_url( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-url-wrapper field-url-wrapper-<?php echo esc_attr($id); ?>">
                <input type='url' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_time( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-time-wrapper
            field-time-wrapper-<?php echo esc_attr($id); ?>">
                <input type='time' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_tel( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-tel-wrapper field-tel-wrapper-<?php
            echo esc_attr($id); ?>">
                <input type='tel' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function field_text( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id))  return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $visible 	    = isset( $option['visible'] ) ? $option['visible'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();




            ?>


            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-text-wrapper
         field-text-wrapper-<?php echo esc_attr($id); ?>">
                <input type='text' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>'
                       placeholder='<?php
                echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>


            <?php

            return ob_get_clean();
        }


        public function field_hidden( $option ){




            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";

            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>


            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-hidden-wrapper
            field-hidden-wrapper-<?php echo esc_attr($id); ?>">
                <input type='hidden' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php
                echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }




        public function field_text_multi( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $remove_text 	= isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fas fa-times"></i>';
            $sortable 	    = isset( $option['sortable'] ) ? $option['sortable'] : true;
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();

            $values 	    = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($values) ? $values : $default;
            $limit 	        = !empty( $option['limit'] ) ? $option['limit'] : '';

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-text-multi-wrapper
            field-text-multi-wrapper-<?php echo esc_attr($field_id); ?>">
                <div class="field-list <?php if($sortable){ echo 'sortable'; }?>" id="<?php echo esc_attr($field_id); ?>">
                    <?php
                    if(!empty($values)):
                        foreach ($values as $value):
                            ?>
                            <div class="item">
                                <input type='text' name='<?php echo esc_attr($field_name); ?>[]'  placeholder='<?php
                                echo esc_attr($placeholder); ?>' value="<?php echo esc_attr($value); ?>" />

                                <span class="ppof-button clone"><i class="far fa-clone"></i></span>

                                <?php if($sortable):?>
                                <span class="ppof-button sort"><i class="fas fa-arrows-alt"></i></span>
                                <?php endif; ?>

                                <span class="ppof-button remove" onclick="jQuery(this).parent().remove()"><?php echo mep_esc_html($remove_text); ?></span>
                            </div>
                        <?php
                        endforeach;
                    else:
                        ?>
                        <div class="item">
                            <input type='text' name='<?php echo esc_attr($field_name); ?>[]'  placeholder='<?php echo
                            esc_attr($placeholder); ?>'
                                   value='' /><span class="button remove" onclick="jQuery(this).parent().remove()
"><?php echo mep_esc_html($remove_text); ?></span>
                            <?php if($sortable):?>
                                <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
                            <?php endif; ?>
                            <span class="button clone"><i class="far fa-clone"></i></span>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
	            <span class="ppof-button add-item"><?php echo __('Add','tour-booking-manager'); ?></span>
                <div class="error-mgs"></div>
                <script>
                    jQuery(document).ready(function($) {
                        jQuery(document).on('click', '.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .clone',function(){


                            <?php
                            if(!empty($limit)):
                            ?>
                            var limit = <?php  echo esc_attr($limit); ?>;
                            var node_count = $( ".field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list .item" ).size();
                            if(limit > node_count){
                                $( this ).parent().clone().appendTo('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list' );
                            }else{
                                jQuery('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                            }
                            <?php
                            else:
                            ?>
                            $( this ).parent().clone().appendTo('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list' );
                            <?php
                            endif;
                            ?>

                            //$( this ).parent().appendTo( '.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list' );


                        })
                    jQuery(document).on('click', '.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .add-item',function(){


                        html_<?php echo esc_attr($id); ?> = '<div class="item">';
                        html_<?php echo esc_attr($id); ?> += '<input type="text" name="<?php echo esc_attr($field_name); ?>[]" placeholder="<?php
                            echo esc_attr($placeholder); ?>" />';
                        html_<?php echo esc_attr($id); ?> += '<span class="button remove" onclick="jQuery(this).parent().remove()' +
                            '"><?php echo mep_esc_html($remove_text); ?></span>';
                        html_<?php echo esc_attr($id); ?> += '<span class="button clone"><i class="far fa-clone"></i></span>';
                        <?php if($sortable):?>
                        html_<?php echo esc_attr($id); ?> += ' <span class="button sort" ><i class="fas fa-arrows-alt"></i></span>';
                        <?php endif; ?>
                        html_<?php echo esc_attr($id); ?> += '</div>';


                        <?php
                        if(!empty($limit)):
                            ?>
                            var limit = <?php  echo esc_attr($limit); ?>;
                            var node_count = $( ".field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list .item" ).size();
                            if(limit > node_count){
                                jQuery('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list').append(html_<?php echo esc_attr($id); ?>);
                            }else{
                                jQuery('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                            }
                            <?php
                        else:
                            ?>
                            jQuery('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list').append(html_<?php echo esc_attr($id); ?>);
                            <?php
                        endif;
                        ?>




                    })

                })
                </script>

            </div>
            <?php
            return ob_get_clean();

        }



        public function field_textarea( $option ){

            $id             = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $visible 	    = isset( $option['visible'] ) ? $option['visible'] : "";
            $placeholder    = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $_value         = !empty($value) ? $value : $default;
            $__value        = str_replace('<br />', PHP_EOL, html_entity_decode($_value));
                  
            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-textarea-wrapper field-textarea-wrapper-<?php echo esc_attr($field_id); ?>">
                <textarea name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' cols='40' rows='5' placeholder='<?php echo esc_attr($placeholder); ?>'><?php echo mep_esc_html($__value); ?></textarea>
                <div class="error-mgs"></div>
            </div>



            <?php
            return ob_get_clean();
        }


        public function field_code( $option ){

            $id             = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $placeholder    = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;
            $args	        = isset( $option['args'] ) ? $option['args'] : array(
                'lineNumbers'	=> true,
                'mode'	=> "javascript",
            );


            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>"  class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper  field-code-wrapper
            field-code-wrapper-<?php echo esc_attr($field_id); ?>">
                <textarea name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' cols='40' rows='5' placeholder='<?php echo esc_attr($placeholder); ?>'><?php echo esc_attr($value); ?></textarea>
                <div class="error-mgs"></div>
            </div>
            <script>
                var editor = CodeMirror.fromTextArea(document.getElementById("<?php echo esc_attr($field_id); ?>"), {
                    <?php
                    foreach ($args as $argkey=>$arg):
                        echo esc_html($argkey).':'.esc_html($arg).',';
                    endforeach;
                    ?>
                });
            </script>

            <?php
            return ob_get_clean();
        }

        public function field_checkbox( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : "";

            $default 		= isset( $option['default'] ) ? $option['default'] : array();
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : array();
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-checkbox-wrapper
            field-checkbox-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = (  $key == $value ) ? "checked" : "";
                    ?>
                    <label for='<?php echo esc_attr($field_id); ?>'><input <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  class="<?php echo esc_attr($field_id); ?>" name='<?php echo esc_attr($field_name); ?>' type='checkbox' id='<?php echo esc_attr($field_id); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><?php echo esc_html($argName); ?></label><br>
                <?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>


            <?php
            return ob_get_clean();
        }

        public function field_checkbox_multi( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : "";

            $default 		= isset( $option['default'] ) ? $option['default'] : array();
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : array();
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name.'[]' : $id.'[]';



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-checkbox-wrapper
            field-checkbox-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = is_array( $value ) && in_array( $key, $value ) ? "checked" : "";
                    ?>
                    <label for='<?php echo esc_attr($field_id).'-'.esc_attr($key); ?>'><input class="<?php echo esc_attr($field_id); ?>" name='<?php
                        echo esc_attr($field_name); ?>' type='checkbox' id='<?php echo esc_attr($field_id).'-'.esc_attr($key); ?>' value='<?php
                        echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><?php echo esc_html($argName); ?></label><br>
                    <?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_radio( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : array();
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-radio-wrapper
            field-radio-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = ( $key == $value ) ? "checked" : "";
                    ?>
                    <label for='<?php echo esc_attr($field_id).'-'.esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($field_id).'-'.esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><?php echo esc_html($argName); ?></label><br>
                <?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_select( $option ){

            $id 	    = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $args	    = is_array( $args ) ? $args : $this->args_from_string( $args );
            $default    = isset( $option['default'] ) ? $option['default'] : "";
            $multiple 	= isset( $option['multiple'] ) ? $option['multiple'] : false;
            $limit 	    = !empty( $option['limit'] ) ? $option['limit'] : '';
            $value		= isset( $option['value'] ) ? $option['value'] : '';
            $value      = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-select-wrapper
            field-select-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                if($multiple):
                    ?>
                    <select name='<?php echo esc_attr($field_name); ?>[]' id='<?php echo esc_attr($field_id); ?>' multiple>
                    <?php
                else:
                    ?>
                        <select name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>'>
                    <?php
                endif;

                foreach( $args as $key => $argName ):
                    if( $multiple ) $selected = is_array( $value ) && in_array( $key, $value ) ? "selected" : "";
                    else $selected = ($value == $key) ? "selected" : "";
                    ?>
                    <option <?php echo esc_attr($selected); ?> value='<?php echo esc_attr($key); ?>'><?php echo esc_html($argName); ?></option>
                    <?php
                endforeach;
                ?>
                </select>


                <div class="error-mgs"></div>

            </div>
            <script>
                jQuery(document).ready(function($) {

                    <?php
                    if($limit > 0):
                        ?>
                        jQuery(document).on('change', '.field-select-wrapper-<?php echo esc_attr($id); ?> select', function() {

                            last_value = $('.field-select-wrapper-<?php echo esc_attr($id); ?> select :selected').last().val();

                            var node_count = $( ".field-select-wrapper-<?php echo esc_attr($id); ?> select :selected" ).size();

                            console.log(last_value);

                            var limit = <?php  echo esc_attr($limit); ?>;
                            //var node_count = $(".field-select-wrapper-<?php echo esc_attr($id); ?> select :selected").length;
                            //var node_count = $( ".field-select-wrapper-<?php echo esc_attr($id); ?> .field-list .item-wrap" ).size();
                            //console.log(node_count);
                            if(limit >= node_count){

                                //jQuery('.<?php echo 'field-select-wrapper-'.$id; ?> .field-list').append(html);
                            }else{
                                $(".field-select-wrapper-<?php echo esc_attr($id); ?> select option[value='"+last_value+"']").prop("selected", false);
                                jQuery('.field-select-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can select max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                            }

                        })
                        <?php

                    endif;
                    ?>





                })






            </script>
            <?php
            return ob_get_clean();
        }


        public function field_range( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $min            = isset( $args['min'] ) ? $args['min'] : 0;
            $max            = isset( $args['max'] ) ? $args['max'] : 100;
            $step           = isset( $args['step'] ) ? $args['step'] : 1;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-range-wrapper
            field-range-wrapper-<?php echo esc_attr($id); ?>">
                <input type='range' min='<?php echo esc_attr($min); ?>' max='<?php echo esc_attr($max); ?>' step='<?php echo esc_attr($args['step']); ?>' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function field_range_input( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 	= isset( $option['default'] ) ? $option['default'] : "";
            $args 	= isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value = !empty($value) ? $value : $default;

            $min            = isset( $args['min'] ) ? $args['min'] : 0;
            $max            = isset( $args['max'] ) ? $args['max'] : 100;
            $step           = isset( $args['step'] ) ? $args['step'] : 1;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-range-input-wrapper
            field-range-input-wrapper-<?php echo esc_attr($id); ?>">
                <input type="number" class="range-val" name='<?php echo esc_attr($field_name); ?>' value="<?php echo esc_attr($value); ?>">
                <input type='range' class='range-hndle' id="<?php echo esc_attr($field_id); ?>" min='<?php echo esc_attr($args['min']); ?>' max='<?php echo esc_attr($args['max']); ?>' step='<?php echo esc_attr($args['step']); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_switch( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switch-wrapper
            field-switch-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = ( $key == $value ) ? "checked" : "";
                    ?><label class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><?php echo esc_html($argName); ?></span></label><?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>


            <?php
            return ob_get_clean();
        }



        public function field_switch_multi( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : array();
            $value      = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switch-multi-wrapper
            field-switch-multi-wrapper-<?php echo
            $id; ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = is_array( $value ) && in_array( $key, $value ) ? "checked" : "";
                    ?><label class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($field_id); ?>-<?php echo esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>[]' type='checkbox' id='<?php echo esc_attr($field_id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><?php echo esc_html($argName); ?></span></label><?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_switch_img( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $width			= isset( $option['width'] ) ? $option['width'] : "";
            $height			= isset( $option['height'] ) ? $option['height'] : "";
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switch-img-wrapper
            field-switch-img-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $arg ):
                    $src = isset( $arg['src'] ) ? $arg['src'] : "";

                    $checked = ( $key == $value ) ? "checked" : "";
                    ?><label class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><img src="<?php echo esc_attr($src); ?>"> </span></label><?php

                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_time_format( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 	= isset( $option['default'] ) ? $option['default'] : "";
            $args 	= isset( $option['args'] ) ? $option['args'] : "";

            $value 	= isset( $option['value'] ) ? $option['value'] : "";
            $value 	 		= !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;




            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-time-format-wrapper
            field-time-format-wrapper-<?php echo esc_attr($id); ?>">
                <div class="format-list">
                    <?php
                    if(!empty($args)):
                        foreach ($args as $item):
                            $checked = ($item == $value) ? 'checked':false;
                            ?>
                            <div class="format" datavalue="<?php echo esc_attr($item); ?>">
                                <label><input type="radio" <?php echo esc_attr($checked); ?> name="preset_<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($item); ?>">
                                    <span class="name"><?php echo gmdate($item); ?></span></label>
                                <span class="format"><code><?php echo esc_attr($item); ?></code></span>
                            </div>
                        <?php
                        endforeach;
                        ?>
                        <div class="format-value">
                            <span class="format"><input value="<?php echo esc_attr($value); ?>" name="<?php echo esc_attr($field_name); ?>"></span>
                            <div class="">Preview: <?php echo gmdate($value); ?></div>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }






        public function field_date_format( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 	= isset( $option['default'] ) ? $option['default'] : "";
            $args 	= isset( $option['args'] ) ? $option['args'] : "";

            $value 	= isset( $option['value'] ) ? $option['value'] : "";
            $value 	 		= !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-date-format-wrapper
            field-date-format-wrapper-<?php echo esc_attr($id); ?>">
                <div class="format-list">
                    <?php
                    if(!empty($args)):
                        foreach ($args as $item):
                            $checked = ($item == $value) ? 'checked':false;
                            ?>
                            <div class="format" datavalue="<?php echo esc_attr($item); ?>">
                                <label><input type="radio" <?php echo esc_attr($checked); ?> name="preset_<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($item); ?>"><span class="name"><?php echo gmdate($item); ?></span></label>
                                <span class="format"><code><?php echo esc_html($item); ?></code></span>
                            </div>
                            <?php
                        endforeach;
                        ?>
                        <div class="format-value">
                            <span class="format"><input value="<?php echo esc_attr($value); ?>" name="<?php echo esc_attr($field_name); ?>"></span>
                            <div class="">Preview: <?php echo gmdate($value); ?></div>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_datepicker( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $date_format	= isset( $option['date_format'] ) ? $option['date_format'] : "dd-mm-yy";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ?$value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-datepicker-wrapper
            field-datepicker-wrapper-<?php echo esc_attr($id); ?>">
                <input type='text' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    $('#<?php echo esc_attr($field_id); ?>').datepicker({dateFormat : '<?php echo esc_attr($date_format); ?>'})});
            </script>

            <?php
            return ob_get_clean();
        }






        public function field_colorpicker( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-colorpicker-wrapper
            field-colorpicker-wrapper-<?php echo esc_attr($id); ?>">
                <input type='text'  name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>
            <script>jQuery(document).ready(function($) { $('#<?php echo esc_attr($field_id); ?>').wpColorPicker();});</script>

            <?php
            return ob_get_clean();
        }


        public function field_colorpicker_multi( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $limit 	        = isset( $option['limit'] ) ? $option['limit'] : "";
            $remove_text 	= isset( $option['remove_text'] ) ? $option['remove_text'] : "X";
            $default 	= isset( $option['default'] ) ? $option['default'] : array();

            $values = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($values)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-colorpicker-multi-wrapper
                field-colorpicker-multi-wrapper-<?php echo esc_attr($id);
                ?>">
                    <div class="ppof-button add"><?php echo __('Add','tour-booking-manager'); ?></div>
                    <div class="item-list">
                        <?php
                        foreach ($values as $value):
                            ?>
                            <div class="item">
                                <span class="ppof-button remove"><?php echo mep_esc_html($remove_text); ?></span>
                                <input type='text' name='<?php echo esc_attr($field_name); ?>[]' value='<?php echo esc_attr($value); ?>' />
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>
                <?php
            endif;
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .item-list .remove', function(){
                        jQuery(this).parent().remove();
                    })
                    jQuery(document).on('click', '.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .add', function() {
                        html='<div class="item">';
                        html+='<span class="button remove"><?php echo mep_esc_html($remove_text); ?></span> <input type="text"  name="<?php echo esc_attr($field_name); ?>[]" value="" />';
                        html+='</div>';


                        <?php
                        if(!empty($limit)):
                        ?>
                        var limit = <?php  echo esc_attr($limit); ?>;
                        var node_count = $( ".field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .item-list .item" ).size();
                        if(limit > node_count){

                            $('.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .item-list').append(html);
                            $('.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> input').wpColorPicker();


                        }else{
                            jQuery('.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                        }
                        <?php
                        endif;
                        ?>





                    })
                    $('.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> input').wpColorPicker();
                });
            </script>

            <?php

            return ob_get_clean();
        }




        public function field_link_color( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $args 	        = isset( $option['args'] ) ? $option['args'] : array('link'	=> '#1B2A41','hover' => '#3F3244','active' => '#60495A','visited' => '#7D8CA3' );

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-link-color-wrapper
            field-link-color-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                if(!empty($values) && is_array($values)):
                    foreach ($args as $argindex=>$value):
                        ?>
                        <div>
                            <div class="item"><span class="title">a:<?php echo esc_html($argindex); ?> Color</span><div class="colorpicker"><input type='text' class='<?php echo esc_attr($id); ?>' name='<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($argindex); ?>]'   value='<?php echo esc_attr($values[$argindex]); ?>' /></div></div>
                        </div>
                        <?php
                    endforeach;
                else:
                    foreach ($args as $argindex=>$value):
                        ?>
                        <div>
                            <div class="item"><span class="title">a:<?php echo esc_html($argindex); ?> Color</span><div class="colorpicker"><input type='text' class='<?php echo esc_attr($id); ?>' name='<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($argindex); ?>]'   value='<?php echo esc_attr($value); ?>' /></div></div>
                        </div>
                    <?php
                    endforeach;
                endif;
                ?>
                <div class="error-mgs"></div>
            </div>
            <script>jQuery(document).ready(function($) { $('.<?php echo esc_attr($id); ?>').wpColorPicker();});</script>

            <?php
            return ob_get_clean();
        }






        public function field_user( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args 			= isset( $option['args'] ) ? $option['args'] : array();
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $icons		    = is_array( $args ) ? $args :  $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-user-multi-wrapper
            field-user-multi-wrapper-<?php echo esc_attr($id); ?>">
                <div class="users-wrapper" >
                    <?php if(!empty($values)):
                        foreach ($values as $user_id):
                            $get_avatar_url = get_avatar_url($user_id,array('size'=>'60'));

                            ?><div class="item" title="click to remove"><img src="<?php echo esc_attr($get_avatar_url); ?>" /><input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="<?php echo esc_attr($user_id); ?>"></div><?php
                        endforeach;
                    endif; ?>
                </div>
                <div class="user-list">
                    <div class="ppof-button select-user" ><?php echo __('Choose User','tour-booking-manager');?></div>
                    <div class="search-user" ><input class="" type="text" placeholder="<?php echo __('Start typing...','tour-booking-manager');?>"></div>
                    <ul>
                        <?php
                        if(!empty($icons)):
                            foreach ($icons as $user_id=>$iconTitle):
                                $user_data = get_user_by('ID',$user_id);
                                $get_avatar_url = get_avatar_url($user_id,array('size'=>'60'));
                                ?>
                                <li title="<?php echo esc_attr($user_data->display_name); ?>(#<?php echo esc_attr($user_id); ?>)"
                                    userSrc="<?php echo
                                $get_avatar_url; ?>"
                                    iconData="<?php echo esc_attr($user_id); ?>"><img src="<?php echo esc_attr($get_avatar_url); ?>" />
                                </li>
                            <?php
                            endforeach;
                        endif;
                        ?>
                    </ul>
                </div>
                <div class="error-mgs"></div>
            </div>


            <script>
                jQuery(document).ready(function($){
                    jQuery(document).on('click', '.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .users-wrapper .item', function(){
                        jQuery(this).remove();
                    })
                    jQuery(document).on('click', '.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .select-user', function(){
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                    jQuery(document).on('keyup', '.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .search-user input', function(){
                        text = jQuery(this).val();
                        $('.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .user-list li').each(function( index ) {
                            //console.log( index + ": " + $( this ).attr('title') );
                            title = $( this ).attr('title');
                            n = title.indexOf(text);
                            if(n<0){
                                $( this ).hide();
                            }else{
                                $( this ).show();
                            }
                        });
                    })
                    jQuery(document).on('click', '.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .user-list li', function(){
                        iconData = jQuery(this).attr('iconData');
                        userSrc = jQuery(this).attr('userSrc');
                        html = '';
                        html = '<div class="item" title="click to remove"><img src="'+userSrc+'" /><input type="hidden" ' +
                        'name="<?php echo esc_attr($field_name); ?>[]" value="'+iconData+'"></div>';
                        jQuery('.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .users-wrapper').append(html);
                    })
                })
            </script>

            <?php
            return ob_get_clean();
        }



        public function field_icon( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $args 			= isset( $option['args'] ) ? $option['args'] : array();
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $icons		    = is_array( $args ) ? $args : $this->args_from_string( $args );

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-icon-wrapper
            field-icon-wrapper-<?php echo esc_attr($id); ?>">
                <div class="icon-wrapper" >
                    <span><i class="<?php echo esc_attr($value); ?>"></i></span>
                    <input type="hidden" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($value); ?>">
                </div>
                <div class="icon-list">
                    <div class="ppof-button select-icon" ><?php echo __('Choose Icon','tour-booking-manager'); ?></div>
                    <div class="search-icon" ><input class="" type="text" placeholder="<?php echo __('Start typing...','tour-booking-manager'); ?>"></div>
                    <ul>
                        <?php
                        if(!empty($icons)):
                            foreach ($icons as $iconindex=>$iconTitle):
                                ?>
                                <li title="<?php echo esc_attr($iconTitle); ?>" iconData="<?php echo esc_attr($iconindex); ?>"><i class="<?php echo esc_attr($iconindex); ?>"></i></li>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </ul>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }
        public function mp_field_icon( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $args 			= isset( $option['args'] ) ? $option['args'] : array();
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $icons		    = is_array( $args ) ? $args : $this->args_from_string( $args );

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-icon-wrapper
            field-icon-wrapper-<?php echo esc_attr($id); ?>">
             
	            <?php do_action('ttbm_input_add_icon',$field_name,$value); ?>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_icon_multi( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $args 			= isset( $option['args'] ) ? $option['args'] : array();
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $icons		    = is_array( $args ) ? $args :  $this->args_from_string( $args );

            $limit 	        = isset( $option['limit'] ) ? $option['limit'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-icon-multi-wrapper
            field-icon-multi-wrapper-<?php echo esc_attr($id); ?>">
                <div class="icons-wrapper" >
                    <?php if(!empty($values)):
                        foreach ($values as $value):
                            ?><div class="item" title="click to remove"><span><i class="<?php echo esc_attr($value); ?>"></i></span><input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="<?php echo esc_attr($value); ?>"></div><?php
                        endforeach;
                    endif; ?>
                </div>
                <div class="icon-list">
                    <div class="ppof-button select-icon" ><?php echo __('Choose Icon','tour-booking-manager'); ?></div>
                    <div class="search-icon" ><input class="" type="text" placeholder="<?php echo __('Start typing...','tour-booking-manager'); ?>"></div>
                    <ul>
                        <?php
                        if(!empty($icons)):
                            foreach ($icons as $iconindex=>$iconTitle):
                                ?><li title="<?php echo esc_attr($iconTitle); ?>" iconData="<?php echo esc_attr($iconindex); ?>"><i class="<?php echo esc_attr($iconindex); ?>"></i></li><?php
                            endforeach;
                        endif;
                        ?>
                    </ul>
                </div>
                <div class="error-mgs"></div>
            </div>


            <script>
                jQuery(document).ready(function($){


                    jQuery(document).on('click', '.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icons-wrapper .item', function(){
                        jQuery(this).remove();
                    })
                    jQuery(document).on('click', '.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .select-icon', function(){
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                    jQuery(document).on('keyup', '.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .search-icon input', function(){
                        text = jQuery(this).val();
                        $('.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icon-list li').each(function( index ) {
                            console.log( index + ": " + $( this ).attr('title') );
                            title = $( this ).attr('title');
                            n = title.indexOf(text);
                            if(n<0){
                                $( this ).hide();
                            }else{
                                $( this ).show();
                            }
                        });
                    })
                    jQuery(document).on('click', '.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icon-list li', function(){
                        iconData = jQuery(this).attr('iconData');
                        html = '<div class="item" title="click to remove"><span><i class="'+iconData+'"></i></span><input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="'+iconData+'"></div>';


                        <?php
                        if(!empty($limit)):
                        ?>
                        var limit = <?php  echo esc_attr($limit); ?>;
                        var node_count = $( ".field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icons-wrapper .item" ).size();
                        if(limit > node_count){

                            jQuery('.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icons-wrapper').append(html);


                        }else{
                            jQuery('.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                        }
                        <?php
                        endif;
                        ?>




                    })


                })
            </script>

            <?php
            return ob_get_clean();
        }









        public function field_number( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 			= isset( $option['default'] ) ? $option['default'] : "";
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";

            $value 			= isset( $option['value'] ) ? $option['value'] : "";
            $value = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
             <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-number-wrapper
             field-number-wrapper-<?php echo esc_attr($id); ?>">
                <input type='number' class='' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                 <div class="error-mgs"></div>
             </div>

            <?php
            return ob_get_clean();
        }



        public function field_wp_editor( $option ){

            $id = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder    = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default        = isset( $option['default'] ) ? $option['default'] : "";
            $editor_settings= isset( $option['editor_settings'] ) ? $option['editor_settings'] : array('textarea_name'=>$field_name);
            $value 			= isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;
            $field_id       = $id;
            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-wp_editor-wrapper
            field-wp_editor-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                wp_editor( html_entity_decode(nl2br($value)), $id, $settings = $editor_settings);
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }




        public function field_select2( $option ){

            $id 	    = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $args	    = is_array( $args ) ? $args : $this->args_from_string( $args );
            $default    = isset( $option['default'] ) ? $option['default'] : "";
            $multiple 	= isset( $option['multiple'] ) ? $option['multiple'] : false;

            $value		= isset( $option['value'] ) ? $option['value'] : '';
            $value      = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            if($multiple):
                $value = !empty($value) ? $value : array();
            endif;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-select2-wrapper
            field-select2-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                if($multiple):
                    ?>
                    <select <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?> name='<?php echo esc_attr($field_name); ?>[]' id='<?php echo esc_attr($field_id); ?>' class="ttbm_select2" multiple>
                    <?php
                else:
                    ?>
                    <select <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?> name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>'>
                    <?php
                endif;
                foreach( $args as $key => $name ):

                    if( $multiple ) $selected = in_array( $key, $value ) ? "selected" : "";
                    else $selected = $value == $key ? "selected" : "";
                    ?>
                    <option <?php echo esc_attr($selected); ?> value='<?php echo esc_attr($key); ?>'><?php echo esc_attr($name); ?></option>
                    <?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
            </select>


            <?php
            return ob_get_clean();

        }


        public function field_option_group( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            $options			= isset( $option['options'] ) ? $option['options'] : array();
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $values			= isset( $option['value'] ) ? $option['value'] : '';
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $FormFieldsGenerator = new FormFieldsGenerator();

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;




            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>

            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-option-group-tabs-wrapper
            field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?>">

                        <?php
                        if(!empty($options)):
                            ?>
                            <table class="form-table">
                                <tbody>

                                <?php

                                foreach ($options as $key =>$option):

                                    $option_id = isset($option['id']) ? $option['id'] : '';
                                    $option_title = isset($option['title']) ? $option['title'] : '';


                                    $option['field_name'] = $field_name.'['.$option_id.']';
                                    $option['value'] = isset($values[$option_id]) ? $values[$option_id] : '';


                                    ?>
                                    <tr>
                                        <th scope="row"><?php echo esc_html($option_title); ?></th>
                                        <td>
                                            <?php                                           
                                            if (sizeof($option) > 0 && isset($option['type'])) {
                                                echo mep_field_generator($option['type'], $option);
                                                do_action("wp_theme_settings_field_$type", $option);
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php




                                endforeach;

                                ?>


                                </tbody>
                            </table>
                        <?php

                        endif;
                        ?>


                    <?php

                ?>

                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function field_option_group_tabs( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $values			= isset( $option['value'] ) ? $option['value'] : '';
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $FormFieldsGenerator = new FormFieldsGenerator();

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;




            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.faq-list-<?php echo esc_attr($id); ?> .faq-header', function() {
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                })
            </script>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-option-group-tabs-wrapper
            field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?>">

                <ul class="tab-navs">
                    <?php
                    $i = 1;
                    foreach( $args as $key => $value ):
                        $title = $value['title'];
                        ?>
                        <li index="<?php echo esc_attr($i); ?>" class="<?php if($i == 1) echo 'active'; ?>"><?php echo esc_html($title); ?></li>
                        <?php
                        $i++;
                    endforeach;
                    ?>
                </ul>


                    <?php
                    $i = 1;
                    foreach( $args as $key => $value ):
                        $title = $value['title'];
                        $link = $value['link'];
                        $options = $value['options'];
                        ?>
                        <div class="tab-content tab-content-<?php echo esc_attr($i); ?> <?php if($i == 1) echo 'active'; ?>">


                                <?php
                                if(!empty($options)):
                                    ?>
                                    <table class="form-table">
                                        <tbody>

                                        <?php

                                        foreach ($options as $option):

                                            $option_id = isset($option['id']) ? $option['id'] : '';
                                            $option_title = isset($option['title']) ? $option['title'] : '';

                                            $option['field_name'] = $field_name.'['.$key.']['.$option_id.']';
                                            $option['value'] = isset($values[$key][$option_id]) ? $values[$key][$option_id] : '';


                                            ?>
                                            <tr>
                                                <th scope="row"><?php echo esc_html($option['title']); ?></th>
                                                <td>
                                                    <?php
                                                        if (sizeof($option) > 0 && isset($option['type'])) {
                                                            echo mep_field_generator($option['type'], $option);
                                                            do_action("wp_theme_settings_field_$type", $option);
                                                        }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php




                                        endforeach;

                                        ?>


                                        </tbody>
                                    </table>
                                <?php

                                endif;
                                ?>

                        </div>
                    <?php
                        $i++;
                    endforeach;
                    ?>

                <div class="error-mgs"></div>
            </div>
            <script>

                jQuery(document).on('click', '.field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?> .tab-navs li', function() {

                    index = $(this).attr('index');

                    jQuery(".field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?> .tab-navs li").removeClass('active');
                    jQuery(".field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?> .tab-content").removeClass('active');
                    if(jQuery(this).hasClass('active')){

                    }else{
                        jQuery(this).addClass('active');
                        jQuery(".field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?> .tab-content-"+index).addClass('active');
                    }



                })


            </script>
            <?php
            return ob_get_clean();
        }


        public function field_option_group_accordion( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $values			= isset( $option['value'] ) ? $option['value'] : '';
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $FormFieldsGenerator = new FormFieldsGenerator();

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;




            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.faq-list-<?php echo esc_attr($id); ?> .faq-header', function() {
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                })
            </script>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-faq-wrapper
            field-faq-wrapper-<?php echo esc_attr($id); ?>">
                <div class='faq-list faq-list-<?php echo esc_attr($id); ?>'>
                    <?php
                    foreach( $args as $key => $value ):
                        $title      = $value['title'];
                        $link       = $value['link'];
                        $options    = $value['options'];
                        ?>
                        <div class="faq-item">
                            <div class="faq-header"><?php echo esc_html($title); ?></div>
                            <div class="faq-content">
                                <?php
                                if(!empty($options)):
                                    ?>
                                    <table class="form-table">
                                        <tbody>
                                        <?php
                                        foreach ($options as $option):
                                            $option['field_name'] = $field_name.'['.$key.']['.$option['id'].']';
                                            $option['value'] = $values[$key][$option['id']];
                                                ?>
                                                <tr>
                                                    <th scope="row"><?php echo esc_html($option['title']); ?></th>
                                                    <td>
                                                        <?php
                                                            if (sizeof($option) > 0 && isset($option['type'])) {
                                                                echo mep_field_generator($option['type'], $option);
                                                                do_action("wp_theme_settings_field_$type", $option);
                                                            }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            endforeach;
                                        ?>
                                        </tbody>
                                    </table>
                                    <?php

                                endif;
                                ?>
                            </div>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_faq( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            $args			= isset( $option['args'] ) ? $option['args'] : array();

            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.faq-list-<?php echo esc_attr($id); ?> .faq-header', function() {
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                })
            </script>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-faq-wrapper
            field-faq-wrapper-<?php echo esc_attr($id); ?>">
                <div class='faq-list faq-list-<?php echo esc_attr($id); ?>'>
                    <?php
                    foreach( $args as $key => $value ):
                        $title = $value['title'];
                        $link = $value['link'];
                        $content = $value['content'];
                        ?>
                        <div class="faq-item">
                            <div class="faq-header"><?php echo esc_html($title); ?></div>
                            <div class="faq-content"><?php echo esc_html($content); ?></div>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }




        public function field_grid( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            $args 			= isset( $option['args'] ) ? $option['args'] : "";
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $widths 		= isset( $option['width'] ) ? $option['width'] : array('768px'=>'100%','992px'=>'50%', '1200px'=>'30%', );
            $heights 		= isset( $option['height'] ) ? $option['height'] : array('768px'=>'auto','992px'=>'250px', '1200px'=>'250px', );


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;




            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-grid-wrapper
            field-grid-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach($args as $key=>$grid_item){
                    $title = isset($grid_item['title']) ? $grid_item['title'] : '';
                    $link = isset($grid_item['link']) ? $grid_item['link'] : '';
                    $thumb = isset($grid_item['thumb']) ? $grid_item['thumb'] : '';
                    ?>
                    <div class="item">
                        <div class="thumb"><a href="<?php echo esc_attr($link); ?>"><img src="<?php echo esc_attr($thumb); ?>"></img></a></div>
                        <div class="name"><a href="<?php echo esc_attr($link); ?>"><?php echo esc_html($title); ?></a></div>
                    </div>
                    <?php
                }
                ?>
                <div class="error-mgs"></div>
            </div>
            <style type="text/css">
                <?php
                if(!empty($widths)):
                    foreach ($widths as $screen_size=>$width):
                    $height = !empty($heights[$screen_size]) ? $heights[$screen_size] : 'auto';
                    ?>
                    @media screen and (min-width: <?php echo esc_attr($screen_size); ?>) {
                        .field-grid-wrapper-<?php echo esc_attr($id); ?> .item{
                            width: <?php echo esc_attr($width); ?>;
                        }
                        .field-grid-wrapper-<?php echo esc_attr($id); ?> .item .thumb{
                            height: <?php echo esc_attr($height); ?>;
                        }
                    }
                    <?php
                    endforeach;
                endif;
                ?>
            </style>

            <?php
            return ob_get_clean();
        }





        public function field_color_sets( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $width			= isset( $args['width'] ) ? $args['width'] : "";
            $height			= isset( $args['height'] ) ? $args['height'] : "";
            $sets		    = isset( $option['sets'] ) ? $option['sets'] : array();
            //$option_value	= get_option( $id );
            $default		= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-sets-wrapper
            field-color-sets-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $sets as $key => $set ):

                    //var_dump($value);

                    $checked = ( $key == $value ) ? "checked" : "";
                    ?>
                    <label  class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'>
                        <input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>>
                        <?php
                        foreach ($set as $color):
                            ?>
                            <span class="color-srick" style="background-color: <?php echo esc_attr($color); ?>;"></span>
                            <?php

                        endforeach;
                        ?>


                        <span class="checked-icon"><i class="fas fa-check"></i></span>

                    </label><?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
            <style type="text/css">
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> .sw-button{
                    transition: ease all 1s;
                <?php if(!empty($width)):  ?>
                    width: <?php echo esc_attr($width); ?>;
                <?php endif; ?>
                <?php if(!empty($height)):  ?>
                    height: <?php echo esc_attr($height); ?>;
                <?php endif; ?>
                }
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> label:hover .sw-button{
                }
            </style>


            <?php
            return ob_get_clean();

        }


        public function field_image_link( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $width				= isset( $args['width'] ) ? $args['width'] : "";
            $height				= isset( $args['height'] ) ? $args['height'] : "";
            $links		= isset( $option['links'] ) ? $option['links'] : array();
            //$option_value	= get_option( $id );
            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-image-link-wrapper
            field-image-link-wrapper-<?php echo esc_attr($id); ?>">
                <?php


                    if(!empty($links))
                        foreach( $links as $key => $link ):



                            $checked = ( $link == $value ) ? "checked" : "";
                            ?><label  class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input
                                    type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'
                                    value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>>
                            <img src="<?php echo esc_attr($link); ?>">
                            <span class="checked-icon"><i class="fas fa-check"></i></span>
                            </label><?php
                        endforeach;
                if(!in_array($value, $links)){
                    ?><label  class="checked" for='<?php echo esc_attr($id); ?>-custom'><input
                            type='radio' id='<?php echo esc_attr($id); ?>-custom'
                            value='<?php echo esc_attr($value); ?>' checked>
                    <img src="<?php echo esc_attr($value); ?>">
                    <span class="checked-icon"><i class="fas fa-check"></i></span>
                    </label><?php
                }


                ?>
                <div class="val-wrap">
                    <input class="link-val" name='<?php echo esc_attr($field_name); ?>' type="text" value="<?php echo esc_attr($value); ?>"> <span class='ppof-button upload' id='media_upload_<?php echo esc_attr($id); ?>'><?php echo __('Upload','tour-booking-manager');?></span> <span class="ppof-button clear">Clear</span>
                </div>
                <div class="error-mgs"></div>
            </div>
            <script>jQuery(document).ready(function($){
                    $('#media_upload_<?php echo esc_attr($id); ?>').click(function() {
                        //var send_attachment_bkp = wp.media.editor.send.attachment;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            //$('#media_preview_<?php echo esc_attr($id); ?>').attr('src', attachment.url);
                            //$('#media_input_<?php echo esc_attr($id); ?>').val(attachment.url);
                            jQuery('.field-image-link-wrapper-<?php echo esc_attr($id); ?> .link-val').val(attachment.url);
                            //wp.media.editor.send.attachment = send_attachment_bkp;
                        }
                        wp.media.editor.open($(this));
                        return false;
                    });

                });
            </script>
            <style type="text/css">
                .field-image-link-wrapper-<?php echo esc_attr($id); ?> img{
                    transition: ease all 1s;
                <?php if(!empty($width)):  ?>
                    width: <?php echo esc_attr($width); ?>;
                <?php endif; ?>
                <?php if(!empty($height)):  ?>
                    height: <?php echo esc_attr($height); ?>;
                <?php endif; ?>
                }
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> label:hover .sw-button{
                }
            </style>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-image-link-wrapper-<?php echo esc_attr($id); ?> .clear', function() {
                        jQuery('.field-image-link-wrapper-<?php echo esc_attr($id); ?> .link-val').val("");
                    })

                    jQuery(document).on('click', '.field-image-link-wrapper-<?php echo esc_attr($id); ?> img', function() {

                        var src = $(this).attr('src');
                        jQuery('.field-image-link-wrapper-<?php echo esc_attr($id); ?> .link-val').val(src);

                        jQuery('.field-image-link-wrapper-<?php echo esc_attr($id); ?> label').removeClass('checked');
                        if(jQuery(this).parent().hasClass('checked')){
                            jQuery(this).parent().removeClass('checked');
                        }else{
                            jQuery(this).parent().addClass('checked');
                        }
                    })
                })
            </script>

            <?php
            return ob_get_clean();

        }


        public function field_color_palette( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $width				= isset( $args['width'] ) ? $args['width'] : "";
            $height				= isset( $args['height'] ) ? $args['height'] : "";
            $colors			= isset( $option['colors'] ) ? $option['colors'] : array();
            //$option_value	= get_option( $id );
            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-palette-wrapper
            field-color-palette-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $colors as $key => $color ):

                    $checked = ( $key == $value ) ? "checked" : "";
                    ?><label  class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input
                            name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'
                            value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span title="<?php echo esc_attr($color); ?>" style="background-color: <?php
                    echo esc_attr($color); ?>" class="sw-button"></span>
                    <span class="checked-icon"><i class="fas fa-check"></i></span>
                    </label><?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
            <style type="text/css">
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> .sw-button{
                    transition: ease all 1s;
                <?php if(!empty($width)):  ?>
                    width: <?php echo esc_attr($width); ?>;
                <?php endif; ?>
                <?php if(!empty($height)):  ?>
                    height: <?php echo esc_attr($height); ?>;
                <?php endif; ?>
                }
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> label:hover .sw-button{
                }
            </style>


            <?php
            return ob_get_clean();

        }




        public function field_color_palette_multi( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";

            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $width				= isset( $args['width'] ) ? $args['width'] : "";
            $height				= isset( $args['height'] ) ? $args['height'] : "";
            $colors			= isset( $option['colors'] ) ? $option['colors'] : array();
            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-palette-multi-wrapper
            field-color-palette-multi-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $colors as $key => $color ):
                    $checked = is_array( $value ) && in_array( $key, $value ) ? "checked" : "";
                    ?><label  class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input
                            name='<?php echo esc_attr($field_name); ?>[]' type='checkbox' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'
                            value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span title="<?php echo esc_attr($color); ?>" style="background-color: <?php
                    echo esc_attr($color); ?>" class="sw-button"></span>
                    <span class="checked-icon"><i class="fas fa-check"></i></span>
                    </label><?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
            <style type="text/css">
                .field-color-palette-multi-wrapper-<?php echo esc_attr($id); ?> .sw-button{
                    transition: ease all 1s;
                <?php if(!empty($width)):  ?>
                    width: <?php echo esc_attr($width); ?>;
                <?php endif; ?>
                <?php if(!empty($height)):  ?>
                    height: <?php echo esc_attr($height); ?>;
                <?php endif; ?>
                }
                .field-color-palette-multi-wrapper-<?php echo esc_attr($id); ?> label:hover .sw-button{
                }
            </style>


            <?php
            return ob_get_clean();
        }




        public function field_media( $option ){

            $id			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";

            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $media_url	= wp_get_attachment_url( $value );
            $media_type	= get_post_mime_type( $value );
            $media_title= get_the_title( $value );
            $media_url = !empty($media_url) ? $media_url : $placeholder;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            wp_enqueue_media();

            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-media-wrapper
            field-media-wrapper-<?php echo esc_attr($id); ?>">
                <div class='media_preview' style='width: 150px;margin-bottom: 10px;background: #eee;padding: 5px;    text-align: center;'>
                    <?php

                    if( "audio/mpeg" == $media_type ){
                        ?>
                        <div id='media_preview_$id' class='dashicons dashicons-format-audio' style='font-size: 70px;display: inline;'></div>
                        <div><?php echo esc_html($media_title); ?></div>
                        <?php
                    }
                    else {
                        ?>
                        <img id='media_preview_<?php echo esc_attr($id); ?>' src='<?php echo esc_attr($media_url); ?>' style='width:100%'/>
                        <?php
                    }
                    ?>
                </div>
                <input type='hidden' name='<?php echo esc_attr($field_name); ?>' id='media_input_<?php echo esc_attr($id); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class='ppof-button upload' id='media_upload_<?php echo esc_attr($id); ?>'><?php echo __('Upload','tour-booking-manager');?></div><div class='ppof-button clear' id='media_clear_<?php echo esc_attr($id); ?>'><?php echo __('Clear','tour-booking-manager');?></div>
                <div class="error-mgs"></div>
            </div>

            <script>jQuery(document).ready(function($){
                    $('#media_upload_<?php echo esc_attr($id); ?>').click(function() {
                        var send_attachment_bkp = wp.media.editor.send.attachment;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            $('#media_preview_<?php echo esc_attr($id); ?>').attr('src', attachment.url);
                            $('#media_input_<?php echo esc_attr($id); ?>').val(attachment.id);
                            wp.media.editor.send.attachment = send_attachment_bkp;
                        }
                        wp.media.editor.open($(this));
                        return false;
                    });
                    $('#media_clear_<?php echo esc_attr($id); ?>').click(function() {
                        $('#media_input_<?php echo esc_attr($id); ?>').val('');
                        $('#media_preview_<?php echo esc_attr($id); ?>').attr('src','');
                    })

                });
            </script>

            <?php
            return ob_get_clean();
        }




        public function field_media_multi( $option ){

            $id			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $remove_text			= isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fas fa-times"></i>';
            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $values			= isset( $option['value'] ) ? $option['value'] : '';

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            wp_enqueue_media();

            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-media-multi-wrapper
            field-media-multi-wrapper-<?php echo esc_attr($id); ?>">
                <div class='ppof-button upload' id='media_upload_<?php echo esc_attr($id); ?>'><?php echo __('Upload','tour-booking-manager');?></div><div class='ppof-button clear'
                                                                                          id='media_clear_<?php echo
                                                                                          $id;
                                                                                          ?>'><?php echo __('Clear','tour-booking-manager');?></div>
                <div class="media-list media-list-<?php echo esc_attr($id); ?> sortable">
                    <?php
                    if(!empty($values) && is_array($values)):
                        foreach ($values as $value ):
                            $media_url	= wp_get_attachment_url( $value );
                            $media_type	= get_post_mime_type( $value );
                            $media_title= get_the_title( $value );
                            ?>
                            <div class="item">
                                <span class="remove" onclick="jQuery(this).parent().remove()"><?php echo mep_esc_html($remove_text); ?></span>
                                <span class="sort" >sort</span>
                                <img id='media_preview_<?php echo esc_attr($id); ?>' src='<?php echo esc_attr($media_url); ?>' style='width:100%'/>
                                <div class="item-title"><?php echo esc_html($media_title); ?></div>
                                <input type='hidden' name='<?php echo esc_attr($field_name); ?>[]' value='<?php echo esc_attr($value); ?>' />
                            </div>
                        <?php
                        endforeach;
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>
            <script>jQuery(document).ready(function($){
                    $('#media_upload_<?php echo esc_attr($id); ?>').click(function() {
                        //var send_attachment_bkp = wp.media.editor.send.attachment;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            attachment_id = attachment.id;
                            attachment_url = attachment.url;
                            html = '<div class="item">';
                            html += '<span class="remove" onclick="jQuery(this).parent().remove()"><?php echo mep_esc_html($remove_text); ?></span>';
                            html += '<img src="'+attachment_url+'" style="width:100%"/>';
                            html += '<input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="'+attachment_id+'" />';
                            html += '</div>';
                            $('.media-list-<?php echo esc_attr($id); ?>').append(html);
                            //wp.media.editor.send.attachment = send_attachment_bkp;
                        }
                        wp.media.editor.open($(this));
                        return false;
                    });
                    $('#media_clear_<?php echo esc_attr($id); ?>').click(function() {
                        $('.media-list-<?php echo esc_attr($id); ?> .item').remove();
                    })
                });
            </script>

            <?php
            return ob_get_clean();
        }




        public function field_custom_html( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            $args 			= isset( $option['args'] ) ? $option['args'] : "";
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $html 			= isset( $option['html'] ) ? $option['html'] : "";


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;




            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-custom-html-wrapper
            field-custom-html-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                echo esc_html($html);
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();


        }

        function get_form_title($arr,$val){
            foreach ($arr as $_arr) {
                $name[] = $val[$_arr];
            }

            return join(' - ',$name);
        }

        public function field_repeatable( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $sortable 	    = isset( $option['sortable'] ) ? $option['sortable'] : true;
            $collapsible 	= isset( $option['collapsible'] ) ? $option['collapsible'] : true;
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $values			= isset( $option['value'] ) ? $option['value'] : '';
            $btntext		= isset( $option['btn_text'] ) ? $option['btn_text'] : 'Add';
            $fields 		= isset( $option['fields'] ) ? $option['fields'] : array();
            $title_field 	= isset( $option['title_field'] ) ? $option['title_field'] : '';
            $remove_text 	= isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fas fa-times"></i>';
            $limit 	        = isset( $option['limit'] ) ? $option['limit'] : '';
            $args 	        = isset( $option['args'] ) ? $option['args'] : '';
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );
            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            $new_title      =  explode('/',$title_field);
            $title_field    = $new_title;            
            foreach ($fields as $key => $value) {            
                # code...
                $new[$key]['type']      = $fields[$key]['type'];
                $new[$key]['default']   = $fields[$key]['default'];
                $new[$key]['item_id']   = $fields[$key]['item_id'];
                $new[$key]['name']      = $fields[$key]['name'];
                if(array_key_exists('args',$value)){
                 $new[$key]['args']      = !is_array($fields[$key]['args']) ? $this->args_from_string($fields[$key]['args']) : $fields[$key]['args'];
                }
                 
            }
            $fields = $new;
           

            if(!empty($conditions)):

                $depends = '';

                $field      = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type       = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern    = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier   = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like       = isset($conditions['like']) ? $conditions['like'] : '';
                $strict     = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty      = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign       = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min        = isset($conditions['min']) ? $conditions['min'] : '';
                $max        = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';
            endif;
            ob_start();
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .collapsible .header .title-text', function() {
                        if(jQuery(this).parent().parent().hasClass('active')){
                            jQuery(this).parent().parent().removeClass('active');
                        }else{
                            jQuery(this).parent().parent().addClass('active');
                        }
                    })

                    jQuery(document).on('click', '.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .clone',function(){

                        //event.preventDefault();

                        index_id = $(this).attr('index_id');
                        now = jQuery.now();
                        <?php
                        if(!empty($limit)):


                        ?>
                        var limit = <?php  echo esc_attr($limit); ?>;
                        var node_count = $( ".field-repeatable-wrapper-<?php echo esc_attr($id); ?> .field-list .item-wrap" ).size();
                        if(limit > node_count){
                            $( this ).parent().parent().clone().appendTo('.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .field-list' );
                           // html = $( this ).parent().parent().clone();
                            //var html_new = html.replace(index_id, now);
                            //jQuery('.<?php echo 'field-repeatable-wrapper-'.$id; ?> .field-list').append(html_new);
                            //console.log(html);

                        }else{
                            jQuery('.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                        }
                        <?php
                        else:
                        ?>
                        $( this ).parent().clone().appendTo('.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .field-list' );
                        <?php
                        endif;
                        ?>
                    })

                    jQuery(document).on('click', '.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .add-item', function() {
                        now = jQuery.now();
                        fields_arr = <?php echo json_encode($fields); ?>;
                        html = '<div class="item-wrap collapsible"><div class="header"><span class="button remove" ' +
                            'onclick="jQuery(this).parent().parent().remove()"><?php echo mep_esc_html($remove_text); ?></span> ';
                        
                        <?php if($sortable):?>
                        html += '<span class="button sort" ><i class="fas fa-arrows-alt"></i></span>';
                        <?php endif; ?>
                        html += ' <span  class="button title-text" style="cursor:pointer;display: inline-block;"><i class="fas fa-angle-double-down"></i> Expand</span></div>';
                        // html += ' <span  class="title-text">#'+now+'</span></div>';
                        fields_arr.forEach(function(element) {
                            type = element.type;
                            item_id = element.item_id;
                            default_val = element.default;
                            html+='<div class="item">';
                            <?php if($collapsible):?>
                            html+='<div class="content">';
                            <?php endif; ?>
                            html+='<label class="item-title">'+element.name+'</label>';
                            if(type == 'text'){
                                html+='<input type="text" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?> value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'mp_icon'){
								html+='<div class="ttbm_input_add_icon">' +
								    '<button type="button" class="ttbm_input_add_icon_button dButton_xs">' +
								    '<input type="hidden" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']" placeholder="" value=""/>' +
								    '<span class="" data-empty-text="<?php esc_html_e( 'Add Icon', 'tour-booking-manager' ); ?>"><?php esc_html_e( 'Add Icon', 'tour-booking-manager' );?></span>' +
								    '<span class="fas fa-times remove_input_icon " title="<?php esc_html_e( 'Remove Icon', 'tour-booking-manager' ); ?>"></span>' +
								    '</button>' +
								    '</div>';
                            }else if(type == 'number'){
                                html+='<input type="number" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'tel'){
                                html+='<input type="tel" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'time'){
                                html+='<input type="time" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?> name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'url'){
                                html+='<input type="url" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'date'){
                                html+='<input type="date" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?> value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'month'){
                                html+='<input type="month" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'search'){
                                html+='<input type="search" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'color'){
                                html+='<input type="color" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'email'){
                                html+='<input type="email" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'textarea'){
                                html+='<textarea <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"></textarea>';
                            }else if(type == 'select'){
                                args = element.args;
                                html+='<select <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']" class="<?php echo esc_attr($field_name); ?>">';
                                for(argKey in args){                                                                       
                                    html+='<option value="'+argKey+'">'+args[argKey]+'</option>';
                                }
                                html+='</select>';
 
                            }else if(type == 'radio'){
                                args = element.args;
                                for(argKey in args){
                                    html+='<label>';
                                    html+='<input value="'+argKey+'" type="radio" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                                    html+= args[argKey];
                                    html+='</label ><br/>';
                                }
                            }else if(type == 'checkbox'){
                                args = element.args;
                                for(argKey in args){
                                    html+='<label>';
                                    html+='<input value="'+argKey+'" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  type="checkbox" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                                    html+= args[argKey];
                                    html+='</label ><br/>';
                                }
                            }
                            <?php if($collapsible):?>
                            html+='</div>';
                            <?php endif; ?>
                            html+='</div>';
                        });
                        html+='</div>';

                        <?php
                        if(!empty($limit)):
                            ?>
                            var limit = <?php  echo esc_attr($limit); ?>;
                            var node_count = $( ".field-repeatable-wrapper-<?php echo esc_attr($id); ?> .field-list .item-wrap" ).size();
                            if(limit > node_count){
                                jQuery('.<?php echo 'field-repeatable-wrapper-'.$id; ?> .field-list').append(html);
                            }else{
                                jQuery('.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                            }

                            <?php
                        else:
                            ?>
                            jQuery('.<?php echo 'field-repeatable-wrapper-'.$id; ?> .field-list').append(html);
                            <?php
                        endif;
                        ?>
                    })
                });
            </script>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-repeatable-wrapper
            field-repeatable-wrapper-<?php echo esc_attr($id); ?>">
                
                <div class="field-list <?php if($sortable){ echo 'sortable'; }?>" id="<?php echo esc_attr($id); ?>">
                    <?php
                    if(!empty($values)):
                        $count = 1;
                        foreach ($values as $index=>$val):
                            $title_field_val = !empty($title_field) ? $this->get_form_title($title_field,$val) : '==> Click to Expand';
                            ?>
                            <div class="item-wrap <?php if($collapsible) echo 'collapsible'; ?>">
                                <?php if($collapsible):?>
                                <div class="header">
                                    <?php endif; ?>                                  
                                    <?php if($sortable):?>
                                        <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
                                    <?php endif; ?>
                                    <span class="title-text" style="cursor:pointer;display: inline-block;width: 84%;"><?php echo mep_esc_html($title_field_val); ?></span>
                                    <span class="button remove" onclick="jQuery(this).parent().parent().remove()"><?php echo mep_esc_html($remove_text); ?></span>
                                    <?php if($collapsible):?>
                                </div>
                            <?php endif; ?>
                                <?php foreach ($fields as $field_index => $field):
                                    $type               = $field['type'];
                                    $item_id            = $field['item_id'];
                                    $name               = $field['name'];
                                    $title_field_class = ($title_field == $field_index) ? 'title-field':'';
                                    ?>
                                    <div class="item <?php echo esc_attr($title_field_class); ?>">
                                        <?php if($collapsible):?>
                                        <div class="content">
                                            <?php endif; ?>
                                            <div><?php echo esc_attr($name); ?></div>
                                            <?php if($type == 'text'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="text" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'mp_icon'):
	                                            $mp_icon_name="{$field_name}[{$index}][{$item_id}]";
								  $default = isset($field['default']) ? $field['default'] : '';
								  $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
							  do_action('ttbm_input_add_icon',$mp_icon_name,$value);
	                                            ?>
                                            <?php elseif($type == 'number'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="number" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'url'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="url" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'tel'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="tel" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'time'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="time" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?> class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'search'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="search" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'month'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="month" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'color'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="color" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'date'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="date" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?> class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'email'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="email" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'textarea'):
                                                $default    = isset($field['default']) ? $field['default'] : '';
                                                $_value     = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                $__value    = str_replace('<br />', PHP_EOL, html_entity_decode($_value));;
                                                ?>
                                                <textarea <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]"><?php echo mep_esc_html($__value); ?></textarea>
                                            <?php elseif($type == 'select'):
                                                $args = isset($field['args']) ? $field['args'] : array();
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;

                                               
                                                ?>
                                                <select <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  class="" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]">
                                                <?php                                                    
                                                   if(!is_array($args)){                                                   
                                                   $this->args_from_string($args);
                                                   }else{                                                    
                                                        foreach ($args as $argIndex => $argName):
                                                        $selected = ($argIndex == $value) ? 'selected' : '';
                                                        ?>
                                                        <option <?php echo esc_attr($selected); ?>  value="<?php echo esc_attr($argIndex); ?>"><?php echo esc_html($argName); ?></option>
                                                    <?php endforeach; }?>
                                                </select>
                                            <?php elseif($type == 'radio'):
                                                $args = isset($field['args']) ? $field['args'] : array();
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <?php 
                                                if(!is_array($args)){                                                   
                                                    $this->args_from_string($args);
                                                }else{                                                  
                                                foreach ($args as $argIndex => $argName):
                                                $checked = ($argIndex == $value) ? 'checked' : '';
                                                
                                                ?>
                                                <label class="" >
                                                    <input  type="radio" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" <?php echo esc_attr($checked); ?>  value="<?php echo esc_attr($argIndex); ?>"><?php echo esc_html($argName); ?></input>
                                                </label>
                                            <?php endforeach; } ?>
                                            <?php elseif($type == 'checkbox'):
                                                $args = isset($field['args']) ? $field['args'] : array();
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <?php 
                                                
                                                foreach ($args as $argIndex => $argName):
                                                $value = is_array($value) ? $value : array();
                                                // print_r($value);
                                                $checked = in_array($argIndex, $value ) ? 'checked' : '';
                                                // $checked = isset($argIndex) ? 'checked' : '';
                                                ?>
                                                <label class="" >
                                                    <input  type="checkbox" <?php echo esc_attr(TTBM_Layout::no_pro_disabled($field_name)); ?>  name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>][]" <?php echo esc_attr($checked); ?>  value="<?php echo esc_attr($argIndex); ?>"><?php echo esc_html($argName); ?></input>
                                                </label>
                                            <?php endforeach; ?>
                                            <?php
                                            else:
                                                do_action('repeatable_custom_input_field_'.$type, $field);
                                                ?>
                                            <?php endif;?>
                                            <?php if($collapsible):?>
                                        </div>
                                    <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php
                            //endforeach;
                            $count++;
                        endforeach;
                        ?>

                        <?php
                    else:
                        ?>
                    <?php
                    endif;
                    ?>                    
                </div>
                <div class="error-mgs"></div>
                <div class="ppof-button add-item"><i class="fas fa-plus-square"></i> <?php echo esc_html($btntext); ?></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function get_tax_data($args){
            foreach ($this->get_rep_taxonomies_array( $args ) as $argIndex => $argName):
            $selected = ($argIndex == $value) ? 'selected' : ''; ?><option <?php echo esc_attr($selected); ?>  value="<?php echo esc_attr($argIndex); ?>"><?php echo esc_html($argName); ?></option> <?php endforeach;
        }


        public function args_from_string( $string ){

            if( strpos( $string, 'PAGES_IDS_ARRAY' )    !== false ) return $this->get_pages_array();
            if( strpos( $string, 'POSTS_IDS_ARRAY' )    !== false ) return $this->get_posts_array();
            if( strpos( $string, 'POST_TYPES_ARRAY' )   !== false ) return $this->get_post_types_array();
            if( strpos( $string, 'TAX_' )               !== false ) return $this->get_taxonomies_array( $string );
            if( strpos( $string, 'TAXN_' )               !== false ) return $this->get_rep_taxonomies_array( $string );
            if( strpos( $string, 'CPT_' )               !== false ) return $this->get_cpt_array( $string );
            if( strpos( $string, 'USER_ROLES' )         !== false ) return $this->get_user_roles_array();
            if( strpos( $string, 'USER_IDS_ARRAY' )     !== false ) return $this->get_user_ids_array();
            if( strpos( $string, 'MENUS' )              !== false ) return $this->get_menus_array();
            if( strpos( $string, 'SIDEBARS_ARRAY' )     !== false ) return $this->get_sidebars_array();
            if( strpos( $string, 'THUMB_SIEZS_ARRAY' )  !== false ) return $this->get_thumb_sizes_array();
            if( strpos( $string, 'FONTAWESOME_ARRAY' )  !== false ) return $this->get_font_aws_array();
            return array();
        }




        public function get_rep_taxonomies_array( $string ){

            $taxonomies = array();

            preg_match_all( "/\%([^\]]*)\%/", $string, $matches );

            if( isset( $matches[1][0] ) ) $taxonomy = $matches[1][0];
            else throw new Pick_error('Invalid taxonomy declaration !');

            if( ! taxonomy_exists( $taxonomy ) ) throw new Pick_error("Taxonomy <strong>$taxonomy</strong> doesn't exists !");

            $terms = get_terms( $taxonomy, array(
                'hide_empty' => false,
            ) );

            foreach( $terms as $term ) $taxonomies[ $term->name ] = $term->name;

            return $taxonomies;
        }



        public function get_taxonomies_array( $string ){
            $taxonomies = array();
            preg_match_all( "/\%([^\]]*)\%/", $string, $matches );
            if( isset( $matches[1][0] ) ) $taxonomy = $matches[1][0];
            else throw new Pick_error('Invalid taxonomy declaration !');
            if( ! taxonomy_exists( $taxonomy ) ) throw new Pick_error("Taxonomy <strong>$taxonomy</strong> doesn't exists !");
            $terms = get_terms( $taxonomy, array(
                'hide_empty' => false,
            ) );
            foreach( $terms as $term ) $taxonomies[ $term->term_id ] = $term->name;
            return $taxonomies;
        }

        public function get_cpt_array( $string ){
            preg_match_all( "/\%([^\]]*)\%/", $string, $matches );
            $cpt_name = $matches[1][0];
            $defaults = array(
                'numberposts'      => -1,
                'post_type' => $cpt_name,
            );
            $cpt_arr = get_posts($defaults);
            $cpt = array();
            foreach ($cpt_arr as $_cpt_arr) {
                $cpt[$_cpt_arr->ID]  = $_cpt_arr->post_title;
            }
            return $cpt;
        }

        public function get_user_ids_array(){
            $user_ids = array();
            $users = get_users();
            foreach( $users as $user ) $user_ids[ $user->ID ] = $user->display_name. '(#'.$user->ID.')';
            return apply_filters( 'USER_IDS_ARRAY', $user_ids );
        }


        public function get_pages_array(){
            $pages_array = array();
            foreach( get_pages() as $page ) $pages_array[ $page->ID ] = $page->post_title;
            return apply_filters( 'PAGES_IDS_ARRAY', $pages_array );
        }

        public function get_menus_array(){
            $menus = get_registered_nav_menus();
            return apply_filters( 'MENUS_ARRAY', $menus );
        }

        public function get_sidebars_array(){

            global $wp_registered_sidebars;
            $sidebars = $wp_registered_sidebars;

            foreach ($sidebars as $index => $sidebar){

                $sidebars_name[$index] = $sidebar['name'];
            }


            return apply_filters( 'SIDEBARS_ARRAY', $sidebars_name );
        }

        public function get_user_roles_array(){
            require_once ABSPATH . 'wp-admin/includes/user.php';

            $roles = get_editable_roles();

            foreach ($roles as $index => $data){

                $role_name[$index] = $data['name'];
            }

            return apply_filters( 'USER_ROLES', $role_name );
        }



        public function get_post_types_array(){

            $post_types = get_post_types('', 'names' );
            $pages_array = array();
            foreach( $post_types as $index => $name ) $pages_array[ $index ] = $name;

            return apply_filters( 'POST_TYPES_ARRAY', $pages_array );
        }


        public function get_posts_array(){

            $posts_array = array();
            foreach( get_posts(array('posts_per_page'=>-1)) as $page ) $posts_array[ $page->ID ] = $page->post_title;

            return apply_filters( 'POSTS_IDS_ARRAY', $posts_array );
        }


        public function get_thumb_sizes_array(){

            $get_intermediate_image_sizes =  get_intermediate_image_sizes();
            $get_intermediate_image_sizes = array_merge($get_intermediate_image_sizes,array('full'));
            $thumb_sizes_array = array();

            foreach( $get_intermediate_image_sizes as $key => $name ):
                $size_key = str_replace('_', ' ',$name);
                $size_key = str_replace('-', ' ',$size_key);
                $size_name = ucfirst($size_key);
                $thumb_sizes_array[$name] = $size_name;
            endforeach;

            return apply_filters( 'THUMB_SIEZS_ARRAY', $get_intermediate_image_sizes );
        }


        public function get_font_aws_array(){

            $fonts_arr = array (
                'fab fa-500px' => __( '500px', 'tour-booking-manager' ),
                'fab fa-accessible-icon' => __( 'accessible-icon', 'tour-booking-manager' ),
                'fab fa-accusoft' => __( 'accusoft', 'tour-booking-manager' ),
                'fas fa-address-book' => __( 'address-book', 'tour-booking-manager' ),
                'far fa-address-book' => __( 'address-book', 'tour-booking-manager' ),
                'fas fa-address-card' => __( 'address-card', 'tour-booking-manager' ),
                'far fa-address-card' => __( 'address-card', 'tour-booking-manager' ),
                'fas fa-adjust' => __( 'adjust', 'tour-booking-manager' ),
                'fab fa-adn' => __( 'adn', 'tour-booking-manager' ),
                'fab fa-adversal' => __( 'adversal', 'tour-booking-manager' ),
                'fab fa-affiliatetheme' => __( 'affiliatetheme', 'tour-booking-manager' ),
                'fab fa-algolia' => __( 'algolia', 'tour-booking-manager' ),
                'fas fa-align-center' => __( 'align-center', 'tour-booking-manager' ),
                'fas fa-align-justify' => __( 'align-justify', 'tour-booking-manager' ),
                'fas fa-align-left' => __( 'align-left', 'tour-booking-manager' ),
                'fas fa-align-right' => __( 'align-right', 'tour-booking-manager' ),
                'fas fa-allergies' => __( 'allergies', 'tour-booking-manager' ),
                'fab fa-amazon' => __( 'amazon', 'tour-booking-manager' ),
                'fab fa-amazon-pay' => __( 'amazon-pay', 'tour-booking-manager' ),
                'fas fa-ambulance' => __( 'ambulance', 'tour-booking-manager' ),
                'fas fa-american-sign-language-interpreting' => __( 'american-sign-language-interpreting', 'tour-booking-manager' ),
                'fab fa-amilia' => __( 'amilia', 'tour-booking-manager' ),
                'fas fa-anchor' => __( 'anchor', 'tour-booking-manager' ),
                'fab fa-android' => __( 'android', 'tour-booking-manager' ),
                'fab fa-angellist' => __( 'angellist', 'tour-booking-manager' ),
                'fas fa-angle-double-down' => __( 'angle-double-down', 'tour-booking-manager' ),
                'fas fa-angle-double-left' => __( 'angle-double-left', 'tour-booking-manager' ),
                'fas fa-angle-double-right' => __( 'angle-double-right', 'tour-booking-manager' ),
                'fas fa-angle-double-up' => __( 'angle-double-up', 'tour-booking-manager' ),
                'fas fa-angle-down' => __( 'angle-down', 'tour-booking-manager' ),
                'fas fa-angle-left' => __( 'angle-left', 'tour-booking-manager' ),
                'fas fa-angle-right' => __( 'angle-right', 'tour-booking-manager' ),
                'fas fa-angle-up' => __( 'angle-up', 'tour-booking-manager' ),
                'fab fa-angrycreative' => __( 'angrycreative', 'tour-booking-manager' ),
                'fab fa-angular' => __( 'angular', 'tour-booking-manager' ),
                'fab fa-app-store' => __( 'app-store', 'tour-booking-manager' ),
                'fab fa-app-store-ios' => __( 'app-store-ios', 'tour-booking-manager' ),
                'fab fa-apper' => __( 'apper', 'tour-booking-manager' ),
                'fab fa-apple' => __( 'apple', 'tour-booking-manager' ),
                'fab fa-apple-pay' => __( 'apple-pay', 'tour-booking-manager' ),
                'fas fa-archive' => __( 'archive', 'tour-booking-manager' ),
                'fas fa-arrow-alt-circle-down' => __( 'arrow-alt-circle-down', 'tour-booking-manager' ),
                'far fa-arrow-alt-circle-down' => __( 'arrow-alt-circle-down', 'tour-booking-manager' ),
                'fas fa-arrow-alt-circle-left' => __( 'arrow-alt-circle-left', 'tour-booking-manager' ),
                'far fa-arrow-alt-circle-left' => __( 'arrow-alt-circle-left', 'tour-booking-manager' ),
                'fas fa-arrow-alt-circle-right' => __( 'arrow-alt-circle-right', 'tour-booking-manager' ),
                'far fa-arrow-alt-circle-right' => __( 'arrow-alt-circle-right', 'tour-booking-manager' ),
                'fas fa-arrow-alt-circle-up' => __( 'arrow-alt-circle-up', 'tour-booking-manager' ),
                'far fa-arrow-alt-circle-up' => __( 'arrow-alt-circle-up', 'tour-booking-manager' ),
                'fas fa-arrow-circle-down' => __( 'arrow-circle-down', 'tour-booking-manager' ),
                'fas fa-arrow-circle-left' => __( 'arrow-circle-left', 'tour-booking-manager' ),
                'fas fa-arrow-circle-right' => __( 'arrow-circle-right', 'tour-booking-manager' ),
                'fas fa-arrow-circle-up' => __( 'arrow-circle-up', 'tour-booking-manager' ),
                'fas fa-arrow-down' => __( 'arrow-down', 'tour-booking-manager' ),
                'fas fa-arrow-left' => __( 'arrow-left', 'tour-booking-manager' ),
                'fas fa-arrow-right' => __( 'arrow-right', 'tour-booking-manager' ),
                'fas fa-arrow-up' => __( 'arrow-up', 'tour-booking-manager' ),
                'fas fa-arrows-alt' => __( 'arrows-alt', 'tour-booking-manager' ),
                'fas fa-arrows-alt-h' => __( 'arrows-alt-h', 'tour-booking-manager' ),
                'fas fa-arrows-alt-v' => __( 'arrows-alt-v', 'tour-booking-manager' ),
                'fas fa-assistive-listening-systems' => __( 'assistive-listening-systems', 'tour-booking-manager' ),
                'fas fa-asterisk' => __( 'asterisk', 'tour-booking-manager' ),
                'fab fa-asymmetrik' => __( 'asymmetrik', 'tour-booking-manager' ),
                'fas fa-at' => __( 'at', 'tour-booking-manager' ),
                'fab fa-audible' => __( 'audible', 'tour-booking-manager' ),
                'fas fa-audio-description' => __( 'audio-description', 'tour-booking-manager' ),
                'fab fa-autoprefixer' => __( 'autoprefixer', 'tour-booking-manager' ),
                'fab fa-avianex' => __( 'avianex', 'tour-booking-manager' ),
                'fab fa-aviato' => __( 'aviato', 'tour-booking-manager' ),
                'fab fa-aws' => __( 'aws', 'tour-booking-manager' ),
                'fas fa-backward' => __( 'backward', 'tour-booking-manager' ),
                'fas fa-balance-scale' => __( 'balance-scale', 'tour-booking-manager' ),
                'fas fa-ban' => __( 'ban', 'tour-booking-manager' ),
                'fas fa-band-aid' => __( 'band-aid', 'tour-booking-manager' ),
                'fab fa-bandcamp' => __( 'bandcamp', 'tour-booking-manager' ),
                'fas fa-barcode' => __( 'barcode', 'tour-booking-manager' ),
                'fas fa-bars' => __( 'bars', 'tour-booking-manager' ),
                'fas fa-baseball-ball' => __( 'baseball-ball', 'tour-booking-manager' ),
                'fas fa-basketball-ball' => __( 'basketball-ball', 'tour-booking-manager' ),
                'fas fa-bath' => __( 'bath', 'tour-booking-manager' ),
                'fas fa-battery-empty' => __( 'battery-empty', 'tour-booking-manager' ),
                'fas fa-battery-full' => __( 'battery-full', 'tour-booking-manager' ),
                'fas fa-battery-half' => __( 'battery-half', 'tour-booking-manager' ),
                'fas fa-battery-quarter' => __( 'battery-quarter', 'tour-booking-manager' ),
                'fas fa-battery-three-quarters' => __( 'battery-three-quarters', 'tour-booking-manager' ),
                'fas fa-bed' => __( 'bed', 'tour-booking-manager' ),
                'fas fa-beer' => __( 'beer', 'tour-booking-manager' ),
                'fab fa-behance' => __( 'behance', 'tour-booking-manager' ),
                'fab fa-behance-square' => __( 'behance-square', 'tour-booking-manager' ),
                'fas fa-bell' => __( 'bell', 'tour-booking-manager' ),
                'far fa-bell' => __( 'bell', 'tour-booking-manager' ),
                'fas fa-bell-slash' => __( 'bell-slash', 'tour-booking-manager' ),
                'far fa-bell-slash' => __( 'bell-slash', 'tour-booking-manager' ),
                'fas fa-bicycle' => __( 'bicycle', 'tour-booking-manager' ),
                'fab fa-bimobject' => __( 'bimobject', 'tour-booking-manager' ),
                'fas fa-binoculars' => __( 'binoculars', 'tour-booking-manager' ),
                'fas fa-birthday-cake' => __( 'birthday-cake', 'tour-booking-manager' ),
                'fab fa-bitbucket' => __( 'bitbucket', 'tour-booking-manager' ),
                'fab fa-bitcoin' => __( 'bitcoin', 'tour-booking-manager' ),
                'fab fa-bity' => __( 'bity', 'tour-booking-manager' ),
                'fab fa-black-tie' => __( 'black-tie', 'tour-booking-manager' ),
                'fab fa-blackberry' => __( 'blackberry', 'tour-booking-manager' ),
                'fas fa-blind' => __( 'blind', 'tour-booking-manager' ),
                'fab fa-blogger' => __( 'blogger', 'tour-booking-manager' ),
                'fab fa-blogger-b' => __( 'blogger-b', 'tour-booking-manager' ),
                'fab fa-bluetooth' => __( 'bluetooth', 'tour-booking-manager' ),
                'fab fa-bluetooth-b' => __( 'bluetooth-b', 'tour-booking-manager' ),
                'fas fa-bold' => __( 'bold', 'tour-booking-manager' ),
                'fas fa-bolt' => __( 'bolt', 'tour-booking-manager' ),
                'fas fa-bomb' => __( 'bomb', 'tour-booking-manager' ),
                'fas fa-book' => __( 'book', 'tour-booking-manager' ),
                'fas fa-bookmark' => __( 'bookmark', 'tour-booking-manager' ),
                'far fa-bookmark' => __( 'bookmark', 'tour-booking-manager' ),
                'fas fa-bowling-ball' => __( 'bowling-ball', 'tour-booking-manager' ),
                'fas fa-box' => __( 'box', 'tour-booking-manager' ),
                'fas fa-box-open' => __( 'box-open', 'tour-booking-manager' ),
                'fas fa-boxes' => __( 'boxes', 'tour-booking-manager' ),
                'fas fa-braille' => __( 'braille', 'tour-booking-manager' ),
                'fas fa-briefcase' => __( 'briefcase', 'tour-booking-manager' ),
                'fas fa-briefcase-medical' => __( 'briefcase-medical', 'tour-booking-manager' ),
                'fab fa-btc' => __( 'btc', 'tour-booking-manager' ),
                'fas fa-bug' => __( 'bug', 'tour-booking-manager' ),
                'fas fa-building' => __( 'building', 'tour-booking-manager' ),
                'far fa-building' => __( 'building', 'tour-booking-manager' ),
                'fas fa-bullhorn' => __( 'bullhorn', 'tour-booking-manager' ),
                'fas fa-bullseye' => __( 'bullseye', 'tour-booking-manager' ),
                'fas fa-burn' => __( 'burn', 'tour-booking-manager' ),
                'fab fa-buromobelexperte' => __( 'buromobelexperte', 'tour-booking-manager' ),
                'fas fa-bus' => __( 'bus', 'tour-booking-manager' ),
                'fab fa-buysellads' => __( 'buysellads', 'tour-booking-manager' ),
                'fas fa-calculator' => __( 'calculator', 'tour-booking-manager' ),
                'fas fa-calendar' => __( 'calendar', 'tour-booking-manager' ),
                'far fa-calendar' => __( 'calendar', 'tour-booking-manager' ),
                'fas fa-calendar-alt' => __( 'calendar-alt', 'tour-booking-manager' ),
                'far fa-calendar-alt' => __( 'calendar-alt', 'tour-booking-manager' ),
                'fas fa-calendar-check' => __( 'calendar-check', 'tour-booking-manager' ),
                'far fa-calendar-check' => __( 'calendar-check', 'tour-booking-manager' ),
                'fas fa-calendar-minus' => __( 'calendar-minus', 'tour-booking-manager' ),
                'far fa-calendar-minus' => __( 'calendar-minus', 'tour-booking-manager' ),
                'fas fa-calendar-plus' => __( 'calendar-plus', 'tour-booking-manager' ),
                'far fa-calendar-plus' => __( 'calendar-plus', 'tour-booking-manager' ),
                'fas fa-calendar-times' => __( 'calendar-times', 'tour-booking-manager' ),
                'far fa-calendar-times' => __( 'calendar-times', 'tour-booking-manager' ),
                'fas fa-camera' => __( 'camera', 'tour-booking-manager' ),
                'fas fa-camera-retro' => __( 'camera-retro', 'tour-booking-manager' ),
                'fas fa-capsules' => __( 'capsules', 'tour-booking-manager' ),
                'fas fa-car' => __( 'car', 'tour-booking-manager' ),
                'fas fa-caret-down' => __( 'caret-down', 'tour-booking-manager' ),
                'fas fa-caret-left' => __( 'caret-left', 'tour-booking-manager' ),
                'fas fa-caret-right' => __( 'caret-right', 'tour-booking-manager' ),
                'fas fa-caret-square-down' => __( 'caret-square-down', 'tour-booking-manager' ),
                'far fa-caret-square-down' => __( 'caret-square-down', 'tour-booking-manager' ),
                'fas fa-caret-square-left' => __( 'caret-square-left', 'tour-booking-manager' ),
                'far fa-caret-square-left' => __( 'caret-square-left', 'tour-booking-manager' ),
                'fas fa-caret-square-right' => __( 'caret-square-right', 'tour-booking-manager' ),
                'far fa-caret-square-right' => __( 'caret-square-right', 'tour-booking-manager' ),
                'fas fa-caret-square-up' => __( 'caret-square-up', 'tour-booking-manager' ),
                'far fa-caret-square-up' => __( 'caret-square-up', 'tour-booking-manager' ),
                'fas fa-caret-up' => __( 'caret-up', 'tour-booking-manager' ),
                'fas fa-cart-arrow-down' => __( 'cart-arrow-down', 'tour-booking-manager' ),
                'fas fa-cart-plus' => __( 'cart-plus', 'tour-booking-manager' ),
                'fab fa-cc-amazon-pay' => __( 'cc-amazon-pay', 'tour-booking-manager' ),
                'fab fa-cc-amex' => __( 'cc-amex', 'tour-booking-manager' ),
                'fab fa-cc-apple-pay' => __( 'cc-apple-pay', 'tour-booking-manager' ),
                'fab fa-cc-diners-club' => __( 'cc-diners-club', 'tour-booking-manager' ),
                'fab fa-cc-discover' => __( 'cc-discover', 'tour-booking-manager' ),
                'fab fa-cc-jcb' => __( 'cc-jcb', 'tour-booking-manager' ),
                'fab fa-cc-mastercard' => __( 'cc-mastercard', 'tour-booking-manager' ),
                'fab fa-cc-paypal' => __( 'cc-paypal', 'tour-booking-manager' ),
                'fab fa-cc-stripe' => __( 'cc-stripe', 'tour-booking-manager' ),
                'fab fa-cc-visa' => __( 'cc-visa', 'tour-booking-manager' ),
                'fab fa-centercode' => __( 'centercode', 'tour-booking-manager' ),
                'fas fa-certificate' => __( 'certificate', 'tour-booking-manager' ),
                'fas fa-chart-area' => __( 'chart-area', 'tour-booking-manager' ),
                'fas fa-chart-bar' => __( 'chart-bar', 'tour-booking-manager' ),
                'far fa-chart-bar' => __( 'chart-bar', 'tour-booking-manager' ),
                'fas fa-chart-line' => __( 'chart-line', 'tour-booking-manager' ),
                'fas fa-chart-pie' => __( 'chart-pie', 'tour-booking-manager' ),
                'fas fa-check' => __( 'check', 'tour-booking-manager' ),
                'fas fa-check-circle' => __( 'check-circle', 'tour-booking-manager' ),
                'far fa-check-circle' => __( 'check-circle', 'tour-booking-manager' ),
                'fas fa-check-square' => __( 'check-square', 'tour-booking-manager' ),
                'far fa-check-square' => __( 'check-square', 'tour-booking-manager' ),
                'fas fa-chess' => __( 'chess', 'tour-booking-manager' ),
                'fas fa-chess-bishop' => __( 'chess-bishop', 'tour-booking-manager' ),
                'fas fa-chess-board' => __( 'chess-board', 'tour-booking-manager' ),
                'fas fa-chess-king' => __( 'chess-king', 'tour-booking-manager' ),
                'fas fa-chess-knight' => __( 'chess-knight', 'tour-booking-manager' ),
                'fas fa-chess-pawn' => __( 'chess-pawn', 'tour-booking-manager' ),
                'fas fa-chess-queen' => __( 'chess-queen', 'tour-booking-manager' ),
                'fas fa-chess-rook' => __( 'chess-rook', 'tour-booking-manager' ),
                'fas fa-chevron-circle-down' => __( 'chevron-circle-down', 'tour-booking-manager' ),
                'fas fa-chevron-circle-left' => __( 'chevron-circle-left', 'tour-booking-manager' ),
                'fas fa-chevron-circle-right' => __( 'chevron-circle-right', 'tour-booking-manager' ),
                'fas fa-chevron-circle-up' => __( 'chevron-circle-up', 'tour-booking-manager' ),
                'fas fa-chevron-down' => __( 'chevron-down', 'tour-booking-manager' ),
                'fas fa-chevron-left' => __( 'chevron-left', 'tour-booking-manager' ),
                'fas fa-chevron-right' => __( 'chevron-right', 'tour-booking-manager' ),
                'fas fa-chevron-up' => __( 'chevron-up', 'tour-booking-manager' ),
                'fas fa-child' => __( 'child', 'tour-booking-manager' ),
                'fab fa-chrome' => __( 'chrome', 'tour-booking-manager' ),
                'fas fa-circle' => __( 'circle', 'tour-booking-manager' ),
                'far fa-circle' => __( 'circle', 'tour-booking-manager' ),
                'fas fa-circle-notch' => __( 'circle-notch', 'tour-booking-manager' ),
                'fas fa-clipboard' => __( 'clipboard', 'tour-booking-manager' ),
                'far fa-clipboard' => __( 'clipboard', 'tour-booking-manager' ),
                'fas fa-clipboard-check' => __( 'clipboard-check', 'tour-booking-manager' ),
                'fas fa-clipboard-list' => __( 'clipboard-list', 'tour-booking-manager' ),
                'fas fa-clock' => __( 'clock', 'tour-booking-manager' ),
                'far fa-clock' => __( 'clock', 'tour-booking-manager' ),
                'fas fa-clone' => __( 'clone', 'tour-booking-manager' ),
                'far fa-clone' => __( 'clone', 'tour-booking-manager' ),
                'fas fa-closed-captioning' => __( 'closed-captioning', 'tour-booking-manager' ),
                'far fa-closed-captioning' => __( 'closed-captioning', 'tour-booking-manager' ),
                'fas fa-cloud' => __( 'cloud', 'tour-booking-manager' ),
                'fas fa-cloud-download-alt' => __( 'cloud-download-alt', 'tour-booking-manager' ),
                'fas fa-cloud-upload-alt' => __( 'cloud-upload-alt', 'tour-booking-manager' ),
                'fab fa-cloudscale' => __( 'cloudscale', 'tour-booking-manager' ),
                'fab fa-cloudsmith' => __( 'cloudsmith', 'tour-booking-manager' ),
                'fab fa-cloudversify' => __( 'cloudversify', 'tour-booking-manager' ),
                'fas fa-code' => __( 'code', 'tour-booking-manager' ),
                'fas fa-code-branch' => __( 'code-branch', 'tour-booking-manager' ),
                'fab fa-codepen' => __( 'codepen', 'tour-booking-manager' ),
                'fab fa-codiepie' => __( 'codiepie', 'tour-booking-manager' ),
                'fas fa-coffee' => __( 'coffee', 'tour-booking-manager' ),
                'fas fa-cog' => __( 'cog', 'tour-booking-manager' ),
                'fas fa-cogs' => __( 'cogs', 'tour-booking-manager' ),
                'fas fa-columns' => __( 'columns', 'tour-booking-manager' ),
                'fas fa-comment' => __( 'comment', 'tour-booking-manager' ),
                'far fa-comment' => __( 'comment', 'tour-booking-manager' ),
                'fas fa-comment-alt' => __( 'comment-alt', 'tour-booking-manager' ),
                'far fa-comment-alt' => __( 'comment-alt', 'tour-booking-manager' ),
                'fas fa-comment-dots' => __( 'comment-dots', 'tour-booking-manager' ),
                'fas fa-comment-slash' => __( 'comment-slash', 'tour-booking-manager' ),
                'fas fa-comments' => __( 'comments', 'tour-booking-manager' ),
                'far fa-comments' => __( 'comments', 'tour-booking-manager' ),
                'fas fa-compass' => __( 'compass', 'tour-booking-manager' ),
                'far fa-compass' => __( 'compass', 'tour-booking-manager' ),
                'fas fa-compress' => __( 'compress', 'tour-booking-manager' ),
                'fab fa-connectdevelop' => __( 'connectdevelop', 'tour-booking-manager' ),
                'fab fa-contao' => __( 'contao', 'tour-booking-manager' ),
                'fas fa-copy' => __( 'copy', 'tour-booking-manager' ),
                'far fa-copy' => __( 'copy', 'tour-booking-manager' ),
                'fas fa-copyright' => __( 'copyright', 'tour-booking-manager' ),
                'far fa-copyright' => __( 'copyright', 'tour-booking-manager' ),
                'fas fa-couch' => __( 'couch', 'tour-booking-manager' ),
                'fab fa-cpanel' => __( 'cpanel', 'tour-booking-manager' ),
                'fab fa-creative-commons' => __( 'creative-commons', 'tour-booking-manager' ),
                'fas fa-credit-card' => __( 'credit-card', 'tour-booking-manager' ),
                'far fa-credit-card' => __( 'credit-card', 'tour-booking-manager' ),
                'fas fa-crop' => __( 'crop', 'tour-booking-manager' ),
                'fas fa-crosshairs' => __( 'crosshairs', 'tour-booking-manager' ),
                'fab fa-css3' => __( 'css3', 'tour-booking-manager' ),
                'fab fa-css3-alt' => __( 'css3-alt', 'tour-booking-manager' ),
                'fas fa-cube' => __( 'cube', 'tour-booking-manager' ),
                'fas fa-cubes' => __( 'cubes', 'tour-booking-manager' ),
                'fas fa-cut' => __( 'cut', 'tour-booking-manager' ),
                'fab fa-cuttlefish' => __( 'cuttlefish', 'tour-booking-manager' ),
                'fab fa-d-and-d' => __( 'd-and-d', 'tour-booking-manager' ),
                'fab fa-dashcube' => __( 'dashcube', 'tour-booking-manager' ),
                'fas fa-database' => __( 'database', 'tour-booking-manager' ),
                'fas fa-deaf' => __( 'deaf', 'tour-booking-manager' ),
                'fab fa-delicious' => __( 'delicious', 'tour-booking-manager' ),
                'fab fa-deploydog' => __( 'deploydog', 'tour-booking-manager' ),
                'fab fa-deskpro' => __( 'deskpro', 'tour-booking-manager' ),
                'fas fa-desktop' => __( 'desktop', 'tour-booking-manager' ),
                'fab fa-deviantart' => __( 'deviantart', 'tour-booking-manager' ),
                'fas fa-diagnoses' => __( 'diagnoses', 'tour-booking-manager' ),
                'fab fa-digg' => __( 'digg', 'tour-booking-manager' ),
                'fab fa-digital-ocean' => __( 'digital-ocean', 'tour-booking-manager' ),
                'fab fa-discord' => __( 'discord', 'tour-booking-manager' ),
                'fab fa-discourse' => __( 'discourse', 'tour-booking-manager' ),
                'fas fa-dna' => __( 'dna', 'tour-booking-manager' ),
                'fab fa-dochub' => __( 'dochub', 'tour-booking-manager' ),
                'fab fa-docker' => __( 'docker', 'tour-booking-manager' ),
                'fas fa-dollar-sign' => __( 'dollar-sign', 'tour-booking-manager' ),
                'fas fa-dolly' => __( 'dolly', 'tour-booking-manager' ),
                'fas fa-dolly-flatbed' => __( 'dolly-flatbed', 'tour-booking-manager' ),
                'fas fa-donate' => __( 'donate', 'tour-booking-manager' ),
                'fas fa-dot-circle' => __( 'dot-circle', 'tour-booking-manager' ),
                'far fa-dot-circle' => __( 'dot-circle', 'tour-booking-manager' ),
                'fas fa-dove' => __( 'dove', 'tour-booking-manager' ),
                'fas fa-download' => __( 'download', 'tour-booking-manager' ),
                'fab fa-draft2digital' => __( 'draft2digital', 'tour-booking-manager' ),
                'fab fa-dribbble' => __( 'dribbble', 'tour-booking-manager' ),
                'fab fa-dribbble-square' => __( 'dribbble-square', 'tour-booking-manager' ),
                'fab fa-dropbox' => __( 'dropbox', 'tour-booking-manager' ),
                'fab fa-drupal' => __( 'drupal', 'tour-booking-manager' ),
                'fab fa-dyalog' => __( 'dyalog', 'tour-booking-manager' ),
                'fab fa-earlybirds' => __( 'earlybirds', 'tour-booking-manager' ),
                'fab fa-edge' => __( 'edge', 'tour-booking-manager' ),
                'fas fa-edit' => __( 'edit', 'tour-booking-manager' ),
                'far fa-edit' => __( 'edit', 'tour-booking-manager' ),
                'fas fa-eject' => __( 'eject', 'tour-booking-manager' ),
                'fab fa-elementor' => __( 'elementor', 'tour-booking-manager' ),
                'fas fa-ellipsis-h' => __( 'ellipsis-h', 'tour-booking-manager' ),
                'fas fa-ellipsis-v' => __( 'ellipsis-v', 'tour-booking-manager' ),
                'fab fa-ember' => __( 'ember', 'tour-booking-manager' ),
                'fab fa-empire' => __( 'empire', 'tour-booking-manager' ),
                'fas fa-envelope' => __( 'envelope', 'tour-booking-manager' ),
                'far fa-envelope' => __( 'envelope', 'tour-booking-manager' ),
                'fas fa-envelope-open' => __( 'envelope-open', 'tour-booking-manager' ),
                'far fa-envelope-open' => __( 'envelope-open', 'tour-booking-manager' ),
                'fas fa-envelope-square' => __( 'envelope-square', 'tour-booking-manager' ),
                'fab fa-envira' => __( 'envira', 'tour-booking-manager' ),
                'fas fa-eraser' => __( 'eraser', 'tour-booking-manager' ),
                'fab fa-erlang' => __( 'erlang', 'tour-booking-manager' ),
                'fab fa-ethereum' => __( 'ethereum', 'tour-booking-manager' ),
                'fab fa-etsy' => __( 'etsy', 'tour-booking-manager' ),
                'fas fa-euro-sign' => __( 'euro-sign', 'tour-booking-manager' ),
                'fas fa-exchange-alt' => __( 'exchange-alt', 'tour-booking-manager' ),
                'fas fa-exclamation' => __( 'exclamation', 'tour-booking-manager' ),
                'fas fa-exclamation-circle' => __( 'exclamation-circle', 'tour-booking-manager' ),
                'fas fa-exclamation-triangle' => __( 'exclamation-triangle', 'tour-booking-manager' ),
                'fas fa-expand' => __( 'expand', 'tour-booking-manager' ),
                'fas fa-expand-arrows-alt' => __( 'expand-arrows-alt', 'tour-booking-manager' ),
                'fab fa-expeditedssl' => __( 'expeditedssl', 'tour-booking-manager' ),
                'fas fa-external-link-alt' => __( 'external-link-alt', 'tour-booking-manager' ),
                'fas fa-external-link-square-alt' => __( 'external-link-square-alt', 'tour-booking-manager' ),
                'fas fa-eye' => __( 'eye', 'tour-booking-manager' ),
                'fas fa-eye-dropper' => __( 'eye-dropper', 'tour-booking-manager' ),
                'fas fa-eye-slash' => __( 'eye-slash', 'tour-booking-manager' ),
                'far fa-eye-slash' => __( 'eye-slash', 'tour-booking-manager' ),
                'fab fa-facebook' => __( 'facebook', 'tour-booking-manager' ),
                'fab fa-facebook-f' => __( 'facebook-f', 'tour-booking-manager' ),
                'fab fa-facebook-messenger' => __( 'facebook-messenger', 'tour-booking-manager' ),
                'fab fa-facebook-square' => __( 'facebook-square', 'tour-booking-manager' ),
                'fas fa-fast-backward' => __( 'fast-backward', 'tour-booking-manager' ),
                'fas fa-fast-forward' => __( 'fast-forward', 'tour-booking-manager' ),
                'fas fa-fax' => __( 'fax', 'tour-booking-manager' ),
                'fas fa-female' => __( 'female', 'tour-booking-manager' ),
                'fas fa-fighter-jet' => __( 'fighter-jet', 'tour-booking-manager' ),
                'fas fa-file' => __( 'file', 'tour-booking-manager' ),
                'far fa-file' => __( 'file', 'tour-booking-manager' ),
                'fas fa-file-alt' => __( 'file-alt', 'tour-booking-manager' ),
                'far fa-file-alt' => __( 'file-alt', 'tour-booking-manager' ),
                'fas fa-file-archive' => __( 'file-archive', 'tour-booking-manager' ),
                'far fa-file-archive' => __( 'file-archive', 'tour-booking-manager' ),
                'fas fa-file-audio' => __( 'file-audio', 'tour-booking-manager' ),
                'far fa-file-audio' => __( 'file-audio', 'tour-booking-manager' ),
                'fas fa-file-code' => __( 'file-code', 'tour-booking-manager' ),
                'far fa-file-code' => __( 'file-code', 'tour-booking-manager' ),
                'fas fa-file-excel' => __( 'file-excel', 'tour-booking-manager' ),
                'far fa-file-excel' => __( 'file-excel', 'tour-booking-manager' ),
                'fas fa-file-image' => __( 'file-image', 'tour-booking-manager' ),
                'far fa-file-image' => __( 'file-image', 'tour-booking-manager' ),
                'fas fa-file-medical' => __( 'file-medical', 'tour-booking-manager' ),
                'fas fa-file-medical-alt' => __( 'file-medical-alt', 'tour-booking-manager' ),
                'fas fa-file-pdf' => __( 'file-pdf', 'tour-booking-manager' ),
                'far fa-file-pdf' => __( 'file-pdf', 'tour-booking-manager' ),
                'fas fa-file-powerpoint' => __( 'file-powerpoint', 'tour-booking-manager' ),
                'far fa-file-powerpoint' => __( 'file-powerpoint', 'tour-booking-manager' ),
                'fas fa-file-video' => __( 'file-video', 'tour-booking-manager' ),
                'far fa-file-video' => __( 'file-video', 'tour-booking-manager' ),
                'fas fa-file-word' => __( 'file-word', 'tour-booking-manager' ),
                'far fa-file-word' => __( 'file-word', 'tour-booking-manager' ),
                'fas fa-film' => __( 'film', 'tour-booking-manager' ),
                'fas fa-filter' => __( 'filter', 'tour-booking-manager' ),
                'fas fa-fire' => __( 'fire', 'tour-booking-manager' ),
                'fas fa-fire-extinguisher' => __( 'fire-extinguisher', 'tour-booking-manager' ),
                'fab fa-firefox' => __( 'firefox', 'tour-booking-manager' ),
                'fas fa-first-aid' => __( 'first-aid', 'tour-booking-manager' ),
                'fab fa-first-order' => __( 'first-order', 'tour-booking-manager' ),
                'fab fa-firstdraft' => __( 'firstdraft', 'tour-booking-manager' ),
                'fas fa-flag' => __( 'flag', 'tour-booking-manager' ),
                'far fa-flag' => __( 'flag', 'tour-booking-manager' ),
                'fas fa-flag-checkered' => __( 'flag-checkered', 'tour-booking-manager' ),
                'fas fa-flask' => __( 'flask', 'tour-booking-manager' ),
                'fab fa-flickr' => __( 'flickr', 'tour-booking-manager' ),
                'fab fa-flipboard' => __( 'flipboard', 'tour-booking-manager' ),
                'fab fa-fly' => __( 'fly', 'tour-booking-manager' ),
                'fas fa-folder' => __( 'folder', 'tour-booking-manager' ),
                'far fa-folder' => __( 'folder', 'tour-booking-manager' ),
                'fas fa-folder-open' => __( 'folder-open', 'tour-booking-manager' ),
                'far fa-folder-open' => __( 'folder-open', 'tour-booking-manager' ),
                'fas fa-font' => __( 'font', 'tour-booking-manager' ),
                'fab fa-font-awesome' => __( 'font-awesome', 'tour-booking-manager' ),
                'fab fa-font-awesome-alt' => __( 'font-awesome-alt', 'tour-booking-manager' ),
                'fab fa-font-awesome-flag' => __( 'font-awesome-flag', 'tour-booking-manager' ),
                'fab fa-fonticons' => __( 'fonticons', 'tour-booking-manager' ),
                'fab fa-fonticons-fi' => __( 'fonticons-fi', 'tour-booking-manager' ),
                'fas fa-football-ball' => __( 'football-ball', 'tour-booking-manager' ),
                'fab fa-fort-awesome' => __( 'fort-awesome', 'tour-booking-manager' ),
                'fab fa-fort-awesome-alt' => __( 'fort-awesome-alt', 'tour-booking-manager' ),
                'fab fa-forumbee' => __( 'forumbee', 'tour-booking-manager' ),
                'fas fa-forward' => __( 'forward', 'tour-booking-manager' ),
                'fab fa-foursquare' => __( 'foursquare', 'tour-booking-manager' ),
                'fab fa-free-code-camp' => __( 'free-code-camp', 'tour-booking-manager' ),
                'fab fa-freebsd' => __( 'freebsd', 'tour-booking-manager' ),
                'fas fa-frown' => __( 'frown', 'tour-booking-manager' ),
                'far fa-frown' => __( 'frown', 'tour-booking-manager' ),
                'fas fa-futbol' => __( 'futbol', 'tour-booking-manager' ),
                'far fa-futbol' => __( 'futbol', 'tour-booking-manager' ),
                'fas fa-gamepad' => __( 'gamepad', 'tour-booking-manager' ),
                'fas fa-gavel' => __( 'gavel', 'tour-booking-manager' ),
                'fas fa-gem' => __( 'gem', 'tour-booking-manager' ),
                'far fa-gem' => __( 'gem', 'tour-booking-manager' ),
                'fas fa-genderless' => __( 'genderless', 'tour-booking-manager' ),
                'fab fa-get-pocket' => __( 'get-pocket', 'tour-booking-manager' ),
                'fab fa-gg' => __( 'gg', 'tour-booking-manager' ),
                'fab fa-gg-circle' => __( 'gg-circle', 'tour-booking-manager' ),
                'fas fa-gift' => __( 'gift', 'tour-booking-manager' ),
                'fab fa-git' => __( 'git', 'tour-booking-manager' ),
                'fab fa-git-square' => __( 'git-square', 'tour-booking-manager' ),
                'fab fa-github' => __( 'github', 'tour-booking-manager' ),
                'fab fa-github-alt' => __( 'github-alt', 'tour-booking-manager' ),
                'fab fa-github-square' => __( 'github-square', 'tour-booking-manager' ),
                'fab fa-gitkraken' => __( 'gitkraken', 'tour-booking-manager' ),
                'fab fa-gitlab' => __( 'gitlab', 'tour-booking-manager' ),
                'fab fa-gitter' => __( 'gitter', 'tour-booking-manager' ),
                'fas fa-glass-martini' => __( 'glass-martini', 'tour-booking-manager' ),
                'fab fa-glide' => __( 'glide', 'tour-booking-manager' ),
                'fab fa-glide-g' => __( 'glide-g', 'tour-booking-manager' ),
                'fas fa-globe' => __( 'globe', 'tour-booking-manager' ),
                'fab fa-gofore' => __( 'gofore', 'tour-booking-manager' ),
                'fas fa-golf-ball' => __( 'golf-ball', 'tour-booking-manager' ),
                'fab fa-goodreads' => __( 'goodreads', 'tour-booking-manager' ),
                'fab fa-goodreads-g' => __( 'goodreads-g', 'tour-booking-manager' ),
                'fab fa-google' => __( 'google', 'tour-booking-manager' ),
                'fab fa-google-drive' => __( 'google-drive', 'tour-booking-manager' ),
                'fab fa-google-play' => __( 'google-play', 'tour-booking-manager' ),
                'fab fa-google-plus' => __( 'google-plus', 'tour-booking-manager' ),
                'fab fa-google-plus-g' => __( 'google-plus-g', 'tour-booking-manager' ),
                'fab fa-google-plus-square' => __( 'google-plus-square', 'tour-booking-manager' ),
                'fab fa-google-wallet' => __( 'google-wallet', 'tour-booking-manager' ),
                'fas fa-graduation-cap' => __( 'graduation-cap', 'tour-booking-manager' ),
                'fab fa-gratipay' => __( 'gratipay', 'tour-booking-manager' ),
                'fab fa-grav' => __( 'grav', 'tour-booking-manager' ),
                'fab fa-gripfire' => __( 'gripfire', 'tour-booking-manager' ),
                'fab fa-grunt' => __( 'grunt', 'tour-booking-manager' ),
                'fab fa-gulp' => __( 'gulp', 'tour-booking-manager' ),
                'fas fa-h-square' => __( 'h-square', 'tour-booking-manager' ),
                'fab fa-hacker-news' => __( 'hacker-news', 'tour-booking-manager' ),
                'fab fa-hacker-news-square' => __( 'hacker-news-square', 'tour-booking-manager' ),
                'fas fa-hand-holding' => __( 'hand-holding', 'tour-booking-manager' ),
                'fas fa-hand-holding-heart' => __( 'hand-holding-heart', 'tour-booking-manager' ),
                'fas fa-hand-holding-usd' => __( 'hand-holding-usd', 'tour-booking-manager' ),
                'fas fa-hand-lizard' => __( 'hand-lizard', 'tour-booking-manager' ),
                'far fa-hand-lizard' => __( 'hand-lizard', 'tour-booking-manager' ),
                'fas fa-hand-paper' => __( 'hand-paper', 'tour-booking-manager' ),
                'far fa-hand-paper' => __( 'hand-paper', 'tour-booking-manager' ),
                'fas fa-hand-peace' => __( 'hand-peace', 'tour-booking-manager' ),
                'far fa-hand-peace' => __( 'hand-peace', 'tour-booking-manager' ),
                'fas fa-hand-point-down' => __( 'hand-point-down', 'tour-booking-manager' ),
                'far fa-hand-point-down' => __( 'hand-point-down', 'tour-booking-manager' ),
                'fas fa-hand-point-left' => __( 'hand-point-left', 'tour-booking-manager' ),
                'far fa-hand-point-left' => __( 'hand-point-left', 'tour-booking-manager' ),
                'fas fa-hand-point-right' => __( 'hand-point-right', 'tour-booking-manager' ),
                'far fa-hand-point-right' => __( 'hand-point-right', 'tour-booking-manager' ),
                'fas fa-hand-point-up' => __( 'hand-point-up', 'tour-booking-manager' ),
                'far fa-hand-point-up' => __( 'hand-point-up', 'tour-booking-manager' ),
                'fas fa-hand-pointer' => __( 'hand-pointer', 'tour-booking-manager' ),
                'far fa-hand-pointer' => __( 'hand-pointer', 'tour-booking-manager' ),
                'fas fa-hand-rock' => __( 'hand-rock', 'tour-booking-manager' ),
                'far fa-hand-rock' => __( 'hand-rock', 'tour-booking-manager' ),
                'fas fa-hand-scissors' => __( 'hand-scissors', 'tour-booking-manager' ),
                'far fa-hand-scissors' => __( 'hand-scissors', 'tour-booking-manager' ),
                'fas fa-hand-spock' => __( 'hand-spock', 'tour-booking-manager' ),
                'far fa-hand-spock' => __( 'hand-spock', 'tour-booking-manager' ),
                'fas fa-hands' => __( 'hands', 'tour-booking-manager' ),
                'fas fa-hands-helping' => __( 'hands-helping', 'tour-booking-manager' ),
                'fas fa-handshake' => __( 'handshake', 'tour-booking-manager' ),
                'far fa-handshake' => __( 'handshake', 'tour-booking-manager' ),
                'fas fa-hashtag' => __( 'hashtag', 'tour-booking-manager' ),
                'fas fa-hdd' => __( 'hdd', 'tour-booking-manager' ),
                'far fa-hdd' => __( 'hdd', 'tour-booking-manager' ),
                'fas fa-heading' => __( 'heading', 'tour-booking-manager' ),
                'fas fa-headphones' => __( 'headphones', 'tour-booking-manager' ),
                'fas fa-heart' => __( 'heart', 'tour-booking-manager' ),
                'far fa-heart' => __( 'heart', 'tour-booking-manager' ),
                'fas fa-heartbeat' => __( 'heartbeat', 'tour-booking-manager' ),
                'fab fa-hips' => __( 'hips', 'tour-booking-manager' ),
                'fab fa-hire-a-helper' => __( 'hire-a-helper', 'tour-booking-manager' ),
                'fas fa-history' => __( 'history', 'tour-booking-manager' ),
                'fas fa-hockey-puck' => __( 'hockey-puck', 'tour-booking-manager' ),
                'fas fa-home' => __( 'home', 'tour-booking-manager' ),
                'fab fa-hooli' => __( 'hooli', 'tour-booking-manager' ),
                'fas fa-hospital' => __( 'hospital', 'tour-booking-manager' ),
                'far fa-hospital' => __( 'hospital', 'tour-booking-manager' ),
                'fas fa-hospital-alt' => __( 'hospital-alt', 'tour-booking-manager' ),
                'fas fa-hospital-symbol' => __( 'hospital-symbol', 'tour-booking-manager' ),
                'fab fa-hotjar' => __( 'hotjar', 'tour-booking-manager' ),
                'fas fa-hourglass' => __( 'hourglass', 'tour-booking-manager' ),
                'far fa-hourglass' => __( 'hourglass', 'tour-booking-manager' ),
                'fas fa-hourglass-end' => __( 'hourglass-end', 'tour-booking-manager' ),
                'fas fa-hourglass-half' => __( 'hourglass-half', 'tour-booking-manager' ),
                'fas fa-hourglass-start' => __( 'hourglass-start', 'tour-booking-manager' ),
                'fab fa-houzz' => __( 'houzz', 'tour-booking-manager' ),
                'fab fa-html5' => __( 'html5', 'tour-booking-manager' ),
                'fab fa-hubspot' => __( 'hubspot', 'tour-booking-manager' ),
                'fas fa-i-cursor' => __( 'i-cursor', 'tour-booking-manager' ),
                'fas fa-id-badge' => __( 'id-badge', 'tour-booking-manager' ),
                'far fa-id-badge' => __( 'id-badge', 'tour-booking-manager' ),
                'fas fa-id-card' => __( 'id-card', 'tour-booking-manager' ),
                'far fa-id-card' => __( 'id-card', 'tour-booking-manager' ),
                'fas fa-id-card-alt' => __( 'id-card-alt', 'tour-booking-manager' ),
                'fas fa-image' => __( 'image', 'tour-booking-manager' ),
                'far fa-image' => __( 'image', 'tour-booking-manager' ),
                'fas fa-images' => __( 'images', 'tour-booking-manager' ),
                'far fa-images' => __( 'images', 'tour-booking-manager' ),
                'fab fa-imdb' => __( 'imdb', 'tour-booking-manager' ),
                'fas fa-inbox' => __( 'inbox', 'tour-booking-manager' ),
                'fas fa-indent' => __( 'indent', 'tour-booking-manager' ),
                'fas fa-industry' => __( 'industry', 'tour-booking-manager' ),
                'fas fa-info' => __( 'info', 'tour-booking-manager' ),
                'fas fa-info-circle' => __( 'info-circle', 'tour-booking-manager' ),
                'fab fa-instagram' => __( 'instagram', 'tour-booking-manager' ),
                'fab fa-internet-explorer' => __( 'internet-explorer', 'tour-booking-manager' ),
                'fab fa-ioxhost' => __( 'ioxhost', 'tour-booking-manager' ),
                'fas fa-italic' => __( 'italic', 'tour-booking-manager' ),
                'fab fa-itunes' => __( 'itunes', 'tour-booking-manager' ),
                'fab fa-itunes-note' => __( 'itunes-note', 'tour-booking-manager' ),
                'fab fa-java' => __( 'java', 'tour-booking-manager' ),
                'fab fa-jenkins' => __( 'jenkins', 'tour-booking-manager' ),
                'fab fa-joget' => __( 'joget', 'tour-booking-manager' ),
                'fab fa-joomla' => __( 'joomla', 'tour-booking-manager' ),
                'fab fa-js' => __( 'js', 'tour-booking-manager' ),
                'fab fa-js-square' => __( 'js-square', 'tour-booking-manager' ),
                'fab fa-jsfiddle' => __( 'jsfiddle', 'tour-booking-manager' ),
                'fas fa-key' => __( 'key', 'tour-booking-manager' ),
                'fas fa-keyboard' => __( 'keyboard', 'tour-booking-manager' ),
                'far fa-keyboard' => __( 'keyboard', 'tour-booking-manager' ),
                'fab fa-keycdn' => __( 'keycdn', 'tour-booking-manager' ),
                'fab fa-kickstarter' => __( 'kickstarter', 'tour-booking-manager' ),
                'fab fa-kickstarter-k' => __( 'kickstarter-k', 'tour-booking-manager' ),
                'fab fa-korvue' => __( 'korvue', 'tour-booking-manager' ),
                'fas fa-language' => __( 'language', 'tour-booking-manager' ),
                'fas fa-laptop' => __( 'laptop', 'tour-booking-manager' ),
                'fab fa-laravel' => __( 'laravel', 'tour-booking-manager' ),
                'fab fa-lastfm' => __( 'lastfm', 'tour-booking-manager' ),
                'fab fa-lastfm-square' => __( 'lastfm-square', 'tour-booking-manager' ),
                'fas fa-leaf' => __( 'leaf', 'tour-booking-manager' ),
                'fab fa-leanpub' => __( 'leanpub', 'tour-booking-manager' ),
                'fas fa-lemon' => __( 'lemon', 'tour-booking-manager' ),
                'far fa-lemon' => __( 'lemon', 'tour-booking-manager' ),
                'fab fa-less' => __( 'less', 'tour-booking-manager' ),
                'fas fa-level-down-alt' => __( 'level-down-alt', 'tour-booking-manager' ),
                'fas fa-level-up-alt' => __( 'level-up-alt', 'tour-booking-manager' ),
                'fas fa-life-ring' => __( 'life-ring', 'tour-booking-manager' ),
                'far fa-life-ring' => __( 'life-ring', 'tour-booking-manager' ),
                'fas fa-lightbulb' => __( 'lightbulb', 'tour-booking-manager' ),
                'far fa-lightbulb' => __( 'lightbulb', 'tour-booking-manager' ),
                'fab fa-line' => __( 'line', 'tour-booking-manager' ),
                'fas fa-link' => __( 'link', 'tour-booking-manager' ),
                'fab fa-linkedin' => __( 'linkedin', 'tour-booking-manager' ),
                'fab fa-linkedin-in' => __( 'linkedin-in', 'tour-booking-manager' ),
                'fab fa-linode' => __( 'linode', 'tour-booking-manager' ),
                'fab fa-linux' => __( 'linux', 'tour-booking-manager' ),
                'fas fa-lira-sign' => __( 'lira-sign', 'tour-booking-manager' ),
                'fas fa-list' => __( 'list', 'tour-booking-manager' ),
                'fas fa-list-alt' => __( 'list-alt', 'tour-booking-manager' ),
                'far fa-list-alt' => __( 'list-alt', 'tour-booking-manager' ),
                'fas fa-list-ol' => __( 'list-ol', 'tour-booking-manager' ),
                'fas fa-list-ul' => __( 'list-ul', 'tour-booking-manager' ),
                'fas fa-location-arrow' => __( 'location-arrow', 'tour-booking-manager' ),
                'fas fa-lock' => __( 'lock', 'tour-booking-manager' ),
                'fas fa-lock-open' => __( 'lock-open', 'tour-booking-manager' ),
                'fas fa-long-arrow-alt-down' => __( 'long-arrow-alt-down', 'tour-booking-manager' ),
                'fas fa-long-arrow-alt-left' => __( 'long-arrow-alt-left', 'tour-booking-manager' ),
                'fas fa-long-arrow-alt-right' => __( 'long-arrow-alt-right', 'tour-booking-manager' ),
                'fas fa-long-arrow-alt-up' => __( 'long-arrow-alt-up', 'tour-booking-manager' ),
                'fas fa-low-vision' => __( 'low-vision', 'tour-booking-manager' ),
                'fab fa-lyft' => __( 'lyft', 'tour-booking-manager' ),
                'fab fa-magento' => __( 'magento', 'tour-booking-manager' ),
                'fas fa-magic' => __( 'magic', 'tour-booking-manager' ),
                'fas fa-magnet' => __( 'magnet', 'tour-booking-manager' ),
                'fas fa-male' => __( 'male', 'tour-booking-manager' ),
                'fas fa-map' => __( 'map', 'tour-booking-manager' ),
                'far fa-map' => __( 'map', 'tour-booking-manager' ),
                'fas fa-map-marker' => __( 'map-marker', 'tour-booking-manager' ),
                'fas fa-map-marker-alt' => __( 'map-marker-alt', 'tour-booking-manager' ),
                'fas fa-map-pin' => __( 'map-pin', 'tour-booking-manager' ),
                'fas fa-map-signs' => __( 'map-signs', 'tour-booking-manager' ),
                'fas fa-mars' => __( 'mars', 'tour-booking-manager' ),
                'fas fa-mars-double' => __( 'mars-double', 'tour-booking-manager' ),
                'fas fa-mars-stroke' => __( 'mars-stroke', 'tour-booking-manager' ),
                'fas fa-mars-stroke-h' => __( 'mars-stroke-h', 'tour-booking-manager' ),
                'fas fa-mars-stroke-v' => __( 'mars-stroke-v', 'tour-booking-manager' ),
                'fab fa-maxcdn' => __( 'maxcdn', 'tour-booking-manager' ),
                'fab fa-medapps' => __( 'medapps', 'tour-booking-manager' ),
                'fab fa-medium' => __( 'medium', 'tour-booking-manager' ),
                'fab fa-medium-m' => __( 'medium-m', 'tour-booking-manager' ),
                'fas fa-medkit' => __( 'medkit', 'tour-booking-manager' ),
                'fab fa-medrt' => __( 'medrt', 'tour-booking-manager' ),
                'fab fa-meetup' => __( 'meetup', 'tour-booking-manager' ),
                'fas fa-meh' => __( 'meh', 'tour-booking-manager' ),
                'far fa-meh' => __( 'meh', 'tour-booking-manager' ),
                'fas fa-mercury' => __( 'mercury', 'tour-booking-manager' ),
                'fas fa-microchip' => __( 'microchip', 'tour-booking-manager' ),
                'fas fa-microphone' => __( 'microphone', 'tour-booking-manager' ),
                'fas fa-microphone-slash' => __( 'microphone-slash', 'tour-booking-manager' ),
                'fab fa-microsoft' => __( 'microsoft', 'tour-booking-manager' ),
                'fas fa-minus' => __( 'minus', 'tour-booking-manager' ),
                'fas fa-minus-circle' => __( 'minus-circle', 'tour-booking-manager' ),
                'fas fa-minus-square' => __( 'minus-square', 'tour-booking-manager' ),
                'far fa-minus-square' => __( 'minus-square', 'tour-booking-manager' ),
                'fab fa-mix' => __( 'mix', 'tour-booking-manager' ),
                'fab fa-mixcloud' => __( 'mixcloud', 'tour-booking-manager' ),
                'fab fa-mizuni' => __( 'mizuni', 'tour-booking-manager' ),
                'fas fa-mobile' => __( 'mobile', 'tour-booking-manager' ),
                'fas fa-mobile-alt' => __( 'mobile-alt', 'tour-booking-manager' ),
                'fab fa-modx' => __( 'modx', 'tour-booking-manager' ),
                'fab fa-monero' => __( 'monero', 'tour-booking-manager' ),
                'fas fa-money-bill-alt' => __( 'money-bill-alt', 'tour-booking-manager' ),
                'far fa-money-bill-alt' => __( 'money-bill-alt', 'tour-booking-manager' ),
                'fas fa-moon' => __( 'moon', 'tour-booking-manager' ),
                'far fa-moon' => __( 'moon', 'tour-booking-manager' ),
                'fas fa-motorcycle' => __( 'motorcycle', 'tour-booking-manager' ),
                'fas fa-mouse-pointer' => __( 'mouse-pointer', 'tour-booking-manager' ),
                'fas fa-music' => __( 'music', 'tour-booking-manager' ),
                'fab fa-napster' => __( 'napster', 'tour-booking-manager' ),
                'fas fa-neuter' => __( 'neuter', 'tour-booking-manager' ),
                'fas fa-newspaper' => __( 'newspaper', 'tour-booking-manager' ),
                'far fa-newspaper' => __( 'newspaper', 'tour-booking-manager' ),
                'fab fa-nintendo-switch' => __( 'nintendo-switch', 'tour-booking-manager' ),
                'fab fa-node' => __( 'node', 'tour-booking-manager' ),
                'fab fa-node-js' => __( 'node-js', 'tour-booking-manager' ),
                'fas fa-notes-medical' => __( 'notes-medical', 'tour-booking-manager' ),
                'fab fa-npm' => __( 'npm', 'tour-booking-manager' ),
                'fab fa-ns8' => __( 'ns8', 'tour-booking-manager' ),
                'fab fa-nutritionix' => __( 'nutritionix', 'tour-booking-manager' ),
                'fas fa-object-group' => __( 'object-group', 'tour-booking-manager' ),
                'far fa-object-group' => __( 'object-group', 'tour-booking-manager' ),
                'fas fa-object-ungroup' => __( 'object-ungroup', 'tour-booking-manager' ),
                'far fa-object-ungroup' => __( 'object-ungroup', 'tour-booking-manager' ),
                'fab fa-odnoklassniki' => __( 'odnoklassniki', 'tour-booking-manager' ),
                'fab fa-odnoklassniki-square' => __( 'odnoklassniki-square', 'tour-booking-manager' ),
                'fab fa-opencart' => __( 'opencart', 'tour-booking-manager' ),
                'fab fa-openid' => __( 'openid', 'tour-booking-manager' ),
                'fab fa-opera' => __( 'opera', 'tour-booking-manager' ),
                'fab fa-optin-monster' => __( 'optin-monster', 'tour-booking-manager' ),
                'fab fa-osi' => __( 'osi', 'tour-booking-manager' ),
                'fas fa-outdent' => __( 'outdent', 'tour-booking-manager' ),
                'fab fa-page4' => __( 'page4', 'tour-booking-manager' ),
                'fab fa-pagelines' => __( 'pagelines', 'tour-booking-manager' ),
                'fas fa-paint-brush' => __( 'paint-brush', 'tour-booking-manager' ),
                'fab fa-palfed' => __( 'palfed', 'tour-booking-manager' ),
                'fas fa-pallet' => __( 'pallet', 'tour-booking-manager' ),
                'fas fa-paper-plane' => __( 'paper-plane', 'tour-booking-manager' ),
                'far fa-paper-plane' => __( 'paper-plane', 'tour-booking-manager' ),
                'fas fa-paperclip' => __( 'paperclip', 'tour-booking-manager' ),
                'fas fa-parachute-box' => __( 'parachute-box', 'tour-booking-manager' ),
                'fas fa-paragraph' => __( 'paragraph', 'tour-booking-manager' ),
                'fas fa-paste' => __( 'paste', 'tour-booking-manager' ),
                'fab fa-patreon' => __( 'patreon', 'tour-booking-manager' ),
                'fas fa-pause' => __( 'pause', 'tour-booking-manager' ),
                'fas fa-pause-circle' => __( 'pause-circle', 'tour-booking-manager' ),
                'far fa-pause-circle' => __( 'pause-circle', 'tour-booking-manager' ),
                'fas fa-paw' => __( 'paw', 'tour-booking-manager' ),
                'fab fa-paypal' => __( 'paypal', 'tour-booking-manager' ),
                'fas fa-pen-square' => __( 'pen-square', 'tour-booking-manager' ),
                'fas fa-pencil-alt' => __( 'pencil-alt', 'tour-booking-manager' ),
                'fas fa-people-carry' => __( 'people-carry', 'tour-booking-manager' ),
                'fas fa-percent' => __( 'percent', 'tour-booking-manager' ),
                'fab fa-periscope' => __( 'periscope', 'tour-booking-manager' ),
                'fab fa-phabricator' => __( 'phabricator', 'tour-booking-manager' ),
                'fab fa-phoenix-framework' => __( 'phoenix-framework', 'tour-booking-manager' ),
                'fas fa-phone' => __( 'phone', 'tour-booking-manager' ),
                'fas fa-phone-slash' => __( 'phone-slash', 'tour-booking-manager' ),
                'fas fa-phone-square' => __( 'phone-square', 'tour-booking-manager' ),
                'fas fa-phone-volume' => __( 'phone-volume', 'tour-booking-manager' ),
                'fab fa-php' => __( 'php', 'tour-booking-manager' ),
                'fab fa-pied-piper' => __( 'pied-piper', 'tour-booking-manager' ),
                'fab fa-pied-piper-alt' => __( 'pied-piper-alt', 'tour-booking-manager' ),
                'fab fa-pied-piper-hat' => __( 'pied-piper-hat', 'tour-booking-manager' ),
                'fab fa-pied-piper-pp' => __( 'pied-piper-pp', 'tour-booking-manager' ),
                'fas fa-piggy-bank' => __( 'piggy-bank', 'tour-booking-manager' ),
                'fas fa-pills' => __( 'pills', 'tour-booking-manager' ),
                'fab fa-pinterest' => __( 'pinterest', 'tour-booking-manager' ),
                'fab fa-pinterest-p' => __( 'pinterest-p', 'tour-booking-manager' ),
                'fab fa-pinterest-square' => __( 'pinterest-square', 'tour-booking-manager' ),
                'fas fa-plane' => __( 'plane', 'tour-booking-manager' ),
                'fas fa-play' => __( 'play', 'tour-booking-manager' ),
                'fas fa-play-circle' => __( 'play-circle', 'tour-booking-manager' ),
                'far fa-play-circle' => __( 'play-circle', 'tour-booking-manager' ),
                'fab fa-playstation' => __( 'playstation', 'tour-booking-manager' ),
                'fas fa-plug' => __( 'plug', 'tour-booking-manager' ),
                'fas fa-plus' => __( 'plus', 'tour-booking-manager' ),
                'fas fa-plus-circle' => __( 'plus-circle', 'tour-booking-manager' ),
                'fas fa-plus-square' => __( 'plus-square', 'tour-booking-manager' ),
                'far fa-plus-square' => __( 'plus-square', 'tour-booking-manager' ),
                'fas fa-podcast' => __( 'podcast', 'tour-booking-manager' ),
                'fas fa-poo' => __( 'poo', 'tour-booking-manager' ),
                'fas fa-pound-sign' => __( 'pound-sign', 'tour-booking-manager' ),
                'fas fa-power-off' => __( 'power-off', 'tour-booking-manager' ),
                'fas fa-prescription-bottle' => __( 'prescription-bottle', 'tour-booking-manager' ),
                'fas fa-prescription-bottle-alt' => __( 'prescription-bottle-alt', 'tour-booking-manager' ),
                'fas fa-print' => __( 'print', 'tour-booking-manager' ),
                'fas fa-procedures' => __( 'procedures', 'tour-booking-manager' ),
                'fab fa-product-hunt' => __( 'product-hunt', 'tour-booking-manager' ),
                'fab fa-pushed' => __( 'pushed', 'tour-booking-manager' ),
                'fas fa-puzzle-piece' => __( 'puzzle-piece', 'tour-booking-manager' ),
                'fab fa-python' => __( 'python', 'tour-booking-manager' ),
                'fab fa-qq' => __( 'qq', 'tour-booking-manager' ),
                'fas fa-qrcode' => __( 'qrcode', 'tour-booking-manager' ),
                'fas fa-question' => __( 'question', 'tour-booking-manager' ),
                'fas fa-question-circle' => __( 'question-circle', 'tour-booking-manager' ),
                'far fa-question-circle' => __( 'question-circle', 'tour-booking-manager' ),
                'fas fa-quidditch' => __( 'quidditch', 'tour-booking-manager' ),
                'fab fa-quinscape' => __( 'quinscape', 'tour-booking-manager' ),
                'fab fa-quora' => __( 'quora', 'tour-booking-manager' ),
                'fas fa-quote-left' => __( 'quote-left', 'tour-booking-manager' ),
                'fas fa-quote-right' => __( 'quote-right', 'tour-booking-manager' ),
                'fas fa-random' => __( 'random', 'tour-booking-manager' ),
                'fab fa-ravelry' => __( 'ravelry', 'tour-booking-manager' ),
                'fab fa-react' => __( 'react', 'tour-booking-manager' ),
                'fab fa-readme' => __( 'readme', 'tour-booking-manager' ),
                'fab fa-rebel' => __( 'rebel', 'tour-booking-manager' ),
                'fas fa-recycle' => __( 'recycle', 'tour-booking-manager' ),
                'fab fa-red-river' => __( 'red-river', 'tour-booking-manager' ),
                'fab fa-reddit' => __( 'reddit', 'tour-booking-manager' ),
                'fab fa-reddit-alien' => __( 'reddit-alien', 'tour-booking-manager' ),
                'fab fa-reddit-square' => __( 'reddit-square', 'tour-booking-manager' ),
                'fas fa-redo' => __( 'redo', 'tour-booking-manager' ),
                'fas fa-redo-alt' => __( 'redo-alt', 'tour-booking-manager' ),
                'fas fa-registered' => __( 'registered', 'tour-booking-manager' ),
                'far fa-registered' => __( 'registered', 'tour-booking-manager' ),
                'fab fa-rendact' => __( 'rendact', 'tour-booking-manager' ),
                'fab fa-renren' => __( 'renren', 'tour-booking-manager' ),
                'fas fa-reply' => __( 'reply', 'tour-booking-manager' ),
                'fas fa-reply-all' => __( 'reply-all', 'tour-booking-manager' ),
                'fab fa-replyd' => __( 'replyd', 'tour-booking-manager' ),
                'fab fa-resolving' => __( 'resolving', 'tour-booking-manager' ),
                'fas fa-retweet' => __( 'retweet', 'tour-booking-manager' ),
                'fas fa-ribbon' => __( 'ribbon', 'tour-booking-manager' ),
                'fas fa-road' => __( 'road', 'tour-booking-manager' ),
                'fas fa-rocket' => __( 'rocket', 'tour-booking-manager' ),
                'fab fa-rocketchat' => __( 'rocketchat', 'tour-booking-manager' ),
                'fab fa-rockrms' => __( 'rockrms', 'tour-booking-manager' ),
                'fas fa-rss' => __( 'rss', 'tour-booking-manager' ),
                'fas fa-rss-square' => __( 'rss-square', 'tour-booking-manager' ),
                'fas fa-ruble-sign' => __( 'ruble-sign', 'tour-booking-manager' ),
                'fas fa-rupee-sign' => __( 'rupee-sign', 'tour-booking-manager' ),
                'fab fa-safari' => __( 'safari', 'tour-booking-manager' ),
                'fab fa-sass' => __( 'sass', 'tour-booking-manager' ),
                'fas fa-save' => __( 'save', 'tour-booking-manager' ),
                'far fa-save' => __( 'save', 'tour-booking-manager' ),
                'fab fa-schlix' => __( 'schlix', 'tour-booking-manager' ),
                'fab fa-scribd' => __( 'scribd', 'tour-booking-manager' ),
                'fas fa-search' => __( 'search', 'tour-booking-manager' ),
                'fas fa-search-minus' => __( 'search-minus', 'tour-booking-manager' ),
                'fas fa-search-plus' => __( 'search-plus', 'tour-booking-manager' ),
                'fab fa-searchengin' => __( 'searchengin', 'tour-booking-manager' ),
                'fas fa-seedling' => __( 'seedling', 'tour-booking-manager' ),
                'fab fa-sellcast' => __( 'sellcast', 'tour-booking-manager' ),
                'fab fa-sellsy' => __( 'sellsy', 'tour-booking-manager' ),
                'fas fa-server' => __( 'server', 'tour-booking-manager' ),
                'fab fa-servicestack' => __( 'servicestack', 'tour-booking-manager' ),
                'fas fa-share' => __( 'share', 'tour-booking-manager' ),
                'fas fa-share-alt' => __( 'share-alt', 'tour-booking-manager' ),
                'fas fa-share-alt-square' => __( 'share-alt-square', 'tour-booking-manager' ),
                'fas fa-share-square' => __( 'share-square', 'tour-booking-manager' ),
                'far fa-share-square' => __( 'share-square', 'tour-booking-manager' ),
                'fas fa-shekel-sign' => __( 'shekel-sign', 'tour-booking-manager' ),
                'fas fa-shield-alt' => __( 'shield-alt', 'tour-booking-manager' ),
                'fas fa-ship' => __( 'ship', 'tour-booking-manager' ),
                'fas fa-shipping-fast' => __( 'shipping-fast', 'tour-booking-manager' ),
                'fab fa-shirtsinbulk' => __( 'shirtsinbulk', 'tour-booking-manager' ),
                'fas fa-shopping-bag' => __( 'shopping-bag', 'tour-booking-manager' ),
                'fas fa-shopping-basket' => __( 'shopping-basket', 'tour-booking-manager' ),
                'fas fa-shopping-cart' => __( 'shopping-cart', 'tour-booking-manager' ),
                'fas fa-shower' => __( 'shower', 'tour-booking-manager' ),
                'fas fa-sign' => __( 'sign', 'tour-booking-manager' ),
                'fas fa-sign-in-alt' => __( 'sign-in-alt', 'tour-booking-manager' ),
                'fas fa-sign-language' => __( 'sign-language', 'tour-booking-manager' ),
                'fas fa-sign-out-alt' => __( 'sign-out-alt', 'tour-booking-manager' ),
                'fas fa-signal' => __( 'signal', 'tour-booking-manager' ),
                'fab fa-simplybuilt' => __( 'simplybuilt', 'tour-booking-manager' ),
                'fab fa-sistrix' => __( 'sistrix', 'tour-booking-manager' ),
                'fas fa-sitemap' => __( 'sitemap', 'tour-booking-manager' ),
                'fab fa-skyatlas' => __( 'skyatlas', 'tour-booking-manager' ),
                'fab fa-skype' => __( 'skype', 'tour-booking-manager' ),
                'fab fa-slack' => __( 'slack', 'tour-booking-manager' ),
                'fab fa-slack-hash' => __( 'slack-hash', 'tour-booking-manager' ),
                'fas fa-sliders-h' => __( 'sliders-h', 'tour-booking-manager' ),
                'fab fa-slideshare' => __( 'slideshare', 'tour-booking-manager' ),
                'fas fa-smile' => __( 'smile', 'tour-booking-manager' ),
                'far fa-smile' => __( 'smile', 'tour-booking-manager' ),
                'fas fa-smoking' => __( 'smoking', 'tour-booking-manager' ),
                'fab fa-snapchat' => __( 'snapchat', 'tour-booking-manager' ),
                'fab fa-snapchat-ghost' => __( 'snapchat-ghost', 'tour-booking-manager' ),
                'fab fa-snapchat-square' => __( 'snapchat-square', 'tour-booking-manager' ),
                'fas fa-snowflake' => __( 'snowflake', 'tour-booking-manager' ),
                'far fa-snowflake' => __( 'snowflake', 'tour-booking-manager' ),
                'fas fa-sort' => __( 'sort', 'tour-booking-manager' ),
                'fas fa-sort-alpha-down' => __( 'sort-alpha-down', 'tour-booking-manager' ),
                'fas fa-sort-alpha-up' => __( 'sort-alpha-up', 'tour-booking-manager' ),
                'fas fa-sort-amount-down' => __( 'sort-amount-down', 'tour-booking-manager' ),
                'fas fa-sort-amount-up' => __( 'sort-amount-up', 'tour-booking-manager' ),
                'fas fa-sort-down' => __( 'sort-down', 'tour-booking-manager' ),
                'fas fa-sort-numeric-down' => __( 'sort-numeric-down', 'tour-booking-manager' ),
                'fas fa-sort-numeric-up' => __( 'sort-numeric-up', 'tour-booking-manager' ),
                'fas fa-sort-up' => __( 'sort-up', 'tour-booking-manager' ),
                'fab fa-soundcloud' => __( 'soundcloud', 'tour-booking-manager' ),
                'fas fa-space-shuttle' => __( 'space-shuttle', 'tour-booking-manager' ),
                'fab fa-speakap' => __( 'speakap', 'tour-booking-manager' ),
                'fas fa-spinner' => __( 'spinner', 'tour-booking-manager' ),
                'fab fa-spotify' => __( 'spotify', 'tour-booking-manager' ),
                'fas fa-square' => __( 'square', 'tour-booking-manager' ),
                'far fa-square' => __( 'square', 'tour-booking-manager' ),
                'fas fa-square-full' => __( 'square-full', 'tour-booking-manager' ),
                'fab fa-stack-exchange' => __( 'stack-exchange', 'tour-booking-manager' ),
                'fab fa-stack-overflow' => __( 'stack-overflow', 'tour-booking-manager' ),
                'fas fa-star' => __( 'star', 'tour-booking-manager' ),
                'far fa-star' => __( 'star', 'tour-booking-manager' ),
                'fas fa-star-half' => __( 'star-half', 'tour-booking-manager' ),
                'far fa-star-half' => __( 'star-half', 'tour-booking-manager' ),
                'fab fa-staylinked' => __( 'staylinked', 'tour-booking-manager' ),
                'fab fa-steam' => __( 'steam', 'tour-booking-manager' ),
                'fab fa-steam-square' => __( 'steam-square', 'tour-booking-manager' ),
                'fab fa-steam-symbol' => __( 'steam-symbol', 'tour-booking-manager' ),
                'fas fa-step-backward' => __( 'step-backward', 'tour-booking-manager' ),
                'fas fa-step-forward' => __( 'step-forward', 'tour-booking-manager' ),
                'fas fa-stethoscope' => __( 'stethoscope', 'tour-booking-manager' ),
                'fab fa-sticker-mule' => __( 'sticker-mule', 'tour-booking-manager' ),
                'fas fa-sticky-note' => __( 'sticky-note', 'tour-booking-manager' ),
                'far fa-sticky-note' => __( 'sticky-note', 'tour-booking-manager' ),
                'fas fa-stop' => __( 'stop', 'tour-booking-manager' ),
                'fas fa-stop-circle' => __( 'stop-circle', 'tour-booking-manager' ),
                'far fa-stop-circle' => __( 'stop-circle', 'tour-booking-manager' ),
                'fas fa-stopwatch' => __( 'stopwatch', 'tour-booking-manager' ),
                'fab fa-strava' => __( 'strava', 'tour-booking-manager' ),
                'fas fa-street-view' => __( 'street-view', 'tour-booking-manager' ),
                'fas fa-strikethrough' => __( 'strikethrough', 'tour-booking-manager' ),
                'fab fa-stripe' => __( 'stripe', 'tour-booking-manager' ),
                'fab fa-stripe-s' => __( 'stripe-s', 'tour-booking-manager' ),
                'fab fa-studiovinari' => __( 'studiovinari', 'tour-booking-manager' ),
                'fab fa-stumbleupon' => __( 'stumbleupon', 'tour-booking-manager' ),
                'fab fa-stumbleupon-circle' => __( 'stumbleupon-circle', 'tour-booking-manager' ),
                'fas fa-subscript' => __( 'subscript', 'tour-booking-manager' ),
                'fas fa-subway' => __( 'subway', 'tour-booking-manager' ),
                'fas fa-suitcase' => __( 'suitcase', 'tour-booking-manager' ),
                'fas fa-sun' => __( 'sun', 'tour-booking-manager' ),
                'far fa-sun' => __( 'sun', 'tour-booking-manager' ),
                'fab fa-superpowers' => __( 'superpowers', 'tour-booking-manager' ),
                'fas fa-superscript' => __( 'superscript', 'tour-booking-manager' ),
                'fab fa-supple' => __( 'supple', 'tour-booking-manager' ),
                'fas fa-sync' => __( 'sync', 'tour-booking-manager' ),
                'fas fa-sync-alt' => __( 'sync-alt', 'tour-booking-manager' ),
                'fas fa-syringe' => __( 'syringe', 'tour-booking-manager' ),
                'fas fa-table' => __( 'table', 'tour-booking-manager' ),
                'fas fa-table-tennis' => __( 'table-tennis', 'tour-booking-manager' ),
                'fas fa-tablet' => __( 'tablet', 'tour-booking-manager' ),
                'fas fa-tablet-alt' => __( 'tablet-alt', 'tour-booking-manager' ),
                'fas fa-tablets' => __( 'tablets', 'tour-booking-manager' ),
                'fas fa-tachometer-alt' => __( 'tachometer-alt', 'tour-booking-manager' ),
                'fas fa-tag' => __( 'tag', 'tour-booking-manager' ),
                'fas fa-tags' => __( 'tags', 'tour-booking-manager' ),
                'fas fa-tape' => __( 'tape', 'tour-booking-manager' ),
                'fas fa-tasks' => __( 'tasks', 'tour-booking-manager' ),
                'fas fa-taxi' => __( 'taxi', 'tour-booking-manager' ),
                'fab fa-telegram' => __( 'telegram', 'tour-booking-manager' ),
                'fab fa-telegram-plane' => __( 'telegram-plane', 'tour-booking-manager' ),
                'fab fa-tencent-weibo' => __( 'tencent-weibo', 'tour-booking-manager' ),
                'fas fa-terminal' => __( 'terminal', 'tour-booking-manager' ),
                'fas fa-text-height' => __( 'text-height', 'tour-booking-manager' ),
                'fas fa-text-width' => __( 'text-width', 'tour-booking-manager' ),
                'fas fa-th' => __( 'th', 'tour-booking-manager' ),
                'fas fa-th-large' => __( 'th-large', 'tour-booking-manager' ),
                'fas fa-th-list' => __( 'th-list', 'tour-booking-manager' ),
                'fab fa-themeisle' => __( 'themeisle', 'tour-booking-manager' ),
                'fas fa-thermometer' => __( 'thermometer', 'tour-booking-manager' ),
                'fas fa-thermometer-empty' => __( 'thermometer-empty', 'tour-booking-manager' ),
                'fas fa-thermometer-full' => __( 'thermometer-full', 'tour-booking-manager' ),
                'fas fa-thermometer-half' => __( 'thermometer-half', 'tour-booking-manager' ),
                'fas fa-thermometer-quarter' => __( 'thermometer-quarter', 'tour-booking-manager' ),
                'fas fa-thermometer-three-quarters' => __( 'thermometer-three-quarters', 'tour-booking-manager' ),
                'fas fa-thumbs-down' => __( 'thumbs-down', 'tour-booking-manager' ),
                'far fa-thumbs-down' => __( 'thumbs-down', 'tour-booking-manager' ),
                'fas fa-thumbs-up' => __( 'thumbs-up', 'tour-booking-manager' ),
                'far fa-thumbs-up' => __( 'thumbs-up', 'tour-booking-manager' ),
                'fas fa-thumbtack' => __( 'thumbtack', 'tour-booking-manager' ),
                'fas fa-ticket-alt' => __( 'ticket-alt', 'tour-booking-manager' ),
                'fas fa-times' => __( 'times', 'tour-booking-manager' ),
                'fas fa-times-circle' => __( 'times-circle', 'tour-booking-manager' ),
                'far fa-times-circle' => __( 'times-circle', 'tour-booking-manager' ),
                'fas fa-tint' => __( 'tint', 'tour-booking-manager' ),
                'fas fa-toggle-off' => __( 'toggle-off', 'tour-booking-manager' ),
                'fas fa-toggle-on' => __( 'toggle-on', 'tour-booking-manager' ),
                'fas fa-trademark' => __( 'trademark', 'tour-booking-manager' ),
                'fas fa-train' => __( 'train', 'tour-booking-manager' ),
                'fas fa-transgender' => __( 'transgender', 'tour-booking-manager' ),
                'fas fa-transgender-alt' => __( 'transgender-alt', 'tour-booking-manager' ),
                'fas fa-trash' => __( 'trash', 'tour-booking-manager' ),
                'fas fa-trash-alt' => __( 'trash-alt', 'tour-booking-manager' ),
                'far fa-trash-alt' => __( 'trash-alt', 'tour-booking-manager' ),
                'fas fa-tree' => __( 'tree', 'tour-booking-manager' ),
                'fab fa-trello' => __( 'trello', 'tour-booking-manager' ),
                'fab fa-tripadvisor' => __( 'tripadvisor', 'tour-booking-manager' ),
                'fas fa-trophy' => __( 'trophy', 'tour-booking-manager' ),
                'fas fa-truck' => __( 'truck', 'tour-booking-manager' ),
                'fas fa-truck-loading' => __( 'truck-loading', 'tour-booking-manager' ),
                'fas fa-truck-moving' => __( 'truck-moving', 'tour-booking-manager' ),
                'fas fa-tty' => __( 'tty', 'tour-booking-manager' ),
                'fab fa-tumblr' => __( 'tumblr', 'tour-booking-manager' ),
                'fab fa-tumblr-square' => __( 'tumblr-square', 'tour-booking-manager' ),
                'fas fa-tv' => __( 'tv', 'tour-booking-manager' ),
                'fab fa-twitch' => __( 'twitch', 'tour-booking-manager' ),
                'fab fa-twitter' => __( 'twitter', 'tour-booking-manager' ),
                'fab fa-twitter-square' => __( 'twitter-square', 'tour-booking-manager' ),
                'fab fa-typo3' => __( 'typo3', 'tour-booking-manager' ),
                'fab fa-uber' => __( 'uber', 'tour-booking-manager' ),
                'fab fa-uikit' => __( 'uikit', 'tour-booking-manager' ),
                'fas fa-umbrella' => __( 'umbrella', 'tour-booking-manager' ),
                'fas fa-underline' => __( 'underline', 'tour-booking-manager' ),
                'fas fa-undo' => __( 'undo', 'tour-booking-manager' ),
                'fas fa-undo-alt' => __( 'undo-alt', 'tour-booking-manager' ),
                'fab fa-uniregistry' => __( 'uniregistry', 'tour-booking-manager' ),
                'fas fa-universal-access' => __( 'universal-access', 'tour-booking-manager' ),
                'fas fa-university' => __( 'university', 'tour-booking-manager' ),
                'fas fa-unlink' => __( 'unlink', 'tour-booking-manager' ),
                'fas fa-unlock' => __( 'unlock', 'tour-booking-manager' ),
                'fas fa-unlock-alt' => __( 'unlock-alt', 'tour-booking-manager' ),
                'fab fa-untappd' => __( 'untappd', 'tour-booking-manager' ),
                'fas fa-upload' => __( 'upload', 'tour-booking-manager' ),
                'fab fa-usb' => __( 'usb', 'tour-booking-manager' ),
                'fas fa-user' => __( 'user', 'tour-booking-manager' ),
                'far fa-user' => __( 'user', 'tour-booking-manager' ),
                'fas fa-user-circle' => __( 'user-circle', 'tour-booking-manager' ),
                'far fa-user-circle' => __( 'user-circle', 'tour-booking-manager' ),
                'fas fa-user-md' => __( 'user-md', 'tour-booking-manager' ),
                'fas fa-user-plus' => __( 'user-plus', 'tour-booking-manager' ),
                'fas fa-user-secret' => __( 'user-secret', 'tour-booking-manager' ),
                'fas fa-user-times' => __( 'user-times', 'tour-booking-manager' ),
                'fas fa-users' => __( 'users', 'tour-booking-manager' ),
                'fab fa-ussunnah' => __( 'ussunnah', 'tour-booking-manager' ),
                'fas fa-utensil-spoon' => __( 'utensil-spoon', 'tour-booking-manager' ),
                'fas fa-utensils' => __( 'utensils', 'tour-booking-manager' ),
                'fab fa-vaadin' => __( 'vaadin', 'tour-booking-manager' ),
                'fas fa-venus' => __( 'venus', 'tour-booking-manager' ),
                'fas fa-venus-double' => __( 'venus-double', 'tour-booking-manager' ),
                'fas fa-venus-mars' => __( 'venus-mars', 'tour-booking-manager' ),
                'fab fa-viacoin' => __( 'viacoin', 'tour-booking-manager' ),
                'fab fa-viadeo' => __( 'viadeo', 'tour-booking-manager' ),
                'fab fa-viadeo-square' => __( 'viadeo-square', 'tour-booking-manager' ),
                'fas fa-vial' => __( 'vial', 'tour-booking-manager' ),
                'fas fa-vials' => __( 'vials', 'tour-booking-manager' ),
                'fab fa-viber' => __( 'viber', 'tour-booking-manager' ),
                'fas fa-video' => __( 'video', 'tour-booking-manager' ),
                'fas fa-video-slash' => __( 'video-slash', 'tour-booking-manager' ),
                'fab fa-vimeo' => __( 'vimeo', 'tour-booking-manager' ),
                'fab fa-vimeo-square' => __( 'vimeo-square', 'tour-booking-manager' ),
                'fab fa-vimeo-v' => __( 'vimeo-v', 'tour-booking-manager' ),
                'fab fa-vine' => __( 'vine', 'tour-booking-manager' ),
                'fab fa-vk' => __( 'vk', 'tour-booking-manager' ),
                'fab fa-vnv' => __( 'vnv', 'tour-booking-manager' ),
                'fas fa-volleyball-ball' => __( 'volleyball-ball', 'tour-booking-manager' ),
                'fas fa-volume-down' => __( 'volume-down', 'tour-booking-manager' ),
                'fas fa-volume-off' => __( 'volume-off', 'tour-booking-manager' ),
                'fas fa-volume-up' => __( 'volume-up', 'tour-booking-manager' ),
                'fab fa-vuejs' => __( 'vuejs', 'tour-booking-manager' ),
                'fas fa-warehouse' => __( 'warehouse', 'tour-booking-manager' ),
                'fab fa-weibo' => __( 'weibo', 'tour-booking-manager' ),
                'fas fa-weight' => __( 'weight', 'tour-booking-manager' ),
                'fab fa-weixin' => __( 'weixin', 'tour-booking-manager' ),
                'fab fa-whatsapp' => __( 'whatsapp', 'tour-booking-manager' ),
                'fab fa-whatsapp-square' => __( 'whatsapp-square', 'tour-booking-manager' ),
                'fas fa-wheelchair' => __( 'wheelchair', 'tour-booking-manager' ),
                'fab fa-whmcs' => __( 'whmcs', 'tour-booking-manager' ),
                'fas fa-wifi' => __( 'wifi', 'tour-booking-manager' ),
                'fab fa-wikipedia-w' => __( 'wikipedia-w', 'tour-booking-manager' ),
                'fas fa-window-close' => __( 'window-close', 'tour-booking-manager' ),
                'far fa-window-close' => __( 'window-close', 'tour-booking-manager' ),
                'fas fa-window-maximize' => __( 'window-maximize', 'tour-booking-manager' ),
                'far fa-window-maximize' => __( 'window-maximize', 'tour-booking-manager' ),
                'fas fa-window-minimize' => __( 'window-minimize', 'tour-booking-manager' ),
                'far fa-window-minimize' => __( 'window-minimize', 'tour-booking-manager' ),
                'fas fa-window-restore' => __( 'window-restore', 'tour-booking-manager' ),
                'far fa-window-restore' => __( 'window-restore', 'tour-booking-manager' ),
                'fab fa-windows' => __( 'windows', 'tour-booking-manager' ),
                'fas fa-wine-glass' => __( 'wine-glass', 'tour-booking-manager' ),
                'fas fa-won-sign' => __( 'won-sign', 'tour-booking-manager' ),
                'fab fa-wordpress' => __( 'wordpress', 'tour-booking-manager' ),
                'fab fa-wordpress-simple' => __( 'wordpress-simple', 'tour-booking-manager' ),
                'fab fa-wpbeginner' => __( 'wpbeginner', 'tour-booking-manager' ),
                'fab fa-wpexplorer' => __( 'wpexplorer', 'tour-booking-manager' ),
                'fab fa-wpforms' => __( 'wpforms', 'tour-booking-manager' ),
                'fas fa-wrench' => __( 'wrench', 'tour-booking-manager' ),
                'fas fa-x-ray' => __( 'x-ray', 'tour-booking-manager' ),
                'fab fa-xbox' => __( 'xbox', 'tour-booking-manager' ),
                'fab fa-xing' => __( 'xing', 'tour-booking-manager' ),
                'fab fa-xing-square' => __( 'xing-square', 'tour-booking-manager' ),
                'fab fa-y-combinator' => __( 'y-combinator', 'tour-booking-manager' ),
                'fab fa-yahoo' => __( 'yahoo', 'tour-booking-manager' ),
                'fab fa-yandex' => __( 'yandex', 'tour-booking-manager' ),
                'fab fa-yandex-international' => __( 'yandex-international', 'tour-booking-manager' ),
                'fab fa-yelp' => __( 'yelp', 'tour-booking-manager' ),
                'fas fa-yen-sign' => __( 'yen-sign', 'tour-booking-manager' ),
                'fab fa-yoast' => __( 'yoast', 'tour-booking-manager' ),
                'fab fa-youtube' => __( 'youtube', 'tour-booking-manager' ),
                'fab fa-youtube-square' => __( 'youtube-square', 'tour-booking-manager' ),
            );
            return apply_filters( 'FONTAWESOME_ARRAY', $fonts_arr );
        }
    }
    global $wbtmcore;
    $wbtmcore = new FormFieldsGenerator();
}
	if ( ! function_exists( 'mep_field_generator' ) ) {
		function mep_field_generator( $type, $option ) {
			$FormFieldsGenerator = new FormFieldsGenerator();
			
			if ( $type === 'text' ) {
				return $FormFieldsGenerator->field_text( $option );
			} elseif ( $type === 'text_multi' ) {
				return $FormFieldsGenerator->field_text_multi( $option );
			} elseif ( $type === 'textarea' ) {
				return $FormFieldsGenerator->field_textarea( $option );
			} elseif ( $type === 'checkbox' ) {
				return $FormFieldsGenerator->field_checkbox( $option );
			} elseif ( $type === 'checkbox_multi' ) {
				return $FormFieldsGenerator->field_checkbox_multi( $option );
			} elseif ( $type === 'radio' ) {
				return $FormFieldsGenerator->field_radio( $option );
			} elseif ( $type === 'select' ) {
				return $FormFieldsGenerator->field_select( $option );
			} elseif ( $type === 'range' ) {
				return $FormFieldsGenerator->field_range( $option );
			} elseif ( $type === 'range_input' ) {
				return $FormFieldsGenerator->field_range_input( $option );
			} elseif ( $type === 'switch' ) {
				return $FormFieldsGenerator->field_switch( $option );
			} elseif ( $type === 'switch_multi' ) {
				return $FormFieldsGenerator->field_switch_multi( $option );
			} elseif ( $type === 'switch_img' ) {
				return $FormFieldsGenerator->field_switch_img( $option );
			} elseif ( $type === 'time_format' ) {
				return $FormFieldsGenerator->field_time_format( $option );
			} elseif ( $type === 'date_format' ) {
				return $FormFieldsGenerator->field_date_format( $option );
			} elseif ( $type === 'datepicker' ) {
				return $FormFieldsGenerator->field_datepicker( $option );
			} elseif ( $type === 'color_sets' ) {
				return $FormFieldsGenerator->field_color_sets( $option );
			} elseif ( $type === 'colorpicker' ) {
				return $FormFieldsGenerator->field_colorpicker( $option );
			} elseif ( $type === 'colorpicker_multi' ) {
				return $FormFieldsGenerator->field_colorpicker_multi( $option );
			} elseif ( $type === 'link_color' ) {
				return $FormFieldsGenerator->field_link_color( $option );
			} elseif ( $type === 'icon' ) {
				return $FormFieldsGenerator->field_icon( $option );
			} elseif ( $type === 'mp_icon' ) {
				return $FormFieldsGenerator->mp_field_icon( $option );
			} elseif ( $type === 'icon_multi' ) {
				return $FormFieldsGenerator->field_icon_multi( $option );
			} elseif ( $type === 'dimensions' ) {
				return $FormFieldsGenerator->field_dimensions( $option );
			} elseif ( $type === 'wp_editor' ) {
				return $FormFieldsGenerator->field_wp_editor( $option );
			} elseif ( $type === 'select2' ) {
				return $FormFieldsGenerator->field_select2( $option );
			} elseif ( $type === 'faq' ) {
				return $FormFieldsGenerator->field_faq( $option );
			} elseif ( $type === 'grid' ) {
				return $FormFieldsGenerator->field_grid( $option );
			} elseif ( $type === 'color_palette' ) {
				return $FormFieldsGenerator->field_color_palette( $option );
			} elseif ( $type === 'color_palette_multi' ) {
				return $FormFieldsGenerator->field_color_palette_multi( $option );
			} elseif ( $type === 'media' ) {
				return $FormFieldsGenerator->field_media( $option );
			} elseif ( $type === 'media_multi' ) {
				return $FormFieldsGenerator->field_media_multi( $option );
			} elseif ( $type === 'repeatable' ) {
				return $FormFieldsGenerator->field_repeatable( $option );
			} elseif ( $type === 'user' ) {
				return $FormFieldsGenerator->field_user( $option );
			} elseif ( $type === 'margin' ) {
				return $FormFieldsGenerator->field_margin( $option );
			} elseif ( $type === 'padding' ) {
				return $FormFieldsGenerator->field_padding( $option );
			} elseif ( $type === 'border' ) {
				return $FormFieldsGenerator->field_border( $option );
			} elseif ( $type === 'switcher' ) {
				return $FormFieldsGenerator->field_switcher( $option );
			} elseif ( $type === 'password' ) {
				return $FormFieldsGenerator->field_password( $option );
			} elseif ( $type === 'post_objects' ) {
				return $FormFieldsGenerator->field_post_objects( $option );
			} elseif ( $type === 'google_map' ) {
				return $FormFieldsGenerator->field_google_map( $option );
			} elseif ( $type === 'image_link' ) {
				return $FormFieldsGenerator->field_image_link( $option );
			} elseif ( $type === 'time' ) {
				return $FormFieldsGenerator->field_time( $option );
			} else {
				return '';
			}
		}
	}