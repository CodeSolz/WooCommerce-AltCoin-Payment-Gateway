<?php namespace WooGateWayCoreLib\admin\builders;
/**
 * From Builder
 * 
 * @package WAPG Admin 
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}


class CsFormBuilder {
    
    /**
     * Generate html form fields
     * 
     * @param type $fields
     * @return boolean
     */
    public function generate_html_fields( $fields, $return_array = false ){
        if( empty( $fields ) ) { return false; }
        
        $html_fields = array();
        $field_count = count( $fields );
        
        $i = 1;
        foreach( $fields as  $field_name => $field ){
            $no_border = ''; $input = '';
            if( $field_count == $i ){
                $no_border = 'no-border';
            }
            
            
            if( isset($field['type']) && $field['type'] == 'section_title' ){
                $input  = $this->form_field_section_title( $field );
            }elseif( isset($field['type']) && $field['type'] == 'alert_div' ){
                $input  = $this->generate_alert_div( $field );
            }else{
                
                //if field start with new section
                if( isset($field['section']) && !empty($field['section']) ){
                    $input  = $field['section'];
                }
                
                $input  .= '<div class="form-group '.$no_border.'">';
                $input  .= $this->generate_field( $field_name, $field, $i );
                $input  .= '</div>';

                if( isset($field['section']) && !empty($field['section']) ){
                    $input  .= '</div>';
                }
                
            }
            
            $html_fields[] = $input;
            $i++;
        }
        
        //return html as string
        if( false == $return_array ){
            echo implode( '', $html_fields );
        }
        
        //return array
        return $html_fields;
    }
    
    /**
     * generate field
     * 
     * @param type $field
     */
    private function generate_field( $field_name, $field, $field_id ){
        $input = '<div class="label"><label>';
        $input .= $this->generate_title( $field );
        $input .= '</label></div>';
        $input .= '<div class="input-group">';
        if( isset( $field['input_field_wrap_start'] ) && !empty($field['input_field_wrap_start']) ){
            $input .= $field['input_field_wrap_start'];
        }
        
        
        
        if( $field['type'] == 'text' || $field['type'] == 'number' || $field['type'] == 'password' ){
            $input .= $this->generate_text_field($field_name, $field, $field_id);
        }
        elseif( $field['type'] == 'select' ){
            $input .= $this->generate_select_field($field_name, $field, $field_id);
        }
        elseif( $field['type'] == 'checkbox' ){
            $input .= $this->generate_checkbox_field($field_name, $field, $field_id);
        }
        elseif( $field['type'] == 'miscellaneous' ){
            foreach( $field['options'] as $item_name => $item_assets ){
                if( $item_assets['type'] == 'text' || $item_assets['type'] == 'number' || $item_assets['type'] == 'password' ){
                    $input .= $this->generate_text_field($item_name, $item_assets, 'mis_' . $field_id);
                }elseif( $item_assets['type'] == 'select' ){
                    $input .= $this->generate_select_field($item_name, $item_assets, 'mis_' . $field_id);
                }
                
                if( isset( $item_assets['after_text'] ) ){
                    $input .= $item_assets['after_text'];
                }
                
            }
        }
        
        
        if( isset( $field['input_field_wrap_end'] ) && !empty($field['input_field_wrap_end']) ){
            $input .= $field['input_field_wrap_end'];
        }
        if( isset( $field['desc_tip'] ) && !empty($field['desc_tip']) ){
            $input .= '<p class="description">'.$field['desc_tip'].'</p>';
        }
        $input .= '</div>';
        
        return $input;
    }
    
    /**
     * Generate section title
     * 
     * @param type $args
     * @return boolean
     */
    public function form_field_section_title( $args ){
        $section_title = '<div class="section-title">' . sprintf( $args['title'], '<i class="fa fa-edit"></i> ')  . '</div>';
        if( isset( $args['desc_tip'] ) && !empty( $args['desc_tip'] ) ){
            $section_title .= '<p class="section-description">' . $args['desc_tip']  . '</p>';
        }
        return $section_title;
    }
    
    /**
     * Generate alert div
     * 
     * @param type $args
     * @return string
     */
    private function generate_alert_div( $args ){
        $input =  '<div class="'.$args['section_wrapper_class'].'">';
        $input .= '<div class="'.$args['alert_class'].'">' .$args['alert_msg']. '</div>';
        $input .= '</div>';
        return $input;
    }

    /**
     * Generate attribute
     * 
     * @param type $field_name
     * @param type $field
     * @param type $field_id
     * @return type
     */
    private function generate_attribute( $field_name, $field, $field_id ){
        $input_item = '';
        foreach( $field as $item_id => $item_val ){
            if( $field['type'] == 'select' && ($item_id == 'placeholder' || $item_id == 'type' ) ){
                continue;
            }
            
            if( method_exists( $this, ( $method = 'attr_' .$item_id ) )  ){
                $input_item .= $this->$method( $item_val );
            }
            if( $item_id == 'custom_attributes' ){
                foreach( $item_val  as  $cs_attr_name => $cs_attr_val ){
                    $input_item .= " $cs_attr_name = '$cs_attr_val' ";
                }
            }
        }
        $input_item .= $this->attr_name($field_name);
        $input_item .= $this->attr_id($field_id);
        
        return $input_item;
    }

    /**
     * Generate text filed
     * 
     * @param type $field_name
     * @param type $field
     * @param type $field_id
     * @return type
     */
    private function generate_text_field(  $field_name, $field, $field_id ){
        $input_item = $this->generate_attribute($field_name, $field, $field_id);
        return "<input  {$input_item} />";
    }
    
    /**
     * Generate text filed
     * 
     * @param type $field_name
     * @param type $field
     * @param type $field_id
     * @return type
     */
    private function generate_checkbox_field(  $field_name, $field, $field_id ){
        $value = '';
        if( isset( $field['value']) && !empty( $field['value'] ) ){
            $value = $field['value'] == 1 ? ' checked = "checked" ' : '';
            unset($field['value']);
        }
        
        $input_item = $this->generate_attribute( $field_name, $field, $field_id );
        return "<input  {$input_item} {$value}/>";
    }
    
    /**
     * Generate select field
     * 
     * @param type $field_name
     * @param type $field
     * @param type $field_id
     * @return string
     */
    private function generate_select_field( $field_name, $field, $field_id ){
        $value = '';
        if( isset( $field['value']) && !empty( $field['value'] ) ){
            $value = $field['value'];
            unset($field['value']);
        }
        
        $input_item = $this->generate_attribute($field_name, $field, $field_id);
        $input = "<select  {$input_item} >";
        $input .= '<option value="">==================== '.$field['placeholder'].' ====================</option>';
        foreach( $field['options'] as $key => $val ){
            $selected = '';
            if( $key == $value ){
                $selected = 'selected="selected"';
            }
            $input .= '<option value ="'.$key.'" '.$selected.' >'.$val.'</option>';
        }
        $input .= "</select>";
        
        return $input;
    }

    /**
     * Generate title
     */
    private function generate_title( $field ){
        return isset( $field['title'] ) ? $field['title'] : '&nbsp';
    }
    
    /**
     * Attr type
     * 
     * @param type $type
     * @return type
     */
    private function attr_type( $type ){
        return ' type = "'.$type.'"';
    }
    
    /**
     * attr class
     * 
     * @param type $class
     * @return string
     */
    private function attr_class( $class ){
        return ' class = "' . $class . '" ';
    }
    
    /**
     * attr placeholder
     * 
     * @param type $placeholder
     * @return string
     */
    private function attr_placeholder( $placeholder ){
        return ' placeholder = "' . $placeholder . '" ';
    }
    
    /**
     * attr value
     * 
     * @param type $value
     * @return string
     */
    private function attr_value( $value ){
        return ' value = "' . $value . '" ';
    }
    
    /**
     * attr disable
     * 
     * @param type $item
     * @return string
     */
    private function attr_disabled(){
        return ' disabled = "disabled" ';
    }
    
    /**
     * attr required
     * 
     * @param type $item
     * @return string
     */
    private function attr_required(){
        return ' required = "required" ';
    }
    
    /**
     * attr name
     * 
     * @param type $field_name
     * @return string
     */
    private function attr_name( $field_name ){
        return ' name = "'.$field_name.'" ';
    }
    
    /**
     * attr name
     * 
     * @param type $field_id
     * @return string
     */
    private function attr_id( $field_id ){
        return ' id = "cs_field_'.$field_id.'" ';
    }
    
    
    
}
