<?php
namespace themes\clipone\views;
trait listTrait{
	private $buttons;
	public function setButton($name, $active, $params = array()){
		if(!isset($params['classes'])){
			$params['classes'] = array('btn', 'btn-xs', 'btn-default');
		}
		if(isset($params['title']) and $params['title']){
			$params['classes'][] = 'tooltips';
		}
		if(!isset($params['link'])){
			$params['link'] = '#';
		}
		$button = array(
			'active' => $active,
			'params' => $params
		);
		$this->buttons[$name] = $button;
	}
	public function setButtonActive($name, $active){
		if(isset($this->buttons[$name])){
			$this->buttons[$name]['active'] = $active;
			return true;
		}
		return false;
	}
	public function setButtonParam($name, $parameter, $value){
		if(isset($this->buttons[$name])){
			$this->buttons[$name]['params'][$parameter] = $value;
			return true;
		}
		return false;
	}
	public function unsetButtonParam($name, $parameter){
		if(isset($this->buttons[$name])){
			unset($this->buttons[$name]['params'][$parameter]);
			return true;
		}
		return false;
	}
	public function hasButtons(){
		$have = false;
		foreach($this->buttons as $btn){
			if($btn['active']){
				$have = true;
				break;
			}
		}
		return $have;
	}
	public function genButtons($responsive = true){
		$buttons = array();
		foreach($this->buttons as $name => $btn){
			if($btn['active']){
				$buttons[$name] = $btn;
			}
		}
		$code = '';
		if($buttons){
			if($responsive and count($buttons) > 1){
				$code .= '<div class="visible-md visible-lg hidden-sm hidden-xs">';
			}
			foreach($buttons as $btn){
				$code .= '<a';
				if(isset($btn['params']['link']) and $btn['params']['link']){
					$code .= ' href="'.$btn['params']['link'].'"';
				}
				if(isset($btn['params']['classes']) and $btn['params']['classes']){
					if(is_array($btn['params']['classes'])){
						$btn['params']['classes'] = implode(" ", $btn['params']['classes']);
					}
					$code .= ' class="'.$btn['params']['classes'].'"';
				}
				if(isset($btn['params']['data']) and $btn['params']['data']){
					foreach($btn['params']['data'] as $name => $value){
						$code .= ' data-'.$name.'="'.$value.'"';
					}
				}
				if(isset($btn['params']['title']) and $btn['params']['title']){
					$code .= ' title="'.$btn['params']['title'].'"';
				}
				$code .= '>';
				if(isset($btn['params']['icon']) and $btn['params']['icon']){
					$code .= '<i class="'.$btn['params']['icon'].'"></i>';
				}
				$code .= '</a> ';
			}
			if($responsive and count($buttons) > 1){
				$code .= '</div>';
				$code .= '<div class="visible-xs visible-sm hidden-md hidden-lg">';
				$code .= '<div class="btn-group">';
				$code .= '<a class="btn btn-primary dropdown-toggle btn-sm" data-toggle="dropdown" href="#"><i class="fa fa-cog"></i> <span class="caret"></span></a>';
				$code .= '<ul role="menu" class="dropdown-menu pull-right">';
			}
			foreach($buttons as $btn){
				$code .= '<li><a tabindex="-1"';
				if(isset($btn['params']['link']) and $btn['params']['link']){
					$code .= ' href="'.$btn['params']['link'].'"';
				}
				if(isset($btn['data']) and $btn['params']['data']){
					foreach($btn['params']['data'] as $name => $value){
						$code .= ' data-'.$name.'="'.$value.'"';
					}
				}
				$code .= '>';
				if(isset($btn['params']['icon']) and $btn['params']['icon']){
					$code .= '<i class="'.$btn['params']['icon'].'"></i>';
				}
				if(isset($btn['params']['title']) and $btn['params']['title']){
					$code .= ' '.$btn['params']['title'];
				}
				$code .= '</a></li>';
			}
			if($responsive and count($buttons) > 1){
				$code .= '</ul></div></div>';
			}
		}


		return $code;
	}
}

?>
