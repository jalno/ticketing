<?php
namespace themes\clipone\views;
use \packages\base\translator;
use \themes\clipone\utility;
trait formTrait{
	public function createField($options = array()){
		if(!isset($options['name'])){
			$options['name'] = '';
		}
		if(!isset($options['error']) or $options['error']){
			$error =  $this->getFromErrorsByInput($options['name']);
		}else{
			$error = false;
		}
		$code = '<div class="form-group'.($error ? ' has-error' : '').'">';
		if(isset($options['label']) and $options['label'])
			$code .= '<label class="control-label">'.$options['label'].'</label>';
		if(!isset($options['type'])){
			$options['type'] = 'text';
		}
		if(!isset($options['value'])){
			$options['value'] = $this->getDataForm($options['name']);
		}
		if(!isset($options['class'])){
			$options['class'] = 'form-control';
		}
		if($options['type'] == 'radio'){
			if(!isset($options['inline'])){
				$options['inline'] = false;
			}
			$code .= "<div>";
			foreach($options['options'] as $option){
				$code .= '<label class="radio'.($options['inline'] ? '-inline' : '').'">';
				$code .= "<input type=\"radio\" name=\"{$options['name']}\" value=\"{$option['value']}\"";
				if(isset($option['class']) and $option['class']){
					$code .= " class=\"{$option['class']}\"";
				}
				if($option['value'] == $options['value']){
					$code .= " checked";
				}
				$code .= ">".$option['label'];
				$code .= '</label>';
			}
		}elseif($options['type'] == 'select'){
			$code .= "<select";
		}else{
			$code .= "<input type=\"{$options['type']}\" value=\"{$options['value']}\"";
		}
		if($options['type'] != 'radio'){
			$code .= " name=\"{$options['name']}\"";
			if($options['class']){
				$code .= " class=\"{$options['class']}\"";
			}
			if(isset($options['placeholder']) and $options['placeholder']){
				$code .= " placeholder=\"{$options['placeholder']}\"";
			}
			$code .= ">";
		}
		if($options['type'] == 'select'){
		 	$code .= utility::selectOptions($options['options'], $options['value']);
			$code .="</select>";
		}
		if($options['type'] == 'radio'){
			$code .= "</div>";
		}
		if($error){
			$text = null;
			if(isset($options['error']) and is_array($options['error'])){
				foreach($options['error'] as $type => $value){
					if($type == $error->error){
						if(substr($value, -strlen($error->error)) == $error->error){
							$text = translator::trans($value);
						}else{
							$text = $value;
						}
						break;
					}
				}
			}
			if(!$text){
				$text = translator::trans("{$options['name']}.{$error->error}");
			}
			if(!$text){
				$text = translator::trans($error->error);
			}
			if($text){
				$code .= "<span class=\"help-block\" id=\"{$options['name']}-error\">{$text}</span>";
			}
		}
		$code .= '</div>';
		echo $code;
	}
}
